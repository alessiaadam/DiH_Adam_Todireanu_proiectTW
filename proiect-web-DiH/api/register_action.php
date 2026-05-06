<?php
require_once '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $hashed_password]);
        echo "<h3>Cont creat cu succes!</h3>";
        echo "<a href='../login.html'>Mergi la pagina de Login</a>";

    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "<h3>Eroare: Acest email este deja folosit!</h3>";
            echo "<a href='../register.html'>Încearcă din nou</a>";
        } else {
            echo "A apărut o eroare: " . $e->getMessage();
        }
    }
} else {
    header("Location: ../register.html");
    exit();
}
?>