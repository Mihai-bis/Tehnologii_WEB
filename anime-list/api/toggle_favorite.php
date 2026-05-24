<?php
// ─── Endpoint toggle favorite (add/remove) pentru utilizator logat ───
// Acest fișier este apelat prin AJAX din funcția toggleFavorite din main.js.
// Verifică dacă utilizatorul este autentificat și validează CSRF.
// Dacă anime-ul este deja favorit, îl șterge din tabela favorites;
// altfel, îl adaugă. Returnează starea nouă (is_favorite) pentru actualizarea UI.
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Please login to add favorites');
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    jsonResponse(false, 'Invalid token');
}

$animeId = isset($_POST['anime_id']) ? intval($_POST['anime_id']) : 0;
$userId = $_SESSION['user_id'];

if ($animeId <= 0) {
    jsonResponse(false, 'Invalid anime ID');
}

try {
    $db = getDB();
    
    // Check if already in favorites
    $stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND anime_id = ?");
    $stmt->execute([$userId, $animeId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Remove from favorites
        $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND anime_id = ?");
        $stmt->execute([$userId, $animeId]);
        jsonResponse(true, 'Removed from favorites', ['is_favorite' => false]);
    } else {
        // Add to favorites
        $stmt = $db->prepare("INSERT INTO favorites (user_id, anime_id) VALUES (?, ?)");
        $stmt->execute([$userId, $animeId]);
        jsonResponse(true, 'Added to favorites', ['is_favorite' => true]);
    }
} catch (PDOException $e) {
    jsonResponse(false, 'Database error');
}
