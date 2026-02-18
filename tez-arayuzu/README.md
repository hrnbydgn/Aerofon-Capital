# Tez Yazım Arayüzü

HTML/PHP tabanlı tez yazım arayüzü. Bölüm bölüm içerik oluşturup Word formatında export edebilirsiniz.

## Özellikler
- Bölüm bazlı içerik editörü (On Sayfa, Özet, Giriş, Bölümler, Kaynakça vb.)
- Otomatik kaydetme
- Ulusal tez yazım kurallarına uygun varsayılan ayarlar (YÖK)
- Ayarlar: Yazı tipi, boyut, satır aralığı, kenar boşlukları, sayfa numarası

## Kurulum
PHP 7.4+ gereklidir. `data` klasörü yazılabilir olmalı.

```bash
cd tez-arayuzu
php -S localhost:8080
```

Tarayıcıda http://localhost:8080 adresine gidin.

## Dosya Yapısı
- `index.php` - Ana arayüz
- `save.php` - İçerik kaydetme
- `save_config.php` - Ayar kaydetme
- `export.php` - Word (.doc) export
