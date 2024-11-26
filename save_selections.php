<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['repair_selection'] = [
        'brand' => $_POST['brand'],
        'brand_id' => $_POST['brand_id'],
        'model' => $_POST['model'],
        'model_id' => $_POST['model_id'],
        'repair' => $_POST['repair'],
        'repair_type_id' => $_POST['repair_type_id'],
        'price' => $_POST['price']
    ];
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}