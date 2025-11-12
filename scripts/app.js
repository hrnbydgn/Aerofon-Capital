/**
 * DJI Agras Spray Drone Interface - Main JavaScript
 * Agras-style UI control logic
 */

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DJI Agras Spray UI initialized');
    
    // Initialize components
    initApplicationRate();
    initMapControls();
    initConnectionPoints();
    initMobilePanel();
});

/**
 * Application Rate Control
 */
function initApplicationRate() {
    const decreaseBtn = document.querySelector('#operation .btn-outline-secondary:first-of-type');
    const increaseBtn = document.querySelector('#operation .btn-outline-secondary:last-of-type');
    const rateDisplay = document.querySelector('#operation h2');
    
    if (decreaseBtn && increaseBtn && rateDisplay) {
        let currentRate = 3.21;
        
        decreaseBtn.addEventListener('click', function() {
            if (currentRate > 0.5) {
                currentRate = Math.max(0.5, currentRate - 0.1);
                updateRate();
            }
        });
        
        increaseBtn.addEventListener('click', function() {
            if (currentRate < 10) {
                currentRate = Math.min(10, currentRate + 0.1);
                updateRate();
            }
        });
        
        function updateRate() {
            rateDisplay.textContent = currentRate.toFixed(2);
            // Update flow rate (example calculation)
            const flowRate = (currentRate * 0.555).toFixed(2);
            const flowDisplay = document.querySelector('#operation .mb-3:nth-of-type(4) strong');
            if (flowDisplay) {
                flowDisplay.textContent = flowRate + ' gal/min';
            }
            showNotification(`Application rate: ${currentRate.toFixed(2)} gal/acre`, 'success');
        }
    }
}

/**
 * Map Controls
 */
function initMapControls() {
    // Zoom button
    const zoomBtn = document.querySelector('.zoom-control .btn');
    if (zoomBtn) {
        zoomBtn.addEventListener('click', function() {
            showNotification('Zoom in', 'info');
        });
    }
    
    // Map action buttons
    const exitBtn = document.querySelector('.map-actions .btn-light:first-child');
    const editBtn = document.querySelector('.map-actions .btn-light:last-child');
    const startBtn = document.querySelector('.map-actions .btn-success');
    
    if (exitBtn) {
        exitBtn.addEventListener('click', function() {
            showNotification('Exiting mission planning', 'warning');
        });
    }
    
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            showNotification('Edit mode activated', 'info');
        });
    }
    
    if (startBtn) {
        startBtn.addEventListener('click', function() {
            showNotification('Starting mission...', 'success');
            // Simulate mission start
            setTimeout(() => {
                showNotification('Mission in progress!', 'success');
            }, 2000);
        });
    }
}

/**
 * Connection Point Management
 */
function initConnectionPoints() {
    const addCPBtn = document.querySelector('.map-popup .btn-light');
    if (addCPBtn) {
        addCPBtn.addEventListener('click', function() {
            showNotification('Connection point added', 'success');
            // In real app, would add marker to map
        });
    }
}

/**
 * Mobile Panel Controls
 */
function initMobilePanel() {
    const mobilePanel = document.getElementById('mobilePanel');
    if (mobilePanel) {
        mobilePanel.addEventListener('shown.bs.offcanvas', function() {
            console.log('Mobile control panel opened');
        });
        
        mobilePanel.addEventListener('hidden.bs.offcanvas', function() {
            console.log('Mobile control panel closed');
        });
    }
}

/**
 * Show Toast Notification
 * @param {string} message - Notification message
 * @param {string} type - Bootstrap color type (success, info, warning, danger)
 */
function showNotification(message, type = 'info') {
    // Create toast container if not exists
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : 
                   type === 'warning' ? 'bg-warning' :
                   type === 'danger' ? 'bg-danger' : 'bg-info';
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    // Remove after hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

/**
 * Simulate Drone Status Updates
 */
function updateDroneStatus() {
    // This would be connected to real drone telemetry
    const speed = (Math.random() * 10).toFixed(1);
    const distance = (Math.random() * 50).toFixed(1);
    const flow = (Math.random() * 2).toFixed(2);
    
    // Update displays if they exist
    const statusBar = document.querySelector('.bottom-status-bar');
    if (statusBar) {
        const values = statusBar.querySelectorAll('.fw-bold');
        if (values.length >= 3) {
            // values[0].innerHTML = `${speed}<small>ft/s</small>`;
            // values[1].innerHTML = `${distance}<small>ft</small>`;
            // values[2].innerHTML = `${flow}<small>gal/min</small>`;
        }
    }
}

/**
 * Template Selection Handler
 */
function selectTemplate() {
    showNotification('Template selection opened', 'info');
    // In real app, would show template picker modal
}

/**
 * Refresh Settings
 */
function refreshSettings() {
    showNotification('Settings refreshed', 'success');
    // Reset to default values
}

/**
 * Export functions for inline event handlers
 */
window.selectTemplate = selectTemplate;
window.refreshSettings = refreshSettings;
window.showNotification = showNotification;

// Optional: Auto-update drone status every 2 seconds (when connected)
// setInterval(updateDroneStatus, 2000);
