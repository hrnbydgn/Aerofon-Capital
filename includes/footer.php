<?php $cp = $page ?? 'home'; ?>
  </main>

  <nav class="btm-nav">
    <div class="btm-nav-inner">
      <a href="?page=home" class="nav-item <?= $cp === 'home' ? 'active' : '' ?>">
        <span class="nav-icon">🏠</span><span class="nav-label">Ana Sayfa</span>
      </a>
      <a href="?page=inventory" class="nav-item <?= $cp === 'inventory' ? 'active' : '' ?>">
        <span class="nav-icon">📦</span><span class="nav-label">Envanter</span>
      </a>
      <a href="?page=scan" class="nav-item nav-scan">
        <div class="scan-btn">📷</div><span class="nav-label">Tara</span>
      </a>
      <a href="?page=shopping" class="nav-item <?= $cp === 'shopping' ? 'active' : '' ?>">
        <span class="nav-icon">🛒</span><span class="nav-label">Liste</span>
      </a>
      <a href="?page=profile" class="nav-item <?= $cp === 'profile' ? 'active' : '' ?>">
        <span class="nav-icon">👤</span><span class="nav-label">Profil</span>
      </a>
    </div>
  </nav>

  <div class="modal-overlay" id="modal-overlay"></div>
  <div class="btm-sheet" id="btm-sheet"><div class="sheet-handle"></div><div id="sheet-content"></div></div>

  <script src="assets/js/app.js"></script>
</body>
</html>
