<?php
require_once '../database/database.php';
header('Content-Type: application/json');

$origin = $_GET['origin'] ?? '';
$soil = $_GET['soil'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT p.*, m.file_path 
        FROM plants p 
        LEFT JOIN media m ON p.id = m.plant_id AND m.type = 'image' 
        WHERE 1=1";
$params = [];

if (!empty($origin)) {
    $sql .= " AND origin = ?";
    $params[] = $origin;
}
if (!empty($search)) {
    $sql .= " AND (common_name LIKE ? OR scientific_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $plants = $stmt->fetchAll();

    echo json_encode($plants);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}