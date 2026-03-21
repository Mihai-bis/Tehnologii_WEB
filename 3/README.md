# Laborator 3: HTTP, Cookies și Sesiuni

## Partea 1.1 - Întrebări Teoretice

### 1. Care sunt cele 4 metode HTTP principale și când se folosește fiecare?

- **GET** – Se folosește pentru a solicita date de la un server. Este idempotentă și nu ar trebui să modifice resursele pe server. Utilizare: vizualizare pagini web, obținere date din API.
- **POST** – Se folosește pentru a trimite date către server, de obicei pentru crearea de resurse noi. Utilizare: trimitere formulare, înregistrare utilizatori, adăugare conținut.
- **PUT** – Se folosește pentru a actualiza complet o resursă existentă pe server. Utilizare: modificare resurse identificate prin URI.
- **DELETE** – Se folosește pentru a șterge o resursă de pe server. Utilizare: ștergere date, eliminare resurse.

### 2. Ce semnifică codurile de status: 200, 301, 400, 401, 403, 404, 500?

- **200 OK** – Cererea a reușit. Serverul a returnat datele solicitate cu succes.
- **301 Moved Permanently** – Resursa a fost mutată permanent la o nouă adresă URL. Browser-ul va face redirect automat.
- **400 Bad Request** – Cererea este invalidă sau malformată. Clientul a trimis date incorecte.
- **401 Unauthorized** – Autentificare necesară. Utilizatorul trebuie să se logheze pentru a accesa resursa.
- **403 Forbidden** – Acces interzis. Utilizatorul este autentificat dar nu are permisiuni pentru resursă.
- **404 Not Found** – Resursa solicitată nu a fost găsită pe server.
- **500 Internal Server Error** – Eroare internă pe server. Ceva nu a funcționat corect pe partea serverului.

### 3. Care este diferența între HTTP și HTTPS?

- **HTTP (HyperText Transfer Protocol)** – Protocol necriptat care transmite date în clar pe rețea. Orice poate intercepta traficul poate citi datele. Utilizează portul 80.

- **HTTPS (HTTP Secure)** – Este HTTP securizat prin TLS/SSL. Criptează datele între client și server, asigurând confidențialitate, integritate și autentificarea serverului. Utilizează portul 443. Certificatele digitale verifică identitatea serverului și protejează împotriva atacurilor man-in-the-middle.

**În rezumat:** HTTPS = HTTP + criptare + autentificare.

---

## Partea 1.2 și 1.3 - Exerciții Practice

Pentru exercițiile practice (Analiza HTTP cu Developer Tools și Testarea cu Fetch API), trebuie:
- Deschideți https://httpbin.org în browser
- Folosiți Developer Tools (F12) → Tab "Network" pentru analiză
- Faceți screenshot-uri cu cererile analizate și rezultatele din consolă

---


## Structura Proiectului

```
Laborator 3 - HTTP, Cookies și Sesiuni/
├── README.md
├── Cerinte_Laborator_3.md
└── laborator3/
    ├── index.html
    ├── preferences.html
    ├── cookies-info.html
    ├── login.html
    ├── register.html
    ├── dashboard.html
    ├── cart.html
    ├── style.css
    └── js/
        ├── cookies.js
        └── storage.js
```

## Credențiale de test

- **Admin:** username: `admin`, parolă: `password`
- **Student:** username: `student`, parolă: `student123`
