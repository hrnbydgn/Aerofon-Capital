<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$taskId = $_GET['id'] ?? 0;

if (!$taskId) {
    header('Location: /public/dashboard.php');
    exit;
}

$pageTitle = 'Görev Detayları';
?>
<?php include '../views/header.php'; ?>
<?php include '../views/navbar.php'; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Ana İçerik -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Görev Detayları -->
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 p-6">
                <div id="taskDetails">
                    <!-- JavaScript ile doldurulacak -->
                </div>
            </div>
            
            <!-- Yorumlar -->
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700">
                <div class="p-6 border-b border-gray-700">
                    <h2 class="text-xl font-bold">
                        <i class="fas fa-comments mr-2 text-blue-500"></i>
                        Yorumlar
                    </h2>
                </div>
                <div class="p-6">
                    <form id="commentForm" class="mb-6">
                        <div class="flex space-x-3">
                            <input type="text" 
                                   name="comment" 
                                   placeholder="Yorum ekle..." 
                                   required
                                   class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    <div id="commentsList" class="space-y-4">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Yan Panel -->
        <div class="space-y-6">
            <!-- Görev Bilgileri -->
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 p-6">
                <h3 class="font-bold mb-4 text-lg">Görev Bilgileri</h3>
                <div id="taskInfo" class="space-y-4">
                    <!-- JavaScript ile doldurulacak -->
                </div>
            </div>
            
            <!-- Görev Geçmişi -->
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 p-6">
                <h3 class="font-bold mb-4 text-lg">
                    <i class="fas fa-history mr-2 text-purple-500"></i>
                    Geçmiş
                </h3>
                <div id="taskHistory" class="space-y-3">
                    <!-- JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Görev Düzenleme Modal -->
<div id="editTaskModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg shadow-xl p-8 max-w-md w-full mx-4 border border-gray-700">
        <h3 class="text-2xl font-bold mb-6">Görev Düzenle</h3>
        <form id="editTaskForm" class="space-y-4">
            <input type="hidden" name="id" id="editTaskId">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Görev Başlığı</label>
                <input type="text" name="title" id="editTaskTitle" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Açıklama</label>
                <textarea name="description" id="editTaskDescription" rows="3" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Durum</label>
                    <select name="status" id="editTaskStatus" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
                        <option value="Yapılacak">Yapılacak</option>
                        <option value="Devam Ediyor">Devam Ediyor</option>
                        <option value="Tamamlandı">Tamamlandı</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Öncelik</label>
                    <select name="priority" id="editTaskPriority" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
                        <option value="Düşük">Düşük</option>
                        <option value="Orta">Orta</option>
                        <option value="Yüksek">Yüksek</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Bitiş Tarihi</label>
                <input type="date" name="due_date" id="editTaskDueDate" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-white">
            </div>
            <div class="flex space-x-3 mt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition">
                    Güncelle
                </button>
                <button type="button" onclick="closeEditTaskModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition">
                    İptal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const taskId = <?php echo $taskId; ?>;
</script>
<script src="/public/js/task.js"></script>

<?php include '../views/footer.php'; ?>
