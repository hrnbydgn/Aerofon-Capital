# QGroundControl Web Clone

Web tabanlı, mobil uyumlu, profesyonel drone kontrol istasyonu uygulaması. QGroundControl arayüzünden esinlenilmiştir.

## 🎯 Özellikler

### Görsel Özellikler
- ✨ **Swap View** - Harita ve kamera görüntüsü arasında akıcı geçiş
- 🎥 Picture-in-Picture (PIP) modu
- 🗺️ Uydu görüntülü canlı harita (ArcGIS)
- 📊 Profesyonel attitude indicator (yapay ufuk)
- 🧭 Dinamik pusula göstergesi
- 💫 Smooth animasyonlar ve geçişler

### Telemetri & Kontrol
- 📡 Gerçek zamanlı telemetri gösterimi
  - Altitude (İrtifa)
  - Ground Speed (Yer hızı)
  - Climb Rate (Tırmanma hızı)
  - GPS durumu ve uydu sayısı
  - Batarya voltajı ve yüzdesi
- ✈️ Uçuş modları (Stabilize, Loiter, Auto, RTL)
- 🔋 Batarya izleme ve uyarıları
- 🎮 ARM/DISARM kontrolü
- 🛫 Slide-to-Takeoff özelliği

### Mobil Optimizasyon
- 📱 Sadece yatay (landscape) ekran desteği
- 🔄 Otomatik oryantasyon uyarısı
- 👆 Touch-friendly kontroller
- 📏 Responsive tasarım

## 🚀 Kullanım

1. `index.html` dosyasını bir web tarayıcısında açın
2. **Cihazınızı yatay konuma çevirin** (zorunlu)
3. Uygulama otomatik olarak başlar

### Temel Kontroller

- **Harita/Kamera Değiştirme**: Küçük pencereye (PIP) tıklayın
- **Takeoff**: Sol toolbar'daki TAKEOFF butonuna basın, slider'ı kaydırın
- **ARM/DISARM**: Sağ üst köşedeki ARM butonuna tıklayın
- **Harita Zoom**: Pinch veya mouse wheel ile zoom yapın

## 🛠️ Teknolojiler

- **HTML5** - Yapı
- **CSS3** - Modern tasarım, animasyonlar, glassmorphism
- **JavaScript (ES6+)** - OOP, Canvas API
- **Leaflet.js** - İnteraktif harita
- **Font Awesome** - İkonlar
- **ArcGIS Satellite Imagery** - Uydu görüntüleri

## 📱 Tarayıcı Desteği

- ✅ Chrome/Chromium (önerilen)
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ✅ Mobil tarayıcılar (iOS Safari, Chrome Mobile)

**Not**: Mobil cihazlarda sadece yatay (landscape) modda çalışır.

## 🎨 Tasarım Özellikleri

- Modern glassmorphism efektleri
- Backdrop blur
- Smooth CSS transitions
- Gradient backgrounds
- Pulse animasyonlar
- Professional color palette
- QGroundControl-inspired layout

## 📊 Simülasyon Özellikleri

Uygulama şu anda simülasyon modunda çalışır:

- ✈️ Otomatik uçuş simülasyonu
- 📍 GPS konumu güncelleme
- 🔄 Roll, pitch, yaw animasyonları
- 🔋 Batarya tüketimi
- 📡 Sinyal gücü değişimleri

## 🔮 Gelecek Özellikler

Gerçek drone bağlantısı için:
- MAVLink protokol entegrasyonu
- WebSocket/WebRTC bağlantısı
- Video stream desteği
- Mission planner
- Waypoint sistemi
- Telemetry logging
- Joystick/gamepad desteği

## 📄 Lisans

MIT License - Eğitim ve geliştirme amaçlı kullanım için.

## 🙏 Credits

- QGroundControl projesinden esinlenilmiştir
- Font Awesome icons
- Leaflet.js mapping library
- ArcGIS satellite imagery
