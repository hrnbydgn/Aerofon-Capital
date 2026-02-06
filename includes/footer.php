<?php
/**
 * Evly - Alt Footer / Navigasyon Bileşeni
 */
$currentPage = $page ?? 'home';
?>
  </main>

  <!-- Bottom Navigation -->
  <nav class="bottom-nav">
    <div class="bottom-nav-inner">
      <a href="?page=home" class="nav-item <?= $currentPage === 'home' ? 'active' : '' ?>">
        <span class="nav-icon">🏠</span>
        <span class="nav-label">Ana Sayfa</span>
      </a>
      <a href="?page=inventory" class="nav-item <?= $currentPage === 'inventory' ? 'active' : '' ?>">
        <span class="nav-icon">📦</span>
        <span class="nav-label">Envanter</span>
      </a>
      <a href="?page=scan" class="nav-item nav-scan <?= $currentPage === 'scan' ? 'active' : '' ?>">
        <div class="nav-icon-wrap">📷</div>
        <span class="nav-label">Tara</span>
      </a>
      <a href="?page=shopping" class="nav-item <?= $currentPage === 'shopping' ? 'active' : '' ?>">
        <span class="nav-icon">🛒</span>
        <span class="nav-label">Liste</span>
      </a>
      <a href="?page=profile" class="nav-item <?= $currentPage === 'profile' ? 'active' : '' ?>">
        <span class="nav-icon">👤</span>
        <span class="nav-label">Profil</span>
      </a>
    </div>
  </nav>

  <!-- Modal Overlay -->
  <div class="modal-overlay" id="modal-overlay"></div>

  <!-- Bottom Sheet -->
  <div class="bottom-sheet" id="bottom-sheet">
    <div class="sheet-handle"></div>
    <div id="sheet-content"></div>
  </div>

  <script src="assets/js/app.js"></script>
</body>
</html>
