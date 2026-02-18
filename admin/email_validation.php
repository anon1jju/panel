<?php
require_once '../config.php'; // Sesuaikan path jika perlu

// Query untuk tabel pertama (Customers)
$sql_customers = "
    SELECT 
        c.id, 
        c.api_key, 
        c.customer_name,
        c.email, 
        c.daily_request_limit, 
        c.is_active, 
        c.created_at,
        c.email_check_server,
        COALESCE(cau.request_count, 0) AS todays_usage
    FROM 
        customers c
    LEFT JOIN 
        customer_api_usage cau ON c.id = cau.customer_id AND cau.usage_date = CURDATE()
    ORDER BY 
        c.created_at DESC";
$result_customers = mysqli_query($conn, $sql_customers);

// --- [DIPERBARUI] ---
// Query untuk tabel kedua (customer_api_usage) dengan JOIN
$sql_usage = "
    SELECT 
        cau.usage_date, 
        cau.request_count,
        cau.last_request_at,
        c.customer_name 
    FROM 
        customer_api_usage cau
    JOIN 
        customers c ON cau.customer_id = c.id
    ORDER BY 
        cau.usage_date DESC, cau.last_request_at DESC
    LIMIT 20"; // Batasi untuk menampilkan 20 log terbaru
$result_usage = mysqli_query($conn, $sql_usage);

