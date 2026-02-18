<?php
// Mulai session untuk menangani pesan notifikasi dan status login admin
//session_start();

// Di sini Anda bisa menambahkan pengecekan apakah admin sudah login
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: login.php");
//     exit;
// }

// Masukkan file koneksi database.
require_once '../config.php';

// Variabel untuk menampung pesan error
$errors = [];
$product_name_val = '';
$description_val = '';
$price_val = '';

// --- LOGIKA UNTUK MENAMBAH PRODUK BARU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form dan bersihkan
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $is_active = isset($_POST['is_active']) ? 1 : 0; // Cek jika checkbox dicentang

    // Simpan nilai untuk diisi kembali jika ada error
    $product_name_val = $product_name;
    $description_val = $description;
    $price_val = $price;

    // Validasi
    if (empty($product_name)) {
        $errors[] = "Nama produk tidak boleh kosong.";
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Harga harus berupa angka yang valid.";
    }
    // Cek duplikasi nama produk
    $stmt_check = $conn->prepare("SELECT id FROM products WHERE product_name = ?");
    $stmt_check->bind_param("s", $product_name);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $errors[] = "Nama produk '" . htmlspecialchars($product_name) . "' sudah ada.";
    }
    $stmt_check->close();


    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $product_name, $description, $price, $is_active);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Produk '" . htmlspecialchars($product_name) . "' berhasil ditambahkan!";
            header("Location: products.php");
            exit;
        } else {
            $errors[] = "Gagal menambahkan produk: " . $stmt->error;
        }
        $stmt->close();
    }
}
// --- LOGIKA UNTUK MENGAMBIL SEMUA PRODUK DARI DATABASE ---
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>

<?php $currentPage = 'Produk'; // Variabel untuk menandai halaman aktif di sidebar ?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Manajemen Produk - Admin Dashboard</title>

        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <style>
            body { font-family: "Nunito", sans-serif; }
            ::-webkit-scrollbar { width: 8px; height: 8px; }
            ::-webkit-scrollbar-track { background: #f1f1f1; }
            ::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
            ::-webkit-scrollbar-thumb:hover { background: #555; }
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
                    <!-- Notifikasi -->
                    <?php
                    if (isset($_SESSION['success_message'])) {
                        echo '<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert"><p>' . $_SESSION['success_message'] . '</p></div>';
                        unset($_SESSION['success_message']);
                    }
                    if (!empty($errors)) {
                        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">';
                        foreach ($errors as $error) { echo '<p>' . $error . '</p>'; }
                        echo '</div>';
                    }
                    ?>

                    <!-- Form Tambah/Edit Produk -->
                    <div x-data="{ open: false }" class="mb-8">
                        <button @click="open = !open" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors duration-200 flex items-center">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                            <span x-text="open ? 'Tutup Form' : 'Tambah Produk Baru'"></span>
                        </button>
                        <div x-show="open" x-cloak x-transition class="bg-white p-6 md:p-8 rounded-xl shadow-lg mt-4">
                            <form action="products.php" method="POST">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="product_name" class="block text-sm font-bold text-gray-700 mb-2">Nama Produk</label>
                                        <input type="text" id="product_name" name="product_name" value="<?= htmlspecialchars($product_name_val) ?>" required class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="price" class="block text-sm font-bold text-gray-700 mb-2">Harga (IDR)</label>
                                        <input type="number" id="price" name="price" step="1" value="<?= htmlspecialchars($price_val) ?>" required class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Deskripsi</label>
                                    <textarea id="description" name="description" rows="4" class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($description_val) ?></textarea>
                                </div>
                                <div class="mt-6 flex items-center">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Aktifkan produk ini</label>
                                </div>
                                <div class="mt-8 border-t pt-6 text-right">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition-colors duration-200">
                                        Simpan Produk
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel Daftar Produk -->
                    <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg">
                        <h2 class="text-xl font-semibold text-gray-700 mb-6">Daftar Produk</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Produk</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Harga</th>
                                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (!empty($products)): ?>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($product['product_name']); ?></div>
                                                    <div class="text-sm text-gray-500 truncate max-w-xs"><?= htmlspecialchars($product['description']); ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Rp <?= number_format($product['price'], 0, ',', '.'); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                                    <?php if ($product['is_active']): ?>
                                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Nonaktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="edit_product.php?id=<?= $product['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                    <a href="delete_product.php?id=<?= $product['id']; ?>" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada produk. Silakan tambahkan produk baru.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
                const sidebar = document.getElementById("sidebar");
                const mainContent = document.getElementById("main-content");
                const sidebarToggle = document.getElementById("sidebar-toggle");
                let isSidebarOpen = window.innerWidth >= 1024;

                const updateSidebar = () => {
                    if (isSidebarOpen) {
                        sidebar.classList.remove("-translate-x-full");
                        mainContent.classList.add("lg:ml-64");
                    } else {
                        sidebar.classList.add("-translate-x-full");
                        mainContent.classList.remove("lg:ml-64");
                    }
                };

                sidebarToggle.addEventListener("click", () => {
                    isSidebarOpen = !isSidebarOpen;
                    updateSidebar();
                });

                if (window.innerWidth < 1024) isSidebarOpen = false;
                updateSidebar();

                window.addEventListener("resize", () => {
                    if (window.innerWidth >= 1024) {
                        if (!isSidebarOpen) {
                            isSidebarOpen = true;
                            updateSidebar();
                        }
                    } else {
                        if (isSidebarOpen) {
                            isSidebarOpen = false;
                            updateSidebar();
                        }
                    }
                });
            });
        </script>
    </body>
</html>