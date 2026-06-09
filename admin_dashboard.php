<?php
require_once 'api/check_auth.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}
require_once 'database/database.php';
require_once 'api/fetch_admin_data.php';
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Panou Administrare - Ierbar Virtual</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>

<body>

    <div class="container">
        <h1>Panou de Administrare</h1>
        <div class="toolbar">
            <a href="add_plant.php" class="btn btn-green">+ Adaugă plantă</a>

            <form id="formExport" action="api/export_plants_json.php" method="GET" class="action-form">
                <select onchange="document.getElementById('formExport').action = this.value;" class="format-select">
                    <option value="api/export_plants_csv.php">CSV</option>
                    <option value="api/export_plants_json.php">JSON</option>
                    <option value="api/export_plants_xml.php">XML</option>
                </select>
                <button type="submit" class="btn btn-green">Exportă</button>
            </form>

            <form id="formImport" class="action-form import-section">
                <input type="file" id="importFile" name="import_file" accept=".csv,.json,.xml" required>

                <select id="importType" class="format-select" required>
                    <option value="" disabled selected>Alege formatul</option>
                    <option value="csv">CSV</option>
                    <option value="json">JSON</option>
                    <option value="xml">XML</option>
                </select>

                <button type="submit" class="btn btn-green">Importă</button>
            </form>
        </div>
        <h2>Lista Utilizatori</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nume</th>
                <th>Email</th>
                <th>Rol</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']); ?></td>
                    <td><?= htmlspecialchars($user['name']); ?></td>
                    <td><?= htmlspecialchars($user['email']); ?></td>
                    <td><?= htmlspecialchars($user['role']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Gestiune Plante (Ștergere)</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nume Popular</th>
                <th>Nume Științific</th>
                <th>Statut</th>
                <th>Acțiuni</th>
            </tr>
            <?php foreach ($plants as $plant): ?>
                <tr>
                    <td><?= htmlspecialchars($plant['id']); ?></td>
                    <td><?= htmlspecialchars($plant['common_name']); ?></td>
                    <td><?= htmlspecialchars($plant['scientific_name']); ?></td>
                    <td><?= htmlspecialchars($plant['status']); ?></td>
                    <td>
                        <form action="api/delete_plant.php" method="POST"
                            onsubmit="return confirm('Ești sigur că vrei să ștergi această plantă?');">
                            <input type="hidden" name="plant_id" value="<?= htmlspecialchars($plant['id']); ?>">
                            <button type="submit" class="btn-delete">Șterge</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <script>
        document.getElementById('formImport').addEventListener('submit', function (e) {
            // 1. Oprim reîncărcarea paginii
            e.preventDefault();

            const fileInput = document.getElementById('importFile');
            const importType = document.getElementById('importType').value;

            // 2. Pregătim datele pentru trimitere (inclusiv fișierul)
            const formData = new FormData();
            formData.append('import_file', fileInput.files[0]);

            // 3. Stabilim către ce script PHP trimitem datele
            let endpoint = '';
            if (importType === 'csv') endpoint = 'api/import_plants_csv.php';
            if (importType === 'json') endpoint = 'api/import_plants_json.php';
            if (importType === 'xml') endpoint = 'api/import_plants_xml.php';

            // 4. Trimitem cererea asincronă
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Eroare de comunicare cu serverul.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('Succes: ' + data.message);
                        // Reîncărcăm pagina ca să vezi imediat noile plante în tabel
                        window.location.reload();
                    } else {
                        alert('Eroare: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Eroare:', error);
                    alert('A apărut o problemă la import.');
                });
        });
    </script>

</body>

</html>