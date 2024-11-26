<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['model_id'])) {
    echo json_encode(['error' => 'Model ID required']);
    exit;
}

$model_id = (int)$_GET['model_id'];

try {
    $stmt = $db->prepare("
        SELECT rt.id, rt.name, p.price 
        FROM repair_types rt
        INNER JOIN prices p ON rt.id = p.repair_type_id
        WHERE p.model_id = ? AND rt.status = TRUE
        ORDER BY rt.name
    ");
    $stmt->execute([$model_id]);
    $repairs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($repairs);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>