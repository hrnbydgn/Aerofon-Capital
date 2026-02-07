<?php
/**
 * Evly — Akıllı Alışveriş Listesi
 * AI önerileri, öncelik sıralaması, tahmini fiyat, kategori gruplaması
 */
$db = getDB();

$message = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_item':
            $name = trim($_POST['item_name'] ?? '');
            $qty = max(1, intval($_POST['item_qty'] ?? 1));
            if ($name) {
                $db->prepare("INSERT INTO shopping_list (name, icon, quantity, is_ai_suggested, reason) VALUES (?,'🛒',?,0,'Manuel eklendi')")->execute([$name, $qty]);
                $message = ['type'=>'success','text'=>"\"$name\" eklendi"];
            }
            break;
        case 'toggle_item':
            $id = intval($_POST['item_id'] ?? 0);
            if ($id) $db->prepare("UPDATE shopping_list SET is_checked = CASE WHEN is_checked=1 THEN 0 ELSE 1 END WHERE id=?")->execute([$id]);
            break;
        case 'delete_item':
            $id = intval($_POST['item_id'] ?? 0);
            if ($id) { $db->prepare("DELETE FROM shopping_list WHERE id=?")->execute([$id]); $message = ['type'=>'success','text'=>'Silindi']; }
            break;
        case 'clear_checked':
            $db->exec("DELETE FROM shopping_list WHERE is_checked=1");
            $message = ['type'=>'success','text'=>'Tamamlananlar temizlendi'];
            break;
    }
}

// Verileri çek - AI önerileri öncelik sırasıyla, sonra manueller
$aiItems = $db->query("SELECT sl.*, c.name AS cat_name, c.icon AS cat_icon FROM shopping_list sl LEFT JOIN categories c ON c.id=sl.category_id WHERE sl.is_ai_suggested=1 ORDER BY sl.is_checked ASC, sl.priority DESC, sl.id ASC")->fetchAll();
$manualItems = $db->query("SELECT sl.*, c.name AS cat_name, c.icon AS cat_icon FROM shopping_list sl LEFT JOIN categories c ON c.id=sl.category_id WHERE sl.is_ai_suggested=0 ORDER BY sl.is_checked ASC, sl.id ASC")->fetchAll();
$totalItems = count($aiItems) + count($manualItems);
$checkedCount = $db->query("SELECT COUNT(*) FROM shopping_list WHERE is_checked=1")->fetchColumn();
$unchecked = $totalItems - $checkedCount;
$estimatedTotal = $db->query("SELECT COALESCE(SUM(estimated_price * quantity),0) FROM shopping_list WHERE is_checked=0")->fetchColumn();

// En uygun market önerisi (basit)
$bestStore = $db->query("SELECT store_name, COUNT(*) AS cnt, AVG(total_amount/item_count) AS avg_item FROM receipts WHERE item_count > 0 GROUP BY store_name ORDER BY avg_item ASC LIMIT 1")->fetch();
?>

<?php if ($message): ?>
<script>document.addEventListener('DOMContentLoaded',()=>showToast('<?= $message['type']==='success'?'✅':'❌' ?>','<?= e($message['text']) ?>'));</script>
<?php endif; ?>

<div class="flex justify-between items-center mb-2">
  <div><div class="pg-title">Alışveriş</div><div class="pg-sub" style="margin-bottom:0">AI destekli akıllı alışveriş listesi</div></div>
  <div class="flex gap-2">
    <?php if ($checkedCount > 0): ?>
    <form method="post"><input type="hidden" name="action" value="clear_checked"><button type="submit" class="btn btn-sm btn-danger">🗑️</button></form>
    <?php endif; ?>
    <button class="btn btn-sm btn-ghost" id="btn-share-list">📤 Paylaş</button>
  </div>
</div>

<!-- AI Market Önerisi -->
<?php if ($bestStore): ?>
<div class="ai-banner mb-3" style="border-left-color:var(--accent-green);">
  <div class="ai-avatar bg-green-l">🏪</div>
  <div class="ai-body">
    <div class="ai-label">Akıllı Öneri</div>
    <div class="ai-text">Bu listeyi <strong><?= e($bestStore['store_name']) ?></strong>'ta almanız önerilir</div>
    <div class="ai-sub">Fiyat geçmişine göre en uygun ortalama birim fiyat bu markette</div>
  </div>
</div>
<?php endif; ?>

