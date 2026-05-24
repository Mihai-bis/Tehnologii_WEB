<?php
// Include funcțiile comune ale aplicației
require_once __DIR__ . '/includes/functions.php';

// ─── Verificare admin ───
// Această pagină este restricționată exclusiv utilizatorilor cu rol de administrator.
// Dacă utilizatorul curent nu este admin, este redirecționat către pagina principală.
if (!isAdmin()) {
    setFlash('error', 'Access denied. Admin only.');
    redirect(SITE_URL . '/index.php');
}

$db = getDB();
$errors = [];
$success = '';

// ─── Procesare add_anime ───
// Gestionează adăugarea unui anime nou prin POST.
// Include: validare CSRF, curățare input, generare slug unic,
// upload imagini (cover și banner) în folderele specifice,
// inserare în tabela anime și asocierea genurilor în tabela anime_genres.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_anime') {
    // Validare token CSRF pentru protecție împotriva atacurilor cross-site
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $title = clean($_POST['title'] ?? '');
        $description = clean($_POST['description'] ?? '');
        $synopsis = clean($_POST['synopsis'] ?? '');
        $status = clean($_POST['status'] ?? 'upcoming');
        $type = clean($_POST['type'] ?? 'tv');
        $episodesCount = intval($_POST['episodes_count'] ?? 0);
        $rating = floatval($_POST['rating'] ?? 0);
        $releaseYear = intval($_POST['release_year'] ?? 0) ?: null;
        $studio = clean($_POST['studio'] ?? '');
        $duration = clean($_POST['duration'] ?? '');
        $genres = $_POST['genres'] ?? [];
        
        if (empty($title)) {
            $errors[] = 'Title is required.';
        }
        
        if (empty($errors)) {
            try {
                $slug = generateSlug($title);
                
                // Verifică dacă slug-ul există deja și îl face unic adăugând timestamp-ul
                $stmt = $db->prepare("SELECT id FROM anime WHERE slug = ?");
                $stmt->execute([$slug]);
                if ($stmt->fetch()) {
                    $slug .= '-' . time();
                }
                
                // Upload imagine copertă în folderul 'covers'
                $coverImage = 'default-cover.jpg';
                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = uploadImage($_FILES['cover_image'], 'covers');
                    if ($uploaded) $coverImage = $uploaded;
                }
                
                // Upload imagine banner în folderul 'banners'
                $bannerImage = 'default-banner.jpg';
                if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = uploadImage($_FILES['banner_image'], 'banners');
                    if ($uploaded) $bannerImage = $uploaded;
                }
                
                // Inserare anime nou în baza de date
                $stmt = $db->prepare("
                    INSERT INTO anime (title, slug, description, synopsis, cover_image, banner_image, 
                                     status, type, episodes_count, rating, release_year, studio, duration, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $title, $slug, $description, $synopsis, $coverImage, $bannerImage,
                    $status, $type, $episodesCount, $rating, $releaseYear, $studio, $duration, $_SESSION['user_id']
                ]);
                
                $animeId = $db->lastInsertId();
                
                // Adaugă genurile selectate în tabela de legătură anime_genres
                if (!empty($genres)) {
                    $stmt = $db->prepare("INSERT INTO anime_genres (anime_id, genre_id) VALUES (?, ?)");
                    foreach ($genres as $genreId) {
                        $stmt->execute([$animeId, intval($genreId)]);
                    }
                }
                
                $success = 'Anime added successfully!';
                
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// ─── Procesare edit_anime ───
// Gestionează actualizarea unui anime existent prin POST.
// Include: validare CSRF, preluarea imaginilor curente,
// upload eventuale noi imagini, update în tabela anime,
// ștergerea genurilor vechi și re-asocierea celor noi.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_anime') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $animeId = intval($_POST['anime_id'] ?? 0);
        $title = clean($_POST['title'] ?? '');
        $description = clean($_POST['description'] ?? '');
        $synopsis = clean($_POST['synopsis'] ?? '');
        $status = clean($_POST['status'] ?? 'upcoming');
        $type = clean($_POST['type'] ?? 'tv');
        $episodesCount = intval($_POST['episodes_count'] ?? 0);
        $rating = floatval($_POST['rating'] ?? 0);
        $releaseYear = intval($_POST['release_year'] ?? 0) ?: null;
        $studio = clean($_POST['studio'] ?? '');
        $duration = clean($_POST['duration'] ?? '');
        $genres = $_POST['genres'] ?? [];
        
        if (empty($title)) {
            $errors[] = 'Title is required.';
        }
        
        if (empty($errors) && $animeId > 0) {
            try {
                // Preia imaginile curente ale anime-ului din DB
                $stmt = $db->prepare("SELECT cover_image, banner_image FROM anime WHERE id = ?");
                $stmt->execute([$animeId]);
                $current = $stmt->fetch();
                $coverImage = $current['cover_image'];
                $bannerImage = $current['banner_image'];
                
                // Upload nouă imagine copertă dacă a fost trimisă
                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = uploadImage($_FILES['cover_image'], 'covers');
                    if ($uploaded) $coverImage = $uploaded;
                }
                
                // Upload nouă imagine banner dacă a fost trimisă
                if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = uploadImage($_FILES['banner_image'], 'banners');
                    if ($uploaded) $bannerImage = $uploaded;
                }
                
                // Actualizează datele anime-ului în baza de date
                $stmt = $db->prepare("
                    UPDATE anime SET 
                        title = ?, description = ?, synopsis = ?, status = ?, type = ?, 
                        episodes_count = ?, rating = ?, release_year = ?, 
                        studio = ?, duration = ?, cover_image = ?, banner_image = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $title, $description, $synopsis, $status, $type,
                    $episodesCount, $rating, $releaseYear,
                    $studio, $duration, $coverImage, $bannerImage, $animeId
                ]);
                
                // Reîmprospătează genurile: șterge vechile și inserează cele selectate
                $db->prepare("DELETE FROM anime_genres WHERE anime_id = ?")->execute([$animeId]);
                if (!empty($genres)) {
                    $stmt = $db->prepare("INSERT INTO anime_genres (anime_id, genre_id) VALUES (?, ?)");
                    foreach ($genres as $genreId) {
                        $stmt->execute([$animeId, intval($genreId)]);
                    }
                }
                
                $success = 'Anime updated successfully!';
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// ─── Statistici query ───
// Interogare unică în baza de date pentru a obține statisticile generale:
// număr total de anime-uri, utilizatori, episoade și favorite.
// Afișate în cardurile de la începutul paginii de admin.
$stats = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM anime) as total_anime,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM episodes) as total_episodes,
        (SELECT COUNT(*) FROM favorites) as total_favorites
