<?php
session_start();

// Tüm session verilerini temizle
$_SESSION = array();

// Session cookie'sini sil
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Remember me cookie'sini sil
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Session'ı yok et
session_destroy();

// Başarı mesajı için yeni session başlat
session_start();
$_SESSION['success'] = 'Başarıyla çıkış yaptınız!';

// Login sayfasına yönlendir
header('Location: index.php');
exit;
?>
