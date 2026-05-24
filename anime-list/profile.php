<?php
// Include funcțiile comune ale aplicației (autentificare, DB, utilitare)
require_once __DIR__ . '/includes/functions.php';

// ─── Verificare login ───
// Dacă utilizatorul nu este autentificat, îl redirecționăm către pagina de login
if (!isLoggedIn()) {
    setFlash('error', 'Please login to view your profile.');
    redirect(SITE_URL . '/auth.php');
}

// Preluăm datele utilizatorului curent și conexiunea la baza de date
$user = getCurrentUser();
$db = getDB();

// ─── Preluare favorite ───
// Interoghează baza de date pentru a obține anime-urile favorite ale utilizatorului logat,
// ordonate descrescător după data adăugării. Folosit în secțiunea de statistici și pe pagina profilului.
$stmt = $db->prepare("
    SELECT a.* FROM favorites f 
    JOIN anime a ON f.anime_id = a.id 
    WHERE f.user_id = ? 
    ORDER BY f.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll();

// ─── Preluare istoric vizionare ───
// Obține ultimele 10 episoade vizionate de utilizator, împreună cu informațiile despre anime.
// Afișate în secțiunea "Watch History" din pagina de profil.
$stmt = $db->prepare("
    SELECT a.title, a.slug, a.cover_image, e.episode_number, e.title as episode_title, wh.watched_at 
    FROM watch_history wh 
    JOIN episodes e ON wh.episode_id = e.id 
    JOIN anime a ON e.anime_id = a.id 
    WHERE wh.user_id = ? 
    ORDER BY wh.watched_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$watchHistory = $stmt->fetchAll();

// ─── Preluare recenzii ───
// Obține ultimele 20 recenzii lăsate de utilizator, împreună cu datele anime-ului asociat.
// Afișate în secțiunea "My Reviews".
$stmt = $db->prepare("
    SELECT r.*, a.title as anime_title, a.slug as anime_slug, a.cover_image 
    FROM reviews r 
    JOIN anime a ON r.anime_id = a.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 20
");
$stmt->execute([$_SESSION['user_id']]);
$reviews = $stmt->fetchAll();

// ─── Schimbare avatar (POST + validare) ───
// Procesează cererea de schimbare a imaginii de profil prin POST.
// Validează că avatarul selectat face parte din lista de avatare predefinite existente în folder,
// apoi actualizează câmpul "avatar" în tabela users pentru utilizatorul curent.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_avatar') {
    $avatar = clean($_POST['avatar'] ?? '');
    $allowedAvatars = [];
    $avatarDir = __DIR__ . '/assets/images/avatars/predefined/';
    if (is_dir($avatarDir)) {
        foreach (glob($avatarDir . '*.png') as $file) {
            $allowedAvatars[] = 'avatars/predefined/' . basename($file);
        }
    }
    if (in_array($avatar, $allowedAvatars)) {
        $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$avatar, $_SESSION['user_id']]);
        setFlash('success', 'Profile picture updated successfully!');
        redirect(SITE_URL . '/profile.php');
    } else {
        setFlash('error', 'Invalid avatar selection.');
        redirect(SITE_URL . '/profile.php');
    }
}

