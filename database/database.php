<?php
// Setările pentru baza de date
$host = '127.0.0.1'; // sau 'localhost'
$db   = 'dih_db';    // Numele bazei tale de date
$user = 'root';      // Utilizatorul implicit pentru XAMPP
$pass = '';          // Parola implicită (la XAMPP este goală)
$charset = 'utf8mb4';

// Configurarea conexiunii (DSN - Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opțiuni suplimentare pentru securitate și afișarea erorilor
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Ne va arăta erorile clar
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Returnează datele ca un array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Ajută la securitate împotriva SQL Injection
];

// Încercăm să ne conectăm la baza de date
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Dacă vrei să testezi, poți decomenta (șterge cele două slash-uri) linia de mai jos:
    echo "Conexiunea la baza de date a reusit!";
} catch (\PDOException $e) {
    // Dacă pică conexiunea, oprim tot și afișăm eroarea
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>