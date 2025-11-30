<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$projectId = $_GET['id'] ?? 0;

if (!$projectId || !canAccessProject($_SESSION['user_id'], $projectId)) {
    header('Location: /public/dashboard.php');
    exit;
}

$pageTitle = 'Proje Detayları';
?>
<?php include '../views/header.php'; ?>
<?php include '../views/navbar.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Proje Header -->
    <div id="projectHeader" class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 p-6 mb-8">
        <!-- JavaScript ile doldurulacak -->
    </div>
    
    <!-- Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-700">
            <nav class="flex space-x-4">
                <button onclick="switchTab('tasks')" id="tasksTab" class="tab-button active px-4 py-2 font-medium border-b-2 transition">
                    <i class="fas fa-tasks mr-2"></i>Görevler
                </button>
                <button onclick="switchTab('kanban')" id="kanbanTab" class="tab-button px-4 py-2 font-medium border-b-2 transition">
                    <i class="fas fa-columns mr-2"></i>Kanban Board
                </button>
                <button onclick="switchTab('members')" id="membersTab" class="tab-button px-4 py-2 font-medium border-b-2 transition">
                    <i class="fas fa-users mr-2"></i>Takım
                </button>
            </nav>
        </div>
    </div>
    
    <!-- Tasks Tab -->
    <div id="tasksContent" class="tab-content">
        <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700">
            <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold">Görevler</h2>
                <button onclick="showCreateTaskModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Yeni Görev
                </button>
            </div>
            <div class="p-6">
                <div id="tasksList" class="space-y-4">
                    <!-- JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Kanban Tab -->
    <div id="kanbanContent" class="tab-content hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700">
                <div class="p-4 border-b border-gray-700 bg-gray-700">
                    <h3 class="font-bold text-gray-300">
                        <i class="fas fa-circle text-gray-400 mr-2 text-xs"></i>
                        Yapılacak
                    </h3>
                </div>
                <div id="kanban-todo" class="p-4 min-h-[500px] space-y-3">
                    <!-- JavaScript ile doldurulacak -->
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700">
                <div class="p-4 border-b border-gray-700 bg-blue-900">
                    <h3 class="font-bold text-blue-300">
                        <i class="fas fa-circle text-blue-400 mr-2 text-xs"></i>
                        Devam Ediyor
                    </h3>
                </div>
                <div id="kanban-progress" class="p-4 min-h-[500px] space-y-3">
                    <!-- JavaScript ile doldurulacak -->
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700">
                <div class="p-4 border-b border-gray-700 bg-green-900">
                    <h3 class="font-bold text-green-300">
                        <i class="fas fa-circle text-green-400 mr-2 text-xs"></i>
                        Tamamlandı
                    </h3>
                </div>
                <div id="kanban-done" class="p-4 min-h-[500px] space-y-3">
                    <!-- JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Members Tab -->
    <div id="membersContent" class="tab-content hidden">
        <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700">
            <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold">Takım Üyeleri</h2>
                <button onclick="showAddMemberModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition" id="addMemberBtn">
                    <i class="fas fa-user-plus mr-2"></i>Üye Ekle
                </button>
            </div>
            <div class="p-6">
                <div id="membersList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Görev Oluşturma Modal -->
<div id="createTaskModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg shadow-xl p-8 max-w-md w-full mx-4 border border-gray-700">
        <h3 class="text-2xl font-bold mb-6">Yeni Görev Oluştur</h3>
        <form id="createTaskForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Görev Başlığı</label>
                <input type="text" name="title" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Açıklama</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Atanan Kişi</label>
                <select name="assigned_to" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white" id="assignedToSelect">
                    <option value="">Atanmamış</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Durum</label>
                    <select name="status" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
                        <option value="Yapılacak">Yapılacak</option>
                        <option value="Devam Ediyor">Devam Ediyor</option>
                        <option value="Tamamlandı">Tamamlandı</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Öncelik</label>
                    <select name="priority" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
                        <option value="Düşük">Düşük</option>
                        <option value="Orta" selected>Orta</option>
                        <option value="Yüksek">Yüksek</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Bitiş Tarihi</label>
                <input type="date" name="due_date" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
            </div>
            <div class="flex space-x-3 mt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition">
                    Oluştur
                </button>
                <button type="button" onclick="closeCreateTaskModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition">
                    İptal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Üye Ekleme Modal -->
<div id="addMemberModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg shadow-xl p-8 max-w-md w-full mx-4 border border-gray-700">
        <h3 class="text-2xl font-bold mb-6">Üye Ekle</h3>
        <form id="addMemberForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">E-posta Adresi</label>
                <input type="email" name="email" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Rol</label>
                <select name="role" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
                    <option value="Üye">Üye</option>
                    <option value="Yönetici">Yönetici</option>
                    <option value="Görüntüleyici">Görüntüleyici</option>
                </select>
            </div>
            <div class="flex space-x-3 mt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition">
                    Ekle
                </button>
                <button type="button" onclick="closeAddMemberModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition">
                    İptal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const projectId = <?php echo $projectId; ?>;
</script>
<script src="/public/js/project.js"></script>
<script src="/public/js/kanban.js"></script>

<?php include '../views/footer.php'; ?>
