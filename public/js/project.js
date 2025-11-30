/**
 * Proje Detay Sayfası JavaScript
 */

let currentProject = null;
let projectMembers = [];

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    loadProjectData();
});

// Proje verilerini yükle
async function loadProjectData() {
    await loadProject();
    await loadTasks();
    await loadMembers();
}

// Proje bilgilerini yükle
async function loadProject() {
    const result = await apiCall(`/api/projects.php?action=get&id=${projectId}`);
    
    if (result.success) {
        currentProject = result.project;
        projectMembers = result.project.members || [];
        
        const headerHTML = `
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-6 h-6 rounded-full" style="background: ${currentProject.color}"></div>
                        <h1 class="text-3xl font-bold">${currentProject.name}</h1>
                        ${getStatusBadge(currentProject.status)}
                    </div>
                    <p class="text-gray-400 mb-4">${currentProject.description || 'Açıklama yok'}</p>
                    <div class="flex items-center space-x-6 text-sm text-gray-400">
                        <span><i class="fas fa-user mr-2"></i>Sahip: ${currentProject.owner_name}</span>
                        ${currentProject.start_date ? `<span><i class="fas fa-calendar-alt mr-2"></i>Başlangıç: ${formatDate(currentProject.start_date)}</span>` : ''}
                        ${currentProject.end_date ? `<span><i class="fas fa-calendar-check mr-2"></i>Bitiş: ${formatDate(currentProject.end_date)}</span>` : ''}
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="editProject()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteProject()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        document.getElementById('projectHeader').innerHTML = headerHTML;
        
        // Üye select'ini doldur
        updateAssignedToSelect();
    }
}

// Görevleri yükle
async function loadTasks() {
    showLoading('tasksList');
    
    const result = await apiCall(`/api/tasks.php?action=list&project_id=${projectId}`);
    
    if (result.success && result.tasks.length > 0) {
        const tasksHTML = result.tasks.map(task => `
            <div class="bg-gray-700 rounded-lg p-4 hover:bg-gray-600 transition cursor-pointer card-hover"
                 onclick="window.location.href='/public/task.php?id=${task.id}'">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-bold text-lg">${task.title}</h3>
                    <div class="flex space-x-2">
                        ${getPriorityBadge(task.priority)}
                        ${getStatusBadge(task.status)}
                    </div>
                </div>
                ${task.description ? `<p class="text-gray-400 text-sm mb-3 line-clamp-2">${task.description}</p>` : ''}
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center space-x-4 text-gray-400">
                        <span><i class="fas fa-user mr-1"></i>${task.assigned_name || 'Atanmamış'}</span>
                        ${task.due_date ? `<span class="${isPastDue(task.due_date) ? 'text-red-400' : ''}">
                            <i class="fas fa-calendar mr-1"></i>${formatDate(task.due_date)}
                        </span>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
        
        document.getElementById('tasksList').innerHTML = tasksHTML;
        
        // Kanban board'u da güncelle
        loadKanbanBoard(result.tasks);
    } else {
        showEmptyState('tasksList', 'Henüz görev yok. Yeni görev oluşturun!', 'tasks');
        document.getElementById('kanban-todo').innerHTML = '';
        document.getElementById('kanban-progress').innerHTML = '';
        document.getElementById('kanban-done').innerHTML = '';
    }
}

// Üyeleri yükle
async function loadMembers() {
    if (!projectMembers.length) {
        showEmptyState('membersList', 'Henüz üye yok', 'users');
        return;
    }
    
    const membersHTML = projectMembers.map(member => `
        <div class="bg-gray-700 rounded-lg p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <img src="${member.avatar || '/public/images/default-avatar.png'}" 
                     alt="${member.name}" 
                     class="w-10 h-10 rounded-full">
                <div>
                    <p class="font-semibold">${member.name}</p>
                    <p class="text-sm text-gray-400">${member.email}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="badge badge-blue">${member.role}</span>
                <button onclick="removeMember(${member.user_id})" class="text-red-400 hover:text-red-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `).join('');
    
    document.getElementById('membersList').innerHTML = membersHTML;
}

// Tab değiştirme
function switchTab(tabName) {
    // Tab butonlarını güncelle
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabName + 'Tab').classList.add('active');
    
    // İçerikleri göster/gizle
    document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
    document.getElementById(tabName + 'Content').classList.remove('hidden');
    
    // Kanban board'a geçildiğinde yükle
    if (tabName === 'kanban') {
        loadTasks();
    }
}

// Görev oluşturma modal
function showCreateTaskModal() {
    showModal('createTaskModal');
}

function closeCreateTaskModal() {
    hideModal('createTaskModal');
    document.getElementById('createTaskForm').reset();
}

// Görev oluşturma form submit
document.getElementById('createTaskForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = getFormData('createTaskForm');
    formData.project_id = projectId;
    
    const result = await apiCall('/api/tasks.php?action=create', {
        method: 'POST',
        body: JSON.stringify(formData)
    });
    
    if (result.success) {
        showToast('Görev başarıyla oluşturuldu!', 'success');
        closeCreateTaskModal();
        loadTasks();
    } else {
        showToast(result.message, 'error');
    }
});

// Üye ekleme modal
function showAddMemberModal() {
    showModal('addMemberModal');
}

function closeAddMemberModal() {
    hideModal('addMemberModal');
    document.getElementById('addMemberForm').reset();
}

// Üye ekleme form submit
document.getElementById('addMemberForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = getFormData('addMemberForm');
    formData.project_id = projectId;
    
    const result = await apiCall('/api/projects.php?action=add_member', {
        method: 'POST',
        body: JSON.stringify(formData)
    });
    
    if (result.success) {
        showToast('Üye başarıyla eklendi!', 'success');
        closeAddMemberModal();
        loadProjectData();
    } else {
        showToast(result.message, 'error');
    }
});

// Üye çıkarma
async function removeMember(memberId) {
    if (!confirmAction('Bu üyeyi projeden çıkarmak istediğinize emin misiniz?')) {
        return;
    }
    
    const result = await apiCall('/api/projects.php?action=remove_member', {
        method: 'POST',
        body: JSON.stringify({
            project_id: projectId,
            member_id: memberId
        })
    });
    
    if (result.success) {
        showToast('Üye projeden çıkarıldı', 'success');
        loadProjectData();
    } else {
        showToast(result.message, 'error');
    }
}

// Proje silme
async function deleteProject() {
    if (!confirmAction('Bu projeyi silmek istediğinize emin misiniz? Tüm görevler de silinecek!')) {
        return;
    }
    
    const result = await apiCall('/api/projects.php?action=delete', {
        method: 'POST',
        body: JSON.stringify({ id: projectId })
    });
    
    if (result.success) {
        showToast('Proje silindi', 'success');
        setTimeout(() => {
            window.location.href = '/public/dashboard.php';
        }, 1000);
    } else {
        showToast(result.message, 'error');
    }
}

// Assigned To select'ini güncelle
function updateAssignedToSelect() {
    const select = document.getElementById('assignedToSelect');
    if (select && projectMembers) {
        const options = projectMembers.map(member => 
            `<option value="${member.user_id}">${member.name}</option>`
        ).join('');
        select.innerHTML = `<option value="">Atanmamış</option>${options}`;
    }
}
