<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Profil';
$currentUser = getCurrentUser();
?>
<?php include '../views/header.php'; ?>
<?php include '../views/navbar.php'; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 p-8">
        <div class="flex items-center space-x-6 mb-8">
            <img src="<?php echo escape($currentUser['avatar'] ?? '/public/images/default-avatar.png'); ?>" 
                 alt="Avatar" 
                 class="w-24 h-24 rounded-full border-4 border-blue-500">
            <div>
                <h1 class="text-3xl font-bold"><?php echo escape($currentUser['name']); ?></h1>
                <p class="text-gray-400 mt-1"><?php echo escape($currentUser['email']); ?></p>
            </div>
        </div>
        
        <div class="border-t border-gray-700 pt-8">
            <h2 class="text-xl font-bold mb-6">Profil Bilgileri</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Ad Soyad</label>
                    <p class="bg-gray-700 px-4 py-2 rounded-lg"><?php echo escape($currentUser['name']); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">E-posta</label>
                    <p class="bg-gray-700 px-4 py-2 rounded-lg"><?php echo escape($currentUser['email']); ?></p>
                </div>
                
                <div class="bg-blue-900/30 border border-blue-700 rounded-lg p-4 mt-8">
                    <p class="text-blue-300 text-sm">
                        <i class="fas fa-info-circle mr-2"></i>
                        Profil düzenleme özelliği yakında eklenecek.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../views/footer.php'; ?>
