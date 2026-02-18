<?php


// Panggil file konfigurasi database
require_once '../config.php'; // Sesuaikan path jika perlu

// Cek apakah data dikirim melalui metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil username dan password dari form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi dasar: pastikan input tidak kosong
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username dan Password tidak boleh kosong.";
        header("Location: ../signin.php");
        exit();
    }

    // Gunakan prepared statement untuk mencari user berdasarkan username
    $sql = "SELECT id, username, password FROM admins WHERE username = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            // Cek apakah user ditemukan
            if (mysqli_num_rows($result) == 1) {
                $admin = mysqli_fetch_assoc($result);
                
                // Verifikasi password yang diinput dengan hash di database
                if (password_verify($password, $admin['password'])) {
                    // Password cocok! Buat sesi untuk user.
                    
                    // Regenerasi ID sesi untuk keamanan
                    session_regenerate_id(true);
                    
                    // Simpan informasi penting ke dalam sesi
                    $_SESSION['is_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    // Arahkan ke halaman dashboard
                    header("Location: dashboard.php");
                    exit();
                    
                } else {
                    // Password tidak cocok
                    $_SESSION['login_error'] = "Username atau password salah.";
                    header("Location: ../signin.php");
                    exit();
                }
            } else {
                // User tidak ditemukan
                $_SESSION['login_error'] = "Username atau password salah.";
                header("Location: ../signin.php");
                exit();
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);

} else {
    // Jika halaman diakses langsung tanpa POST, kembalikan ke login
    header("Location: ../signin.php");
    exit();
}
?>