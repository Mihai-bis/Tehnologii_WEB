<?php
require_once __DIR__ . '/../config.php';

// ============================================================
// FUNCȚIE PENTRU OBTINEREA CONEXIUNII LA BAZA DE DATE
// Folosește pattern-ul Singleton: creează conexiunea PDO doar la
// primul apel și o păstrează în variabila statică $db pentru
// apelurile ulterioare. Acest lucru evită conexiuni multiple
// inutile și îmbunătățește performanța aplicației.
//
// Setări PDO importante:
// - ERRMODE_EXCEPTION: aruncă excepții la erori SQL
// - FETCH_ASSOC: returnează rezultatele ca array asociativ
// - EMULATE_PREPARES false: folosește prepared statements native
//   pentru o securitate mai bună împotriva SQL Injection
// ============================================================
/**
 * Get database connection
 * @return PDO
 */
function getDB() {
    static $db = null;
    
    if ($db === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $db = new PDO($dsn, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    return $db;
}
