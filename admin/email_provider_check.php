<?php

// Mencegah akses langsung ke file ini
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Akses Dilarang');
}

// Memastikan skrip ini dipanggil oleh api.php yang sudah memiliki koneksi DB dan data customer
if (!isset($db) || !isset($customer)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Kesalahan Internal: Skrip tidak dapat dijalankan secara mandiri.']);
    exit;
}

// Tentukan biaya (cost) untuk setiap permintaan ke endpoint ini
$cost_per_request = 1;

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan. Gunakan POST.']);
    exit;
}

// Ambil data JSON dari body permintaan
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validasi input: pastikan 'email' ada
if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid atau tidak disediakan.']);
    exit;
}

$email = $data['email'];
$domain = substr(strrchr($email, "@"), 1);

// --- SISTEM PENGURANGAN KUOTA & PENCATATAN ---

// Dapatkan total penggunaan hari ini
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT SUM(request_count) as total_usage FROM customer_api_usage WHERE customer_id = ? AND usage_date = ?");
$stmt->execute([$customer['id'], $today]);
$usage_today = $stmt->fetch(PDO::FETCH_ASSOC)['total_usage'] ?? 0;

// Cek jika kuota harian masih mencukupi
if ($usage_today + $cost_per_request > $customer['daily_request_limit']) {
    http_response_code(429); // Too Many Requests
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Batas permintaan harian Anda telah tercapai.',
        'email' => $email,
        'limit' => $customer['daily_request_limit']
    ]);
    exit;
}

// Catat penggunaan API
try {
    $stmt = $db->prepare("
        INSERT INTO customer_api_usage (customer_id, usage_date, request_count)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE request_count = request_count + VALUES(request_count);
    ");
    $stmt->execute([$customer['id'], $today, $cost_per_request]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Gagal mencatat penggunaan API.']);
    exit;
}


// --- FUNGSI UTAMA PENGECEKAN PROVIDER ---

/**
 * Mendeteksi MX records dari sebuah domain.
 * @param string $domain Nama domain.
 * @return array|false Daftar MX records atau false jika gagal.
 */
function detectMxRecords($domain) {
    $mxhosts = [];
    $mxweights = [];

    if (getmxrr($domain, $mxhosts, $mxweights)) {
        $mx_records = [];
        for ($i = 0; $i < count($mxhosts); $i++) {
            $mx_records[] = [
                'host' => $mxhosts[$i],
                'priority' => $mxweights[$i]
            ];
        }
        // Urutkan berdasarkan prioritas (terendah lebih dulu)
        usort($mx_records, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        return $mx_records;
    } else {
        return false;
    }
}

/**
 * Mengidentifikasi penyedia layanan email berdasarkan MX host.
 * @param string $mx_host Host dari MX record.
 * @return string Nama penyedia layanan email.
 */
function identifyEmailProvider($mx_host) {
    $mx_host = strtolower($mx_host);

    if (strpos($mx_host, '.google.com') !== false || strpos($mx_host, 'googlemail.com') !== false) {
        return 'Google Workspace (Gmail, G Suite)';
    } elseif (strpos($mx_host, '.outlook.com') !== false || strpos($mx_host, '.protection.outlook.com') !== false) {
        return 'Microsoft 365 (Outlook, Exchange Online)';
    } elseif (strpos($mx_host, '.emailsrvr.com') !== false) {
        return 'Rackspace Email';
    } elseif (strpos($mx_host, '.secureserver.net') !== false) {
        return 'GoDaddy Mail';
    } elseif (strpos($mx_host, '.zoho.com') !== false) {
        return 'Zoho Mail';
    } elseif (strpos($mx_host, '.yandex.net') !== false) {
        return 'Yandex Mail';
    } elseif (strpos($mx_host, '.protonmail.ch') !== false) {
        return 'Proton Mail';
    } elseif (strpos($mx_host, '.amazonses.com') !== false) {
        return 'Amazon SES (Simple Email Service)';
    } elseif (strpos($mx_host, '.mailgun.org') !== false) {
        return 'Mailgun';
    } elseif (strpos($mx_host, '.sendgrid.net') !== false) {
        return 'SendGrid';
    } elseif (strpos($mx_host, '.mandrillapp.com') !== false) {
        return 'Mandrill (by Mailchimp)';
    } elseif (strpos($mx_host, '.mx.cloudflare.net') !== false) {
        return 'Cloudflare Email Routing';
    } elseif (strpos($mx_host, '.mimecast.com') !== false) {
        return 'Mimecast';
    } elseif (strpos($mx_host, '.proofpoint.com') !== false) {
        return 'Proofpoint';
    } elseif (strpos($mx_host, '.barracudanetworks.com') !== false) {
        return 'Barracuda Networks';
    }
    // Tambahkan rules lain dari kode Anda jika diperlukan
    // ...

    return 'Tidak Diketahui / Lainnya';
}

// --- PROSES DAN OUTPUT ---

$mx_records = detectMxRecords($domain);

if ($mx_records) {
    // Ambil MX record dengan prioritas tertinggi (angka terkecil)
    $primary_mx_host = $mx_records[0]['host'];
    $provider = identifyEmailProvider($primary_mx_host);

    $response = [
        'status' => 'success',
        'email' => $email,
        'domain' => $domain,
        'provider' => $provider,
        'mx_records' => $mx_records
    ];
} else {
    $response = [
        'status' => 'error',
        'message' => 'Tidak dapat menemukan MX records untuk domain ini.',
        'email' => $email,
        'domain' => $domain
    ];
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode($response);
exit;

?>