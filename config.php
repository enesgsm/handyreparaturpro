<?php
// config.php
$db_host = 'localhost';
$db_name = 'u174630048_enesgsm';    // Veritabanı adı
$db_user = 'u174630048_t';          // Kullanıcı adı
$db_pass = 'Y/oGd>xw7H^';          // Şifre

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}

// Oturum başlat
session_start();

// Güvenlik fonksiyonları
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Admin kontrolü
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Güvenli sayfa yönlendirmesi
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: login.php");
        exit();
    }
}

// Site ayarları
$site_settings = [
    'site_title' => 'Handy Reparatur Service',
    'whatsapp_number' => '491234567890', // WhatsApp numaranızı buraya ekleyin
    'company_address' => 'Berlin, Germany',
    'service_hours' => '09:00-18:00'
];
?>