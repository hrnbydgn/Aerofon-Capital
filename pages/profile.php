<?php
/**
 * Evly - Profil & Ayarlar Sayfası
 */
$db = getDB();
$user = $db->query("SELECT * FROM users LIMIT 1")->fetch();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalReceipts = $db->query("SELECT COUNT(*) FROM receipts")->fetchColumn();

// Profil güncelleme
$message = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $name = trim($_POST['user_name'] ?? '');
        $email = trim($_POST['user_email'] ?? '');
        if ($name) {
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $user['id']]);
            $user['name'] = $name;
            $user['email'] = $email;
            $message = ['type' => 'success', 'text' => 'Profil güncellendi!'];
        }
    }

    if ($_POST['action'] === 'reset_data') {
        // Tüm verileri sıfırla
        $db->exec("DELETE FROM shopping_list");
        $db->exec("DELETE FROM receipt_items");
        $db->exec("DELETE FROM receipts");
        $db->exec("DELETE FROM insights");
        $db->exec("DELETE FROM products");
        $db->exec("DELETE FROM categories");
        $db->exec("DELETE FROM users");
        // Yeniden seed et
        require_once BASE_PATH . '/database.php';
        seedDatabase();
        $message = ['type' => 'success', 'text' => 'Veriler sıfırlandı ve demo veriler yüklendi!'];
        $user = $db->query("SELECT * FROM users LIMIT 1")->fetch();
        $totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $totalReceipts = $db->query("SELECT COUNT(*) FROM receipts")->fetchColumn();
    }
}
?>

<?php if ($message): ?>
<script>document.addEventListener('DOMContentLoaded',()=>showToast('<?= $message['type'] === 'success' ? '✅' : '❌' ?>','<?= e($message['text']) ?>'));</script>
<?php endif; ?>

<!-- Profil Başlığı -->
<div class="profile-header-card">
  <div class="profile-avatar"><?= $user['avatar'] ?? '👤' ?></div>
  <div class="profile-name"><?= e($user['name'] ?? 'Kullanıcı') ?></div>
  <div class="profile-email"><?= e($user['email'] ?? '') ?></div>
  <div class="profile-stats-row">
    <div class="profile-stat">
      <span class="ps-value"><?= $totalProducts ?></span>
      <span class="ps-label">Ürün</span>
    </div>
    <div class="profile-stat">
      <span class="ps-value"><?= $totalReceipts ?></span>
      <span class="ps-label">Fiş</span>
    </div>
    <div class="profile-stat">
      <span class="ps-value"><?= e(APP_VERSION) ?></span>
      <span class="ps-label">Versiyon</span>
    </div>
  </div>
</div>

<!-- Ev Ayarları -->
<div class="settings-group">
  <div class="settings-group-title">Ev Ayarları</div>
  <div class="settings-list">
    <div class="settings-item" id="btn-edit-profile">
      <div class="si-icon">👤</div>
      <div class="si-text">
        <div class="si-label">Profil Düzenle</div>
        <div class="si-desc"><?= e($user['name'] ?? '') ?></div>
      </div>
      <span class="si-arrow">›</span>
    </div>
    <div class="settings-item">
      <div class="si-icon">🏠</div>
      <div class="si-text">
        <div class="si-label">Ev Üyeleri</div>
        <div class="si-desc">4 kişi aktif</div>
      </div>
      <span class="si-arrow">›</span>
    </div>
    <div class="settings-item">
      <div class="si-icon">📍</div>
      <div class="si-text">
        <div class="si-label">Tercih Edilen Marketler</div>
        <div class="si-desc">Migros, BİM, A101</div>
      </div>
      <span class="si-arrow">›</span>
    </div>
    <div class="settings-item">
      <div class="si-icon">🍽️</div>
      <div class="si-text">
        <div class="si-label">Beslenme Tercihleri</div>
        <div class="si-desc">Normal diyet</div>
      </div>
      <span class="si-arrow">›</span>
    </div>
  </div>
</div>

