# Drone Ground Control - DJI Agras Style

🚁 Web tabanlı, DJI Agras T100 stilinde profesyonel drone kontrol istasyonu. Minimalist, kompakt ve ekran-verimli tasarım.

## ✨ DJI Agras Stil Özellikleri

### 🎨 Minimalist & Kompakt Tasarım
- **DJI Design Language** - Temiz, profesyonel, minimal
- **Ultra-thin Bars** - İnce üst/alt barlar (42px/50px)
- **Floating Widgets** - Transparan mini paneller
- **Maximum Map Space** - %90+ harita görünümü
- **Icon-First UI** - Az metin, çok icon
- **Backdrop Blur** - Gerçek glassmorphism efekti

### 🗺️ Ekran Verimliliği
- **Full Screen Map** - Harita tam ekran dominant
- **Smart PIP** - Küçük kamera penceresi (200x112px)
- **Compact Instruments** - 100-140px genişlik paneller
- **Minimal Padding** - Sıkı yerleşim, sıfır boşluk kaybı
- **Overlay Telemetry** - Haritanın üzerinde veri gösterimi
- **Auto-hide Elements** - Gereksiz UI elemanları gizli

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

### 📱 Mobil & Desktop Optimize
- **Landscape Only** - Sadece yatay ekran (zorunlu)
- **Responsive Scaling** - 1280px - 1920px+ tam destek
- **Touch Gestures** - Swipe, pinch, tap optimized
- **Adaptive Layout** - Ekran boyutuna göre otomatik düzen
- **High DPI Support** - Retina display uyumlu
- **Performance Mode** - Canvas optimizasyonları

## 🚀 Hızlı Başlangıç

### Kurulum
1. `index.html` dosyasını modern bir tarayıcıda açın
2. **Cihazı landscape (yatay) moda çevirin** (zorunlu)
3. Sistem otomatik olarak başlar ve harita yüklenir

### Temel Kontroller

#### 🎮 Uçuş Kontrolleri
- **ARM/DISARM** - Sağ üst yeşil/kırmızı buton
- **Takeoff** - Sol panel → TAKEOFF → Slider'ı sağa kaydır
- **Land** - Sol panel → LAND butonu
- **RTH** - Sol panel → RTH (Return to Home)
- **Emergency Stop** - Sol panel → STOP butonu (kırmızı)

#### 🗺️ Görüntü Kontrolleri
- **View Switch** - Sağ alttaki mavi yuvarlak buton veya PIP penceresine tıkla
- **Map Zoom** - Mouse wheel, pinch gesture
- **Map Pan** - Sürükle (drag)

#### ⚙️ Uçuş Modları
- **STABILIZE** - Manuel stabilize uçuş
- **LOITER** - Otomatik konum tutma
- **AUTO** - Otonom görev modu
- **RTL** - Return to Launch (otomatik dönüş)

## 🛠️ Teknolojiler

### Core Stack
- **HTML5** - Minimal semantic markup
- **CSS3** - DJI-style glassmorphism, backdrop-filter
- **JavaScript ES6+** - OOP, Canvas rendering
- **Canvas 2D API** - Real-time instruments

### Libraries
- **Leaflet.js 1.9.4** - Interactive mapping
- **Font Awesome 6.4.0** - Professional icons
- **ArcGIS Satellite** - High-res imagery

### Design System (DJI Agras Inspired)
- **Colors**: #0084ff (primary), #00d56a (green), #ff3b30 (red)
- **Fonts**: SF Pro Display, SF Mono (fallback: system fonts)
- **Spacing**: 8px base grid system
- **Borders**: 1px solid rgba(255,255,255,0.15)
- **Shadows**: Soft, minimal drop shadows
- **Blur**: 20px backdrop blur for panels

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

## 🎮 Simülasyon Özellikleri

Uygulama tam özellikli simülasyon modunda çalışır:

### Fizik Simülasyonu
- ✈️ **Gerçekçi Uçuş Dinamikleri** - Smooth climb, cruise, turn
- 📍 **GPS Navigasyon** - Heading-based movement, path tracking
- 🔄 **Attitude Simulation** - Realistic roll/pitch oscillations
- 🌍 **Coordinate System** - Lat/lon decimal degrees
- 📏 **Distance Calculation** - Haversine formula

### Sistem Simülasyonu
- 🔋 **Battery Drain** - Realistic voltage/current draw
- 📡 **Sensor Data** - GPS sats, HDOP, signal strength
- ⏱️ **Flight Timer** - Accurate time tracking
- 🛤️ **Flight Path** - Visual trail on map
- 📊 **Telemetry Graphs** - Real-time altitude history

### Visual Effects
- 💫 **Animated Instruments** - 60 FPS canvas rendering
- 🎨 **Dynamic UI** - Reactive colors based on status
- ⚡ **Smooth Transitions** - Cubic-bezier easing
- 🌟 **Glow Effects** - Pulsing indicators

