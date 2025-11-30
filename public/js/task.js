/**
 * Görev Detay Sayfası JavaScript
 */

let currentTask = null;

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    loadTaskData();
});

// Görev verilerini yükle
async function loadTaskData() {
    const result = await apiCall(`/api/tasks.php?action=get&id=${taskId}`);
    
    if (result.success) {
        currentTask = result.task;
        renderTaskDetails();
        renderTaskInfo();
        renderComments(result.task.comments);
        renderHistory(result.task.history);
    } else {
        showToast('Görev bulunamadı', 'error');
        setTimeout(() => {
            window.location.href = '/public/dashboard.php';
        }, 2000);
    }
}

// Görev detaylarını render et
function renderTaskDetails() {
    const detailsHTML = `
        <div class="mb-6">
            <div class="flex items-start justify-between mb-4">
                <h1 class="text-2xl font-bold flex-1">${currentTask.title}</h1>
                <div class="flex space-x-2">
                    <button onclick="showEditTaskModal()" class="text-blue-400 hover:text-blue-300">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteTask()" class="text-red-400 hover:text-red-300">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center space-x-3 mb-4">
                ${getStatusBadge(currentTask.status)}
                ${getPriorityBadge(currentTask.priority)}
            </div>
            
            <div class="bg-gray-700 rounded-lg p-4">
                <h3 class="font-semibold mb-2 text-sm text-gray-400">Açıklama</h3>
                <p class="text-gray-300">${currentTask.description || 'Açıklama yok'}</p>
            </div>
        </div>
    `;
    
    document.getElementById('taskDetails').innerHTML = detailsHTML;
}

// Görev bilgilerini render et
function renderTaskInfo() {
    const infoHTML = `
        <div class="space-y-3">
            <div>
                <p class="text-xs text-gray-400 mb-1">Proje</p>
                <p class="font-medium text-blue-400 cursor-pointer hover:text-blue-300" 
                   onclick="window.location.href='/public/project.php?id=${currentTask.project_id}'">
                    <i class="fas fa-folder mr-1"></i>${currentTask.project_name}
                </p>
            </div>
            
            <div>
                <p class="text-xs text-gray-400 mb-1">Atanan Kişi</p>
                <div class="flex items-center space-x-2">
                    ${currentTask.assigned_avatar ? `<img src="${currentTask.assigned_avatar}" class="w-6 h-6 rounded-full">` : '<i class="fas fa-user-circle text-2xl text-gray-400"></i>'}
                    <p class="font-medium">${currentTask.assigned_name || 'Atanmamış'}</p>
                </div>
            </div>
            
            ${currentTask.due_date ? `
                <div>
                    <p class="text-xs text-gray-400 mb-1">Bitiş Tarihi</p>
                    <p class="font-medium ${isPastDue(currentTask.due_date) ? 'text-red-400' : isDeadlineNear(currentTask.due_date) ? 'text-yellow-400' : ''}">
                        <i class="fas fa-calendar mr-1"></i>${formatDate(currentTask.due_date)}
                    </p>
                </div>
            ` : ''}
            
            <div>
                <p class="text-xs text-gray-400 mb-1">Oluşturan</p>
                <p class="font-medium">${currentTask.creator_name}</p>
            </div>
            
            <div>
                <p class="text-xs text-gray-400 mb-1">Oluşturulma</p>
                <p class="font-medium text-sm">${formatDateTime(currentTask.created_at)}</p>
            </div>
        </div>
    `;
    
    document.getElementById('taskInfo').innerHTML = infoHTML;
}

// Yorumları render et
function renderComments(comments) {
    if (!comments || comments.length === 0) {
        document.getElementById('commentsList').innerHTML = `
            <p class="text-gray-400 text-center py-4">Henüz yorum yok</p>
        `;
        return;
    }
    
    const commentsHTML = comments.map(comment => `
        <div class="bg-gray-700 rounded-lg p-4">
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center space-x-2">
                    <img src="${comment.avatar || '/public/images/default-avatar.png'}" 
                         class="w-8 h-8 rounded-full">
                    <div>
                        <p class="font-semibold text-sm">${comment.name}</p>
                        <p class="text-xs text-gray-400">${timeAgo(comment.created_at)}</p>
                    </div>
                </div>
            </div>
            <p class="text-gray-300">${comment.comment}</p>
        </div>
    `).join('');
    
    document.getElementById('commentsList').innerHTML = commentsHTML;
}

// Geçmişi render et
function renderHistory(history) {
    if (!history || history.length === 0) {
        document.getElementById('taskHistory').innerHTML = `
            <p class="text-gray-400 text-xs text-center py-2">Henüz değişiklik yok</p>
        `;
        return;
    }
    
    const historyHTML = history.map(item => `
        <div class="border-l-2 border-gray-700 pl-3">
            <p class="text-xs font-semibold text-gray-300">${item.action}</p>
            ${item.old_value && item.new_value ? `
                <p class="text-xs text-gray-400">
                    <span class="line-through">${item.old_value}</span> → ${item.new_value}
                </p>
            ` : ''}
            <p class="text-xs text-gray-500 mt-1">
                ${item.name} • ${timeAgo(item.created_at)}
            </p>
        </div>
    `).join('');
    
    document.getElementById('taskHistory').innerHTML = historyHTML;
}

// Yorum ekleme
document.getElementById('commentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = getFormData('commentForm');
    formData.task_id = taskId;
    
    const result = await apiCall('/api/comments.php?action=create', {
        method: 'POST',
        body: JSON.stringify(formData)
    });
    
    if (result.success) {
        showToast('Yorum eklendi', 'success');
        this.reset();
        loadTaskData(); // Yeniden yükle
    } else {
        showToast(result.message, 'error');
    }
});

// Görev düzenleme modal
function showEditTaskModal() {
    document.getElementById('editTaskId').value = currentTask.id;
    document.getElementById('editTaskTitle').value = currentTask.title;
    document.getElementById('editTaskDescription').value = currentTask.description || '';
    document.getElementById('editTaskStatus').value = currentTask.status;
    document.getElementById('editTaskPriority').value = currentTask.priority;
    document.getElementById('editTaskDueDate').value = currentTask.due_date || '';
    
    showModal('editTaskModal');
}

function closeEditTaskModal() {
    hideModal('editTaskModal');
}

// Görev düzenleme form submit
document.getElementById('editTaskForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = getFormData('editTaskForm');
    
    const result = await apiCall('/api/tasks.php?action=update', {
        method: 'POST',
        body: JSON.stringify(formData)
    });
    
    if (result.success) {
        showToast('Görev güncellendi', 'success');
        closeEditTaskModal();
        loadTaskData();
    } else {
        showToast(result.message, 'error');
    }
});

// Görev silme
async function deleteTask() {
    if (!confirmAction('Bu görevi silmek istediğinize emin misiniz?')) {
        return;
    }
    
    const result = await apiCall('/api/tasks.php?action=delete', {
        method: 'POST',
        body: JSON.stringify({ id: taskId })
    });
    
    if (result.success) {
        showToast('Görev silindi', 'success');
        setTimeout(() => {
            window.location.href = `/public/project.php?id=${currentTask.project_id}`;
        }, 1000);
    } else {
        showToast(result.message, 'error');
    }
}
