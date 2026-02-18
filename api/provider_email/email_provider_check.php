<?php
/**
 * File: api_action_check_email.php
 *
 * Deskripsi: Endpoint API yang melakukan dua hal:
 * 1. Menghubungkan ke database.
 * 2. Menggunakan koneksi tersebut untuk menjalankan aksi identifikasi 
 *    penyedia layanan email dan (opsional) mencatat hasilnya.
 *
 * Cara Penggunaan:
 * Panggil file ini dengan parameter GET 'email'.
 * Contoh: /api_action_check_email.php?email=contoh@gmail.com
 */

// Menampilkan semua error untuk debugging (nonaktifkan di produksi)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- BAGIAN 1: KONEKSI DATABASE ---

$db_host = 'localhost'; // atau alamat IP server database Anda
$db_name = 'gandalftools';    // Ganti dengan nama database Anda
$db_user = 'gandalftools';  // Ganti dengan username database Anda
$db_pass = 'gandalftools123';   // Ganti dengan password database Anda
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";

// Opsi untuk koneksi PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Membuat instance PDO (objek koneksi database)
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // Menangani error jika koneksi gagal
    error_log("FATAL: Database connection failed: " . $e->getMessage());
    
    // Memberikan respons error yang aman
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status_code' => 500,
        'message' => 'Tidak dapat terhubung ke database. Silakan coba lagi nanti.'
    ]);
    exit; // Menghentikan eksekusi skrip
}


// --- BAGIAN 2: FUNGSI-FUNGSI LOGIKA ---

/**
 * Fungsi helper untuk mengirim respons JSON yang konsisten.
 *
 * @param int $code Kode status HTTP (e.g., 200, 400, 404, 500).
 * @param string $message Pesan status.
 * @param array|null $data Data tambahan yang akan dikirim.
 */

