<?php
session_start();
require_once 'config.php';

// Benzersiz takip kodu oluşturma fonksiyonu
function generateTrackingCode() {
    return 'RP' . date('Ymd') . rand(1000, 9999);
}

// Sipariş durumlarımız
$orderStatuses = [
    'Beklemede' => 'Beklemede',
    'Teşhis' => 'Teşhis Aşamasında',
    'Onarım' => 'Onarılıyor',
    'Test' => 'Test Ediliyor',
    'Tamamlandı' => 'Tamamlandı',
    'Hazır' => 'Teslime Hazır'
];

// Takip koduyla sipariş detaylarını getir
if(isset($_GET['tracking_code'])) {
    $tracking_code = $_GET['tracking_code'];
    $stmt = $conn->prepare("SELECT o.*, c.customer_email FROM orders o WHERE o.tracking_code = ?");
    $stmt->bind_param("s", $tracking_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if($order) {
        $response = [
            'success' => true,
            'order' => $order
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Sipariş bulunamadı'
        ];
    }
    
    echo json_encode($response);
    exit;
}

// Sipariş durumunu güncelle
if(isset($_POST['update_status']) && isset($_SESSION['admin'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if($stmt->execute()) {
        // E-posta bildirimi gönder
        sendStatusUpdateEmail($order_id, $new_status);
        
        $response = [
            'success' => true,
            'message' => 'Durum güncellendi'
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Güncelleme başarısız'
        ];
    }
    
    echo json_encode($response);
    exit;
}

// E-posta bildirimi gönderme fonksiyonu
function sendStatusUpdateEmail($order_id, $new_status) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if($order) {
        $to = $order['customer_email'];
        $subject = "Onarım Durumu Güncellendi - " . $order['tracking_code'];
        $message = "Sayın " . $order['customer_name'] . ",\n\n";
        $message .= "Onarım talebinizin durumu güncellendi.\n";
        $message .= "Yeni Durum: " . $new_status . "\n";
        $message .= "Takip Kodu: " . $order['tracking_code'] . "\n\n";
        $message .= "Onarımınızı takip etmek için: https://handyreparaturpro.de/track?code=" . $order['tracking_code'];
        
        $headers = "From: info@handyreparaturpro.de";
        
        mail($to, $subject, $message, $headers);
    }
}

// Yeni sipariş oluşturulduğunda takip kodu atama
function assignTrackingCode($order_id) {
    global $conn;
    $tracking_code = generateTrackingCode();
    
    $stmt = $conn->prepare("UPDATE orders SET tracking_code = ? WHERE id = ?");
    $stmt->bind_param("si", $tracking_code, $order_id);
    return $stmt->execute();
}
?>