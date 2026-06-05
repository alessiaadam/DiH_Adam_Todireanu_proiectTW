<?php
require_once 'api/check_auth.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Adaugă o Plantă - Ierbar Virtual</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f9; }
        .form-container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input[type="text"], textarea, select, input[type="file"] { width: 100%; padding: 10px; margin: 8px 0 20px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .checkbox-group { margin-bottom: 20px; }
        .checkbox-group label { display: inline-block; margin-right: 15px; cursor: pointer; }
        button { background-color: #4CAF50; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        button:hover { background-color: #45a049; }
        h2 { text-align: center; color: #333; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Adaugă o Plantă în Ierbar</h2>
    <form action="api/process_add_plant.php" method="POST" enctype="multipart/form-data">
        
        <label for="common_name">Denumire Populară:</label>
        <input type="text" id="common_name" name="common_name" required placeholder="ex. Trandafir">

        <label for="scientific_name">Denumire Științifică:</label>
        <input type="text" id="scientific_name" name="scientific_name" required placeholder="ex. Rosa rubiginosa">

        <label for="description">Descriere:</label>
        <textarea id="description" name="description" rows="4" required placeholder="Descrie planta..."></textarea>

        <label for="origin">Origine:</label>
        <input type="text" id="origin" name="origin" placeholder="ex. Europa">

        <label for="status">Statut:</label>
        <select id="status" name="status" required>
            <option value="" disabled selected>-- Alege statutul --</option>
            <option value="Comună">Comună</option>
            <option value="Vulnerabilă">Vulnerabilă</option>
            <option value="Pe cale de dispariție">Pe cale de dispariție</option>
            <option value="Rară">Rară</option>
            <option value="Protejată de lege">Protejată de lege</option>
            <option value="Invazivă">Invazivă</option>
        </select>

        <label for="propagation_method">Metodă de înmulțire:</label>
        <input type="text" id="propagation_method" name="propagation_method" placeholder="ex. Prin semințe, butași">

        <label><b>Caracteristici:</b></label>
        <div class="checkbox-group" style="background: #fdfdfd; padding: 15px; border: 1px solid #eee; border-radius: 5px;">
            
            <h4 style="margin-top: 0; color: #4CAF50;">☀️ Necesarul de Lumină</h4>
            <label><input type="checkbox" name="characteristics[]" value="1"> Iubitoare de soare</label><br>
            <label><input type="checkbox" name="characteristics[]" value="2"> Semiumbră</label><br>
            <label><input type="checkbox" name="characteristics[]" value="3"> Iubitoare de umbră</label>
            
            <h4 style="color: #4CAF50;">💧 Necesarul de Apă</h4>
            <label><input type="checkbox" name="characteristics[]" value="4"> Rezistentă la secetă</label><br>
            <label><input type="checkbox" name="characteristics[]" value="18"> Moderat</label><br>
            <label><input type="checkbox" name="characteristics[]" value="5"> Iubitoare de umezeală</label><br>
            <label><input type="checkbox" name="characteristics[]" value="6"> Plantă acvatică</label>
            
            <h4 style="color: #4CAF50;">⏳ Ciclul de Viață</h4>
            <label><input type="checkbox" name="characteristics[]" value="7"> Anuală</label><br>
            <label><input type="checkbox" name="characteristics[]" value="8"> Perenă</label>
            
            <h4 style="color: #4CAF50;">🧪 Proprietăți și Utilizări</h4>
            <label><input type="checkbox" name="characteristics[]" value="9"> Medicinală</label><br>
            <label><input type="checkbox" name="characteristics[]" value="10"> Comestibilă</label><br>
            <label><input type="checkbox" name="characteristics[]" value="11"> Toxică / Otrăvitoare</label><br>
            <label><input type="checkbox" name="characteristics[]" value="12"> Meliferă</label><br>
            <label><input type="checkbox" name="characteristics[]" value="13"> Aromatică</label><br>
            <label><input type="checkbox" name="characteristics[]" value="14"> Purifică aerul</label>
            
            <h4 style="color: #4CAF50;">🌱 Tipul de creștere</h4>
            <label><input type="checkbox" name="characteristics[]" value="19"> Arbust</label><br>
            <label><input type="checkbox" name="characteristics[]" value="15"> Cățărătoare / Liană</label><br>
            <label><input type="checkbox" name="characteristics[]" value="16"> Târâtoare</label><br>
            <label><input type="checkbox" name="characteristics[]" value="17"> Suculentă</label>

        </div>
        <label for="plant_image"><b>Încarcă o imagine reprezentativă:</b></label>
        <input type="file" id="plant_image" name="plant_image" accept="image/*" required>

        <button type="submit">Salvează Planta</button>
    </form>
</div>

</body>
</html>