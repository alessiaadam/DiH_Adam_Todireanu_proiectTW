<?php
session_start();
require_once '../database/database.php';
header('Content-Type: application/json');

$origin = $_GET['origin'] ?? '';
$soil = $_GET['soil'] ?? '';
$search = $_GET['search'] ?? '';
$characteristics = $_GET['characteristics'] ?? '';

$sql = "SELECT p.*, MIN(m.file_path) as file_path 
        FROM plants p 
        LEFT JOIN media m ON p.id = m.plant_id AND m.type = 'image' 
        WHERE 1=1";
$params = [];

if (!empty($origin)) {
    $sql .= " AND p.origin LIKE ?";
    $params[] = "%$origin%";
}
if (!empty($soil)) {
    $sql .= " AND p.soil = ?";
    $params[] = $soil;
}
if (!empty($search)) {
    $sql .= " AND (p.common_name LIKE ? OR p.scientific_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}


if (!empty($characteristics)) {
    $char_ids = explode(',', $characteristics);
    $placeholders = implode(',', array_fill(0, count($char_ids), '?'));
    $sql .= " AND p.id IN (
        SELECT plant_id 
        FROM plant_characteristics 
        WHERE characteristic_id IN ($placeholders)
        GROUP BY plant_id
        HAVING COUNT(DISTINCT characteristic_id) = " . count($char_ids) . "
    )";
    $params = array_merge($params, $char_ids);
}

$sql .= " GROUP BY p.id";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        "current_user_id" => $_SESSION['user_id'] ?? null,
        "current_user_role" => $_SESSION['user_role'] ?? 'user',
        "plants" => $plants
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>