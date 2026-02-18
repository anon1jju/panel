<?php
// Untuk Debugging (HAPUS ATAU KOMENTARI DI PRODUKSI)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Panggil file konfigurasi database Anda (yang menggunakan MySQLi)
require_once __DIR__ . '/../config.php'; // Path ini harus benar

// Fungsi helper untuk response (tetap sama)
function api_response($status_code, $message, $data = null) {
    http_response_code($status_code);
    $response = ['message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// ==========================================================
// [BARU] 0. CEK MODE MAINTENANCE
// ==========================================================
// Ambil semua pengaturan dari database
$settings = [];
$sql_settings = "SELECT setting_key, setting_value FROM settings";
$result_settings = mysqli_query($conn, $sql_settings);
if ($result_settings) {
    while ($row = mysqli_fetch_assoc($result_settings)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Cek jika maintenance mode aktif
if (isset($settings['maintenance_mode_status']) && $settings['maintenance_mode_status'] == 1) {
    // Kirim response 503 Service Unavailable dan hentikan eksekusi
    $maintenance_message = $settings['maintenance_mode_message'] ?? 'Maintenance is in progress';
    api_response(503, $maintenance_message);
}


// ==========================================================
// 1. VALIDASI INPUT DASAR
// ==========================================================
if (!isset($_GET['customer_api_key']) || empty(trim($_GET['customer_api_key']))) {
    api_response(401, "API Key is required.");
}
if (!isset($_GET['action']) || empty(trim($_GET['action']))) {
    api_response(400, "Parameter 'action' is required.");
}

$customerApiKey = trim($_GET['customer_api_key']);
$action = trim($_GET['action']);


// ==========================================================
// 2. VALIDASI CUSTOMER & PENGATURANNYA (MENGGUNAKAN MySQLi)
// ==========================================================
$sql_customer = "SELECT id, daily_request_limit, is_active, email_check_server FROM customers WHERE api_key = ?";
$stmt_customer = mysqli_prepare($conn, $sql_customer);
mysqli_stmt_bind_param($stmt_customer, "s", $customerApiKey);
mysqli_stmt_execute($stmt_customer);
$result_customer = mysqli_stmt_get_result($stmt_customer);

if (mysqli_num_rows($result_customer) == 0) {
    api_response(403, 'API Key invalid.');
}

$customer = mysqli_fetch_assoc($result_customer);
mysqli_stmt_close($stmt_customer);

if ($customer['is_active'] != 1) {
    api_response(403, 'Your account is inactive.');
}

$customerId = $customer['id'];
$dailyLimit = $customer['daily_request_limit'];
$server_script = $customer['email_check_server'];


// ==========================================================
// 3. CEK LIMIT PENGGUNAAN (MENGGUNAKAN MySQLi)
// ==========================================================
$today = date('Y-m-d');
$sql_usage = "SELECT request_count FROM customer_api_usage WHERE customer_id = ? AND usage_date = ?";
$stmt_usage = mysqli_prepare($conn, $sql_usage);
mysqli_stmt_bind_param($stmt_usage, "is", $customerId, $today);
mysqli_stmt_execute($stmt_usage);
$result_usage = mysqli_stmt_get_result($stmt_usage);
$usage_today = mysqli_fetch_assoc($result_usage);
mysqli_stmt_close($stmt_usage);

if ($usage_today && $usage_today['request_count'] >= $dailyLimit) {
    // [BARU] Hitung waktu hingga reset limit (pukul 00:00 besok)
    $now = new DateTime('now', new DateTimeZone('Asia/Jakarta')); // Sesuaikan dengan zona waktu server Anda
    $tomorrow = new DateTime('tomorrow', new DateTimeZone('Asia/Jakarta')); // Reset pada tengah malam
    $time_until_reset = $now->diff($tomorrow);
    
    // Format sisa waktu menjadi jam, menit, dan detik
    $timeLeft = $time_until_reset->format('%h hours, %i minutes, dan %s seconds');

    // Buat pesan yang lebih informatif
    $message = sprintf(
        'Your daily limit (%d) has been reached. Please try again in %s. (Timezone use : Asia/Bangkok)',
        $dailyLimit,
        $timeLeft
    );
    
    api_response(429, $message);
}


// ==========================================================
// 4. CATAT PENGGUNAAN (MENGGUNAKAN MySQLi)
// ==========================================================
// Query ini sekarang menggunakan ON DUPLICATE KEY UPDATE untuk menghindari error jika data sudah ada
$sql_track = "
    INSERT INTO customer_api_usage (customer_id, usage_date, request_count) 
    VALUES (?, ?, 1)
    ON DUPLICATE KEY UPDATE request_count = request_count + 1";
$stmt_track = mysqli_prepare($conn, $sql_track);
mysqli_stmt_bind_param($stmt_track, "is", $customerId, $today);
mysqli_stmt_execute($stmt_track);
mysqli_stmt_close($stmt_track);


// ==========================================================
// 5. ROUTING (PANGGIL SKRIP AKSI YANG TEPAT)
// ==========================================================
switch ($action) {
    case 'email_check':
        $script_path = __DIR__ . '/actions/' . $server_script;
        if (file_exists($script_path)) {
            // $conn (koneksi mysqli) dan variabel lain bisa diakses di file yang di-include
            require_once $script_path;
        } else {
            api_response(500, 'Error: Server pengecekan tidak dikonfigurasi dengan benar untuk akun Anda.');
        }
        break;
    
    case 'email_provider_check':
        $script_path = __DIR__ . '/provider_email/' . $server_script;
        if (file_exists($script_path)) {
            require_once $script_path;
        } else {
            api_response(500, 'Error: File untuk aksi email_provider_check tidak ditemukan.');
        }
        break;
        
    case 'card_check':
        $script_path = __DIR__ . '/card/' . $server_script;
        if (file_exists($script_path)) {
            require_once $script_path;
        } else {
            api_response(500, 'Error: File untuk aksi card check tidak ditemukan.');
        }
        break;

    default:
        api_response(400, 'Aksi tidak valid atau tidak ditemukan.');
        break;
}

mysqli_close($conn);
?>