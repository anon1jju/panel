<?php
// Untuk Debugging (HAPUS ATAU KOMENTARI DI PRODUKSI)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';
// Set header untuk output JSON
header('Content-Type: application/json');

// --- Konfigurasi ---
$emailValidationServiceKeyFile = 'key1.json'; // File untuk API key layanan validasi email
$output = [];

// --- Fungsi untuk mengakhiri skrip dan mencetak output JSON ---
function printJsonAndExit($data) {
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

if (isset($conn) && $conn) {
    mysqli_close($conn);
    // error_log("DEBUG: mysqli connection from config.php closed."); // Untuk debugging
}

$pdo = null; // Inisialisasi variabel PDO

try {
    // Gunakan variabel dari config.php untuk membuat koneksi PDO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    error_log("DEBUG: Database connection successful using config.php variables."); // Log koneksi berhasil
} catch (PDOException $e) {
    error_log("FATAL: Database connection failed (PDO using config variables): " . $e->getMessage());
    $output['status'] = 'database_error';
    $output['message'] = "Database connection failed. Please check server logs.";
    printJsonAndExit($output);
}


if (!isset($_GET['customer_api_key']) || empty(trim($_GET['customer_api_key']))) {
    $output['status'] = 'auth_error';
    $output['message'] = "Error: Parameter 'customer_api_key' tidak ditemukan atau kosong.";
    printJsonAndExit($output);
}
$customerApiKey = trim($_GET['customer_api_key']);
error_log("DEBUG: Received customer_api_key: " . $customerApiKey);

if (isset($_GET['email']) && !empty(trim($_GET['email']))) {
    $emailToValidate = trim($_GET['email']);
    if (!filter_var($emailToValidate, FILTER_VALIDATE_EMAIL)) {
        $output['status'] = 'input_error';
        $output['message'] = "Error: Format email yang diberikan tidak valid.";
        $output['provided_email'] = $emailToValidate;
        printJsonAndExit($output);
    }
    $encodedEmailToValidate = urlencode($emailToValidate);
    error_log("DEBUG: Received email: " . $emailToValidate . ", Encoded: " . $encodedEmailToValidate);
} else {
    $output['status'] = 'input_error';
    $output['message'] = "Error: Parameter 'email' tidak ditemukan atau kosong di URL.";
    printJsonAndExit($output);
}
// --- Akhir pengambilan parameter ---


// --- Validasi API Key Pelanggan dan Cek Batas Permintaan ---
$customerId = null;
$dailyRequestLimit = 15000; // Default

error_log("DEBUG: Attempting to validate customer API key: " . $customerApiKey);
if ($pdo) {
    try {
        // Tambahkan pengecekan is_active jika kolom itu ada di tabel Anda
        $stmt = $pdo->prepare("SELECT id, daily_request_limit, is_active FROM customers WHERE api_key = :api_key LIMIT 1");
        $stmt->bindParam(':api_key', $customerApiKey);
        $stmt->execute();
        $customer = $stmt->fetch();

        if ($customer) {
            if (isset($customer['is_active']) && !$customer['is_active']) { // Cek jika kolom is_active ada dan nilainya false
                error_log("AUTH_FAIL: Customer account is inactive. API Key: " . $customerApiKey);
                $output['status'] = 'auth_error';
                $output['message'] = "Error: Akun pelanggan tidak aktif.";
                printJsonAndExit($output);
            }
            $customerId = $customer['id'];
            $dailyRequestLimit = (int)$customer['daily_request_limit'];
            error_log("DEBUG: Customer validated. ID: " . $customerId . ", Daily Limit: " . $dailyRequestLimit);
        } else {
            error_log("AUTH_FAIL: Invalid customer API Key: " . $customerApiKey);
            $output['status'] = 'auth_error';
            $output['message'] = "Error: API Key pelanggan tidak valid.";
            printJsonAndExit($output);
        }
    } catch (PDOException $e) {
        error_log("DB_ERROR (customer_check): " . $e->getMessage() . " for API Key: " . $customerApiKey);
        $output['status'] = 'database_error';
        $output['message'] = "Database query error (customer check). Please check server logs.";
        // $output['message'] = "Database query error (customer check): " . $e->getMessage();
        printJsonAndExit($output);
    }
} else {
    // Ini seharusnya tidak terjadi jika koneksi awal berhasil
    error_log("FATAL: PDO object is null before customer check. This should not happen.");
    $output['status'] = 'database_error';
    $output['message'] = "Database connection not established for customer check.";
    printJsonAndExit($output);
}

$currentDate = date('Y-m-d'); // Tanggal server saat ini
error_log("DEBUG: Current server date for usage check: " . $currentDate);
$requestsToday = 0;

if ($pdo && $customerId) { // Pastikan customerId didapatkan
    try {
        $stmt = $pdo->prepare("SELECT request_count FROM customer_api_usage WHERE customer_id = :customer_id AND usage_date = :usage_date LIMIT 1");
        $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->bindParam(':usage_date', $currentDate);
        $stmt->execute();
        $usage = $stmt->fetch();

        if ($usage) {
            $requestsToday = (int)$usage['request_count'];
        }
        error_log("DEBUG: Customer ID: " . $customerId . ", Requests today (" . $currentDate . "): " . $requestsToday);
    } catch (PDOException $e) {
        error_log("DB_ERROR (usage_check): " . $e->getMessage() . " for Customer ID: " . $customerId);
        $output['status'] = 'database_error';
        $output['message'] = "Database query error (usage check). Please check server logs.";
        // $output['message'] = "Database query error (usage check): " . $e->getMessage();
        printJsonAndExit($output);
    }
} else {
    error_log("DEBUG: Skipped usage check. PDO is " . ($pdo ? 'set' : 'null') . ", CustomerID is " . ($customerId ?: 'null or not set'));
}


if ($requestsToday >= $dailyRequestLimit) {
    error_log("LIMIT_EXCEEDED: Customer ID: " . $customerId . ", Requests: " . $requestsToday . ", Limit: " . $dailyRequestLimit);
    $output['status'] = 'limit_exceeded';
    $output['message'] = "Error: Batas permintaan harian (" . $dailyRequestLimit . ") telah tercapai.";
    $output['requests_today'] = $requestsToday;

    // --- [TAMBAHAN] Menghitung dan menambahkan waktu reset ke output ---
    // Set zona waktu ke Waktu Indonesia Barat (WIB)
    $timezone = new DateTimeZone('Asia/Jakarta');
    // Dapatkan waktu reset, yaitu tengah malam (pukul 00:00:00) besok
    $resetTime = new DateTime('tomorrow', $timezone);
    // Tambahkan informasi waktu reset ke dalam array output
    $output['dapat_digunakan_lagi_pada'] = $resetTime->format('Y-m-d H:i:s T');
    // --- [AKHIR TAMBAHAN] ---

    printJsonAndExit($output);
}
// --- Akhir Validasi API Key Pelanggan dan Cek Batas ---


// --- Baca Konfigurasi Layanan Validasi Email (key1.json) ---
if (!file_exists($emailValidationServiceKeyFile)) {
    error_log("CONFIG_ERROR: Service key file '$emailValidationServiceKeyFile' not found.");
    $output['status'] = 'config_error';
    $output['message'] = "Error: File konfigurasi layanan '$emailValidationServiceKeyFile' tidak ditemukan.";
    printJsonAndExit($output);
}
$jsonData = file_get_contents($emailValidationServiceKeyFile);
if ($jsonData === false) {
    error_log("CONFIG_ERROR: Failed to read service key file '$emailValidationServiceKeyFile'.");
    $output['status'] = 'config_error';
    $output['message'] = "Error: Gagal membaca file konfigurasi layanan '$emailValidationServiceKeyFile'.";
    printJsonAndExit($output);
}
$apiKeyAndReferers = json_decode($jsonData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("CONFIG_ERROR: Failed to parse JSON from '$emailValidationServiceKeyFile'. Error: " . json_last_error_msg());
    $output['status'] = 'config_error';
    $output['message'] = "Error: Gagal mem-parsing JSON dari file '$emailValidationServiceKeyFile'. Pesan error: " . json_last_error_msg();
    printJsonAndExit($output);
}
if (empty($apiKeyAndReferers) || !is_array($apiKeyAndReferers) || count($apiKeyAndReferers) === 0) {
    error_log("CONFIG_ERROR: Service key data in '$emailValidationServiceKeyFile' is empty or malformed.");
    $output['status'] = 'config_error';
    $output['message'] = "Error: Data API Key layanan kosong atau formatnya tidak sesuai dalam '$emailValidationServiceKeyFile'.";
    printJsonAndExit($output);
}
// --- Akhir Baca Konfigurasi Layanan ---

// --- Pilih satu pasangan key/referer layanan secara acak ---
$randomIndex = array_rand($apiKeyAndReferers);
$selectedPair = $apiKeyAndReferers[$randomIndex];

if (!isset($selectedPair['key']) || !isset($selectedPair['referer'])) {
    error_log("CONFIG_ERROR: Randomly selected service key/referer pair is malformed. Index: " . $randomIndex);
    $output['status'] = 'config_error';
    $output['message'] = "Error: Pasangan key/referer layanan yang dipilih secara acak tidak memiliki format yang benar.";
    printJsonAndExit($output);
}
$serviceApiKey = $selectedPair['key'];
$serviceRefererUrl = $selectedPair['referer'];
error_log("DEBUG: Using service API Key (randomly selected): " . substr($serviceApiKey, 0, 4) . "****, Referer: " . $serviceRefererUrl);

$output['email_validated'] = $emailToValidate;
// --- Akhir Pemilihan Acak Key Layanan ---

// Konstruksi URL endpoint layanan validasi email
$url = 'https://services.postcodeanywhere.co.uk/EmailValidation/Interactive/Validate/v2.00/json3ex.ws?Key=' . urlencode($serviceApiKey) . '&Email=' . $encodedEmailToValidate;
error_log("DEBUG: cURL Request URL: " . preg_replace('/Key=([^&]+)/', 'Key=HIDDEN', $url)); // Log URL tanpa service API key

// --- Persiapan dan Eksekusi cURL ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Referer: ' . $serviceRefererUrl,
]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErrorNumber = curl_errno($ch);
$curlErrorMessage = curl_error($ch);
curl_close($ch);
error_log("DEBUG: cURL execution complete. HTTP Code: " . $httpCode . ", cURL Error No: " . $curlErrorNumber . ", cURL Error Msg: " . $curlErrorMessage);
// --- Akhir cURL ---