## 🔮 Roadmap (Gelecek Özellikler)

### Phase 1 - Real Hardware Integration
- [ ] **MAVLink Protocol** - WebSerial/WebSocket MAVLink parser
- [ ] **ArduPilot/PX4** - Flight controller connectivity
- [ ] **Real Telemetry** - Live sensor data streaming
- [ ] **Command & Control** - Two-way communication

### Phase 2 - Advanced Features
- [ ] **Video Streaming** - WebRTC H.264/H.265 video
- [ ] **Mission Planner** - Drag-drop waypoint creation
- [ ] **Geofencing** - No-fly zone enforcement
- [ ] **Autonomous Flight** - Survey, orbit, follow-me modes
- [ ] **Multi-vehicle** - Fleet management
- [ ] **Telemetry Logger** - Flight data recording & replay

### Phase 3 - Pro Features
- [ ] **Joystick Control** - Gamepad API integration
- [ ] **Voice Commands** - Speech recognition
- [ ] **AR Overlay** - Augmented reality HUD
- [ ] **AI Co-pilot** - Machine learning flight assistance
- [ ] **Cloud Sync** - Mission backup, sharing
- [ ] **Analytics** - Flight statistics, heatmaps

## 📸 DJI Agras Style Layout

### Main Interface
```
┌────────────────────────────────────────────────────┐
│ [LOGO] [STATUS]  [MODES]  [BATTERY] [ARM]      42px│
├─┬──────────────────────────────────────────────┬─┬─┤
│F│                                              │I│C│
│L│                                              │N│A│
│Y│           FULL SCREEN MAP                    │S│M│
│ │                                              │T│ │
│P│          (90% screen space)                  │R│P│
│L│                                              │U│I│
│A│                                              │M│P│
│N│                                              │E│ │
│ │                                              │N│ │
│T│                                              │T│2│
│A│                                              │S│0│
│K│                                              │ │0│
│E│                                              │ │x│
│ │                                              │1│1│
│ │                                              │4│1│
│ │                                              │0│2│
├─┴──────────────────────────────────────────────┴─┴─┤
│ [ALT] [SPEED] [V/S] [DIST] [HOME] [GPS] [STS] 50px│
└────────────────────────────────────────────────────┘
```

### Features
- **Top Bar**: Ultra-thin (42px), minimal controls
- **Left Panel**: 48px wide, floating widgets
- **Right Panel**: 140px instruments, auto-hide speed/alt on small screens
- **Bottom Bar**: 50px, essential telemetry only
- **Map**: Dominant, 90%+ screen coverage

## ⚡ Performance

- **60 FPS** - Smooth animations
- **Canvas Rendering** - Hardware accelerated
- **Optimized Leaflet** - Efficient tile loading
- **Low Latency** - <16ms frame time
- **Memory Efficient** - <100MB RAM usage

## 🔧 Geliştirme

### Dosya Yapısı
```
/workspace/
├── index.html      # Ana HTML yapısı
├── styles.css      # Futuristik CSS tasarımı
├── app.js          # DroneController class + simülasyon
└── README.md       # Bu dosya
```

### Özelleştirme
- **Renkler**: `styles.css` → `:root` CSS variables
- **Simülasyon**: `app.js` → `updateSimulation()` method
- **Layout**: `index.html` → Panel yapıları

## 🤝 Katkıda Bulunma

Bu proje açık kaynak geliştirme için hazır. Eklemek istediğiniz özellikler:
1. Fork edin
2. Feature branch oluşturun
3. Commit edin
4. Pull request gönderin

## 📄 Lisans

MIT License - Eğitim, geliştirme ve ticari kullanım için özgür.

## 🙏 Credits & Inspiration

- **QGroundControl** - Original inspiration
- **Font Awesome** - Icon library
- **Leaflet.js** - Map rendering
- **ArcGIS** - Satellite imagery tiles
- **Modern UI Design** - Glassmorphism, neumorphism trends

## 🎯 DJI Agras Inspired Features

### Space Efficiency
- **90%+ Map Coverage** - Maksimum harita alanı
- **Compact Instruments** - 100-140px dar paneller
- **Thin Bars** - 42px üst, 50px alt bar
- **Floating Mini Widgets** - Overlay paneller
- **Smart Auto-hide** - Gereksiz elemanlar gizli

### Professional UI
- **DJI Color Palette** - #0084ff (mavi), #00d56a (yeşil), #ff3b30 (kırmızı)
- **SF Pro Display Font** - Apple system font
- **Monospace Numbers** - SF Mono ile telemetri
- **Icon-First Design** - 48x48px butonlar, minimal metin
- **Smooth Transitions** - 200-400ms cubic-bezier

### Responsive & Adaptive
- **1600px+** - Full featured view
- **1280px-1600px** - Compact mode
- **<1280px** - Ultra minimal
- **Auto-adjust** - Ekran boyutuna göre dinamik

---

**Built with ❤️ for the drone community**
