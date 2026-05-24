<?php
// Include fișierul cu funcții utilitare (curățare, autentificare, baza de date etc.)
require_once __DIR__ . '/functions.php';

// Inițializează variabilele globale necesare în header:
// utilizatorul curent (dacă este autentificat), mesajul flash și lista de genuri
$currentUser = getCurrentUser();
$flash = getFlash();
$genres = getGenres();
?>
<!DOCTYPE html>
<html lang="en">

<!-- ============================================================ -->
<!-- SECȚIUNEA <HEAD>: meta-tag-uri, titlu, descriere SEO, -->
<!-- fișiere CSS (stiluri proprii, Font Awesome, Google Fonts) -->
<!-- și favicon. Include și token-ul CSRF pentru cererile AJAX. -->
<!-- ============================================================ -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? clean($pageTitle) . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? clean($pageDescription) : 'Watch your favorite anime online. Stream the latest episodes of popular anime series.'; ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='48' fill='%23ff6b6b'/%3E%3Cpolygon points='40,30 70,50 40,70' fill='white'/%3E%3C/svg%3E">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
</head>
<body <?php echo isLoggedIn() ? 'data-logged-in="true"' : ''; ?>>

    <!-- ============================================================ -->
    <!-- MESAJE FLASH: afișează temporar notificări (succes, eroare, info)
         stocate în sesiune după redirect; se șterg automat la refresh -->
    <!-- ============================================================ -->
    <!-- Flash Messages -->
    <?php if ($flash): ?>
    <div class="flash-message flash-<?php echo $flash['type']; ?>" id="flashMessage">
        <div class="flash-content">
            <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : ($flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'); ?>"></i>
            <span><?php echo $flash['message']; ?></span>
            <button class="flash-close" onclick="closeFlash()"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- HEADER PRINCIPAL: conține logo-ul, navigația desktop,
         bara de căutare și meniul utilizatorului (autentificat sau nu) -->
    <!-- ============================================================ -->
    <!-- Header -->
    <header class="header" id="header">
        <div class="header-container">
            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                <i class="fas fa-play-circle"></i>
                <span><?php echo SITE_NAME; ?></span>
            </a>

            <!-- Navigation -->
            <nav class="main-nav" id="mainNav">
                <a href="<?php echo SITE_URL; ?>/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Home
                </a>
                <div class="nav-dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-th-large"></i> Genres <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="dropdown-menu genres-dropdown">
                        <?php foreach ($genres as $genre): ?>
                        <a href="<?php echo SITE_URL; ?>/index.php?genre=<?php echo $genre['slug']; ?>" class="dropdown-item">
                            <?php echo $genre['name']; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="<?php echo SITE_URL; ?>/index.php?status=ongoing" class="nav-link">
                    <i class="fas fa-broadcast-tower"></i> Ongoing
                </a>
                <a href="<?php echo SITE_URL; ?>/index.php?status=completed" class="nav-link">
                    <i class="fas fa-check-circle"></i> Completed
                </a>
                <a href="<?php echo SITE_URL; ?>/index.php?type=movie" class="nav-link">
                    <i class="fas fa-film"></i> Movies
                </a>
            </nav>

            <!-- ============================================================ -->
            <!-- BARA DE CĂUTARE: formular care trimite către search.php;
                 include și un dropdown pentru rezultate live via AJAX -->
            <!-- ============================================================ -->
            <!-- Search Bar -->
            <div class="search-container">
                <form action="<?php echo SITE_URL; ?>/search.php" method="GET" class="search-form" id="searchForm">
                    <input type="text" name="q" class="search-input" placeholder="Search anime..." 
                           value="<?php echo isset($_GET['q']) ? clean($_GET['q']) : ''; ?>" autocomplete="off" id="searchInput">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <div class="search-results-dropdown" id="searchDropdown"></div>
            </div>

            <!-- ============================================================ -->
            <!-- MENIUL UTILIZATORULUI: afișează avatarul și numele dacă este
                 autentificat, cu dropdown pentru Profil, Favorite, Admin, Logout;
                 altfel afișează butonul de Login -->
            <!-- ============================================================ -->
            <!-- User Menu -->
            <div class="user-menu">
                <?php if ($currentUser): ?>
                <div class="nav-dropdown">
                    <a href="#" class="user-toggle">
                        <img src="<?php echo UPLOAD_URL . $currentUser['avatar']; ?>?v=2" alt="<?php echo $currentUser['username']; ?>" class="user-avatar-small">
                        <span class="user-name"><?php echo $currentUser['username']; ?></span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="dropdown-menu user-dropdown">
                        <a href="<?php echo SITE_URL; ?>/profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="<?php echo SITE_URL; ?>/favorites.php" class="dropdown-item">
                            <i class="fas fa-heart"></i> Favorites
                        </a>
                        <?php if (isAdmin()): ?>
                        <a href="<?php echo SITE_URL; ?>/admin.php" class="dropdown-item">
                            <i class="fas fa-cog"></i> Admin Panel
                        </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo SITE_URL; ?>/api/logout.php" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/auth.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <?php endif; ?>
            </div>

            <!-- ============================================================ -->
            <!-- BUTON TOGGLE MOBILE: vizibil doar pe ecrane mici,
                 declanșează afișarea meniului de navigație mobil -->
            <!-- ============================================================ -->
            <!-- Mobile Menu Toggle -->
            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- ============================================================ -->
    <!-- MENIU MOBILE: navigație completă optimizată pentru telefoane,
         cu linkuri către secțiuni, genuri și opțiunile contului -->
    <!-- ============================================================ -->
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <span class="mobile-menu-title">Menu</span>
            <button class="mobile-menu-close" id="mobileMenuClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="mobile-nav">
            <a href="<?php echo SITE_URL; ?>/index.php" class="mobile-nav-link">
                <i class="fas fa-home"></i> Home
            </a>
            <div class="mobile-nav-section">
                <span class="mobile-nav-label">Genres</span>
                <?php foreach ($genres as $genre): ?>
                <a href="<?php echo SITE_URL; ?>/index.php?genre=<?php echo $genre['slug']; ?>" class="mobile-nav-link sub-link">
                    <?php echo $genre['name']; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo SITE_URL; ?>/index.php?status=ongoing" class="mobile-nav-link">
                <i class="fas fa-broadcast-tower"></i> Ongoing
            </a>
            <a href="<?php echo SITE_URL; ?>/index.php?status=completed" class="mobile-nav-link">
                <i class="fas fa-check-circle"></i> Completed
            </a>
            <a href="<?php echo SITE_URL; ?>/index.php?type=movie" class="mobile-nav-link">
                <i class="fas fa-film"></i> Movies
            </a>
            <?php if ($currentUser): ?>
            <div class="mobile-nav-divider"></div>
            <a href="<?php echo SITE_URL; ?>/profile.php" class="mobile-nav-link">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="<?php echo SITE_URL; ?>/favorites.php" class="mobile-nav-link">
                <i class="fas fa-heart"></i> Favorites
            </a>
            <?php if (isAdmin()): ?>
            <a href="<?php echo SITE_URL; ?>/admin.php" class="mobile-nav-link">
                <i class="fas fa-cog"></i> Admin Panel
            </a>
            <?php endif; ?>
            <a href="<?php echo SITE_URL; ?>/api/logout.php" class="mobile-nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <?php else: ?>
            <div class="mobile-nav-divider"></div>
            <a href="<?php echo SITE_URL; ?>/auth.php" class="mobile-nav-link">
                <i class="fas fa-sign-in-alt"></i> Login / Register
            </a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="overlay" id="overlay"></div>

    <!-- ============================================================ -->
    <!-- WRAPPER CONȚINUT PRINCIPAL: aici începe conținutul specific
         fiecărei pagini care include acest header -->
    <!-- ============================================================ -->
    <!-- Main Content Wrapper -->
    <main class="main-content">