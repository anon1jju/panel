<?php

require_once '../config.php';

// Variabel untuk menampung data produk dan pesan error
$product = null;
$errors = [];
$product_id = null;
$product_name = '';
$description = '';
$price = '';
$is_active = 1;

// --- LANGKAH 1: DAPATKAN ID DAN AMBIL DATA PRODUK (UNTUK GET REQUEST) ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['error_message'] = "ID Produk tidak valid.";
        header("Location: products.php");
        exit;
    }
    
    $product_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
        // Isi variabel untuk ditampilkan di form
        $product_name = $product['product_name'];
        $description = $product['description'];
        $price = $product['price'];
        $is_active = $product['is_active'];
    } else {
        $_SESSION['error_message'] = "Produk tidak ditemukan.";
        header("Location: products.php");
        exit;
    }
    $stmt->close();
}

// --- LANGKAH 2: PROSES FORM SAAT DISUBMIT (UNTUK POST REQUEST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $product_id = $_POST['id'];
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validasi
    if (empty($product_name)) {
        $errors[] = "Nama produk tidak boleh kosong.";
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Harga harus berupa angka yang valid.";
    }
    // Cek duplikasi nama produk, KECUALI untuk produk itu sendiri
    $stmt_check = $conn->prepare("SELECT id FROM products WHERE product_name = ? AND id != ?");
    $stmt_check->bind_param("si", $product_name, $product_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $errors[] = "Nama produk '" . htmlspecialchars($product_name) . "' sudah digunakan oleh produk lain.";
    }
    $stmt_check->close();

    // Jika tidak ada error, update data
    if (empty($errors)) {
        $stmt_update = $conn->prepare("UPDATE products SET product_name = ?, description = ?, price = ?, is_active = ? WHERE id = ?");
        $stmt_update->bind_param("ssdii", $product_name, $description, $price, $is_active, $product_id);

        if ($stmt_update->execute()) {
            $_SESSION['success_message'] = "Produk '" . htmlspecialchars($product_name) . "' berhasil diperbarui!";
            header("Location: products.php");
            exit;
        } else {
            $errors[] = "Gagal memperbarui produk: " . $stmt_update->error;
        }
        $stmt_update->close();
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
                        <h2 class="text-xl font-semibold text-gray-700 ml-4">Edit Produk</h2>
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
                <!-- ISI KONTEN -->
                <main class="flex-1 p-6 overflow-x-hidden overflow-y-auto">
                    <!-- Notifikasi Error -->
                    <?php
                    if (!empty($errors)) {
                        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">';
                        foreach ($errors as $error) { echo '<p>' . $error . '</p>'; }
                        echo '</div>';
                    }
                    ?>
                    
                    <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg">
                        <form action="edit_product.php" method="POST">
                            <!-- Input tersembunyi untuk menyimpan ID produk -->
                            <input type="hidden" name="id" value="<?= htmlspecialchars($product_id); ?>">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="product_name" class="block text-sm font-bold text-gray-700 mb-2">Nama Produk</label>
                                    <input type="text" id="product_name" name="product_name" value="<?= htmlspecialchars($product_name); ?>" required class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="price" class="block text-sm font-bold text-gray-700 mb-2">Harga (IDR)</label>
                                    <input type="number" id="price" name="price" step="1" value="<?= htmlspecialchars($price); ?>" required class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="mt-6">
                                <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Deskripsi</label>
                                <textarea id="description" name="description" rows="4" class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($description); ?></textarea>
                            </div>
                            <div class="mt-6 flex items-center">
                                <input type="checkbox" id="is_active" name="is_active" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" <?= $is_active ? 'checked' : ''; ?>>
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">Aktifkan produk ini</label>
                            </div>
                            <div class="mt-8 border-t pt-6 flex justify-end space-x-4">
                                <a href="products.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition-colors duration-200">
                                    Batal
                                </a>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition-colors duration-200">
                                    Update Produk
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