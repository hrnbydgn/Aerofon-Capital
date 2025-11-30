<?php
/**
 * Veritabanı ve Uygulama Yapılandırması
 */

// Hata raporlama (production'da kapatın)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı Ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'project_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Uygulama Ayarları
define('APP_NAME', 'Proje Yönetim Sistemi');
define('APP_URL', 'http://localhost');
define('BASE_PATH', dirname(__DIR__));

// Session Ayarları
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // HTTPS kullanıyorsanız 1 yapın
ini_set('session.cookie_samesite', 'Strict');

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Europe/Istanbul');
