<?php
// Include fișierul cu funcții comune folosit în tot site-ul
require_once __DIR__ . '/includes/functions.php';

// Preluăm termenul de căutare din URL (parametrul q); folosit în header-ul de rezultate și în interogare
$query = isset($_GET['q']) ? trim(clean($_GET['q'])) : '';

// Dacă termenul de căutare este gol, redirecționăm utilizatorul către pagina principală
if (empty($query)) {
    redirect(SITE_URL . '/index.php');
}

$pageTitle = 'Search: ' . $query;
$pageDescription = 'Search results for ' . $query;

// Include header-ul care generează începutul paginii HTML
require_once __DIR__ . '/includes/header.php';

// Apelăm funcția getAnimeList() pentru a căuta anime care conțin termenul; rezultatele sunt afișate mai jos în grid
$result = getAnimeList(1, 48, '', '', '', $query);
$animeList = $result['data'];
$total = $result['total'];
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-search"></i> Search Results
                <span style="color: var(--text-muted); font-size: 0.9rem; margin-left: 8px;">for "<?php echo $query; ?>" (<?php echo $total; ?> results)</span>
            </h2>
        </div>
        
        <?php if (empty($animeList)): ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>No results found</h3>
            <p>Try different keywords or check your spelling</p>
        </div>
        <?php else: ?>
        <div class="anime-grid">
            <?php foreach ($animeList as $anime): ?>
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
        <?php endif; ?>
    </div>
</section>

<!-- Footer-ul comun care încheie pagina HTML; folosit în toate paginile site-ului -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>