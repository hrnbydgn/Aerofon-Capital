<?php
session_start();
require_once 'config.php';
require_once 'config.sections.php';

$sections = require 'config.sections.php';
uasort($sections, fn($a, $b) => $a['order'] <=> $b['order']);

$dataFile = __DIR__ . '/data/tez_data.json';
$configFile = __DIR__ . '/data/tez_config.json';

$content = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
$config = $defaultConfig = require 'config.php';
if (file_exists($configFile)) {
    $savedConfig = json_decode(file_get_contents($configFile), true);
    if ($savedConfig) $config = array_merge($config, $savedConfig);
}

$fontFamily = $config['font']['family'] ?? 'Times New Roman';
$fontSize = $config['font']['size'] ?? 12;
$lineHeight = ($config['spacing']['line'] ?? 1.5) * 100;
$marginTop = $config['margins']['top'] ?? 3;
$marginBottom = $config['margins']['bottom'] ?? 3;
$marginLeft = $config['margins']['left'] ?? 3;
$marginRight = $config['margins']['right'] ?? 3;

$html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">';
$html .= '<head><meta charset="UTF-8"></head>';
$html .= '<body style="font-family:' . htmlspecialchars($fontFamily) . '; font-size:' . $fontSize . 'pt; ';
$html .= 'line-height:' . $lineHeight . '%; margin:' . $marginTop . 'cm ' . $marginRight . 'cm ' . $marginBottom . 'cm ' . $marginLeft . 'cm; text-align:justify;">';

$first = true;
foreach ($sections as $key => $sec) {
    $text = $content[$key] ?? '';
    $html .= '<div' . ($first ? '' : ' style="page-break-before:always;"') . '>';
    $html .= '<h2 style="font-size:14pt; font-weight:bold; margin-bottom:12pt;">' . htmlspecialchars($sec['title']) . '</h2>';
    $html .= '<div style="margin-bottom:6pt;">' . nl2br(htmlspecialchars($text)) . '</div>';
    $html .= '</div>';
    $first = false;
}

$html .= '</body></html>';

header('Content-Type: application/vnd.ms-word');
header('Content-Disposition: attachment; filename="tez_' . date('Y-m-d_H-i') . '.doc"');
header('Cache-Control: no-cache');
echo "\xEF\xBB\xBF" . $html;
