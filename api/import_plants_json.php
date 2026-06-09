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
    $data = json_decode($json_content, true);
    
    if ($data === null) {
        echo json_encode(["status" => "error", "message" => "Eroare: Fișierul nu este un JSON valid sau este corupt."]);
        exit();
    }

    try {
        $pdo->beginTransaction();
        $current_user_id = $_SESSION['user_id'];
        $plant_id_map = []; // Mapare ID-uri vechi -> noi
        
        // 1. Importă plante
        if (isset($data['plants']) && is_array($data['plants'])) {
            $sql = "INSERT INTO plants (user_id, common_name, scientific_name, description, origin, soil, status, propagation_method) 
                    VALUES (:user_id, :common_name, :scientific_name, :description, :origin, :soil, :status, :propagation_method)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($data['plants'] as $plant) {
                $old_id = $plant['id'];
                $stmt->execute([
                    ':user_id'            => $current_user_id,
                    ':common_name'        => $plant['common_name'] ?? 'Plantă Importată',
                    ':scientific_name'    => $plant['scientific_name'] ?? 'Necunoscut',
                    ':description'        => $plant['description'] ?? '',
                    ':origin'             => $plant['origin'] ?? '',
                    ':soil'               => $plant['soil'] ?? null,
                    ':status'             => $plant['status'] ?? 'Comună',
                    ':propagation_method' => $plant['propagation_method'] ?? ''
                ]);
                $new_id = $pdo->lastInsertId();
                $plant_id_map[$old_id] = $new_id;
            }
        }
        
        // 2. Importă media
        if (isset($data['media']) && is_array($data['media'])) {
            $sql = "INSERT INTO media (plant_id, file_path, type) VALUES (:plant_id, :file_path, :type)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($data['media'] as $med) {
                $mapped_plant_id = $plant_id_map[$med['plant_id']] ?? null;
                if ($mapped_plant_id) {
                    $stmt->execute([
                        ':plant_id' => $mapped_plant_id,
                        ':file_path' => $med['file_path'],
                        ':type' => $med['type']
                    ]);
                }
            }
        }
        
        // 3. Importă caracteristici
        if (isset($data['plant_characteristics']) && is_array($data['plant_characteristics'])) {
            $sql = "INSERT INTO plant_characteristics (plant_id, characteristic_id) VALUES (:plant_id, :characteristic_id)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($data['plant_characteristics'] as $char) {
                $mapped_plant_id = $plant_id_map[$char['plant_id']] ?? null;
                if ($mapped_plant_id) {
                    $stmt->execute([
                        ':plant_id' => $mapped_plant_id,
                        ':characteristic_id' => $char['characteristic_id']
                    ]);
                }
            }
        }
        
        // 4. Importă relații între specii
        if (isset($data['related_species']) && is_array($data['related_species'])) {
            $sql = "INSERT INTO related_species (plant_id_1, plant_id_2) VALUES (:plant_id_1, :plant_id_2)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($data['related_species'] as $rel) {
                $mapped_id_1 = $plant_id_map[$rel['plant_id_1']] ?? null;
                $mapped_id_2 = $plant_id_map[$rel['plant_id_2']] ?? null;
                if ($mapped_id_1 && $mapped_id_2) {
                    $stmt->execute([
                        ':plant_id_1' => $mapped_id_1,
                        ':plant_id_2' => $mapped_id_2
                    ]);
                }
            }
        }

        $pdo->commit();
        echo json_encode([
            "status" => "success", 
            "message" => "Structura completă a ierbarului a fost importată cu succes."
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