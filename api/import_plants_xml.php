<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Acces interzis! Doar administratorii."]);
    exit();
}

require_once '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Eroare la încărcarea fișierului XML."]);
        exit();
    }
    
    $xml = simplexml_load_file($file['tmp_name']);
    if ($xml === false) {
        echo json_encode(["status" => "error", "message" => "Fișierul nu este un XML valid."]);
        exit();
    }
    try {
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO plants (common_name, scientific_name, description, origin, status, propagation_method, user_id) 
                VALUES (:common_name, :scientific_name, :description, :origin, :status, :propagation_method, :user_id)";
        $stmt = $pdo->prepare($sql);
        $current_user_id = $_SESSION['user_id'];
        
        $count = 0;
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
            $count++;
        }
        $pdo->commit();
        echo json_encode([
            "status" => "success", 
            "message" => "Au fost importate " . $count . " plante cu succes din fișierul XML."
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