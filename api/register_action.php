<?php
// MODIFICARE 1: Ieșim din folderul 'api' folosind '../' pentru a găsi fisierul db.php
require_once '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Preluăm datele
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash-uim parola
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // MODIFICARE 2: Am schimbat 'username' în 'name' ca să se potrivească cu baza noastră de date
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([$username, $email, $hashed_password]);

        echo "<h3>Cont creat cu succes!</h3>";
        // MODIFICARE 3: Ajustăm calea înapoi către root pentru login
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
    // Redirecționare corectată
    header("Location: ../register.html");
    exit();
}
?>