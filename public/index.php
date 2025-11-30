<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Giriş yapmışsa dashboard'a, değilse login'e yönlendir
if (isLoggedIn()) {
    header('Location: /public/dashboard.php');
} else {
    header('Location: /public/login.php');
}
exit;
