/**
 * AnimeList - Main JavaScript
 * Gestionează interacțiunile AJAX, comportamentul UI și conținutul dinamic al aplicației.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inițializează toate componentele interfeței la încărcarea paginii
    initMobileMenu();
    initSearch();
    initFlashMessages();
    initDropdowns();
    initHeaderScroll();
    initHeroCarousel();
});

/**
 * ─── initMobileMenu ───
 * Inițializează comportamentul meniului mobil: deschidere/închidere prin click pe toggle,
 * overlay și butonul de închidere. Blochează scroll-ul pe body când meniul este deschis.
 * Folosit în header-ul aplicației pe dispozitive mobile.
 */
function initMobileMenu() {
    const mobileToggle = document.getElementById('mobileToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuClose = document.getElementById('mobileMenuClose');
    const overlay = document.getElementById('overlay');
    
    if (!mobileToggle || !mobileMenu) return;
    
    function openMenu() {
        mobileMenu.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeMenu() {
        mobileMenu.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    mobileToggle.addEventListener('click', openMenu);
    mobileMenuClose?.addEventListener('click', closeMenu);
    overlay?.addEventListener('click', closeMenu);
}

/**
 * ─── initSearch ───
 * Inițializează funcționalitatea de căutare live din header.
 * La fiecare tastare (minim 2 caractere) declanșează performSearch() cu debounce de 300ms.
 * Gestionează deschiderea/închiderea dropdown-ului de rezultate.
 * Folosit în bara de navigare pe toate paginile.
 */
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    
    if (!searchInput || !searchDropdown) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            searchDropdown.classList.remove('active');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            searchDropdown.classList.add('active');
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            searchDropdown.classList.remove('active');
        }
    });
}

/**
 * ─── performSearch ───
 * Trimite o cerere GET către api/search_ajax.php cu termenul de căutare.
 * Primește rezultatele JSON și apelează renderSearchResults() pentru a le afișa în dropdown.
 * Folosit de initSearch la tastarea în câmpul de căutare.
 */
