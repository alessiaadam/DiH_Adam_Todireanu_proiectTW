<?php
session_start();
header('Content-Type: application/json');
require_once '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            echo json_encode(["status" => "success", "message" => "Autentificare reușită!"]);
            exit();

        } else {
            echo json_encode(["status" => "error", "message" => "Email sau parolă incorecte!"]);
            exit();
        }

    } catch (\PDOException $e) {
        echo json_encode(["status" => "error", "message" => "A apărut o eroare la conectare: " . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Metoda de cerere nu este suportată!"]);
    exit();
}
?>