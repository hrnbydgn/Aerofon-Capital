# Tez Yazım Arayüzü

HTML + PHP tabanlı tez yazma arayüzü. Bölüm bölüm içerik oluşturup YÖK/Ulusal yazım kurallarına uygun Word çıktısı alabilirsiniz.

## Kurulum

```bash
composer install
```

## Çalıştırma

PHP yerleşik sunucusu:
```bash
php -S localhost:8000
```

Tarayıcıda: http://localhost:8000

## Özellikler

- **Bölüm bölüm içerik**: Her bölümü ayrı ayrı yazın
- **YÖK formatı**: Times New Roman 12pt, 1.5 satır aralığı, 4cm sol kenar
- **Word çıktısı**: Tek tıkla .docx indirme
- **Opsiyonel ayarlar**: Font, kenar boşlukları, satır aralığı vb.

## Dosya Yapısı

- `index.php` - Ana arayüz
- `api.php` - REST API
- `export.php` - Word dışa aktarma
- `config.php` - Yapılandırma
- `database.php` - SQLite veritabanı
