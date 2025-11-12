/**
 * DJI Agras Drone Interface - Main JavaScript
 * Bootstrap-based UI control logic
 */

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DJI Agras UI initialized');
    
    // Initialize components
    initSpeedSlider();
    initFPVControls();
    initCPHandlers();
    initFormHandlers();
});

/**
 * Speed Slider Handler
 */
function initSpeedSlider() {
    const slider = document.getElementById('speedSlider');
    const speedValue = document.getElementById('speedValue');
    
    if (slider && speedValue) {
        slider.addEventListener('input', function() {
            speedValue.textContent = this.value + ' m/s';
        });
    }
}

/**
 * FPV Preview Controls
 */
function initFPVControls() {
    const closeFpvBtn = document.getElementById('closeFpv');
    const fpvPreview = document.querySelector('.fpv-preview');
    
    if (closeFpvBtn && fpvPreview) {
        closeFpvBtn.addEventListener('click', function() {
            fpvPreview.style.display = 'none';
        });
    }
}

/**
 * Connection Point (CP) Handlers
 */
function initCPHandlers() {
    // View CP Detail button handler is defined globally
    console.log('CP handlers initialized');
}

/**
 * View Connection Point Detail
 * @param {string} cpId - Connection Point ID
 */
function viewCPDetail(cpId) {
    console.log('Viewing CP:', cpId);
    
    // Switch to CP Detail tab
    const cpDetailTab = document.getElementById('cp-detail-tab');
    if (cpDetailTab) {
        const tab = new bootstrap.Tab(cpDetailTab);
        tab.show();
    }
    
    // Update form with CP data (mock)
    const cpNameInput = document.getElementById('cpName');
    if (cpNameInput) {
        cpNameInput.value = cpId;
    }
    
    // Show notification
    showNotification('Loaded: ' + cpId, 'info');
}

/**
 * Form Submit Handlers
 */
function initFormHandlers() {
    // CP Detail Form
    const cpDetailForm = document.querySelector('#cp-detail form');
    if (cpDetailForm) {
        cpDetailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const cpName = document.getElementById('cpName').value;
            showNotification('CP saved: ' + cpName, 'success');
        });
    }
    
    // Route Settings Form
    const routeSettingsForm = document.querySelector('#route-settings form');
    if (routeSettingsForm) {
        routeSettingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const scanDirection = document.getElementById('scanDirection').value;
            const lineSpacing = document.getElementById('lineSpacing').value;
            showNotification('Route settings applied: ' + scanDirection + ', ' + lineSpacing + 'm', 'success');
        });
    }
}

/**
 * Show Toast Notification
 * @param {string} message - Notification message
 * @param {string} type - Bootstrap color type (success, info, warning, danger)
 */
function showNotification(message, type = 'info') {
    // Create toast element if not exists
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
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
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
 * Mission Control Functions
 */
function takeoff() {
    showNotification('Takeoff initiated...', 'success');
    console.log('Drone takeoff');
}

function land() {
    showNotification('Landing initiated...', 'warning');
    console.log('Drone landing');
}

/**
 * Map Control Functions (placeholders for Leaflet/Mapbox integration)
 */
function zoomIn() {
    console.log('Zoom in');
    showNotification('Zooming in', 'info');
}

function zoomOut() {
    console.log('Zoom out');
    showNotification('Zooming out', 'info');
}

function centerMap() {
    console.log('Center map on drone');
    showNotification('Map centered', 'info');
}

/**
 * Connection Point Management
 */
function addNewCP() {
    console.log('Adding new connection point');
    showNotification('New CP created', 'success');
    // In real app, would open form modal or create marker on map
}

/**
 * Export functions for global use
 */
window.viewCPDetail = viewCPDetail;
window.takeoff = takeoff;
window.land = land;
window.zoomIn = zoomIn;
window.zoomOut = zoomOut;
window.centerMap = centerMap;
window.addNewCP = addNewCP;
