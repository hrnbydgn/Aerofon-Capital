/* ============================================
   EVLY - Akıllı Ev Yönetimi
   PHP Tabanlı Uygulama JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  initScanArea();
  initInventorySearch();
  initBottomSheets();
  initNotifications();
  initShareList();
  registerServiceWorker();
});

// ============================================
// SCAN AREA (Fiş Tarama Animasyonu)
// ============================================
function initScanArea() {
  const scanArea = document.getElementById('scan-area');
  const btnGallery = document.getElementById('btn-gallery');

  if (scanArea) {
    scanArea.addEventListener('click', () => {
      if (!scanArea.classList.contains('scanning')) {
        startScanAnimation(scanArea);
      }
    });
  }

  if (btnGallery) {
    btnGallery.addEventListener('click', () => {
      showToast('🖼️', 'Galeri açılıyor...');
      if (scanArea) {
        setTimeout(() => {
          startScanAnimation(scanArea);
          setTimeout(() => {
            stopScanAnimation(scanArea);
            showToast('✅', 'Fiş başarıyla okundu! Sayfa yenileniyor...');
            setTimeout(() => location.reload(), 1500);
          }, 3000);
        }, 500);
      }
    });
  }
}

function startScanAnimation(scanArea) {
  scanArea.classList.add('scanning');
  const placeholder = scanArea.querySelector('.scan-placeholder');
  if (placeholder) {
    placeholder.querySelector('.scan-big-icon').textContent = '📡';
    placeholder.querySelector('p').textContent = 'Taranıyor...';
    placeholder.querySelector('.scan-hint').textContent = 'Lütfen fişi sabit tutun';
  }
}

function stopScanAnimation(scanArea) {
  scanArea.classList.remove('scanning');
  const placeholder = scanArea.querySelector('.scan-placeholder');
  if (placeholder) {
    placeholder.querySelector('.scan-big-icon').textContent = '📸';
    placeholder.querySelector('p').textContent = 'Fişi çerçeveye yerleştirin';
    placeholder.querySelector('.scan-hint').textContent = 'OCR ile otomatik okunacak';
  }
}

// ============================================
// INVENTORY SEARCH (Client-side filter)
// ============================================
function initInventorySearch() {
  const searchInput = document.getElementById('inventory-search');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      const query = e.target.value.toLowerCase().trim();
      document.querySelectorAll('.inv-card').forEach(card => {
        const name = (card.dataset.name || card.querySelector('.inv-name')?.textContent || '').toLowerCase();
        card.style.display = name.includes(query) || query === '' ? '' : 'none';
      });
    });
  }
}

// ============================================
// BOTTOM SHEETS (Template Based)
// ============================================
function initBottomSheets() {
  // Manuel fiş girişi
  const btnManual = document.getElementById('btn-manual');
  if (btnManual) {
    btnManual.addEventListener('click', () => {
      const tpl = document.getElementById('tpl-manual-receipt');
      if (tpl) openBottomSheet(tpl.innerHTML);
    });
  }

  // Ürün ekleme
  const btnAddProduct = document.getElementById('btn-add-product');
  if (btnAddProduct) {
    btnAddProduct.addEventListener('click', () => {
      const tpl = document.getElementById('tpl-add-product');
      if (tpl) openBottomSheet(tpl.innerHTML);
    });
  }

  // Profil düzenleme
  const btnEditProfile = document.getElementById('btn-edit-profile');
  if (btnEditProfile) {
    btnEditProfile.addEventListener('click', () => {
      const tpl = document.getElementById('tpl-edit-profile');
      if (tpl) openBottomSheet(tpl.innerHTML);
    });
  }
}

// ============================================
// NOTIFICATIONS
// ============================================
function initNotifications() {
  const notifBtn = document.getElementById('btn-notifications');
  if (notifBtn) {
    notifBtn.addEventListener('click', () => {
      fetch('api/products.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) return;
          const lowItems = res.data.filter(p => p.pct <= 35);
          let html = '<h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">Bildirimler</h3>';
          html += '<div style="display:flex;flex-direction:column;gap:12px;">';
          if (lowItems.length === 0) {
            html += '<div class="empty-state" style="padding:16px;"><p>Yeni bildirim yok</p></div>';
          } else {
            lowItems.forEach(p => {
              html += `
                <div class="card" style="display:flex;gap:12px;align-items:flex-start;">
                  <div style="font-size:1.3rem;">${p.icon}</div>
                  <div>
                    <div style="font-size:0.85rem;font-weight:600;">${escapeHtml(p.name)} azalıyor</div>
                    <div style="font-size:0.75rem;color:var(--text-secondary);">AI tahminine göre kısa sürede bitecek. Stok: %${p.pct}</div>
                  </div>
                </div>`;
            });
          }
          html += '</div>';
          openBottomSheet(html);
        })
        .catch(() => {
          openBottomSheet('<h3 style="font-size:1.1rem;font-weight:700;margin-bottom:12px;">Bildirimler</h3><p class="text-muted text-sm">Bildirimler yüklenemedi.</p>');
        });
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
        <div id="search-results"></div>
        <div style="margin-bottom:12px;">
          <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Hızlı Erişim</div>
          <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <a href="?page=inventory&filter=dairy" class="filter-chip">🥛 Süt Ürünleri</a>
            <a href="?page=inventory&filter=fruits" class="filter-chip">🍎 Meyve</a>
            <a href="?page=inventory&filter=cleaning" class="filter-chip">🧹 Temizlik</a>
            <a href="?page=inventory&filter=meat" class="filter-chip">🥩 Et</a>
          </div>
        </div>
      `);
      setTimeout(() => {
        const input = document.getElementById('global-search');
        if (input) {
          input.focus();
          input.addEventListener('input', debounce((e) => {
            const q = e.target.value.trim();
            if (q.length < 2) {
              document.getElementById('search-results').innerHTML = '';
              return;
            }
            fetch(`api/products.php?search=${encodeURIComponent(q)}`)
              .then(r => r.json())
              .then(res => {
                const container = document.getElementById('search-results');
                if (!res.success || res.data.length === 0) {
                  container.innerHTML = '<p class="text-sm text-muted mb-3">Sonuç bulunamadı</p>';
                  return;
                }
                let html = '<div style="margin-bottom:12px;">';
                res.data.forEach(p => {
                  html += `<a href="?page=inventory" class="product-card" style="margin-bottom:6px;display:flex;">
                    <div class="product-icon" style="background:rgba(99,102,241,0.12)">${p.icon}</div>
                    <div class="product-info"><div class="product-name">${escapeHtml(p.name)}</div><div class="product-meta">${escapeHtml(p.cat_name || '')}</div></div>
                    <span class="status-badge ${p.pct <= 20 ? 'critical' : p.pct <= 45 ? 'warning' : 'good'}">%${p.pct}</span>
                  </a>`;
                });
                html += '</div>';
                container.innerHTML = html;
              });
          }, 300));
        }
      }, 400);
    });
  }
}

// ============================================
// SHARE LIST
// ============================================
function initShareList() {
  const shareBtn = document.getElementById('btn-share-list');
  if (shareBtn) {
    shareBtn.addEventListener('click', () => {
      const items = document.querySelectorAll('.shop-item:not(.checked) .shop-name');
      let text = '🛒 Evly Alışveriş Listesi\n\n';
      items.forEach((item, i) => {
        text += `${i + 1}. ${item.textContent}\n`;
      });

      if (navigator.share) {
        navigator.share({ title: 'Evly Alışveriş Listesi', text }).catch(() => {});
      } else {
        navigator.clipboard.writeText(text).then(() => {
          showToast('📋', 'Liste panoya kopyalandı');
        }).catch(() => {
          showToast('ℹ️', 'Paylaşım desteklenmiyor');
        });
      }
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

  if (overlay) overlay.onclick = closeBottomSheet;
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
    setTimeout(() => toast.classList.remove('show'), 3000);
  }
}

// ============================================
// SERVICE WORKER
// ============================================
function registerServiceWorker() {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js').catch(() => {});
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

function debounce(fn, delay) {
  let timer;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

// Swipe to close bottom sheet
let touchStartY = 0;
document.addEventListener('touchstart', e => {
  const sheet = document.getElementById('bottom-sheet');
  if (sheet?.classList.contains('active')) touchStartY = e.touches[0].clientY;
});
document.addEventListener('touchmove', e => {
  const sheet = document.getElementById('bottom-sheet');
  if (sheet?.classList.contains('active') && (e.touches[0].clientY - touchStartY) > 80) {
    closeBottomSheet();
  }
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeBottomSheet();
});
