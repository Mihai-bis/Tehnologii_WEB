<?php
// AnimeList Configuration

// ============================================================
// CONSTANTE PENTRU CONEXIUNEA LA BAZA DE DATE (MySQL)
// Stochează datele de autentificare necesare conectării PDO
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'animelist');

// ============================================================
// CONSTANTE PENTRU CONFIGURAREA SITE-ULUI
// Detectează automat URL-ul de bază pentru a funcționa
// corect indiferent de host sau subdosar unde e găzduit
// ============================================================
define('SITE_NAME', 'AnimeList');
// Auto-detect base URL to work on any host/subfolder
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
$configDir = str_replace('\\', '/', __DIR__);
$relativePath = '';
if (strpos($configDir, $docRoot) === 0) {
    $relativePath = substr($configDir, strlen($docRoot));
    $relativePath = rtrim($relativePath, '/');
}
define('SITE_URL', $protocol . '://' . $host . $relativePath);
define('ADMIN_EMAIL', 'admin@animelist.com');

// ============================================================
// CONSTANTE PENTRU ÎNCĂRCAREA FIȘIERELOR (upload)
// Definim calea fizică pe server și URL-ul public pentru imagini
// ============================================================
define('UPLOAD_PATH', __DIR__ . '/assets/images/');
define('UPLOAD_URL', SITE_URL . '/assets/images/');

// ============================================================
// CONSTANTE PENTRU VALIDAREA FIȘIERELOR ÎNCĂRCATE
// Limităm dimensiunea maximă și tipurile MIME acceptate
// pentru a preveni încărcarea de fișiere malițioase
// ============================================================
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// ============================================================
// CONFIGURAREA SESIUNII ȘI A COOKIE-URILOR
// Setăm parametrii de securitate: durata de 30 de zile,
// httponly pentru a preveni accesul JavaScript la cookie,
// și samesite Lax pentru protecție CSRF de bază
// ============================================================
// Session and cookie settings
session_set_cookie_params([
    'lifetime' => 86400 * 30, // 30 days
    'path' => '/',
    'secure' => false, // Set to true in production with HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// ============================================================
// PORNIREA SESIUNII
// Inițializăm sesiunea PHP doar dacă nu este deja activă,
// pentru a evita erorile la includeri multiple ale fișierului
// ============================================================
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// FUS ORAR
// Setăm fusul orar pe București pentru ca toate funcțiile
// de dată/oră să returneze informații corecte pentru utilizator
// ============================================================
// Set timezone
date_default_timezone_set('Europe/Bucharest');

// ============================================================
// RAPORTAREA ERORILOR
// Activăm afișarea tuturor erorilor pentru depanare în dezvoltare;
// în producție, aceste linii trebuie dezactivate sau logate în fișier
// ============================================================
// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
