<?php
require_once __DIR__ . '/db.php';

// Curăță datele primite de la utilizator pentru a preveni atacurile XSS
// prin eliminarea tag-urilor HTML și conversia caracterelor speciale
/**
 * Clean user input to prevent XSS attacks
 * @param string $data
 * @return string
 */
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Generează un token aleatoriu criptografic sigur folosit pentru
// CSRF token, nume de fișiere unice sau alte scopuri de securitate
/**
 * Generate a secure random token
 * @param int $length
 * @return string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Transformă un titlu sau text într-un slug URL-friendly:
// litere mici, înlocuire caractere speciale cu cratime, fără spații
/**
 * Generate a slug from a string
 * @param string $string
 * @return string
 */
function generateSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Verifică dacă utilizatorul este autentificat verificând
// existența și validitatea ID-ului de utilizator din sesiune
/**

 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

// Verifică dacă utilizatorul autentificat are rolul de administrator
/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Preia din baza de date datele complete ale utilizatorului curent
// autentificat (pentru afișare în header, profil etc.)
/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email, avatar, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Redirecționează browserul către o altă pagină și oprește
// imediat execuția scriptului pentru a evita procesarea inutilă
/**
 * Redirect to a URL
 * @param string $url
 * @return void
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Stochează în sesiune un mesaj temporar (succes, eroare etc.)
// care va fi afișat o singură dată utilizatorului după redirect
/**
 * Set a flash message
 * @param string $type (success, error, warning, info)
 * @param string $message
 * @return void
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Preia mesajul flash din sesiune și îl șterge imediat,
// astfel încât să nu fie afișat din nou la următoarea încărcare
/**
 * Get and clear flash message
 * @return array|null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Generează sau returnează tokenul CSRF din sesiune;
// acesta protejează formularile împotriva atacurilor Cross-Site Request Forgery
/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

// Validează tokenul CSRF primit din formular comparându-l în mod
// sigur (hash_equals) cu cel stocat în sesiune pentru a preveni CSRF
/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Gestionează încărcarea imaginilor pe server cu validări de securitate:
// verifică dimensiunea, tipul MIME real (via finfo), extensia fișierului
// și generează un nume unic pentru a evita conflictele sau suprascrierile
/**
 * Upload image file
 * @param array $file (from $_FILES)
 * @param string $subdir
 * @return string|false
 */
function uploadImage($file, $subdir = '') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return false;
    }
    
    // Verify MIME type with finfo (not trust client-provided type)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    // Whitelist extension
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        return false;
    }
    
    $uploadDir = UPLOAD_PATH . ($subdir ? $subdir . '/' : '');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = generateToken(16) . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ($subdir ? $subdir . '/' : '') . $filename;
    }
    
    return false;
}

// Preia din baza de date un anime specific după ID numeric sau slug,
// împreună cu genurile asociate (concatenate într-un singur câmp)
/**
 * Get anime by ID or slug
 * @param string|int $identifier
 * @return array|null
 */
function getAnime($identifier) {
    $db = getDB();
    $field = is_numeric($identifier) ? 'id' : 'slug';
    $stmt = $db->prepare("SELECT a.*, 
        GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR ', ') as genres,
        GROUP_CONCAT(g.slug ORDER BY g.name SEPARATOR ',') as genre_slugs
        FROM anime a 
        LEFT JOIN anime_genres ag ON a.id = ag.anime_id 
        LEFT JOIN genres g ON ag.genre_id = g.id 
        WHERE a.{$field} = ?
        GROUP BY a.id");
    $stmt->execute([$identifier]);
    return $stmt->fetch();
}

// Returnează lista de episoade pentru un anime dat, ordonate
// crescător după numărul episodului pentru afișare secvențială
/**
 * Get episodes for an anime
 * @param int $animeId
 * @return array
 */