// ─── Grid avatare ───
// Construiește lista de avatare predefinite disponibile din folderul assets/images/avatars/predefined/.
// Folosit pentru a afișa grid-ul de selecție a imaginii de profil din pagina profilului.
$predefinedAvatars = [];
$avatarDir = __DIR__ . '/assets/images/avatars/predefined/';
if (is_dir($avatarDir)) {
    foreach (glob($avatarDir . '*.png') as $file) {
        $predefinedAvatars[] = 'avatars/predefined/' . basename($file);
    }
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 40px;">
    <!-- ─── Afișare profil + statistici ─── -->
    <!-- Afișează datele utilizatorului (avatar, nume, email, data înregistrării)
         și statisticile rapide: număr de favorite, episoade vizionate și recenzii. -->
    <!-- Profile Header -->
    <div class="profile-header">
        <img src="assets/images/<?php echo $user['avatar']; ?>?v=2" alt="<?php echo $user['username']; ?>" class="profile-avatar" onerror="this.src='assets/images/default-avatar.jpg'">
        <div class="profile-info">
            <h2><?php echo $user['username']; ?></h2>
            <p><i class="fas fa-envelope"></i> <?php echo $user['email']; ?></p>
            <p style="margin-top: 4px;"><i class="fas fa-calendar"></i> Member since <?php echo formatDate($user['created_at']); ?></p>
            <div class="profile-stats">
                <div class="profile-stat">
                    <div class="profile-stat-value"><?php echo count($favorites); ?></div>
                    <div class="profile-stat-label">Favorites</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-value"><?php echo count($watchHistory); ?></div>
                    <div class="profile-stat-label">Watched</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-value"><?php echo count($reviews); ?></div>
                    <div class="profile-stat-label">Reviews</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Avatar Selection -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-user-circle"></i> Choose Profile Picture</h2>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="change_avatar">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="avatar-grid">
                <?php foreach ($predefinedAvatars as $avatar): ?>
                <label class="avatar-option">
                    <input type="radio" name="avatar" value="<?php echo $avatar; ?>" <?php echo $user['avatar'] === $avatar ? 'checked' : ''; ?>>
                    <img src="assets/images/<?php echo $avatar; ?>?v=2" alt="Avatar" class="avatar-option-img">
                    <span class="avatar-check"><i class="fas fa-check"></i></span>
                </label>
                <?php endforeach; ?>
            </div>
            <div style="margin-top: 20px; text-align: center;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Profile Picture</button>
            </div>
        </form>
    </div>
    
    <!-- Watch History -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-history"></i> Watch History</h2>
        </div>
        
        <?php if (!empty($watchHistory)): ?>
        <div class="episodes-grid">
            <?php foreach ($watchHistory as $item): ?>
            <a href="watch.php?anime=<?php echo $item['slug']; ?>&ep=<?php echo $item['episode_number']; ?>" class="episode-card">
                <div class="episode-card-thumb">
                    <img src="assets/images/<?php echo $item['cover_image']; ?>" alt="<?php echo $item['title']; ?>" onerror="this.src='assets/images/default-cover.jpg'">
                    <div class="episode-card-play"><i class="fas fa-play"></i></div>
                </div>
                <div class="episode-card-info">
                    <div class="episode-card-number"><?php echo truncate($item['title'], 25); ?></div>
                    <div class="episode-card-title">Episode <?php echo $item['episode_number']; ?><?php echo $item['episode_title'] ? ' - ' . truncate($item['episode_title'], 20) : ''; ?></div>
                    <div class="episode-card-duration"><i class="fas fa-clock"></i> <?php echo formatDate($item['watched_at'], 'M d, Y'); ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-tv"></i>
            <h3>No watch history</h3>
            <p>Start watching anime to see your history here!</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- My Reviews -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-comments"></i> My Reviews</h2>
        </div>
        
        <?php if (!empty($reviews)): ?>
        <div class="episodes-grid">
            <?php foreach ($reviews as $review): ?>
            <div class="episode-card" style="cursor: default;">
                <div class="episode-card-thumb">
                    <img src="assets/images/<?php echo $review['cover_image']; ?>" alt="<?php echo $review['anime_title']; ?>" onerror="this.src='assets/images/default-cover.jpg'">
                </div>
                <div class="episode-card-info">
                    <div class="episode-card-number">
                        <a href="anime.php?slug=<?php echo $review['anime_slug']; ?>"><?php echo $review['anime_title']; ?></a>
                    </div>
                    <div style="color: var(--warning); margin: 4px 0;">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? '' : 'text-muted'; ?>" style="font-size: 0.75rem; opacity: <?php echo $i <= $review['rating'] ? 1 : 0.3; ?>"></i>
                        <?php endfor; ?>
                        <span style="color: var(--warning); font-size: 0.85rem;"><?php echo $review['rating']; ?>/10</span>
                    </div>
                    <p style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.5;"><?php echo truncate($review['comment'], 100); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-pen"></i>
            <h3>No reviews yet</h3>
            <p>Review anime to share your thoughts with others!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>