<?php
session_start();

// Veritabanı yapılandırması (örnek)
// Gerçek uygulamada veritabanı bağlantısı kullanın
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'login_db');

// Demo kullanıcılar (gerçek uygulamada veritabanından çekilmeli)
$demo_users = [
    'admin' => [
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'email' => 'admin@example.com',
        'name' => 'Admin User'
    ],
    'user@example.com' => [
        'password' => password_hash('user123', PASSWORD_DEFAULT),
        'email' => 'user@example.com',
        'name' => 'Demo User'
    ]
];

// POST isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Güvenlik: CSRF koruması eklenebilir
    
    // Form verilerini al ve temizle
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validasyon
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Kullanıcı adı gereklidir';
    }
    
    if (empty($password)) {
        $errors[] = 'Şifre gereklidir';
    }
    
    if (strlen($username) < 3) {
        $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Şifre en az 6 karakter olmalıdır';
    }
    
    // Hata varsa geri dön
    if (!empty($errors)) {
        $_SESSION['error'] = implode(', ', $errors);
        header('Location: index.php');
        exit;
    }
    
    // Kullanıcı doğrulama (demo)
    $user_found = false;
    $user_data = null;
    
    foreach ($demo_users as $key => $user) {
        if ($key === $username || $user['email'] === $username) {
            if (password_verify($password, $user['password'])) {
                $user_found = true;
                $user_data = $user;
                break;
            }
        }
    }
    
    if ($user_found) {
        // Başarılı giriş
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $key;
        $_SESSION['username'] = $username;
        $_SESSION['user_name'] = $user_data['name'];
        $_SESSION['user_email'] = $user_data['email'];
        $_SESSION['login_time'] = time();
        
        // Beni hatırla özelliği
        if ($remember) {
            // Cookie oluştur (30 gün)
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            $_SESSION['remember_token'] = $token;
        }
        
        // Güvenlik: Session yenileme
        session_regenerate_id(true);
        
        // Başarılı giriş sonrası yönlendirme
        header('Location: dashboard.php');
        exit;
        
    } else {
        // Başarısız giriş
        $_SESSION['error'] = 'Kullanıcı adı veya şifre hatalı!';
        
        // Güvenlik: Brute force koruması için gecikme eklenebilir
        sleep(1);
        
        header('Location: index.php');
        exit;
    }
    
} else {
    // GET isteği varsa ana sayfaya yönlendir
    header('Location: index.php');
    exit;
}

/*
 * NOTLAR:
 * 
 * 1. VERİTABANI BAĞLANTISI (MySQL örneği):
 * 
 * $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
 * 
 * if ($conn->connect_error) {
 *     die("Bağlantı hatası: " . $conn->connect_error);
 * }
 * 
 * $stmt = $conn->prepare("SELECT id, username, email, password, name FROM users WHERE username = ? OR email = ?");
 * $stmt->bind_param("ss", $username, $username);
 * $stmt->execute();
 * $result = $stmt->get_result();
 * 
 * if ($result->num_rows === 1) {
 *     $user = $result->fetch_assoc();
 *     if (password_verify($password, $user['password'])) {
 *         // Başarılı giriş
 *     }
 * }
 * 
 * 
 * 2. VERİTABANI TABLOSU (SQL):
 * 
 * CREATE TABLE users (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     username VARCHAR(50) UNIQUE NOT NULL,
 *     email VARCHAR(100) UNIQUE NOT NULL,
 *     password VARCHAR(255) NOT NULL,
 *     name VARCHAR(100),
 *     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *     last_login TIMESTAMP NULL
 * );
 * 
 * 
 * 3. GÜVENLİK ÖNERİLERİ:
 * - HTTPS kullanın
 * - CSRF token ekleyin
 * - Rate limiting uygulayın
 * - SQL injection'a karşı prepared statements kullanın
 * - XSS'e karşı output encoding yapın
 * - Password hashing için password_hash() kullanın
 * - Session güvenliği için session.cookie_httponly ve session.cookie_secure ayarlayın
 */
?>
