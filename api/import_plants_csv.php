<?php
session_start();
// Verificare admin...
require_once '../database/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Eroare la încărcarea fișierului."]);
        exit();
    }

    // Deschidem fișierul încărcat pentru citire
    $handle = fopen($file['tmp_name'], "r");
    if ($handle !== FALSE) {
        try {
            $sql = "INSERT INTO plants (common_name, scientific_name, origin, status, propagation_method, soil, user_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $current_user_id = $_SESSION['user_id'];

            // Citim primul rând (capul de tabel) ca să-l ignorăm (nu vrem să inserăm cuvântul "Nume Popular" în BD)
            fgetcsv($handle, 1000, ",");

            // Citim restul rândurilor unul câte unul
            // fgetcsv împarte automat rândul într-un array bazat pe virgule
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // $data[0] este ID-ul (pe care îl ignorăm la insert), $data[1] e common_name, etc.
                // Atenție la indecși: ei trebuie să corespundă cu ordinea din fputcsv de la export!
                $stmt->execute([
                    $data[1], // common_name
                    $data[2], // scientific_name
                    $data[3], // origin
                    $data[4], // status
                    $data[5], // propagation_method
                    $data[6], // soil
                    $current_user_id
                ]);
            }
            fclose($handle);

            echo json_encode(["status" => "success", "message" => "Datele din CSV au fost importate!"]);
            exit();

        } catch (\PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Eroare la baza de date: " . $e->getMessage()]);
            exit();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Nu s-a putut citi fișierul CSV."]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Metodă incorectă."]);
    exit();
}
?>