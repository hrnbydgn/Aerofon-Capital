<?php
$db = getDB();
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$filter = $_GET['filter'] ?? 'all';

$message = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = trim($_POST['product_name'] ?? '');
    $icon = trim($_POST['product_icon'] ?? '📦');
    $catId = intval($_POST['category_id'] ?? 1);
    $unit = trim($_POST['unit'] ?? 'adet');
    $qty = floatval($_POST['quantity'] ?? 0);
    $maxQty = floatval($_POST['max_quantity'] ?? 1);
    if ($name && $maxQty > 0) {
        $days = max(1, intval($_POST['avg_days'] ?? 7));
        $stmt = $db->prepare("INSERT INTO products (name, icon, category_id, unit, quantity, max_quantity, predicted_end, avg_consumption_days, last_purchased) VALUES (?,?,?,?,?,?,date('now','+' || ? || ' days'),?,date('now'))");
        $stmt->execute([$name, $icon, $catId, $unit, $qty, $maxQty, $days, $days]);
        header("Location: ?page=inventory&filter=$filter&msg=added"); exit;
    }
}

if ($filter === 'all') {
    $products = $db->query("SELECT p.*, c.slug AS cs, c.name AS cn, c.icon AS ci, c.color AS cc, ROUND((p.quantity/p.max_quantity)*100) AS pct FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY (p.quantity/p.max_quantity) ASC")->fetchAll();
} else {
    $stmt = $db->prepare("SELECT p.*, c.slug AS cs, c.name AS cn, c.icon AS ci, c.color AS cc, ROUND((p.quantity/p.max_quantity)*100) AS pct FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE c.slug=? ORDER BY (p.quantity/p.max_quantity) ASC");
    $stmt->execute([$filter]);
    $products = $stmt->fetchAll();
}
?>

<?php if (isset($_GET['msg'])): ?>
<script>document.addEventListener('DOMContentLoaded',()=>showToast('✅','Ürün eklendi!'));</script>
<?php endif; ?>

<div class="flex justify-between items-center mb-2">
  <div><div class="pg-title">Envanter</div><div class="pg-sub" style="margin-bottom:0">Evinizdeki <?= count($products) ?> ürünün stok durumu</div></div>
  <button class="btn btn-brand btn-sm" id="btn-add-product">+ Ekle</button>
</div>

<div class="search-bar">
  <span class="s-icon">🔍</span>
  <input type="text" placeholder="Ürün ara..." id="inventory-search">
</div>

<div class="inv-filters">
  <a href="?page=inventory&filter=all" class="chip <?= $filter==='all'?'active':'' ?>">Tümü</a>
  <?php foreach ($categories as $cat):
    $cnt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id=?"); $cnt->execute([$cat['id']]); $c = $cnt->fetchColumn(); if(!$c) continue;
  ?>
  <a href="?page=inventory&filter=<?= e($cat['slug']) ?>" class="chip <?= $filter===$cat['slug']?'active':'' ?>"><?= $cat['icon'] ?> <?= e($cat['name']) ?></a>
  <?php endforeach; ?>
</div>

<div class="inv-grid" id="inv-grid">
  <?php foreach ($products as $p):
    $pctVal = (int)$p['pct'];
    $level = statusLevel($pctVal);
    $dl = daysLeft($p['predicted_end']);
  ?>
  <div class="inv-card <?= $level ?>" data-name="<?= e(strtolower($p['name'])) ?>">
    <div class="ic-emoji"><?= $p['icon'] ?></div>
    <div class="ic-name"><?= e($p['name']) ?></div>
    <div class="ic-qty"><?= $p['quantity'] ?> <?= e($p['unit']) ?> kalan</div>
    <div class="ic-ai"><span class="ai-tag">AI</span> <?= e($dl['text']) ?></div>
    <?php if ($p['avg_price'] > 0): ?>
    <div style="font-size:0.65rem;color:var(--text-muted);margin-top:2px;">≈ <?= money($p['avg_price']) ?></div>
    <?php endif; ?>
    <div class="inv-bar"><div class="progress-fill <?= $level ?>" style="width:<?= $pctVal ?>%"></div></div>
  </div>
  <?php endforeach; ?>
</div>

<template id="tpl-add-product">
  <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">Yeni Ürün Ekle</h3>
  <form method="post" action="?page=inventory&filter=<?= e($filter) ?>">
    <input type="hidden" name="action" value="add_product">
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div style="display:flex;gap:10px;">
        <div style="width:70px;"><label class="form-label">İkon</label><input type="text" name="product_icon" value="📦" class="form-input" style="text-align:center;font-size:1.3rem;"></div>
        <div style="flex:1;"><label class="form-label">Ürün Adı</label><input type="text" name="product_name" placeholder="Örn: Süt" class="form-input" required></div>
      </div>
      <div><label class="form-label">Kategori</label><select name="category_id" class="form-input"><?php foreach($categories as $cat):?><option value="<?=$cat['id']?>"><?=$cat['icon']?> <?=e($cat['name'])?></option><?php endforeach;?></select></div>
      <div style="display:flex;gap:10px;">
        <div style="flex:1"><label class="form-label">Mevcut</label><input type="number" name="quantity" value="1" min="0" step="0.1" class="form-input"></div>
        <div style="flex:1"><label class="form-label">Maksimum</label><input type="number" name="max_quantity" value="1" min="0.1" step="0.1" class="form-input" required></div>
      </div>
      <div style="display:flex;gap:10px;">
        <div style="flex:1"><label class="form-label">Birim</label><select name="unit" class="form-input"><option>adet</option><option>litre</option><option>kg</option><option>gram</option><option>ml</option><option>paket</option></select></div>
        <div style="flex:1"><label class="form-label">Tüketim (gün)</label><input type="number" name="avg_days" value="7" min="1" class="form-input"></div>
      </div>
      <button type="submit" class="btn btn-brand btn-block btn-lg">📦 Ekle</button>
    </div>
  </form>
</template>
