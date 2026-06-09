<?php
session_start();
header('Content-Type: application/json; charset=utf-8'); 
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Acces interzis! Doar administratorii pot importa date."]);
    exit();
}

require_once '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Eroare la încărcarea fișierului pe server."]);
        exit();
    }
    
    $json_content = file_get_contents($file['tmp_name']);
    $plants = json_decode($json_content, true);
    
    if ($plants === null) {
        echo json_encode(["status" => "error", "message" => "Eroare: Fișierul nu este un JSON valid sau este corupt."]);
        exit();
    }

    try {
        $pdo->beginTransaction(); 
        
        $sql = "INSERT INTO plants (common_name, scientific_name, description, origin, status, propagation_method, user_id) 
                VALUES (:common_name, :scientific_name, :description, :origin, :status, :propagation_method, :user_id)";
        $stmt = $pdo->prepare($sql);
        $current_user_id = $_SESSION['user_id'];
        
        $count = 0;
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
            $count++;
        }
        
        $pdo->commit();
        echo json_encode([
            "status" => "success", 
            "message" => "Au fost importate " . $count . " plante cu succes din fișierul JSON."
        ]);
        exit();

    } catch (\PDOException $e) {
        $pdo->rollBack(); 
        echo json_encode(["status" => "error", "message" => "Eroare baza de date: " . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Cerere invalidă."]);
    exit();
}
?>