<?php
// ─── Endpoint fetch anime by ID pentru populare modal edit ───
// Acest fișier este apelat prin AJAX (openEditModal) pentru a prelua datele complete
// ale unui anime după ID, inclusiv lista de genre_ids asociate, necesare pentru
// a bifa genurile corecte în formularul de editare din modalul adminului.
require_once __DIR__ . '/../includes/functions.php';

// Check if admin
if (!isAdmin()) {
    jsonResponse(false, 'Unauthorized');
}

$animeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($animeId <= 0) {
    jsonResponse(false, 'Invalid anime ID');
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM anime WHERE id = ?");
    $stmt->execute([$animeId]);
    $anime = $stmt->fetch();
    
    if ($anime) {
        $genreStmt = $db->prepare("SELECT genre_id FROM anime_genres WHERE anime_id = ?");
        $genreStmt->execute([$animeId]);
        $genreIds = $genreStmt->fetchAll(PDO::FETCH_COLUMN);
        $anime['genre_ids'] = $genreIds;
        
        jsonResponse(true, '', $anime);
    } else {
        jsonResponse(false, 'Anime not found');
    }
} catch (PDOException $e) {
    jsonResponse(false, 'Database error');
}
