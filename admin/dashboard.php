<?php 
require_once "../config.php";
$currentPage = 'dashboard'; 


?>
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
                border-radius: 10px;
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
                        <h2 class="text-xl font-semibold text-gray-700 ml-4">Dashboard</h2>
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
                
                <main class="flex-1 p-6 overflow-x-hidden overflow-y-auto">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-500 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                        ></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Pengguna</p>
                                    <?php
                                    $sql_customers = "SELECT COUNT(id) AS total_customers FROM customers";
                                    $result_customers = $conn->query($sql_customers);
                                    $total_customers = 0;
                                    if ($result_customers->num_rows > 0) {
                                        $row = $result_customers->fetch_assoc();
                                        $total_customers = $row['total_customers'];
                                    }
                                    ?>
                                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($total_customers); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-500 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Pendapatan</p>
                                    <p class="text-2xl font-bold text-gray-800">Rp 45.7M</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 bg-yellow-500 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                        ></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Pesanan Baru</p>
                                    <p class="text-2xl font-bold text-gray-800">321</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 bg-red-500 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Tiket Support</p>
                                    <p class="text-2xl font-bold text-gray-800">12</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
                        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Grafik Penggunaan API (6 Bulan Terakhir)</h3>
                            <canvas id="salesChart"></canvas>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Aktivitas Terbaru</h3>
                            <ul class="divide-y divide-gray-200">
                                <?php
                                $sql_latest_usage = "SELECT c.customer_name, cau.last_request_at FROM customer_api_usage cau JOIN customers c ON cau.customer_id = c.id ORDER BY cau.last_request_at DESC LIMIT 5";
                                $result_latest_usage = $conn->query($sql_latest_usage);

                                if ($result_latest_usage->num_rows > 0) {
                                    while ($row = $result_latest_usage->fetch_assoc()) {
                                        // Format waktu menjadi "X jam/hari yang lalu"
                                        $time_ago = "";
                                        $last_request_timestamp = strtotime($row['last_request_at']);
                                        $current_timestamp = time();
                                        $diff = $current_timestamp - $last_request_timestamp;

                                        if ($diff < 3600) { // Kurang dari 1 jam
                                            $minutes = round($diff / 60);
                                            $time_ago = $minutes == 0 ? "Baru saja" : $minutes . " menit yang lalu";
                                        } elseif ($diff < 86400) { // Kurang dari 24 jam
                                            $hours = round($diff / 3600);
                                            $time_ago = $hours . " jam yang lalu";
                                        } else { // Lebih dari 24 jam
                                            $days = round($diff / 86400);
                                            $time_ago = $days . " hari yang lalu";
                                        }
                                ?>
                                <li class="py-3">
                                    <div class="text-sm">
                                        <p class="text-gray-800">Pengguna <?php echo htmlspecialchars($row['customer_name']); ?> melakukan pengecekan API.</p>
                                        <p class="text-xs text-gray-500"><?php echo $time_ago; ?></p>
                                    </div>
                                </li>
                                <?php
                                    }
                                } else {
                                    echo '<li class="py-3"><div class="text-sm text-gray-500">Tidak ada aktivitas terbaru.</div></li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-8 bg-white rounded-lg shadow-md">
                        <div class="flex justify-between items-center p-4 border-b">
                            <h3 class="text-lg font-semibold text-gray-700">Daftar Pengguna</h3>
                            <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm">Tambah Pengguna</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">API Key</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Limit Harian</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Dibuat Pada</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                    $sql_customers_list = "SELECT id, customer_name, api_key, daily_request_limit, is_active, created_at FROM customers ORDER BY created_at DESC LIMIT 5";
                                    $result_customers_list = $conn->query($sql_customers_list);

                                    if ($result_customers_list->num_rows > 0) {
                                        while ($row = $result_customers_list->fetch_assoc()) {
                                            $status = $row['is_active'] ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>' : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Nonaktif</span>';
                                            $created_at_formatted = date('d F Y, H:i', strtotime($row['created_at']));
                                    ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-10 h-10"><img class="w-10 h-10 rounded-full" src="https://i.pravatar.cc/150?u=<?php echo $row['id']; ?>" alt="" /></div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['api_key']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo number_format($row['daily_request_limit']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $status; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $created_at_formatted; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="#" class="text-blue-600 hover:text-blue-900">Edit</a><span class="mx-2 text-gray-300">|</span><a href="#" class="text-red-600 hover:text-red-900">Hapus</a>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada pengguna yang terdaftar.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                                <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <p class="text-sm text-gray-700">Menampilkan <span class="font-medium">1</span> sampai <span class="font-medium">3</span> dari <span class="font-medium">15</span> hasil</p>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">&laquo;</a>
                                    <a href="#" aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">1</a>
                                    <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">2</a>
                                    <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">3</a>
                                    <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">&raquo;</a>
                                </nav>
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

                // --- LOGIKA GRAFIK (CHART.JS) ---
                <?php
                // Fetch data for the chart from customer_api_usage
                // Get the last 6 months
                $months = [];
                $usage_data = [];
                for ($i = 5; $i >= 0; $i--) {
                    $month_name = date('F', strtotime("-$i month"));
                    $months[] = $month_name;
                    $start_date = date('Y-m-01', strtotime("-$i month"));
                    $end_date = date('Y-m-t', strtotime("-$i month"));

                    $sql_chart_data = "SELECT SUM(request_count) AS total_requests FROM customer_api_usage WHERE usage_date BETWEEN '$start_date' AND '$end_date'";
                    $result_chart_data = $conn->query($sql_chart_data);
                    if ($result_chart_data->num_rows > 0) {
                        $row_chart = $result_chart_data->fetch_assoc();
                        $usage_data[] = $row_chart['total_requests'] ? $row_chart['total_requests'] : 0;
                    } else {
                        $usage_data[] = 0;
                    }
                }
                $conn->close(); // Close the database connection
                ?>
                const ctx = document.getElementById("salesChart").getContext("2d");
                new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: <?php echo json_encode($months); ?>,
                        datasets: [
                            {
                                label: "Total Permintaan API",
                                data: <?php echo json_encode($usage_data); ?>,
                                borderColor: "rgb(59, 130, 246)",
                                backgroundColor: "rgba(59, 130, 246, 0.1)",
                                tension: 0.4,
                                fill: true,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                            },
                        },
                    },
                });
            });
        </script>
    </body>
</html>
