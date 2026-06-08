<?php
header('Content-Type: application/json');
require_once '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([$username, $email, $hashed_password]);

        echo json_encode(["status" => "success", "message" => "Cont creat cu succes!"]);
        exit();
    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(["status" => "error", "message" => "Acest email este deja folosit!"]);
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "A apărut o eroare: " . $e->getMessage()]);
            exit();
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Metoda de cerere nu este suportată!"]);
    exit();
}
?>