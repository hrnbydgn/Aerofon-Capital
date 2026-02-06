<?php
/**
 * Evly - Akıllı Ev Yönetimi
 * Konfigürasyon Dosyası
 */

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Uygulama sabitleri
define('APP_NAME', 'Evly');
define('APP_VERSION', '1.0.0');
define('APP_DESC', 'Akıllı Ev Yönetimi');
define('BASE_PATH', __DIR__);
define('DATA_PATH', BASE_PATH . '/data');
define('DB_PATH', DATA_PATH . '/evly.db');

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantısı (Singleton)
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('PRAGMA foreign_keys=ON');
    }
    return $pdo;
}

// JSON yanıt yardımcısı
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// XSS koruması
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Tarih formatlama
function trDate(string $date): string {
    $ts = strtotime($date);
    $now = time();
    $diff = $now - $ts;

    if ($diff < 60) return 'Az önce';
    if ($diff < 3600) return floor($diff / 60) . ' dakika önce';
    if ($diff < 86400) return floor($diff / 3600) . ' saat önce';
    if ($diff < 172800) return 'Dün';
    if ($diff < 604800) return floor($diff / 86400) . ' gün önce';

    return date('d M Y', $ts);
}

// Kalan gün hesaplama
function daysLeft(?string $predictedEnd): string {
    if (!$predictedEnd) return '—';
    $days = (int)ceil((strtotime($predictedEnd) - time()) / 86400);
    if ($days <= 0) return 'Bitti!';
    if ($days === 1) return 'Yarın';
    return $days . ' gün';
}

// Durum seviyesi
function statusLevel(int $percentage): string {
    if ($percentage <= 20) return 'critical';
    if ($percentage <= 45) return 'warning';
    return 'good';
}
