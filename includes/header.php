<?php
$user = getDB()->query("SELECT * FROM users LIMIT 1")->fetch();
$notifCount = getDB()->query("SELECT COUNT(*) FROM products WHERE predicted_end <= date('now', '+3 days')")->fetchColumn();
$insightCount = getDB()->query("SELECT COUNT(*) FROM insights WHERE is_read = 0")->fetchColumn();
$totalNotif = $notifCount + $insightCount;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
  <meta name="theme-color" content="#f8f9fb" id="meta-theme">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="description" content="Evly — Evinizin akıllı yöneticisi. Fiş tarama, stok takibi, AI önerileri.">
  <title><?= e(APP_NAME) ?> — <?= e(APP_DESC) ?></title>
  <link rel="manifest" href="manifest.json">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <!-- Prevent flash: apply saved theme before render -->
  <script>
    (function(){
      var t = localStorage.getItem('evly-theme');
      if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.setAttribute('data-theme','dark');
      }
    })();
  </script>
</head>
<body>

  <header class="header">
    <div class="header-inner">
      <a href="?page=home" class="logo">
        <div class="logo-mark">E</div>
        <h1>Evly</h1>
      </a>
      <div class="header-actions">
        <button class="hdr-btn" id="btn-search" aria-label="Ara">🔍</button>
        <button class="hdr-btn" id="btn-notif" aria-label="Bildirimler">
          🔔
          <?php if ($totalNotif > 0): ?><span class="badge"><?= $totalNotif ?></span><?php endif; ?>
        </button>
        <button class="hdr-btn theme-toggle" id="btn-theme" aria-label="Tema değiştir">🌙</button>
      </div>
    </div>
  </header>

  <div class="toast" id="toast"><span id="toast-icon">✅</span><span id="toast-msg"></span></div>

  <main class="container anim-fade">
