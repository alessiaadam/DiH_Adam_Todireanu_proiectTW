<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: dashboard.php?error=not_found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalii plantă — Ierbar Digital</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/plant_details.css">
</head>

<body>
    <header>
        <h1>Colecția mea de Plante</h1>
        <nav>
            <span id="headerUsername"></span>
            <a href="add_plant.php">Adaugă Plantă</a>
            <a href="admin_dashboard.php" id="adminLink" class="green-button" style="display:none;">Panou Admin</a>
            <a href="api/logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <article id="plantDetails">
            <div class="loading-state">
                <p>Se încarcă detaliile plantei...</p>
            </div>
        </article>
    </main>

    <script src="js/plant_details.js"></script>
</body>

</html>