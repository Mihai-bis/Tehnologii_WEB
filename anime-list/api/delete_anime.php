<?php
// ─── Endpoint ștergere anime (admin only) + ștergere înregistrări asociate ───
// Acest fișier este apelat prin AJAX din panoul de administrare (confirmDeleteAnime).
// Doar administratorii pot executa ștergerea. Se validează CSRF, apoi se șterg explicit
// înregistrările asociate din tabelele anime_genres, favorites, reviews și episodes,
// după care se șterge anime-ul din tabela anime.
require_once __DIR__ . '/../includes/functions.php';

// Check if admin
if (!isAdmin()) {
    jsonResponse(false, 'Unauthorized');
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    jsonResponse(false, 'Invalid token');
}

$animeId = isset($_POST['anime_id']) ? intval($_POST['anime_id']) : 0;

if ($animeId <= 0) {
    jsonResponse(false, 'Invalid anime ID');
}

try {
    $db = getDB();
    
    // Database has ON DELETE CASCADE, but being explicit is fine
    $stmt = $db->prepare("DELETE FROM anime_genres WHERE anime_id = ?");
    $stmt->execute([$animeId]);
    
    $stmt = $db->prepare("DELETE FROM favorites WHERE anime_id = ?");
    $stmt->execute([$animeId]);
    
    $stmt = $db->prepare("DELETE FROM reviews WHERE anime_id = ?");
    $stmt->execute([$animeId]);
    
    $stmt = $db->prepare("DELETE FROM episodes WHERE anime_id = ?");
    $stmt->execute([$animeId]);
    
    $stmt = $db->prepare("DELETE FROM anime WHERE id = ?");
    $stmt->execute([$animeId]);
    
    jsonResponse(true, 'Anime deleted successfully');
} catch (PDOException $e) {
    jsonResponse(false, 'Database error');
}
