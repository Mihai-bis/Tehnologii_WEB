<?php
// Include funcțiile comune ale aplicației
require_once __DIR__ . '/includes/functions.php';

// ─── Verificare login ───
// Pagina de favorite este accesibilă doar utilizatorilor autentificați.
// Dacă nu este logat, utilizatorul este redirecționat către auth.php.
if (!isLoggedIn()) {
    setFlash('error', 'Please login to view your favorites.');
    redirect(SITE_URL . '/auth.php');
}

$db = getDB();

// ─── Interogare favorite cu genuri ───
// Preia din baza de date lista de anime-uri favorite ale utilizatorului logat,
// împreună cu genurile asociate (concatenate într-un singur string).
// Rezultatele sunt grupate după anime și ordonate descrescător.
$stmt = $db->prepare("
    SELECT a.*, GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR ', ') as genres
    FROM favorites f 
    JOIN anime a ON f.anime_id = a.id 
    LEFT JOIN anime_genres ag ON a.id = ag.anime_id
    LEFT JOIN genres g ON ag.genre_id = g.id
    WHERE f.user_id = ? 
    GROUP BY a.id
    ORDER BY MAX(f.created_at) DESC
");
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll();

$pageTitle = 'My Favorites';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 40px;">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-heart"></i> My Favorites
            <span style="color: var(--text-muted); font-size: 0.9rem; margin-left: 8px;">(<?php echo count($favorites); ?> anime)</span>
        </h2>
    </div>
    
    <!-- ─── Afișare grid sau mesaj gol ─── -->
    <!-- Dacă utilizatorul are favorite, afișează un grid de carduri anime;
         altfel, afișează un mesaj informativ cu un link către pagina principală. -->
    <?php if (!empty($favorites)): ?>
    <div class="anime-grid">
        <?php foreach ($favorites as $anime): ?>
        <a href="anime.php?slug=<?php echo $anime['slug']; ?>" class="anime-card">
            <div class="anime-card-img-wrapper">
                <img src="assets/images/<?php echo $anime['cover_image']; ?>" alt="<?php echo $anime['title']; ?>" class="anime-card-img" onerror="this.src='assets/images/default-cover.jpg'">
                <div class="anime-card-overlay">
                    <div class="anime-card-play"><i class="fas fa-play"></i></div>
                </div>
                <span class="anime-card-badge <?php echo $anime['status']; ?>"><?php echo $anime['status']; ?></span>
                <span class="anime-card-episodes"><?php echo $anime['episodes_count']; ?> EP</span>
            </div>
            <div class="anime-card-info">
                <h3 class="anime-card-title"><?php echo $anime['title']; ?></h3>
                <div class="anime-card-meta">
                    <span><?php echo $anime['type']; ?></span>
                    <span class="anime-card-rating"><i class="fas fa-star"></i> <?php echo $anime['rating']; ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-heart"></i>
        <h3>No favorites yet</h3>
        <p>Browse anime and add them to your favorites!</p>
        <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-primary" style="margin-top: 16px;">
            <i class="fas fa-search"></i> Browse Anime
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>