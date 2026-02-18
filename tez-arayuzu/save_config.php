<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$defaultConfig = require __DIR__ . '/config.php';
$config = $defaultConfig;

$config['font']['family'] = $_POST['font_family'] ?? $config['font']['family'];
$config['font']['size'] = (int)($_POST['font_size'] ?? $config['font']['size']);
$config['spacing']['line'] = (float)($_POST['line_spacing'] ?? $config['spacing']['line']);
$config['margins']['top'] = (float)($_POST['margin_top'] ?? $config['margins']['top']);
$config['margins']['bottom'] = (float)($_POST['margin_bottom'] ?? $config['margins']['bottom']);
$config['margins']['left'] = (float)($_POST['margin_left'] ?? $config['margins']['left']);
$config['margins']['right'] = (float)($_POST['margin_right'] ?? $config['margins']['right']);
$config['numbering']['position'] = $_POST['page_number_pos'] ?? $config['numbering']['position'];

$_SESSION['tez_config'] = $config;

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) mkdir($dataDir);
file_put_contents($dataDir . '/tez_config.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header('Location: index.php');
