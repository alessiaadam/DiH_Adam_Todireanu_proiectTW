<?php
session_start();
require_once '../database/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die('Acces interzis!');
}

// Export CSV - Acoperă doar datele de bază ale plantelor
// Pentru o copie completă a ierbarului cu media, caracteristici și relații,
// se recomandă folosirea export-ului JSON sau XML.
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="ierbar_plante_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Antet coloane
fputcsv($output, ['ID', 'User ID', 'Nume Popular', 'Nume Științific', 'Descriere', 'Origine', 'Tip Sol', 'Statut', 'Metodă Înmulțire']);

try {
    $stmt = $pdo->query("SELECT id, user_id, common_name, scientific_name, description, origin, soil, status, propagation_method FROM plants");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} catch (\PDOException $e) {
    fputcsv($output, ['EROARE: ' . $e->getMessage()]);
}

fclose($output);
exit();
?>