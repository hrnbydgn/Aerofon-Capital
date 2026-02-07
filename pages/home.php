<?php
$db = getDB();
$user = $db->query("SELECT * FROM users LIMIT 1")->fetch();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$criticalCount = $db->query("SELECT COUNT(*) FROM products WHERE predicted_end <= date('now', '+3 days')")->fetchColumn();
$warningCount = $db->query("SELECT COUNT(*) FROM products WHERE predicted_end > date('now', '+3 days') AND predicted_end <= date('now', '+7 days')")->fetchColumn();
$monthlySpend = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM receipts WHERE receipt_date >= date('now','start of month')")->fetchColumn();
$lastMonthSpend = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM receipts WHERE receipt_date >= date('now','start of month','-1 month') AND receipt_date < date('now','start of month')")->fetchColumn();
$spendDiff = $lastMonthSpend > 0 ? round((($monthlySpend - $lastMonthSpend) / $lastMonthSpend) * 100) : 0;

$urgentProducts = $db->query("
    SELECT p.*, c.icon AS cat_icon, c.color AS cat_color,
           ROUND((p.quantity / p.max_quantity)*100) AS pct
    FROM products p LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.predicted_end <= date('now','+7 days')
    ORDER BY p.predicted_end ASC LIMIT 5
")->fetchAll();

$recentReceipts = $db->query("SELECT r.*, (SELECT COUNT(*) FROM receipt_items WHERE receipt_id = r.id) AS real_items FROM receipts r ORDER BY receipt_date DESC LIMIT 3")->fetchAll();

$topInsight = $db->query("SELECT * FROM insights WHERE is_read = 0 ORDER BY created_at DESC LIMIT 1")->fetch();

$weeklyReceipts = $db->query("SELECT COUNT(*) FROM receipts WHERE receipt_date >= date('now','-7 days')")->fetchColumn();
$weeklySpend = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM receipts WHERE receipt_date >= date('now','-7 days')")->fetchColumn();
$shoppingPending = $db->query("SELECT COUNT(*) FROM shopping_list WHERE is_checked=0")->fetchColumn();

// Israf skoru (basit simülasyon)
$wasteScore = 82;
$storeIcons = ['Migros'=>'🏪','BİM'=>'🏬','A101'=>'🛒','ŞOK'=>'🏪','CarrefourSA'=>'🛍️'];
?>

<!-- AI Hero -->
<div class="ai-hero">
  <div class="ai-hero-content">
    <div class="greeting"><?= aiGreeting() ?>, <?= e(explode(' ', $user['name'] ?? '')[0]) ?> 👋</div>
    <h2><?= aiMotivation() ?></h2>
    <div class="ai-msg">
      <?php if ($criticalCount > 0): ?>
        ⚠️ <strong><?= $criticalCount ?> ürün</strong> 3 gün içinde bitecek.
      <?php else: ?>
        Tüm stoklar iyi durumda, rahat olabilirsiniz.
      <?php endif; ?>
      <?php if ($shoppingPending > 0): ?>
        Alışveriş listenizde <strong><?= $shoppingPending ?> ürün</strong> bekliyor.
      <?php endif; ?>
    </div>
    <div class="ai-hero-stats">
      <div class="ai-hero-stat">
        <span class="val"><?= $totalProducts ?></span>
        <span class="lbl">Ürün</span>
      </div>
      <div class="ai-hero-stat">
        <span class="val"><?= $criticalCount + $warningCount ?></span>
        <span class="lbl">Azalan</span>
      </div>
      <div class="ai-hero-stat">
        <span class="val"><?= moneyShort($monthlySpend) ?></span>
        <span class="lbl">Bu Ay</span>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="quick-grid">
  <a href="?page=scan" class="qa">
    <div class="qa-icon bg-brand-l">📷</div>
    <span class="qa-label">Fiş Tara</span>
  </a>
  <a href="?page=shopping" class="qa">
    <div class="qa-icon bg-blue-l">🛒</div>
    <span class="qa-label">Alışveriş</span>
  </a>
  <a href="?page=meal-plan" class="qa">
    <div class="qa-icon bg-amber-l">🍽️</div>
    <span class="qa-label">Menü</span>
  </a>
  <a href="?page=analytics" class="qa">
    <div class="qa-icon bg-purple-l">📊</div>
    <span class="qa-label">Analiz</span>
  </a>
</div>

<!-- AI Top Insight -->
<?php if ($topInsight): ?>
<div class="ai-banner" style="border-left-color:<?= $topInsight['type'] === 'warn' ? 'var(--caution)' : 'var(--brand)' ?>;">
  <div class="ai-avatar bg-brand-l"><?= $topInsight['icon'] ?></div>
  <div class="ai-body">
    <div class="ai-label">AI Öneri</div>
    <div class="ai-text"><?= e($topInsight['title']) ?></div>
    <div class="ai-sub"><?= e($topInsight['description']) ?></div>
  </div>
  <?php if ($topInsight['action_url']): ?>
    <a href="<?= e($topInsight['action_url']) ?>" class="btn btn-sm btn-ghost"><?= e($topInsight['action_text']) ?></a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Ev Sağlık Skoru -->
<div class="section">
  <div class="sec-header">
    <div class="sec-title">🏠 Ev Sağlık Durumu</div>
  </div>
  <div class="scroll-row">
    <div class="scroll-card">
      <div class="sc-icon">🛡️</div>
      <div class="sc-val">%<?= $wasteScore ?></div>
      <div class="sc-lbl">Israf Skoru</div>
    </div>
    <div class="scroll-card" <?= $criticalCount > 0 ? 'style="border-left-color:var(--danger)"' : '' ?>>
      <div class="sc-icon"><?= $criticalCount > 0 ? '⚠️' : '✅' ?></div>
      <div class="sc-val"><?= $criticalCount ?></div>
      <div class="sc-lbl">Acil Stok</div>
    </div>
    <div class="scroll-card">
      <div class="sc-icon">📊</div>
      <div class="sc-val"><?= $spendDiff > 0 ? "+$spendDiff%" : "$spendDiff%" ?></div>
      <div class="sc-lbl">Harcama Trendi</div>
    </div>
    <div class="scroll-card">
      <div class="sc-icon">🤖</div>
      <div class="sc-val">%94</div>
      <div class="sc-lbl">AI Doğruluk</div>
    </div>
    <div class="scroll-card">
      <div class="sc-icon">🛒</div>
      <div class="sc-val"><?= $shoppingPending ?></div>
      <div class="sc-lbl">Alınacak</div>
    </div>
  </div>
</div>

<!-- Acil Stoklar -->
<?php if (!empty($urgentProducts)): ?>
<div class="section">
  <div class="sec-header">
    <div class="sec-title">⚡ Acil Stoklar</div>
    <a href="?page=inventory" class="sec-link">Tümü →</a>
  </div>
  <div class="prod-list">
    <?php foreach ($urgentProducts as $p):
      $pctVal = pct($p['quantity'], $p['max_quantity']);
      $level = statusLevel($pctVal);
      $dl = daysLeft($p['predicted_end']);
    ?>
    <div class="prod-card">
      <div class="prod-icon"><?= $p['icon'] ?></div>
      <div class="prod-info">
        <div class="prod-name"><?= e($p['name']) ?></div>
        <div class="prod-meta">
          <span class="ai-tag">AI</span>
          <span><?= e($dl['text']) ?></span>
          <span>·</span>
          <span>Son alım: <?= trDate($p['last_purchased']) ?></span>
        </div>
      </div>
      <div class="prod-right">
        <span class="prod-badge <?= $level ?>"><?= $dl['days'] <= 1 ? 'ACİL' : $dl['text'] ?></span>
        <div class="progress-bar"><div class="progress-fill <?= $level ?>" style="width:<?= $pctVal ?>%"></div></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Son Fişler -->
<div class="section">
  <div class="sec-header">
    <div class="sec-title">🧾 Son Fişler</div>
    <a href="?page=scan" class="sec-link">Tümü →</a>
  </div>
  <div class="flex flex-col gap-2">
    <?php foreach ($recentReceipts as $r):
      $icon = $storeIcons[$r['store_name']] ?? '🏪';
    ?>
    <a href="?page=receipt-detail&id=<?= $r['id'] ?>" class="receipt-card">
      <div class="receipt-icon bg-brand-l"><?= $icon ?></div>
      <div class="receipt-info">
        <div class="receipt-store"><?= e($r['store_name']) ?> <?= $r['store_branch'] ? '· ' . e($r['store_branch']) : '' ?></div>
        <div class="receipt-meta">
          <span><?= trDate($r['receipt_date']) ?></span>
          <span>·</span>
          <span><?= $r['real_items'] ?> ürün tespit edildi</span>
        </div>
      </div>
      <div class="receipt-amount"><?= money($r['total_amount']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Haftalık Menü -->
<div class="section">
  <div class="sec-header">
    <div class="sec-title">🍽️ Bugünün Menüsü</div>
    <a href="?page=meal-plan" class="sec-link">Haftalık Plan →</a>
  </div>
  <?php
    $dayOfWeek = (int)date('N');
    $todayMeals = $db->query("SELECT * FROM meal_plans WHERE day_of_week = $dayOfWeek ORDER BY CASE meal_type WHEN 'breakfast' THEN 1 WHEN 'lunch' THEN 2 WHEN 'dinner' THEN 3 ELSE 4 END")->fetchAll();
    if (empty($todayMeals)) $todayMeals = $db->query("SELECT * FROM meal_plans WHERE day_of_week = 1 ORDER BY id ASC")->fetchAll();
  ?>
  <div class="scroll-row">
    <?php foreach ($todayMeals as $meal): ?>
    <div class="scroll-card" style="width:180px;">
      <span class="meal-type <?= $meal['meal_type'] ?>">
        <?= $meal['meal_type'] === 'breakfast' ? '☀️ Kahvaltı' : ($meal['meal_type'] === 'lunch' ? '🌤️ Öğle' : '🌙 Akşam') ?>
      </span>
      <div class="meal-title"><?= e($meal['title']) ?></div>
      <div class="meal-ings"><?= e($meal['ingredients']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
