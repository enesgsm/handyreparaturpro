<?php
session_start();
require_once 'config.php';

// Cookie kontrolü
if (isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
    if ($_COOKIE['remember_user'] === 'admin' && $_COOKIE['remember_token'] === hash('sha256', 'admin123')) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        
        // Beni hatırla seçeneği işaretlenmişse cookie oluştur
        if ($remember) {
            setcookie('remember_user', $username, time() + (86400 * 30), "/"); // 30 gün
            setcookie('remember_token', hash('sha256', $password), time() + (86400 * 30), "/");
        }

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Hatalı kullanıcı adı veya şifre!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold text-center mb-6">Admin Girişi</h1>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kullanıcı Adı</label>
                    <input type="text" name="username" required value="<?php echo isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : ''; ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Şifre</label>
                    <input type="password" name="password" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-blue-600" 
                           <?php echo isset($_COOKIE['remember_user']) ? 'checked' : ''; ?>>
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Beni Hatırla
                    </label>
                </div>

                <button type="submit" 
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                    Giriş Yap
                </button>
            </form>
        </div>
    </div>
</body>
</html>