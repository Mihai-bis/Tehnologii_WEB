<?php
// Include header-ul comun care conține funcții, sesiunea și structura HTML de început
require_once __DIR__ . '/includes/header.php';

// Preluăm parametrii din URL pentru filtrare și paginare; sunt folosiți pe pagina principală și în bara de filtre
$page   = isset($_GET['page'])   ? max(1, intval($_GET['page'])) : 1;   // Pagina curentă (folosită la paginare)
$status = isset($_GET['status']) ? clean($_GET['status'])        : '';  // Filtrare după status: ongoing, completed, upcoming
$type   = isset($_GET['type'])   ? clean($_GET['type'])          : '';  // Filtrare după tip: tv, movie, ova, ona, special
$genre  = isset($_GET['genre'])  ? clean($_GET['genre'])         : '';  // Filtrare după gen (slug); folosit în hero și în grid
$search = isset($_GET['q'])      ? clean($_GET['q'])             : '';  // Căutare după cuvinte cheie; folosit și în pagina search.php

// Apelăm funcția centrală care returnează lista de anime paginată, cu filtrele active; folosită în toată aplicația pentru listing
$result = getAnimeList($page, 24, $status, $type, $genre, $search);
$animeList = $result['data'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
$total = $result['total'];

// Interogăm baza de date pentru a obține top 6 anime cu cel mai mare rating (folosit în hero carousel-ul de pe prima pagină)
$db = getDB();
$stmt = $db->query("SELECT * FROM anime ORDER BY rating DESC, views DESC LIMIT 6");
$featuredAnimeList = $stmt->fetchAll();

$pageTitle = $search ? "Search: {$search}" : ($genre ? ucfirst($genre) . ' Anime' : ($status ? ucfirst($status) . ' Anime' : ($type ? ucfirst($type) . 's' : 'Home')));
$pageDescription = "Browse and watch your favorite anime online. Stream the latest episodes.";
?>

<!-- Hero Section -->
<?php if (!empty($featuredAnimeList) && $page === 1 && !$search && !$genre && !$status && !$type): ?>
<section class="hero-carousel" id="heroCarousel">
    <div class="hero-carousel-track" id="heroTrack">
        <?php foreach ($featuredAnimeList as $index => $featuredAnime): ?>
        <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
            <?php
            $heroBg = $featuredAnime['banner_image'];
            if ($heroBg === 'default-banner.jpg' || empty($heroBg)) {
                $heroBg = $featuredAnime['cover_image'];
            }
            ?>
            <div class="hero-bg" style="background-image: url('assets/images/<?php echo $heroBg; ?>');"></div>
            <div class="hero-overlay"></div>
            <div class="container">
                <div class="hero-content">
                    <span class="hero-badge">
                        <i class="fas fa-star"></i> #<?php echo $index + 1; ?> Rated
                    </span>
                    <h1 class="hero-title"><?php echo $featuredAnime['title']; ?></h1>
                    <div class="hero-meta">
                        <span class="rating"><i class="fas fa-star"></i> <?php echo $featuredAnime['rating']; ?></span>
                        <span><i class="fas fa-tv"></i> <?php echo $featuredAnime['type']; ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo $featuredAnime['release_year']; ?></span>
                        <span><i class="fas fa-film"></i> <?php echo $featuredAnime['episodes_count']; ?> Episodes</span>
                    </div>
                    <p class="hero-desc"><?php echo truncate($featuredAnime['synopsis'], 200); ?></p>
                    <div class="hero-actions">
                        <a href="watch.php?anime=<?php echo $featuredAnime['slug']; ?>&ep=1" class="btn btn-primary btn-lg">
                            <i class="fas fa-play"></i> Watch Now
                        </a>
                        <a href="anime.php?slug=<?php echo $featuredAnime['slug']; ?>" class="btn btn-secondary btn-lg">
                            <i class="fas fa-info-circle"></i> Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Carousel Navigation -->
    <button class="hero-carousel-btn hero-prev" id="heroPrev" aria-label="Previous">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="hero-carousel-btn hero-next" id="heroNext" aria-label="Next">
        <i class="fas fa-chevron-right"></i>
    </button>

    <!-- Carousel Dots -->
    <div class="hero-carousel-dots" id="heroDots">
        <?php foreach ($featuredAnimeList as $index => $a): ?>
        <button class="hero-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Anime List Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-fire"></i>
                <?php 
                if ($search) echo "Search Results for \"{$search}\"";
                elseif ($genre) echo ucfirst($genre) . ' Anime';
                elseif ($status) echo ucfirst($status) . ' Anime';
                elseif ($type) echo ucfirst($type) . 's';
                else echo 'Latest Anime';
                ?>
                <span style="color: var(--text-muted); font-size: 0.9rem; margin-left: 8px;">(<?php echo $total; ?> results)</span>
            </h2>
        </div>

        <!-- Bara de filtre: permite utilizatorului să filtreze lista după status și tip; folosită pe pagina principală -->
        <div class="filters-bar">
            <select class="filter-select" onchange="window.location.href='?<?php echo http_build_query(array_diff_key($_GET, ['status' => 1, 'page' => 1])); ?>&status='+this.value">
                <option value="">All Status</option>
                <option value="ongoing" <?php echo $status === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="upcoming" <?php echo $status === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
            </select>
            
            <select class="filter-select" onchange="window.location.href='?<?php echo http_build_query(array_diff_key($_GET, ['type' => 1, 'page' => 1])); ?>&type='+this.value">
                <option value="">All Types</option>
                <option value="tv" <?php echo $type === 'tv' ? 'selected' : ''; ?>>TV Series</option>
                <option value="movie" <?php echo $type === 'movie' ? 'selected' : ''; ?>>Movie</option>
                <option value="ova" <?php echo $type === 'ova' ? 'selected' : ''; ?>>OVA</option>
                <option value="ona" <?php echo $type === 'ona' ? 'selected' : ''; ?>>ONA</option>
                <option value="special" <?php echo $type === 'special' ? 'selected' : ''; ?>>Special</option>
            </select>
            
            <?php if ($genre): ?>
            <a href="?<?php echo http_build_query(array_diff_key($_GET, ['genre' => 1, 'page' => 1])); ?>" class="btn btn-sm btn-outline">
                <i class="fas fa-times"></i> Clear Genre: <?php echo ucfirst($genre); ?>
            </a>
            <?php endif; ?>
        </div>

        <!-- Grid-ul de anime: afișează cardurile returnate de getAnimeList(); folosit pe pagina principală și în rezultatele de căutare -->
        <?php if (empty($animeList)): ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>No anime found</h3>
            <p>Try adjusting your filters or search query</p>
        </div>
        <?php else: ?>
        <div class="anime-grid">
            <?php foreach ($animeList as $anime): ?>
            <a href="anime.php?slug=<?php echo $anime['slug']; ?>" class="anime-card">
                <div class="anime-card-img-wrapper">
                    <img src="assets/images/<?php echo $anime['cover_image']; ?>" 
                         alt="<?php echo $anime['title']; ?>" 
                         class="anime-card-img"
                         onerror="this.src='assets/images/default-cover.jpg'">
                    <div class="anime-card-overlay">
                        <div class="anime-card-play">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                    <span class="anime-card-badge <?php echo $anime['status']; ?>"><?php echo $anime['status']; ?></span>
                    <span class="anime-card-episodes"><?php echo $anime['episodes_count']; ?> EP</span>
                </div>
                <div class="anime-card-info">
                    <h3 class="anime-card-title"><?php echo $anime['title']; ?></h3>
                    <div class="anime-card-meta">
                        <span><?php echo $anime['type']; ?></span>
                        <span class="anime-card-rating">
                            <i class="fas fa-star"></i> <?php echo $anime['rating']; ?>
                        </span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Paginare: generează linkuri către paginile vecine păstrând filtrele active din URL; folosită pe pagina principală -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
            <?php if ($i === $currentPage): ?>
            <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>