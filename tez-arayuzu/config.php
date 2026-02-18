<?php
/**
 * Tez Yazım Arayüzü - Varsayılan Ayarlar (Ulusal Tez Yazım Kuralları)
 * YÖK Tez Yazım Kılavuzu esas alınmıştır
 */

return [
    'font' => [
        'family' => 'Times New Roman',
        'size' => 12,
        'size_title' => 16,
        'size_heading' => 14,
    ],
    'margins' => [
        'top' => 3,
        'bottom' => 3,
        'left' => 3,
        'right' => 3,
    ],
    'spacing' => [
        'line' => 1.5,      // Satır aralığı
        'paragraph' => 6,   // Paragraf öncesi/sonrası (pt)
    ],
    'page' => [
        'size' => 'A4',
        'orientation' => 'portrait',
    ],
    'numbering' => [
        'position' => 'bottom_center',
        'start_page' => 1,
    ],
];
