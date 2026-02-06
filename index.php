<?php
/**
 * Evly - Akıllı Ev Yönetimi
 * Ana Router (SPA benzeri PHP)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// Geçerli sayfalar
$validPages = ['home', 'scan', 'inventory', 'shopping', 'analytics', 'profile'];
$page = $_GET['page'] ?? 'home';

if (!in_array($page, $validPages)) {
    $page = 'home';
}

// Header
require_once BASE_PATH . '/includes/header.php';

// Sayfa içeriği
$pageFile = BASE_PATH . '/pages/' . $page . '.php';
if (file_exists($pageFile)) {
    require_once $pageFile;
} else {
    echo '<div class="empty-state"><div class="empty-icon">🚧</div><h3>Sayfa bulunamadı</h3><p>Aradığınız sayfa mevcut değil.</p></div>';
}

// Footer
require_once BASE_PATH . '/includes/footer.php';
