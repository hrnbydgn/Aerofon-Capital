# Proje Yönetim Sistemi

Modern ve profesyonel bir proje yönetimi web uygulaması. PHP backend ve modern frontend teknolojileriyle geliştirilmiştir.

## 🚀 Özellikler

### Kullanıcı Yönetimi
- ✅ Kayıt olma ve giriş yapma
- ✅ Güvenli oturum yönetimi
- ✅ Şifre hashleme (bcrypt)
- ✅ Kullanıcı profili

### Proje Yönetimi
- ✅ Proje oluşturma, düzenleme, silme
- ✅ Proje durumu takibi (Aktif, Tamamlandı, Askıda)
- ✅ Proje renk/etiket sistemi
- ✅ Tarih yönetimi
- ✅ İlerleme takibi

### Takım Yönetimi
- ✅ Projelere üye ekleme/çıkarma
- ✅ Rol yönetimi (Yönetici, Üye, Görüntüleyici)
- ✅ Takım listesi görüntüleme

### Görev Yönetimi
- ✅ Görev oluşturma, düzenleme, silme
- ✅ Görev atama
- ✅ Durum yönetimi (Yapılacak, Devam Ediyor, Tamamlandı)
- ✅ Öncelik belirleme (Düşük, Orta, Yüksek)
- ✅ Bitiş tarihi takibi
- ✅ Görev açıklaması

### Kanban Board
- ✅ Sürükle-bırak özelliği
- ✅ 3 sütun (Yapılacak, Devam Ediyor, Tamamlandı)
- ✅ Görsel kart tasarımı
- ✅ Anlık durum güncelleme

### Dashboard
- ✅ Tüm projelere genel bakış
- ✅ Atanan görevler
- ✅ Yaklaşan deadline'lar
- ✅ İstatistikler

### Görev Detay
- ✅ Yorum sistemi
- ✅ Görev geçmişi
- ✅ Detaylı bilgiler

## 🛠️ Teknoloji Stack

### Backend
- PHP 8.1+
- MySQL/MariaDB
- PDO (Prepared Statements)
- OOP Architecture

### Frontend
- HTML5
- CSS3
- Tailwind CSS
- Vanilla JavaScript
- AJAX

### Güvenlik
- ✅ SQL Injection koruması (Prepared Statements)
- ✅ XSS koruması (htmlspecialchars)
- ✅ Password hashing (password_hash)
- ✅ Session güvenliği
- ✅ CSRF koruması hazır

## 📁 Proje Yapısı

```
/workspace
├── public/              # Web root
│   ├── css/            # Stil dosyaları
│   ├── js/             # JavaScript dosyaları
│   ├── images/         # Resim dosyaları
│   ├── index.php       # Ana giriş
│   ├── login.php       # Giriş sayfası
│   ├── register.php    # Kayıt sayfası
│   ├── dashboard.php   # Dashboard
│   ├── projects.php    # Projeler listesi
│   ├── project.php     # Proje detay
│   └── task.php        # Görev detay
├── includes/           # PHP include dosyaları
│   ├── config.php      # Yapılandırma
│   ├── db.php          # Veritabanı bağlantısı
│   └── functions.php   # Yardımcı fonksiyonlar
├── api/                # RESTful API endpoint'leri
│   ├── auth.php        # Kimlik doğrulama
│   ├── projects.php    # Proje işlemleri
│   ├── tasks.php       # Görev işlemleri
│   └── comments.php    # Yorum işlemleri
├── views/              # HTML template'leri
│   ├── header.php      # Header
│   ├── navbar.php      # Navigasyon
│   └── footer.php      # Footer
├── database.sql        # Veritabanı şeması
├── .htaccess          # Apache yapılandırması
├── README.md          # Bu dosya
└── KURULUM.md         # Detaylı kurulum rehberi
```

## 🚀 Hızlı Başlangıç

### 1. Gereksinimler
- PHP 8.1 veya üzeri
- MySQL 5.7+ / MariaDB 10.2+
- Apache/Nginx (mod_rewrite etkin)

### 2. Kurulum

```bash
# Veritabanını oluşturun
mysql -u root -p < database.sql

# Yapılandırmayı düzenleyin
nano includes/config.php

# Web sunucusunu başlatın
php -S localhost:8000 -t public/
```

