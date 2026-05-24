<?php
// Include funcțiile comune pentru sesiune, bază de date și utilitare
require_once __DIR__ . '/includes/functions.php';

// Preluăm din URL slug-ul anime-ului și numărul episodului dorit; folosite pentru a încărca conținutul corect în player
$slug  = isset($_GET['anime']) ? clean($_GET['anime']) : '';
$epNum = isset($_GET['ep'])    ? intval($_GET['ep'])   : 1;

// Validare: dacă nu este furnizat un slug, redirecționăm către pagina principală
if (empty($slug)) {
    setFlash('error', 'Anime not found.');
    redirect(SITE_URL . '/index.php');
}

// Obținem datele anime-ului după slug; folosite pentru titlu, banner, metadate și navigare
$anime = getAnime($slug);

// Dacă anime-ul nu există, redirecționăm utilizatorul către home cu un mesaj de eroare
if (!$anime) {
    setFlash('error', 'Anime not found.');
    redirect(SITE_URL . '/index.php');
}

// Obținem toate episoadele asociate acestui anime; folosite în sidebar, navigare prev/next și player
$episodes = getEpisodes($anime['id']);

// Găsim episodul curent după numărul său; este cel afișat în player și utilizat pentru incrementarea vizualizărilor
$currentEpisode = null;
foreach ($episodes as $ep) {
    if ($ep['episode_number'] == $epNum) {
        $currentEpisode = $ep;
        break;
    }
}

// Dacă episodul cerut nu există, implicit îl setăm pe primul din listă
if (!$currentEpisode && !empty($episodes)) {
    $currentEpisode = $episodes[0];
    $epNum = $currentEpisode['episode_number'];
}

// Incrementăm numărul de vizualizări pentru anime și pentru episodul curent; folosit în clasamente și statistici
$db = getDB();
$stmt = $db->prepare("UPDATE anime SET views = views + 1 WHERE id = ?");
$stmt->execute([$anime['id']]);

if ($currentEpisode) {
    $stmt = $db->prepare("UPDATE episodes SET views = views + 1 WHERE id = ?");
    $stmt->execute([$currentEpisode['id']]);
}

// Dacă utilizatorul este autentificat, actualizăm istoricul de vizionare cu episodul curent; folosit în profil și pentru "Continue Watching"
if (isLoggedIn() && $currentEpisode) {
    $stmt = $db->prepare("
        INSERT INTO watch_history (user_id, episode_id, progress) 
        VALUES (?, ?, 0) 
        ON DUPLICATE KEY UPDATE watched_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$_SESSION['user_id'], $currentEpisode['id']]);
}

$pageTitle = $anime['title'] . ' - Episode ' . $epNum;
$pageDescription = 'Watch ' . $anime['title'] . ' Episode ' . $epNum . ' online.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="watch-container">
        <!-- Player video: afișează videoclipul episodului curent sau un placeholder dacă nu există sursă; folosit în pagina watch.php -->
        <div>
            <div class="video-player">
                <?php if ($currentEpisode && $currentEpisode['video_url']): ?>
                <video controls poster="assets/images/<?php echo $anime['banner_image']; ?>" onerror="this.poster='assets/images/default-banner.jpg'">
                    <source src="<?php echo $currentEpisode['video_url']; ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <?php else: ?>
                <div class="video-placeholder">
                    <i class="fas fa-play-circle"></i>
                    <p>Video not available</p>
                    <span style="font-size: 0.85rem;">This is a demo - no actual video file</span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="video-info">
                <h1 class="video-title">
                    <?php echo $anime['title']; ?> - 
                    Episode <?php echo $currentEpisode ? $currentEpisode['episode_number'] : $epNum; ?>
                </h1>
                <?php if ($currentEpisode && $currentEpisode['title']): ?>
                <p style="color: var(--text-secondary); margin-top: 4px;"><?php echo $currentEpisode['title']; ?></p>
                <?php endif; ?>
                <div class="video-meta">
                    <span><i class="fas fa-tv"></i> <?php echo strtoupper($anime['type']); ?></span>
                    <span><i class="fas fa-star"></i> <?php echo $anime['rating']; ?></span>
                    <span><i class="fas fa-eye"></i> <?php echo number_format($anime['views']); ?> views</span>
                    <?php if ($currentEpisode && $currentEpisode['duration']): ?>
                    <span><i class="fas fa-clock"></i> <?php echo $currentEpisode['duration']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Navigație prev/next: determină episodul anterior și următor pentru a permite navigarea rapidă între episoade -->
            <div style="display: flex; gap: 12px; margin-top: 20px;">
                <?php 
                $prevEp = null;
                $nextEp = null;
                foreach ($episodes as $i => $ep) {
                    if ($ep['episode_number'] == $epNum) {
                        if (isset($episodes[$i - 1])) $prevEp = $episodes[$i - 1];
                        if (isset($episodes[$i + 1])) $nextEp = $episodes[$i + 1];
                        break;
                    }
                }
                ?>
                <?php if ($prevEp): ?>
                <a href="watch.php?anime=<?php echo $anime['slug']; ?>&ep=<?php echo $prevEp['episode_number']; ?>" class="btn btn-secondary">
                    <i class="fas fa-chevron-left"></i> Previous Episode
                </a>
                <?php endif; ?>
                
                <a href="anime.php?slug=<?php echo $anime['slug']; ?>" class="btn btn-secondary">
                    <i class="fas fa-info-circle"></i> Anime Details
                </a>
                
                <?php if ($nextEp): ?>
                <a href="watch.php?anime=<?php echo $anime['slug']; ?>&ep=<?php echo $nextEp['episode_number']; ?>" class="btn btn-primary">
                    Next Episode <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sidebar cu lista de episoade: afișează toate episoadele anime-ului și evidențiază episodul curent; folosit în watch.php -->
        <div class="episodes-sidebar">
            <div class="episodes-sidebar-header">
                <i class="fas fa-list"></i> Episodes List
            </div>
            <div class="episodes-sidebar-list">
                <?php foreach ($episodes as $episode): ?>
                <a href="watch.php?anime=<?php echo $anime['slug']; ?>&ep=<?php echo $episode['episode_number']; ?>" 
                   class="episode-sidebar-item <?php echo $episode['episode_number'] == $epNum ? 'active' : ''; ?>">
                    <div class="episode-sidebar-thumb">
                        <img src="assets/images/<?php echo $episode['thumbnail'] ?: $anime['cover_image']; ?>" 
                             alt="Episode <?php echo $episode['episode_number']; ?>"
                             onerror="this.src='assets/images/default-cover.jpg'">
                    </div>
                    <div class="episode-sidebar-info">
                        <div class="episode-sidebar-number">EP <?php echo $episode['episode_number']; ?></div>
                        <div class="episode-sidebar-title"><?php echo $episode['title'] ?: 'Episode ' . $episode['episode_number']; ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
                
                <?php if (empty($episodes)): ?>
                <div class="empty-state" style="padding: 40px 20px;">
                    <p>No episodes available</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>