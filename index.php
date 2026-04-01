<?php
define('CHARGE_AUTOLOAD', true);
require_once 'inc/autoload.inc.php';
require_once 'inc/database.php';

if (session_status() == PHP_SESSION_NONE) session_start();

// ─── LOGOUT ──────────────────────────────────────────────────
if (isset($_GET['page']) && $_GET['page'] === 'logout') {
    User::logout();
    $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST']
          . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
    header('Location: ' . $base . '?page=home');
    exit;
}

// ─── ROUTAGE ─────────────────────────────────────────────────
$page = $_GET['page'] ?? 'home';

// ─── LANGUES ─────────────────────────────────────────────────
$lang = is_string($_GET['lang'] ?? null) ? $_GET['lang'] : ($_SESSION['lang'] ?? 'en');
$_SESSION['lang'] = $lang;
$langFile = 'lang/' . $lang . '.php';
require_once (file_exists($langFile)) ? $langFile : 'lang/en.php';

// ─── ACTIONS AJAX ────────────────────────────────────────────
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if (!User::isLoggedIn()) {
        if (in_array($action, ['updateStatus', 'updateBio', 'updateAvatar', 'create'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Non connecté']);
            exit;
        }
    }

    $userModel = new User($pdo);
    $userId = $_SESSION['user_id'] ?? null;

    if ($action === 'saveFullProfile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'bio'      => $_POST['bio'] ?? '',
            'country'  => $_POST['country'] ?? '',
            'language' => $_POST['language'] ?? 'en',
            'password' => !empty($_POST['new_password']) ? $_POST['new_password'] : null
        ];
        if ($userModel->updateFullProfile($userId, $data)) {
            header('Location: ?page=profile&status=success');
        } else {
            header('Location: ?page=profile&action=edit&error=1');
        }
        exit;
    }

    if ($action === 'updateStatus' && isset($_POST['status'])) {
        header('Content-Type: application/json');
        $status = mb_substr(trim($_POST['status']), 0, 100);
        echo json_encode(['success' => $userModel->updateStatus($userId, $status)]);
        exit;
    }

    if ($action === 'updateBio' && isset($_POST['bio'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $userModel->updateBio($userId, $_POST['bio'])]);
        exit;
    }

    if ($action === 'updateAvatar' && isset($_FILES['avatar'])) {
        header('Content-Type: application/json');
        $file = $_FILES['avatar'];
        $maxSize = 2 * 1024 * 1024;
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if ($file['size'] > $maxSize || !in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Fichier invalide']);
            exit;
        }

        $uploadDir = 'uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $userModel->updateAvatar($userId, $destPath);
            echo json_encode(['success' => true, 'path' => $destPath]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur upload']);
        }
        exit;
    }

    // AJOUT D'UN SPOT
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');

        $input = [
            'title'       => $_POST['title'] ?? '',
            'location'    => $_POST['location'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category'    => $_POST['category'] ?? ''
        ];

        if (empty($input['title']) || empty($input['location'])) {
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            exit;
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $maxSize = 5 * 1024 * 1024; // 5MB limit
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if ($file['size'] <= $maxSize && in_array($file['type'], $allowedTypes)) {
                $uploadDir = 'uploads/spots/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFilename = 'spot_' . $userId . '_' . time() . '.' . $ext;
                $destPath = $uploadDir . $newFilename;

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $input['image'] = $destPath;
                }
            }
        }

        $spotModel = new Spot($pdo);
        $spotId = $spotModel->create($userId, $input);
        if ($spotId) {
            echo json_encode(['success' => true, 'id' => $spotId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur de création']);
        }
        exit;
    }
}

// ─── INSCRIPTION ─────────────────────────────────────────────

$registerError = null;
if ($page === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new User($pdo);
    $res = $userModel->register(
        trim($_POST['pseudo'] ?? ''),
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? '',
        $_POST['pays'] ?? '',
        'en'  // ✅ valeur fixe pour l'instant, on règle $lang après
    );
$langRaw = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
$lang = is_array($langRaw) ? 'en' : (string)$langRaw;
    if (is_int($res)) {
        $newUser = $userModel->findById($res);
        User::startSession($newUser);
        header('Location: ?page=profile');
        exit;
    } else {
        $registerError = "Pseudo ou email déjà utilisé.";
    }
}

// ─── LOGIN ───────────────────────────────────────────────────
$loginError = null;
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new User($pdo);
    $user = $userModel->login(
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? ''
    );
    if ($user) {
        User::startSession($user);
        header('Location: ?page=profile');
        exit;
    } else {
        $loginError = "Email ou mot de passe incorrect.";
        $GLOBALS['loginError'] = $loginError;
    }
}