<!-- Ürün Ekle -->
<form method="post" class="add-row">
  <input type="hidden" name="action" value="add_item">
  <input type="text" name="item_name" placeholder="Ürün ekle..." required>
  <input type="number" name="item_qty" value="1" min="1" style="width:52px;padding:11px 6px;background:var(--bg-card);border:1.5px solid var(--bg-elevated);border-radius:var(--radius-md);color:var(--text-primary);font-size:0.88rem;text-align:center;outline:none;">
  <button type="submit" class="btn btn-brand">+</button>
</form>

<!-- AI Önerileri -->
<?php if (!empty($aiItems)): ?>
<div class="section">
  <div class="sec-header">
    <div class="sec-title">🤖 AI Önerileri</div>
    <span class="ai-tag" style="font-size:0.62rem;">AKILLI LİSTE</span>
  </div>
  <div class="flex flex-col gap-2">
    <?php foreach ($aiItems as $item):
      $priorityClass = $item['priority'] >= 3 ? 'priority-3' : ($item['priority'] >= 2 ? 'priority-2' : ($item['priority'] >= 1 ? 'priority-1' : ''));
    ?>
    <div class="shop-item <?= $item['is_checked'] ? 'checked' : '' ?> <?= $priorityClass ?>">
      <form method="post" style="display:contents"><input type="hidden" name="action" value="toggle_item"><input type="hidden" name="item_id" value="<?= $item['id'] ?>">
        <button type="submit" class="si-check">✓</button>
      </form>
      <div style="font-size:1.2rem;flex-shrink:0;"><?= $item['icon'] ?></div>
      <div class="si-body">
        <div class="si-name"><?= e($item['name']) ?></div>
        <div class="si-reason">
          <span class="ai-tag">AI</span>
          <?php if ($item['reason']): ?><span><?= e($item['reason']) ?></span><?php endif; ?>
        </div>
      </div>
      <div class="si-right">
        <?php if ($item['estimated_price'] > 0): ?><div class="si-price">≈<?= money($item['estimated_price']) ?></div><?php endif; ?>
        <div class="si-qty">×<?= $item['quantity'] ?></div>
      </div>
      <form method="post" style="display:inline"><input type="hidden" name="action" value="delete_item"><input type="hidden" name="item_id" value="<?= $item['id'] ?>">
        <button type="submit" class="si-del">✕</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Manuel Eklenen -->
<div class="section">
  <div class="sec-header">
    <div class="sec-title">📝 Manuel Eklenen</div>
  </div>
  <div class="flex flex-col gap-2">
    <?php if (empty($manualItems)): ?>
      <div class="empty-state" style="padding:16px;"><p style="font-size:0.82rem;">Henüz manuel ürün eklenmedi</p></div>
    <?php else: ?>
      <?php foreach ($manualItems as $item): ?>
      <div class="shop-item <?= $item['is_checked'] ? 'checked' : '' ?>">
        <form method="post" style="display:contents"><input type="hidden" name="action" value="toggle_item"><input type="hidden" name="item_id" value="<?= $item['id'] ?>">
          <button type="submit" class="si-check">✓</button>
        </form>
        <div style="font-size:1.2rem;flex-shrink:0;"><?= $item['icon'] ?></div>
        <div class="si-body">
          <div class="si-name"><?= e($item['name']) ?></div>
          <div class="si-reason"><?= e($item['reason'] ?? 'Manuel eklendi') ?></div>
        </div>
        <div class="si-right">
          <?php if ($item['estimated_price'] > 0): ?><div class="si-price">≈<?= money($item['estimated_price']) ?></div><?php endif; ?>
          <div class="si-qty">×<?= $item['quantity'] ?></div>
        </div>
        <form method="post" style="display:inline"><input type="hidden" name="action" value="delete_item"><input type="hidden" name="item_id" value="<?= $item['id'] ?>">
          <button type="submit" class="si-del">✕</button>
        </form>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Özet -->
<div class="shop-summary">
  <div class="ss-left">
    <div class="ss-label">Tahmini Toplam</div>
    <div class="ss-val"><?= money($estimatedTotal) ?></div>
  </div>
  <div class="ss-right">
    <div class="ss-label"><?= $checkedCount ?>/<?= $totalItems ?> tamamlandı</div>
    <div style="font-size:1.1rem;font-weight:700;color:var(--brand);"><?= $unchecked ?> ürün kaldı</div>
  </div>
</div>

<?php if ($totalItems > 0): ?>
<div style="margin-top:8px;"><div class="progress-bar" style="width:100%;height:6px;"><div class="progress-fill good" style="width:<?= $totalItems > 0 ? round(($checkedCount/$totalItems)*100) : 0 ?>%"></div></div></div>
<?php endif; ?>
