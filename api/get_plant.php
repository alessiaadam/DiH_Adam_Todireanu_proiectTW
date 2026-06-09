<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Verificare autentificare
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Neautentificat']);
    exit();
}

require_once '../database/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID invalid']);
    exit();
}

try {
    // Date principale planta
    $stmt = $pdo->prepare("SELECT * FROM plants WHERE id = ?");
    $stmt->execute([$id]);
    $plant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plant) {
        http_response_code(404);
        echo json_encode(['error' => 'Planta nu a fost găsită']);
        exit();
    }

    // Media (imagini si videouri)
    $stmt_media = $pdo->prepare(
        "SELECT file_path, type FROM media WHERE plant_id = ?"
    );
    $stmt_media->execute([$id]);
    $media = $stmt_media->fetchAll(PDO::FETCH_ASSOC);

    // Caracteristici grupate dupa categorie
    $stmt_chars = $pdo->prepare("
        SELECT c.name, c.category
        FROM characteristics c
        JOIN plant_characteristics pc ON c.id = pc.characteristic_id
        WHERE pc.plant_id = ?
    ");
    $stmt_chars->execute([$id]);
    $characteristics_raw = $stmt_chars->fetchAll(PDO::FETCH_ASSOC);

    // Grupare dupa categorie
    $characteristics = [];
    foreach ($characteristics_raw as $char) {
        $cat = $char['category'];
        if (!isset($characteristics[$cat])) {
            $characteristics[$cat] = [];
        }
        $characteristics[$cat][] = $char['name'];
    }

    // Specii inrudite
    $stmt_related = $pdo->prepare("
        SELECT p.id, p.common_name, m.file_path
        FROM related_species rs
        JOIN plants p ON rs.plant_id_2 = p.id
        LEFT JOIN media m ON p.id = m.plant_id AND m.type = 'image'
        WHERE rs.plant_id_1 = ?
        GROUP BY p.id
    ");
    $stmt_related->execute([$id]);
    $related_plants = $stmt_related->fetchAll(PDO::FETCH_ASSOC);

    // Drept de editare doar pentru creatorul plantei sau admin
    $can_edit = (
        $plant['user_id'] == $_SESSION['user_id'] ||
        ($_SESSION['user_role'] ?? '') === 'admin'
    );

    // Raspuns JSON cu toate datele
    echo json_encode([
        'plant' => $plant,
        'media' => $media,
        'characteristics' => $characteristics,
        'related_plants' => $related_plants,
        'can_edit' => $can_edit,
        'current_user' => [
            'username' => $_SESSION['username'] ?? 'Utilizator',
            'role' => $_SESSION['user_role'] ?? 'user',
        ],
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Eroare server']);
}