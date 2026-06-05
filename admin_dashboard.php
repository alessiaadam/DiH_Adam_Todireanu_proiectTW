<?php
require_once 'api/check_auth.php';
require_once 'database/database.php';
require_once 'api/fetch_admin_data.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Panou Administrare - Ierbar Virtual</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f9; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        h2 { border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .btn-delete { background-color: #f44336; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn-delete:hover { background-color: #d32f2f; }
    </style>
</head>
<body>

<div class="container">
    <h1>Panou de Administrare</h1>
    <a href="add_plant.php" style="display:inline-block; margin-bottom: 20px;">+ Adaugă o plantă nouă</a>

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
            <td><?php echo htmlspecialchars($user['id']); ?></td>
            <td><?php echo htmlspecialchars($user['name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['role']); ?></td>
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
            <td><?php echo htmlspecialchars($plant['id']); ?></td>
            <td><?php echo htmlspecialchars($plant['common_name']); ?></td>
            <td><?php echo htmlspecialchars($plant['scientific_name']); ?></td>
            <td><?php echo htmlspecialchars($plant['status']); ?></td>
            <td>
                <form action="api/delete_plant.php" method="POST" onsubmit="return confirm('Ești sigur că vrei să ștergi această plantă?');">
                    <input type="hidden" name="plant_id" value="<?php echo htmlspecialchars($plant['id']); ?>">
                    <button type="submit" class="btn-delete">Șterge</button>
                </form>
            </td>>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>