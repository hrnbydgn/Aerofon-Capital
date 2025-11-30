# 🚀 Kurulum Rehberi

Bu rehber, Proje Yönetim Sistemini farklı ortamlarda nasıl kuracağınızı adım adım açıklar.

## 📋 Gereksinimler

- PHP 8.1 veya üzeri
- MySQL 5.7+ veya MariaDB 10.2+
- Apache veya Nginx web sunucusu
- Composer (opsiyonel)

## 🖥️ Yerel Geliştirme Ortamı

### Windows (XAMPP)

1. **XAMPP İndirin ve Kurun**
   - [XAMPP](https://www.apachefriends.org/) sitesinden indirin
   - PHP 8.1+ versiyonunu seçin

2. **Projeyi Kopyalayın**
   ```bash
   cd C:\xampp\htdocs
   git clone [repo-url] project-management
   ```

3. **Veritabanını Oluşturun**
   - XAMPP Control Panel'den MySQL'i başlatın
   - http://localhost/phpmyadmin adresine gidin
   - "Yeni" veya "New" butonuna tıklayın
   - Veritabanı adı: `project_management`
   - "SQL" sekmesine gidin
   - `database.sql` dosyasının içeriğini yapıştırın ve çalıştırın

4. **Yapılandırma**
   - `includes/config.php` dosyasını açın
   - Veritabanı bilgilerini kontrol edin:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'project_management');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // XAMPP'te varsayılan olarak boş
   ```

5. **Çalıştırın**
   - http://localhost/project-management/public adresine gidin
   - Demo hesaplarla giriş yapın

### macOS (MAMP)

1. **MAMP İndirin ve Kurun**
   - [MAMP](https://www.mamp.info/) sitesinden indirin

2. **Projeyi Kopyalayın**
   ```bash
   cd /Applications/MAMP/htdocs
   git clone [repo-url] project-management
   ```

3. **Veritabanını Oluşturun**
   - MAMP'i başlatın
   - phpMyAdmin'e gidin (genellikle http://localhost:8888/phpMyAdmin)
   - Yukarıdaki Windows adımlarını takip edin

4. **Yapılandırma**
   - MAMP'teki port numarasını kontrol edin (genellikle 8888)
   - `includes/config.php`'yi düzenleyin

5. **Çalıştırın**
   - http://localhost:8888/project-management/public

### Linux (Ubuntu/Debian)

1. **LAMP Stack Kurun**
   ```bash
   sudo apt update
   sudo apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-mbstring php8.1-xml
   ```

2. **Apache Yapılandırması**
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

3. **Projeyi Kopyalayın**
   ```bash
   cd /var/www/html
   sudo git clone [repo-url] project-management
   sudo chown -R www-data:www-data project-management
   sudo chmod -R 755 project-management
   ```

4. **Veritabanını Oluşturun**
   ```bash
   sudo mysql -u root -p
   ```
   
   MySQL'de:
   ```sql
   CREATE DATABASE project_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'pmuser'@'localhost' IDENTIFIED BY 'güçlü_şifre';
   GRANT ALL PRIVILEGES ON project_management.* TO 'pmuser'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   
   mysql -u pmuser -p project_management < /var/www/html/project-management/database.sql
   ```

5. **Yapılandırma**
   ```bash
   sudo nano /var/www/html/project-management/includes/config.php
   ```
   
   Veritabanı bilgilerini güncelleyin

6. **Virtual Host Oluşturun (Opsiyonel)**
   ```bash
   sudo nano /etc/apache2/sites-available/project-management.conf
   ```
   
   İçerik:
   ```apache
   <VirtualHost *:80>
       ServerName project-management.local
       DocumentRoot /var/www/html/project-management/public
       
       <Directory /var/www/html/project-management/public>
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/pm_error.log
       CustomLog ${APACHE_LOG_DIR}/pm_access.log combined
   </VirtualHost>
   ```
   
   Aktif edin:
   ```bash
   sudo a2ensite project-management.conf
   sudo systemctl reload apache2
   ```
   
   `/etc/hosts` dosyasına ekleyin:
   ```
   127.0.0.1 project-management.local
   ```

7. **Çalıştırın**
   - http://project-management.local veya
   - http://localhost/project-management/public

## 🐳 Docker ile Kurulum

1. **docker-compose.yml Oluşturun**
   ```yaml
   version: '3.8'
   services:
     web:
       image: php:8.1-apache
       ports:
         - "8080:80"
       volumes:
         - ./:/var/www/html
       depends_on:
         - db
     
     db:
       image: mysql:8.0
       environment:
         MYSQL_ROOT_PASSWORD: rootpass
         MYSQL_DATABASE: project_management
         MYSQL_USER: pmuser
         MYSQL_PASSWORD: pmpass
       volumes:
         - db_data:/var/lib/mysql
         - ./database.sql:/docker-entrypoint-initdb.d/database.sql
   
   volumes:
     db_data:
   ```

2. **Çalıştırın**
   ```bash
   docker-compose up -d
   ```

3. **Tarayıcıda Açın**
   - http://localhost:8080/public

## 🌐 Production Sunucusu (cPanel)

1. **Dosyaları Yükleyin**
   - FileManager veya FTP ile dosyaları `public_html` klasörüne yükleyin

2. **Veritabanı Oluşturun**
   - cPanel'de MySQL Databases'e gidin
   - Yeni veritabanı oluşturun
   - Kullanıcı oluşturun ve veritabanına bağlayın
   - phpMyAdmin'den `database.sql`'i import edin

3. **Yapılandırma**
   - `includes/config.php`'yi düzenleyin:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cpanel_user_dbname');
   define('DB_USER', 'cpanel_user_dbuser');
   define('DB_PASS', 'db_password');
   ```

