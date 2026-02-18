<?php
//session_start(); // Diperlukan untuk menyimpan data admin yang login
require_once '../config.php'; // Sesuaikan path jika perlu

// Definisikan halaman saat ini untuk sidebar
$currentPage = 'settings';

$pesan_sukses = '';
$pesan_error = '';

// --- Logika untuk memproses form "Pengaturan Umum" ---
if (isset($_POST['simpan_pengaturan_umum'])) {
    $app_name = mysqli_real_escape_string($conn, $_POST['app_name']);
    $default_limit = (int)$_POST['default_api_limit'];

    // Query UPDATE untuk setiap pengaturan
    $sql1 = "UPDATE settings SET setting_value = ? WHERE setting_key = 'app_name'";
    $stmt1 = mysqli_prepare($conn, $sql1);
    mysqli_stmt_bind_param($stmt1, "s", $app_name);
    mysqli_stmt_execute($stmt1);

    $sql2 = "UPDATE settings SET setting_value = ? WHERE setting_key = 'default_api_limit'";
    $stmt2 = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($stmt2, "i", $default_limit);
    mysqli_stmt_execute($stmt2);

    $pesan_sukses = "Pengaturan umum berhasil diperbarui!";
}

// --- Logika untuk memproses form "Ubah Password" ---
if (isset($_POST['simpan_password'])) {
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (empty($password_baru) || empty($konfirmasi_password)) {
        $pesan_error = "Semua kolom password harus diisi.";
    } elseif ($password_baru !== $konfirmasi_password) {
        $pesan_error = "Password baru dan konfirmasi tidak cocok.";
    } elseif (strlen($password_baru) < 8) {
        $pesan_error = "Password baru harus minimal 8 karakter.";
    } else {
        // Hash password baru sebelum disimpan untuk keamanan
        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
        
        // Asumsikan kita mengupdate admin dengan id=1
        $admin_id = 1; 
        
        $sql_pass = "UPDATE admins SET password = ? WHERE id = ?";
        
        $stmt_pass = mysqli_prepare($conn, $sql_pass);
        
        if ($stmt_pass === false) {
            $pesan_error = "Error: Gagal menyiapkan query password. " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt_pass, "si", $hashed_password, $admin_id);
            
            if (mysqli_stmt_execute($stmt_pass)) {
                 $pesan_sukses = "Password berhasil diubah!";
            } else {
                 $pesan_error = "Gagal mengubah password: " . mysqli_stmt_error($stmt_pass);
            }
            mysqli_stmt_close($stmt_pass);
        }
    }
}

// --- [BARU] Logika untuk memproses form "Mode Maintenance" ---
if (isset($_POST['simpan_maintenance'])) {
    $maintenance_status = (int)$_POST['maintenance_status'];
    $maintenance_message = mysqli_real_escape_string($conn, $_POST['maintenance_message']);

    // Query UPDATE untuk status
    $sql_maint_status = "UPDATE settings SET setting_value = ? WHERE setting_key = 'maintenance_mode_status'";
    $stmt_maint_status = mysqli_prepare($conn, $sql_maint_status);
    mysqli_stmt_bind_param($stmt_maint_status, "i", $maintenance_status);
    mysqli_stmt_execute($stmt_maint_status);
    mysqli_stmt_close($stmt_maint_status);

    // Query UPDATE untuk pesan
    $sql_maint_msg = "UPDATE settings SET setting_value = ? WHERE setting_key = 'maintenance_mode_message'";
    $stmt_maint_msg = mysqli_prepare($conn, $sql_maint_msg);
    mysqli_stmt_bind_param($stmt_maint_msg, "s", $maintenance_message);
    mysqli_stmt_execute($stmt_maint_msg);
    mysqli_stmt_close($stmt_maint_msg);

    $pesan_sukses = "Pengaturan mode maintenance berhasil diperbarui!";
}