<!-- Yapay Zeka Ayarları -->
<div class="settings-group">
  <div class="settings-group-title">Yapay Zeka</div>
  <div class="settings-list">
    <div class="settings-item">
      <div class="si-icon">🤖</div>
      <div class="si-text">
        <div class="si-label">AI Tahminleri</div>
        <div class="si-desc">Otomatik ürün bitim tahmini</div>
      </div>
      <div class="toggle active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="settings-item">
      <div class="si-icon">🔔</div>
      <div class="si-text">
        <div class="si-label">Stok Uyarıları</div>
        <div class="si-desc">Ürün azaldığında bildirim</div>
      </div>
      <div class="toggle active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="settings-item">
      <div class="si-icon">💡</div>
      <div class="si-text">
        <div class="si-label">Akıllı Öneriler</div>
        <div class="si-desc">Alışveriş optimizasyonu</div>
      </div>
      <div class="toggle active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="settings-item">
      <div class="si-icon">📊</div>
      <div class="si-text">
        <div class="si-label">Tüketim Analizi</div>
        <div class="si-desc">Haftalık rapor oluştur</div>
      </div>
      <div class="toggle" onclick="this.classList.toggle('active')"></div>
    </div>
  </div>
</div>

<!-- Uygulama Ayarları -->
<div class="settings-group">
  <div class="settings-group-title">Uygulama</div>
  <div class="settings-list">
    <div class="settings-item">
      <div class="si-icon">🌙</div>
      <div class="si-text">
        <div class="si-label">Karanlık Tema</div>
        <div class="si-desc">Varsayılan olarak aktif</div>
      </div>
      <div class="toggle active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="settings-item">
      <div class="si-icon">🌐</div>
      <div class="si-text">
        <div class="si-label">Dil</div>
        <div class="si-desc">Türkçe</div>
      </div>
      <span class="si-arrow">›</span>
    </div>
    <div class="settings-item">
      <div class="si-icon">💾</div>
      <div class="si-text">
        <div class="si-label">Veri Yedekleme</div>
        <div class="si-desc">Son yedek: <?= date('d.m.Y H:i') ?></div>
      </div>
      <span class="si-arrow">›</span>
    </div>
    <div class="settings-item">
      <div class="si-icon">🔒</div>
      <div class="si-text">
        <div class="si-label">Gizlilik & Güvenlik</div>
        <div class="si-desc">Şifre, biyometrik ayarlar</div>
      </div>
      <span class="si-arrow">›</span>
    </div>
  </div>
</div>

<!-- Veri Sıfırlama -->
<div class="settings-group">
  <div class="settings-group-title">Gelişmiş</div>
  <div class="settings-list">
    <form method="post">
      <input type="hidden" name="action" value="reset_data">
      <button type="submit" class="settings-item w-full" onclick="return confirm('Tüm veriler sıfırlanacak. Emin misiniz?')">
        <div class="si-icon" style="background:rgba(239,68,68,0.12);">🔄</div>
        <div class="si-text" style="text-align:left;">
          <div class="si-label">Verileri Sıfırla</div>
          <div class="si-desc">Demo verilere geri dön</div>
        </div>
        <span class="si-arrow">›</span>
      </button>
    </form>
  </div>
</div>

<!-- Versiyon Bilgisi -->
<div class="card card-glass mt-2" style="text-align:center;padding:20px;">
  <div style="font-size:0.85rem;font-weight:600;margin-bottom:4px;"><?= e(APP_NAME) ?> v<?= e(APP_VERSION) ?></div>
  <div style="font-size:0.75rem;color:var(--text-secondary);"><?= e(APP_DESC) ?></div>
  <div style="font-size:0.7rem;color:var(--text-muted);margin-top:8px;">© <?= date('Y') ?> Evly. Tüm hakları saklıdır.</div>
</div>

<!-- Profil Düzenleme Şablonu -->
<template id="tpl-edit-profile">
  <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">Profil Düzenle</h3>
  <form method="post" action="?page=profile">
    <input type="hidden" name="action" value="update_profile">
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div>
        <label class="form-label">Ad Soyad</label>
        <input type="text" name="user_name" value="<?= e($user['name'] ?? '') ?>" class="form-input" required>
      </div>
      <div>
        <label class="form-label">E-posta</label>
        <input type="email" name="user_email" value="<?= e($user['email'] ?? '') ?>" class="form-input">
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">💾 Kaydet</button>
    </div>
  </form>
</template>
