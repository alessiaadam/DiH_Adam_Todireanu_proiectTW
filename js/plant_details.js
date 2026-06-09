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

//asteptam ca DOM-ul sa fie incarcat inainte de a rula scriptul
document.addEventListener('DOMContentLoaded', () => {
    const plantId = new URLSearchParams(window.location.search).get('id');

    if (!plantId) {
        showError('ID plantă lipsă. Redirectare...');
        // Asteptam 1.5 secunde inainte de redirectare pentru a lasa utilizatorului sa citeasca mesajul
        setTimeout(() => { window.location.href = 'dashboard.php'; }, 1500);
        return;
    }

    loadPlantDetails(plantId);
});

function loadPlantDetails(plantId) {
    const container = document.getElementById('plantDetails');
    showLoading(container); //afisam mesaj de incarcare cat asteptam raspunsul de la server

    // Fetch pentru a obtine detaliile plantei
    fetch(`api/get_plant.php?id=${encodeURIComponent(plantId)}`)
        .then(response => {
            // Daca raspunsul este 401 Unauthorized, redirectionam utilizatorul catre pagina de login
            if (response.status === 401) {
                window.location.href = 'login.html';
                return null;
            }
            return response.json();
        })
        .then(data => {
            if (!data) return;

            if (data.error) {
                showError(`Nu s-au putut încărca datele: ${data.error}`);
                return;
            }

            renderPage(data);
        })
        // Daca apare o eroare de retea sau alta exceptie, afisam un mesaj de eroare
        .catch(error => showError(`Eroare de rețea: ${error.message}`));
}


function renderPage(data) {
    //extragem datele din raspunsul JSON
    const { plant, media, characteristics, related_plants, can_edit, current_user } = data;

    // Construim titlul paginii
    document.title = `${escapeHTML(plant.common_name)} — Ierbar Digital`;

    // Actualizare header cu datele utilizatorului curent
    const usernameSpan = document.getElementById('headerUsername');
    if (usernameSpan) usernameSpan.textContent = `Salut, ${current_user.username}!`;

    //butonul admin — vizibil doar pentru admini
    const adminLink = document.getElementById('adminLink');
    if (adminLink) adminLink.style.display = current_user.role === 'admin' ? 'inline-block' : 'none';

    const container = document.getElementById('plantDetails');

    const editButton = can_edit
        ? `<a href="edit_plant.php?id=${plant.id}" class="edit-button">Editează Planta</a>`
        : '';

    //pregatim informatiile sub forma de label - valoare
    const infoFields = [
        { label: 'Nume științific', value: plant.scientific_name },
        { label: 'Origine', value: plant.origin },
        { label: 'Statut', value: plant.status },
        { label: 'Metodă de înmulțire', value: plant.propagation_method },
        { label: 'Descriere', value: plant.description },
    ];

    //contruim codul html pentru fiecare camp
    const infoHTML = infoFields
        .map(({ label, value }) => `
            <p><strong>${label}:</strong> ${escapeHTML(value) || '-'}</p>
        `)
        .join('');

    // Construim pagina cu toate informatiile
    container.innerHTML = `
        <a href="dashboard.php" class="back-button">Înapoi la colecție</a>
        ${editButton}

        <h2>Detalii Plantă</h2>

        ${renderMedia(media, plant.common_name)}

        <h3>${escapeHTML(plant.common_name) || '-'}</h3>
        ${infoHTML}

        ${renderCharacteristics(characteristics)}
        ${related_plants?.length ? renderRelatedSpecies(related_plants) : ''}
    `;
}

// Componenta pentru afisarea de imagini si videouri
function renderMedia(mediaList, plantName) {
    if (!mediaList?.length) {
        return `
            <div class="plant-detail-image-container">
                <img src="https://placehold.co/800x400/e0e0e0/666666?text=Fara+Poza"
                     alt="Fără imagine"
                     class="plant-image-large placeholder">
            </div>`;
    }

    const itemsHTML = mediaList.map(item => {
        if (item.type === 'video') {
            return `<video controls><source src="${item.file_path}"></video>`;
        }
        return `
            <img src="${item.file_path}"
                 alt="${escapeHTML(plantName) || 'Imagine plantă'}"
                 class="plant-image-large">`;
    }).join('');

    return `<div class="plant-detail-image-container">${itemsHTML}</div>`;
}

// Componenta pentru afisarea caracteristicilor
function renderCharacteristics(characteristics) {
    const categories = Object.keys(characteristics ?? {});

    if (!categories.length) {
        return `
            <div class="plant-characteristics">
                <h4>Caracteristici</h4>
                <p><i>Nu există caracteristici specifice adăugate pentru această plantă.</i></p>
            </div>`;
    }

    // Se creeaza liste pentru fiecare categorie, iar caracteristicile sunt afisate sub forma de badge-uri
    const categoriesHTML = categories.map(category => {
        const badgesHTML = characteristics[category]
            .map(name => `<span class="char-badge">${escapeHTML(name)}</span>`)
            .join('');

        return `<li><strong>${escapeHTML(category)}:</strong> ${badgesHTML}</li>`;
    }).join('');

    return `
        <div class="plant-characteristics">
            <h4>Caracteristici</h4>
            <ul>${categoriesHTML}</ul>
        </div>`;
}

// Componenta pentru afisarea speciilor inrudite
function renderRelatedSpecies(relatedPlants) {
    const cardsHTML = relatedPlants.map(({ id, common_name, file_path }) => {
        const imgSrc = file_path
            ?? 'https://placehold.co/150x150/e0e0e0/666666?text=Fara+Poza';

        return `
            <a href="plant_details.php?id=${encodeURIComponent(id)}" class="related-plant-card">
                <img src="${imgSrc}" alt="${escapeHTML(common_name) || ''}">
                <strong>${escapeHTML(common_name) || '-'}</strong>
            </a>`;
    }).join('');

    return `
        <div class="related-species-section">
            <h3>Specii Înrudite</h3>
            <div class="related-species-grid">${cardsHTML}</div>
        </div>`;
}


function showLoading(container) {
    container.innerHTML = `
        <div class="loading-state">
            <p>Se încarcă detaliile plantei...</p>
        </div>`;
}

function showError(message) {
    const container = document.getElementById('plantDetails');
    if (!container) return;
    container.innerHTML = `
        <div class="error-state">
            <a href="dashboard.php" class="back-button">Înapoi la colecție</a>
            <p>${escapeHTML(message)}</p>
        </div>`;
}