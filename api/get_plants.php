<?php
session_start();
require_once '../database/database.php';
header('Content-Type: application/json');

// Prevenim erorile de tip undefined index pentru parametrii de filtrare
$origin = $_GET['origin'] ?? '';
$soil = $_GET['soil'] ?? '';
$search = $_GET['search'] ?? '';
$characteristics = $_GET['characteristics'] ?? '';

// Construim interogarea SQL dinamic in functie de filtrele aplicate
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

// Daca sunt aplicate filtre pe caracteristici, adaugam o subinterogare pentru a selecta doar plantele care au toate caracteristicile selectate
if (!empty($characteristics)) {
    $char_ids = explode(',', $characteristics);
    $placeholders = implode(',', array_fill(0, count($char_ids), '?'));
    $sql .= " AND p.id IN (
        SELECT plant_id 
        FROM plant_characteristics 
        WHERE characteristic_id IN ($placeholders)
        GROUP BY plant_id
        HAVING COUNT(DISTINCT characteristic_id) = " . count($char_ids) . " // asiguram ca planta are toate caracteristicile selectate, nu doar una dintre ele
    )";
    $params = array_merge($params, $char_ids); // adaugam ID-urile caracteristicilor ca parametrii pentru interogare
}

$sql .= " GROUP BY p.id";// Grupam rezultatele pentru a evita duplicatele cauzate de join-ul cu media 

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    //formatam rezultatele intr-un array asociativ in care cheile sunt numele coloanelor
    $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Trimitem inapoi ID-ul userului, rolul lui si lista de plante
    echo json_encode([
        "current_user_id" => $_SESSION['user_id'] ?? null,
        "current_user_role" => $_SESSION['user_role'] ?? 'user',
        "plants" => $plants
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>