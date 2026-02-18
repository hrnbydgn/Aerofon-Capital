<?php
session_start();
require_once 'config.php';
require_once 'config.sections.php';

$sections = require 'config.sections.php';
$defaultConfig = require 'config.php';
$configFile = __DIR__ . '/data/tez_config.json';
$savedConfig = (file_exists($configFile)) ? json_decode(file_get_contents($configFile), true) : [];
$config = array_merge($defaultConfig, $savedConfig ?? [], $_SESSION['tez_config'] ?? []);

// Bölümleri sırala
uasort($sections, fn($a, $b) => $a['order'] <=> $b['order']);

// Kaydedilmiş içerikleri yükle
$dataFile = __DIR__ . '/data/tez_data.json';
if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data');
$content = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tez Yazım Arayüzü</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <h1>Tez Yazım Arayüzü</h1>
        <div class="header-actions">
            <button type="button" class="btn btn-secondary" onclick="openSettings()">⚙️ Ayarlar</button>
            <button type="button" class="btn btn-primary" onclick="exportWord()">📄 Word'e Aktar</button>
        </div>
    </header>

    <main class="main">
        <aside class="sidebar">
            <nav class="section-nav">
                <?php foreach ($sections as $key => $sec): ?>
                <button type="button" class="nav-item" data-section="<?= htmlspecialchars($key) ?>">
                    <?= htmlspecialchars($sec['title']) ?>
                </button>
                <?php endforeach; ?>
            </nav>
        </aside>

        <section class="editor-area">
            <?php foreach ($sections as $key => $sec): ?>
            <div class="editor-panel" id="panel-<?= htmlspecialchars($key) ?>" data-section="<?= htmlspecialchars($key) ?>">
                <h2 class="panel-title"><?= htmlspecialchars($sec['title']) ?></h2>
                <textarea class="tez-editor" name="<?= htmlspecialchars($key) ?>" 
                    placeholder="Buraya içeriğinizi yazın..."><?= htmlspecialchars($content[$key] ?? '') ?></textarea>
            </div>
            <?php endforeach; ?>
        </section>
    </main>

    <!-- Ayarlar Modal -->
    <div id="settingsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tez Yazım Ayarları</h2>
                <button type="button" class="close-btn" onclick="closeSettings()">&times;</button>
            </div>
            <form id="settingsForm">
                <div class="form-group">
                    <label>Yazı Tipi</label>
                    <select name="font_family">
                        <option value="Times New Roman" <?= ($config['font']['family'] ?? '') === 'Times New Roman' ? 'selected' : '' ?>>Times New Roman</option>
                        <option value="Arial" <?= ($config['font']['family'] ?? '') === 'Arial' ? 'selected' : '' ?>>Arial</option>
                        <option value="Georgia" <?= ($config['font']['family'] ?? '') === 'Georgia' ? 'selected' : '' ?>>Georgia</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Yazı Boyutu (pt)</label>
                        <input type="number" name="font_size" value="<?= $config['font']['size'] ?? 12 ?>" min="10" max="14">
                    </div>
                    <div class="form-group">
                        <label>Satır Aralığı</label>
                        <select name="line_spacing">
                            <option value="1" <?= ($config['spacing']['line'] ?? 1.5) == 1 ? 'selected' : '' ?>>Tek</option>
                            <option value="1.15" <?= ($config['spacing']['line'] ?? 1.5) == 1.15 ? 'selected' : '' ?>>1.15</option>
                            <option value="1.5" <?= ($config['spacing']['line'] ?? 1.5) == 1.5 ? 'selected' : '' ?>>1.5 (Önerilen)</option>
                            <option value="2" <?= ($config['spacing']['line'] ?? 1.5) == 2 ? 'selected' : '' ?>>Çift</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Üst Kenar Boşluğu (cm)</label>
                        <input type="number" name="margin_top" value="<?= $config['margins']['top'] ?? 3 ?>" min="2" max="5" step="0.5">
                    </div>
                    <div class="form-group">
                        <label>Alt Kenar Boşluğu (cm)</label>
                        <input type="number" name="margin_bottom" value="<?= $config['margins']['bottom'] ?? 3 ?>" min="2" max="5" step="0.5">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sol Kenar (cm)</label>
                        <input type="number" name="margin_left" value="<?= $config['margins']['left'] ?? 3 ?>" min="2" max="5" step="0.5">
                    </div>
                    <div class="form-group">
                        <label>Sağ Kenar (cm)</label>
                        <input type="number" name="margin_right" value="<?= $config['margins']['right'] ?? 3 ?>" min="2" max="5" step="0.5">
                    </div>
                </div>
                <div class="form-group">
                    <label>Sayfa Numarası Konumu</label>
                    <select name="page_number_pos">
                        <option value="bottom_center" <?= ($config['numbering']['position'] ?? '') === 'bottom_center' ? 'selected' : '' ?>>Alt Orta</option>
                        <option value="bottom_right" <?= ($config['numbering']['position'] ?? '') === 'bottom_right' ? 'selected' : '' ?>>Alt Sağ</option>
                        <option value="bottom_left" <?= ($config['numbering']['position'] ?? '') === 'bottom_left' ? 'selected' : '' ?>>Alt Sol</option>
                        <option value="none" <?= ($config['numbering']['position'] ?? '') === 'none' ? 'selected' : '' ?>>Yok</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeSettings()">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
