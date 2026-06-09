# Ierbar Virtual

Specificații, instrucțiuni și descriere pentru proiectul "Ierbar Virtual" (colecție digitală de plante).

## Descriere

O aplicație web PHP/MySQL pentru gestionarea unei colecții de plante: autentificare, CRUD plante, încărcare media, import/export (CSV/JSON/XML) și panou administrativ.

## Cuprins

- Funcționalități
- Cerințe de sistem
- Instalare
- Utilizare
- Structura proiectului
- Contribuții
- Referințe

## Funcționalități

- Autentificare / înregistrare
- Adăugare / editare / ștergere plante
- Vizualizare detalii plantă + media
- Import / Export: CSV, JSON, XML
- Panou administrare (gestionare utilizatori și plante)

## Cerințe de sistem

- PHP 7.4+ cu extensia PDO
- MySQL / MariaDB
- Server web (Apache / Nginx)
- Permisiuni de scriere pentru folderul `uploads/`

## Instalare (local)

1. Clonați repo-ul în directorul serverului web.
2. Importați schema SQL din `database/dih_db.sql` în MySQL.
3. Actualizați setările DB în `database/database.php` dacă este necesar.
4. Asigurați permisiuni de scriere pentru `uploads/`.

## Rulare

Accesați aplicația în browser la URL-ul serverului local (ex: `http://localhost/Proiect_web_git_prefinal`).

## Structura proiectului (selectiv)

- `api/` — endpoint-uri PHP (login, CRUD, import/export)
- `css/`, `js/` — resurse statice
- `database/` — conexiune și SQL
- `uploads/` — media încărcată
- `raport.html` — SRS (Scholarly HTML)

## Contribuții

Deschideți un issue sau trimiteți un pull request. Pentru modificări majore, deschideți mai întâi o discuție.

## Referințe

- SRS model: IEEE SRS template
- Scholarly HTML: W3C Scholarly HTML
