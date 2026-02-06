/* ============================================
   EVLY - Akıllı Ev Yönetimi
   Main Application JavaScript
   ============================================ */

// ---- State ----
const state = {
  currentPage: 'home',
  scanActive: false,
  shoppingItems: [],
  inventoryFilter: 'all'
};

// ---- DOM Ready ----
document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
  initQuickActions();
  initScan();
  initInventoryFilters();
  initInventorySearch();
  initShoppingList();
  initNotifications();
  registerServiceWorker();
});

// ============================================
// NAVIGATION
// ============================================
function initNavigation() {
  // Bottom nav items
  const navItems = document.querySelectorAll('.nav-item[data-page]');
  navItems.forEach(item => {
    item.addEventListener('click', () => {
      const page = item.dataset.page;
      navigateTo(page);
    });
  });
}

function navigateTo(pageName) {
  // Update pages
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  const targetPage = document.getElementById(`page-${pageName}`);
  if (targetPage) {
    targetPage.classList.add('active');
  }

  // Update nav
  document.querySelectorAll('.nav-item[data-page]').forEach(n => n.classList.remove('active'));
  const navItem = document.querySelector(`.nav-item[data-page="${pageName}"]`);
  if (navItem) {
    navItem.classList.add('active');
  }

  state.currentPage = pageName;

  // Stop scanning when leaving scan page
  if (pageName !== 'scan') {
    stopScanning();
  }

  // Scroll to top
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ============================================
// QUICK ACTIONS (Dashboard)
// ============================================
function initQuickActions() {
  document.querySelectorAll('[data-navigate]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.navigate;
      navigateTo(target);
    });
  });
}

// ============================================
// SCAN FUNCTIONALITY
// ============================================
function initScan() {
  const scanArea = document.getElementById('scan-area');
  const btnCamera = document.getElementById('btn-camera');
  const btnGallery = document.getElementById('btn-gallery');
  const btnManual = document.getElementById('btn-manual');

  if (btnCamera) {
    btnCamera.addEventListener('click', () => {
      startScanning();
    });
  }

  if (btnGallery) {
    btnGallery.addEventListener('click', () => {
      showToast('🖼️', 'Galeri açılıyor...');
      // Simulate gallery pick
      setTimeout(() => {
        startScanning();
        setTimeout(() => {
          stopScanning();
          showToast('✅', 'Fiş başarıyla okundu! 12 ürün eklendi.');
        }, 3000);
      }, 500);
    });
  }

  if (btnManual) {
    btnManual.addEventListener('click', () => {
      openBottomSheet(`
        <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">Manuel Fiş Girişi</h3>
        <div style="display:flex;flex-direction:column;gap:12px;">
          <div>
            <label style="font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Market Adı</label>
            <input type="text" placeholder="Örn: Migros" style="width:100%;padding:12px 16px;background:var(--bg-primary);border:1px solid rgba(148,163,184,0.1);border-radius:var(--radius-md);color:var(--text-primary);font-size:0.9rem;outline:none;">
          </div>
          <div>
            <label style="font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Tarih</label>
            <input type="date" style="width:100%;padding:12px 16px;background:var(--bg-primary);border:1px solid rgba(148,163,184,0.1);border-radius:var(--radius-md);color:var(--text-primary);font-size:0.9rem;outline:none;">
          </div>
          <div>
            <label style="font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Toplam Tutar (₺)</label>
            <input type="number" placeholder="0.00" style="width:100%;padding:12px 16px;background:var(--bg-primary);border:1px solid rgba(148,163,184,0.1);border-radius:var(--radius-md);color:var(--text-primary);font-size:0.9rem;outline:none;">
          </div>
          <button class="btn btn-primary btn-block btn-lg" onclick="closeBottomSheet();showToast('✅','Fiş kaydedildi!');">
            💾 Kaydet
          </button>
        </div>
      `);
    });
  }

  if (scanArea) {
    scanArea.addEventListener('click', () => {
      if (!state.scanActive) {
        startScanning();
      }
    });
  }
}

function startScanning() {
  const scanArea = document.getElementById('scan-area');
  if (scanArea) {
    scanArea.classList.add('scanning');
    state.scanActive = true;

    // Update placeholder text
    const placeholder = scanArea.querySelector('.scan-placeholder');
    if (placeholder) {
      placeholder.querySelector('.scan-big-icon').textContent = '📡';
      placeholder.querySelector('p').textContent = 'Taranıyor...';
      placeholder.querySelector('.scan-hint').textContent = 'Lütfen fişi sabit tutun';
    }

    // Simulate scan completion
    setTimeout(() => {
      stopScanning();
      showToast('✅', 'Fiş başarıyla okundu! 12 ürün eklendi.');
    }, 4000);
  }
}

