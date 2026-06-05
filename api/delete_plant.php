<?php
    require_once 'check_auth.php';
    require_once '../database/database.php';
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
            header("Location: ../admin_dashboard.php");
            exit();
        }catch(\PDOException $exception){
            die("Eroare la stergerea plantei: ".  exception->getMessage());
        }
    }else{
        header("Location: ../admin_dashboard.php");
        exit();
    }
?>