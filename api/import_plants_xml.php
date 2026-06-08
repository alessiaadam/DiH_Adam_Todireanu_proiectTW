<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acces interzis!");
}

require_once '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Eroare la încărcarea fișierului XML.");
    }
    $xml = simplexml_load_file($file['tmp_name']);
    if ($xml === false) {
        die("Fișierul nu este un XML valid.");
    }

    try {
        $sql = "INSERT INTO plants (common_name, scientific_name, description, origin, status, propagation_method, user_id) 
                VALUES (:common_name, :scientific_name, :description, :origin, :status, :propagation_method, :user_id)";
        $stmt = $pdo->prepare($sql);
        $current_user_id = $_SESSION['user_id'];
        foreach ($xml->planta as $plant) {
            $stmt->execute([
                ':common_name'        => (string)$plant->common_name ?: 'Plantă Importată XML',
                ':scientific_name'    => (string)$plant->scientific_name ?: 'Necunoscut',
                ':description'        => (string)$plant->description ?: '',
                ':origin'             => (string)$plant->origin ?: '',
                ':status'             => (string)$plant->status ?: 'Comună',
                ':propagation_method' => (string)$plant->propagation_method ?: '',
                ':user_id'            => $current_user_id
            ]);
        }
        header("Location: ../admin_dashboard.php?import=xml_success");
        exit();

    } catch (\PDOException $e) {
        die("Eroare la inserarea datelor din XML: " . $e->getMessage());
    }
} else {
    header("Location: ../admin_dashboard.php");
    exit();
}
?>