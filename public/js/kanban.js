/**
 * Kanban Board JavaScript
 */

// Kanban board'u yükle
function loadKanbanBoard(tasks) {
    const columns = {
        'Yapılacak': 'kanban-todo',
        'Devam Ediyor': 'kanban-progress',
        'Tamamlandı': 'kanban-done'
    };
    
    // Sütunları temizle
    Object.values(columns).forEach(columnId => {
        document.getElementById(columnId).innerHTML = '';
    });
    
    // Görevleri sütunlara yerleştir
    tasks.forEach(task => {
        const columnId = columns[task.status];
        if (columnId) {
            const taskCard = createKanbanCard(task);
            document.getElementById(columnId).appendChild(taskCard);
        }
    });
    
    // Drag & Drop'u etkinleştir
    enableDragAndDrop();
}

// Kanban kartı oluştur
function createKanbanCard(task) {
    const card = document.createElement('div');
    card.className = 'kanban-card';
    card.draggable = true;
    card.dataset.taskId = task.id;
    card.dataset.status = task.status;
    
    card.innerHTML = `
        <div class="flex items-start justify-between mb-2">
            <h4 class="font-semibold text-sm cursor-pointer hover:text-blue-400" onclick="window.location.href='/public/task.php?id=${task.id}'">
                ${task.title}
            </h4>
            ${getPriorityBadge(task.priority)}
        </div>
        ${task.description ? `<p class="text-xs text-gray-400 mb-3 line-clamp-2">${task.description}</p>` : ''}
        <div class="flex items-center justify-between text-xs">
            <div class="flex items-center space-x-2">
                ${task.assigned_name ? `
                    <span class="text-gray-400">
                        <i class="fas fa-user mr-1"></i>${task.assigned_name}
                    </span>
                ` : ''}
            </div>
            ${task.due_date ? `
                <span class="${isPastDue(task.due_date) ? 'text-red-400' : isDeadlineNear(task.due_date) ? 'text-yellow-400' : 'text-gray-400'}">
                    <i class="fas fa-clock mr-1"></i>${formatDate(task.due_date)}
                </span>
            ` : ''}
        </div>
    `;
    
    return card;
}

// Drag & Drop'u etkinleştir
function enableDragAndDrop() {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('[id^="kanban-"]');
    
    // Card drag events
    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });
    
    // Column drop events
    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragleave', handleDragLeave);
    });
}

// Drag başladığında
function handleDragStart(e) {
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
    e.dataTransfer.setData('taskId', this.dataset.taskId);
    e.dataTransfer.setData('oldStatus', this.dataset.status);
}

// Drag bittiğinde
function handleDragEnd(e) {
    this.classList.remove('dragging');
}

// Drag over
function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    this.classList.add('bg-gray-700');
    return false;
}

// Drag leave
function handleDragLeave(e) {
    this.classList.remove('bg-gray-700');
}

// Drop
async function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    this.classList.remove('bg-gray-700');
    
    const taskId = e.dataTransfer.getData('taskId');
    const oldStatus = e.dataTransfer.getData('oldStatus');
    
    // Yeni durumu belirle
    const columnId = this.id;
    let newStatus = '';
    
    switch(columnId) {
        case 'kanban-todo':
            newStatus = 'Yapılacak';
            break;
        case 'kanban-progress':
            newStatus = 'Devam Ediyor';
            break;
        case 'kanban-done':
            newStatus = 'Tamamlandı';
            break;
    }
    
    if (newStatus && newStatus !== oldStatus) {
        await updateTaskStatus(taskId, newStatus);
    }
    
    return false;
}

// Görev durumunu güncelle
async function updateTaskStatus(taskId, newStatus) {
    const result = await apiCall('/api/tasks.php?action=update_status', {
        method: 'POST',
        body: JSON.stringify({
            id: taskId,
            status: newStatus
        })
    });
    
    if (result.success) {
        showToast('Görev durumu güncellendi', 'success');
        loadTasks(); // Kanban board'u yeniden yükle
    } else {
        showToast(result.message, 'error');
        loadTasks(); // Hata durumunda eski haline getir
    }
}
