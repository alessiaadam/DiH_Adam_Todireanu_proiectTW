<?php
header('Content-Type: application/json');
require_once '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //extragem si curatam datele primite din formular
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    //validari simple
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Toate câmpurile sunt obligatorii."]);
        exit();
    }
    if (strlen($password) < 6) {
        echo json_encode(["status" => "error", "message" => "Parola trebuie să aibă minim 6 caractere."]);
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Email invalid."]);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $hashed_password]);

        echo json_encode(["status" => "success", "message" => "Cont creat cu succes!"]);
        exit();

    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) { // Cod de eroare pentru incalcarea unui constraint UNIQUE
            echo json_encode(["status" => "error", "message" => "Acest email este deja folosit!"]);
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "A apărut o eroare: " . $e->getMessage()]);
            exit();
        }
    }
} else {
    //daca metoda de cerere nu este POST, returnam o eroare
    echo json_encode(["status" => "error", "message" => "Metoda de cerere nu este suportată!"]);
    exit();
}
?>