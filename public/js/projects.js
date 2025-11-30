/**
 * Projeler Sayfası JavaScript
 */

let allProjects = [];
let currentFilter = 'all';

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    loadProjects();
});

// Projeleri yükle
async function loadProjects() {
    showLoading('projectsGrid');
    
    const result = await apiCall('/api/projects.php?action=list');
    
    if (result.success) {
        allProjects = result.projects;
        renderProjects();
    } else {
        showEmptyState('projectsGrid', 'Bir hata oluştu', 'exclamation-triangle');
    }
}

// Projeleri render et
function renderProjects() {
    let filteredProjects = allProjects;
    
    // Filtre uygula
    if (currentFilter !== 'all') {
        filteredProjects = allProjects.filter(p => p.status === currentFilter);
    }
    
    if (filteredProjects.length === 0) {
        showEmptyState('projectsGrid', 'Proje bulunamadı', 'folder-open');
        return;
    }
    
    const projectsHTML = filteredProjects.map(project => {
        const progress = calculateProgress(parseInt(project.completed_count), parseInt(project.task_count));
        
        return `
            <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 p-6 card-hover cursor-pointer transition"
                 onclick="window.location.href='/public/project.php?id=${project.id}'">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-4 h-4 rounded-full" style="background: ${project.color}"></div>
                        <h3 class="font-bold text-xl">${project.name}</h3>
                    </div>
                    ${getStatusBadge(project.status)}
                </div>
                
                <p class="text-gray-400 text-sm mb-4 line-clamp-2">
                    ${project.description || 'Açıklama yok'}
                </p>
                
                ${createProgressBar(parseInt(project.completed_count), parseInt(project.task_count))}
                
                <div class="mt-4 pt-4 border-t border-gray-700 flex items-center justify-between text-sm text-gray-400">
                    <div class="flex items-center space-x-4">
                        <span><i class="fas fa-user mr-1"></i>${project.owner_name}</span>
                        <span><i class="fas fa-tasks mr-1"></i>${project.task_count} görev</span>
                    </div>
                    ${project.end_date ? `
                        <span class="${isPastDue(project.end_date) ? 'text-red-400' : ''}">
                            <i class="fas fa-calendar mr-1"></i>${formatDate(project.end_date)}
                        </span>
                    ` : ''}
                </div>
            </div>
        `;
    }).join('');
    
    document.getElementById('projectsGrid').innerHTML = projectsHTML;
}

// Proje filtreleme
function filterProjects(filter) {
    currentFilter = filter;
    
    // Filter butonlarını güncelle
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    renderProjects();
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
        loadProjects();
    } else {
        showToast(result.message, 'error');
    }
});
