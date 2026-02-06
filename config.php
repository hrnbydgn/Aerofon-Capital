<?php
/**
 * Evly - Akıllı Ev Yönetimi
 * Konfigürasyon Dosyası
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
date_default_timezone_set('Europe/Istanbul');

define('APP_NAME', 'Evly');
define('APP_VERSION', '2.0.0');
define('APP_DESC', 'Akıllı Ev Yönetimi');
define('BASE_PATH', __DIR__);
define('DATA_PATH', BASE_PATH . '/data');
define('DB_PATH', DATA_PATH . '/evly.db');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function trDate(?string $date): string {
    if (!$date) return '—';
    $ts = strtotime($date);
    $now = time();
    $diff = $now - $ts;
    if ($diff < 0) return date('d.m.Y', $ts);
    if ($diff < 60) return 'Az önce';
    if ($diff < 3600) return floor($diff / 60) . ' dk önce';
    if ($diff < 86400) return floor($diff / 3600) . ' saat önce';
    if ($diff < 172800) return 'Dün, ' . date('H:i', $ts);
    if ($diff < 604800) return floor($diff / 86400) . ' gün önce';
    return date('d.m.Y', $ts);
}

function trDateShort(?string $date): string {
    if (!$date) return '—';
    return date('d.m.Y H:i', strtotime($date));
}

function daysLeft(?string $predictedEnd): array {
    if (!$predictedEnd) return ['text' => '—', 'days' => 999, 'level' => 'good'];
    $days = (int)ceil((strtotime($predictedEnd) - time()) / 86400);
    if ($days <= 0) return ['text' => 'Bitti!', 'days' => 0, 'level' => 'critical'];
    if ($days === 1) return ['text' => 'Yarın bitecek', 'days' => 1, 'level' => 'critical'];
    if ($days <= 3) return ['text' => "$days gün kaldı", 'days' => $days, 'level' => 'critical'];
    if ($days <= 7) return ['text' => "$days gün kaldı", 'days' => $days, 'level' => 'warning'];
    return ['text' => "$days gün kaldı", 'days' => $days, 'level' => 'good'];
}

function statusLevel(int $percentage): string {
    if ($percentage <= 20) return 'critical';
    if ($percentage <= 40) return 'warning';
    return 'good';
}

function pct(float $qty, float $max): int {
    if ($max <= 0) return 0;
    return max(0, min(100, (int)round(($qty / $max) * 100)));
}

// AI selamlaması
function aiGreeting(): string {
    $hour = (int)date('H');
    if ($hour < 6) return 'İyi geceler';
    if ($hour < 12) return 'Günaydın';
    if ($hour < 18) return 'İyi günler';
    return 'İyi akşamlar';
}

function aiMotivation(): string {
    $msgs = [
        'Bugün mutfağınız kontrol altında! 🎯',
        'Akıllı alışveriş için hazırım! 🛒',
        'Ev ekonominizi birlikte yönetelim! 💚',
        'Tasarruf fırsatlarını yakalayalım! 🌱',
        'Her şey kontrol altında, rahat olun! ☀️',
    ];
    return $msgs[array_rand($msgs)];
}

// Para formatlama
function money(float $val): string {
    return '₺' . number_format($val, 2, ',', '.');
}

function moneyShort(float $val): string {
    if ($val >= 1000) return '₺' . number_format($val / 1000, 1, ',', '.') . 'k';
    return '₺' . number_format($val, 0, ',', '.');
}
