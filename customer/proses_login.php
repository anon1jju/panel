<?php
// Memasukkan file koneksi database
require_once '../config.php';

// Memeriksa apakah data form telah dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $api_key = $_POST['api_key'];

    // Mencegah SQL Injection dengan prepared statements
    $stmt = $conn->prepare("SELECT id, customer_name, api_key, is_active FROM customers WHERE customer_name = ? AND api_key = ?");
    
    // -- PERBAIKAN: Menambahkan pengecekan error setelah prepare --
    if ($stmt === false) {
        // Jika prepare gagal, hentikan eksekusi dan tampilkan error dari database
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $customer_name, $api_key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $customer = $result->fetch_assoc();

        // Memeriksa apakah akun aktif
        if ($customer['is_active'] == 1) {
            // Jika kredensial valid dan akun aktif, mulai session
            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['customer_name'];
            $_SESSION['api_key'] = $customer['api_key'];

            // Arahkan ke dashboard customer
            header("Location: dashboard.php");
            exit;
        } else {
            // Jika akun tidak aktif
            header("Location: login.php?error=Akun Anda tidak aktif.");
            exit;
        }
    } else {
        // Jika kredensial tidak valid
        header("Location: login.php?error=Customer Name atau API Key salah.");
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    // Jika halaman diakses langsung, arahkan ke halaman login
    header("Location: login.php");
    exit;
}
?>