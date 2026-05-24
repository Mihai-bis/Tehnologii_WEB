# AnimeList

O platformă web pentru streaming anime, dezvoltată în PHP procedural cu MySQL. Proiectul permite utilizatorilor să vizioneze anime, să adauge recenzii, să gestioneze favorite și să urmărească istoricul vizionărilor. Panoul de administrare permite gestionarea conținutului (anime, episoade, genuri).

---

## Tehnologii folosite

- **Backend:** PHP 7.4+ (procedural)
- **Bază de date:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (vanilla)
- **Stilizare:** Font Awesome 6.5.1, Google Fonts (Inter)
- **Securitate:** PDO prepared statements, CSRF tokens, XSS protection, password hashing (bcrypt)

---

## Harta proiectului

### Rădăcină — Fișiere pagini principale

| Fișier | Ce conține | La ce se folosește |
|--------|-----------|-------------------|
| `index.php` | Pagina principală | Afișează hero carousel cu top 6 anime, grid paginat de anime, filtre (status, tip, gen, căutare) și paginare. Este punctul de intrare al aplicației. |
| `auth.php` | Pagină combinată login / register | Gestionează autentificarea și înregistrarea utilizatorilor. Include validare server-side, hashing parole și comutare între tab-uri login/register. |
| `anime.php` | Pagină detalii anime | Afișează informații complete despre un anime (banner, cover, metadata, synopsis), lista de episoade, recenzii utilizatori și buton de favorite. Parametru: `?slug=` |
| `watch.php` | Pagină player video | Player HTML5 pentru vizionarea episoadelor, navigație prev/next, sidebar cu lista episoadelor și înregistrare în istoricul vizionărilor. Parametri: `?anime=&ep=` |
| `profile.php` | Pagină profil utilizator | Afișează datele utilizatorului logat, statisticile (favorite, vizionate, recenzii), selector de avatar predefinit, istoric vizionare și recenzii personale. |
| `favorites.php` | Pagină favorite | Grid cu anime-urile adăugate la favorite de utilizatorul logat. Necesită autentificare. |
| `search.php` | Pagină rezultate căutare | Afișează rezultatele căutării după query. Redirectează pe homepage dacă query-ul este gol. |
| `admin.php` | Panou administrare | Dashboard admin cu statistici (anime, useri, episoade, favorite), tabel paginat de anime, modaluri pentru adăugare și editare anime cu upload de imagini. Necesită rol admin. |
| `config.php` | Configurație aplicație | Definește constantele esențiale: credențiale DB, URL site, căi upload, setări cookie/sesiune, fus orar și raportare erori. |
| `database.sql` | Schema bazei de date | Conține comenzile SQL pentru crearea bazei de date `animelist`, a tabelelor, a relațiilor (FK, CASCADE) și a datelor inițiale (genuri, anime demo, admin default). |

### `includes/` — Componente partajate

| Fișier | Ce conține | La ce se folosește |
|--------|-----------|-------------------|
| `includes/db.php` | Conexiune la bază de date | Factory Singleton pentru conexiunea PDO. Este inclus de toate fișierele care accesează baza de date. Configurează UTF-8, mod excepții și fetch asociativ. |
| `includes/functions.php` | Bibliotecă de funcții utilitare | ~350 linii cu funcții esențiale folosite în tot proiectul: curățare input (`clean()`), generare token/CSRF/slug, verificare autentificare (`isLoggedIn()`, `isAdmin()`), preluare user curent, upload imagini securizat, interogări DB (getAnime, getEpisodes, getGenres, getAnimeList), flash messages, răspuns JSON. |
| `includes/header.php` | Header comun | Template HTML pentru începutul paginilor: `<head>` cu meta, CSS, Font Awesome, CSRF token, navbar (logo, linkuri nav, dropdown genuri, search bar live, user menu), mobile menu overlay și containerul principal `<main>`. |
| `includes/footer.php` | Footer comun | Template HTML pentru sfârșitul paginilor: footer în 4 coloane (brand, quick links, genuri, cont), copyright, încărcare `main.js` și închidere taguri HTML. |

### `api/` — Endpointuri AJAX

