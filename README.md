# Evly - Akıllı Ev Yönetimi

Evdeki alışveriş ve tüketim süreçlerini düzenleyerek unutulan, eksik veya gereksiz alışverişleri azaltmayı amaçlayan yapay zeka destekli ev ERP uygulaması.

## Özellikler

- **Fiş Tarama (OCR):** Alışveriş fişlerini kamera ile tarayıp otomatik ürün tanıma
- **AI Tüketim Tahmini:** Fiş tarihlerini analiz ederek ürünlerin ne zaman biteceğini otomatik tahmin
- **Akıllı Alışveriş Listesi:** AI destekli ürün önerileri ve tüketim bazlı otomatik liste oluşturma
- **Envanter Yönetimi:** Evdeki tüm ürünlerin stok durumu takibi
- **Harcama Analizi:** Kategori bazlı harcama grafikleri ve tüketim trendleri
- **PWA Desteği:** Mobil cihazlarda uygulama gibi çalışır

## Teknoloji

- Vanilla HTML5, CSS3, JavaScript (framework bağımsız)
- PWA (Progressive Web App) - offline çalışma desteği
- Mobil uyumlu (Mobile-first responsive tasarım)
- Kart tabanlı, karanlık tema UI

## Kurulum

Herhangi bir HTTP sunucusu ile çalıştırabilirsiniz:

```bash
# Python ile
python -m http.server 8000

# Node.js ile
npx serve .
```

Ardından tarayıcınızda `http://localhost:8000` adresini açın.

## Yapı

```
├── index.html          # Ana HTML dosyası (tüm sayfalar SPA olarak)
├── css/
│   └── style.css       # Ana stil dosyası
├── js/
│   └── app.js          # Ana JavaScript dosyası
├── manifest.json       # PWA manifest
├── sw.js               # Service Worker
└── icons/              # Uygulama ikonları
```
