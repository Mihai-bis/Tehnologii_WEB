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