?>
<?php $currentPage = 'Email Validation'; ?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Email Validation - Admin Dashboard</title>

        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <style>
            body {
                font-family: "Nunito", sans-serif;
            }
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
                        <h2 class="text-xl font-semibold text-gray-700 ml-4">Email Validator</h2>
                    </div>
                    <div class="flex items-center space-x-4" x-data="{ notificationOpen: false, profileOpen: false }">
                        <div class="relative">
                            <button @click="notificationOpen = !notificationOpen" class="relative text-gray-600 hover:text-gray-800 focus:outline-none">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <div class="divide-y divide-gray-100"><a href="#" class="block px-4 py-3 text-sm text-gray-600 hover:bg-gray-100">Pesanan baru #INV-123 masuk.</a></div>
                            </div>
                        </div>
                        <div class="relative">
                            <button @click="profileOpen = !profileOpen" class="flex items-center space-x-2 focus:outline-none">
                                <img src="https://ui-avatars.com/api/?name=Admin+User&background=313a46&color=fff" alt="Avatar" class="w-10 h-10 rounded-full" /><span class="hidden md:block font-medium text-gray-700">Admin User</span>
                            </button>
                            <div x-show="profileOpen" @click.away="profileOpen = false" x-cloak class="absolute right-0 w-48 mt-2 py-2 bg-white border rounded-lg shadow-xl">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-500 hover:text-white">Profil Saya</a>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-500 hover:text-white">Logout</a>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="flex-1 p-6 overflow-x-hidden overflow-y-auto">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">API Keys</h1>
                        <p class="text-gray-600 mt-1">Kelola data customer, API Key, dan limit request di sini.</p>
                    </div>
                    <a href="tambah_user.php" class="mt-4 sm:mt-0 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <span>Tambah User Baru</span>
                    </a>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto overflow-y-auto max-h-[60vh]">
        <table class="w-full min-w-full">
            <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                <tr>
                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Customer</th>
                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">API Key</th>
                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Server</th>
                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Penggunaan Hari Ini</th>
                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Tanggal Dibuat</th>
                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
    <?php
        // Pemetaan dari nama file ke nama yang mudah dibaca
        $server_display_names = [
            'check.php' => 'Server 1 (Utama)',
            'check2.php' => 'Server 2 (Alternatif)',
            'check3.php' => 'Server 3 (Cadangan)'
        ];
    ?>
    <?php if (mysqli_num_rows($result_customers) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result_customers)): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['customer_name']); ?></div><div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['email']); ?></div></td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-500"><?php echo substr($row['api_key'], 0, 6) . '...' . substr($row['api_key'], -6); ?></td>
                <td class="px-6 py-4 whitespace-nowrap"><?php if ($row['is_active'] == 1): ?><span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">Aktif</span><?php else: ?><span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">Nonaktif</span><?php endif; ?></td>
                
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <?php
                        $server_file = $row['email_check_server'];
                        echo $server_display_names[$server_file] ?? htmlspecialchars($server_file);
                    ?>
                </td>
                
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <?php
                        $limit = $row['daily_request_limit'];
                        $usage = $row['todays_usage'];
                        $percentage = ($limit > 0) ? ($usage / $limit) * 100 : 0;
                        $bar_color = 'bg-green-500';
                        if ($percentage > 75) { $bar_color = 'bg-yellow-500'; }
                        if ($percentage >= 100) { $bar_color = 'bg-red-500'; $percentage = 100; }
                    ?>
                    <div class="flex items-center">
                        <div class="w-2/3">
                            <div class="text-gray-800 font-semibold"><?php echo number_format($usage); ?> / <?php echo number_format($limit); ?></div>
                        
                        </div>
                        
                    </div>
                </td>


                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date("d F Y", strtotime($row['created_at'])); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"><a href="edit_user.php?id=<?php echo $row['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a><span class="mx-2 text-gray-300">|</span><a href="#" data-url="hapus_user.php?id=<?php echo $row['id']; ?>" class="delete-button text-red-600 hover:text-red-900">Hapus</a></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="8" class="px-6 py-10 text-center text-gray-500">Tidak ada data customer yang ditemukan.</td></tr>
    <?php endif; ?>
</tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="text-xl font-bold text-slate-800">Log Penggunaan API Harian Terbaru</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Nama Customer</th>
                                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Tanggal Penggunaan</th>
                                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Jumlah Request</th>
                                    <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Request Terakhir pada</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (mysqli_num_rows($result_usage) > 0): ?>
                                    <?php while($row_usage = mysqli_fetch_assoc($result_usage)): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($row_usage['customer_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date("d F Y", strtotime($row_usage['usage_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold">
                                                <?php echo number_format($row_usage['request_count']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date("H:i:s", strtotime($row_usage['last_request_at'])); ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                            Tidak ada data penggunaan (usage) yang ditemukan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                    <footer class="mt-8 text-center text-sm text-gray-500">
                        &copy; 2025 Admin Panel. Semua Hak Cipta Dilindungi.
                    </footer>
                </main>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const sidebar = document.getElementById("sidebar");
                const mainContent = document.getElementById("main-content");
                const sidebarToggle = document.getElementById("sidebar-toggle");
                let isSidebarOpen = window.innerWidth >= 1024;
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
                updateSidebar();
                window.addEventListener("resize", () => {
                    if (window.innerWidth < 1024 && isSidebarOpen) {
                        isSidebarOpen = false;
                        updateSidebar();
                    } else if (window.innerWidth >= 1024 && !isSidebarOpen) {
                        isSidebarOpen = true;
                        updateSidebar();
                    }
                });
            });

            // ==========================================================
            // [TAMBAHKAN INI] LOGIKA UNTUK SWEETALERT SAAT HAPUS
            // ==========================================================
            const deleteButtons = document.querySelectorAll(".delete-button");

            deleteButtons.forEach((button) => {
                button.addEventListener("click", function (event) {
                    event.preventDefault(); // Mencegah link berjalan secara default

                    const deleteUrl = this.getAttribute("data-url");

                    Swal.fire({
                        title: "Apakah Anda yakin?",
                        text: "Data yang akan dihapus tidak dapat dikembalikan!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Batal",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Jika user mengklik "Ya, hapus!", arahkan ke URL penghapusan
                            window.location.href = deleteUrl;
                        }
                    });
                });
            });
        </script>
    </body>
</html>
<?php
// Menutup kedua koneksi hasil query
if (isset($result_customers)) mysqli_free_result($result_customers);
if (isset($result_usage)) mysqli_free_result($result_usage);
mysqli_close($conn);
?>
