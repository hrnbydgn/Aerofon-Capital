<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Kullanıcı bilgileri
$username = $_SESSION['username'] ?? 'Kullanıcı';
$user_name = $_SESSION['user_name'] ?? 'Kullanıcı';
$user_email = $_SESSION['user_email'] ?? '';
$login_time = $_SESSION['login_time'] ?? time();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hoş Geldiniz</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .welcome-text {
            color: #fff;
        }

        .welcome-text h1 {
            font-size: 32px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .welcome-text p {
            font-size: 16px;
            opacity: 0.9;
        }

        .user-info {
            text-align: right;
            color: #fff;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .logout-btn {
            background: rgba(255, 82, 82, 0.8);
            color: #fff;
            padding: 10px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }

        .logout-btn:hover {
            background: rgba(255, 82, 82, 1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.5);
        }

        .card-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .card h3 {
            color: #fff;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .card p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            line-height: 1.6;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: #fff;
        }

        .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .user-info {
                text-align: center;
                margin-top: 20px;
            }

            .user-avatar {
                margin: 0 auto 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="welcome-text">
                <h1>🎉 Hoş Geldiniz, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p>Giriş Zamanı: <?php echo date('d.m.Y H:i', $login_time); ?></p>
            </div>
            <div class="user-info">
                <div class="user-avatar">👤</div>
                <div><?php echo htmlspecialchars($user_email); ?></div>
                <a href="logout.php" class="logout-btn">🚪 Çıkış Yap</a>
            </div>
        </div>

        <div class="content-grid">
            <div class="card">
                <div class="card-icon">📊</div>
                <h3>İstatistikler</h3>
                <p>Hesap aktivitelerinizi ve istatistiklerinizi burada görüntüleyebilirsiniz.</p>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value">24</div>
                        <div class="stat-label">Toplam</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">12</div>
                        <div class="stat-label">Aktif</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">7</div>
                        <div class="stat-label">Bekleyen</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">📝</div>
                <h3>Projeler</h3>
                <p>Aktif projelerinizi yönetin ve yeni projeler oluşturun.</p>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value">5</div>
                        <div class="stat-label">Aktif</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">15</div>
                        <div class="stat-label">Tamamlanan</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">3</div>
                        <div class="stat-label">Taslak</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">⚙️</div>
                <h3>Ayarlar</h3>
                <p>Hesap ayarlarınızı düzenleyin ve güvenlik tercihlerinizi yapılandırın.</p>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value">✓</div>
                        <div class="stat-label">Profil</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">✓</div>
                        <div class="stat-label">Güvenlik</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">✓</div>
                        <div class="stat-label">Bildirim</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">💬</div>
                <h3>Mesajlar</h3>
                <p>Gelen kutunuzu kontrol edin ve yeni mesajlar gönderin.</p>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value">8</div>
                        <div class="stat-label">Yeni</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">42</div>
                        <div class="stat-label">Okundu</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">5</div>
                        <div class="stat-label">Taslak</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">🔔</div>
                <h3>Bildirimler</h3>
                <p>Son bildirimlerinizi ve güncellemeleri görüntüleyin.</p>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value">3</div>
                        <div class="stat-label">Yeni</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">25</div>
                        <div class="stat-label">Bu Hafta</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">100+</div>
                        <div class="stat-label">Toplam</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">📈</div>
                <h3>Performans</h3>
                <p>Performans metriklerinizi takip edin ve raporlar oluşturun.</p>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value">92%</div>
                        <div class="stat-label">Başarı</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">A+</div>
                        <div class="stat-label">Skor</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">↑15%</div>
                        <div class="stat-label">Artış</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
