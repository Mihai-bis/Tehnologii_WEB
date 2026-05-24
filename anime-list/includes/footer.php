    </main>

    <!-- ============================================================ -->
    <!-- FOOTER: structură în 4 coloane -->
    <!--   1. Brand & descriere + rețele sociale -->
    <!--   2. Quick Links (linkuri rapide către secțiuni populare) -->
    <!--   3. Genres (către categoriile principale de anime) -->
    <!--   4. Account (autentificare, înregistrare, favorite, profil) -->
    <!-- ============================================================ -->
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-section">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="footer-logo">
                        <i class="fas fa-play-circle"></i>
                        <span><?php echo SITE_NAME; ?></span>
                    </a>
                    <p class="footer-desc">Your ultimate destination for streaming anime online. Watch the latest episodes of your favorite series in high quality.</p>
                    <div class="footer-social">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-discord"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/index.php?status=ongoing">Ongoing Anime</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/index.php?status=completed">Completed Anime</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/index.php?type=movie">Movies</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Genres</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/index.php?genre=action">Action</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/index.php?genre=romance">Romance</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/index.php?genre=comedy">Comedy</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/index.php?genre=fantasy">Fantasy</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Account</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/auth.php">Login</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/auth.php?tab=register">Register</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/favorites.php">Favorites</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/profile.php">Profile</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- ============================================================ -->
            <!-- COPYRIGHT: afișează anul curent și numele site-ului,
                 plus o mențiune că este un proiect demonstrativ/educațional -->
            <!-- ============================================================ -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                <p class="footer-disclaimer">This is a demo project for educational purposes.</p>
            </div>
        </div>
    </footer>

    <!-- ============================================================ -->
    <!-- ÎNCĂRCARE JAVASCRIPT: fișierul principal cu logica interactivă
         (căutare live, meniu mobil, flash messages, favorite etc.) -->
    <!-- ============================================================ -->
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>