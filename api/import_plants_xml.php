<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Acces interzis! Doar administratorii pot importa."]);
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
        $current_user_id = $_SESSION['user_id'];
        $plant_id_map = []; // Mapare ID-uri vechi -> noi
        
        // 1. Importă plante
        if (isset($xml->plante)) {
            $sql = "INSERT INTO plants (user_id, common_name, scientific_name, description, origin, soil, status, propagation_method) 
                    VALUES (:user_id, :common_name, :scientific_name, :description, :origin, :soil, :status, :propagation_method)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($xml->plante->planta as $plant) {
                $old_id = (int)$plant->id;
                $stmt->execute([
                    ':user_id'            => $current_user_id,
                    ':common_name'        => (string)$plant->common_name ?: 'Plantă Importată',
                    ':scientific_name'    => (string)$plant->scientific_name ?: 'Necunoscut',
                    ':description'        => (string)$plant->description ?: '',
                    ':origin'             => (string)$plant->origin ?: '',
                    ':soil'               => (string)$plant->soil ?: null,
                    ':status'             => (string)$plant->status ?: 'Comună',
                    ':propagation_method' => (string)$plant->propagation_method ?: ''
                ]);
                $new_id = $pdo->lastInsertId();
                $plant_id_map[$old_id] = $new_id;
            }
        }
        
        // 2. Importă media
        if (isset($xml->media_list)) {
            $sql = "INSERT INTO media (plant_id, file_path, type) VALUES (:plant_id, :file_path, :type)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($xml->media_list->media_item as $med) {
                $old_plant_id = (int)$med->plant_id;
                $mapped_plant_id = $plant_id_map[$old_plant_id] ?? null;
                if ($mapped_plant_id) {
                    $stmt->execute([
                        ':plant_id' => $mapped_plant_id,
                        ':file_path' => (string)$med->file_path,
                        ':type' => (string)$med->type
                    ]);
                }
            }
        }
        
        // 3. Importă caracteristici plante
        if (isset($xml->plant_characteristics)) {
            $sql = "INSERT INTO plant_characteristics (plant_id, characteristic_id) VALUES (:plant_id, :characteristic_id)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($xml->plant_characteristics->characteristic as $char) {
                $old_plant_id = (int)$char->plant_id;
                $mapped_plant_id = $plant_id_map[$old_plant_id] ?? null;
                if ($mapped_plant_id) {
                    $stmt->execute([
                        ':plant_id' => $mapped_plant_id,
                        ':characteristic_id' => (int)$char->characteristic_id
                    ]);
                }
            }
        }
        
        // 4. Importă relații între specii
        if (isset($xml->related_species)) {
            $sql = "INSERT INTO related_species (plant_id_1, plant_id_2) VALUES (:plant_id_1, :plant_id_2)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($xml->related_species->relation as $rel) {
                $old_id_1 = (int)$rel->plant_id_1;
                $old_id_2 = (int)$rel->plant_id_2;
                $mapped_id_1 = $plant_id_map[$old_id_1] ?? null;
                $mapped_id_2 = $plant_id_map[$old_id_2] ?? null;
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
            "message" => "Structura completă a ierbarului a fost importată din XML cu succes."
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