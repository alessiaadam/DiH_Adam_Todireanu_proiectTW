<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acces interzis!");
}

require_once '../database/database.php';

try {
    $stmt = $pdo->query("SELECT * FROM plants");
    $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="export_plante_' . date('Y-m-d') . '.xml"');
    $xml = new SimpleXMLElement('<ierbar/>');
    
    foreach ($plants as $plant) {
        $plantNode = $xml->addChild('planta');
        foreach ($plant as $key => $value) {
            $plantNode->addChild($key, htmlspecialchars($value ?? ''));
        }
    }
    echo $xml->asXML();
    exit();

} catch (\PDOException $e) {
    die("Eroare la exportul XML: " . $e->getMessage());
}
?>