<?php
$currentUser = getCurrentUser();
?>
<nav class="bg-gray-800 border-b border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="/public/dashboard.php" class="text-xl font-bold text-blue-500">
                    <i class="fas fa-project-diagram mr-2"></i>
                    Proje Yönetim
                </a>
                <?php if (isLoggedIn()): ?>
                <div class="ml-10 flex space-x-4">
                    <a href="/public/dashboard.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-home mr-1"></i> Dashboard
                    </a>
                    <a href="/public/projects.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-folder mr-1"></i> Projeler
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isLoggedIn()): ?>
            <div class="flex items-center">
                <div class="relative" id="userMenu">
                    <button onclick="toggleUserMenu()" class="flex items-center space-x-3 text-gray-300 hover:text-white transition">
                        <img src="<?php echo escape($currentUser['avatar'] ?? '/public/images/default-avatar.png'); ?>" 
                             alt="Avatar" 
                             class="w-8 h-8 rounded-full">
                        <span><?php echo escape($currentUser['name']); ?></span>
                        <i class="fas fa-chevron-down text-sm"></i>
                    </button>
                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-700">
                        <a href="/public/profile.php" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">
                            <i class="fas fa-user mr-2"></i> Profil
                        </a>
                        <a href="#" onclick="logout()" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">
                            <i class="fas fa-sign-out-alt mr-2"></i> Çıkış Yap
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('hidden');
}

// Dışarı tıklanınca menüyü kapat
document.addEventListener('click', function(event) {
    const userMenu = document.getElementById('userMenu');
    if (userMenu && !userMenu.contains(event.target)) {
        document.getElementById('userDropdown').classList.add('hidden');
    }
});

function logout() {
    if (confirm('Çıkış yapmak istediğinize emin misiniz?')) {
        fetch('/api/auth.php?action=logout', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/public/login.php';
            }
        });
    }
}
</script>
