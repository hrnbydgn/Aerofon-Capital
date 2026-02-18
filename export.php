<?php
/**
 * Tez RTF çıktısı - Tez Yazım Kılavuzuna uygun
 * Bağımlılık yok, AWebServer uyumlu
 */
require_once __DIR__ . '/database.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('HTTP/1.1 400 Bad Request');
    exit('Proje ID gerekli');
}

$proje = getProjeById($id);

$ayarlar = array_merge([
    'font' => 'Times New Roman',
    'font_boyutu' => 12,
    'satir_araligi' => 1.5,
    'sol_kenar' => 4.0,
    'sag_kenar' => 2.5,
    'ust_kenar' => 2.5,
    'ust_bolum' => 5.0,
    'alt_kenar' => 2.5,
    'paragraf_girinti' => 1.0,
    'tablo_font' => 12,
    'tablo_satir' => 1,
    'tablo_hizalama' => 'sol',
    'tablo_baslik_kalin' => true,
    'tablo_yazi_font' => 12,
    'tablo_yazi_konum' => 'ust_sol',
    'tablo_yazi_kalin' => true,
    'tablo_kenarlik_kalin' => 1,
    'tablo_kenarlik_stil' => 'tek',
    'tablo_dis_kenarlik' => true,
    'tablo_ic_kenarlik' => true,
    'tablo_padding_ust' => 1.5,
    'tablo_padding_alt' => 1.5,
    'tablo_padding_sol' => 2,
    'tablo_padding_sag' => 2,
    'tablo_ust_bosluk' => 0.5,
    'tablo_alt_bosluk' => 0.5,
    'tablo_genislik' => 100,
    'tablo_ortala' => true,
    'sekil_font' => 12,
    'sekil_konum' => 'alt_orta',
    'sekil_kalin' => true,
    'kaynak_font' => 10,
], $proje['ayarlar'] ?? []);

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

