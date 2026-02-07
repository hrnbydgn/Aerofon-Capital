<?php
/**
 * Evly — AI Haftalık Yemek Planı
 * Mevcut stoklara göre yemek önerisi
 */
$db = getDB();
$meals = $db->query("SELECT * FROM meal_plans ORDER BY day_of_week ASC, id ASC")->fetchAll();
$dayNames = [1=>'Pazartesi',2=>'Salı',3=>'Çarşamba',4=>'Perşembe',5=>'Cuma',6=>'Cumartesi',7=>'Pazar'];
$today = (int)date('N');

// Günlere göre grupla
$byDay = [];
foreach ($meals as $m) {
    $byDay[$m['day_of_week']][] = $m;
}

// Mevcut stokta olan malzemeler
$inStock = $db->query("SELECT name FROM products WHERE (quantity/max_quantity) > 0.1")->fetchAll(PDO::FETCH_COLUMN);
$inStockLower = array_map('strtolower', $inStock);
?>

<div class="flex justify-between items-center mb-2">
  <div><div class="pg-title">Haftalık Menü</div><div class="pg-sub" style="margin-bottom:0">AI, stoklarınıza göre menü hazırladı</div></div>
</div>

<div class="ai-banner mb-4" style="border-left-color:var(--accent-purple);">
  <div class="ai-avatar bg-purple-l">🤖</div>
  <div class="ai-body">
    <div class="ai-label">AI Menü Planlayıcı</div>
    <div class="ai-text">Evdeki mevcut stoklar analiz edilerek yemek planı oluşturuldu</div>
    <div class="ai-sub">Yeşil etiketli malzemeler evinizde mevcut</div>
  </div>
</div>

<?php foreach ($byDay as $dayNum => $dayMeals):
  $isToday = $dayNum === $today;
?>
<div class="section">
  <div class="sec-header">
    <div class="sec-title">
      <?= $isToday ? '📍 ' : '' ?><?= $dayNames[$dayNum] ?? "Gün $dayNum" ?>
      <?php if ($isToday): ?><span style="font-size:0.65rem;background:var(--brand-lighter);color:var(--brand);padding:2px 8px;border-radius:var(--radius-full);margin-left:6px;">BUGÜN</span><?php endif; ?>
    </div>
  </div>
  <div class="flex flex-col gap-2">
    <?php foreach ($dayMeals as $meal):
      $mealLabel = ['breakfast'=>'☀️ Kahvaltı','lunch'=>'🌤️ Öğle','dinner'=>'🌙 Akşam'][$meal['meal_type']] ?? $meal['meal_type'];
      $ingredients = array_map('trim', explode(',', $meal['ingredients']));
    ?>
    <div class="meal-card">
      <span class="meal-type <?= $meal['meal_type'] ?>"><?= $mealLabel ?></span>
      <div class="meal-title"><?= e($meal['title']) ?></div>
      <div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:6px;">
        <?php foreach ($ingredients as $ing):
          $isAvailable = false;
          foreach ($inStockLower as $s) {
              if (stripos($s, strtolower(trim($ing))) !== false || stripos(strtolower(trim($ing)), $s) !== false) {
                  $isAvailable = true; break;
              }
          }
        ?>
        <span style="font-size:0.68rem;padding:3px 8px;border-radius:var(--radius-full);font-weight:500;<?= $isAvailable ? 'background:#f0fdf4;color:#16a34a;' : 'background:#fef2f2;color:#dc2626;' ?>">
          <?= $isAvailable ? '✅' : '❌' ?> <?= e(trim($ing)) ?>
        </span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<?php if (empty($byDay)): ?>
<div class="empty-state">
  <div class="e-icon">🍽️</div>
  <h3>Henüz menü planı yok</h3>
  <p>AI daha fazla veri toplandıkça menü önerisi oluşturacak</p>
</div>
<?php endif; ?>
