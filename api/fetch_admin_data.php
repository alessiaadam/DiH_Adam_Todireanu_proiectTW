<?php
try{
    $stmt_users =$pdo->query("SELECT id, name, email, role FROM users");
    $users = $stmt_users->fetchAll();
    $stmt_plants = $pdo->query("SELECT id,common_name, scientific_name, status FROM plants");
    $plants = $stmt_plants->fetchAll();

}catch(\PDOException $exception){
    die("Eroare la preluarea datelor: " . $exception->getMessage());
}
?>