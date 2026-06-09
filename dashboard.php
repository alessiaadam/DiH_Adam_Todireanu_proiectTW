<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Ierbar Digital</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <header>
        <h1>Colecția mea de Plante</h1>
        <nav>
            <span>Salut, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilizator'); ?>!</span>
            <a href="add_plant.php">Adaugă Plantă</a>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="admin_dashboard.php" class="green-button">Panou Admin</a>
                <?php endif; ?>
            <a href="api/logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <section id="filters">
            <h3>Filtrează plantele</h3>
            <div class="filter-group">
                <input type="text" id="searchName" placeholder="Caută după nume...">

                <select id="filterOrigin">
                    <option value="">Toate originile</option>
                    <option value="Europe">Europa</option>
                    <option value="Asia">Asia</option>
                    <option value="America">America</option>
                </select>

                <select id="filterSoil">
                    <option value="">Orice tip de sol</option>
                    <option value="nisipos">Nisipos</option>
                    <option value="argilos">Argilos</option>
                </select>

                <button id="btnSearch">Aplică Filtre</button>
            </div>
        </section>

        <section id="plantsContainer" class="plants-grid">
            <p>Se încarcă plantele...</p>
        </section>
    </main>

    <script src="js/dashboard.js?v=2"></script>
</body>

</html>