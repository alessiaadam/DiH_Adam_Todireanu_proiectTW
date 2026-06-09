<?php
session_start();
require_once '../database/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die('Acces interzis!');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="ierbar_complet_' . date('Y-m-d') . '.csv"');

echo "\xEF\xBB\xBF";
$output = fopen('php://output', 'w');

try {
    // Planta
    fputcsv($output, ['# SECTION: plants']);
    fputcsv($output, ['id', 'user_id', 'common_name', 'scientific_name', 'description', 'origin', 'soil', 'status', 'propagation_method']);
    $stmt = $pdo->query("SELECT id, user_id, common_name, scientific_name, description, origin, soil, status, propagation_method FROM plants");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fputcsv($output, []);

    // Media
    fputcsv($output, ['# SECTION: media']);
    fputcsv($output, ['id', 'plant_id', 'file_path', 'type']);
    $stmt = $pdo->query("SELECT id, plant_id, file_path, type FROM media");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fputcsv($output, []);

    // Caracteristici
    fputcsv($output, ['# SECTION: characteristics']);
    fputcsv($output, ['id', 'name', 'category']);
    $stmt = $pdo->query("SELECT id, name, category FROM characteristics");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fputcsv($output, []);

    // Plant characteristics
    fputcsv($output, ['# SECTION: plant_characteristics']);
    fputcsv($output, ['plant_id', 'characteristic_id']);
    $stmt = $pdo->query("SELECT plant_id, characteristic_id FROM plant_characteristics");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fputcsv($output, []);

    // Related species
    fputcsv($output, ['# SECTION: related_species']);
    fputcsv($output, ['plant_id_1', 'plant_id_2']);
    $stmt = $pdo->query("SELECT plant_id_1, plant_id_2 FROM related_species");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} catch (\PDOException $e) {
    fputcsv($output, ['EROARE: ' . $e->getMessage()]);
}

fclose($output);
exit();
?>