<?php
$db = getDB();
$user = $db->query("SELECT * FROM users LIMIT 1")->fetch();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalReceipts = $db->query("SELECT COUNT(*) FROM receipts")->fetchColumn();
$totalSpend = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM receipts")->fetchColumn();

$message = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $name = trim($_POST['user_name'] ?? '');
        $email = trim($_POST['user_email'] ?? '');
        if ($name) {
            $db->prepare("UPDATE users SET name=?, email=? WHERE id=?")->execute([$name, $email, $user['id']]);
            $user['name'] = $name; $user['email'] = $email;
            $message = ['type'=>'success','text'=>'Profil güncellendi!'];
        }
    }
    if ($_POST['action'] === 'reset_data') {
        $db->exec("DELETE FROM price_history; DELETE FROM meal_plans; DELETE FROM shopping_list; DELETE FROM receipt_items; DELETE FROM receipts; DELETE FROM insights; DELETE FROM products; DELETE FROM categories; DELETE FROM users;");
        require_once BASE_PATH . '/database.php';
        seedDatabase();
        $message = ['type'=>'success','text'=>'Demo veriler yeniden yüklendi!'];
        $user = $db->query("SELECT * FROM users LIMIT 1")->fetch();
        $totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $totalReceipts = $db->query("SELECT COUNT(*) FROM receipts")->fetchColumn();
        $totalSpend = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM receipts")->fetchColumn();
    }
}
?>

<?php if ($message): ?>
<script>document.addEventListener('DOMContentLoaded',()=>showToast('<?= $message['type']==='success'?'✅':'❌' ?>','<?= e($message['text']) ?>'));</script>
<?php endif; ?>

<div class="profile-hero">
  <div class="prof-avatar">👤</div>
  <div class="prof-name"><?= e($user['name'] ?? 'Kullanıcı') ?></div>
  <div class="prof-email"><?= e($user['email'] ?? '') ?></div>
  <div class="prof-stats">
    <div class="prof-stat"><span class="pv"><?= $totalProducts ?></span><span class="pl">Ürün</span></div>
    <div class="prof-stat"><span class="pv"><?= $totalReceipts ?></span><span class="pl">Fiş</span></div>
    <div class="prof-stat"><span class="pv"><?= moneyShort($totalSpend) ?></span><span class="pl">Harcama</span></div>
  </div>
</div>

<div class="settings-grp">
  <div class="settings-grp-title">Ev Ayarları</div>
  <div class="settings-list">
    <div class="set-item" id="btn-edit-profile"><div class="set-icon">👤</div><div class="set-text"><div class="set-label">Profil Düzenle</div><div class="set-desc"><?= e($user['name'] ?? '') ?></div></div><span class="set-arrow">›</span></div>
    <div class="set-item"><div class="set-icon">🏠</div><div class="set-text"><div class="set-label">Ev Üyeleri</div><div class="set-desc"><?= $user['household_size'] ?? 4 ?> kişi</div></div><span class="set-arrow">›</span></div>
    <div class="set-item"><div class="set-icon">📍</div><div class="set-text"><div class="set-label">Tercih Edilen Marketler</div><div class="set-desc">Migros, BİM, A101</div></div><span class="set-arrow">›</span></div>
  </div>
</div>

<div class="settings-grp">
  <div class="settings-grp-title">Yapay Zeka</div>
  <div class="settings-list">
    <div class="set-item"><div class="set-icon">🤖</div><div class="set-text"><div class="set-label">AI Tahminleri</div><div class="set-desc">Otomatik ürün bitim tahmini</div></div><div class="toggle on" onclick="this.classList.toggle('on')"></div></div>
    <div class="set-item"><div class="set-icon">🔔</div><div class="set-text"><div class="set-label">Stok Uyarıları</div><div class="set-desc">Ürün azaldığında bildirim</div></div><div class="toggle on" onclick="this.classList.toggle('on')"></div></div>
    <div class="set-item"><div class="set-icon">🍽️</div><div class="set-text"><div class="set-label">Menü Önerisi</div><div class="set-desc">Stoklara göre yemek planı</div></div><div class="toggle on" onclick="this.classList.toggle('on')"></div></div>
    <div class="set-item"><div class="set-icon">💰</div><div class="set-text"><div class="set-label">Fiyat Karşılaştırma</div><div class="set-desc">En uygun market önerisi</div></div><div class="toggle on" onclick="this.classList.toggle('on')"></div></div>
    <div class="set-item"><div class="set-icon">📊</div><div class="set-text"><div class="set-label">Haftalık Rapor</div><div class="set-desc">Tüketim analiz raporu</div></div><div class="toggle" onclick="this.classList.toggle('on')"></div></div>
  </div>
</div>

<div class="settings-grp">
  <div class="settings-grp-title">Uygulama</div>
  <div class="settings-list">
    <div class="set-item"><div class="set-icon">🌐</div><div class="set-text"><div class="set-label">Dil</div><div class="set-desc">Türkçe</div></div><span class="set-arrow">›</span></div>
    <div class="set-item"><div class="set-icon">💾</div><div class="set-text"><div class="set-label">Veri Yedekleme</div><div class="set-desc">Son: <?= date('d.m.Y H:i') ?></div></div><span class="set-arrow">›</span></div>
    <div class="set-item"><div class="set-icon">🔒</div><div class="set-text"><div class="set-label">Gizlilik & Güvenlik</div><div class="set-desc">Şifre, biyometrik</div></div><span class="set-arrow">›</span></div>
  </div>
</div>

<div class="settings-grp">
  <div class="settings-grp-title">Gelişmiş</div>
  <div class="settings-list">
    <form method="post"><input type="hidden" name="action" value="reset_data">
      <button type="submit" class="set-item w-full" onclick="return confirm('Tüm veriler sıfırlanacak. Emin misiniz?')">
        <div class="set-icon" style="background:#fef2f2;">🔄</div>
        <div class="set-text" style="text-align:left;"><div class="set-label">Verileri Sıfırla</div><div class="set-desc">Demo verilere geri dön</div></div>
        <span class="set-arrow">›</span>
      </button>
    </form>
  </div>
</div>

<div class="card text-center mt-2" style="padding:20px;">
  <div style="font-size:0.84rem;font-weight:700;color:var(--brand);"><?= e(APP_NAME) ?> v<?= e(APP_VERSION) ?></div>
  <div style="font-size:0.72rem;color:var(--text-muted);">Evinizin akıllı yöneticisi</div>
  <div style="font-size:0.65rem;color:var(--text-muted);margin-top:6px;">© <?= date('Y') ?> Evly</div>
</div>

<template id="tpl-edit-profile">
  <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">Profil Düzenle</h3>
  <form method="post" action="?page=profile">
    <input type="hidden" name="action" value="update_profile">
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div><label class="form-label">Ad Soyad</label><input type="text" name="user_name" value="<?= e($user['name'] ?? '') ?>" class="form-input" required></div>
      <div><label class="form-label">E-posta</label><input type="email" name="user_email" value="<?= e($user['email'] ?? '') ?>" class="form-input"></div>
      <button type="submit" class="btn btn-brand btn-block btn-lg">💾 Kaydet</button>
    </div>
  </form>
</template>
