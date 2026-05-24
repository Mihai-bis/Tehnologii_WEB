<?php
// ─── Endpoint căutare live AJAX (min 2 caractere) ───
// Acest fișier este apelat din funcția performSearch din main.js pe măsură ce utilizatorul tastează
// în câmpul de căutare. Returnează maxim 10 rezultate care conțin titlul căutat,
// ordonate descrescător după numărul de vizualizări.
require_once __DIR__ . '/../includes/functions.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    jsonResponse(false, 'Query too short');
}

try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id, title, slug, cover_image, status, type, rating 
        FROM anime 
        WHERE title LIKE ? 
        ORDER BY views DESC 
        LIMIT 10
    ");
    $stmt->execute(["%{$query}%"]);
    $results = $stmt->fetchAll();
    
    jsonResponse(true, '', $results);
} catch (PDOException $e) {
    jsonResponse(false, 'Search failed');
}
