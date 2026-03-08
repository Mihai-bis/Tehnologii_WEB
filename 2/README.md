# Campus Info Hub

Un portal informativ pentru resursele campusului universitarii.

## Întrebări

### 1. Ce este o resursă (resource) în aplicația ta?

O **resursă** reprezintă un spațiu sau serviciu disponibil pe campus, cum ar fi Biblioteca Centrală, Cantina Studențească, Sala de Studiu 24/7 sau Sala de Evenimente. Fiecare resursă are atribute precum nume, tip, locație, program de funcționare și etichete (tags) pentru categorizare.

### 2. Da exemplu de un URI și explică componentele acestuia.

**Exemplu:** `https://example.com/pages/library.html#schedule`

- **Scheme:** `https` – protocolul de comunicare
- **Host:** `example.com` – domeniul serverului
- **Path:** `/pages/library.html` – calea către fișierul HTML
- **Fragment:** `#schedule` – identificator care indică secțiunea cu programul din pagină

### 3. Care părți sunt statice și care sunt dinamice?

**Statice:**
- Structura HTML a paginilor (index.html, library.html, cafeteria.html, events.html)
- Fișierul resources.json (conținutul este fix, dar este încărcat dinamic)
- CSS-ul și layout-ul

**Dinamice:**
- Lista de resurse pe pagina principală (generată prin JavaScript cu fetch din JSON)
- Rezultatele filtrării (doar locuri de studiu / toate)
- Afișarea tag-urilor și categoriilor (extrase din JSON la rulare)

### 4. Este aplicația ta document-centric sau interactivă (sau ambele)? De ce?

Aplicația este **ambele**:

- **Document-centric:** Paginile Biblioteca, Cantina și Evenimente sunt documente statice, cu conținut fix. Utilizatorul navighează între ele ca între documente informative.

- **Interactivă:** Pe pagina principală utilizatorul poate filtra resursele (ex. doar spații de studiu), iar conținutul se actualizează dinamic. Lista de resurse și tag-urile sunt generate prin JavaScript după încărcarea JSON-ului.
