# Evly 2.0 — Akıllı Ev Yönetimi

Evdeki alışveriş ve tüketim süreçlerini AI ile yöneten, fiş tarama (OCR), stok takibi, fiyat karşılaştırma ve haftalık menü planlama özellikli ev ERP uygulaması.

## Öne Çıkan Özellikler

### 🤖 AI Destekli Tahminler
- Ürünlerin ne zaman biteceğini otomatik tahmin
- Tüketim alışkanlıklarına göre akıllı alışveriş listesi
- Fiyat geçmişine göre en uygun market önerisi
- Israf skoru ve tasarruf önerileri

### 🧾 Fiş Tarama & Ürün Tespiti
- Kamera ile fiş tarama (OCR simülasyonu)
- **Her fişten tespit edilen ürünler tek tek gösterilir**
- Ürünler otomatik kategorize edilir
- Fiyat geçmişi ve stok durumu otomatik güncellenir

### 🍽️ AI Haftalık Menü Planı
- Mevcut stoklara göre yemek önerileri
- Stokta olan/olmayan malzeme etiketleri (yeşil/kırmızı)
- Kahvaltı, öğle, akşam yemeği planı

### 🛒 Akıllı Alışveriş Listesi
- AI önerileri öncelik sırasıyla (acil/yüksek/normal)
- Tahmini fiyat ve toplam hesaplama
- En uygun market önerisi
- Listeyi paylaşma (native share / clipboard)

### 📊 Analiz & Fiyat Karşılaştırma
- Aylık harcama grafikleri
- Kategori bazlı dağılım
- Aynı ürünün farklı marketlerdeki fiyatları
- Tüketim hızı sıralaması
- AI öngörüleri ve tasarruf ipuçları

## Teknoloji

| Katman | Teknoloji |
|--------|-----------|
| Backend | PHP 8+ (Native) |
| Veritabanı | SQLite |
| Frontend | Vanilla CSS3 + JS |
| Tema | Light (Ferah yeşil-mavi tonları) |
| Mimari | MVC benzeri + REST API |
| PWA | Manifest + Service Worker |

## Kurulum

```bash
php -S localhost:8000
# Veritabanı ilk çalışmada otomatik oluşur
```

## Proje Yapısı

```
├── index.php                  # Ana router
├── config.php                 # Konfigürasyon, yardımcı fonksiyonlar
├── database.php               # DB kurulumu ve seed verileri
├── includes/
│   ├── header.php             # Üst header
│   └── footer.php             # Alt navigasyon
├── pages/
│   ├── home.php               # Dashboard (AI asistan, stok durumu, menü)
│   ├── scan.php               # Fiş tarama
│   ├── receipt-detail.php     # Fiş detay (ürün listesi)
│   ├── inventory.php          # Envanter yönetimi
│   ├── shopping.php           # Akıllı alışveriş listesi
│   ├── analytics.php          # Analiz ve fiyat karşılaştırma
│   ├── meal-plan.php          # AI haftalık menü
│   └── profile.php            # Profil ve ayarlar
├── api/
│   ├── products.php           # Ürün CRUD
│   ├── receipts.php           # Fiş CRUD
│   └── shopping.php           # Alışveriş listesi
├── assets/
│   ├── css/style.css          # Ana stil (Light tema)
│   └── js/app.js              # Frontend JS
└── data/
    └── evly.db                # SQLite (otomatik oluşur)
```
