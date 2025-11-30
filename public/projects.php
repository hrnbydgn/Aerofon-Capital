<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Projeler';
?>
<?php include '../views/header.php'; ?>
<?php include '../views/navbar.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">
            <i class="fas fa-folder mr-3 text-blue-500"></i>
            Tüm Projeler
        </h1>
        <button onclick="showCreateProjectModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition shadow-lg">
            <i class="fas fa-plus mr-2"></i>
            Yeni Proje
        </button>
    </div>
    
    <!-- Filtreler -->
    <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 p-4 mb-6">
        <div class="flex space-x-4">
            <button onclick="filterProjects('all')" class="filter-btn active px-4 py-2 rounded-lg transition">
                Tümü
            </button>
            <button onclick="filterProjects('Aktif')" class="filter-btn px-4 py-2 rounded-lg transition">
                Aktif
            </button>
            <button onclick="filterProjects('Tamamlandı')" class="filter-btn px-4 py-2 rounded-lg transition">
                Tamamlandı
            </button>
            <button onclick="filterProjects('Askıda')" class="filter-btn px-4 py-2 rounded-lg transition">
                Askıda
            </button>
        </div>
    </div>
    
    <div id="projectsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- JavaScript ile doldurulacak -->
    </div>
</div>

<!-- Proje Oluşturma Modal (aynı dashboard'daki gibi) -->
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

<script src="/public/js/projects.js"></script>

<?php include '../views/footer.php'; ?>