function getEpisodes($animeId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM episodes WHERE anime_id = ? ORDER BY episode_number ASC");
    $stmt->execute([$animeId]);
    return $stmt->fetchAll();
}

// Preia lista tuturor genurilor din baza de date cu un mecanism
// simplu de cache în sesiune (5 minute) pentru a reduce interogările repetitive
/**
 * Get all genres
 * @return array
 */
function getGenres() {
    $cacheKey = 'genres_cache';
    $cacheTime = 300; // 5 minutes
    
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time']) && (time() - $_SESSION[$cacheKey . '_time']) < $cacheTime) {
        return $_SESSION[$cacheKey];
    }
    
    $db = getDB();
    $stmt = $db->query("SELECT * FROM genres ORDER BY name ASC");
    $genres = $stmt->fetchAll();
    
    $_SESSION[$cacheKey] = $genres;
    $_SESSION[$cacheKey . '_time'] = time();
    
    return $genres;
}

// Verifică dacă un anumit anime se află în lista de favorite
// a utilizatorului autentificat (folosit pentru iconița de inimă)
/**
 * Check if anime is in user's favorites
 * @param int $userId
 * @param int $animeId
 * @return bool
 */
function isFavorite($userId, $animeId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND anime_id = ?");
    $stmt->execute([$userId, $animeId]);
    return $stmt->fetch() !== false;
}

// Construiește și returnează o listă paginată de anime cu suport
// pentru filtre (status, tip, gen) și căutare text; include și numărul
// total de pagini necesar pentru navigarea prin rezultate
/**
 * Get paginated anime list
 * @param int $page
 * @param int $perPage
 * @param string $status
 * @param string $type
 * @param string $genre
 * @param string $search
 * @return array
 */
function getAnimeList($page = 1, $perPage = 24, $status = '', $type = '', $genre = '', $search = '') {
    $db = getDB();
    
    $where = [];
    $params = [];
    
    if ($status) {
        $where[] = "a.status = ?";
        $params[] = $status;
    }
    
    if ($type) {
        $where[] = "a.type = ?";
        $params[] = $type;
    }
    
    if ($search) {
        $where[] = "(a.title LIKE ? OR a.description LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    if ($genre) {
        $where[] = "EXISTS (SELECT 1 FROM anime_genres ag2 JOIN genres g2 ON ag2.genre_id = g2.id WHERE ag2.anime_id = a.id AND g2.slug = ?)";
        $params[] = $genre;
    }
    
    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(DISTINCT a.id) as total FROM anime a {$whereClause}");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT a.*, 
        GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR ', ') as genres
        FROM anime a 
        LEFT JOIN anime_genres ag ON a.id = ag.anime_id 
        LEFT JOIN genres g ON ag.genre_id = g.id 
        {$whereClause}
        GROUP BY a.id
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $anime = $stmt->fetchAll();
    
    return [
        'data' => $anime,
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'current_page' => $page
    ];
}

// Formatează o dată din formatul bazei de date într-un format
// lizibil pentru utilizator (ex: "Jan 15, 2024")
/**
 * Format date
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Trunchiază un text la o lungime maximă dată, adăugând "..."
// la final; util pentru previzualizări de descrieri în carduri
/**
 * Truncate text
 * @param string $text
 * @param int $length
 * @return string
 */
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Returnează un răspuns JSON și termină execuția scriptului;
// folosit de endpoint-urile API pentru cereri AJAX asincrone
/**
 * Return JSON response for AJAX requests
 * @param bool $success
 * @param string $message
 * @param array $data
 * @return void
 */
function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Înregistrează o activitate a utilizatorului în log-ul de erori
// al serverului (simplu, poate fi extins cu tabel dedicat în BD)
/**
 * Log user activity
 * @param int $userId
 * @param string $action
 * @param string $details
 * @return void
 */
function logActivity($userId, $action, $details = '') {
    // Simple activity logging - can be expanded
    error_log("[USER {$userId}] {$action}: {$details}");
}
