/**
 * Ana JavaScript Yardımcı Fonksiyonlar
 */

// Toast bildirimi göster
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} text-xl"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// API çağrısı yardımcısı
async function apiCall(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        showToast('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
        return { success: false, message: error.message };
    }
}

// Tarih formatla
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('tr-TR', options);
}

// Tarih/saat formatla
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('tr-TR', options);
}

// Göreceli zaman formatla
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    const intervals = {
        yıl: 31536000,
        ay: 2592000,
        hafta: 604800,
        gün: 86400,
        saat: 3600,
        dakika: 60
    };
    
    for (const [name, secondsInInterval] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / secondsInInterval);
        if (interval >= 1) {
            return `${interval} ${name} önce`;
        }
    }
    
    return 'az önce';
}

// Durum badge'i oluştur
function getStatusBadge(status) {
    const colors = {
        'Aktif': 'blue',
        'Tamamlandı': 'green',
        'Askıda': 'yellow',
        'Yapılacak': 'gray',
        'Devam Ediyor': 'blue'
    };
    
    const color = colors[status] || 'gray';
    return `<span class="badge badge-${color}">${status}</span>`;
}

// Öncelik badge'i oluştur
function getPriorityBadge(priority) {
    const colors = {
        'Düşük': 'green',
        'Orta': 'yellow',
        'Yüksek': 'red'
    };
    
    const icons = {
        'Düşük': 'arrow-down',
        'Orta': 'minus',
        'Yüksek': 'arrow-up'
    };
    
    const color = colors[priority] || 'gray';
    const icon = icons[priority] || 'minus';
    
    return `<span class="badge badge-${color}">
        <i class="fas fa-${icon} mr-1"></i>${priority}
    </span>`;
}

// Loading spinner göster
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = `
            <div class="flex justify-center items-center py-12">
                <div class="spinner"></div>
            </div>
        `;
    }
}

// Empty state göster
function showEmptyState(elementId, message, icon = 'inbox') {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-${icon}"></i>
                <p class="text-lg">${message}</p>
            </div>
        `;
    }
}

// Onay dialogu
function confirmAction(message) {
    return confirm(message);
}

// Form verilerini al
function getFormData(formId) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    return data;
}

// Modal göster/gizle helper fonksiyonları
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Tarih karşılaştırma - deadline yakın mı?
function isDeadlineNear(dueDate, daysThreshold = 3) {
    if (!dueDate) return false;
    
    const due = new Date(dueDate);
    const now = new Date();
    const diffTime = due - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    return diffDays <= daysThreshold && diffDays >= 0;
}

// Tarih geçmiş mi?
function isPastDue(dueDate) {
    if (!dueDate) return false;
    
    const due = new Date(dueDate);
    const now = new Date();
    
    return due < now;
}

// Progress yüzdesi hesapla
function calculateProgress(completed, total) {
    if (total === 0) return 0;
    return Math.round((completed / total) * 100);
}

// Progress bar oluştur
function createProgressBar(completed, total) {
    const percentage = calculateProgress(completed, total);
    return `
        <div class="progress-bar">
            <div class="progress-fill" style="width: ${percentage}%"></div>
        </div>
        <div class="text-xs text-gray-400 mt-1">${completed}/${total} tamamlandı (${percentage}%)</div>
    `;
}
