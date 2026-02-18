<?php
/**
 * Tez Yazım Arayüzü - Yapılandırma
 */
define('DB_PATH', __DIR__ . '/data/tez.db');
define('UPLOAD_PATH', __DIR__ . '/data/uploads/');

if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// YÖK/Ulusal Tez Yazım Kuralları Varsayılanları
$GLOBALS['tez_ayarlari'] = [
    'sayfa_boyutu' => 'A4',
    'font' => 'Times New Roman',
    'font_boyutu' => 12,
    'satir_araligi' => 1.5,
    'sol_kenar' => 4.0,   // cm
    'sag_kenar' => 2.5,
    'ust_kenar' => 2.5,
    'alt_kenar' => 2.5,
    'paragraf_girinti' => 1.25,
    'baslik_font_boyutu' => 14,
    'baslik_buyuk_harf' => true,
];
