<?php
// Memanggil file konfigurasi database
require_once '../config.php';

$pesan_sukses = '';
$pesan_error = '';

// Cek apakah form telah di-submit (metode POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Ambil data dari form dan bersihkan
    $customer_name = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
    
    // --- [DIUBAH] Logika untuk menangani email kosong ---
    // Jika email diisi, bersihkan. Jika tidak, set sebagai NULL.
    $email_input = trim($_POST['email']);
    $email = !empty($email_input) ? mysqli_real_escape_string($conn, $email_input) : NULL;

    // Ambil service_type dari form
    $service_type = mysqli_real_escape_string($conn, trim($_POST['service_type']));

    $daily_request_limit = (int)$_POST['daily_request_limit'];
    $is_active = (int)$_POST['is_active'];
    $email_check_server = mysqli_real_escape_string($conn, $_POST['email_check_server']);

    // 2. Generate API Key unik secara otomatis
    $api_key = bin2hex(random_bytes(8));

    // --- [DIUBAH] Validasi sekarang hanya memeriksa customer_name ---
    if (!empty($customer_name)) {

        // 4. Gunakan Prepared Statements untuk keamanan, tambahkan service_type
        $sql = "INSERT INTO customers (api_key, customer_name, email, service_type, daily_request_limit, is_active, email_check_server) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // 5. Bind variabel ke prepared statement sebagai parameter (tambahkan 's' untuk service_type)
            mysqli_stmt_bind_param($stmt, "ssssiss", $api_key, $customer_name, $email, $service_type, $daily_request_limit, $is_active, $email_check_server);
            
            // 6. Eksekusi statement
            if (mysqli_stmt_execute($stmt)) {
                // Jika berhasil, alihkan kembali ke halaman utama
                header("Location: users.php?status=sukses_tambah");
                exit();
            } else {
                $pesan_error = "Error: Gagal menyimpan data. " . mysqli_stmt_error($stmt);
            }
            
            // 7. Tutup statement
            mysqli_stmt_close($stmt);
        } else {
            $pesan_error = "Error: Gagal menyiapkan query. " . mysqli_error($conn);
        }

    } else {
        // --- [DIUBAH] Pesan error disesuaikan ---
        $pesan_error = "Nama Customer tidak boleh kosong.";
    }
}

// 8. Tutup koneksi database
mysqli_close($conn);
?>
<?php $currentPage = 'Manajemen User'; ?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Admin Dashboard</title>

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
                        <h2 class="text-xl font-semibold text-gray-700 ml-4">Tambah User</h2>
                    </div>

                    <div class="flex items-center space-x-4" x-data="{ notificationOpen: false, profileOpen: false }">
                        <div class="relative">
                            <button @click="notificationOpen = !notificationOpen" class="relative text-gray-600 hover:text-gray-800 focus:outline-none">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
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
                    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
                    <h1 class="text-2xl font-bold text-slate-800 mb-6">Formulir Customer Baru</h1>

                    <?php if(!empty($pesan_error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline"><?php echo $pesan_error; ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="tambah_user.php" method="POST">
                        <div class="mb-4">
                            <label for="customer_name" class="block text-gray-700 text-sm font-bold mb-2">Nama Customer:</label>
                            <input type="text" id="customer_name" name="customer_name" required
                                   class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                            <input type="text" id="email" name="email"
                                   class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label for="service_type" class="block text-gray-700 text-sm font-bold mb-2">Tipe Layanan:</label>
                            <select id="service_type" name="service_type"
                                    class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="email_bounce">Email Bounce</option>
                                <option value="email_provider">Email Provider</option>
                                <option value="card_check">Card Check</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="daily_request_limit" class="block text-gray-700 text-sm font-bold mb-2">Limit Request Harian:</label>
                            <input type="number" id="daily_request_limit" name="daily_request_limit" value="15000"
                                   class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="mb-6">
                            <label for="is_active" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                            <select id="is_active" name="is_active"
                                    class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="1" selected>Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label for="email_check_server" class="block text-gray-700 text-sm font-bold mb-2">Server Pengecekan Email:</label>
                            <select id="email_check_server" name="email_check_server"
                                    class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none">
                                <option value="check.php">Server 1 (Utama)</option>
                                <option value="check2.php">Server 2 (Alternatif)</option>
                                <option value="check3.php">Server 3 (Cadangan)</option>
                                <option value="check4.php">Server 4 (Cadangan)</option>
                                <option value="check5.php">Server 5 (Cadangan)</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-end">
                            <a href="users.php" class="text-gray-600 hover:text-gray-800 font-medium mr-4">Batal</a>
                            <button type="submit"
                                    class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 focus:outline-none focus:shadow-outline">
                                Simpan User
                            </button>
                        </div>
                    </form>
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
                // --- LOGIKA FORM DINAMIS ---
                const serviceTypeSelect = document.getElementById("service_type");
                const emailCheckServerSelect = document.getElementById("email_check_server");

                const serverOptions = {
                    email_bounce: [
                        { value: 'check.php', text: 'Server 1 (Utama)' },
                        { value: 'check2.php', text: 'Server 2 (Alternatif)' },
                        { value: 'check3.php', text: 'Server 3 (Cadangan)' },
                        { value: 'check4.php', text: 'Server 4 (Cadangan)' },
                        { value: 'check5.php', text: 'Server 5 (Cadangan)' }
                    ],
                    email_provider: [
                        { value: 'email_provider_check.php', text: 'Email Provider Check (Server 1)' }
                    ],
                    card_check: [
                        { value: 'card.php', text: 'Card (Server 1)' }
                    ]
                };

                serviceTypeSelect.addEventListener('change', (event) => {
                    const selectedType = event.target.value;
                    
                    // Kosongkan select server
                    emailCheckServerSelect.innerHTML = '';

                    // Isi ulang berdasarkan pilihan
                    if (serverOptions[selectedType]) {
                        serverOptions[selectedType].forEach(optionData => {
                            const option = document.createElement('option');
                            option.value = optionData.value;
                            option.textContent = optionData.text;
                            emailCheckServerSelect.appendChild(option);
                        });
                    }
                });

                
            });
        </script>
    </body>
</html>