<?php
$db = getDB();

$message = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_receipt') {
        $store = trim($_POST['store_name'] ?? '');
        $branch = trim($_POST['store_branch'] ?? '');
        $amount = floatval($_POST['total_amount'] ?? 0);
        $date = $_POST['receipt_date'] ?? date('Y-m-d');
        if ($store && $amount > 0) {
            $stmt = $db->prepare("INSERT INTO receipts (store_name, store_branch, total_amount, item_count, ocr_accuracy, receipt_date) VALUES (?,?,?,0,0,?)");
            $stmt->execute([$store, $branch, $amount, $date . ' ' . date('H:i:s')]);
            $message = ['type'=>'success','text'=>'Fiş kaydedildi!'];
        }
    }
    if ($_POST['action'] === 'simulate_scan') {
        $stores = [
            ['Migros','Ataşehir',[['Süt (1L)',2,'litre',32.50,65.00,'Süt Ürünleri'],['Ekmek',1,'adet',12.00,12.00,'Fırın & Unlu'],['Domates',1.5,'kg',34.90,52.35,'Meyve & Sebze'],['Muz',1.2,'kg',49.90,59.88,'Meyve & Sebze']]],
            ['BİM','Kadıköy',[['Yumurta (30lu)',1,'kutu',99.90,99.90,'Süt Ürünleri'],['Makarna (500g)',3,'paket',15.50,46.50,'Temel Gıda'],['Soğan',2,'kg',18.50,37.00,'Meyve & Sebze']]],
            ['A101','Üsküdar',[['Tavuk Göğüs',1.5,'kg',149.90,224.85,'Et & Protein'],['Pirinç (1kg)',1,'kg',39.90,39.90,'Temel Gıda'],['Tereyağı (250g)',1,'adet',69.90,69.90,'Süt Ürünleri']]],
        ];
        $s = $stores[array_rand($stores)];
        $total = array_sum(array_column($s[2], 4));
        $accuracy = rand(93,99);
        $stmt = $db->prepare("INSERT INTO receipts (store_name, store_branch, total_amount, item_count, ocr_accuracy, receipt_date) VALUES (?,?,?,?,?,datetime('now'))");
        $stmt->execute([$s[0], $s[1], $total, count($s[2]), $accuracy]);
        $rid = $db->lastInsertId();
        $stmtI = $db->prepare("INSERT INTO receipt_items (receipt_id, name, quantity, unit, unit_price, total_price, category_guess) VALUES (?,?,?,?,?,?,?)");
        foreach ($s[2] as $item) $stmtI->execute([$rid, $item[0], $item[1], $item[2], $item[3], $item[4], $item[5]]);
        $message = ['type'=>'success','text'=>$s[0]." fişi tarandı! ".count($s[2])." ürün tespit edildi (OCR: %$accuracy)"];
    }
}

$recentReceipts = $db->query("SELECT r.*, (SELECT COUNT(*) FROM receipt_items WHERE receipt_id = r.id) AS real_items FROM receipts r ORDER BY receipt_date DESC LIMIT 8")->fetchAll();
$storeIcons = ['Migros'=>'🏪','BİM'=>'🏬','A101'=>'🛒','ŞOK'=>'🏪','CarrefourSA'=>'🛍️'];
?>

<?php if ($message): ?>
<script>document.addEventListener('DOMContentLoaded',()=>showToast('<?= $message['type']==='success'?'✅':'❌' ?>','<?= e($message['text']) ?>'));</script>
<?php endif; ?>

<div class="pg-title">Fiş Tara</div>
<div class="pg-sub">Kamera ile tarayın, AI ürünleri otomatik tespit etsin</div>

<!-- Tarama Alanı -->
<div class="scan-zone" id="scan-area">
  <div class="scan-corners">
    <div class="scan-corner tl"></div><div class="scan-corner tr"></div>
    <div class="scan-corner bl"></div><div class="scan-corner br"></div>
  </div>
  <div class="scan-line"></div>
  <div class="scan-ph">
    <div class="s-icon">📸</div>
    <p>Fişi çerçeveye yerleştirin</p>
    <div class="s-hint">AI ile otomatik ürün tespiti yapılacak</div>
  </div>
</div>

<div class="scan-actions">
  <form method="post" style="flex:1"><input type="hidden" name="action" value="simulate_scan">
    <button type="submit" class="btn btn-brand btn-block">📷 Tara (Simüle)</button>
  </form>
  <button class="btn btn-secondary btn-block" id="btn-manual" style="flex:1">✏️ Manuel Giriş</button>
</div>

<!-- AI Bilgi -->
<div class="ai-banner mb-4">
  <div class="ai-avatar bg-purple-l">🧠</div>
  <div class="ai-body">
    <div class="ai-label">Akıllı OCR</div>
    <div class="ai-text">Fişi taradığınızda her ürün tek tek tespit edilir</div>
    <div class="ai-sub">Ürünler kategorize edilir, fiyat geçmişi ve stok durumu güncellenir</div>
  </div>
</div>

<!-- Tüm Fişler -->
<div class="section">
  <div class="sec-header">
    <div class="sec-title">📋 Tüm Fişler</div>
    <span style="font-size:0.72rem;color:var(--text-muted);"><?= count($recentReceipts) ?> fiş</span>
  </div>
  <div class="flex flex-col gap-2">
    <?php foreach ($recentReceipts as $r):
      $icon = $storeIcons[$r['store_name']] ?? '🏪';
    ?>
    <a href="?page=receipt-detail&id=<?= $r['id'] ?>" class="receipt-card">
      <div class="receipt-icon bg-brand-l"><?= $icon ?></div>
      <div class="receipt-info">
        <div class="receipt-store"><?= e($r['store_name']) ?><?= $r['store_branch'] ? ' · '.e($r['store_branch']) : '' ?></div>
        <div class="receipt-meta">
          <span><?= trDate($r['receipt_date']) ?></span>
          <?php if ($r['ocr_accuracy'] > 0): ?>
            <span>·</span><span>OCR %<?= (int)$r['ocr_accuracy'] ?></span>
          <?php endif; ?>
        </div>
        <?php if ($r['real_items'] > 0): ?>
        <div class="receipt-items-preview">✅ <?= $r['real_items'] ?> ürün tespit edildi — detay için tıklayın</div>
        <?php endif; ?>
      </div>
      <div class="receipt-amount"><?= money($r['total_amount']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<template id="tpl-manual-receipt">
  <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">Manuel Fiş Girişi</h3>
  <form method="post" action="?page=scan">
    <input type="hidden" name="action" value="add_receipt">
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div><label class="form-label">Market Adı</label><input type="text" name="store_name" placeholder="Örn: Migros" class="form-input" required></div>
      <div><label class="form-label">Şube</label><input type="text" name="store_branch" placeholder="Opsiyonel" class="form-input"></div>
      <div><label class="form-label">Tarih</label><input type="date" name="receipt_date" value="<?= date('Y-m-d') ?>" class="form-input"></div>
      <div><label class="form-label">Toplam Tutar (₺)</label><input type="number" name="total_amount" placeholder="0.00" step="0.01" min="0" class="form-input" required></div>
      <button type="submit" class="btn btn-brand btn-block btn-lg">💾 Kaydet</button>
    </div>
  </form>
</template>
