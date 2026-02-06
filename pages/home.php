<?php
/**
 * Evly - Ana Sayfa / Dashboard
 */
$db = getDB();
$user = $db->query("SELECT * FROM users LIMIT 1")->fetch();

// İstatistikler
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStock = $db->query("SELECT COUNT(*) FROM products WHERE (quantity / max_quantity) <= 0.35")->fetchColumn();
$monthlySpend = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM receipts WHERE receipt_date >= date('now', 'start of month')")->fetchColumn();

// Azalan ürünler
$lowProducts = $db->query("
    SELECT p.*, c.slug AS cat_slug, c.name AS cat_name,
           ROUND((p.quantity / p.max_quantity) * 100) AS pct
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE (p.quantity / p.max_quantity) <= 0.45
    ORDER BY (p.quantity / p.max_quantity) ASC
    LIMIT 5
")->fetchAll();

// Son fişler
$recentReceipts = $db->query("
    SELECT * FROM receipts ORDER BY receipt_date DESC LIMIT 3
")->fetchAll();

$storeIcons = ['Migros' => '🏪', 'BİM' => '🏬', 'A101' => '🛒', 'ŞOK' => '🏪', 'CarrefourSA' => '🛍️'];
?>

<!-- Hero Card -->
<div class="hero-card">
  <div class="hero-content">
    <h2>Merhaba, <?= e($user['name'] ?? 'Kullanıcı') ?> 👋</h2>
    <p>Evinizin stok durumu özetine göz atın</p>
    <div class="hero-stats">
      <div class="hero-stat">
        <span class="stat-value"><?= $totalProducts ?></span>
        <span class="stat-label">Ürün</span>
      </div>
      <div class="hero-stat">
        <span class="stat-value"><?= $lowStock ?></span>
        <span class="stat-label">Azalan</span>
      </div>
      <div class="hero-stat">
        <span class="stat-value">₺<?= number_format($monthlySpend / 1000, 1) ?>k</span>
        <span class="stat-label">Bu Ay</span>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
  <a href="?page=scan" class="quick-action">
    <div class="qa-icon purple">📷</div>
    <span class="qa-label">Fiş Tara</span>
  </a>
  <a href="?page=shopping" class="quick-action">
    <div class="qa-icon cyan">📝</div>
    <span class="qa-label">Liste</span>
  </a>
  <a href="?page=inventory" class="quick-action">
    <div class="qa-icon green">📦</div>
    <span class="qa-label">Envanter</span>
  </a>
  <a href="?page=analytics" class="quick-action">
    <div class="qa-icon amber">📊</div>
    <span class="qa-label">Analiz</span>
  </a>
</div>

<!-- AI Prediction Banner -->
<div class="section">
  <div class="shopping-ai-banner">
    <div class="ai-icon ai-pulse">🤖</div>
    <div class="ai-text">
      <div class="ai-title">AI Tahmin Motoru Aktif</div>
      <div class="ai-desc"><?= $lowStock ?> ürün bu hafta bitebilir. Alışveriş listesine eklendi.</div>
    </div>
    <a href="?page=shopping" class="btn btn-outline" style="padding:8px 12px;font-size:0.75rem;">Gör</a>
  </div>
</div>

<!-- Azalan Ürünler -->
<div class="section">
  <div class="section-header">
    <div class="section-title">
      <span class="section-icon">⚠️</span>
      Azalan Ürünler
    </div>
    <a href="?page=inventory" class="section-link">Tümü →</a>
  </div>
  <div class="product-list">
    <?php foreach ($lowProducts as $p):
      $pct = (int)$p['pct'];
      $level = statusLevel($pct);
      $daysStr = daysLeft($p['predicted_end']);
    ?>
    <div class="product-card">
      <div class="product-icon <?= $level ?>"><?= $p['icon'] ?></div>
      <div class="product-info">
        <div class="product-name"><?= e($p['name']) ?></div>
        <div class="product-meta">
          <span>Son alım: <?= trDate($p['last_purchased']) ?></span>
        </div>
      </div>
      <div class="product-status">
        <span class="status-badge <?= $level ?>"><?= e($daysStr) ?></span>
        <div class="progress-mini">
          <div class="progress-fill <?= $level ?>" style="width:<?= $pct ?>%"></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Haftalık Özet -->
<div class="section">
  <div class="section-header">
    <div class="section-title">
      <span class="section-icon">📈</span>
      Haftalık Özet
    </div>
    <a href="?page=analytics" class="section-link">Detay →</a>
  </div>
  <?php
    $weeklyReceipts = $db->query("SELECT COUNT(*) FROM receipts WHERE receipt_date >= date('now', '-7 days')")->fetchColumn();
    $weeklySpend = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM receipts WHERE receipt_date >= date('now', '-7 days')")->fetchColumn();
    $weeklyItems = $db->query("SELECT COALESCE(SUM(item_count), 0) FROM receipts WHERE receipt_date >= date('now', '-7 days')")->fetchColumn();
  ?>
  <div class="scroll-row">
    <div class="scroll-card">
      <div class="sc-icon">🛒</div>
      <div class="sc-value"><?= $weeklyReceipts ?></div>
      <div class="sc-label">Alışveriş</div>
    </div>
    <div class="scroll-card">
      <div class="sc-icon">💰</div>
      <div class="sc-value">₺<?= number_format($weeklySpend, 0, ',', '.') ?></div>
      <div class="sc-label">Harcama</div>
    </div>
    <div class="scroll-card">
      <div class="sc-icon">📦</div>
      <div class="sc-value"><?= $weeklyItems ?></div>
      <div class="sc-label">Ürün Alındı</div>
    </div>
    <div class="scroll-card">
      <div class="sc-icon">🤖</div>
      <div class="sc-value">%92</div>
      <div class="sc-label">AI Doğruluk</div>
    </div>
    <div class="scroll-card">
      <div class="sc-icon">💡</div>
      <div class="sc-value">₺120</div>
      <div class="sc-label">Tasarruf</div>
    </div>
  </div>
</div>

<!-- Son Fişler -->
<div class="section">
  <div class="section-header">
    <div class="section-title">
      <span class="section-icon">🧾</span>
      Son Fişler
    </div>
    <a href="?page=scan" class="section-link">Tümü →</a>
  </div>
  <div class="receipt-list">
    <?php foreach ($recentReceipts as $r):
      $icon = $storeIcons[$r['store_name']] ?? '🏪';
    ?>
    <div class="receipt-card">
      <div class="receipt-thumb"><?= $icon ?></div>
      <div class="receipt-info">
        <div class="receipt-store"><?= e($r['store_name']) ?></div>
        <div class="receipt-meta">
          <span><?= trDate($r['receipt_date']) ?></span>
          <span>•</span>
          <span><?= $r['item_count'] ?> ürün</span>
        </div>
      </div>
      <div class="receipt-amount">₺<?= number_format($r['total_amount'], 0, ',', '.') ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
