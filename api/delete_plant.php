<?php
session_start();
require_once '../database/database.php';
header('Content-Type: application/json');

// Deoarece trimitem date din JavaScript via Fetch în format JSON, le citim așa:
$date_primite = json_decode(file_get_contents('php://input'), true);
$plant_id = $date_primite['id'] ?? null;

if ($plant_id) {
    try {
        // 1. (Opțional, dar recomandat) Găsim poza și o ștergem fizic de pe server
        $stmt_media = $pdo->prepare("SELECT file_path FROM media WHERE plant_id = ? AND type = 'image'");
        $stmt_media->execute([$plant_id]);
        $media = $stmt_media->fetch();

        if ($media && file_exists("../" . $media['file_path'])) {
            unlink("../" . $media['file_path']); // unlink este funcția PHP care șterge un fișier de pe disc
        }

        // 2. Ștergem planta din baza de date
        // Datorită ON DELETE CASCADE, rândul din tabelul `media` se va șterge și el automat!
        $stmt = $pdo->prepare("DELETE FROM plants WHERE id = ?");
        $stmt->execute([$plant_id]);

        // Trimitem un răspuns de succes înapoi către JavaScript
        echo json_encode(["success" => true, "message" => "Planta a fost ștearsă!"]);

    } catch (\PDOException $e) {
        echo json_encode(["success" => false, "message" => "Eroare la baza de date: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "ID-ul plantei lipsește."]);
}
?>