// ─── SWITCH PAGES ────────────────────────────────────────────
switch ($page) {
    case 'home':
        $view = new Home(['page' => 'home']);
    break;

    case 'login':
        $view = new Login(['page' => 'login']);
    break;

    case 'register':
        $view = new Register(['page' => 'register', 'error' => $registerError]);
    break;

    case 'profile':
        if (!User::isLoggedIn()) {
            header('Location: ?page=login');
            exit;
        }
        $userModel = new User($pdo);
        $userData  = $userModel->findById($_SESSION['user_id']);

        if (isset($_GET['action']) && $_GET['action'] === 'edit') {
            $view = new EditProfile(['user' => $userData]);
        } else {
            $profileStats = $userModel->getStats($_SESSION['user_id']);
            $spotModel = new Spot($pdo);
            $userSpots = $spotModel->getByUser($_SESSION['user_id'], 6);
            $view = new Profile([
                'page'         => 'profile',
                'profileUser'  => $userData,
                'profileStats' => $profileStats,
                'userSpots'    => $userSpots
            ]);
        }
    break;

    case 'spots':
        if (!User::isLoggedIn()) { header('Location: ?page=login'); exit; }
        $userModel = new User($pdo);
        $userData  = $userModel->findById($_SESSION['user_id']);
        $spotModel = new Spot($pdo);
        $allSpots  = $spotModel->getFeed();
        $view = new Spots(['page' => 'spots', 'profileUser' => $userData, 'spots' => $allSpots]);
    break;

    case 'spot':
        $spotId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $spotModel = new Spot($pdo);
        $spotData = $spotModel->findById($spotId);
        $view = new SingleSpot(['page' => 'spot', 'spot' => $spotData]);
    break;

    case 'admin':
        if (!User::isLoggedIn() || !User::isAdmin()) {
            header('Location: ?page=home');
            exit;
        }
        $view = new Admin(['page' => 'admin']);
    break;
    case 'dashboard':
    if (!User::isLoggedIn()) { header('Location: ?page=login'); exit; }
    $userModel = new User($pdo);
    $userData  = $userModel->findById($_SESSION['user_id']);
    $profileStats = $userModel->getStats($_SESSION['user_id']);
    $view = new Dashboard(['profileUser' => $userData, 'profileStats' => $profileStats]);
    break;

    case 'agenda':
        if (!User::isLoggedIn()) { header('Location: ?page=login'); exit; }
        $userModel = new User($pdo);
        $userData  = $userModel->findById($_SESSION['user_id']);
        $view = new Agenda(['profileUser' => $userData]);
    break;

    case 'messages':
        if (!User::isLoggedIn()) { header('Location: ?page=login'); exit; }
        $userModel = new User($pdo);
        $userData  = $userModel->findById($_SESSION['user_id']);
        $view = new Messages(['profileUser' => $userData]);
    break;
    
    case 'discover':
        $spotModel = new Spot($pdo);
        $allSpots  = $spotModel->getFeed(500, 0); // 500 spots max pour la map
        $view = new Discover(['page' => 'discover', 'spots' => $allSpots]);
    break;

    case 'directory':
    $view = new Directory(['page' => 'directory']);
    break;

    case 'about':
    case 'legal':
    case 'terms':
    case 'privacy':
    $view = new LegalPages(['page' => $page]);
    break;

    default:
        $view = new NotFound(['page' => 'error']);
    break;
}

echo $view->render();