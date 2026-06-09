<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Acces interzis! Doar administratorii pot exporta.');
}

require_once '../database/database.php';

try {
    // Exportă structura completă cu toate relațiile
    $stmt = $pdo->query("SELECT * FROM plants");
    $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_media = $pdo->query("SELECT * FROM media");
    $media = $stmt_media->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_chars = $pdo->query("SELECT * FROM plant_characteristics");
    $characteristics = $stmt_chars->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_related = $pdo->query("SELECT * FROM related_species");
    $related_species = $stmt_related->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="export_ierbar_' . date('Y-m-d') . '.xml"');
    
    $xml = new SimpleXMLElement('<ierbar/>');
    
    // Secțiune plante
    $plants_node = $xml->addChild('plante');
    foreach ($plants as $plant) {
        $plant_node = $plants_node->addChild('planta');
        foreach ($plant as $key => $value) {
            $plant_node->addChild($key, htmlspecialchars($value ?? ''));
        }
    }
    
    // Secțiune media
    $media_node = $xml->addChild('media_list');
    foreach ($media as $med) {
        $med_node = $media_node->addChild('media_item');
        foreach ($med as $key => $value) {
            $med_node->addChild($key, htmlspecialchars($value ?? ''));
        }
    }
    
    // Secțiune caracteristici plante
    $chars_node = $xml->addChild('plant_characteristics');
    foreach ($characteristics as $char) {
        $char_node = $chars_node->addChild('characteristic');
        foreach ($char as $key => $value) {
            $char_node->addChild($key, htmlspecialchars($value ?? ''));
        }
    }
    
    // Secțiune specii înrudite
    $related_node = $xml->addChild('related_species');
    foreach ($related_species as $rel) {
        $rel_node = $related_node->addChild('relation');
        foreach ($rel as $key => $value) {
            $rel_node->addChild($key, htmlspecialchars($value ?? ''));
        }
    }
    
    echo $xml->asXML();
    exit();

} catch (\PDOException $e) {
    die("Eroare la exportul XML: " . $e->getMessage());
}
?>