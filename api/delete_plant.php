<?php
    require_once 'check_auth.php';
    require_once '../database/database.php';
    header('Content-Type: application/json; charset=utf-8');
    if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['plant_id'])){
        $plant_id=$_POST['plant_id'];
        try{
            $stmt_media=$pdo->prepare("SELECT file_path FROM media WHERE plant_id=?");
            $stmt_media->execute([$plant_id]);
            $media = $stmt_media->fetch();
            if($media && file_exists("../". $media['file_path'])){
                unlink("../". $media['file_path']);
            }
            $stmt_delete=$pdo->prepare("DELETE FROM plants WHERE id=?");
            $stmt_delete->execute([$plant_id]);
            echo json_encode([
            "status" => "success", 
            "message" => "Planta a fost ștearsă cu succes!"
        ]);
            exit();
        }catch(\PDOException $exception){
            echo json_encode([
            "status" => "error", 
            "message" => "Eroare la ștergere: " . $exception->getMessage()
        ]);
        exit();
        }
    }else{
        echo json_encode([
        "status" => "error", 
        "message" => "Cerere invalidă!"
    ]);
    exit();
    }
?>