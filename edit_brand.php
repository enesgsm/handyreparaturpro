<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("SELECT * FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        $brand = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($brand) {
            echo json_encode([
                'success' => true,
                'brand' => $brand
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Marka bulunamadı']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}