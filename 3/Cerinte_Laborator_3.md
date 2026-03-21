# Laborator 3: HTTP, Cookies și Sesiuni

## Tehnologii folosite

- HTML5
- CSS3
- JavaScript

---

## Partea 1: Analiza Protocolului HTTP

### 1.1 Întrebări Teoretice (răspundeți în scris)

1. Care sunt cele 4 metode HTTP principale și când se folosește fiecare?
2. Ce semnifică codurile de status: 200, 301, 400, 401, 403, 404, 500?
3. Care este diferența între HTTP și HTTPS?

### 1.2 Exercițiu Practic - Analiza HTTP cu Developer Tools

**Pași:**
1. Deschideți browser-ul (Chrome/Firefox) și accesați `https://httpbin.org`
2. Deschideți Developer Tools (F12) → Tab "Network"
3. Navigați pe site și analizați cererile HTTP

**Cerințe:**
- Identificați și documentați un exemplu de cerere GET
- Notați header-ele: `User-Agent`, `Accept`, `Accept-Language`, `Host`
- Identificați header-ele de răspuns: `Content-Type`, `Server`, `Date`
- Faceți screenshot-uri cu cererile analizate

### 1.3 Testarea Metodelor HTTP cu Fetch API

Deschideți consola browser-ului (F12 → Console) și testați următoarele cereri:

```javascript
// Testați GET
fetch('https://httpbin.org/get')
    .then(response => response.json())
    .then(data => console.log('GET:', data));

// Testați POST cu date JSON
fetch('https://httpbin.org/post', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nume: 'NumeleVostru', laborator: 3 })
})
    .then(response => response.json())
    .then(data => console.log('POST:', data));
```

**Livrabil:** Screenshot-uri cu rezultatele din consolă.

---

## Partea 2: Implementare Cookies și Storage

### 2.1 Structura Proiectului

Creați următoarea structură de fișiere:

```
laborator3/
├── index.html
├── preferences.html
├── cookies-info.html
├── js/
│   ├── cookies.js      (cod furnizat mai jos)
│   └── storage.js      (cod furnizat mai jos)
└── style.css
```

### 2.2 Cod Furnizat - js/cookies.js

```javascript
/**
 * Cookie Manager - Funcții utilitare pentru gestionarea cookie-urilor
 * ACEST COD VĂ ESTE FURNIZAT - NU TREBUIE MODIFICAT
 */
const CookieManager = {
    /**
     * Setează un cookie
     * @param {string} name - Numele cookie-ului
     * @param {string} value - Valoarea cookie-ului
     * @param {number} days - Numărul de zile până la expirare
     * @param {string} path - Calea (default: '/')
     */
    set: function(name, value, days = 365, path = '/') {
        let expires = '';
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=' + path;
        console.log(`Cookie setat: ${name}=${value}`);
    },

    /**
     * Obține valoarea unui cookie
     * @param {string} name - Numele cookie-ului
     * @returns {string|null} - Valoarea cookie-ului sau null
     */
    get: function(name) {
        const nameEQ = name + '=';
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i].trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return decodeURIComponent(cookie.substring(nameEQ.length));
            }
        }
        return null;
    },

    /**
     * Șterge un cookie
     * @param {string} name - Numele cookie-ului
     */
    delete: function(name) {
        this.set(name, '', -1);
        console.log(`Cookie șters: ${name}`);
    },

    /**
     * Obține toate cookie-urile ca obiect
     * @returns {Object} - Obiect cu toate cookie-urile
     */
    getAll: function() {
        const cookies = {};
        if (document.cookie) {
            document.cookie.split(';').forEach(cookie => {
                const [name, value] = cookie.trim().split('=');
                if (name) {
                    cookies[name] = decodeURIComponent(value || '');
                }
            });
        }
        return cookies;
    },

    /**
     * Șterge toate cookie-urile
     */
    deleteAll: function() {
        const cookies = this.getAll();
        for (const name in cookies) {
            this.delete(name);
        }
        console.log('Toate cookie-urile au fost șterse');
    },

    /**
     * Verifică dacă un cookie există
     * @param {string} name - Numele cookie-ului
     * @returns {boolean}
     */
    exists: function(name) {
        return this.get(name) !== null;
    }
};

// Export pentru utilizare globală
window.CookieManager = CookieManager;
```

### 2.3 Cod Furnizat - js/storage.js