function performSearch(query) {
    const searchDropdown = document.getElementById('searchDropdown');
    
    fetch(`api/search_ajax.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                renderSearchResults(data.data);
                searchDropdown.classList.add('active');
            } else {
                searchDropdown.innerHTML = '<div class="empty-state" style="padding: 20px;"><p>No results found</p></div>';
                searchDropdown.classList.add('active');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

/**
 * ─── renderSearchResults ───
 * Construiește HTML-ul pentru rezultatele căutării și îl injectează în dropdown-ul de căutare.
 * Fiecare rezultat este un link către pagina anime-ului respectiv.
 * Folosit de performSearch pentru afișarea rezultatelor live.
 */
function renderSearchResults(results) {
    const searchDropdown = document.getElementById('searchDropdown');
    
    const html = results.map(anime => `
        <a href="anime.php?slug=${anime.slug}" class="search-result-item">
            <img src="assets/images/${anime.cover_image}" alt="${anime.title}" 
                 onerror="this.src='assets/images/default-cover.jpg'">
            <div class="search-result-info">
                <h5>${anime.title}</h5>
                <span>${anime.type?.toUpperCase() || 'TV'} | ${anime.status || ''} | ${anime.rating || 'N/A'}</span>
            </div>
        </a>
    `).join('');
    
    searchDropdown.innerHTML = html;
}

/**
 * ─── initFlashMessages ───
 * Gestionează auto-dispărerea mesajelor flash (succes/eroare) după 5 secunde,
 * cu o animație de fade-out. Folosit pe paginile care afișează mesaje de confirmare sau eroare.
 */
function initFlashMessages() {
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            flashMessage.style.transform = 'translateX(100%)';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }
}

function closeFlash() {
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        flashMessage.style.opacity = '0';
        flashMessage.style.transform = 'translateX(100%)';
        setTimeout(() => flashMessage.remove(), 300);
    }
}

/**
 * ─── initDropdowns ───
 * Gestionează dropdown-urile din navigare pe dispozitive mobile.
 * Pe desktop sunt controlate prin CSS :hover, iar pe mobile (≤1024px) se deschid/închid la click.
 * Folosit în meniul principal de navigare.
 */
function initDropdowns() {
    // Desktop dropdowns are handled via CSS :hover
    // Mobile dropdowns need click handling
    
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                e.preventDefault();
                const parent = this.closest('.nav-dropdown');
                parent.classList.toggle('active');
            }
        });
    });
}

/**
 * ─── initHeaderScroll ───
 * Modifică transparența/opacitatea header-ului în funcție de poziția scroll-ului paginii.
 * Când scroll-ul depășește 100px, fundalul devine mai opac pentru lizibilitate.
 * Folosit pe toate paginile pentru efect vizual în header.
 */
function initHeaderScroll() {
    const header = document.getElementById('header');
    if (!header) return;
    
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            header.style.background = 'rgba(15, 15, 26, 0.98)';
        } else {
            header.style.background = 'rgba(15, 15, 26, 0.95)';
        }
        
        lastScroll = currentScroll;
    });
}

/**
 * ─── initHeroCarousel ───
 * Inițializează caruselul hero de pe pagina principală cu auto-play la fiecare 5 secunde,
 * navigare prin butoane prev/next, dots indicator, suport touch (swipe) și tastatură (săgeți).
 * Oprește auto-play la hover sau touch și îl reia la ieșire.
 * Folosit exclusiv în secțiunea hero de pe index.php.
 */
function initHeroCarousel() {
    const carousel = document.getElementById('heroCarousel');
    if (!carousel) return;
    
    const slides = carousel.querySelectorAll('.hero-slide');
    const dots = carousel.querySelectorAll('.hero-dot');
    const prevBtn = document.getElementById('heroPrev');
    const nextBtn = document.getElementById('heroNext');
    
    if (slides.length <= 1) return;
    
    let currentIndex = 0;
    let autoPlayInterval;
    
    function goToSlide(index) {
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;
        
        slides[currentIndex].classList.remove('active');
        dots[currentIndex]?.classList.remove('active');
        
        currentIndex = index;
        
        slides[currentIndex].classList.add('active');
        dots[currentIndex]?.classList.add('active');
    }
    
    function nextSlide() {
        goToSlide(currentIndex + 1);
    }
    
    function prevSlide() {
        goToSlide(currentIndex - 1);
    }
    
    function startAutoPlay() {
        autoPlayInterval = setInterval(nextSlide, 5000);
    }
    
    function stopAutoPlay() {
        clearInterval(autoPlayInterval);
    }
    
    // Event listeners
    prevBtn?.addEventListener('click', () => {
        prevSlide();
        stopAutoPlay();
        startAutoPlay();
    });
    
    nextBtn?.addEventListener('click', () => {
        nextSlide();
        stopAutoPlay();
        startAutoPlay();
    });
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            goToSlide(index);
            stopAutoPlay();
            startAutoPlay();
        });
    });
    
    // Pause on hover
    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);
    
    // Touch support
    let touchStartX = 0;
    let touchEndX = 0;
    
    carousel.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoPlay();
    }, { passive: true });
    
    carousel.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        startAutoPlay();
    }, { passive: true });
    
    function handleSwipe() {
        const diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        }
    }
    
    // Keyboard support
    document.addEventListener('keydown', (e) => {
        if (!carousel.matches(':hover')) return;
        if (e.key === 'ArrowLeft') {
            prevSlide();
            stopAutoPlay();
            startAutoPlay();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            stopAutoPlay();
            startAutoPlay();
        }
    });
    
    // Start auto-play
    startAutoPlay();
}

/**
 * AJAX Helper Functions
 */

/**
 * ─── toggleFavorite ───
 * Endpoint client care trimite o cerere POST către api/toggle_favorite.php
 * pentru a adăuga sau elimina un anime din lista de favorite a utilizatorului logat.
 * Actualizează iconița și stilul butonului și afișează o notificare toast.
 * Folosit pe cardurile anime din grile și pe pagina de detalii anime.
 */
function toggleFavorite(animeId, button) {
    const isLoggedIn = document.body.dataset.loggedIn === 'true';
    
    if (!isLoggedIn) {
        window.location.href = 'auth.php';
        return;
    }
    
    fetch('api/toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `anime_id=${animeId}&csrf_token=${getCsrfToken()}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button appearance
            const icon = button.querySelector('i');
            if (data.data.is_favorite) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.classList.add('active');
                showNotification('Added to favorites', 'success');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.classList.remove('active');
                showNotification('Removed from favorites', 'info');
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

/**
 * ─── getCsrfToken ───
 * Preia token-ul CSRF din meta tag-ul <meta name="csrf-token"> din pagină,
 * necesar pentru validarea cererilor AJAX POST (favorite, recenzii, admin etc.).
 * Folosit de funcțiile toggleFavorite, confirmDeleteAnime și altele.
 */
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.content : '';
}

/**
 * ─── showNotification ───
 * Afișează o notificare toast temporară în colțul din dreapta-sus al ecranului.
 * Suportă trei tipuri: success (verde), error (roșu) și info (albastru).
 * Dispare automat după 3 secunde cu animație de slide-out.
 * Folosit pe întreaga aplicație pentru feedback vizual rapid.
 */
function showNotification(message, type = 'info') {
    const existing = document.querySelector('.notification-toast');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 3000;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 20px;
        background: var(--bg-secondary, #1a1a2e);
        border-left: 4px solid ${type === 'success' ? '#4ade80' : type === 'error' ? '#f87171' : '#60a5fa'};
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.4);
        animation: slideIn 0.3s ease;
        font-size: 0.9rem;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}


/**
 * ─── openModal ───
 * Deschide un modal după ID-ul primit ca parametru, adăugând clasa 'active' pe overlay.
 * Blochează scroll-ul pe body pentru a preveni interacțiunea cu fundalul.
 * Folosit în panoul de admin pentru modalurile Add/Edit anime.
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * ─── closeModal ───
 * Închide un modal după ID-ul primit, eliminând clasa 'active' și redând scroll-ul pe body.
 * Folosit împreună cu openModal pentru gestionarea ferestrelor modale.
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Închide modalul la click pe overlay (în afara conținutului modalului)
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Tab switching for auth page
function switchAuthTab(tab) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    
    if (tab === 'login') {
        loginForm.classList.add('active');
        registerForm.classList.remove('active');
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
    } else {
        loginForm.classList.remove('active');
        registerForm.classList.add('active');
        loginTab.classList.remove('active');
        registerTab.classList.add('active');
    }
}

/**
 * ─── confirmDeleteAnime ───
 * Afișează o confirmare nativă (confirm) înainte de a trimite o cerere POST
 * către api/delete_anime.php pentru ștergerea unui anime. Dacă ștergerea reușește,
 * elimină rândul corespunzător din tabelul admin fără reîncărcarea paginii.
 * Folosit exclusiv în panoul de administrare, în coloana Actions a tabelului anime.
 */
function confirmDeleteAnime(animeId, animeTitle) {
    if (confirm(`Are you sure you want to delete "${animeTitle}"? This action cannot be undone.`)) {
        fetch('api/delete_anime.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `anime_id=${animeId}&csrf_token=${getCsrfToken()}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Anime deleted successfully', 'success');
                // Remove the row from table
                const row = document.querySelector(`[data-anime-id="${animeId}"]`);
                if (row) row.remove();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
    }
}

/**
 * ─── openEditModal ───
 * Preia datele unui anime prin api/get_anime.php și populează câmpurile din modalul de editare,
 * inclusiv checkbox-urile genurilor și preview-urile imaginilor curente.
 * La final deschide modalul de editare prin openModal().
 * Folosit exclusiv în panoul de administrare, la click pe butonul de edit din tabel.
 */
function openEditModal(animeId) {
    fetch(`api/get_anime.php?id=${animeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const anime = data.data;
                document.getElementById('edit_anime_id').value = anime.id;
                document.getElementById('edit_title').value = anime.title;
                document.getElementById('edit_description').value = anime.description || '';
                document.getElementById('edit_synopsis').value = anime.synopsis || '';
                document.getElementById('edit_status').value = anime.status;
                document.getElementById('edit_type').value = anime.type;
                document.getElementById('edit_episodes_count').value = anime.episodes_count;
                document.getElementById('edit_rating').value = anime.rating;
                document.getElementById('edit_release_year').value = anime.release_year || '';
                document.getElementById('edit_studio').value = anime.studio || '';
                document.getElementById('edit_duration').value = anime.duration || '';
                
                // Set genres
                document.querySelectorAll('.edit-genre-checkbox').forEach(cb => {
                    cb.checked = anime.genre_ids && anime.genre_ids.includes(parseInt(cb.value));
                });
                
                // Set image previews
                const coverPreview = document.getElementById('edit_cover_preview');
                const bannerPreview = document.getElementById('edit_banner_preview');
                
                if (coverPreview) {
                    if (anime.cover_image && anime.cover_image !== 'default-cover.jpg') {
                        coverPreview.src = 'assets/images/' + anime.cover_image;
                        coverPreview.style.display = 'block';
                    } else {
                        coverPreview.style.display = 'none';
                    }
                }
                if (bannerPreview) {
                    if (anime.banner_image && anime.banner_image !== 'default-banner.jpg') {
                        bannerPreview.src = 'assets/images/' + anime.banner_image;
                        bannerPreview.style.display = 'block';
                    } else {
                        bannerPreview.style.display = 'none';
                    }
                }
                
                openModal('editAnimeModal');
            }
        })
        .catch(error => console.error('Error:', error));
}

/**
 * ─── validateForm ───
 * Validează în mod dinamic un formular după ID, verificând că toate câmpurile
 * marcate cu [required] au o valoare completată. Afișează mesaje de eroare
 * sub câmpurile necompletate și evidențiază border-ul în roșu.
 * Folosit în formularele de autentificare și în modalurile admin.
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = 'var(--error)';
            
            // Add error message
            let errorMsg = field.nextElementSibling;
            if (!errorMsg || !errorMsg.classList.contains('form-error')) {
                errorMsg = document.createElement('div');
                errorMsg.className = 'form-error';
                field.parentNode.insertBefore(errorMsg, field.nextSibling);
            }
            errorMsg.textContent = 'This field is required';
        } else {
            field.style.borderColor = '';
            const errorMsg = field.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('form-error')) {
                errorMsg.remove();
            }
        }
    });
    
    return isValid;
}

/**
 * ─── initLazyLoad ───
 * Implementează încărcarea leneșă (lazy loading) a imaginilor care au atributul data-src,
 * folosind IntersectionObserver. Imaginea se încarcă efectiv doar când intră în viewport.
 * Folosit pe paginile cu multe imagini (ex: grile de anime) pentru performanță îmbunătățită.
 */
function initLazyLoad() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
}

// Initialize lazy load if images have data-src
if (document.querySelector('img[data-src]')) {
    initLazyLoad();
}
