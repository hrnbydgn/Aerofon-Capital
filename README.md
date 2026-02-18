# Tez Yazım Arayüzü

HTML + PHP tabanlı tez yazma arayüzü. Bölüm bölüm içerik oluşturup YÖK/Ulusal yazım kurallarına uygun RTF çıktısı alabilirsiniz. **Bağımlılık yok** - AWebServer, XAMPP vb. ile çalışır.

## Kurulum

Kurulum gerekmez. PHP 7.0+ yeterli.

## Çalıştırma

AWebServer veya benzeri ile doğrudan çalışır. Veya:
```bash
php -S localhost:8000
```

## Özellikler

- **Bölüm + alt bölüm**: Sınırsız hiyerarşi (1., 1.1., 1.1.1 vb.)
- **YÖK formatı**: Times New Roman 12pt, 1.5 satır aralığı, 4cm sol kenar
- **RTF çıktısı**: Word ile açılır, tek tıkla indirme
- **Opsiyonel ayarlar**: Font, kenar boşlukları, satır aralığı vb.

## Dosya Yapısı

- `index.php` - Ana arayüz
- `api.php` - REST API
- `export.php` - Word dışa aktarma
- `config.php` - Yapılandırma
- `database.php` - SQLite veritabanı