```javascript
/**
 * Storage Manager - Gestionare localStorage și sessionStorage
 * ACEST COD VĂ ESTE FURNIZAT - NU TREBUIE MODIFICAT
 */
const StorageManager = {
    /**
     * Salvează date în localStorage (persistente)
     */
    setLocal: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('Eroare localStorage:', e);
            return false;
        }
    },

    /**
     * Obține date din localStorage
     */
    getLocal: function(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            return localStorage.getItem(key);
        }
    },

    /**
     * Șterge din localStorage
     */
    removeLocal: function(key) {
        localStorage.removeItem(key);
    },

    /**
     * Salvează date în sessionStorage (per tab/sesiune)
     */
    setSession: function(key, value) {
        try {
            sessionStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('Eroare sessionStorage:', e);
            return false;
        }
    },

    /**
     * Obține date din sessionStorage
     */
    getSession: function(key) {
        try {
            const item = sessionStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            return sessionStorage.getItem(key);
        }
    },

    /**
     * Șterge din sessionStorage
     */
    removeSession: function(key) {
        sessionStorage.removeItem(key);
    },

    /**
     * Golește tot sessionStorage
     */
    clearSession: function() {
        sessionStorage.clear();
    },

    /**
     * Golește tot localStorage
     */
    clearLocal: function() {
        localStorage.clear();
    },

    /**
     * Obține toate datele din localStorage
     */
    getAllLocal: function() {
        const data = {};
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            data[key] = this.getLocal(key);
        }
        return data;
    },

    /**
     * Obține toate datele din sessionStorage
     */
    getAllSession: function() {
        const data = {};
        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);
            data[key] = this.getSession(key);
        }
        return data;
    }
};

// Export pentru utilizare globală
window.StorageManager = StorageManager;
```

### 2.4 Cerință: Pagina Principală - index.html

Creați pagina `index.html` care:

1. **Afișează un mesaj de bun venit** cu numele utilizatorului (citit din cookie `username`, default: "Vizitator")
2. **Numără și afișează vizitele** utilizatorului (salvate în cookie `visits`)
3. **Aplică tema** salvată de utilizator (cookie `theme`: "light" sau "dark")
4. **Conține un meniu de navigare** cu link-uri către:
   - Setări Preferințe (preferences.html)
   - Vizualizare Cookies (cookies-info.html)
   - Autentificare (login.html)
5. **Afișează un tabel** cu toate cookie-urile active
6. **Afișează un tabel** cu datele din localStorage
7. **Afișează un tabel** cu datele din sessionStorage
8. **Buton "Șterge Tot"** - șterge toate cookies și storage-urile

**Indicații:**
- Includeți scripturile `cookies.js` și `storage.js`
- Folosiți `CookieManager.get()`, `CookieManager.set()`, `CookieManager.getAll()`
- Folosiți `StorageManager.getAllLocal()`, `StorageManager.getAllSession()`
- Pentru temă, adăugați clasa `light` sau `dark` pe `<body>`

### 2.5 Cerință: Pagina Preferințe - preferences.html

Creați pagina `preferences.html` cu un formular care permite setarea:

1. **Nume utilizator** (text input) → salvat în cookie `username`
2. **Tema** (select: Luminos/Întunecat) → salvat în cookie `theme`
3. **Limba** (select: Română/English/Français) → salvat în localStorage
4. **Dimensiune font** (select: 14/16/18/20px) → salvat în localStorage

**Comportament:**
- La încărcarea paginii, formularele trebuie pre-populare cu valorile salvate
- La submit, salvați preferințele și afișați un mesaj de succes
- După 1-2 secunde, redirecționați către index.html
- Aplicați tema curentă pe pagină

### 2.6 Cerință: Pagina Informații - cookies-info.html

Creați pagina `cookies-info.html` care afișează:

1. **Secțiune Cookies:**
   - Valoarea brută a `document.cookie`
   - Tabel cu: Nume, Valoare, Lungime, Buton Șterge

2. **Secțiune localStorage:**
   - JSON formatat cu toate datele
   - Tabel cu: Cheie, Valoare, Dimensiune, Buton Șterge

3. **Secțiune sessionStorage:**
   - JSON formatat cu toate datele
   - Tabel cu: Cheie, Valoare, Dimensiune, Buton Șterge

4. **Tabel comparativ** între Cookies, localStorage și sessionStorage cu:
   - Capacitate
   - Expirare
   - Trimis la server?
   - Accesibil din?

---

## Partea 3: Sistem de Autentificare

### 3.1 Structura Adițională

Adăugați în proiect:

```
laborator3/
├── login.html
├── register.html
├── dashboard.html
└── cart.html
```

### 3.2 Cerință: Pagina Login - login.html

Creați pagina `login.html` cu:

1. **Formular de autentificare:**
   - Input pentru username
   - Input pentru parolă
   - Checkbox "Ține-mă minte"
   - Buton Submit

