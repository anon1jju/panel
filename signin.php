<?php
// Mulai sesi untuk bisa menampilkan pesan error dari proses_login.php
session_start();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 max-w-4xl w-full bg-white rounded-xl shadow-2xl overflow-hidden">
            
            <div class="hidden md:block">
                <img class="w-full h-full object-cover" src="https://img.freepik.com/free-vector/writing-letter-concept-illustration_114360-4442.jpg?ga=GA1.1.1203153332.1749728801&w=740" alt="Login Illustration">
            </div>

            <div class="p-8 md:p-12">
                <div class="w-full">
                    <div class="text-center mb-8">
                         <h1 class="text-3xl font-bold text-slate-900">Gandalf</h1>
                         <p class="text-gray-500">Selamat datang kembali!</p>
                    </div>
                    <?php if (isset($_SESSION['login_error'])): ?>
                        <div class="bg-red-100 border border-red-400 mb-3 text-red-700 px-4 py-3 rounded ..." role="alert">
                            <span class="block sm:inline"><?php echo $_SESSION['login_error']; ?></span>
                        </div>
                        <?php 
                            unset($_SESSION['login_error']); 
                        ?>
                    <?php endif; ?>

                    <form action="admin/proses_login.php" method="POST">
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">User</label>
                            <input type="text" id="email" name="username" placeholder="contoh@email.com"
                                   class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition duration-300">
                        </div>

                        <div class="mb-6">
                            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                            <input type="password" id="password" name="password" placeholder="••••••••"
                                   class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition duration-300">
                        </div>

                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="remember_me" class="ml-2 block text-sm text-gray-900">Ingat Saya</label>
                            </div>
                            <div>
                                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Lupa password?
                                </a>
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                    class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300">
                                Masuk
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-600">
                            Belum punya akun? <a href="#" class="font-medium text-blue-600 hover:text-blue-500">Daftar di sini</a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>