<?php
/**
 * Evly - Üst Header Bileşeni
 */
$user = getDB()->query("SELECT * FROM users LIMIT 1")->fetch();
$notifCount = getDB()->query("SELECT COUNT(*) FROM products WHERE predicted_end <= date('now', '+3 days')")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
  <meta name="theme-color" content="#0f172a">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="description" content="Evly - Akıllı Ev Yönetimi. Alışveriş fişlerini tara, tüketimi analiz et, akıllı alışveriş listeleri oluştur.">
  <title><?= e(APP_NAME) ?> - <?= e(APP_DESC) ?></title>
  <link rel="manifest" href="manifest.json">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <header class="app-header">
    <div class="header-inner">
      <a href="?page=home" class="header-logo">
        <div class="logo-icon">🏠</div>
        <h1>Evly</h1>
      </a>
      <div class="header-actions">
        <button class="header-btn" id="btn-search" aria-label="Ara">🔍</button>
        <button class="header-btn" id="btn-notifications" aria-label="Bildirimler">
          🔔
          <?php if ($notifCount > 0): ?>
            <span class="badge"><?= $notifCount ?></span>
          <?php endif; ?>
        </button>
      </div>
    </div>
  </header>

  <div class="toast" id="toast">
    <span id="toast-icon">✅</span>
    <span id="toast-msg">İşlem başarılı</span>
  </div>

  <main class="app-container">
