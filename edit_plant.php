<?php
require_once 'api/check_auth.php';
require_once 'database/database.php';

$plant_id = $_GET['id'] ?? null;
if (!$plant_id) {
    header("Location: dashboard.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM plants WHERE id = ?");
    $stmt->execute([$plant_id]);
    $plant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plant) {
        header("Location: dashboard.php");
        exit();
    }

    $current_user_id = $_SESSION['user_id'] ?? null;
    $current_user_role = $_SESSION['user_role'] ?? 'user';
    if ($plant['user_id'] != $current_user_id && $current_user_role !== 'admin') {
        header("Location: dashboard.php");
        exit();
    }

    $stmt_chars = $pdo->prepare("SELECT characteristic_id FROM plant_characteristics WHERE plant_id = ?");
    $stmt_chars->execute([$plant_id]);
    $saved_chars = $stmt_chars->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Editează Planta - Ierbar Virtual</title>
    <link rel="stylesheet" href="css/add_plant.css">
</head>
<body>
    
<div class="form-container">
    <h2>Editează Detaliile Plantei</h2>
    <form id="editPlantForm">    
        <input type="hidden" name="plant_id" value="<?= $plant['id']; ?>">

        <label for="common_name">Denumire Populară:</label>
        <input type="text" id="common_name" name="common_name" required value="<?= htmlspecialchars($plant['common_name']); ?>">

        <label for="scientific_name">Denumire Științifică:</label>
        <input type="text" id="scientific_name" name="scientific_name" required value="<?= htmlspecialchars($plant['scientific_name']); ?>">

        <label for="description">Descriere:</label>
        <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($plant['description']); ?></textarea>

        <label for="origin">Origine:</label>
        <input type="text" id="origin" name="origin" value="<?= htmlspecialchars($plant['origin']); ?>">

        <label for="status">Statut:</label>
        <select id="status" name="status" required>
            <option value="Comună" <?= $plant['status'] == 'Comună' ? 'selected' : ''; ?>>Comună</option>
            <option value="Vulnerabilă" <?= $plant['status'] == 'Vulnerabilă' ? 'selected' : ''; ?>>Vulnerabilă</option>
            <option value="Pe cale de dispariție" <?= $plant['status'] == 'Pe cale de dispariție' ? 'selected' : ''; ?>>Pe cale de dispariție</option>
            <option value="Rară" <?= $plant['status'] == 'Rară' ? 'selected' : ''; ?>>Rară</option>
            <option value="Protejată de lege" <?= $plant['status'] == 'Protejată de lege' ? 'selected' : ''; ?>>Protejată de lege</option>
            <option value="Invazivă" <?= $plant['status'] == 'Invazivă' ? 'selected' : ''; ?>>Invazivă</option>
        </select>

        <label for="propagation_method">Metodă de înmulțire:</label>
        <input type="text" id="propagation_method" name="propagation_method" value="<?= htmlspecialchars($plant['propagation_method']); ?>">

        <label><b>Caracteristici:</b></label>
        <div class="checkbox-group" style="background: #fdfdfd; padding: 15px; border: 1px solid #eee; border-radius: 5px;">
            
            <h4 style="margin-top: 0; color: #4CAF50;">☀️ Necesarul de Lumină</h4>
            <label><input type="checkbox" name="characteristics[]" value="1" <?= in_array(1, $saved_chars) ? 'checked' : ''; ?>> Iubitoare de soare</label><br>
            <label><input type="checkbox" name="characteristics[]" value="2" <?= in_array(2, $saved_chars) ? 'checked' : ''; ?>> Semiumbră</label><br>
            <label><input type="checkbox" name="characteristics[]" value="3" <?= in_array(3, $saved_chars) ? 'checked' : ''; ?>> Iubitoare de umbră</label>
            
            <h4 style="color: #4CAF50;">💧 Necesarul de Apă</h4>
            <label><input type="checkbox" name="characteristics[]" value="4" <?= in_array(4, $saved_chars) ? 'checked' : ''; ?>> Rezistentă la secetă</label><br>
            <label><input type="checkbox" name="characteristics[]" value="18" <?= in_array(18, $saved_chars) ? 'checked' : ''; ?>> Moderat</label><br>
            <label><input type="checkbox" name="characteristics[]" value="5" <?= in_array(5, $saved_chars) ? 'checked' : ''; ?>> Iubitoare de umezeală</label><br>
            <label><input type="checkbox" name="characteristics[]" value="6" <?= in_array(6, $saved_chars) ? 'checked' : ''; ?>> Plantă acvatică</label>
            
            <h4 style="color: #4CAF50;">⏳ Ciclul de Viață</h4>
            <label><input type="checkbox" name="characteristics[]" value="7" <?= in_array(7, $saved_chars) ? 'checked' : ''; ?>> Anuală</label><br>
            <label><input type="checkbox" name="characteristics[]" value="8" <?= in_array(8, $saved_chars) ? 'checked' : ''; ?>> Perenă</label>
            
            <h4 style="color: #4CAF50;">🧪 Proprietăți și Utilizări</h4>
            <label><input type="checkbox" name="characteristics[]" value="9" <?= in_array(9, $saved_chars) ? 'checked' : ''; ?>> Medicinală</label><br>
            <label><input type="checkbox" name="characteristics[]" value="10" <?= in_array(10, $saved_chars) ? 'checked' : ''; ?>> Comestibilă</label><br>
            <label><input type="checkbox" name="characteristics[]" value="11" <?= in_array(11, $saved_chars) ? 'checked' : ''; ?>> Toxică / Otrăvitoare</label><br>
            <label><input type="checkbox" name="characteristics[]" value="12" <?= in_array(12, $saved_chars) ? 'checked' : ''; ?>> Meliferă</label><br>
            <label><input type="checkbox" name="characteristics[]" value="13" <?= in_array(13, $saved_chars) ? 'checked' : ''; ?>> Aromatică</label><br>
            <label><input type="checkbox" name="characteristics[]" value="14" <?= in_array(14, $saved_chars) ? 'checked' : ''; ?>> Purifică aerul</label>
            
            <h4 style="color: #4CAF50;">🌱 Tipul de creștere</h4>
            <label><input type="checkbox" name="characteristics[]" value="19" <?= in_array(19, $saved_chars) ? 'checked' : ''; ?>> Arbust</label><br>
            <label><input type="checkbox" name="characteristics[]" value="15" <?= in_array(15, $saved_chars) ? 'checked' : ''; ?>> Cățărătoare / Liană</label><br>
            <label><input type="checkbox" name="characteristics[]" value="16" <?= in_array(16, $saved_chars) ? 'checked' : ''; ?>> Târâtoare</label><br>
            <label><input type="checkbox" name="characteristics[]" value="17" <?= in_array(17, $saved_chars) ? 'checked' : ''; ?>> Suculentă</label>

        </div>
        <label for="plant_image"><b>Schimbă imaginea (opțional):</b></label>
        <input type="file" id="plant_image" name="plant_image" accept="image/*">

        <button type="submit">Salvează Modificările</button>
    </form>
</div>

<script>
document.getElementById('editPlantForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    fetch('api/process_edit_plant.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            alert(data.message);
            window.location.href = 'dashboard.php'; 
        } else {
            alert('Eroare: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Eroare:', error);
        alert('Eroare de conexiune la server.');
    });
});
</script>
</body>
</html>