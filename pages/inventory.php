<?php
/**
 * Evly - Envanter Sayfası
 */
$db = getDB();

// Kategoriler
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Aktif filtre
$filter = $_GET['filter'] ?? 'all';

// Ürünler
if ($filter === 'all') {
    $products = $db->query("
        SELECT p.*, c.slug AS cat_slug, c.name AS cat_name,
               ROUND((p.quantity / p.max_quantity) * 100) AS pct
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        ORDER BY (p.quantity / p.max_quantity) ASC
    ")->fetchAll();
} else {
    $stmt = $db->prepare("
        SELECT p.*, c.slug AS cat_slug, c.name AS cat_name,
               ROUND((p.quantity / p.max_quantity) * 100) AS pct
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE c.slug = ?
        ORDER BY (p.quantity / p.max_quantity) ASC
    ");
    $stmt->execute([$filter]);
    $products = $stmt->fetchAll();
}

// Ürün ekleme
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
        $predicted = date('Y-m-d', strtotime("+{$days} days"));
        $stmt = $db->prepare("INSERT INTO products (name, icon, category_id, unit, quantity, max_quantity, predicted_end, avg_consumption_days, last_purchased) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$name, $icon, $catId, $unit, $qty, $maxQty, $predicted, $days, date('Y-m-d')]);
        $message = ['type' => 'success', 'text' => "\"$name\" eklendi!"];
        header("Location: ?page=inventory&filter=$filter&msg=added");
        exit;
    }
}
?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
<script>document.addEventListener('DOMContentLoaded',()=>showToast('✅','Ürün envantere eklendi!'));</script>
<?php endif; ?>

<div class="flex justify-between items-center mb-2">
  <div>
    <div class="page-title">Envanter</div>
    <div class="page-subtitle" style="margin-bottom:0;">Evinizdeki tüm ürünler ve stok durumları</div>
  </div>
  <button class="btn btn-primary" style="padding:10px 14px;font-size:0.8rem;" id="btn-add-product">+ Ekle</button>
</div>

<!-- Arama -->
<div class="search-bar">
  <span class="search-icon">🔍</span>
  <input type="text" placeholder="Ürün ara..." id="inventory-search">
</div>

<!-- Kategori Filtreleri -->
<div class="inventory-filters">
  <a href="?page=inventory&filter=all" class="filter-chip <?= $filter === 'all' ? 'active' : '' ?>">Tümü (<?= $db->query("SELECT COUNT(*) FROM products")->fetchColumn() ?>)</a>
  <?php foreach ($categories as $cat): 
    $catCount = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $catCount->execute([$cat['id']]);
    $cnt = $catCount->fetchColumn();
    if ($cnt == 0) continue;
  ?>
  <a href="?page=inventory&filter=<?= e($cat['slug']) ?>" class="filter-chip <?= $filter === $cat['slug'] ? 'active' : '' ?>">
    <?= $cat['icon'] ?> <?= e($cat['name']) ?> (<?= $cnt ?>)
  </a>
  <?php endforeach; ?>
</div>

<!-- Ürün Grid -->
<div class="inventory-grid" id="inventory-grid">
  <?php if (empty($products)): ?>
    <div class="empty-state" style="grid-column: 1/-1;">
      <div class="empty-icon">📦</div>
      <h3>Bu kategoride ürün yok</h3>
      <p>Yeni ürün ekleyin veya fiş tarayın</p>
    </div>
  <?php else: ?>
    <?php foreach ($products as $p):
      $pct = (int)$p['pct'];
      $level = statusLevel($pct);
      $daysStr = daysLeft($p['predicted_end']);
    ?>
    <div class="inv-card <?= $level ?>" data-category="<?= e($p['cat_slug'] ?? '') ?>" data-name="<?= e(function_exists('mb_strtolower') ? mb_strtolower($p['name']) : strtolower($p['name'])) ?>">
      <div class="inv-emoji"><?= $p['icon'] ?></div>
      <div class="inv-name"><?= e($p['name']) ?></div>
      <div class="inv-qty">Kalan: <?= $p['quantity'] ?> <?= e($p['unit']) ?></div>
      <div class="inv-prediction">
        <span class="ai-tag">AI</span>
        <?= e($daysStr) ?>
      </div>
      <div class="inv-progress">
        <div class="progress-fill <?= $level ?>" style="width:<?= $pct ?>%"></div>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Ürün Ekleme Şablonu -->
<template id="tpl-add-product">
  <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">Yeni Ürün Ekle</h3>
  <form method="post" action="?page=inventory&filter=<?= e($filter) ?>">
    <input type="hidden" name="action" value="add_product">
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div style="display:flex;gap:10px;">
        <div style="width:70px;">
          <label class="form-label">İkon</label>
          <input type="text" name="product_icon" value="📦" class="form-input" style="text-align:center;font-size:1.3rem;">
        </div>
        <div style="flex:1;">
          <label class="form-label">Ürün Adı</label>
          <input type="text" name="product_name" placeholder="Örn: Süt" class="form-input" required>
        </div>
      </div>
      <div>
        <label class="form-label">Kategori</label>
        <select name="category_id" class="form-input">
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= $cat['icon'] ?> <?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;gap:10px;">
        <div style="flex:1;">
          <label class="form-label">Mevcut Miktar</label>
          <input type="number" name="quantity" value="1" min="0" step="0.1" class="form-input">
        </div>
        <div style="flex:1;">
          <label class="form-label">Maks Miktar</label>
          <input type="number" name="max_quantity" value="1" min="0.1" step="0.1" class="form-input" required>
        </div>
      </div>
      <div style="display:flex;gap:10px;">
        <div style="flex:1;">
          <label class="form-label">Birim</label>
          <select name="unit" class="form-input">
            <option value="adet">Adet</option>
            <option value="litre">Litre</option>
            <option value="kg">Kg</option>
            <option value="gram">Gram</option>
            <option value="ml">ML</option>
            <option value="paket">Paket</option>
          </select>
        </div>
        <div style="flex:1;">
          <label class="form-label">Ort. Tüketim (gün)</label>
          <input type="number" name="avg_days" value="7" min="1" class="form-input">
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">📦 Ekle</button>
    </div>
  </form>
</template>
