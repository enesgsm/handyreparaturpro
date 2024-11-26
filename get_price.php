<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['model_id']) || !isset($_GET['repair_id'])) {
    echo json_encode(['error' => 'Model ID and Repair ID required']);
    exit;
}

$model_id = (int)$_GET['model_id'];
$repair_id = (int)$_GET['repair_id'];

try {
    $stmt = $db->prepare("
        SELECT price 
        FROM prices 
        WHERE model_id = ? AND repair_type_id = ?
    ");
    $stmt->execute([$model_id, $repair_id]);
    $price = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($price) {
        echo json_encode(['price' => number_format($price['price'], 2)]);
    } else {
        echo json_encode(['error' => 'Price not found']);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>