<?php
/**
 * Evly - Analiz / İstatistik Sayfası
 */
$db = getDB();

// Aylık harcamalar (son 6 ay simülasyonu)
$months = [];
$monthNames = ['Oca','Şub','Mar','Nis','May','Haz','Tem','Ağu','Eyl','Eki','Kas','Ara'];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $monthNum = (int)date('n', strtotime("-$i months"));
    $stmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM receipts WHERE strftime('%Y-%m', receipt_date) = ?");
    $stmt->execute([$date]);
    $total = $stmt->fetchColumn();
    // Geçmiş aylar için simüle veri
    if ($total == 0 && $i > 0) {
        $total = rand(1200, 3500);
    }
    $months[] = [
        'label' => $monthNames[$monthNum - 1],
        'value' => $total,
        'current' => $i === 0,
    ];
}
$maxMonth = max(array_column($months, 'value')) ?: 1;

// Kategori bazlı harcama (simülasyon)
$categorySpend = [
    ['name' => 'Süt Ürünleri', 'pct' => 30, 'color' => '#6366f1'],
    ['name' => 'Et & Tavuk', 'pct' => 22, 'color' => '#06b6d4'],
    ['name' => 'Meyve/Sebze', 'pct' => 18, 'color' => '#10b981'],
    ['name' => 'Temizlik', 'pct' => 15, 'color' => '#f59e0b'],
    ['name' => 'Diğer', 'pct' => 15, 'color' => '#ef4444'],
];

// En hızlı tüketilen ürünler
$fastConsumed = $db->query("
    SELECT p.*, c.name AS cat_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.avg_consumption_days ASC
    LIMIT 5
")->fetchAll();

// AI Öngörüler
$insights = $db->query("SELECT * FROM insights ORDER BY created_at DESC")->fetchAll();

// Toplam istatistikler
$totalReceipts = $db->query("SELECT COUNT(*) FROM receipts")->fetchColumn();
$totalSpend = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM receipts")->fetchColumn();
$avgReceipt = $totalReceipts > 0 ? $totalSpend / $totalReceipts : 0;
?>

<div class="page-title">Analiz</div>
<div class="page-subtitle">Tüketim trendleri ve harcama analizi</div>

<!-- Genel İstatistikler -->
<div class="scroll-row mb-4">
  <div class="scroll-card">
    <div class="sc-icon">🧾</div>
    <div class="sc-value"><?= $totalReceipts ?></div>
    <div class="sc-label">Toplam Fiş</div>
  </div>
  <div class="scroll-card">
    <div class="sc-icon">💰</div>
    <div class="sc-value">₺<?= number_format($totalSpend, 0, ',', '.') ?></div>
    <div class="sc-label">Toplam Harcama</div>
  </div>
  <div class="scroll-card">
    <div class="sc-icon">📊</div>
    <div class="sc-value">₺<?= number_format($avgReceipt, 0, ',', '.') ?></div>
    <div class="sc-label">Ort. Fiş</div>
  </div>
  <div class="scroll-card">
    <div class="sc-icon">🤖</div>
    <div class="sc-value">%92</div>
    <div class="sc-label">AI Doğruluk</div>
  </div>
</div>

<!-- Aylık Harcama Grafiği -->
<div class="chart-card">
  <div class="chart-title">Aylık Harcama</div>
  <div class="chart-subtitle">Son 6 ay harcama dağılımı</div>
  <div class="chart-area">
    <div class="bar-chart">
      <?php foreach ($months as $m):
        $heightPct = $maxMonth > 0 ? round(($m['value'] / $maxMonth) * 90) : 5;
      ?>
      <div class="bar-col">
        <div class="bar-value">₺<?= number_format($m['value'], 0, ',', '.') ?></div>
        <div class="bar <?= $m['current'] ? 'cyan' : 'purple' ?>" style="height:<?= $heightPct ?>%"></div>
        <div class="bar-label"><?= $m['label'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Kategori Dağılımı -->
<div class="chart-card">
  <div class="chart-title">Kategori Dağılımı</div>
  <div class="chart-subtitle">Bu ayki harcamaların dağılımı</div>
  <div class="donut-wrapper">
    <svg class="donut-chart" viewBox="0 0 36 36">
      <?php
        $offset = 25;
        foreach ($categorySpend as $cat) {
          echo '<circle cx="18" cy="18" r="14" fill="none" stroke-width="4" '
             . 'stroke="' . $cat['color'] . '" '
             . 'stroke-dasharray="' . ($cat['pct'] * 0.88) . ' ' . (88 - $cat['pct'] * 0.88) . '" '
             . 'stroke-dashoffset="' . $offset . '"></circle>';
          $offset -= $cat['pct'] * 0.88;
        }
      ?>
    </svg>
    <div class="donut-legend">
      <?php foreach ($categorySpend as $cat): ?>
      <div class="legend-item">
        <div class="legend-dot" style="background:<?= $cat['color'] ?>"></div>
        <span><?= e($cat['name']) ?> %<?= $cat['pct'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Tüketim Hızı -->
<div class="chart-card">
  <div class="chart-title">Tüketim Hızı</div>
  <div class="chart-subtitle">En hızlı tüketilen ürünler</div>
  <div class="product-list">
    <?php
      $speedColors = ['#818cf8', '#22d3ee', '#34d399', '#fbbf24', '#f87171'];
      foreach ($fastConsumed as $i => $p):
        $color = $speedColors[$i % count($speedColors)];
    ?>
    <div class="product-card" style="border:none;padding:10px 0;">
      <div class="product-icon" style="background:<?= $color ?>20"><?= $p['icon'] ?></div>
      <div class="product-info">
        <div class="product-name"><?= e($p['name']) ?></div>
        <div class="product-meta">Ortalama: <?= (int)$p['avg_consumption_days'] ?> günde tüketim</div>
      </div>
      <div style="font-size:0.8rem;font-weight:600;color:<?= $color ?>;"><?= (int)$p['avg_consumption_days'] ?> gün</div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- AI Öngörüleri -->
<div class="section">
  <div class="section-header">
    <div class="section-title">
      <span class="section-icon">💡</span>
      AI Öngörüleri
    </div>
  </div>
  <div class="insight-cards">
    <?php foreach ($insights as $ins): ?>
    <div class="insight-card">
      <div class="insight-icon <?= e($ins['type']) ?>"><?= $ins['icon'] ?></div>
      <div class="insight-text">
        <div class="insight-title"><?= e($ins['title']) ?></div>
        <div class="insight-desc"><?= e($ins['description']) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
