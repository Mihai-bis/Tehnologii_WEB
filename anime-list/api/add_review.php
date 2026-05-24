<?php
// ─── Endpoint pentru adăugare/modificare recenzie + recalculare rating anime ───
// Acest fișier este apelat când un utilizator logat trimite o recenzie pentru un anime.
// Inserează recenzia în tabela reviews sau o actualizează dacă există deja (ON DUPLICATE KEY UPDATE),
// apoi recalculează și actualizează rating-ul mediu al anime-ului în tabela anime.
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlash('error', 'Please login to add a review.');
    redirect(SITE_URL . '/auth.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect(SITE_URL . '/index.php');
    }
    
    $animeId = intval($_POST['anime_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = clean($_POST['comment'] ?? '');
    
    if ($animeId <= 0 || $rating < 1 || $rating > 10 || empty($comment)) {
        setFlash('error', 'Please fill in all fields correctly.');
        redirect(SITE_URL . '/anime.php?id=' . $animeId);
    }
    
    try {
        $db = getDB();
        
        // Insert or update review
        $stmt = $db->prepare("
            INSERT INTO reviews (user_id, anime_id, rating, comment) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = ?, comment = ?, updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$_SESSION['user_id'], $animeId, $rating, $comment, $rating, $comment]);
        
        // Update anime rating
        $stmt = $db->prepare("
            UPDATE anime SET rating = (SELECT AVG(rating) FROM reviews WHERE anime_id = ?) WHERE id = ?
        ");
        $stmt->execute([$animeId, $animeId]);
        
        setFlash('success', 'Review submitted successfully!');
        
        // Get anime slug for redirect
        $stmt = $db->prepare("SELECT slug FROM anime WHERE id = ?");
        $stmt->execute([$animeId]);
        $anime = $stmt->fetch();
        
        redirect(SITE_URL . '/anime.php?slug=' . ($anime ? $anime['slug'] : ''));
        
    } catch (PDOException $e) {
        setFlash('error', 'An error occurred. Please try again.');
        redirect(SITE_URL . '/index.php');
    }
} else {
    redirect(SITE_URL . '/index.php');
}
