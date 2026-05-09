<?php
session_start();
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
            header("Location: ../dashboard.php");
            exit();

        } else {
            echo "<h3>Eroare: Email sau parolă incorecte!</h3>";
            echo "<a href='../login.html'>Încearcă din nou</a>";
        }

    } catch (\PDOException $e) {
        echo "A apărut o eroare la conectare: " . $e->getMessage();
    }
} else {
    header("Location: ../login.html");
    exit();
}
?>