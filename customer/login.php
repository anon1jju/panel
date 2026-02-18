<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Nunito -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        // Konfigurasi Tailwind untuk menggunakan font Nunito
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
    <div class="container mx-auto flex items-center justify-center min-h-screen">
        <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Customer Login</h2>
            <?php
            if (isset($_GET['error'])) {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">';
                echo '<span class="block sm:inline">' . htmlspecialchars($_GET['error']) . '</span>';
                echo '</div>';
            }
            ?>
            <form action="proses_login.php" method="POST">
                <div class="mb-4">
                    <label for="customer_name" class="block text-gray-700 text-sm font-bold mb-2">
                        Customer Name
                    </label>
                    <input type="text" id="customer_name" name="customer_name" required
                           class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="mb-6">
                    <label for="api_key" class="block text-gray-700 text-sm font-bold mb-2">
                        API Key
                    </label>
                    <input type="password" id="api_key" name="api_key" required
                           class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-colors duration-200">
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>