/**
 * Dashboard JavaScript
 */

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

// Dashboard verilerini yükle
async function loadDashboardData() {
    await Promise.all([
        loadStats(),
        loadProjects(),
        loadMyTasks(),
        loadUpcomingDeadlines()
    ]);
}

// İstatistikleri yükle
async function loadStats() {
    showLoading('statsCards');
    
    const [projects, tasks] = await Promise.all([
        apiCall('/api/projects.php?action=list'),
        apiCall('/api/tasks.php?action=list')
    ]);
    
    if (projects.success && tasks.success) {
        const activeProjects = projects.projects.filter(p => p.status === 'Aktif').length;
        const completedTasks = tasks.tasks.filter(t => t.status === 'Tamamlandı').length;
        const inProgressTasks = tasks.tasks.filter(t => t.status === 'Devam Ediyor').length;
        
        document.getElementById('statsCards').innerHTML = `
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-200 text-sm">Toplam Proje</p>
                        <p class="text-3xl font-bold text-white mt-2">${projects.projects.length}</p>
                    </div>
                    <i class="fas fa-folder text-5xl text-blue-300 opacity-50"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-lg p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-200 text-sm">Aktif Proje</p>
                        <p class="text-3xl font-bold text-white mt-2">${activeProjects}</p>
                    </div>
                    <i class="fas fa-chart-line text-5xl text-green-300 opacity-50"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-200 text-sm">Devam Eden Görev</p>
                        <p class="text-3xl font-bold text-white mt-2">${inProgressTasks}</p>
                    </div>
                    <i class="fas fa-tasks text-5xl text-purple-300 opacity-50"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-lg p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-200 text-sm">Tamamlanan Görev</p>
                        <p class="text-3xl font-bold text-white mt-2">${completedTasks}</p>
                    </div>
                    <i class="fas fa-check-circle text-5xl text-yellow-300 opacity-50"></i>
                </div>
            </div>
        `;
    }
}

// Projeleri yükle
async function loadProjects() {
    showLoading('projectsList');
    
    const result = await apiCall('/api/projects.php?action=list');
    
    if (result.success && result.projects.length > 0) {
        const projectsHTML = result.projects.slice(0, 5).map(project => {
            const progress = calculateProgress(parseInt(project.completed_count), parseInt(project.task_count));
            
            return `
                <div class="bg-gray-700 rounded-lg p-4 hover:bg-gray-600 transition cursor-pointer card-hover" 
                     onclick="window.location.href='/public/project.php?id=${project.id}'">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full" style="background: ${project.color}"></div>
                            <h3 class="font-bold text-lg">${project.name}</h3>
                        </div>
                        ${getStatusBadge(project.status)}
                    </div>
                    <p class="text-gray-400 text-sm mb-3 line-clamp-2">${project.description || 'Açıklama yok'}</p>
                    ${createProgressBar(parseInt(project.completed_count), parseInt(project.task_count))}
                    <div class="flex items-center justify-between mt-3 text-xs text-gray-400">
                        <span><i class="fas fa-user mr-1"></i>${project.owner_name}</span>
                        <span><i class="fas fa-tasks mr-1"></i>${project.task_count} görev</span>
                    </div>
                </div>
            `;
        }).join('');
        
        document.getElementById('projectsList').innerHTML = projectsHTML;
    } else {
        showEmptyState('projectsList', 'Henüz proje yok', 'folder-open');
    }
}

// Bana atanan görevleri yükle
async function loadMyTasks() {
    showLoading('myTasksList');
    
    const result = await apiCall('/api/tasks.php?action=list');
    
    if (result.success && result.tasks.length > 0) {
        // Bana atanan ve henüz tamamlanmayan görevler
        const myTasks = result.tasks.filter(t => 
            t.assigned_to == getCurrentUserId() && t.status !== 'Tamamlandı'
        ).slice(0, 5);
        
        if (myTasks.length > 0) {
            const tasksHTML = myTasks.map(task => `
                <div class="bg-gray-700 rounded-lg p-3 hover:bg-gray-600 transition cursor-pointer"
                     onclick="window.location.href='/public/task.php?id=${task.id}'">
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="font-semibold text-sm">${task.title}</h4>
                        ${getPriorityBadge(task.priority)}
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-400">
                        ${getStatusBadge(task.status)}
                        ${task.due_date ? `<span class="${isPastDue(task.due_date) ? 'text-red-400' : ''}">
                            <i class="fas fa-calendar mr-1"></i>${formatDate(task.due_date)}
                        </span>` : ''}
                    </div>
                </div>
            `).join('');
            
            document.getElementById('myTasksList').innerHTML = tasksHTML;
        } else {
            showEmptyState('myTasksList', 'Size atanmış görev yok', 'tasks');
        }
    } else {
        showEmptyState('myTasksList', 'Henüz görev yok', 'tasks');
    }
}

// Yaklaşan deadline'ları yükle
async function loadUpcomingDeadlines() {
    showLoading('upcomingDeadlines');
    
    const result = await apiCall('/api/tasks.php?action=list');
    
    if (result.success && result.tasks.length > 0) {
        // Bitmemiş görevleri tarihe göre sırala
        const upcomingTasks = result.tasks
            .filter(t => t.due_date && t.status !== 'Tamamlandı')
            .sort((a, b) => new Date(a.due_date) - new Date(b.due_date))
            .slice(0, 5);
        
        if (upcomingTasks.length > 0) {
            const deadlinesHTML = upcomingTasks.map(task => {
                const isNear = isDeadlineNear(task.due_date);
                const isPast = isPastDue(task.due_date);
                
                return `
                    <div class="bg-gray-700 rounded-lg p-3 hover:bg-gray-600 transition cursor-pointer ${isPast ? 'border-l-4 border-red-500' : isNear ? 'border-l-4 border-yellow-500' : ''}"
                         onclick="window.location.href='/public/task.php?id=${task.id}'">
                        <h4 class="font-semibold text-sm mb-2">${task.title}</h4>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-400">${task.assigned_name || 'Atanmamış'}</span>
                            <span class="${isPast ? 'text-red-400' : isNear ? 'text-yellow-400' : 'text-gray-400'}">
                                <i class="fas fa-clock mr-1"></i>${formatDate(task.due_date)}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');
            
            document.getElementById('upcomingDeadlines').innerHTML = deadlinesHTML;
        } else {
            showEmptyState('upcomingDeadlines', 'Yaklaşan deadline yok', 'clock');
        }
    } else {
        showEmptyState('upcomingDeadlines', 'Henüz görev yok', 'clock');
    }
}

// Mevcut kullanıcı ID'sini al (session'dan)
function getCurrentUserId() {
    // Bu bilgi sayfa yüklenirken PHP tarafından eklenecek
    return window.currentUserId;
}

// Proje oluşturma modal
function showCreateProjectModal() {
    showModal('createProjectModal');
}

function closeCreateProjectModal() {
    hideModal('createProjectModal');
    document.getElementById('createProjectForm').reset();
}

// Proje oluşturma form submit
document.getElementById('createProjectForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = getFormData('createProjectForm');
    const result = await apiCall('/api/projects.php?action=create', {
        method: 'POST',
        body: JSON.stringify(formData)
    });
    
    if (result.success) {
        showToast('Proje başarıyla oluşturuldu!', 'success');
        closeCreateProjectModal();
        loadDashboardData();
    } else {
        showToast(result.message, 'error');
    }
});
