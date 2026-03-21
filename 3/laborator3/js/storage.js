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
