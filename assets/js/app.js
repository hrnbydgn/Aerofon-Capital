/* ============================================
   EVLY 2.0 — Akıllı Ev Yönetimi
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  initScan();
  initInventorySearch();
  initBottomSheets();
  initNotifications();
  initShareList();
  registerSW();
});

// === Scan ===
function initScan() {
  const zone = document.getElementById('scan-area');
  if (zone) zone.addEventListener('click', () => {
    if (!zone.classList.contains('scanning')) {
      zone.classList.add('scanning');
      const ph = zone.querySelector('.scan-ph');
      if (ph) { ph.querySelector('.s-icon').textContent = '📡'; ph.querySelector('p').textContent = 'Taranıyor...'; ph.querySelector('.s-hint').textContent = 'Lütfen fişi sabit tutun'; }
      setTimeout(() => {
        zone.classList.remove('scanning');
        if (ph) { ph.querySelector('.s-icon').textContent = '📸'; ph.querySelector('p').textContent = 'Fişi çerçeveye yerleştirin'; ph.querySelector('.s-hint').textContent = 'AI ile otomatik ürün tespiti yapılacak'; }
        showToast('✅', 'Tarama tamamlandı');
      }, 3500);
    }
  });
}

// === Inventory Search ===
function initInventorySearch() {
  const input = document.getElementById('inventory-search');
  if (input) input.addEventListener('input', e => {
    const q = e.target.value.toLowerCase().trim();
    document.querySelectorAll('.inv-card').forEach(c => {
      c.style.display = (c.dataset.name || '').includes(q) || !q ? '' : 'none';
    });
  });
}

// === Bottom Sheets ===
function initBottomSheets() {
  const map = { 'btn-manual': 'tpl-manual-receipt', 'btn-add-product': 'tpl-add-product', 'btn-edit-profile': 'tpl-edit-profile' };
  Object.entries(map).forEach(([btnId, tplId]) => {
    const btn = document.getElementById(btnId);
    const tpl = document.getElementById(tplId);
    if (btn && tpl) btn.addEventListener('click', () => openSheet(tpl.innerHTML));
  });
}

// === Notifications ===
function initNotifications() {
  const btn = document.getElementById('btn-notif');
  if (btn) btn.addEventListener('click', () => {
    fetch('api/products.php')
      .then(r => r.json())
      .then(res => {
        if (!res.success) return;
        const low = res.data.filter(p => p.pct <= 40);
        let h = '<h3 style="font-size:1.1rem;font-weight:700;margin-bottom:14px;">🔔 Bildirimler</h3><div style="display:flex;flex-direction:column;gap:10px;">';
        if (!low.length) { h += '<p style="color:var(--text-muted);text-align:center;padding:20px;">Yeni bildirim yok ✅</p>'; }
        else low.forEach(p => {
          h += `<div class="ai-banner" style="margin:0;border-left-color:${p.pct<=20?'var(--accent-red)':'var(--accent-amber)'}">
            <div style="font-size:1.3rem;">${p.icon}</div>
            <div class="ai-body"><div class="ai-text" style="font-size:0.84rem;">${esc(p.name)} azalıyor</div>
            <div class="ai-sub">Stok: %${p.pct} — AI tahminine göre kısa sürede bitecek</div></div></div>`;
        });
        h += '</div>';
        openSheet(h);
      }).catch(() => openSheet('<p style="text-align:center;padding:20px;">Bildirimler yüklenemedi</p>'));
  });

  const searchBtn = document.getElementById('btn-search');
  if (searchBtn) searchBtn.addEventListener('click', () => {
    openSheet(`
      <div class="search-bar" style="margin-bottom:14px;"><span class="s-icon">🔍</span>
        <input type="text" placeholder="Ürün, market veya kategori ara..." id="g-search" autofocus style="flex:1;background:none;border:none;outline:none;color:var(--text-primary);font-size:0.88rem;">
      </div>
      <div id="s-results"></div>
      <div style="margin-top:10px;"><div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px;">Hızlı Erişim</div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
          <a href="?page=inventory&filter=dairy" class="chip">🥛 Süt Ürünleri</a>
          <a href="?page=inventory&filter=fruits" class="chip">🍎 Meyve</a>
          <a href="?page=inventory&filter=cleaning" class="chip">🧹 Temizlik</a>
          <a href="?page=inventory&filter=meat" class="chip">🥩 Et</a>
        </div>
      </div>`);
    setTimeout(() => {
      const inp = document.getElementById('g-search');
      if (inp) { inp.focus(); inp.addEventListener('input', debounce(e => {
        const q = e.target.value.trim();
        const c = document.getElementById('s-results');
        if (q.length < 2) { c.innerHTML = ''; return; }
        fetch(`api/products.php?search=${encodeURIComponent(q)}`).then(r=>r.json()).then(res => {
          if (!res.success || !res.data.length) { c.innerHTML = '<p style="font-size:0.82rem;color:var(--text-muted);padding:10px;">Sonuç bulunamadı</p>'; return; }
          let h = '';
          res.data.forEach(p => {
            const lv = p.pct <= 20 ? 'critical' : p.pct <= 40 ? 'warning' : 'good';
            h += `<a href="?page=inventory" class="prod-card" style="margin-bottom:6px;text-decoration:none;">
              <div class="prod-icon bg-brand-l">${p.icon}</div>
              <div class="prod-info"><div class="prod-name">${esc(p.name)}</div><div class="prod-meta">${esc(p.cat_name||'')}</div></div>
              <span class="prod-badge ${lv}">%${p.pct}</span></a>`;
          });
          c.innerHTML = h;
        });
      }, 300)); }
    }, 400);
  });
}

// === Share List ===
function initShareList() {
  const btn = document.getElementById('btn-share-list');
  if (btn) btn.addEventListener('click', () => {
    const items = document.querySelectorAll('.shop-item:not(.checked) .si-name');
    let t = '🛒 Evly Alışveriş Listesi\n\n';
    items.forEach((el, i) => t += `${i+1}. ${el.textContent}\n`);
    if (navigator.share) navigator.share({ title: 'Evly Alışveriş Listesi', text: t }).catch(()=>{});
    else navigator.clipboard.writeText(t).then(() => showToast('📋','Liste panoya kopyalandı')).catch(()=>{});
  });
}

// === Sheet ===
function openSheet(html) {
  const o = document.getElementById('modal-overlay'), s = document.getElementById('btm-sheet'), c = document.getElementById('sheet-content');
  if (c) c.innerHTML = html;
  if (o) { o.classList.add('active'); o.onclick = closeSheet; }
  if (s) s.classList.add('active');
}
function closeSheet() {
  document.getElementById('modal-overlay')?.classList.remove('active');
  document.getElementById('btm-sheet')?.classList.remove('active');
}

// === Toast ===
function showToast(icon, msg) {
  const t = document.getElementById('toast');
  if (!t) return;
  document.getElementById('toast-icon').textContent = icon;
  document.getElementById('toast-msg').textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

// === SW ===
function registerSW() { if ('serviceWorker' in navigator) navigator.serviceWorker.register('sw.js').catch(()=>{}); }

// === Utils ===
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

// Swipe & ESC
let ty = 0;
document.addEventListener('touchstart', e => { if (document.getElementById('btm-sheet')?.classList.contains('active')) ty = e.touches[0].clientY; });
document.addEventListener('touchmove', e => { if (document.getElementById('btm-sheet')?.classList.contains('active') && e.touches[0].clientY - ty > 80) closeSheet(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSheet(); });