// --- Mengambil data pengaturan terkini dari database untuk ditampilkan di form ---
$pengaturan = [];
$sql_get_settings = "SELECT setting_key, setting_value FROM settings";
$result_settings = mysqli_query($conn, $sql_get_settings);
while ($row = mysqli_fetch_assoc($result_settings)) {
    $pengaturan[$row['setting_key']] = $row['setting_value'];
}

?>

<?php $currentPage = 'Pengaturan'; ?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Pengaturan - Admin Dashboard</title>

        <script src="https://cdn.tailwindcss.com"></script>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <style>
            body {
                font-family: "Nunito", sans-serif;
            }
            /* Style untuk scrollbar yang lebih halus */
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }
            ::-webkit-scrollbar-track {
                background: #f1f1f1;
            }
            ::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 10px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
        </style>
    </head>
    <body class="bg-gray-100">
        <div class="flex h-screen bg-gray-200">
            <?php include "sidebar.php"; ?>

            <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300 ease-in-out ml-0 lg:ml-64" id="main-content">
                <header class="flex items-center justify-between p-4 bg-white border-b sticky top-0 z-30">
                    <div class="flex items-center">
                        <button id="sidebar-toggle" class="text-gray-500 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <h2 class="text-xl font-semibold text-gray-700 ml-4">Pengaturan</h2>
                    </div>

                    <div class="flex items-center space-x-4" x-data="{ notificationOpen: false, profileOpen: false }">
                        <div class="relative">
                            <button @click="notificationOpen = !notificationOpen" class="relative text-gray-600 hover:text-gray-800 focus:outline-none">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.442l-1.405 1.405H5a2 2 0 00-2 2v2h16v-2a2 2 0 00-2-2z"
                                    ></path>
                                </svg>
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">3</span>
                            </button>
                            <div x-show="notificationOpen" @click.away="notificationOpen = false" x-cloak class="absolute right-0 w-80 mt-2 py-2 bg-white border rounded-lg shadow-xl">
                                <div class="px-4 py-2 text-sm font-bold text-gray-700">Notifikasi</div>
                                <div class="divide-y divide-gray-100">
                                    <a href="#" class="block px-4 py-3 text-sm text-gray-600 hover:bg-gray-100">Pesanan baru #INV-123 masuk.</a>
                                    <a href="#" class="block px-4 py-3 text-sm text-gray-600 hover:bg-gray-100">Stok produk "Kemeja" menipis.</a>
                                    <a href="#" class="block px-4 py-3 text-sm text-gray-600 hover:bg-gray-100">Pengguna "Budi" baru saja mendaftar.</a>
                                </div>
                            </div>
                        </div>
                        <?php include "profile.php"; ?>
                    </div>
                </header>
                
               <!-- ISI KONTEN -->
                <main class="flex-1 p-6 overflow-x-hidden overflow-y-auto">
                    <h1 class="text-2xl font-bold text-slate-800 mb-6">Pengaturan</h1>
                
                <?php if(!empty($pesan_sukses)): ?>
                    <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800 border border-green-300"><?php echo $pesan_sukses; ?></div>
                <?php endif; ?>
                <?php if(!empty($pesan_error)): ?>
                    <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 border border-red-300"><?php echo $pesan_error; ?></div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Kolom Kiri -->
                    <div>
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h2 class="text-xl font-bold text-slate-700 mb-4">Pengaturan Umum</h2>
                            <form action="pengaturan.php" method="POST">
                                <div class="mb-4">
                                    <label for="app_name" class="block text-gray-700 text-sm font-bold mb-2">Nama Aplikasi:</label>
                                    <input type="text" id="app_name" name="app_name" value="<?php echo htmlspecialchars($pengaturan['app_name'] ?? ''); ?>"
                                           class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none">
                                </div>
                                <div class="mb-6">
                                    <label for="default_api_limit" class="block text-gray-700 text-sm font-bold mb-2">Default API Limit User Baru:</label>
                                    <input type="number" id="default_api_limit" name="default_api_limit" value="<?php echo htmlspecialchars($pengaturan['default_api_limit'] ?? '15000'); ?>"
                                           class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none">
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" name="simpan_pengaturan_umum" class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                            <h2 class="text-xl font-bold text-slate-700 mb-4">Ubah Password</h2>
                             <form action="pengaturan.php" method="POST">
                                <div class="mb-4">
                                    <label for="password_baru" class="block text-gray-700 text-sm font-bold mb-2">Password Baru:</label>
                                    <input type="password" id="password_baru" name="password_baru" class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none">
                                </div>
                                <div class="mb-6">
                                    <label for="konfirmasi_password" class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Password Baru:</label>
                                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none">
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" name="simpan_password" class="px-6 py-2.5 bg-slate-800 text-white font-bold rounded-lg hover:bg-slate-700">Ubah Password</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div>
                        <!-- [BARU] KARTU UNTUK MODE MAINTENANCE -->
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h2 class="text-xl font-bold text-slate-700 mb-4">Mode Maintenance API</h2>
                            <form action="pengaturan.php" method="POST">
                                <div class="mb-4">
                                    <label for="maintenance_status" class="block text-gray-700 text-sm font-bold mb-2">Status API:</label>
                                    <select id="maintenance_status" name="maintenance_status"
                                            class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none">
                                        <option value="0" <?php if(isset($pengaturan['maintenance_mode_status']) && $pengaturan['maintenance_mode_status'] == 0) echo 'selected'; ?>>Aktif (Berjalan Normal)</option>
                                        <option value="1" <?php if(isset($pengaturan['maintenance_mode_status']) && $pengaturan['maintenance_mode_status'] == 1) echo 'selected'; ?>>Maintenance (Tidak Aktif)</option>
                                    </select>
                                </div>
                                <div class="mb-6">
                                    <label for="maintenance_message" class="block text-gray-700 text-sm font-bold mb-2">Pesan Maintenance:</label>
                                    <textarea id="maintenance_message" name="maintenance_message" rows="3"
                                              class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none"><?php echo htmlspecialchars($pengaturan['maintenance_mode_message'] ?? ''); ?></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Pesan ini akan ditampilkan di API jika mode maintenance aktif.</p>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" name="simpan_maintenance" class="px-6 py-2.5 bg-orange-600 text-white font-bold rounded-lg hover:bg-orange-700">Simpan Pengaturan Maintenance</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


                    <footer class="mt-8 text-center text-sm text-gray-500">
                        &copy; 2025 Gandalf
                    </footer>
                </main>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                // --- LOGIKA TOGGLE SIDEBAR ---
                const sidebar = document.getElementById("sidebar");
                const mainContent = document.getElementById("main-content");
                const sidebarToggle = document.getElementById("sidebar-toggle");
                let isSidebarOpen = window.innerWidth >= 1024; // lg breakpoint

                const updateSidebar = () => {
                    if (isSidebarOpen) {
                        sidebar.classList.remove("-translate-x-full");
                        mainContent.classList.add("lg:ml-64");
                        mainContent.classList.remove("ml-0");
                    } else {
                        sidebar.classList.add("-translate-x-full");
                        mainContent.classList.remove("lg:ml-64");
                        mainContent.classList.add("ml-0");
                    }
                };

                sidebarToggle.addEventListener("click", () => {
                    isSidebarOpen = !isSidebarOpen;
                    updateSidebar();
                });

                // Inisialisasi posisi sidebar saat load
                updateSidebar();
                window.addEventListener("resize", () => {
                    // Untuk handle responsive jika user mengubah ukuran window
                    if (window.innerWidth < 1024 && isSidebarOpen) {
                        isSidebarOpen = false;
                        updateSidebar();
                    } else if (window.innerWidth >= 1024 && !isSidebarOpen) {
                        isSidebarOpen = true;
                        updateSidebar();
                    }
                });

                
            });
        </script>
    </body>
</html>