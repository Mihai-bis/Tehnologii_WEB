# Campus Info Hub

Un site informativ pentru resurse din campus: bibliotecă, cantină, evenimente.

## 1. Ce este o resursă (resource) în aplicația ta?

O **resursă** este o entitate din campus care oferă un serviciu sau un spațiu studenților. Fiecare resursă are: nume, tip (studiu, dining, evenimente), locație, program de funcționare și tags (etichete). Exemple: Biblioteca Centrală, Cantina Studențească, Sala de Evenimente, Sala de Lectură, Cafeneaua Campus. Datele sunt stocate în `data/resources.json` și afișate dinamic pe pagina principală.

## 2. Exemplu de URI și componentele acestuia

**Exemplu:** `https://example.com/pages/library.html#schedule`

- **Scheme:** `https` – protocolul utilizat
- **Host:** `example.com` – domeniul serverului
- **Path:** `/pages/library.html` – calea către fișierul HTML al bibliotecii
- **Fragment:** `#schedule` – identificator care indică secțiunea „Program” din pagină; browserul sare direct la acea secțiune

## 3. Care părți sunt statice și care sunt dinamice?

**Statice:**
- Structura HTML a tuturor paginilor (`index.html`, `pages/library.html`, `pages/cafeteria.html`, `pages/events.html`)
- Fișierul CSS (`css/style.css`)
- Conținutul paginilor de resurse (Biblioteca, Cantina, Evenimente)
- Fișierul `data/resources.json` (datele în sine sunt statice, dar sunt încărcate dinamic)

**Dinamice:**
- Lista de resurse de pe pagina principală – generată cu JavaScript prin `fetch()` din JSON
- Filtrarea rezultatelor (ex: doar locuri de studiu)
- Afișarea tags/categoriilor – obținute din JSON și randate în pagină

## 4. Este aplicația document-centric sau interactive (sau ambele)? De ce?

Aplicația este **atât document-centrică, cât și interactivă**:

- **Document-centrică:** Paginile Biblioteca, Cantina și Evenimente sunt documente HTML cu informații fixe, ușor de citit și indexat.
- **Interactive:** Pagina principală folosește JavaScript pentru a încărca date din JSON, a afișa lista de resurse și a filtra după tip (ex: doar locuri de studiu). Utilizatorul poate apăsa butoane și interacționa cu conținutul.

## Structura proiectului

```
├── index.html
├── pages/
│   ├── library.html
│   ├── cafeteria.html
│   └── events.html
├── data/
│   └── resources.json
├── css/
│   └── style.css
├── js/
│   ├── main.js
│   └── nav.js
└── README.md
```

## Rulare locală

`fetch()` poate eșua la deschiderea directă a fișierelor (`file://`) din cauza CORS. Pentru a testa aplicația local, rulați un server HTTP, de ex.: `npx serve .` sau folosiți extensia Live Server din VS Code.

## URI-uri disponibile

- `/index.html` – pagina principală
- `/pages/library.html` – Biblioteca
- `/pages/cafeteria.html` – Cantina
- `/pages/events.html` – Evenimente
- `/data/resources.json` – date JSON
- `/pages/library.html#schedule` – fragment către secțiunea Program
