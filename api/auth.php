<?php
/**
 * Kimlik Doğrulama API Endpoint
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$db = Database::getInstance()->getConnection();

try {
    switch ($action) {
        case 'register':
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $name = trim($data['name'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';
            
            // Validasyon
            if (empty($name) || empty($email) || empty($password)) {
                jsonResponse(['success' => false, 'message' => 'Tüm alanları doldurun'], 400);
            }
            
            if (!validateEmail($email)) {
                jsonResponse(['success' => false, 'message' => 'Geçersiz e-posta adresi'], 400);
            }
            
            if (strlen($password) < 6) {
                jsonResponse(['success' => false, 'message' => 'Şifre en az 6 karakter olmalı'], 400);
            }
            
            // E-posta kontrolü
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                jsonResponse(['success' => false, 'message' => 'Bu e-posta zaten kayıtlı'], 400);
            }
            
            // Kullanıcı oluştur
            $hashedPassword = hashPassword($password);
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword]);
            
            jsonResponse(['success' => true, 'message' => 'Kayıt başarılı']);
            break;
            
        case 'login':
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                jsonResponse(['success' => false, 'message' => 'E-posta ve şifre gerekli'], 400);
            }
            
            // Kullanıcı kontrol
            $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !verifyPassword($password, $user['password'])) {
                jsonResponse(['success' => false, 'message' => 'E-posta veya şifre hatalı'], 401);
            }
            
            // Session oluştur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            jsonResponse(['success' => true, 'message' => 'Giriş başarılı', 'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]]);
            break;
            
        case 'logout':
            session_destroy();
            jsonResponse(['success' => true, 'message' => 'Çıkış yapıldı']);
            break;
            
        case 'check':
            if (isLoggedIn()) {
                $user = getCurrentUser();
                jsonResponse(['success' => true, 'logged_in' => true, 'user' => $user]);
            } else {
                jsonResponse(['success' => true, 'logged_in' => false]);
            }
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Geçersiz aksiyon'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
}