function stopScanning() {
  const scanArea = document.getElementById('scan-area');
  if (scanArea) {
    scanArea.classList.remove('scanning');
    state.scanActive = false;

    const placeholder = scanArea.querySelector('.scan-placeholder');
    if (placeholder) {
      placeholder.querySelector('.scan-big-icon').textContent = '📸';
      placeholder.querySelector('p').textContent = 'Fişi çerçeveye yerleştirin';
      placeholder.querySelector('.scan-hint').textContent = 'OCR ile otomatik okunacak';
    }
  }
}

// ============================================
// INVENTORY FILTERS
// ============================================
function initInventoryFilters() {
  const chips = document.querySelectorAll('.filter-chip[data-filter]');
  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      // Update active chip
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');

      const filter = chip.dataset.filter;
      state.inventoryFilter = filter;
      filterInventory(filter);
    });
  });
}

function filterInventory(category) {
  const cards = document.querySelectorAll('.inv-card[data-category]');
  cards.forEach(card => {
    if (category === 'all' || card.dataset.category === category) {
      card.style.display = '';
      card.style.animation = 'fadeInUp 0.3s ease';
    } else {
      card.style.display = 'none';
    }
  });
}

// ============================================
// INVENTORY SEARCH
// ============================================
function initInventorySearch() {
  const searchInput = document.getElementById('inventory-search');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      const query = e.target.value.toLowerCase().trim();
      const cards = document.querySelectorAll('.inv-card');
      cards.forEach(card => {
        const name = card.querySelector('.inv-name').textContent.toLowerCase();
        if (name.includes(query) || query === '') {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }
}

// ============================================
// SHOPPING LIST
// ============================================
function initShoppingList() {
  const addInput = document.getElementById('add-item-input');
  const addBtn = document.getElementById('btn-add-item');
  const shareBtn = document.getElementById('btn-share-list');

  if (addBtn && addInput) {
    const addItem = () => {
      const value = addInput.value.trim();
      if (value) {
        addShoppingItem(value);
        addInput.value = '';
        showToast('✅', `"${value}" listeye eklendi`);
      }
    };

    addBtn.addEventListener('click', addItem);
    addInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') addItem();
    });
  }

  if (shareBtn) {
    shareBtn.addEventListener('click', () => {
      if (navigator.share) {
        navigator.share({
          title: 'Evly Alışveriş Listesi',
          text: getShoppingListText()
        }).catch(() => {});
      } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(getShoppingListText()).then(() => {
          showToast('📋', 'Liste panoya kopyalandı');
        }).catch(() => {
          showToast('ℹ️', 'Paylaşım desteklenmiyor');
        });
      }
    });
  }
}

function addShoppingItem(name) {
  const list = document.getElementById('manual-list');
  if (!list) return;

  const item = document.createElement('div');
  item.className = 'shop-item';
  item.onclick = function() { toggleShopItem(this); };
  item.innerHTML = `
    <div class="shop-check">✓</div>
    <div class="shop-details">
      <div class="shop-name">${escapeHtml(name)}</div>
      <div class="shop-reason">Manuel eklendi</div>
    </div>
    <div class="shop-qty">×1</div>
  `;

  // Add with animation
  item.style.animation = 'fadeInUp 0.3s ease';
  list.insertBefore(item, list.firstChild);
}

function toggleShopItem(el) {
  el.classList.toggle('checked');
  
  // Haptic feedback (if available)
  if (navigator.vibrate) {
    navigator.vibrate(10);
  }
}

function getShoppingListText() {
  const items = document.querySelectorAll('.shop-item:not(.checked) .shop-name');
  let text = '🛒 Evly Alışveriş Listesi\n\n';
  items.forEach((item, i) => {
    text += `${i + 1}. ${item.textContent}\n`;
  });
  return text;
}

