<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acces interzis! Doar administratorii pot importa date.");
}

require_once '../database/database.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Eroare la încărcarea fișierului pe server.");
    }
    $json_content = file_get_contents($file['tmp_name']);
    $plants = json_decode($json_content, true);
    if ($plants === null) {
        die("Eroare: Fișierul nu este un JSON valid sau este corupt.");
    }

    try {
        $sql = "INSERT INTO plants (common_name, scientific_name, description, origin, status, propagation_method, user_id) 
                VALUES (:common_name, :scientific_name, :description, :origin, :status, :propagation_method, :user_id)";
        $stmt = $pdo->prepare($sql);
        $current_user_id = $_SESSION['user_id'];
        foreach ($plants as $plant) {
            $stmt->execute([
                ':common_name'        => $plant['common_name'] ?? 'Plantă Importată',
                ':scientific_name'    => $plant['scientific_name'] ?? 'Necunoscut',
                ':description'        => $plant['description'] ?? '',
                ':origin'             => $plant['origin'] ?? '',
                ':status'             => $plant['status'] ?? 'Comună',
                ':propagation_method' => $plant['propagation_method'] ?? '',
                ':user_id'            => $current_user_id
            ]);
        }
        header("Location: ../admin_dashboard.php?import=success");
        exit();

    } catch (\PDOException $e) {
        die("Eroare la inserarea datelor în baza de date: " . $e->getMessage());
    }
} else {
    header("Location: ../admin_dashboard.php");
    exit();
}
?>