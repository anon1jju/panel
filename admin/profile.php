<div class="relative">
                            <button @click="profileOpen = !profileOpen" class="flex items-center space-x-2 focus:outline-none">
                                <img src="https://ui-avatars.com/api/?name=Admin+User&background=313a46&color=fff" alt="Avatar" class="w-10 h-10 rounded-full" />
                                <span class="hidden md:block font-medium text-gray-700">Admin User</span>
                            </button>
                            <div x-show="profileOpen" @click.away="profileOpen = false" x-cloak class="absolute right-0 w-48 mt-2 py-2 bg-white border rounded-lg shadow-xl">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-500 hover:text-white">Profil Saya</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-500 hover:text-white">Pengaturan</a>
                                <div class="border-t border-gray-100"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-500 hover:text-white">Logout</a>
                            </div>
                        </div>