document.addEventListener('DOMContentLoaded', () => {
    const plantsContainer = document.getElementById('plantsContainer');
    const btnSearch = document.getElementById('btnSearch');

    function loadPlants() {
        const origin = document.getElementById('filterOrigin').value;
        const search = document.getElementById('searchName').value;

        const url = `api/get_plants.php?origin=${origin}&search=${search}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                plantsContainer.innerHTML = '';

                const plantsList = data.plants;
                const currentUserId = data.current_user_id;
                const currentUserRole = data.current_user_role;

                if (!plantsList || plantsList.length === 0) {
                    plantsContainer.innerHTML = '<p>Nu am găsit nicio plantă.</p>';
                    return;
                }

                plantsList.forEach(plant => {
                    const card = document.createElement('div');
                    card.className = 'plant-card';
                    const imagePath = plant.file_path ? plant.file_path : 'https://placehold.co/400x300/e0e0e0/666666?text=Fara+Poza';

                    let actionButtons = '';
                    if (plant.user_id == currentUserId || currentUserRole === 'admin') {
                        actionButtons = `
                            <button onclick="editPlant(${plant.id})" style="background-color: #f57c00; margin-top: 5px;">Editează</button>
                            <button onclick="deletePlant(${plant.id})" style="background-color: #d32f2f; margin-top: 5px;">Șterge</button>
                        `;
                    }

                    card.innerHTML = `
                        <img src="${imagePath}" alt="${plant.common_name}" class="plant-image">
                        <h4>${plant.common_name}</h4>
                        <p><i>${plant.scientific_name}</i></p>
                        <button onclick="viewDetails(${plant.id})">Vezi Detalii</button>
                        ${actionButtons}
                    `;

                    plantsContainer.appendChild(card);
                });
            })
            .catch(error => console.error('Eroare:', error));
    }
    
    loadPlants();
    btnSearch.addEventListener('click', loadPlants);

    window.deletePlant = function (plantId) {
        if (confirm("Ești sigur că vrei să ștergi această plantă?")) {
            fetch('api/delete_plant.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: plantId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert("Planta a fost ștearsă cu succes!");
                        document.getElementById('btnSearch').click();
                    } else {
                        alert("Eroare: " + data.message);
                    }
                })
                .catch(error => console.error("Eroare la ștergere:", error));
        }
    }

    window.viewDetails = function (plantId) {
        const url = `plant_details.php?id=${plantId}`;
        window.location.href = url;
    }

    window.editPlant = function (plantId) {
        window.location.href = `edit_plant.php?id=${plantId}`;
    }
});