Detaylı kurulum için [KURULUM.md](KURULUM.md) dosyasına bakın.

### 3. Demo Hesaplar

**Admin:**
- E-posta: `admin@example.com`
- Şifre: `password`

**Demo Kullanıcı:**
- E-posta: `demo@example.com`
- Şifre: `password`

## 📖 Kullanım

1. Tarayıcınızda `http://localhost:8000` adresine gidin
2. Demo hesaplardan biriyle giriş yapın
3. Dashboard'dan "Yeni Proje" oluşturun
4. Projeye üye ekleyin
5. Görevler oluşturun ve yönetin
6. Kanban board'da sürükle-bırak yapın

## 🔒 Güvenlik

### Uygulanmış Güvenlik Özellikleri
- ✅ Prepared Statements (SQL Injection koruması)
- ✅ XSS koruması (htmlspecialchars)
- ✅ Password hashing (bcrypt)
- ✅ Secure session management
- ✅ Input validation
- ✅ HTTP-only cookies
- ✅ Security headers

### Production Önerileri
1. HTTPS kullanın
2. `.htaccess`'te HTTPS yönlendirmesini aktif edin
3. `config.php`'de hata raporlamayı kapatın
4. Güçlü veritabanı şifreleri kullanın
5. Düzenli yedekleme yapın

## 🎨 Ekran Görüntüleri

### Dashboard
Modern ve kullanıcı dostu dashboard ile tüm projelere genel bakış

### Kanban Board
Sürükle-bırak özellikli Kanban board ile görev yönetimi

### Proje Detayları
Proje bilgileri, görevler ve takım yönetimi tek ekranda

## 🔧 API Endpoints

### Authentication
- `POST /api/auth.php?action=register` - Kayıt ol
- `POST /api/auth.php?action=login` - Giriş yap
- `POST /api/auth.php?action=logout` - Çıkış yap

### Projects
- `GET /api/projects.php?action=list` - Projeleri listele
- `GET /api/projects.php?action=get&id={id}` - Proje detayı
- `POST /api/projects.php?action=create` - Proje oluştur
- `POST /api/projects.php?action=update` - Proje güncelle
- `POST /api/projects.php?action=delete` - Proje sil

### Tasks
- `GET /api/tasks.php?action=list&project_id={id}` - Görevleri listele
- `GET /api/tasks.php?action=get&id={id}` - Görev detayı
- `POST /api/tasks.php?action=create` - Görev oluştur
- `POST /api/tasks.php?action=update` - Görev güncelle
- `POST /api/tasks.php?action=delete` - Görev sil

## 📊 Veritabanı Şeması

- **users**: Kullanıcı bilgileri
- **projects**: Proje bilgileri
- **project_members**: Proje-kullanıcı ilişkileri
- **tasks**: Görev bilgileri
- **comments**: Görev yorumları
- **task_history**: Görev değişiklik geçmişi

## 🐛 Sorun Giderme

Yaygın sorunlar ve çözümleri için [KURULUM.md](KURULUM.md) dosyasındaki "Sorun Giderme" bölümüne bakın.

## 📝 Geliştirme Notları

### Tamamlanan Özellikler
- [x] Kullanıcı kayıt/giriş sistemi
- [x] Proje yönetimi
- [x] Takım yönetimi
- [x] Görev yönetimi
- [x] Kanban board
- [x] Yorum sistemi
- [x] Görev geçmişi
- [x] Dashboard ve istatistikler

### Gelecek Özellikler
- [ ] Dosya ekleme
- [ ] E-posta bildirimleri
- [ ] Profil düzenleme
- [ ] Avatar yükleme
- [ ] Proje şablonları
- [ ] Zaman takibi
- [ ] Raporlama ve dışa aktarma

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit edin (`git commit -m 'Add amazing feature'`)
4. Push edin (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 👨‍💻 Geliştirici

Claude AI (Sonnet 4.5) tarafından geliştirilmiştir.

## 📞 Destek

- 📖 Dokümantasyon: [README.md](README.md) ve [KURULUM.md](KURULUM.md)
- 🐛 Bug raporu: GitHub Issues
- 💬 Sorularınız için: Issue açabilirsiniz

---

⭐ **Projeyi beğendiyseniz yıldız vermeyi unutmayın!**

**Mutlu kodlamalar!** 🎉
