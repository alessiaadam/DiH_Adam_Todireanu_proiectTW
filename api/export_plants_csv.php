<?php
session_start();
require_once '../database/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die('Acces interzis!');
}

// Setăm headerele pentru a forța browserul să descarce un fișier CSV, nu să-l afișeze pe ecran
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="ierbar_export.csv"');

// Creăm un flux de ieșire (output) direct în browser
$output = fopen('php://output', 'w');

// Adăugăm primul rând: Capul de tabel (numele coloanelor)
fputcsv($output, ['ID', 'Nume Popular', 'Nume Stiintific', 'Origine', 'Statut', 'Metoda Inmultire']);

try {
    // Luăm plantele din baza de date
    $stmt = $pdo->query("SELECT id, common_name, scientific_name, origin, status, propagation_method FROM plants");

    // Parcurgem fiecare rând din baza de date
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // fputcsv transformă automat array-ul într-un rând cu virgule
        fputcsv($output, $row);
    }
} catch (\PDOException $e) {
    // Dacă apare o eroare, o scriem în fișier pentru a o putea citi
    fputcsv($output, ['Eroare la export: ' . $e->getMessage()]);
}

fclose($output);
exit();
?>