<?php
/**
 * Tez RTF çıktısı - Bağımlılık yok, AWebServer uyumlu
 * YÖK/Ulusal yazım kurallarına uygun
 */
require_once __DIR__ . '/database.php';

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

function rtfEscape(string $s): string {
    $out = '';
    $len = mb_strlen($s, 'UTF-8');
    for ($i = 0; $i < $len; $i++) {
        $c = mb_substr($s, $i, 1, 'UTF-8');
        $cp = function_exists('mb_ord') ? mb_ord($c, 'UTF-8') : hexdec(bin2hex(mb_convert_encoding($c, 'UCS-4BE', 'UTF-8')));
        if ($c === '\\' || $c === '{' || $c === '}') {
            $out .= '\\' . $c;
        } elseif ($cp < 128) {
            $out .= $c;
        } else {
            $out .= '\\u' . $cp . '?';
        }
    }
    return $out;
}

function cmToTwips(float $cm): int {
    return (int)round($cm * 567);
}

$fs = (int)($ayarlar['font_boyutu'] ?? 12) * 2; // yarım punto
$fsBaslik = (int)($ayarlar['baslik_font_boyutu'] ?? 14) * 2;
$li = cmToTwips((float)($ayarlar['paragraf_girinti'] ?? 1.25));
$sl = (int)(($ayarlar['satir_araligi'] ?? 1.5) * 240); // satır aralığı
$buyukHarf = ($ayarlar['baslik_buyuk_harf'] ?? true);

$rtf = "{\\rtf1\\ansi\\ansicpg1254\\deff0\n";
$rtf .= "{\\fonttbl{\\f0 " . $ayarlar['font'] . ";}}\n";
$rtf .= "\\paperw11906\\paperh16838\n";
$rtf .= "\\margl" . cmToTwips($ayarlar['sol_kenar']) . "\\margr" . cmToTwips($ayarlar['sag_kenar']);
$rtf .= "\\margt" . cmToTwips($ayarlar['ust_kenar']) . "\\margb" . cmToTwips($ayarlar['alt_kenar'] ?? 2.5) . "\n";
$rtf .= "\\f0\n\n";

// Kapak
$kapakBaslik = $buyukHarf ? mb_strtoupper($proje['baslik'], 'UTF-8') : $proje['baslik'];
$rtf .= "{\\pard\\qc\\fs" . $fsBaslik . "\\b " . rtfEscape($kapakBaslik) . "\\par}\n";
$rtf .= "{\\pard\\qc\\sa240\\par}\n";

if (!empty($proje['yazar'])) {
    $rtf .= "{\\pard\\qc\\fs" . $fs . " " . rtfEscape('Hazırlayan: ' . $proje['yazar']) . "\\par}\n";
}
if (!empty($proje['danisman'])) {
    $rtf .= "{\\pard\\qc\\fs" . $fs . " " . rtfEscape('Danışman: ' . $proje['danisman']) . "\\par}\n";
}
$kurum = trim(($proje['universite'] ?? '') . ' - ' . ($proje['enstitu'] ?? '') . ' - ' . ($proje['bolum'] ?? ''), ' -');
if ($kurum) {
    $rtf .= "{\\pard\\qc\\fs" . $fs . " " . rtfEscape($kurum) . "\\par}\n";
}
if (!empty($proje['yil'])) {
    $rtf .= "{\\pard\\qc\\fs" . $fs . " " . rtfEscape((string)$proje['yil']) . "\\par}\n";
}

$rtf .= "{\\pard\\sa480\\par}\n";
$rtf .= "\\page\n\n";

// Bölümler (hiyerarşik)
function bolumYaz(array $bolumler, array $parents, array $ayarlar, string &$rtf, int $seviye = 0): void {
    $fs = (int)($ayarlar['font_boyutu'] ?? 12) * 2;
    $fsBaslik = max(10, (int)($ayarlar['baslik_font_boyutu'] ?? 14) - $seviye * 2) * 2;
    $li = cmToTwips((float)($ayarlar['paragraf_girinti'] ?? 1.25));
    $sl = (int)(($ayarlar['satir_araligi'] ?? 1.5) * 240);
    $buyukHarf = $ayarlar['baslik_buyuk_harf'] ?? true;
    $girinti = $seviye * cmToTwips(0.5);

    foreach ($bolumler as $b) {
        $baslikMetin = $buyukHarf ? mb_strtoupper($b['baslik'], 'UTF-8') : $b['baslik'];
        $rtf .= "{\\pard\\li" . $girinti . "\\sb240\\sa120\\fs" . $fsBaslik . "\\b " . rtfEscape($baslikMetin) . "\\par}\n";

        $paragraflar = preg_split('/\n\s*\n/', trim($b['icerik'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($paragraflar as $p) {
            $p = trim($p);
            if ($p === '') continue;
            $rtf .= "{\\pard\\li" . ($girinti + $li) . "\\fi-" . $li . "\\sl" . $sl . " " . rtfEscape($p) . "\\par}\n";
        }

        $cocuklar = $parents[(int)$b['id']] ?? [];
        if (!empty($cocuklar)) {
            bolumYaz($cocuklar, $parents, $ayarlar, $rtf, $seviye + 1);
        }
        $rtf .= "{\\pard\\sa120\\par}\n";
    }
}

$tum = $proje['bolumler'] ?? [];
$parents = [];
foreach ($tum as $b) {
    $pid = (int)($b['parent_id'] ?? 0);
    if (!isset($parents[$pid])) $parents[$pid] = [];
    $parents[$pid][] = $b;
}
foreach ($parents as &$arr) {
    usort($arr, function ($a, $b) {
        return ($a['sira'] ?? 0) - ($b['sira'] ?? 0);
    });
}
$kokler = $parents[0] ?? [];
unset($parents[0]);
bolumYaz($kokler, $parents, $ayarlar, $rtf);

$rtf .= "}\n";

$dosyaAdi = preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $proje['baslik']);
$dosyaAdi = preg_replace('/\s+/', '_', trim($dosyaAdi)) ?: 'tez';

header('Content-Type: application/rtf');
header('Content-Disposition: attachment; filename="' . $dosyaAdi . '.rtf"');
header('Cache-Control: max-age=0');
echo $rtf;
exit;
