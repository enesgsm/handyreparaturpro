<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sipariş ID gerekli']);
    exit();
}

try {
    $stmt = $db->prepare("
        SELECT o.*, 
               b.name as brand_name, 
               m.name as model_name, 
               r.name as repair_name,
               CONCAT('RP', LPAD(o.id, 6, '0')) as order_number
        FROM orders o
        JOIN brands b ON o.brand_id = b.id
        JOIN models m ON o.model_id = m.id
        JOIN repair_types r ON o.repair_type_id = r.id
        WHERE o.id = ?
    ");
    
    $stmt->execute([(int)$_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo json_encode([
            'success' => true,
            'order' => $order
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Sipariş bulunamadı'
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası'
    ]);
}
?>