4. **.htaccess Kontrolü**
   - `.htaccess` dosyasının mevcut olduğundan emin olun
   - HTTPS yönlendirmesini aktif edin (production için)

5. **İzinler**
   - Klasörlere 755, dosyalara 644 izni verin

## 🔧 Yapılandırma Seçenekleri

### Temel Ayarlar

`includes/config.php`:

```php
// Hata Raporlama (Production'da kapatın)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Europe/Istanbul');

// Uygulama URL'i
define('APP_URL', 'http://localhost');
```

### Güvenlik Ayarları

Production ortamı için:

```php
// Hata gösterimi kapalı
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');

// Session güvenliği
ini_set('session.cookie_secure', 1); // HTTPS gerekli
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
```

`.htaccess`'te HTTPS yönlendirmesi:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## ✅ Kurulum Testi

1. **Giriş Testi**
   - Demo hesaplarla giriş yapın
   - Email: admin@example.com
   - Şifre: password

2. **Özellik Testi**
   - Yeni proje oluşturun
   - Görev ekleyin
   - Kanban board'u test edin

3. **API Testi**
   - Browser console'da hata var mı kontrol edin
   - Network sekmesinde API çağrılarını inceleyin

## 🐛 Sorun Giderme

### "Cannot connect to database" Hatası
- Veritabanı bilgilerini kontrol edin
- MySQL servisinin çalıştığından emin olun
- Veritabanı kullanıcısının izinlerini kontrol edin

### "Page not found" Hatası
- `.htaccess` dosyasının mevcut olduğundan emin olun
- Apache'de `mod_rewrite` modülünün aktif olduğunu kontrol edin
- Virtual host yapılandırmasını kontrol edin

### "Permission denied" Hatası
- Dosya izinlerini kontrol edin (755/644)
- Web sunucusu kullanıcısının (www-data, apache) dosyalara erişebildiğinden emin olun

### Session Sorunları
- `php.ini`'de session dizininin yazılabilir olduğunu kontrol edin
- Session timeout değerlerini kontrol edin

### XSS/CSRF Hataları
- Browser console'da detaylı hata mesajlarını kontrol edin
- CORS ayarlarını kontrol edin (farklı domain kullanıyorsanız)

## 📚 Ek Kaynaklar

- [PHP Resmi Dokümantasyonu](https://www.php.net/docs.php)
- [MySQL Dokümantasyonu](https://dev.mysql.com/doc/)
- [Tailwind CSS](https://tailwindcss.com/docs)

## 🆘 Destek

Sorun yaşıyorsanız:
1. Bu dokümantasyonu dikkatlice okuyun
2. Hata mesajlarını kontrol edin
3. GitHub Issues'da arama yapın
4. Yeni issue açın

---

Mutlu kodlamalar! 🎉