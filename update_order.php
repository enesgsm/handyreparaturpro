<?php
session_start();
require_once 'config.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('Unauthorized');
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orders'])) {
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("UPDATE models SET sort_order = ? WHERE id = ?");
        
        foreach ($_POST['orders'] as $id => $order) {
            $stmt->execute([(int)$order, (int)$id]);
        }
        
        $db->commit();
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        $db->rollBack();
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>