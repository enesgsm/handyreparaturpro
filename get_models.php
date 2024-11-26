<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['brand_id'])) {
    echo json_encode(['error' => 'Brand ID required']);
    exit;
}

$brand_id = (int)$_GET['brand_id'];

try {
    $stmt = $db->prepare("SELECT id, name FROM models WHERE brand_id = ? AND status = TRUE ORDER BY name");
    $stmt->execute([$brand_id]);
    $models = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($models);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>