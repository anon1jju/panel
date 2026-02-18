<?php
// Memanggil file konfigurasi database
require_once '../config.php';

// Cek apakah 'id' ada di URL dan merupakan angka
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Query DELETE dengan prepared statement untuk keamanan
    $sql = "DELETE FROM customers WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Bind ID user ke statement
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        // Eksekusi statement
        if (mysqli_stmt_execute($stmt)) {
            // Jika berhasil, redirect ke halaman utama dengan pesan sukses
            header("Location: email_validation.php?status=sukses_hapus");
            exit();
        } else {
            // Jika gagal, redirect dengan pesan error
            header("Location: email_validation.php?status=gagal_hapus");
            exit();
        }
        
        // Tutup statement
        mysqli_stmt_close($stmt);
    } else {
         // Gagal menyiapkan query
         header("Location: email_validation.php?status=gagal_query");
         exit();
    }
} else {
    // Jika ID tidak valid atau tidak ada, redirect ke halaman utama
    header("Location: email_validation.php");
    exit();
}

// Tutup koneksi
mysqli_close($conn);
?>