2. **Logică de autentificare:**
   - Citiți lista de utilizatori din `localStorage` (cheie: `users`)
   - Verificați dacă username și parola se potrivesc
   - Dacă "Ține-mă minte" e bifat, salvați username-ul în localStorage
   - La autentificare reușită:
     - Creați o sesiune în `sessionStorage` cu: userId, username, email, loginTime, sessionId (generat random)
     - Redirecționați către dashboard.html
   - La eroare, afișați un mesaj corespunzător

3. **Utilizatori de test:**
   - La prima încărcare, dacă nu există utilizatori în localStorage, creați-i:
     ```javascript
     const defaultUsers = [
         { id: 1, username: 'admin', password: 'password', email: 'admin@example.com' },
         { id: 2, username: 'student', password: 'student123', email: 'student@example.com' }
     ];
     ```

4. **Link către** pagina de înregistrare

### 3.3 Cerință: Pagina Register - register.html

Creați pagina `register.html` cu:

1. **Formular de înregistrare:**
   - Username (minim 3 caractere)
   - Email (validare format)
   - Parolă (minim 6 caractere)
   - Confirmare parolă

2. **Validări:**
   - Toate câmpurile obligatorii
   - Username unic (verificați în lista existentă)
   - Email unic
   - Parolele trebuie să coincidă

3. **La succes:**
   - Adăugați utilizatorul nou în array-ul din localStorage
   - Afișați mesaj de succes
   - Redirecționați către login.html

### 3.4 Cerință: Dashboard - dashboard.html

Creați pagina `dashboard.html` care:

1. **Verifică autentificarea:**
   - Dacă nu există sesiune în sessionStorage, redirecționează către login.html

2. **Afișează informații despre sesiune:**
   - ID Sesiune
   - Username
   - Email
   - Ora autentificării
   - Durata sesiunii (actualizată în timp real la fiecare secundă)

3. **Afișează datele brute** din sessionStorage (pentru debug)

4. **Conține:**
   - Link către Coș de Cumpărături (cart.html)
   - Buton Deconectare (șterge sesiunea și redirecționează)

5. **Experiment propus:**
   - Link care deschide pagina într-un tab nou
   - Observați că sessionStorage este diferit pentru fiecare tab!

### 3.5 Cerință: Coș de Cumpărături - cart.html

Creați pagina `cart.html` cu:

1. **Verificare autentificare** (ca la dashboard)

2. **Lista de produse disponibile** (hard-coded):
   ```javascript
   const products = {
       laptop: { name: 'Laptop', price: 2500 },
       telefon: { name: 'Telefon', price: 1200 },
       tableta: { name: 'Tabletă', price: 800 },
       casti: { name: 'Căști', price: 150 }
   };
   ```

3. **Formular pentru adăugare:**
   - Select cu produsele
   - Input pentru cantitate (1-10)
   - Buton "Adaugă în Coș"

4. **Afișare coș:**
   - Tabel cu: Produs, Cantitate, Preț, Buton Șterge
   - Calculare și afișare TOTAL
   - Buton "Golește Coșul"

5. **Stocare:**
   - Coșul se salvează în `sessionStorage` (cheie: `cart`)
   - Format: `{ laptop: 2, telefon: 1, ... }`

---

## Partea 4: Stilizare CSS

Creați fișierul `style.css` care să includă:

1. **Stiluri de bază:**
   - Font family: Segoe UI sau sans-serif
   - Container centrat cu max-width 900px
   - Padding și margini corespunzătoare

2. **Stiluri pentru formulare:**
   - Inputs și select-uri cu padding și border-radius
   - Butoane cu hover effects
   - Labels cu font-weight bold

3. **Stiluri pentru tabele:**
   - Header colorat
   - Hover pe rânduri
   - Border-collapse

4. **Două teme:**
   - `.light` - fundal deschis, text închis
   - `.dark` - fundal închis, text deschis

5. **Clase utilitare:**
   - `.alert-success` - verde
   - `.alert-error` - roșu
   - `.btn-danger` - roșu pentru butoane de ștergere

---

## Instrucțiuni de Rulare

1. Creați structura de foldere și fișierele
2. Copiați codul furnizat în `js/cookies.js` și `js/storage.js`
3. Implementați celelalte fișiere conform cerințelor
4. Deschideți `index.html` în browser (dublu-click)

**SAU** folosiți Live Server în VS Code:
- Instalați extensia "Live Server"
- Click dreapta pe `index.html` → "Open with Live Server"

---

## Resurse Utile

- [MDN - Document.cookie](https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie)
- [MDN - Web Storage API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Storage_API)
- [MDN - Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)
- [MDN - HTTP Overview](https://developer.mozilla.org/en-US/docs/Web/HTTP/Overview)


---

**Succes!**
