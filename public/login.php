<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isLoggedIn()) {
    header('Location: /public/dashboard.php');
    exit;
}

$pageTitle = 'Giriş Yap';
?>
<?php include '../views/header.php'; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-blue-500 mb-2">
                <i class="fas fa-project-diagram"></i>
            </h1>
            <h2 class="text-3xl font-bold">Proje Yönetim Sistemi</h2>
            <p class="mt-2 text-gray-400">Hesabınıza giriş yapın</p>
        </div>
        
        <div class="bg-gray-800 rounded-lg shadow-xl p-8 border border-gray-700">
            <form id="loginForm" class="space-y-6">
                <div id="errorMessage" class="hidden bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded"></div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                        E-posta Adresi
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400"
                           placeholder="ornek@email.com">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        Şifre
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400"
                           placeholder="••••••••">
                </div>
                
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Giriş Yap
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-400">
                    Hesabınız yok mu? 
                    <a href="/public/register.php" class="text-blue-500 hover:text-blue-400 font-medium">
                        Kayıt Ol
                    </a>
                </p>
            </div>
        </div>
        
        <div class="bg-gray-800 rounded-lg shadow-xl p-6 border border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300 mb-3">Demo Hesaplar:</h3>
            <div class="space-y-2 text-sm text-gray-400">
                <p><strong>Admin:</strong> admin@example.com / password</p>
                <p><strong>Demo:</strong> demo@example.com / password</p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.classList.add('hidden');
    
    const formData = {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
    };
    
    try {
        const response = await fetch('/api/auth.php?action=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = '/public/dashboard.php';
        } else {
            errorDiv.textContent = data.message;
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'Bir hata oluştu. Lütfen tekrar deneyin.';
        errorDiv.classList.remove('hidden');
    }
});
</script>

<?php include '../views/footer.php'; ?>
