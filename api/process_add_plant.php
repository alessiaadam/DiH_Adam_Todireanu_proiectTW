<?php
require_once 'check_auth.php';
require_once '../database/database.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $common_name = $_POST['common_name'];
    $scientific_name = $_POST['scientific_name'];
    $description = $_POST['description'];
    $origin = $_POST['origin'];
    $soil = $_POST['soil'] ?? null;
    $status = $_POST['status'];
    $propagation_method = $_POST['propagation_method'];
    $user_id = $_SESSION['user_id'];

    try {
        $sql_plant = "INSERT INTO `plants` (`user_id`, `common_name`, `scientific_name`, `description`, `origin`, `soil`, `status`, `propagation_method`) VALUES (?,?,?,?,?,?,?,?)";
        $stmt_plant = $pdo->prepare($sql_plant);
        $stmt_plant->execute([$user_id, $common_name, $scientific_name, $description, $origin, $soil, $status, $propagation_method]);
        $plant_id = $pdo->lastInsertId();

        if (isset($_FILES['plant_media']) && !empty($_FILES['plant_media']['name'][0])) {
            $file_count = count($_FILES['plant_media']['name']);
            $target_dir = "../uploads/";

            $sql_media = "INSERT INTO `media`( `plant_id`, `file_path`, `type`) VALUES (?,?,?)";
            $stmt_media = $pdo->prepare($sql_media);

            for ($i = 0; $i < $file_count; $i++) {
                if ($_FILES['plant_media']['error'][$i] == UPLOAD_ERR_OK) {
                    $original_name = basename($_FILES['plant_media']['name'][$i]);
                    $file_name = time() . "_" . uniqid() . "_" . $original_name;

                    $target_file = $target_dir . $file_name;
                    $db_file_path = "uploads/" . $file_name;

                    $mime_type = $_FILES['plant_media']['type'][$i];
                    $media_type = (strpos($mime_type, 'video') === 0) ? 'video' : 'image';

                    if (move_uploaded_file($_FILES['plant_media']['tmp_name'][$i], $target_file)) {
                        $stmt_media->execute([$plant_id, $db_file_path, $media_type]);
                    }
                }
            }
        }

        if (isset($_POST['characteristics'])) {
            $sql_char = "INSERT INTO `plant_characteristics` (`plant_id`, `characteristic_id`) VALUES (?,?)";
            $stmt_char = $pdo->prepare($sql_char);
            foreach ($_POST['characteristics'] as $char_id) {
                $stmt_char->execute([$plant_id, $char_id]);
            }
        }

        if (isset($_POST['related_species']) && is_array($_POST['related_species'])) {
            $sql_rel = "INSERT INTO `related_species` (`plant_id_1`, `plant_id_2`) VALUES (?, ?)";
            $stmt_rel = $pdo->prepare($sql_rel);
            
            foreach ($_POST['related_species'] as $related_id) {
                $stmt_rel->execute([$plant_id, $related_id]);
                $stmt_rel->execute([$related_id, $plant_id]);
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => "Planta a fost salvată cu succes!"
        ]);
        exit();
    } catch (\PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Eroare la adăugarea plantei: " . $e->getMessage()
        ]);
        exit();
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Metodă nepermisă."
    ]);
    exit();
}
?>