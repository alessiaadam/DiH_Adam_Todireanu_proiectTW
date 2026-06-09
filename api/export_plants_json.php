<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die(json_encode(["error" => "Acces interzis! Doar administratorii pot exporta baza de date."]));
}

require_once '../database/database.php';

try {
    // Exportă structura completă: plante, media, caracteristici și specii înrudite
    $stmt = $pdo->query("SELECT * FROM plants");
    $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_media = $pdo->query("SELECT * FROM media");
    $media = $stmt_media->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_chars = $pdo->query("SELECT * FROM plant_characteristics");
    $characteristics = $stmt_chars->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_related = $pdo->query("SELECT * FROM related_species");
    $related_species = $stmt_related->fetchAll(PDO::FETCH_ASSOC);
    
    $export_data = [
        "plants" => $plants,
        "media" => $media,
        "plant_characteristics" => $characteristics,
        "related_species" => $related_species
    ];
    
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="export_ierbar_' . date('Y-m-d') . '.json"');
    echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
} catch (\PDOException $e) {
    die(json_encode(["error" => "Eroare la exportul datelor: " . $e->getMessage()]));
}
?>