| Fișier | Ce conține | La ce se folosește |
|--------|-----------|-------------------|
| `api/add_review.php` | Adăugare / modificare recenzie | Primește POST cu `anime_id`, `rating`, `comment`. Inserează sau actualizează recenzia (UNIQUE pe user+anime) și recalculează ratingul mediu al anime-ului. Necesită autentificare + CSRF valid. |
| `api/delete_anime.php` | Ștergere anime | Primește POST cu `anime_id` și șterge anime-ul plus înregistrările asociate (genuri, favorite, recenzii, episoade) înainte de ștergerea principală. Necesită rol admin + CSRF valid. Returnează JSON. |
| `api/get_anime.php` | Preluare date anime | Primește GET cu `id` și returnează JSON cu toate datele anime-ului plus lista de `genre_ids` asociate. Folosit pentru popularea modalului de editare din admin. Necesită rol admin. |
| `api/logout.php` | Deconectare | Curăță sesiunea, șterge cookie-ul de sesiune, setează flash message și redirectează pe homepage. |
| `api/search_ajax.php` | Căutare live AJAX | Primește GET cu `q` (min. 2 caractere), caută în titluri de anime și returnează top 10 rezultate ordonate după views ca JSON. Folosit de search bar-ul din header. |
| `api/toggle_favorite.php` | Toggle favorite | Primește POST cu `anime_id`. Adaugă sau elimină anime-ul din lista de favorite a utilizatorului logat. Returnează JSON cu starea nouă (`is_favorite`). Necesită autentificare + CSRF valid. |

### `assets/` — Resurse media

| Folder | Ce conține | La ce se folosește |
|--------|-----------|-------------------|
| `assets/images/avatars/predefined/` | Avatare predefinite (PNG) | Imagini de profil pe care utilizatorii le pot selecta în pagina de profil. |
| `assets/images/banners/` | Bannere anime (JPG) | Imagini de fundal pentru hero carousel și pagina de detalii anime. |
| `assets/images/covers/` | Coperte anime (JPG) | Imagini de thumbnail pentru cardurile din griduri și paginile de detalii. |
| `assets/images/default-*.jpg` | Imagini default | Fallback-uri când un anime nu are imagine proprie. |

### `css/` și `js/` — Frontend

| Fișier | Ce conține | La ce se folosește |
|--------|-----------|-------------------|
| `css/style.css` | Foaie de stiluri principală | ~2200 linii cu design dark theme, layout responsive, stilizare carduri, modaluri, formular, tabel admin, animații și variabile CSS. |
| `js/main.js` | JavaScript principal | ~570 linii cu logica frontend: meniu mobil, căutare live AJAX, flash messages auto-hide, dropdowns, hero carousel (auto-play, touch, keyboard), toggle favorite, notificări toast, modaluri admin (edit/delete), validare formular, lazy load imagini. |

---

## Instrucțiuni de instalare

1. **Creează baza de date** în phpMyAdmin sau MySQL CLI:
   ```sql
   SOURCE calea/catre/anime-list/database.sql;
   ```

2. **Configurează conexiunea** în `config.php` dacă este necesar:
   - `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` (default: root / fără parolă / animelist)

3. **Asigură permisiunile** pentru folderul `assets/images/` (upload de imagini):
   ```bash
   chmod -R 755 assets/images/
   ```

4. **Accesează situl** în browser:
   ```
   http://localhost/anime-list/
   ```

---

## Funcționalități principale

- ✅ Vizualizare catalog anime cu filtre (status, tip, gen) și paginare
- ✅ Pagină detalii anime cu episoade, recenzii și anime similare
- ✅ Player video cu navigație între episoade și înregistrare istoric
- ✅ Sistem de autentificare (login / register) cu parole hash-uite
- ✅ Favorite — adăugare/eliminare anime din lista personală
- ✅ Recenzii — rating 1-10 + comentarii, cu recalculare automată a ratingului mediu
- ✅ Profil utilizator — avatar predefinit, statistici, istoric vizionare, recenzii proprii
- ✅ Căutare live AJAX în header (autocomplete)
- ✅ Panou administrare — CRUD anime cu upload imagini (cover + banner)
- ✅ Securitate — CSRF tokens, XSS protection, SQL injection protection, password hashing

---

## Cont default admin

| Username | Parolă | Rol |
|----------|--------|-----|
| `admin` | `admin123` | admin |

---

*Acesta este un proiect demo pentru scopuri educaționale.*
