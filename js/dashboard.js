//Functie pentru a preveni atacurile XSS
function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>'"]/g, function (tag) { //
        const charsToReplace = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        };
        return charsToReplace[tag] || tag;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const plantsContainer = document.getElementById('plantsContainer');
    const btnSearch = document.getElementById('btnSearch');

    // Functia principala pentru a incarca plantele in functie de filtre
    function loadPlants() {
        const origin = document.getElementById('filterOrigin').value;
        const soil = document.getElementById('filterSoil').value;
        const search = document.getElementById('searchName').value;

        // Construim un array pentru caracteristicile selectate
        let selectedChars = [];
        document.querySelectorAll('.char-filter:checked').forEach(checkbox => {
            selectedChars.push(checkbox.value);
        });

        let charString = '';
        if (selectedChars.length > 0) {
            charString = selectedChars.join(',');
        }

        //Construim URL-ul pentru fetch cu parametrii de filtrare
        let url = `api/get_plants.php?origin=${encodeURIComponent(origin)}&soil=${encodeURIComponent(soil)}&search=${encodeURIComponent(search)}`;
        if (charString) {
            url += `&characteristics=${encodeURIComponent(charString)}`;
        }

        plantsContainer.innerHTML = '<p>Se încarcă...</p>';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                plantsContainer.innerHTML = '';

                if (data.error) {
                    console.error("Eroare PHP:", data.error);
                    plantsContainer.innerHTML = '<p>A apărut o eroare la încărcarea datelor.</p>';
                    return;
                }

                // Verificam daca data este un array sau un obiect care contine un array de plante
                let plants = [];
                if (Array.isArray(data)) {
                    plants = data;
                } else {
                    if (data.plants) {
                        plants = data.plants;
                    } else {
                        plants = [];
                    }
                }

                if (plants.length === 0) {
                    plantsContainer.innerHTML = '<p>Nu am găsit nicio plantă care să corespundă filtrelor.</p>';
                    return;
                }

                plants.forEach(plant => {
                    const card = document.createElement('div');
                    card.className = 'plant-card';
                    const imagePath = plant.file_path ? plant.file_path : 'https://placehold.co/400x300/e0e0e0/666666?text=Fara+Poza';

                    let actionButtons = '';

                    const currentUserId = data.current_user_id;
                    const currentUserRole = data.current_user_role;

                    // Daca utilizatorul curent este proprietarul plantei sau are rol de admin, afisam butoanele de editare si stergere
                    if (plant.user_id == currentUserId || currentUserRole === 'admin') {
                        actionButtons = `
                            <button onclick="editPlant(${plant.id})" style="margin-top: 5px;">Editează</button> 
                            <button onclick="deletePlant(${plant.id})" style="background-color: #d32f2f; margin-top: 5px;">Șterge</button>
                        `;
                    }

                    card.innerHTML = `
                        <img src="${imagePath}" alt="${plant.common_name}" class="plant-image">
                        <h4>${escapeHTML(plant.common_name)}</h4>
                        <p><i>${escapeHTML(plant.scientific_name)}</i></p>
                        <button onclick="viewDetails(${plant.id})">Vezi Detalii</button>
                        ${actionButtons}
                    `;

                    plantsContainer.appendChild(card);
                });
            })
            .catch(error => console.error('Eroare de rețea/JSON:', error));
    }

    loadPlants();

    btnSearch.addEventListener('click', loadPlants);
    document.getElementById('filterOrigin').addEventListener('keyup', loadPlants); //se executa cautarea in timpul tastarii
    document.getElementById('filterSoil').addEventListener('change', loadPlants);
    document.getElementById('searchName').addEventListener('keyup', loadPlants);

    //se adauga event listener pentru fiecare checkbox de caracteristici pentru a recalcula plantele automat
    document.querySelectorAll('.char-filter').forEach(checkbox => {
        checkbox.addEventListener('change', loadPlants);
    });

    // Functia pentru a sterge o planta, apelata din butonul de stergere
    window.deletePlant = function (plantId) {
        if (confirm("Ești sigur că vrei să ștergi această plantă?")) {
            fetch('api/delete_plant.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ plant_id: plantId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert("Planta a fost ștearsă cu succes!");
                        loadPlants();
                    } else {
                        alert("Eroare: " + data.message);
                    }
                })
                .catch(error => console.error("Eroare la ștergere:", error));
        }
    }

    // Functia pentru a vedea detaliile unei plante
    window.viewDetails = function (plantId) {
        const url = `plant_details.php?id=${plantId}`;
        window.location.href = url;
    }

    // Functia pentru a edita o planta
    window.editPlant = function (plantId) {
        window.location.href = `edit_plant.php?id=${plantId}`;
    }
});