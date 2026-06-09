<?php
require_once 'check_auth.php';
require_once '../database/database.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['plant_id'])) {
    $plant_id = $_POST['plant_id'];
    $common_name = $_POST['common_name'];
    $scientific_name = $_POST['scientific_name'];
    $description = $_POST['description'];
    $origin = $_POST['origin'];
    $status = $_POST['status'];
    $propagation_method = $_POST['propagation_method'];

    try {
        $stmt_check = $pdo->prepare("SELECT user_id FROM plants WHERE id = ?");
        $stmt_check->execute([$plant_id]);
        $plant = $stmt_check->fetch();

        if (!$plant) {
            echo json_encode(["status" => "error", "message" => "Planta nu există!"]);
            exit();
        }

        $current_user_id = $_SESSION['user_id'] ?? null;
        $current_user_role = $_SESSION['user_role'] ?? 'user';
        if ($plant['user_id'] != $current_user_id && $current_user_role !== 'admin') {
            echo json_encode(["status" => "error", "message" => "Acces interzis!"]);
            exit();
        }

        $sql_update = "UPDATE `plants` SET `common_name` = ?, `scientific_name` = ?, `description` = ?, `origin` = ?, `status` = ?, `propagation_method` = ? WHERE `id` = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$common_name, $scientific_name, $description, $origin, $status, $propagation_method, $plant_id]);

        if (isset($_FILES['plant_image']) && $_FILES['plant_image']['error'] == 0) {
            $stmt_media = $pdo->prepare("SELECT file_path FROM media WHERE plant_id = ?");
            $stmt_media->execute([$plant_id]);
            $old_media = $stmt_media->fetch();
            if ($old_media && file_exists("../" . $old_media['file_path'])) {
                unlink("../" . $old_media['file_path']);
            }

            $file_name = time() . "_" . basename($_FILES["plant_image"]["name"]);
            $target_file = "../uploads/" . $file_name;
            $db_file_path = "uploads/" . $file_name;

            if (move_uploaded_file($_FILES["plant_image"]["tmp_name"], $target_file)) {
                $pdo->prepare("DELETE FROM media WHERE plant_id = ?")->execute([$plant_id]);
                $pdo->prepare("INSERT INTO `media` (`plant_id`, `file_path`, `type`) VALUES (?, ?, 'image')")->execute([$plant_id, $db_file_path]);
            }
        }

        $pdo->prepare("DELETE FROM plant_characteristics WHERE plant_id = ?")->execute([$plant_id]);
        if (isset($_POST['characteristics'])) {
            $stmt_char = $pdo->prepare("INSERT INTO `plant_characteristics` (`plant_id`, `characteristic_id`) VALUES (?, ?)");
            foreach ($_POST['characteristics'] as $char_id) {
                $stmt_char->execute([$plant_id, $char_id]);
            }
        }
        $pdo->prepare("DELETE FROM related_species WHERE plant_id_1 = ? OR plant_id_2 = ?")->execute([$plant_id, $plant_id]);
        if (isset($_POST['related_species']) && is_array($_POST['related_species'])) {
            $stmt_rel = $pdo->prepare("INSERT INTO `related_species` (`plant_id_1`, `plant_id_2`) VALUES (?, ?)");
            foreach ($_POST['related_species'] as $related_id) {
                // Legătura directă
                $stmt_rel->execute([$plant_id, $related_id]);
                // Legătura inversă
                $stmt_rel->execute([$related_id, $plant_id]);
            }
        }
        echo json_encode(["status" => "success", "message" => "Planta a fost modificată cu succes!"]);
        exit();

    } catch (\PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Eroare la editare: " . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Cerere invalidă!"]);
    exit();
}
?>