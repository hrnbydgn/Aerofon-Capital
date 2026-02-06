<?php
/**
 * Evly - Fiş Tarama Sayfası
 */
$db = getDB();
$recentReceipts = $db->query("
    SELECT * FROM receipts ORDER BY scanned_at DESC LIMIT 5
")->fetchAll();

// Fiş ekleme işlemi
$message = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_receipt') {
        $store = trim($_POST['store_name'] ?? '');
        $branch = trim($_POST['store_branch'] ?? '');
        $amount = floatval($_POST['total_amount'] ?? 0);
        $date = $_POST['receipt_date'] ?? date('Y-m-d');
        $itemCount = intval($_POST['item_count'] ?? 0);

        if ($store && $amount > 0) {
            $stmt = $db->prepare("INSERT INTO receipts (store_name, store_branch, total_amount, item_count, ocr_accuracy, receipt_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$store, $branch, $amount, $itemCount, 0, $date . ' ' . date('H:i:s')]);
            $message = ['type' => 'success', 'text' => 'Fiş başarıyla kaydedildi!'];
            // Yeniden çek
            $recentReceipts = $db->query("SELECT * FROM receipts ORDER BY scanned_at DESC LIMIT 5")->fetchAll();
        } else {
            $message = ['type' => 'error', 'text' => 'Lütfen market adı ve tutarı girin.'];
        }
    }

    if ($_POST['action'] === 'simulate_scan') {
        // Simüle tarama
        $stores = ['Migros', 'BİM', 'A101', 'ŞOK', 'CarrefourSA'];
        $store = $stores[array_rand($stores)];
        $amount = rand(80, 600);
        $items = rand(5, 20);
        $accuracy = rand(92, 99);

        $stmt = $db->prepare("INSERT INTO receipts (store_name, store_branch, total_amount, item_count, ocr_accuracy, receipt_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$store, 'Tarama', $amount, $items, $accuracy, date('Y-m-d H:i:s')]);
        $message = ['type' => 'success', 'text' => "Fiş tarandı! $store - $items ürün, ₺$amount (OCR: %$accuracy)"];
        $recentReceipts = $db->query("SELECT * FROM receipts ORDER BY scanned_at DESC LIMIT 5")->fetchAll();
    }
}
?>

<?php if ($message): ?>
<script>document.addEventListener('DOMContentLoaded',()=>showToast('<?= $message['type'] === 'success' ? '✅' : '❌' ?>','<?= e($message['text']) ?>'));</script>
<?php endif; ?>

<div class="page-title">Fiş Tara</div>
<div class="page-subtitle">Kamera ile fişinizi tarayın veya galeriden yükleyin</div>

<!-- Tarama Alanı -->
<div class="scan-area" id="scan-area">
  <div class="scan-corners">
    <div class="scan-corner tl"></div>
    <div class="scan-corner tr"></div>
    <div class="scan-corner bl"></div>
    <div class="scan-corner br"></div>
  </div>
  <div class="scan-line"></div>
  <div class="scan-placeholder">
    <div class="scan-big-icon">📸</div>
    <p>Fişi çerçeveye yerleştirin</p>
    <div class="scan-hint">OCR ile otomatik okunacak</div>
  </div>
</div>

<!-- Tarama Butonları -->
<div class="scan-actions">
  <form method="post" style="flex:1">
    <input type="hidden" name="action" value="simulate_scan">
    <button type="submit" class="btn btn-primary btn-block" id="btn-camera">📷 Tara (Simüle)</button>
  </form>
  <button class="btn btn-secondary btn-block" id="btn-gallery" style="flex:1">🖼️ Galeriden Seç</button>
</div>

<!-- Manuel Giriş -->
<button class="btn btn-outline btn-block mb-4" id="btn-manual">✏️ Manuel Giriş</button>

<!-- Son Taramalar -->
<div class="section">
  <div class="section-header">
    <div class="section-title">
      <span class="section-icon">🕐</span>
      Son Taramalar
    </div>
    <span class="text-sm text-muted"><?= count($recentReceipts) ?> fiş</span>
  </div>
  <div class="receipt-list">
    <?php if (empty($recentReceipts)): ?>
      <div class="empty-state">
        <div class="empty-icon">🧾</div>
        <h3>Henüz fiş yok</h3>
        <p>İlk fişinizi tarayın veya manuel ekleyin</p>
      </div>
    <?php else: ?>
      <?php foreach ($recentReceipts as $r): ?>
      <div class="receipt-card">
        <div class="receipt-thumb">🧾</div>
        <div class="receipt-info">
          <div class="receipt-store"><?= e($r['store_name']) ?><?= $r['store_branch'] ? ' - ' . e($r['store_branch']) : '' ?></div>
          <div class="receipt-meta">
            <span><?= trDate($r['receipt_date']) ?></span>
            <span>•</span>
            <?php if ($r['ocr_accuracy'] > 0): ?>
              <span>OCR: %<?= (int)$r['ocr_accuracy'] ?></span>
            <?php else: ?>
              <span><?= $r['item_count'] ?> ürün</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="receipt-amount">₺<?= number_format($r['total_amount'], 0, ',', '.') ?></div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- OCR Bilgi -->
<div class="card card-glass mt-4">
  <div class="flex items-center gap-3">
    <div class="ai-icon">🧠</div>
    <div>
      <div style="font-size:0.85rem;font-weight:600;">Akıllı OCR Motoru</div>
      <div style="font-size:0.75rem;color:var(--text-secondary);">Fişleri otomatik tanıyıp ürünleri kategorize eder. Tarih analizi ile tüketim hızınızı hesaplar.</div>
    </div>
  </div>
</div>

<!-- Manuel Fiş Giriş Bottom Sheet (hidden form) -->
<template id="tpl-manual-receipt">
  <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">Manuel Fiş Girişi</h3>
  <form method="post" action="?page=scan">
    <input type="hidden" name="action" value="add_receipt">
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div>
        <label class="form-label">Market Adı</label>
        <input type="text" name="store_name" placeholder="Örn: Migros" class="form-input" required>
      </div>
      <div>
        <label class="form-label">Şube (Opsiyonel)</label>
        <input type="text" name="store_branch" placeholder="Örn: Ataşehir" class="form-input">
      </div>
      <div>
        <label class="form-label">Tarih</label>
        <input type="date" name="receipt_date" value="<?= date('Y-m-d') ?>" class="form-input">
      </div>
      <div>
        <label class="form-label">Ürün Sayısı</label>
        <input type="number" name="item_count" placeholder="0" min="0" class="form-input">
      </div>
      <div>
        <label class="form-label">Toplam Tutar (₺)</label>
        <input type="number" name="total_amount" placeholder="0.00" step="0.01" min="0" class="form-input" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">💾 Kaydet</button>
    </div>
  </form>
</template>
