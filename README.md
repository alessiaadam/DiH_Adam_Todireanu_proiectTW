# Ierbar Virtual

Aplicație web colaborativă PHP/MySQL pentru gestionarea unei colecții digitale de plante. Proiectul oferă autentificare, management CRUD pentru plante, upload media, filtre multi-criteriale, import/export de date în formate deschise și modul de administrare.

## Arhitectură și tehnologie

- Front-end: HTML5, CSS3, JavaScript (Fetch API, Ajax)
- Back-end: PHP 7.4+ / 8+, MySQL / MariaDB, PDO
- Stocare date: relațională cu tabele `users`, `plants`, `media`, `characteristics`, `plant_characteristics`, `related_species`
- Comunicarea client-server se face prin API-uri interne PHP apelate asincron
- Nu sunt folosite framework-uri front-end sau back-end

## Funcționalități principale

- autentificare și înregistrare utilizator
- gestionare roluri: utilizator normal și administrator
- adăugare, editare și ștergere plante
- afișare detalii plantă și galerie media (imagini/video)
- filtre multi-criteriale și căutare text în dashboard
- import și export date în formatele CSV, JSON și XML
- modul de administrare cu listă utilizatori și gestionare plante
- upload fișiere media în folderul `uploads/`
- asocieri între specii înrudite și caracteristici de plante

## API-uri și endpoint-uri

- `api/login_action.php` — autentificare
- `api/register_action.php` — înregistrare
- `api/logout.php` — deconectare
- `api/check_auth.php` — verificare sesiune autenticată
- `api/get_plants.php` — listare plante filtrate
- `api/process_add_plant.php` — adăugare plantă
- `api/process_edit_plant.php` — actualizare plantă
- `api/delete_plant.php` — ștergere plantă
- `api/export_plants_csv.php` — export CSV
- `api/export_plants_json.php` — export JSON
- `api/export_plants_xml.php` — export XML
- `api/import_plants_csv.php` — import CSV
- `api/import_plants_json.php` — import JSON
- `api/import_plants_xml.php` — import XML

## Instalație locală

1. Clonați repository-ul în folderul serverului local (Apache / Nginx).
2. Importați schemă SQL din `database/dih_db.sql` în MySQL / MariaDB.
3. Actualizați setările de conectare din `database/database.php`.
4. Asigurați permisiuni de scriere pentru directorul `uploads/`.
5. Deschideți `index.html` în browser și accesați aplicația.

## Utilizare

- `index.html` — pagină principală
- `login.html` / `register.html` — autentificare și cont
- `dashboard.php` — listare plante și filtre
- `add_plant.php` — formular nouă plantă
- `edit_plant.php` — editare plantă existentă
- `admin_dashboard.php` — panou administrare
- `raport.html` — documentație și SRS

## Respectarea cerințelor proiectului

- aplicație Web server-side PHP bazată pe servicii Web
- utilizare Ajax/Fetch pentru apeluri API asincrone
- interfață HTML/CSS validă și responsive
- import/export valori deschise: CSV, JSON, XML
- modul administrativ dedicat pentru controlul resurselor
- protecție minimă SQL injection prin PDO prepared statements
- protecție minimă XSS prin `htmlspecialchars()` la afișarea datelor

## Limitări cunoscute și precizări export/import

- **Export/import CSV**: acoperă doar datele de bază ale plantelor (fără media, caracteristici, relații). Se recomandă pentru backup simplificat.
- **Export/import JSON și XML**: includ structura completă a ierbarului cu plante, media, caracteristici și relații între specii. Se recomandă pentru backup integral și transfer complet de date.
- Nu există încă un mecanism complet de traducere multilingvă pentru descrieri.

## Structura proiectului

- `api/` — endpoint-uri și servicii Web PHP
- `css/` — stiluri CSS
- `js/` — scripturi JavaScript client-side
- `database/` — conexiune DB și schemă SQL
- `uploads/` — fișiere media încărcate
- `raport.html` — specificație Scholarly HTML

## Referințe

- IEEE SRS template
- W3C Scholarly HTML
- cerințele proiectului disciplinei Tehnologii Web
