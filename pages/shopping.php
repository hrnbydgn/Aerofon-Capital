<?php
/**
 * Evly - Alışveriş Listesi Sayfası
 */
$db = getDB();

// İşlemler
$message = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_item':
            $name = trim($_POST['item_name'] ?? '');
            $qty = max(1, intval($_POST['item_qty'] ?? 1));
            if ($name) {
                $stmt = $db->prepare("INSERT INTO shopping_list (name, quantity, is_ai_suggested, reason) VALUES (?, ?, 0, 'Manuel eklendi')");
                $stmt->execute([$name, $qty]);
                $message = ['type' => 'success', 'text' => "\"$name\" listeye eklendi"];
            }
            break;

        case 'toggle_item':
            $id = intval($_POST['item_id'] ?? 0);
            if ($id) {
                $db->prepare("UPDATE shopping_list SET is_checked = CASE WHEN is_checked = 1 THEN 0 ELSE 1 END WHERE id = ?")->execute([$id]);
            }
            break;

        case 'delete_item':
            $id = intval($_POST['item_id'] ?? 0);
            if ($id) {
                $db->prepare("DELETE FROM shopping_list WHERE id = ?")->execute([$id]);
                $message = ['type' => 'success', 'text' => 'Ürün listeden silindi'];
            }
            break;

        case 'clear_checked':
            $db->exec("DELETE FROM shopping_list WHERE is_checked = 1");
            $message = ['type' => 'success', 'text' => 'Tamamlanan ürünler temizlendi'];
            break;
    }
}

// Verileri çek
$aiItems = $db->query("SELECT * FROM shopping_list WHERE is_ai_suggested = 1 ORDER BY is_checked ASC, id ASC")->fetchAll();
$manualItems = $db->query("SELECT * FROM shopping_list WHERE is_ai_suggested = 0 ORDER BY is_checked ASC, id ASC")->fetchAll();
$totalItems = count($aiItems) + count($manualItems);
$checkedCount = $db->query("SELECT COUNT(*) FROM shopping_list WHERE is_checked = 1")->fetchColumn();
$uncheckedCount = $totalItems - $checkedCount;
?>

<?php if ($message): ?>
<script>document.addEventListener('DOMContentLoaded',()=>showToast('<?= $message['type'] === 'success' ? '✅' : '❌' ?>','<?= e($message['text']) ?>'));</script>
<?php endif; ?>

<div class="flex justify-between items-center mb-3">
  <div>
    <div class="page-title">Alışveriş Listesi</div>
    <div class="page-subtitle" style="margin-bottom:0;">AI destekli akıllı liste</div>
  </div>
  <div class="flex gap-2">
    <?php if ($checkedCount > 0): ?>
    <form method="post" style="display:inline;">
      <input type="hidden" name="action" value="clear_checked">
      <button type="submit" class="btn btn-secondary" style="padding:10px 12px;font-size:0.75rem;">🗑️ Temizle</button>
    </form>
    <?php endif; ?>
    <button class="btn btn-primary" style="padding:10px 14px;font-size:0.8rem;" id="btn-share-list">📤 Paylaş</button>
  </div>
</div>

<!-- AI Öneriler Banner -->
<div class="shopping-ai-banner">
  <div class="ai-icon ai-pulse">🤖</div>
  <div class="ai-text">
    <div class="ai-title">AI Önerileri</div>
    <div class="ai-desc">Tüketim alışkanlıklarınıza göre <?= count($aiItems) ?> ürün önerildi</div>
  </div>
</div>

<!-- Ürün Ekle -->
<form method="post" class="add-item-row">
  <input type="hidden" name="action" value="add_item">
  <input type="text" name="item_name" placeholder="Ürün ekle..." required>
  <input type="number" name="item_qty" value="1" min="1" style="width:60px;padding:12px 8px;background:var(--bg-card);border:1px solid rgba(148,163,184,0.1);border-radius:var(--radius-md);color:var(--text-primary);font-size:0.9rem;outline:none;text-align:center;">
  <button type="submit" class="btn btn-primary">+</button>
</form>

<!-- AI Önerileri -->
<?php if (!empty($aiItems)): ?>
<div class="section">
  <div class="section-header">
    <div class="section-title">
      <span class="section-icon">🤖</span>
      AI Önerileri
    </div>
    <span class="ai-suggest-tag">AKILLI</span>
  </div>
  <div class="shopping-list">
    <?php foreach ($aiItems as $item): ?>
    <div class="shop-item <?= $item['is_checked'] ? 'checked' : '' ?>">
      <form method="post" style="display:contents;">
        <input type="hidden" name="action" value="toggle_item">
        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
        <button type="submit" class="shop-check">✓</button>
      </form>
      <div class="shop-details">
        <div class="shop-name"><?= e($item['name']) ?></div>
        <div class="shop-reason"><span class="ai-suggest-tag">AI</span> <?= e($item['reason']) ?></div>
      </div>
      <div class="shop-qty">×<?= $item['quantity'] ?></div>
      <form method="post" style="display:inline;">
        <input type="hidden" name="action" value="delete_item">
        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
        <button type="submit" class="shop-delete-btn" title="Sil">✕</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Manuel Eklenen -->
<div class="section">
  <div class="section-header">
    <div class="section-title">
      <span class="section-icon">📝</span>
      Manuel Eklenen
    </div>
  </div>
  <div class="shopping-list" id="manual-list">
    <?php if (empty($manualItems)): ?>
      <div class="empty-state" style="padding:20px;">
        <p class="text-sm text-muted">Henüz manuel eklenen ürün yok</p>
      </div>
    <?php else: ?>
      <?php foreach ($manualItems as $item): ?>
      <div class="shop-item <?= $item['is_checked'] ? 'checked' : '' ?>">
        <form method="post" style="display:contents;">
          <input type="hidden" name="action" value="toggle_item">
          <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
          <button type="submit" class="shop-check">✓</button>
        </form>
        <div class="shop-details">
          <div class="shop-name"><?= e($item['name']) ?></div>
          <div class="shop-reason"><?= e($item['reason']) ?></div>
        </div>
        <div class="shop-qty">×<?= $item['quantity'] ?></div>
        <form method="post" style="display:inline;">
          <input type="hidden" name="action" value="delete_item">
          <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
          <button type="submit" class="shop-delete-btn" title="Sil">✕</button>
        </form>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Özet -->
<div class="card card-glass mt-4">
  <div class="flex justify-between items-center">
    <div>
      <div style="font-size:0.8rem;color:var(--text-secondary);">Durum</div>
      <div style="font-size:1rem;font-weight:700;">
        <span style="color:var(--accent-success);"><?= $checkedCount ?></span> / <?= $totalItems ?> tamamlandı
      </div>
    </div>
    <div style="text-align:right;">
      <div style="font-size:0.8rem;color:var(--text-secondary);">Kalan</div>
      <div style="font-size:1.3rem;font-weight:700;color:var(--accent-primary-light);"><?= $uncheckedCount ?> ürün</div>
    </div>
  </div>
  <?php if ($totalItems > 0): ?>
  <div class="inv-progress mt-3">
    <div class="progress-fill good" style="width:<?= round(($checkedCount / $totalItems) * 100) ?>%"></div>
  </div>
  <?php endif; ?>
</div>
