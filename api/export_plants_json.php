<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acces interzis! Doar administratorii pot exporta baza de date.");
}

require_once '../database/database.php';

try {
    $stmt = $pdo->query("SELECT * FROM plants");
    $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="export_plante_' . date('Y-m-d') . '.json"');
    echo json_encode($plants, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
} catch (\PDOException $e) {
    die("Eroare la exportul datelor: " . $e->getMessage());
}
?>