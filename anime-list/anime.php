<?php
// Include funcțiile comune necesare pentru baza de date, sesiune și utilitare
require_once __DIR__ . '/includes/functions.php';

// Preluăm slug-ul anime-ului din URL; este folosit pentru a identifica unic anime-ul în baza de date
$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

// Validare: dacă slug-ul lipsește, afișăm eroare și redirecționăm către pagina principală
if (empty($slug)) {
    setFlash('error', 'Anime not found.');
    redirect(SITE_URL . '/index.php');
}

// Obținem datele anime-ului după slug; folosit în tot conținutul paginii (banner, info, episoade, recenzii)
$anime = getAnime($slug);

// Dacă anime-ul nu există în baza de date, redirecționăm utilizatorul către home
if (!$anime) {
    setFlash('error', 'Anime not found.');
    redirect(SITE_URL . '/index.php');
}

// Obținem lista de episoade asociate acestui anime; afișate în secțiunea de episoade și folosite în linkuri către watch.php
$episodes = getEpisodes($anime['id']);

// Obținem ultimele 10 recenzii împreună cu informațiile despre utilizatorii care le-au scris; afișate în secțiunea de recenzii
$db = getDB();
$stmt = $db->prepare("
    SELECT r.*, u.username, u.avatar 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.anime_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 10
");
$stmt->execute([$anime['id']]);
$reviews = $stmt->fetchAll();

// Verificăm dacă anime-ul se află în lista de favorite a utilizatorului logat; folosit pentru a schimba starea butonului de favorite
$isFav = false;
if (isLoggedIn()) {
    $isFav = isFavorite($_SESSION['user_id'], $anime['id']);
}

// Obținem anime-uri similare care au același prim gen; afișate în secțiunea "You May Also Like" de la finalul paginii
$genreSlugs = explode(',', $anime['genre_slugs']);
$relatedAnime = [];
if (!empty($genreSlugs[0])) {
    $stmt = $db->prepare("
        SELECT DISTINCT a.* FROM anime a 
        JOIN anime_genres ag ON a.id = ag.anime_id 
        JOIN genres g ON ag.genre_id = g.id 
        WHERE g.slug = ? AND a.id != ? 
        LIMIT 6
    ");
    $stmt->execute([$genreSlugs[0], $anime['id']]);
    $relatedAnime = $stmt->fetchAll();
}

$pageTitle = $anime['title'];
$pageDescription = truncate($anime['description'], 150);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner-ul anime-ului: imagine de fundal mare afișată în partea de sus a paginii anime.php -->
<div class="anime-banner">
    <img src="assets/images/<?php echo $anime['banner_image']; ?>" alt="<?php echo $anime['title']; ?>" class="anime-banner-img" onerror="this.src='assets/images/default-banner.jpg'">
    <div class="anime-banner-overlay"></div>
</div>

<!-- Conținutul principal al paginii anime.php: poster, informații, butoane de acțiune și sinopsis -->
<div class="container anime-detail-content">
    <div class="anime-detail-grid">
        <!-- Poster -->
        <div class="anime-poster">
            <img src="assets/images/<?php echo $anime['cover_image']; ?>" alt="<?php echo $anime['title']; ?>" onerror="this.src='assets/images/default-cover.jpg'">
        </div>
        
        <!-- Informații detaliate despre anime: titlu, rating, tip, status, an, episoade, genuri și sinopsis -->
        <div class="anime-detail-info">
            <h1 class="anime-detail-title"><?php echo $anime['title']; ?></h1>
            
            <div class="anime-detail-meta">
                <span class="rating"><i class="fas fa-star"></i> <?php echo $anime['rating']; ?></span>
                <span><i class="fas fa-tv"></i> <?php echo strtoupper($anime['type']); ?></span>
                <span><i class="fas fa-circle <?php echo $anime['status'] === 'ongoing' ? 'text-success' : ($anime['status'] === 'upcoming' ? 'text-info' : 'text-success'); ?>"></i> <?php echo ucfirst($anime['status']); ?></span>
                <span><i class="fas fa-calendar"></i> <?php echo $anime['release_year']; ?></span>
                <span><i class="fas fa-film"></i> <?php echo $anime['episodes_count']; ?> Episodes</span>
                <?php if ($anime['duration']): ?>
                <span><i class="fas fa-clock"></i> <?php echo $anime['duration']; ?></span>
                <?php endif; ?>
                <span><i class="fas fa-eye"></i> <?php echo number_format($anime['views']); ?> views</span>
            </div>
            
            <?php if ($anime['genres']): ?>
            <div class="anime-genres">
                <?php foreach (explode(',', $anime['genres']) as $genreName): ?>
                <span class="genre-tag"><?php echo trim($genreName); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="anime-detail-actions">
                <?php if (!empty($episodes)): ?>
                <a href="watch.php?anime=<?php echo $anime['slug']; ?>&ep=1" class="btn btn-primary btn-lg">
                    <i class="fas fa-play"></i> Watch Episode 1
                </a>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                <button class="btn btn-secondary btn-lg" onclick="toggleFavorite(<?php echo $anime['id']; ?>, this)" id="favBtn">
                    <i class="<?php echo $isFav ? 'fas' : 'far'; ?> fa-heart"></i> 
                    <?php echo $isFav ? 'In Favorites' : 'Add to Favorites'; ?>
                </button>
                <?php else: ?>
                <a href="auth.php" class="btn btn-secondary btn-lg">
                    <i class="far fa-heart"></i> Add to Favorites
                </a>
                <?php endif; ?>
            </div>
            
            <div class="anime-synopsis">
                <h3 style="font-size: 1.2rem; margin-bottom: 12px;">Synopsis</h3>
                <p><?php echo nl2br(htmlspecialchars($anime['synopsis'] ?: $anime['description'], ENT_QUOTES, 'UTF-8')); ?></p>
            </div>
            
            <?php if ($anime['studio']): ?>
            <div style="margin-top: 20px;">
                <span style="color: var(--text-muted); font-size: 0.85rem;"><strong>Studio:</strong> <?php echo $anime['studio']; ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Secțiunea de episoade: afișează grid-ul cu episoadele disponibile; fiecare episod duce către watch.php -->
    <?php if (!empty($episodes)): ?>
    <div class="episodes-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-list"></i> Episodes
            </h2>
        </div>
        
        <div class="episodes-grid">
            <?php foreach ($episodes as $episode): ?>
            <a href="watch.php?anime=<?php echo $anime['slug']; ?>&ep=<?php echo $episode['episode_number']; ?>" class="episode-card">
                <div class="episode-card-thumb">
                    <img src="assets/images/<?php echo $episode['thumbnail'] ?: $anime['cover_image']; ?>" alt="Episode <?php echo $episode['episode_number']; ?>" onerror="this.src='assets/images/default-cover.jpg'">
                    <div class="episode-card-play">
                        <i class="fas fa-play"></i>
                    </div>
                </div>
                <div class="episode-card-info">
                    <div class="episode-card-number">Episode <?php echo $episode['episode_number']; ?></div>
                    <div class="episode-card-title"><?php echo $episode['title'] ?: 'Episode ' . $episode['episode_number']; ?></div>
                    <?php if ($episode['duration']): ?>
                    <div class="episode-card-duration"><i class="fas fa-clock"></i> <?php echo $episode['duration']; ?></div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Secțiunea de recenzii: afișează recenziile utilizatorilor și formularul de adăugare recenzie (doar pentru utilizatorii logați) -->
    <div class="episodes-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-comments"></i> Reviews
            </h2>
        </div>
        
        <?php if (isLoggedIn()): ?>
        <!-- Add Review Form -->
        <div style="background: var(--bg-card); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px; border: 1px solid var(--border-color);">
            <h4 style="margin-bottom: 16px;">Write a Review</h4>
            <form action="api/add_review.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="anime_id" value="<?php echo $anime['id']; ?>">
                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="filter-select" required style="width: 100px;">
                        <?php for ($i = 10; $i >= 1; $i--): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Comment</label>
                    <textarea name="comment" class="form-input" rows="3" placeholder="Write your review..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Review
                </button>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($reviews)): ?>
        <div class="episodes-grid">
            <?php foreach ($reviews as $review): ?>
            <div class="episode-card" style="cursor: default;">
                <div class="episode-card-info" style="padding: 8px 0;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <img src="assets/images/<?php echo $review['avatar']; ?>?v=2" alt="<?php echo $review['username']; ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;" onerror="this.src='assets/images/default-avatar.jpg'">
                        <span style="font-weight: 600; font-size: 0.9rem;"><?php echo $review['username']; ?></span>
                        <span style="color: var(--warning); margin-left: auto;">
                            <i class="fas fa-star"></i> <?php echo $review['rating']; ?>/10
                        </span>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;"><?php echo nl2br(htmlspecialchars($review['comment'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <span style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px; display: block;">
                        <i class="fas fa-clock"></i> <?php echo formatDate($review['created_at']); ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <h3>No reviews yet</h3>
            <p>Be the first to review this anime!</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Anime-uri similare (Related Anime): afișează sugestii bazate pe același gen; ajută la descoperirea de conținut similar -->
    <?php if (!empty($relatedAnime)): ?>
    <section class="section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-thumbs-up"></i> You May Also Like
            </h2>
        </div>
        
        <div class="anime-grid">
            <?php foreach ($relatedAnime as $related): ?>
            <a href="anime.php?slug=<?php echo $related['slug']; ?>" class="anime-card">
                <div class="anime-card-img-wrapper">
                    <img src="assets/images/<?php echo $related['cover_image']; ?>" alt="<?php echo $related['title']; ?>" class="anime-card-img" onerror="this.src='assets/images/default-cover.jpg'">
                    <div class="anime-card-overlay">
                        <div class="anime-card-play"><i class="fas fa-play"></i></div>
                    </div>
                    <span class="anime-card-badge <?php echo $related['status']; ?>"><?php echo $related['status']; ?></span>
                </div>
                <div class="anime-card-info">
                    <h3 class="anime-card-title"><?php echo $related['title']; ?></h3>
                    <div class="anime-card-meta">
                        <span class="anime-card-rating"><i class="fas fa-star"></i> <?php echo $related['rating']; ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>