// ============================================
// NOTIFICATIONS
// ============================================
function initNotifications() {
  const notifBtn = document.getElementById('btn-notifications');
  if (notifBtn) {
    notifBtn.addEventListener('click', () => {
      openBottomSheet(`
        <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">Bildirimler</h3>
        <div style="display:flex;flex-direction:column;gap:12px;">
          <div class="card" style="display:flex;gap:12px;align-items:flex-start;">
            <div style="font-size:1.3rem;">⚠️</div>
            <div>
              <div style="font-size:0.85rem;font-weight:600;">Süt bitmek üzere</div>
              <div style="font-size:0.75rem;color:var(--text-secondary);">AI tahminine göre 2 gün içinde bitecek</div>
              <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;">10 dakika önce</div>
            </div>
          </div>
          <div class="card" style="display:flex;gap:12px;align-items:flex-start;">
            <div style="font-size:1.3rem;">🍞</div>
            <div>
              <div style="font-size:0.85rem;font-weight:600;">Ekmek yarın bitecek</div>
              <div style="font-size:0.75rem;color:var(--text-secondary);">Alışveriş listesine eklendi</div>
              <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;">1 saat önce</div>
            </div>
          </div>
          <div class="card" style="display:flex;gap:12px;align-items:flex-start;">
            <div style="font-size:1.3rem;">💡</div>
            <div>
              <div style="font-size:0.85rem;font-weight:600;">Haftalık rapor hazır</div>
              <div style="font-size:0.75rem;color:var(--text-secondary);">Bu hafta ₺847 harcadınız, ₺120 tasarruf ettiniz</div>
              <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;">3 saat önce</div>
            </div>
          </div>
        </div>
      `);
    });
  }

  const searchBtn = document.getElementById('btn-search');
  if (searchBtn) {
    searchBtn.addEventListener('click', () => {
      openBottomSheet(`
        <div class="search-bar" style="margin-bottom:16px;">
          <span class="search-icon">🔍</span>
          <input type="text" placeholder="Ürün, market veya kategori ara..." id="global-search" autofocus style="flex:1;background:none;border:none;outline:none;color:var(--text-primary);font-size:0.9rem;">
        </div>
        <div style="margin-bottom:12px;">
          <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Son Aramalar</div>
          <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <span class="filter-chip" style="cursor:pointer;">Süt</span>
            <span class="filter-chip" style="cursor:pointer;">Migros</span>
            <span class="filter-chip" style="cursor:pointer;">Temizlik</span>
            <span class="filter-chip" style="cursor:pointer;">Yumurta</span>
          </div>
        </div>
        <div>
          <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Popüler</div>
          <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <span class="filter-chip" style="cursor:pointer;">🥛 Süt Ürünleri</span>
            <span class="filter-chip" style="cursor:pointer;">🍎 Meyve</span>
            <span class="filter-chip" style="cursor:pointer;">🧹 Temizlik</span>
          </div>
        </div>
      `);

      // Focus search after sheet opens
      setTimeout(() => {
        const input = document.getElementById('global-search');
        if (input) input.focus();
      }, 400);
    });
  }
}

// ============================================
// BOTTOM SHEET
// ============================================
function openBottomSheet(content) {
  const overlay = document.getElementById('modal-overlay');
  const sheet = document.getElementById('bottom-sheet');
  const sheetContent = document.getElementById('sheet-content');

  if (sheetContent) sheetContent.innerHTML = content;
  if (overlay) overlay.classList.add('active');
  if (sheet) sheet.classList.add('active');

  // Close on overlay click
  if (overlay) {
    overlay.onclick = closeBottomSheet;
  }
}

function closeBottomSheet() {
  const overlay = document.getElementById('modal-overlay');
  const sheet = document.getElementById('bottom-sheet');

  if (overlay) overlay.classList.remove('active');
  if (sheet) sheet.classList.remove('active');
}

// ============================================
// TOAST
// ============================================
function showToast(icon, message) {
  const toast = document.getElementById('toast');
  const toastIcon = document.getElementById('toast-icon');
  const toastMsg = document.getElementById('toast-msg');

  if (toastIcon) toastIcon.textContent = icon;
  if (toastMsg) toastMsg.textContent = message;
  if (toast) {
    toast.classList.add('show');
    setTimeout(() => {
      toast.classList.remove('show');
    }, 3000);
  }
}

// ============================================
// SERVICE WORKER
// ============================================
function registerServiceWorker() {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
      .then(() => console.log('SW registered'))
      .catch(() => console.log('SW registration failed'));
  }
}

// ============================================
// UTILITIES
// ============================================
function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// Swipe down to close bottom sheet
let touchStartY = 0;
document.addEventListener('touchstart', (e) => {
  const sheet = document.getElementById('bottom-sheet');
  if (sheet && sheet.classList.contains('active')) {
    touchStartY = e.touches[0].clientY;
  }
});

document.addEventListener('touchmove', (e) => {
  const sheet = document.getElementById('bottom-sheet');
  if (sheet && sheet.classList.contains('active')) {
    const delta = e.touches[0].clientY - touchStartY;
    if (delta > 80) {
      closeBottomSheet();
    }
  }
});

// Keyboard shortcut (for desktop testing)
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeBottomSheet();
  }
});
