<?php
/**
 * Yardımcı Fonksiyonlar
 */

/**
 * Güvenli çıktı - XSS koruması
 */
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * JSON response gönder
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Kullanıcı giriş yapmış mı kontrol et
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Kullanıcı bilgilerini al
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, name, email, avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Login gerektir - yoksa yönlendir
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /public/login.php');
        exit;
    }
}

/**
 * Kullanıcı projeye erişebilir mi kontrol et
 */
function canAccessProject($userId, $projectId) {
    $db = Database::getInstance()->getConnection();
    
    // Proje sahibi mi?
    $stmt = $db->prepare("SELECT owner_id FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();
    
    if ($project && $project['owner_id'] == $userId) {
        return true;
    }
    
    // Proje üyesi mi?
    $stmt = $db->prepare("SELECT id FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$projectId, $userId]);
    return $stmt->fetch() !== false;
}

/**
 * Kullanıcının projedeki rolünü al
 */
function getUserProjectRole($userId, $projectId) {
    $db = Database::getInstance()->getConnection();
    
    // Proje sahibi mi?
    $stmt = $db->prepare("SELECT owner_id FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();
    
    if ($project && $project['owner_id'] == $userId) {
        return 'Yönetici';
    }
    
    // Üye rolünü kontrol et
    $stmt = $db->prepare("SELECT role FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$projectId, $userId]);
    $member = $stmt->fetch();
    
    return $member ? $member['role'] : null;
}

/**
 * Tarih formatla
 */
function formatDate($date) {
    if (!$date) return '';
    return date('d.m.Y', strtotime($date));
}

/**
 * Tarih/saat formatla
 */
function formatDateTime($datetime) {
    if (!$datetime) return '';
    return date('d.m.Y H:i', strtotime($datetime));
}

/**
 * Durum rengini al
 */
function getStatusColor($status) {
    $colors = [
        'Aktif' => 'blue',
        'Tamamlandı' => 'green',
        'Askıda' => 'yellow',
        'Yapılacak' => 'gray',
        'Devam Ediyor' => 'blue',
    ];
    return $colors[$status] ?? 'gray';
}

/**
 * Öncelik rengini al
 */
function getPriorityColor($priority) {
    $colors = [
        'Düşük' => 'green',
        'Orta' => 'yellow',
        'Yüksek' => 'red',
    ];
    return $colors[$priority] ?? 'gray';
}

/**
 * CSRF token oluştur
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrula
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * E-posta doğrula
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Şifre hashleme
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Şifre doğrulama
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
