<?php
/**
 * Evly — Fiş Detay Sayfası
 * Hangi fişten hangi ürünler tespit edildi, tek tek gösterilir
 */
$db = getDB();
$id = intval($_GET['id'] ?? 0);

if (!$id) { echo '<div class="empty-state"><div class="e-icon">🧾</div><h3>Fiş bulunamadı</h3></div>'; return; }

$receipt = $db->prepare("SELECT * FROM receipts WHERE id = ?");
$receipt->execute([$id]);
$receipt = $receipt->fetch();

if (!$receipt) { echo '<div class="empty-state"><div class="e-icon">🧾</div><h3>Fiş bulunamadı</h3></div>'; return; }

$items = $db->prepare("SELECT * FROM receipt_items WHERE receipt_id = ? ORDER BY id ASC");
$items->execute([$id]);
$items = $items->fetchAll();

$storeIcons = ['Migros'=>'🏪','BİM'=>'🏬','A101'=>'🛒','ŞOK'=>'🏪','CarrefourSA'=>'🛍️'];
$icon = $storeIcons[$receipt['store_name']] ?? '🏪';

// Kategorilere göre grupla
$grouped = [];
foreach ($items as $item) {
    $cat = $item['category_guess'] ?? 'Diğer';
    $grouped[$cat][] = $item;
}
?>

<div class="flex items-center gap-3 mb-3">
  <a href="?page=scan" class="btn btn-sm btn-secondary">← Geri</a>
  <div>
    <div class="pg-title" style="margin-bottom:0;"><?= e($receipt['store_name']) ?></div>
    <div class="pg-sub" style="margin-bottom:0;"><?= $receipt['store_branch'] ? e($receipt['store_branch']) . ' · ' : '' ?><?= trDateShort($receipt['receipt_date']) ?></div>
  </div>
</div>

<!-- Fiş Özet Kartı -->
<div class="card card-brand mb-4" style="border-radius:var(--radius-xl);">
  <div class="flex justify-between items-center">
    <div>
      <div style="font-size:0.72rem;opacity:0.85;">Toplam Tutar</div>
      <div style="font-size:1.6rem;font-weight:800;"><?= money($receipt['total_amount']) ?></div>
    </div>
    <div style="text-align:right;">
      <div style="font-size:2.2rem;"><?= $icon ?></div>
    </div>
  </div>
  <div class="flex gap-4 mt-3" style="opacity:0.9;">
    <div>
      <div style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.3px;">Ürün Sayısı</div>
      <div style="font-size:1rem;font-weight:700;"><?= count($items) ?></div>
    </div>
    <div>
      <div style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.3px;">OCR Doğruluk</div>
      <div style="font-size:1rem;font-weight:700;">%<?= (int)$receipt['ocr_accuracy'] ?></div>
    </div>
    <div>
      <div style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.3px;">Kategori</div>
      <div style="font-size:1rem;font-weight:700;"><?= count($grouped) ?></div>
    </div>
  </div>
</div>

<!-- AI Bilgi -->
<div class="ai-banner mb-4">
  <div class="ai-avatar bg-purple-l">🤖</div>
  <div class="ai-body">
    <div class="ai-label">OCR Analizi</div>
    <div class="ai-text"><?= count($items) ?> ürün fişten tespit edildi ve <?= count($grouped) ?> kategoriye ayrıldı.</div>
    <div class="ai-sub">Tüm ürünler stoklarınıza ve fiyat geçmişine otomatik eklendi.</div>
  </div>
</div>

<!-- Kategorilere Göre Ürünler -->
<?php foreach ($grouped as $catName => $catItems): ?>
<div class="section">
  <div class="sec-header">
    <div class="sec-title"><?= e($catName) ?></div>
    <span style="font-size:0.72rem;color:var(--text-muted);"><?= count($catItems) ?> ürün</span>
  </div>
  <div class="card" style="padding:8px;">
    <div class="ri-list">
      <?php $catTotal = 0; foreach ($catItems as $item): $catTotal += $item['total_price']; ?>
      <div class="ri-item">
        <div class="ri-name"><?= e($item['name']) ?></div>
        <div class="ri-qty"><?= $item['quantity'] ?> <?= e($item['unit']) ?></div>
        <div class="ri-price"><?= money($item['total_price']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="flex justify-between items-center mt-2" style="padding:6px 12px;">
      <span style="font-size:0.72rem;color:var(--text-muted);">Kategori Toplamı</span>
      <span style="font-size:0.88rem;font-weight:700;color:var(--brand);"><?= money($catTotal) ?></span>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- Tüm Ürünler Listesi (düz) -->
<div class="section">
  <div class="sec-header">
    <div class="sec-title">📋 Tüm Kalemler</div>
  </div>
  <div class="card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
      <thead>
        <tr style="background:var(--bg-body);">
          <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--text-secondary);font-size:0.72rem;">ÜRÜN</th>
          <th style="padding:10px 8px;text-align:center;font-weight:600;color:var(--text-secondary);font-size:0.72rem;">MİKTAR</th>
          <th style="padding:10px 8px;text-align:right;font-weight:600;color:var(--text-secondary);font-size:0.72rem;">BİRİM ₺</th>
          <th style="padding:10px 14px;text-align:right;font-weight:600;color:var(--text-secondary);font-size:0.72rem;">TOPLAM</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $item): ?>
        <tr style="border-bottom:1px solid var(--bg-body);<?= $i % 2 === 0 ? '' : 'background:var(--bg-body);' ?>">
          <td style="padding:10px 14px;font-weight:500;"><?= e($item['name']) ?></td>
          <td style="padding:10px 8px;text-align:center;color:var(--text-muted);"><?= $item['quantity'] ?> <?= e($item['unit']) ?></td>
          <td style="padding:10px 8px;text-align:right;color:var(--text-muted);"><?= money($item['unit_price']) ?></td>
          <td style="padding:10px 14px;text-align:right;font-weight:600;"><?= money($item['total_price']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr style="background:var(--brand-lighter);">
          <td colspan="3" style="padding:12px 14px;font-weight:700;color:var(--brand);">TOPLAM</td>
          <td style="padding:12px 14px;text-align:right;font-weight:700;color:var(--brand);font-size:0.95rem;"><?= money($receipt['total_amount']) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