")->fetch(PDO::FETCH_ASSOC);

// ─── Tabel anime paginat ───
// Preia lista tuturor anime-urilor pentru afișare în tabelul de administrare,
// cu paginare (20 de înregistrări per pagină) și informații despre creator.
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$stmt = $db->query("SELECT a.*, u.username as creator 
                     FROM anime a 
                     LEFT JOIN users u ON a.created_by = u.id 
                     ORDER BY a.created_at DESC 
                     LIMIT {$perPage} OFFSET {$offset}");
$allAnime = $stmt->fetchAll();

$totalPages = ceil($stats['total_anime'] / $perPage);

// Preia lista genurilor disponibile pentru formularele Add/Edit
$genres = getGenres();

$pageTitle = 'Admin Panel';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div class="container">
        <div class="admin-header-content">
            <h1 class="admin-title"><i class="fas fa-cog"></i> Admin Panel</h1>
            <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Site
            </a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Messages -->
    <?php if ($success): ?>
    <div class="flash-message flash-success" style="position: static; margin-bottom: 20px;">
        <div class="flash-content">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success; ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
    <div class="flash-message flash-error" style="position: static; margin-bottom: 20px;">
        <div class="flash-content">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo implode(', ', $errors); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-film"></i></div>
            <div class="stat-value"><?php echo $stats['total_anime']; ?></div>
            <div class="stat-label">Total Anime</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?php echo $stats['total_users']; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-play-circle"></i></div>
            <div class="stat-value"><?php echo $stats['total_episodes']; ?></div>
            <div class="stat-label">Total Episodes</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-heart"></i></div>
            <div class="stat-value"><?php echo $stats['total_favorites']; ?></div>
            <div class="stat-label">Total Favorites</div>
        </div>
    </div>

    <!-- Anime Table -->
    <div class="data-table-container" style="margin-bottom: 40px;">
        <div class="data-table-header">
            <h3 class="data-table-title"><i class="fas fa-film"></i> Manage Anime</h3>
            <button class="btn btn-primary btn-sm" onclick="openModal('addAnimeModal')">
                <i class="fas fa-plus"></i> Add Anime
            </button>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Anime</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Episodes</th>
                        <th>Rating</th>
                        <th>Views</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allAnime as $anime): ?>
                    <tr data-anime-id="<?php echo $anime['id']; ?>">
                        <td>
                            <div class="table-anime">
                                <img src="assets/images/<?php echo $anime['cover_image']; ?>" alt="<?php echo $anime['title']; ?>" onerror="this.src='assets/images/default-cover.jpg'">
                                <span style="font-weight: 600;"><?php echo truncate($anime['title'], 30); ?></span>
                            </div>
                        </td>
                        <td><span class="anime-card-badge <?php echo $anime['status']; ?>" style="position: static; display: inline-block;"><?php echo $anime['status']; ?></span></td>
                        <td><?php echo strtoupper($anime['type']); ?></td>
                        <td><?php echo $anime['episodes_count']; ?></td>
                        <td style="color: var(--warning);"><i class="fas fa-star"></i> <?php echo $anime['rating']; ?></td>
                        <td><?php echo number_format($anime['views']); ?></td>
                        <td><?php echo formatDate($anime['created_at']); ?></td>
                        <td>
                            <div class="table-actions">
                                <button class="btn-icon edit" onclick="openEditModal(<?php echo $anime['id']; ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon delete" onclick="confirmDeleteAnime(<?php echo $anime['id']; ?>, '<?php echo htmlspecialchars($anime['title'], ENT_QUOTES, 'UTF-8'); ?>')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="padding: 20px; border-top: 1px solid var(--border-color);">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <?php if ($i === $page): ?>
            <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ─── Modal add ─── -->
