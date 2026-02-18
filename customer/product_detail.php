<?php
session_start();
require_once '../config.php';

// Pastikan customer sudah login
if (!isset($_SESSION['customer_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah ID produk ada dan valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$product_id = $_GET['id'];
$product = null;

// Ambil detail produk dari database
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $product = $result->fetch_assoc();
} else {
    // Jika produk tidak ditemukan atau tidak aktif, kembalikan ke dashboard
    header("Location: dashboard.php");
    exit;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - <?php echo htmlspecialchars($product['product_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Nunito', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-4 md:p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Tombol Kembali ke Dashboard -->
            <div class="mb-6">
                <a href="dashboard.php" class="text-sm text-blue-600 hover:text-blue-800 font-semibold flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Back to Dashboard
                </a>
            </div>

            <div class="bg-white p-8 rounded-xl shadow-lg">
                <!-- Judul Produk -->
                <h1 class="text-4xl font-extrabold text-gray-800 mb-2">
                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $product['product_name']))); ?>
                </h1>
                
                <!-- Harga Produk -->
                <p class="text-2xl font-semibold text-blue-600 mb-6">
                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?> <!--<span class="text-base font-normal text-gray-500">/ bulan</span>-->
                </p>

                <!-- Deskripsi Lengkap Produk -->
                <div class="prose max-w-none text-gray-700">
                    <p class="lead"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <!-- Tombol Call to Action (CTA) -->
                <div class="mt-8 border-t pt-6 text-center">
                    <!--<h3 class="text-lg font-semibold text-gray-800">Interested?</h3>
                    --><p class="text-gray-600 mb-4">Please proceed with your purchase below. At the moment, we only accept cryptocurrency as the method of payment.</p>
                    <a href="subscribe.php?id=<?php echo $product['id']; ?>" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transform hover:scale-105 transition-all duration-200">
                        Purchase
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>