function baslikCase(string $s, int $seviye): string {
    $s = trim($s);
    if ($s === '') return $s;
    $kucuk = ['ve', 'veya', 'ile', 'de', 'da', 'ki', 'mi', 'mı', 'mu', 'mü'];
    if ($seviye === 0) {
        return mb_strtoupper($s, 'UTF-8');  // 1. derece: TÜMÜ BÜYÜK
    }
    if ($seviye === 1) {
        $kelimeler = preg_split('/\s+/u', $s, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($kelimeler as $i => $k) {
            $kAlt = mb_strtolower($k, 'UTF-8');
            if ($i > 0 && in_array($kAlt, $kucuk)) {
                $kelimeler[$i] = $kAlt;
            } else {
                $kelimeler[$i] = mb_strtoupper(mb_substr($k, 0, 1, 'UTF-8'), 'UTF-8') . mb_strtolower(mb_substr($k, 1, null, 'UTF-8'), 'UTF-8');
            }
        }
        return implode(' ', $kelimeler);  // 2. derece: Her kelimenin ilk harfi büyük
    }
    $kelimeler = preg_split('/\s+/u', $s, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($kelimeler as $i => $k) {
        $kAlt = mb_strtolower($k, 'UTF-8');
        if ($i === 0) {
            $kelimeler[$i] = mb_strtoupper(mb_substr($k, 0, 1, 'UTF-8'), 'UTF-8') . mb_strtolower(mb_substr($k, 1, null, 'UTF-8'), 'UTF-8');
        } elseif (in_array($kAlt, $kucuk)) {
            $kelimeler[$i] = $kAlt;
        } else {
            $kelimeler[$i] = mb_strtoupper(mb_substr($k, 0, 1, 'UTF-8'), 'UTF-8') . mb_strtolower(mb_substr($k, 1, null, 'UTF-8'), 'UTF-8');
        }
    }
    return implode(' ', $kelimeler);  // 3+: İlk kelime büyük, bağlaçlar küçük
}

$fs = 24;  // 12 punto = 24 yarım punto
$li = cmToTwips((float)($ayarlar['paragraf_girinti'] ?? 1.0));
$sl = (int)(1.5 * 240);  // 1.5 satır aralığı
$slTek = 240;  // 1 satır aralığı (başlıklar için)

$rtf = "{\\rtf1\\ansi\\ansicpg1254\\deff0\n";
$rtf .= "{\\fonttbl{\\f0 " . ($ayarlar['font'] ?? 'Times New Roman') . ";}}\n";
$rtf .= "\\paperw11906\\paperh16838\n";
$rtf .= "\\margl" . cmToTwips($ayarlar['sol_kenar']) . "\\margr" . cmToTwips($ayarlar['sag_kenar']);
$rtf .= "\\margt" . cmToTwips($ayarlar['ust_kenar']) . "\\margb" . cmToTwips($ayarlar['alt_kenar'] ?? 2.5) . "\n";
$rtf .= "\\f0\n\n";

// Kapak (dış kapak - numara yok)
$rtf .= "{\\pard\\qc\\fs" . $fs . "\\b " . rtfEscape(mb_strtoupper($proje['baslik'], 'UTF-8')) . "\\par}\n";
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

// Bölümler - Kılavuz: 1., 1.1., 1.1.1. numaralandırma, başlık sonunda noktalama yok
// 1. derece: 5 cm üstten, 12pt, TÜMÜ BÜYÜK, koyu, 1 cm girinti
// 2. derece: 12pt, her kelime büyük, koyu, 1 cm girinti
// 3. derece: 12pt, ilk kelime büyük, koyu, 1 cm girinti
// Paragraf: 1 cm girinti, 1.5 satır, iki yana yaslı, paragraftan sonra boşluk (sa), aralarında ekstra satır yok

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

function icerikParse(string $s): array {
    $s = trim($s);
    if ($s === '') return [];
    if (isset($s[0]) && $s[0] === '[') {
        $d = json_decode($s, true);
        return is_array($d) ? $d : [];
    }
    return [];
}

function bloklariRtfYaz(array $bloklar, array $ayarlar, string &$rtf, int $li, int $sl, int $saParagraf, int $fs): void {
    $tabloFs = ((int)($ayarlar['tablo_font'] ?? 12)) * 2;
    $tabloSl = (int)((float)($ayarlar['tablo_satir'] ?? 1) * 240);
    $tabloYaziFs = ((int)($ayarlar['tablo_yazi_font'] ?? 12)) * 2;
    $sekilFs = ((int)($ayarlar['sekil_font'] ?? 12)) * 2;

    foreach ($bloklar as $blok) {
        $tip = $blok['type'] ?? 'text';
        if ($tip === 'text') {
            $paragraflar = preg_split('/\n\s*\n/', trim($blok['content'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($paragraflar as $p) {
                $p = trim($p);
                if ($p === '') continue;
                $rtf .= "{\\pard\\li0\\fi" . $li . "\\qj\\sa" . $saParagraf . "\\sl" . $sl . "\\fs" . $fs . " " . rtfEscape($p) . "\\par}\n";
            }
        } elseif ($tip === 'table') {
            $caption = $blok['caption'] ?? 'Tablo:';
            $kalın = ($ayarlar['tablo_yazi_kalin'] ?? true) ? '\\b ' : '';
            $rtf .= "{\\pard\\li" . $li . "\\sa" . $saParagraf . "\\sl240\\fs" . $tabloYaziFs . " " . $kalın . rtfEscape($caption) . "\\par}\n";
            $data = $blok['data'] ?? [];
            $colW = 2000;
            foreach ($data as $row) {
                $cells = array_map(function ($c) {
                    return rtfEscape((string)$c);
                }, $row);
                $pos = 0;
                $cellx = '';
                foreach ($cells as $_) {
                    $pos += $colW;
                    $cellx .= "\\cellx" . $pos . " ";
                }
                $rtf .= "{\\trowd\\trgaph0" . $cellx . "\\intbl ";
                foreach ($cells as $c) {
                    $rtf .= $c . " \\cell ";
                }
                $rtf .= "\\row}\n";
            }
            if (!empty($blok['source'])) {
                $kaynakFs = ((int)($ayarlar['kaynak_font'] ?? 10)) * 2;
                $rtf .= "{\\pard\\li" . $li . "\\sa" . $saParagraf . "\\sl240\\fs" . $kaynakFs . " Kaynak: " . rtfEscape($blok['source']) . "\\par}\n";
            }
        } elseif ($tip === 'image') {
            $caption = $blok['caption'] ?? 'Şekil:';
            $rtf .= "{\\pard\\qc\\sa" . $saParagraf . "\\sl240\\fs" . $sekilFs . "\\b " . rtfEscape($caption) . "\\par}\n";
            $imgPath = $blok['src'] ?? '';
            if ($imgPath && file_exists(__DIR__ . '/' . $imgPath)) {
                $bin = file_get_contents(__DIR__ . '/' . $imgPath);
                $hex = bin2hex($bin);
                $ext = strtolower(pathinfo($imgPath, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg'])) {
                    $rtf .= "{\\pict\\jpegblip\\picwgoal4000\\pichgoal3000 " . $hex . "}\n";
                } elseif ($ext === 'png') {
                    $rtf .= "{\\pict\\pngblip\\picwgoal4000\\pichgoal3000 " . $hex . "}\n";
                }
            }
        }
    }
}

function bolumYaz(array $bolumler, array $parents, array $ayarlar, string &$rtf, array $numara = []): void {
    $fs = 24;
    $li = cmToTwips(1.0);  // 1 cm girinti (kılavuz)
    $sl = (int)(1.5 * 240);
    $saParagraf = 120;  // paragraftan sonra boşluk

    foreach ($bolumler as $i => $b) {
        $numaraYeni = array_merge($numara, [$i + 1]);
        $numaraStr = implode('.', $numaraYeni);
        $seviye = count($numaraYeni) - 1;

        $baslikMetin = $numaraStr . ' ' . baslikCase($b['baslik'], $seviye);

        if ($seviye === 0) {
            if (empty($numara) && $i > 0) {
                $rtf .= "\\page\n";  // Her ana bölüm yeni sayfada
            }
            $sb = cmToTwips((float)($ayarlar['ust_bolum'] ?? 5.0));  // Bölüm başında üst
        } else {
            $sb = 240;  // Alt başlıklar: normal boşluk
        }

        $rtf .= "{\\pard\\li" . $li . "\\fi0\\qj\\sb" . $sb . "\\sa" . $saParagraf . "\\sl" . $slTek . "\\fs" . $fs . "\\b " . rtfEscape($baslikMetin) . "\\par}\n";

        $icerik = $b['icerik'] ?? '';
        $bloklar = icerikParse($icerik);
        if (empty($bloklar)) {
            $paragraflar = preg_split('/\n\s*\n/', trim($icerik), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($paragraflar as $p) {
                $p = trim($p);
                if ($p === '') continue;
                $rtf .= "{\\pard\\li0\\fi" . $li . "\\qj\\sa" . $saParagraf . "\\sl" . $sl . "\\fs" . $fs . " " . rtfEscape($p) . "\\par}\n";
            }
        } else {
            bloklariRtfYaz($bloklar, $ayarlar, $rtf, $li, $sl, $saParagraf, $fs);
        }

        $cocuklar = $parents[(int)$b['id']] ?? [];
        if (!empty($cocuklar)) {
            bolumYaz($cocuklar, $parents, $ayarlar, $rtf, $numaraYeni);
        }
    }
}

bolumYaz($kokler, $parents, $ayarlar, $rtf);

$rtf .= "}\n";

$dosyaAdi = preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $proje['baslik']);
$dosyaAdi = preg_replace('/\s+/', '_', trim($dosyaAdi)) ?: 'tez';

header('Content-Type: application/rtf');
header('Content-Disposition: attachment; filename="' . $dosyaAdi . '.rtf"');
header('Cache-Control: max-age=0');
echo $rtf;
exit;
