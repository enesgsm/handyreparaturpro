<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orders'])) {
    try {
        $db->beginTransaction();
        
        foreach ($_POST['orders'] as $id => $order) {
            $stmt = $db->prepare("UPDATE brands SET sort_order = ? WHERE id = ?");
            $stmt->execute([(int)$order, (int)$id]);
        }
        
        $db->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}