<?php
require_once __DIR__ . '/includes/functions.php';

// Dacă utilizatorul este deja autentificat, îl redirecționăm către pagina principală pentru a evita accesarea formularului de login
if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

$tab = isset($_GET['tab']) ? clean($_GET['tab']) : 'login';
$errors = [];
$success = '';

// Procesare autentificare (login): verificăm datele trimise prin POST și validăm credențialele
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $errors['login'] = 'Please fill in all fields.';
    } else {
        try {
            $db = getDB();
            // Căutăm utilizatorul după username sau email; folosit la logarea în aplicație
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            // Verificăm parola introdusă contra hash-ului din baza de date; folosit la autentificare
            if ($user && password_verify($password, $user['password'])) {
                // Setăm variabilele de sesiune pentru a marca utilizatorul ca autentificat; folosite în tot site-ul (header, favorite, recenzii)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                setFlash('success', 'Welcome back, ' . $user['username'] . '!');
                redirect(SITE_URL . '/index.php');
            } else {
                $errors['login'] = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $errors['login'] = 'An error occurred. Please try again.';
        }
    }
}

// Procesare înregistrare (register): validăm datele, verificăm unicitatea și inserăm noul utilizator în baza de date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = clean($_POST['username'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validare: lungime username, caractere permise, format email, lungime parolă și confirmare parolă
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errors['register'] = 'Please fill in all fields.';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors['register'] = 'Username must be between 3 and 20 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['register'] = 'Username can only contain letters, numbers, and underscores.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['register'] = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $errors['register'] = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $errors['register'] = 'Passwords do not match.';
    } else {
        try {
            $db = getDB();
            
            // Verificăm dacă username-ul sau email-ul există deja în baza de date
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $errors['register'] = 'Username or email already exists.';
            } else {
                // Hash-uim parola înainte de salvare pentru securitate; folosit la autentificare ulterioară
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Inserăm noul utilizator în tabela users; contul va putea fi folosit pentru login, favorite și recenzii
                $stmt = $db->prepare("
                    INSERT INTO users (username, email, password) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$username, $email, $hashedPassword]);
                
                $success = 'Registration successful! You can now log in.';
                $tab = 'login';
            }
        } catch (PDOException $e) {
            $errors['register'] = 'An error occurred. Please try again.';
        }
    }
}

$pageTitle = 'Login / Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-logo">
            <a href="<?php echo SITE_URL; ?>/index.php">
                <i class="fas fa-play-circle"></i>
                <span><?php echo SITE_NAME; ?></span>
            </a>
        </div>
        
        <div class="auth-box">
            <!-- Tabs -->
            <div class="auth-tabs">
                <button class="auth-tab <?php echo $tab === 'login' ? 'active' : ''; ?>" id="loginTab" onclick="switchAuthTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <button class="auth-tab <?php echo $tab === 'register' ? 'active' : ''; ?>" id="registerTab" onclick="switchAuthTab('register')">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </div>
            
            <!-- Success Message -->
            <?php if ($success): ?>
            <div class="flash-message flash-success" style="position: static; margin-bottom: 20px;">
                <div class="flash-content">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Formular HTML de autentificare (login): trimite datele către același fișier auth.php prin POST -->
            <form method="POST" action="" class="auth-form <?php echo $tab === 'login' ? 'active' : ''; ?>" id="loginForm">
                <input type="hidden" name="action" value="login">
                
                <?php if (isset($errors['login'])): ?>
                <div class="form-error" style="margin-bottom: 16px; padding: 10px; background: rgba(248, 113, 113, 0.1); border-radius: 6px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errors['login']; ?>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Username or Email</label>
                    <input type="text" name="username" class="form-input" placeholder="Enter your username or email" required 
                           value="<?php echo isset($_POST['username']) && $_POST['action'] === 'login' ? clean($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 10px;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <!-- Formular HTML de înregistrare (register): trimite datele către același fișier auth.php prin POST pentru crearea contului -->
            <form method="POST" action="" class="auth-form <?php echo $tab === 'register' ? 'active' : ''; ?>" id="registerForm">
                <input type="hidden" name="action" value="register">
                
                <?php if (isset($errors['register'])): ?>
                <div class="form-error" style="margin-bottom: 16px; padding: 10px; background: rgba(248, 113, 113, 0.1); border-radius: 6px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errors['register']; ?>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Username</label>
                    <input type="text" name="username" class="form-input" placeholder="Choose a username (3-20 characters)" required
                           value="<?php echo isset($_POST['username']) && $_POST['action'] === 'register' ? clean($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" class="form-input" placeholder="Enter your email" required
                           value="<?php echo isset($_POST['email']) && $_POST['action'] === 'register' ? clean($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Choose a password (min 6 characters)" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-input" placeholder="Confirm your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 10px;">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
                
                <p style="text-align: center; margin-top: 16px; font-size: 0.8rem; color: var(--text-muted);">
                    By registering, you agree to our Terms of Service and Privacy Policy.
                </p>
            </form>
        </div>
        
        <p style="text-align: center; margin-top: 24px; color: var(--text-muted); font-size: 0.85rem;">
            <a href="<?php echo SITE_URL; ?>/index.php" style="color: var(--primary);">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </p>
    </div>
    
    <script>
    // Funcție JavaScript care comută vizualizarea între tab-ul de Login și cel de Register în pagina auth.php
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
    </script>
</body>
</html>