if (!function_exists('api_response')) {
    /**
     * Sends a JSON response.
     * @param int $status_code
     * @param string $message
     * @param array|null $data
     */
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

/**
 * Mendeteksi MX records untuk sebuah domain.
 * (Fungsi ini sama seperti yang Anda berikan)
 */
function detectMxRecords($domain) {
    $mxhosts = [];
    $mxweights = [];
    if (@getmxrr($domain, $mxhosts, $mxweights)) {
        $mx_records = [];
        for ($i = 0; $i < count($mxhosts); $i++) {
            $mx_records[] = ['host' => $mxhosts[$i], 'priority' => $mxweights[$i]];
        }
        usort($mx_records, function($a, $b) { return $a['priority'] - $b['priority']; });
        return $mx_records;
    }
    return false;
}

/**
 * Mengidentifikasi penyedia layanan email berdasarkan nama host MX.
 * (Fungsi ini sama seperti yang Anda berikan, saya hanya merapikannya sedikit)
 */
function identifyEmailProvider($mx_host) {
    $mx_host = strtolower($mx_host);

    if (strpos($mx_host, '.outlook.com') !== false || strpos($mx_host, '.protection.outlook.com') !== false || strpos($mx_host, '.mail.microsoft.com') !== false || strpos($mx_host, '.spf.messaging.microsoft.com') !== false) {
        return 'Microsoft 365 (Outlook, Exchange Online)';
    } elseif (strpos($mx_host, '.emailsrvr.com') !== false) {
        return 'Rackspace Email';
    } elseif (strpos($mx_host, '.secureserver.net') !== false || strpos($mx_host, '.prod.sinamail.com') !== false || strpos($mx_host, '.hosting.secureserver.net') !== false || strpos($mx_host, '.domaincontrol.com') !== false) {
        return 'GoDaddy Mail';
    } elseif (strpos($mx_host, '.zoho.com') !== false) {
        return 'Zoho Mail';
    } elseif (strpos($mx_host, '.fastmail.com') !== false) {
        return 'Fastmail';
    } elseif (strpos($mx_host, '.yandex.net') !== false || strpos($mx_host, '.yandex.ru') !== false) {
        return 'Yandex Mail';
    } elseif (strpos($mx_host, '.mail.gandi.net') !== false) {
        return 'Gandi.net Mail';
    } elseif (strpos($mx_host, '.protonmail.ch') !== false || strpos($mx_host, '.protonmail.com') !== false) {
        return 'Proton Mail';
    } elseif (strpos($mx_host, '.serverdata.net') !== false || strpos($mx_host, '.register.com') !== false) {
        return 'Register.com / Web.com Mail';
    } elseif (strpos($mx_host, '.mail.dreamhost.com') !== false) {
        return 'DreamHost Mail';
    } elseif (strpos($mx_host, '.tutanota.com') !== false) {
        return 'Tutanota';
    } elseif (strpos($mx_host, '.migadu.com') !== false) {
        return 'Migadu';
    } elseif (strpos($mx_host, '.mail.ovh.net') !== false || strpos($mx_host, '.mx.ovh.com') !== false) {
        return 'OVHcloud Mail';
    } elseif (strpos($mx_host, '.mail.ionos.com') !== false || strpos($mx_host, '.ionos.com') !== false) {
        return 'IONOS Mail (1&1)';
    } elseif (strpos($mx_host, '.mailbox.org') !== false) {
        return 'mailbox.org';
    } elseif (strpos($mx_host, '.posteo.de') !== false) {
        return 'Posteo';
    } elseif (strpos($mx_host, '.riseup.net') !== false) {
        return 'Riseup';
    } elseif (strpos($mx_host, '.runbox.com') !== false) {
        return 'Runbox';
    } elseif (strpos($mx_host, '.mail.ru') !== false) {
        return 'Mail.ru';
    } elseif (strpos($mx_host, '.google.com') !== false || strpos($mx_host, 'googlemail.com') !== false) {
        return 'Google Workspace (Gmail, G Suite)';
    } elseif (strpos($mx_host, '.mailgun.org') !== false) {
        return 'Mailgun (Transactional/Bulk Email)';
    } elseif (strpos($mx_host, '.sendgrid.net') !== false) {
        return 'SendGrid (Transactional/Bulk Email)';
    } elseif (strpos($mx_host, '.smtp.com') !== false) {
        return 'SMTP.com (Transactional/Relay)';
    } elseif (strpos($mx_host, '.mandrillapp.com') !== false) {
        return 'Mandrill (by Mailchimp - Transactional)';
    } elseif (strpos($mx_host, '.amazonses.com') !== false) {
        return 'Amazon SES (Simple Email Service)';
    } elseif (strpos($mx_host, '.sparkpost.com') !== false) {
        return 'SparkPost (Transactional Email)';
    } elseif (strpos($mx_host, '.mailjet.com') !== false) {
        return 'Mailjet (Transactional Email)';
    } elseif (strpos($mx_host, '.plesk.com') !== false) {
        return 'Plesk (Hosting Control Panel)';
    } elseif (strpos($mx_host, '.cpanel.net') !== false || strpos($mx_host, '.hostinger.com') !== false || strpos($mx_host, '.namecheap.com') !== false || strpos($mx_host, '.bluehost.com') !== false || strpos($mx_host, '.sg-host.com') !== false || strpos($mx_host, '.siteground.com') !== false || strpos($mx_host, '.hostgator.com') !== false || strpos($mx_host, '.inmotionhosting.com') !== false || strpos($mx_host, '.a2hosting.com') !== false || strpos($mx_host, '.dreamhost.com') !== false || strpos($mx_host, '.webhostinghub.com') !== false || strpos($mx_host, '.tmdhosting.com') !== false || strpos($mx_host, '.liquidweb.com') !== false || strpos($mx_host, '.krypt.com') !== false) {
        return 'Shared/Cloud Hosting Provider (Generic)';
    } elseif (strpos($mx_host, '.wix.com') !== false || strpos($mx_host, '.parcom.net') !== false) {
        return 'Wix Mail';
    } elseif (strpos($mx_host, '.squarespace.com') !== false) {
        return 'Squarespace Mail';
    } elseif (strpos($mx_host, '.godaddysites.com') !== false) {
        return 'GoDaddy Website Builder Mail';
    } elseif (strpos($mx_host, '.netsol.xion.oxcs.net') !== false || strpos($mx_host, '.netsolmail.net') !== false) {
        return 'Network Solutions';
    } elseif (strpos($mx_host, '.register.it') !== false) {
        return 'Register.it';
    } elseif (strpos($mx_host, '.aruba.it') !== false) {
        return 'Aruba S.p.A.';
    } elseif (strpos($mx_host, '.strato.com') !== false) {
        return 'Strato';
    } elseif (strpos($mx_host, '.webflow.io') !== false) {
        return 'Webflow (likely forwarding)';
    } elseif (strpos($mx_host, '.mx.cloudflare.net') !== false) {
        return 'Cloudflare (DNS/Security Proxy)';
    } elseif (strpos($mx_host, '.spamtitan.com') !== false) {
        return 'SpamTitan (Email Security)';
    } elseif (strpos($mx_host, '.barracudanetworks.com') !== false) {
        return 'Barracuda Networks (Email Security)';
    } elseif (strpos($mx_host, '.mimecast.com') !== false) {
        return 'Mimecast (Email Security/Archiving)';
    } elseif (strpos($mx_host, '.proofpoint.com') !== false) {
        return 'Proofpoint (Email Security)';
    } elseif (strpos($mx_host, '.appriver.com') !== false) {
        return 'AppRiver (Email Security/Hosting)';
    } elseif (strpos($mx_host, '.hornetsecurity.com') !== false) {
        return 'Hornetsecurity (Email Security)';
    } elseif (strpos($mx_host, '.messagelabs.com') !== false) {
        return 'Symantec MessageLabs (Email Security)';
    }

    return 'Tidak Diketahui / Lainnya';
}


// --- BAGIAN 3: LOGIKA UTAMA EKSEKUSI ---

// Ambil parameter 'email' dari URL.
if (!isset($_GET['email']) || empty(trim($_GET['email']))) {
    api_response(400, "Parameter 'email' dibutuhkan.");
}
$email = trim($_GET['email']);

// Validasi format email.
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_response(400, "Format email yang diberikan tidak valid.", ['email' => $email]);
}