<!-- Modal care conține formularul pentru adăugarea unui anime nou în sistem.
     Se deschide din butonul "Add Anime" și trimite datele prin POST către aceeași pagină. -->
<!-- Add Anime Modal -->
<div class="modal-overlay" id="addAnimeModal">
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-plus"></i> Add New Anime</h3>
            <button class="modal-close" onclick="closeModal('addAnimeModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_anime">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-input" required>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Synopsis</label>
                        <textarea name="synopsis" class="form-input" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="filter-select" style="width: 100%;">
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="upcoming" selected>Upcoming</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" class="filter-select" style="width: 100%;">
                            <option value="tv" selected>TV</option>
                            <option value="movie">Movie</option>
                            <option value="ova">OVA</option>
                            <option value="ona">ONA</option>
                            <option value="special">Special</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Episodes Count</label>
                        <input type="number" name="episodes_count" class="form-input" value="0" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Rating (0-10)</label>
                        <input type="number" name="rating" class="form-input" value="0" min="0" max="10" step="0.1">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Release Year</label>
                        <input type="number" name="release_year" class="form-input" placeholder="2024" min="1950" max="2099">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Duration (e.g., 24 min)</label>
                        <input type="text" name="duration" class="form-input" placeholder="24 min">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Studio</label>
                        <input type="text" name="studio" class="form-input" placeholder="Studio name">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Genres</label>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                            <?php foreach ($genres as $genre): ?>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.85rem; color: var(--text-secondary);">
                                <input type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>" style="accent-color: var(--primary);">
                                <?php echo $genre['name']; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Cover Image</label>
                        <input type="file" name="cover_image" class="form-input" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Banner Image</label>
                        <input type="file" name="banner_image" class="form-input" accept="image/*">
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addAnimeModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Anime</button>
            </div>
        </form>
    </div>
</div>

<!-- ─── Modal edit ─── -->
<!-- Modal care conține formularul pentru editarea unui anime existent.
     Câmpurile sunt populate automat prin JavaScript (openEditModal) cu datele
     preluate de la endpoint-ul api/get_anime.php. -->
<!-- Edit Anime Modal -->
<div class="modal-overlay" id="editAnimeModal">
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-edit"></i> Edit Anime</h3>
            <button class="modal-close" onclick="closeModal('editAnimeModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_anime">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="anime_id" id="edit_anime_id">
            
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" id="edit_title" class="form-input" required>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-input" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Synopsis</label>
                        <textarea name="synopsis" id="edit_synopsis" class="form-input" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="filter-select" style="width: 100%;">
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="upcoming">Upcoming</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" id="edit_type" class="filter-select" style="width: 100%;">
                            <option value="tv">TV</option>
                            <option value="movie">Movie</option>
                            <option value="ova">OVA</option>
                            <option value="ona">ONA</option>
                            <option value="special">Special</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Episodes Count</label>
                        <input type="number" name="episodes_count" id="edit_episodes_count" class="form-input" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Rating (0-10)</label>
                        <input type="number" name="rating" id="edit_rating" class="form-input" min="0" max="10" step="0.1">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Release Year</label>
                        <input type="number" name="release_year" id="edit_release_year" class="form-input" min="1950" max="2099">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Duration</label>
                        <input type="text" name="duration" id="edit_duration" class="form-input">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Studio</label>
                        <input type="text" name="studio" id="edit_studio" class="form-input">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Genres</label>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;" id="edit_genres_container">
                            <?php foreach ($genres as $genre): ?>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.85rem; color: var(--text-secondary);">
                                <input type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>" class="edit-genre-checkbox" style="accent-color: var(--primary);">
                                <?php echo $genre['name']; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Cover Image</label>
                        <input type="file" name="cover_image" class="form-input" accept="image/*">
                        <div style="margin-top: 8px;">
                            <img id="edit_cover_preview" src="" alt="Current Cover" style="width: 80px; height: 110px; object-fit: cover; border-radius: var(--radius-sm); display: none;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Banner Image</label>
                        <input type="file" name="banner_image" class="form-input" accept="image/*">
                        <div style="margin-top: 8px;">
                            <img id="edit_banner_preview" src="" alt="Current Banner" style="width: 120px; height: 60px; object-fit: cover; border-radius: var(--radius-sm); display: none;">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editAnimeModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>