<?php
/**
 * Evly 2.0 — Akıllı Ev Yönetimi
 * Ana Router
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

$validPages = ['home', 'scan', 'inventory', 'shopping', 'analytics', 'profile', 'receipt-detail', 'meal-plan'];
$page = $_GET['page'] ?? 'home';
if (!in_array($page, $validPages)) $page = 'home';

require_once BASE_PATH . '/includes/header.php';

$pageFile = BASE_PATH . '/pages/' . $page . '.php';
if (file_exists($pageFile)) {
    require_once $pageFile;
} else {
    echo '<div class="empty-state"><div class="e-icon">🚧</div><h3>Sayfa bulunamadı</h3></div>';
}

require_once BASE_PATH . '/includes/footer.php';
