<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'database/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: dashboard.php?error=not_found");
    exit();
}

$sql = "SELECT p.*, m.file_path 
        FROM plants p 
        LEFT JOIN media m ON p.id = m.plant_id AND m.type = 'image' 
        WHERE p.id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $plant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plant) {
        header("Location: dashboard.php?error=not_found");
        exit();
    }

    $sql_chars = "SELECT c.name, c.category 
                  FROM characteristics c
                  JOIN plant_characteristics pc ON c.id = pc.characteristic_id
                  WHERE pc.plant_id = ?";

    $stmt_chars = $pdo->prepare($sql_chars);
    $stmt_chars->execute([$id]);

    $characteristics = $stmt_chars->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    header("Location: dashboard.php?error=not_found");
    exit();
}

?>


<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Detalii plantă - Ierbar Digital</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <header>
        <h1>Colecția mea de Plante</h1>
        <nav>
            <span>Salut, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilizator'); ?>!</span>
            <a href="add_plant.php">Adaugă Plantă</a>
            <a href="api/logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <article id="plantDetails">
            <a href="dashboard.php" class="back-button">Înapoi la colecție</a>
            <h2>Detalii Plantă</h2>
            <div class="plant-detail-image-container">
                <?php if (!empty($plant['file_path'])): ?>
                    <img src="<?php echo htmlspecialchars($plant['file_path']); ?>"
                        alt="<?php echo htmlspecialchars($plant['common_name']); ?>" class="plant-image-large">
                <?php else: ?>
                    <img src="https://placehold.co/800x400/e0e0e0/666666?text=Fara+Poza" alt="Fără imagine"
                        class="plant-image-large placeholder">
                <?php endif; ?>
            </div>
            <h3>
                <?php echo htmlspecialchars($plant['common_name'] ?? '-'); ?>
            </h3>
            <p><strong>Nume științific:</strong>
                <?php echo htmlspecialchars($plant['scientific_name'] ?? '-'); ?>
            </p>
            <div class="plant-characteristics"
                style="margin-top: 20px; background: #f9f9f9; padding: 15px; border-radius: 8px;">
                <h4>Caracteristici & Cerințe</h4>

                <?php if (empty($characteristics)): ?>
                    <p><i>Nu există caracteristici specifice adăugate pentru această plantă.</i></p>
                <?php else: ?>
                    <ul style="list-style-type: none; padding-left: 0;">
                        <?php foreach ($characteristics as $char): ?>
                            <li style="margin-bottom: 5px;">
                                <strong><?php echo htmlspecialchars($char['category']); ?>:</strong>
                                <span class="char-badge">
                                    <?php echo htmlspecialchars($char['name']); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <p><strong>Descriere:</strong>
                <?php echo htmlspecialchars($plant['description'] ?? '-'); ?>
            </p>

        </article>
    </main>
</body>

</html>