// Ekstrak domain dari alamat email.
$domain = substr(strrchr($email, "@"), 1);
if (!$domain) {
     api_response(400, "Tidak dapat mengekstrak domain dari email.", ['email' => $email]);
}

// Coba deteksi MX records.
$mx_records = detectMxRecords($domain);

// Siapkan struktur data untuk respons.
$result_data = [
    'email_checked' => $email,
    'domain' => $domain,
    'provider' => 'Tidak Diketahui',
    'mx_records_found' => false,
    'mx_records' => null,
    'notes' => ''
];

$status_code = 200;
$message = 'Provider tidak dapat diidentifikasi.';

if ($mx_records !== false && !empty($mx_records)) {
    $result_data['mx_records_found'] = true;
    $result_data['mx_records'] = $mx_records;
    $primary_mx_host = $mx_records[0]['host'];
    $result_data['provider'] = identifyEmailProvider($primary_mx_host);
    
    $status_code = 200;
    $message = 'Provider email berhasil diidentifikasi.';

} else {
    $a_record = @dns_get_record($domain, DNS_A);
    if (!empty($a_record)) {
        $result_data['provider'] = 'Tidak Diketahui / Kemungkinan Self-Hosted';
        $result_data['notes'] = 'Tidak ada MX record. Namun, domain memiliki A record, mengindikasikan kemungkinan mail server berada di server utama.';
        $result_data['a_record_ips'] = array_map(fn($rec) => $rec['ip'], $a_record);
        
        $status_code = 200;
        $message = 'Provider tidak dapat diidentifikasi secara pasti (tidak ada MX record).';
    } else {
        $result_data['notes'] = 'Tidak ada MX atau A record yang ditemukan untuk domain ini. Domain kemungkinan besar tidak dapat menerima email.';
        $message = 'Domain tidak ditemukan atau tidak memiliki record email (MX/A).';
    }
}

// --- BAGIAN 4: INTERAKSI DATABASE (CONTOH) ---
// Setelah mendapatkan hasilnya, kita bisa menyimpannya ke database.
// Misalkan Anda punya tabel `email_checks_log`.
// CREATE TABLE email_checks_log (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     email_address VARCHAR(255) NOT NULL,
//     domain_name VARCHAR(255) NOT NULL,
//     provider_name VARCHAR(255),
//     full_response TEXT,
//     checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// );
try {
    $sql = "INSERT INTO email_checks_log (email_address, domain_name, provider_name, full_response) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    // Bind parameter dan eksekusi
    $stmt->execute([
        $result_data['email_checked'],
        $result_data['domain'],
        $result_data['provider'],
        json_encode($result_data) // Simpan seluruh respons sebagai JSON
    ]);
} catch (\PDOException $e) {
    // Jika logging gagal, jangan hentikan permintaan.
    // Cukup catat errornya di log server untuk diperbaiki nanti.
    error_log("DB_LOG_ERROR: Gagal menyimpan hasil cek email ke database: " . $e->getMessage());
}


// --- BAGIAN 5: KIRIM RESPONS FINAL ---
// Kirim respons ke pengguna setelah semua proses selesai.
api_response($status_code, $message, $result_data);

?>