// --- Update Jumlah Permintaan Pelanggan di Database ---
error_log("DEBUG: Attempting to update usage count. PDO is " . ($pdo ? 'set' : 'null') . ", CustomerID is " . ($customerId ?: 'null or not set'));
if ($pdo && $customerId) { // Pastikan customerId valid dan PDO ada
    try {
        $stmt = $pdo->prepare("
            INSERT INTO customer_api_usage (customer_id, usage_date, request_count)
            VALUES (:customer_id, :usage_date, 1)
            ON DUPLICATE KEY UPDATE request_count = request_count + 0
        ");
        $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->bindParam(':usage_date', $currentDate); // $currentDate sudah Y-m-d
        $stmt->execute();
        error_log("DB_UPDATE: Usage count updated for Customer ID: " . $customerId . " on Date: " . $currentDate . ". Affected rows: " . $stmt->rowCount());

    } catch (PDOException $e) {
        // --- MODIFIKASI UNTUK DEBUGGING ---
        // Log error ini ke log server, dan juga kirim respons error ke klien
        error_log("DB_ERROR (usage_update): " . $e->getMessage() . " for Customer ID: " . $customerId . " on Date: " . $currentDate);
        $output['status'] = 'database_update_error';
        $output['message'] = "Database error during usage update. Please check server logs.";
        // $output['message'] = "Database error during usage update: " . $e->getMessage(); // Detail error (hati-hati di produksi)
        $output['debug_customer_id'] = $customerId;
        $output['debug_current_date'] = $currentDate;
        printJsonAndExit($output); // HENTIKAN skrip di sini agar error terlihat jelas oleh Anda
        // --- AKHIR MODIFIKASI UNTUK DEBUGGING ---
    }
} else {
    error_log("DEBUG: Skipped usage update because pdo or customerId was not set properly. PDO: " . ($pdo ? 'OK' : 'FAIL') . ", CustomerID: " . ($customerId ?: 'NOT SET'));
}
// --- Akhir Update Jumlah Permintaan ---


// Menyiapkan output berdasarkan hasil cURL
$output['http_status_code_from_service'] = $httpCode;
$output['usage'] = $requestsToday;

if ($curlErrorNumber !== 0) {
    $output['status'] = 'service_connection_error';
    $output['message'] = "Error saat menghubungi layanan validasi email: " . $curlErrorMessage;
    $output['server_response'] = null;
} else {
    if ($httpCode == 200) {
        $output['status'] = 'success';
        $decodedResponse = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $output['server_response'] = $decodedResponse;
        } else {
            $output['server_response'] = $response; // Jika bukan JSON valid
        }
    } else {
        $output['status'] = 'service_provider_error';
        $output['message'] = "Layanan validasi email merespons dengan kode HTTP: " . $httpCode;
        $decodedResponse = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $output['server_response'] = $decodedResponse;
        } else {
            $output['server_response'] = $response;
        }
    }
}

printJsonAndExit($output);

?>