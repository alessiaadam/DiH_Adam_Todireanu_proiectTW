<?php
require_once 'check_auth.php';
require_once '../database/database.php';
header('Content-Type: application/json; charset=utf-8');
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $common_name=$_POST['common_name'];
    $scientific_name=$_POST['scientific_name'];
    $description=$_POST['description'];
    $origin=$_POST['origin'];
    $soil=$_POST['soil'] ?? null;
    $status=$_POST['status'];
    $propagation_method=$_POST['propagation_method'];
    $user_id=$_SESSION['user_id'];
    try{
        $sql_plant="INSERT INTO `plants` (`user_id`, `common_name`, `scientific_name`, `description`, `origin`, `soil`, `status`, `propagation_method`) VALUES (?,?,?,?,?,?,?,?)";
        $stmt_plant=$pdo->prepare($sql_plant);
        $stmt_plant->execute([$user_id, $common_name, $scientific_name, $description, $origin, $soil, $status, $propagation_method]);
        $plant_id=$pdo->lastInsertId();
        if(isset($_FILES['plant_image']) && $_FILES['plant_image']['error']==0){
            $file_name=time()."_".basename($_FILES["plant_image"]["name"]);
            $target_dir="../uploads/";
            $target_file=$target_dir.$file_name;
            $db_file_path="uploads/".$file_name;
            if(move_uploaded_file($_FILES["plant_image"]["tmp_name"],$target_file)){
                $sql_media="INSERT INTO `media`( `plant_id`, `file_path`, `type`) VALUES (?,?,'image')";
                $stmt_media=$pdo->prepare($sql_media);
                $stmt_media->execute([$plant_id, $db_file_path]);
            }
        }
        if(isset($_POST['characteristics'])){
            $sql_char="INSERT INTO `plant_characteristics` (`plant_id`, `characteristic_id`) VALUES (?,?)";
            $stmt_char=$pdo->prepare($sql_char);
            foreach($_POST['characteristics'] as $char_id){
                $stmt_char->execute([$plant_id, $char_id]);
            }
        }
        echo json_encode([
            "status" => "success",
            "message" => "Planta a fost salvată cu succes!"
        ]);
        exit();
    }catch(\PDOException $e){
        echo json_encode([
            "status" => "error",
            "message" => "Eroare la adăugarea plantei: " . $e->getMessage()
        ]);
        exit();
    } 
}else{
   echo json_encode([
        "status" => "error",
        "message" => "Metodă nepermisă."
    ]);
    exit();
}
?>