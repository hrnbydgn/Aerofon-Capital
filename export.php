<?php
/**
 * Tez Word çıktısı - YÖK/Ulusal yazım kurallarına uygun
 */
require_once __DIR__ . '/database.php';
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    header('Content-Type: text/plain; charset=utf-8');
    die("PHPWord kurulu değil. Çalıştırın: composer install");
}
require_once __DIR__ . '/vendor/autoload.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('HTTP/1.1 400 Bad Request');
    exit('Proje ID gerekli');
}

$proje = getProjeById($id);
$varsayilan = [
    'font' => 'Times New Roman',
    'font_boyutu' => 12,
    'satir_araligi' => 1.5,
    'sol_kenar' => 4.0,
    'sag_kenar' => 2.5,
    'ust_kenar' => 2.5,
    'alt_kenar' => 2.5,
    'paragraf_girinti' => 1.25,
    'baslik_font_boyutu' => 14,
    'baslik_buyuk_harf' => true,
];
$ayarlar = array_merge($varsayilan, $proje['ayarlar'] ?? []);
if (!isset($proje['ayarlar']['alt_kenar']) && isset($proje['ayarlar']['ust_kenar'])) {
    $ayarlar['alt_kenar'] = $ayarlar['ust_kenar'];
}

$phpWord = new \PhpOffice\PhpWord\PhpWord();
$section = $phpWord->addSection([
    'marginLeft' => $ayarlar['sol_kenar'] * 28.35,
    'marginRight' => $ayarlar['sag_kenar'] * 28.35,
    'marginTop' => $ayarlar['ust_kenar'] * 28.35,
    'marginBottom' => (float)($ayarlar['alt_kenar'] ?? 2.5) * 28.35,
]);

$fontAdi = $ayarlar['font'];
$fontBoyut = (int)$ayarlar['font_boyutu'];
$baslikFont = (int)($ayarlar['baslik_font_boyutu'] ?? 14);
$satirAraligi = (float)($ayarlar['satir_araligi'] ?? 1.5);
$girinti = (float)($ayarlar['paragraf_girinti'] ?? 1.25) * 28.35;

$paragrafStili = [
    'align' => 'both',
    'spaceAfter' => 0,
    'lineHeight' => $satirAraligi,
    'indentation' => ['firstLine' => $girinti],
];
$fontStili = ['name' => $fontAdi, 'size' => $fontBoyut];

// Kapak / Başlık (YÖK formatı)
$baslikFontStili = ['name' => $fontAdi, 'size' => $baslikFont, 'bold' => true];
$kapakBaslik = $ayarlar['baslik_buyuk_harf'] ? mb_strtoupper($proje['baslik'], 'UTF-8') : $proje['baslik'];
$section->addText($kapakBaslik, $baslikFontStili, ['align' => 'center', 'spaceAfter' => 240]);
$section->addText('', $fontStili, ['spaceAfter' => 120]);

if (!empty($proje['yazar'])) {
    $section->addText('Hazırlayan: ' . $proje['yazar'], $fontStili, ['align' => 'center', 'spaceAfter' => 60]);
}
if (!empty($proje['danisman'])) {
    $section->addText('Danışman: ' . $proje['danisman'], $fontStili, ['align' => 'center', 'spaceAfter' => 60]);
}
if (!empty($proje['universite']) || !empty($proje['bolum'])) {
    $kurum = trim(($proje['universite'] ?? '') . ' - ' . ($proje['enstitu'] ?? '') . ' - ' . ($proje['bolum'] ?? ''));
    $kurum = trim($kurum, ' -');
    if ($kurum) {
        $section->addText($kurum, $fontStili, ['align' => 'center', 'spaceAfter' => 60]);
    }
}
if (!empty($proje['yil'])) {
    $section->addText($proje['yil'], $fontStili, ['align' => 'center', 'spaceAfter' => 240]);
}

$section->addPageBreak();

// Bölümler
$bolumBaslikStili = ['name' => $fontAdi, 'size' => $baslikFont, 'bold' => true];
foreach ($proje['bolumler'] ?? [] as $b) {
    $baslikMetin = $ayarlar['baslik_buyuk_harf'] ? mb_strtoupper($b['baslik'], 'UTF-8') : $b['baslik'];
    $section->addText($baslikMetin, $bolumBaslikStili, ['spaceBefore' => 240, 'spaceAfter' => 120]);

    $paragraflar = preg_split('/\n\s*\n/', trim($b['icerik'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    foreach ($paragraflar as $p) {
        $p = trim($p);
        if ($p === '') continue;
        $section->addText($p, $fontStili, $paragrafStili);
    }
    $section->addText('', $fontStili, ['spaceAfter' => 120]);
}

$dosyaAdi = preg_replace('/[^a-zA-Z0-9\-_ğüşıöçĞÜŞİÖÇ\s]/', '', $proje['baslik']);
$dosyaAdi = preg_replace('/\s+/', '_', trim($dosyaAdi)) ?: 'tez';

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $dosyaAdi . '.docx"');
header('Cache-Control: max-age=0');

$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('php://output');
exit;
