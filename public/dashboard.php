<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Dashboard';
$currentUser = getCurrentUser();
$userId = $_SESSION['user_id'];
?>
<?php include '../views/header.php'; ?>
<?php include '../views/navbar.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Hoşgeldin Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-xl p-6 mb-8">
        <h1 class="text-3xl font-bold text-white">
            Hoşgeldin, <?php echo escape($currentUser['name']); ?>! 👋
        </h1>
        <p class="text-blue-100 mt-2">İşte bugünkü özetiniz</p>
    </div>
    
    <!-- İstatistikler -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" id="statsCards">
        <!-- JavaScript ile doldurulacak -->
    </div>
    
    <!-- Ana İçerik Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Projeler -->
        <div class="lg:col-span-2">
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700">
                <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                    <h2 class="text-xl font-bold">
                        <i class="fas fa-folder mr-2 text-blue-500"></i>
                        Projelerim
                    </h2>
                    <button onclick="showCreateProjectModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-plus mr-2"></i>
                        Yeni Proje
                    </button>
                </div>
                <div class="p-6">
                    <div id="projectsList" class="space-y-4">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Görevler ve Deadline'lar -->
        <div class="space-y-6">
            <!-- Bana Atanan Görevler -->
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700">
                <div class="p-6 border-b border-gray-700">
                    <h2 class="text-xl font-bold">
                        <i class="fas fa-tasks mr-2 text-purple-500"></i>
                        Görevlerim
                    </h2>
                </div>
                <div class="p-6">
                    <div id="myTasksList" class="space-y-3">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                </div>
            </div>
            
            <!-- Yaklaşan Deadline'lar -->
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700">
                <div class="p-6 border-b border-gray-700">
                    <h2 class="text-xl font-bold">
                        <i class="fas fa-clock mr-2 text-red-500"></i>
                        Yaklaşan Deadline'lar
                    </h2>
                </div>
                <div class="p-6">
                    <div id="upcomingDeadlines" class="space-y-3">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Proje Oluşturma Modal -->
<div id="createProjectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg shadow-xl p-8 max-w-md w-full mx-4 border border-gray-700">
        <h3 class="text-2xl font-bold mb-6">Yeni Proje Oluştur</h3>
        <form id="createProjectForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Proje Adı</label>
                <input type="text" name="name" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Açıklama</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Başlangıç</label>
                    <input type="date" name="start_date" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Bitiş</label>
                    <input type="date" name="end_date" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Renk</label>
                <input type="color" name="color" value="#3B82F6" class="w-full h-10 bg-gray-700 border border-gray-600 rounded-lg">
            </div>
            <div class="flex space-x-3 mt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition">
                    Oluştur
                </button>
                <button type="button" onclick="closeCreateProjectModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition">
                    İptal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Mevcut kullanıcı ID'sini JavaScript'e aktar
    window.currentUserId = <?php echo $userId; ?>;
</script>
<script src="/public/js/dashboard.js"></script>

<?php include '../views/footer.php'; ?>
