# Evly - Akıllı Ev Yönetimi (PHP)

Evdeki alışveriş ve tüketim süreçlerini düzenleyerek unutulan, eksik veya gereksiz alışverişleri azaltmayı amaçlayan yapay zeka destekli ev ERP uygulaması.

## Özellikler

- **Fiş Tarama (OCR):** Alışveriş fişlerini kamera ile tarayıp otomatik ürün tanıma
- **AI Tüketim Tahmini:** Fiş tarihlerini analiz ederek ürünlerin ne zaman biteceğini tahmin
- **Akıllı Alışveriş Listesi:** AI destekli ürün önerileri ve tüketim bazlı liste oluşturma
- **Envanter Yönetimi:** Evdeki tüm ürünlerin stok durumu takibi
- **Harcama Analizi:** Kategori bazlı grafikler ve tüketim trendleri
- **REST API:** Tüm veriler JSON API üzerinden erişilebilir
- **PWA Desteği:** Mobil cihazlarda uygulama gibi çalışır

## Teknoloji

- **Backend:** PHP 8+ (Native, framework bağımsız)
- **Veritabanı:** SQLite (kurulum gerektirmez)
- **Frontend:** Vanilla CSS3 + JavaScript
- **Mimari:** MVC benzeri sayfa yapısı + REST API
- **Tasarım:** Mobil-first, kart tabanlı, karanlık tema

## Kurulum

```bash
# PHP built-in sunucu
php -S localhost:8000

# Veritabanı ilk çalışmada otomatik oluşur ve demo veriler yüklenir.
```

Tarayıcıda `http://localhost:8000` adresini açın.

## Proje Yapısı

```
├── index.php              # Ana router
├── config.php             # Konfigürasyon, DB bağlantısı, yardımcı fonksiyonlar
├── database.php           # Veritabanı kurulumu ve seed
├── .htaccess              # Apache rewrite kuralları
├── manifest.json          # PWA manifest
├── sw.js                  # Service Worker
│
├── includes/
│   ├── header.php         # Üst header bileşeni
│   └── footer.php         # Alt navigasyon bileşeni
│
├── pages/
│   ├── home.php           # Dashboard (ana sayfa)
│   ├── scan.php           # Fiş tarama sayfası
│   ├── inventory.php      # Envanter yönetimi
│   ├── shopping.php       # Alışveriş listesi
│   ├── analytics.php      # Analiz & istatistik
│   └── profile.php        # Profil & ayarlar
│
├── api/
│   ├── products.php       # Ürün CRUD API
│   ├── receipts.php       # Fiş CRUD API
│   └── shopping.php       # Alışveriş listesi API
│
├── assets/
│   ├── css/style.css      # Ana stil dosyası
│   └── js/app.js          # Frontend JavaScript
│
└── data/
    └── evly.db            # SQLite veritabanı (otomatik oluşur)
```

## API Endpointleri

| Endpoint | GET | POST | PUT | DELETE |
|---|---|---|---|---|
| `api/products.php` | Ürün listele | Ürün ekle | Ürün güncelle | Ürün sil |
| `api/receipts.php` | Fiş listele | Fiş ekle | — | Fiş sil |
| `api/shopping.php` | Liste getir | Ürün ekle | Toggle/Güncelle | Ürün sil |
