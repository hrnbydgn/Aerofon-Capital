<?php
session_start();

// Initialize variables
$error_message = '';
$success_message = '';

// Simple authentication (in production, use database and proper hashing)
$valid_users = [
    'admin@financeapp.com' => password_hash('admin123', PASSWORD_DEFAULT),
    'user@financeapp.com' => password_hash('user123', PASSWORD_DEFAULT),
    'demo@financeapp.com' => password_hash('demo123', PASSWORD_DEFAULT)
];

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error_message = 'Lütfen tüm alanları doldurun.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Geçerli bir e-posta adresi girin.';
    } elseif (isset($valid_users[$email]) && password_verify($password, $valid_users[$email])) {
        $_SESSION['user_email'] = $email;
        $_SESSION['logged_in'] = true;
        $success_message = 'Giriş başarılı! Yönlendiriliyorsunuz...';
        // In real application, redirect to dashboard
        // header('Location: dashboard.php');
        // exit();
    } else {
        $error_message = 'E-posta veya şifre hatalı.';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>deneme login</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-bg: #0f1419;
            --card-bg: rgba(255, 255, 255, 0.95);
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --border-color: rgba(255, 255, 255, 0.2);
            --shadow-primary: 0 20px 40px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            z-index: -2;
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.8;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            width: 40px;
            height: 40px;
            top: 30%;
            left: 70%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: var(--shadow-primary);
            border: 1px solid var(--border-color);
            width: 100%;
            max-width: 450px;
            position: relative;
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .brand-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: var(--success-gradient);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .brand-logo i {
            font-size: 2rem;
            color: white;
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 400;
        }

        .form-floating {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-floating input {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            padding: 1.2rem 1rem 0.8rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-floating input:focus {
            background: rgba(255, 255, 255, 0.95);
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-floating label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-right: none;
            border-radius: 16px 0 0 16px;
            backdrop-filter: blur(10px);
        }

        .btn-login {
            background: var(--primary-gradient);
            border: none;
            border-radius: 16px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .alert {
            border-radius: 12px;
            border: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .alert-success {
            background: var(--success-gradient);
            color: white;
        }

        .forgot-password {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .forgot-password a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #667eea;
        }

        .divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(113, 128, 150, 0.3);
        }

        .divider span {
            background: var(--card-bg);
            padding: 0 1rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .demo-accounts {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .demo-accounts h6 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .demo-accounts h6 i {
            margin-right: 0.5rem;
            color: #667eea;
        }

        .demo-account {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 0.8rem 1rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .demo-account:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateX(5px);
        }

        .demo-account:last-child {
            margin-bottom: 0;
        }

        .demo-account .email {
            color: var(--text-primary);
            font-weight: 500;
        }

        .demo-account .password {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 2rem 1.5rem;
                margin: 10px;
            }
            
            .brand-title {
                font-size: 1.5rem;
            }
        }

        .loading-spinner {
            display: none;
        }

        .btn-login.loading .loading-spinner {
            display: inline-block;
            margin-right: 0.5rem;
        }

        .btn-login.loading .btn-text {
            display: none;
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="brand-section">
                <div class="brand-logo">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h1 class="brand-title">FinanceHub</h1>
                <p class="brand-subtitle">Gelişmiş Finans Yönetim Platformu</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="E-posta adresiniz" required>
                    <label for="email"><i class="fas fa-envelope me-2"></i>E-posta Adresi</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Şifreniz" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Şifre</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">
                        Beni hatırla
                    </label>
                </div>

                <button type="submit" class="btn btn-login" id="loginBtn">
                    <div class="loading-spinner spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                    <span class="btn-text">
                        <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                    </span>
                </button>
            </form>

            <div class="forgot-password">
                <a href="#" onclick="showForgotPassword()">
                    <i class="fas fa-key me-1"></i>Şifremi Unuttum
                </a>
            </div>

            <div class="divider">
                <span>Demo Hesaplar</span>
            </div>

            <div class="demo-accounts">
                <h6><i class="fas fa-users"></i>Test Hesapları</h6>
                <div class="demo-account" onclick="fillCredentials('admin@financeapp.com', 'admin123')">
                    <span class="email">admin@financeapp.com</span>
                    <span class="password">admin123</span>
                </div>
                <div class="demo-account" onclick="fillCredentials('user@financeapp.com', 'user123')">
                    <span class="email">user@financeapp.com</span>
                    <span class="password">user123</span>
                </div>
                <div class="demo-account" onclick="fillCredentials('demo@financeapp.com', 'demo123')">
                    <span class="email">demo@financeapp.com</span>
                    <span class="password">demo123</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Fill credentials function for demo accounts
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            
            // Add visual feedback
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            emailInput.style.transform = 'scale(1.02)';
            passwordInput.style.transform = 'scale(1.02)';
            
            setTimeout(() => {
                emailInput.style.transform = 'scale(1)';
                passwordInput.style.transform = 'scale(1)';
            }, 200);
        }

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
            
            // Simulate loading delay for better UX
            setTimeout(() => {
                // The form will submit naturally
            }, 500);
        });

        // Forgot password function
        function showForgotPassword() {
            alert('Şifre sıfırlama özelliği yakında eklenecek!\n\nDemo hesapları kullanarak giriş yapabilirsiniz.');
        }

        // Add floating animation to shapes
        document.addEventListener('DOMContentLoaded', function() {
            const shapes = document.querySelectorAll('.shape');
            shapes.forEach((shape, index) => {
                shape.style.animationDuration = (4 + index * 0.5) + 's';
            });
        });

        // Add subtle parallax effect on mouse move
        document.addEventListener('mousemove', function(e) {
            const shapes = document.querySelectorAll('.shape');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.5;
                const xPos = (x - 0.5) * speed * 20;
                const yPos = (y - 0.5) * speed * 20;
                
                shape.style.transform = `translate(${xPos}px, ${yPos}px)`;
            });
        });

        // Add form validation animations
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>