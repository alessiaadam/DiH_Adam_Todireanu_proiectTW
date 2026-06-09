<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Acces interzis! Doar administratorii pot importa date CSV."]);
    exit();
}

require_once '../database/database.php';

function removeBom($value) {
    return preg_replace('/^\xEF\xBB\xBF/', '', $value);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Eroare la încărcarea fișierului."]);
        exit();
    }

    $handle = fopen($file['tmp_name'], 'r');
    if ($handle !== false) {
        try {
            $pdo->beginTransaction();
            $current_user_id = $_SESSION['user_id'];
            $plant_id_map = [];
            $current_section = null;
            $header = [];

            $insert_plant = $pdo->prepare("INSERT INTO plants (user_id, common_name, scientific_name, description, origin, soil, status, propagation_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_media = $pdo->prepare("INSERT INTO media (plant_id, file_path, type) VALUES (?, ?, ?)");
            $insert_char = $pdo->prepare("INSERT INTO characteristics (id, name, category) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), category = VALUES(category)");
            $insert_plant_char = $pdo->prepare("INSERT INTO plant_characteristics (plant_id, characteristic_id) VALUES (?, ?)");
            $insert_related = $pdo->prepare("INSERT INTO related_species (plant_id_1, plant_id_2) VALUES (?, ?)");

            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                if (count($data) === 0) {
                    continue;
                }

                $firstCell = trim(removeBom($data[0]));

                if ($firstCell === '' && count($data) === 1) {
                    continue;
                }

                if (stripos($firstCell, '# SECTION:') === 0) {
                    $sectionName = trim(substr($firstCell, strlen('# SECTION:')));
                    $current_section = strtolower($sectionName);
                    $header = [];
                    continue;
                }

                if ($current_section === null) {
                    continue;
                }

                if (empty($header)) {
                    $header = array_map('trim', $data);
                    continue;
                }

                $row = array_combine($header, array_map('trim', $data));
                if ($row === false) {
                    continue;
                }

                switch ($current_section) {
                    case 'plants':
                        $oldId = (int)($row['id'] ?? 0);
                        $insert_plant->execute([
                            $current_user_id,
                            $row['common_name'] ?? '',
                            $row['scientific_name'] ?? '',
                            $row['description'] ?? '',
                            $row['origin'] ?? '',
                            $row['soil'] ?? null,
                            $row['status'] ?? '',
                            $row['propagation_method'] ?? ''
                        ]);
                        $plant_id_map[$oldId] = $pdo->lastInsertId();
                        break;

                    case 'media':
                        $oldPlantId = (int)($row['plant_id'] ?? 0);
                        $newPlantId = $plant_id_map[$oldPlantId] ?? null;
                        if ($newPlantId) {
                            $insert_media->execute([$newPlantId, $row['file_path'] ?? '', $row['type'] ?? 'image']);
                        }
                        break;

                    case 'characteristics':
                        $insert_char->execute([
                            (int)($row['id'] ?? 0),
                            $row['name'] ?? '',
                            $row['category'] ?? ''
                        ]);
                        break;

                    case 'plant_characteristics':
                        $oldPlantId = (int)($row['plant_id'] ?? 0);
                        $newPlantId = $plant_id_map[$oldPlantId] ?? null;
                        if ($newPlantId) {
                            $insert_plant_char->execute([$newPlantId, (int)($row['characteristic_id'] ?? 0)]);
                        }
                        break;

                    case 'related_species':
                        $oldPlantId1 = (int)($row['plant_id_1'] ?? 0);
                        $oldPlantId2 = (int)($row['plant_id_2'] ?? 0);
                        $newPlantId1 = $plant_id_map[$oldPlantId1] ?? null;
                        $newPlantId2 = $plant_id_map[$oldPlantId2] ?? null;
                        if ($newPlantId1 && $newPlantId2) {
                            $insert_related->execute([$newPlantId1, $newPlantId2]);
                        }
                        break;
                }
            }

            fclose($handle);
            $pdo->commit();

            echo json_encode(["status" => "success", "message" => "CSV-ul a fost importat complet cu succes!" ]);
            exit();
        } catch (\PDOException $e) {
            $pdo->rollBack();
            fclose($handle);
            echo json_encode(["status" => "error", "message" => "Eroare la importul CSV: " . $e->getMessage()]);
            exit();
        }
    }

    echo json_encode(["status" => "error", "message" => "Nu s-a putut citi fișierul CSV."]);
    exit();
}

echo json_encode(["status" => "error", "message" => "Metodă incorectă."]);
exit();
?>