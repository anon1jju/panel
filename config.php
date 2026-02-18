<?php
session_start();

// Ganti detail ini dengan detail koneksi database Anda.
$db_host = 'localhost';
$db_user = 'gandalftools';
$db_pass = 'gandalftools123'; // Biasanya kosong jika menggunakan XAMPP default
$db_name = 'gandalftools'; // Nama database yang sudah kita buat

// Membuat koneksi ke database
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if (!$conn) {
    // Hentikan eksekusi dan tampilkan pesan error jika koneksi gagal
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>