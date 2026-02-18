<?php
// Mulai sesi
session_start();

// Cek apakah variabel sesi 'is_logged_in' tidak ada atau tidak bernilai true
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    // Jika tidak login, paksa kembali ke halaman login
    header("Location: ../signin.php");
    exit;
}
?>
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen bg-slate-900 text-white transition-transform duration-300 ease-in-out">
    <div class="flex items-center justify-center h-20 border-b border-slate-700">
        <h1 class="text-2xl font-bold">Admin Panel</h1>
    </div>

    <?php
        // Daftar halaman yang termasuk dalam menu "Tools"
        $toolsPages = ['Email Validation', 'Email Provider Check', 'Card Check', 'Amazon Email Check', 'Spotify Email Check'];
    ?>

    <nav class="mt-4 px-2" x-data="{ openMenu: '<?php echo in_array($currentPage, $toolsPages) ? 'tools' : '' ?>' }">
        
        <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200
            <?php echo ($currentPage == 'dashboard') ? 'bg-slate-700 text-white' : 'text-gray-300 hover:bg-slate-700 hover:text-white'; ?>">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="mx-4 font-semibold">Dashboard</span>
        </a>

        <div class="mt-2">
            <button @click="openMenu = openMenu === 'tools' ? '' : 'tools'" class="w-full flex justify-between items-center px-4 py-3 rounded-lg transition-colors duration-200
                <?php echo in_array($currentPage, $toolsPages) ? 'text-white' : 'text-gray-300 hover:bg-slate-700 hover:text-white'; ?>">
                <span class="flex items-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    <span class="mx-4 font-semibold">Tools</span>
                </span>
                <span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': openMenu === 'tools'}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </span>
            </button>
            <div x-show="openMenu === 'tools'" x-cloak x-transition class="pl-8 py-2">
                <a href="email_validation.php" class="block px-4 py-2 text-sm rounded-lg <?php echo ($currentPage == 'Email Validation') ? 'bg-slate-700 text-white' : 'text-gray-400 hover:bg-slate-700 hover:text-white'; ?>">Email Validation</a>
                <a href="card_check.php" class="block px-4 py-2 text-sm rounded-lg <?php echo ($currentPage == 'Card Check') ? 'bg-slate-700 text-white' : 'text-gray-400 hover:bg-slate-700 hover:text-white'; ?>">Card Check</a>
                <a href="email_provider_check.php" class="block px-4 py-2 text-sm rounded-lg <?php echo ($currentPage == 'Email Provider Check') ? 'bg-slate-700 text-white' : 'text-gray-400 hover:bg-slate-700 hover:text-white'; ?>">Email Provider Check</a>
                <a href="amazon_check.php" class="block px-4 py-2 text-sm rounded-lg <?php echo ($currentPage == 'Amazon Email Check') ? 'bg-slate-700 text-white' : 'text-gray-400 hover:bg-slate-700 hover:text-white'; ?>">Amazon Email Check</a>
                <a href="spotify_check.php" class="block px-4 py-2 text-sm rounded-lg <?php echo ($currentPage == 'Spotify Email Check') ? 'bg-slate-700 text-white' : 'text-gray-400 hover:bg-slate-700 hover:text-white'; ?>">Spotify Email Check</a>
            </div>
        </div>

        <a href="users.php" class="flex items-center mt-2 px-4 py-3 rounded-lg transition-colors duration-200
            <?php echo ($currentPage == 'Manajemen User') ? 'bg-slate-700 text-white' : 'text-gray-300 hover:bg-slate-700 hover:text-white'; ?>">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span class="mx-4 font-semibold">Manajemen User</span>
        </a>
        
        <a href="products.php" class="flex items-center mt-2 px-4 py-3 rounded-lg transition-colors duration-200
            <?php echo ($currentPage == 'Produk') ? 'bg-slate-700 text-white' : 'text-gray-300 hover:bg-slate-700 hover:text-white'; ?>">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span class="mx-4 font-semibold">Products</span>
        </a>
        
        <a href="pengaturan.php" class="flex items-center mt-2 px-4 py-3 rounded-lg transition-colors duration-200
            <?php echo ($currentPage == 'Pengaturan') ? 'bg-slate-700 text-white' : 'text-gray-300 hover:bg-slate-700 hover:text-white'; ?>">
           <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924-1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.096 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="mx-4 font-semibold">Pengaturan</span>
        </a>
    </nav>
</aside>