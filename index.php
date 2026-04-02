<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

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

    // ── RECHERCHE D'UTILISATEURS (Connections) ──────────────
    if ($action === 'searchUsers') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode([]); exit; }
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) { echo json_encode([]); exit; }
        $friendshipModel = new Friendship($pdo);
        echo json_encode($friendshipModel->searchUsers($_SESSION['user_id'], $q));
        exit;
    }
 
    // ── ACTIONS AMIS : sendFriendRequest / friendAction ─────
    if ($action === 'sendFriendRequest' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Non connecté']); exit; }
        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        if (!$receiverId) { echo json_encode(['success'=>false,'error'=>'ID invalide']); exit; }
        $friendshipModel = new Friendship($pdo);
        $ok = $friendshipModel->sendRequest($_SESSION['user_id'], $receiverId);
        echo json_encode(['success' => $ok, 'error' => $ok ? null : 'Déjà envoyé ou relation existante']);
        exit;
    }
 
    if ($action === 'friendAction' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Non connecté']); exit; }
        $type = $_POST['type'] ?? '';
        $friendshipId = (int)($_POST['friendship_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        $friendshipModel = new Friendship($pdo);
        $ok = false; $message = '';
        switch ($type) {
            case 'accept':
                $ok = $friendshipModel->acceptRequest($friendshipId, $userId);
                $message = '✅ Friend request accepted!';
                break;
            case 'decline':
            case 'cancel':
                $ok = $friendshipModel->deleteRequest($friendshipId, $userId);
                $message = $type === 'cancel' ? '↩️ Request cancelled.' : '❌ Request declined.';
                break;
            case 'remove':
                // On récupère l'autre user_id depuis la DB
                $stmt = $pdo->prepare('SELECT sender_id, receiver_id FROM friendships WHERE id = ?');
                $stmt->execute([$friendshipId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $otherId = ($row['sender_id'] == $userId) ? $row['receiver_id'] : $row['sender_id'];
                    $ok = $friendshipModel->removeFriend($userId, $otherId);
                    $message = '👋 Friend removed.';
                }
                break;
        }
        echo json_encode(['success' => $ok, 'message' => $message]);
        exit;
    }


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


    // ── LISTE DES CONVERSATIONS ──────────────────────────────────
    if ($action === 'getConversations') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode([]); exit; }
        $messaging = new Message($pdo);
        echo json_encode($messaging->getConversations($_SESSION['user_id']));
        exit;
    }
    
    // ── DÉTAILS D'UNE CONVERSATION ───────────────────────────────
    if ($action === 'getConvInfo') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(null); exit; }
        $convId = (int)($_GET['conv_id'] ?? 0);
        $messaging = new Message($pdo);
        echo json_encode($messaging->getConversation($convId, $_SESSION['user_id']));
        exit;
    }
    
    // ── MESSAGES D'UNE CONVERSATION ─────────────────────────────
    if ($action === 'getMessages') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode([]); exit; }
        $convId = (int)($_GET['conv_id'] ?? 0);
        $before = (int)($_GET['before'] ?? 0);
        $messaging = new Message($pdo);
        echo json_encode($messaging->getMessages($convId, $_SESSION['user_id'], 50, $before));
        exit;
    }
    
    // ── POLLING : NOUVEAUX MESSAGES ──────────────────────────────
    if ($action === 'pollMessages') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['messages' => []]); exit; }
        $convId  = (int)($_GET['conv_id'] ?? 0);
        $afterId = (int)($_GET['after_id'] ?? 0);
        $messaging = new Message($pdo);
        $msgs = $messaging->getNewMessages($convId, $_SESSION['user_id'], $afterId);
        echo json_encode(['messages' => $msgs]);
        exit;
    }
    
    // ── ENVOYER UN MESSAGE ───────────────────────────────────────
    if ($action === 'sendMessage' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success' => false]); exit; }
        $convId  = (int)($_POST['conv_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if (!$convId || !$content) { echo json_encode(['success' => false]); exit; }
        $messaging = new Message($pdo);
        $msg = $messaging->sendMessage($convId, $_SESSION['user_id'], $content);
        echo json_encode(['success' => (bool)$msg, 'message' => $msg]);
        exit;
    }
    
    // ── OUVRIR / CRÉER UNE CONVERSATION DIRECTE ─────────────────
    if ($action === 'openDirect' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success' => false]); exit; }
        $targetId = (int)($_POST['target_id'] ?? 0);
        if (!$targetId) { echo json_encode(['success' => false]); exit; }
        $messaging = new Message($pdo);
        $convId = $messaging->getOrCreateDirect($_SESSION['user_id'], $targetId);
        echo json_encode(['success' => true, 'conv_id' => $convId]);
        exit;
    }
    
    // ── CRÉER UN GROUPE ──────────────────────────────────────────
    if ($action === 'createGroup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success' => false]); exit; }
        $name      = trim($_POST['group_name'] ?? '');
        $memberIds = array_map('intval', $_POST['member_ids'] ?? []);
        if (!$name || empty($memberIds)) {
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            exit;
        }
        $messaging = new Message($pdo);
        $convId = $messaging->createGroup($_SESSION['user_id'], $name, $memberIds);
        echo json_encode(['success' => true, 'conv_id' => $convId]);
        exit;
    }
    
    // ── QUITTER UNE CONVERSATION ─────────────────────────────────
    if ($action === 'leaveConversation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success' => false]); exit; }
        $convId = (int)($_POST['conv_id'] ?? 0);
        $messaging = new Message($pdo);
        $ok = $messaging->leaveConversation($convId, $_SESSION['user_id']);
        echo json_encode(['success' => $ok]);
        exit;
    }

    // ── AGENDA API ─────────────────────────────────────────────
    if ($action === 'getAgendaEvents' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Non connecté']); exit; }

        $year  = max(1970, min(9999, (int)($_GET['year'] ?? date('Y'))));
        $month = max(1, min(12, (int)($_GET['month'] ?? date('m'))));

        $eventModel = new Event($pdo);
        $events = $eventModel->getByMonth($_SESSION['user_id'], $year, $month);

        foreach ($events as &$e) {
            $e['event_date'] = date('Y-m-d H:i', strtotime($e['event_date']));
        }

        echo json_encode(['success'=>true,'events'=>$events]);
        exit;
    }

    if ($action === 'createAgendaEvent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Non connecté']); exit; }

        $title = trim($_POST['title'] ?? '');
        $date  = trim($_POST['date'] ?? '');
        $start = trim($_POST['start'] ?? '00:00');
        $end   = trim($_POST['end'] ?? '00:00');
        $type  = $_POST['type'] ?? 'private';
        $notes = trim($_POST['notes'] ?? '');

        if (!$title || !$date || !$start) {
            echo json_encode(['success'=>false,'error'=>'Titre, date, heure de début requis']);
            exit;
        }

        $eventDate = date('Y-m-d H:i:s', strtotime($date . ' ' . $start));
        $description = $notes;
        if ($end) {
            $description = "[end:" . $end . "] " . $description;
        }

        $eventModel = new Event($pdo);
        $eventId = $eventModel->create($_SESSION['user_id'], [
            'title' => $title,
            'description' => $description,
            'location' => trim($_POST['location'] ?? ''),
            'event_date' => $eventDate,
            'type' => $type,
            'status' => 'accepted'
        ]);

        if ($eventId) {
            echo json_encode(['success'=>true,'event_id'=>$eventId]);
        } else {
            echo json_encode(['success'=>false,'error'=>'Impossible de créer l\'événement']);
        }
        exit;
    }

    if ($action === 'updateAgendaEvent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Non connecté']); exit; }

        $eventId = (int)($_POST['event_id'] ?? 0);
        if (!$eventId) { echo json_encode(['success'=>false,'error'=>'ID événement invalide']); exit; }

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'type' => $_POST['type'] ?? 'private',
            'status' => $_POST['status'] ?? 'accepted',
            'description' => trim($_POST['notes'] ?? ''),
        ];

        $date = trim($_POST['date'] ?? '');
        $start = trim($_POST['start'] ?? '00:00');
        if ($date && $start) {
            $data['event_date'] = date('Y-m-d H:i:s', strtotime($date . ' ' . $start));
        }

        $eventModel = new Event($pdo);
        $ok = $eventModel->update($eventId, $_SESSION['user_id'], $data);
        echo json_encode(['success' => $ok]);
        exit;
    }

    if ($action === 'deleteAgendaEvent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Non connecté']); exit; }

        $eventId = (int)($_POST['event_id'] ?? 0);
        if (!$eventId) { echo json_encode(['success'=>false,'error'=>'ID événement invalide']); exit; }

        $eventModel = new Event($pdo);
        $ok = $eventModel->delete($eventId, $_SESSION['user_id']);
        echo json_encode(['success' => $ok]);
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

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'error' => 'Upload error code '. $file['error']]);
                exit;
            }

            $maxSize = 5 * 1024 * 1024; // 5MB limit
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if ($file['size'] > $maxSize || !in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'error' => 'Type ou taille de l\'image invalide']);
                exit;
            }

            $uploadDir = __DIR__ . '/uploads/spots/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                    echo json_encode(['success' => false, 'error' => 'Impossible de créer le dossier uploads/spots']);
                    exit;
                }
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFilename = 'spot_' . $userId . '_' . time() . '.' . $ext;
            $destPathLocal = $uploadDir . $newFilename;

            if (!move_uploaded_file($file['tmp_name'], $destPathLocal)) {
                echo json_encode(['success' => false, 'error' => 'Impossible de déplacer le fichier image']);
                exit;
            }

            $input['image'] = 'uploads/spots/' . $newFilename;
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

    // ── LIKE / UNLIKE UN SPOT ─────────────────────────────────────
    if ($action === 'toggleLikeSpot' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success' => false, 'error' => 'Not logged in']); exit; }
        $spotId = (int)($_POST['spot_id'] ?? 0);
        if (!$spotId) { echo json_encode(['success' => false, 'error' => 'Invalid ID']); exit; }
        $spotModel = new Spot($pdo);
        $isLiked = $spotModel->toggleLike($spotId, $_SESSION['user_id']);
        $newCount = (int)$pdo->query("SELECT COUNT(*) FROM likes WHERE spot_id = $spotId")->fetchColumn();
        echo json_encode(['success' => true, 'isLiked' => $isLiked, 'likes_count' => $newCount]);
        exit;
    }

    // ── POSTER UN COMMENTAIRE ────────────────────────────────────
    if ($action === 'postComment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!User::isLoggedIn()) { echo json_encode(['success' => false, 'error' => 'Not logged in']); exit; }
        $spotId  = (int)($_POST['spot_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if (!$spotId || !$content) { echo json_encode(['success' => false, 'error' => 'Invalid data']); exit; }
        $commentModel = new Comment($pdo);
        $commentId = $commentModel->create($spotId, $_SESSION['user_id'], $content);
        if ($commentId) {
            echo json_encode(['success' => true, 'comment_id' => $commentId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error creating comment']);
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
        $userModel      = new User($pdo);
        $currentUserId  = (int)$_SESSION['user_id'];

        // Determine whose profile to show: ?id=X for another user, own profile otherwise
        $targetId = isset($_GET['id']) ? (int)$_GET['id'] : $currentUserId;
        if ($targetId <= 0) $targetId = $currentUserId;

        $userData = $userModel->findById($targetId);
        if (!$userData) {
            header('Location: ?page=home');
            exit;
        }

        $isOwn = ($targetId === $currentUserId);

        // Only the owner can reach the edit form
        if ($isOwn && isset($_GET['action']) && $_GET['action'] === 'edit') {
            $view = new EditProfile(['user' => $userData]);
        } else {
            $profileStats    = $userModel->getStats($targetId);
            $spotModel       = new Spot($pdo);
            $userSpots       = $spotModel->getByUser($targetId, 6);

            // Friendship relation between current user and target (null if own profile)
            $friendRelation  = null;
            if (!$isOwn) {
                $friendshipModel = new Friendship($pdo);
                $friendRelation  = $friendshipModel->getRelation($currentUserId, $targetId);
            }

            $view = new Profile([
                'page'           => 'profile',
                'profileUser'    => $userData,
                'profileStats'   => $profileStats,
                'userSpots'      => $userSpots,
                'isOwn'          => $isOwn,
                'friendRelation' => $friendRelation,
                'currentUserId'  => $currentUserId,
                'fullWidth'      => true
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
        
        $commentModel = new Comment($pdo);
        $comments = $commentModel->getBySpot($spotId);
        
        $isLiked = false;
        if (User::isLoggedIn()) {
            $isLiked = $spotModel->isLikedBy($spotId, $_SESSION['user_id']);
        }
        
        $view = new SingleSpot([
            'page'     => 'spot',
            'spot'     => $spotData,
            'comments' => $comments,
            'isLiked'  => $isLiked
        ]);
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
    $view = new Dashboard(['profileUser' => $userData, 'profileStats' => $profileStats, 'fullWidth' => true, 'showFooter' => false, 'showHeader' => false]);
    break;

    case 'agenda':
        if (!User::isLoggedIn()) { header('Location: ?page=login'); exit; }
        $userModel = new User($pdo);
        $userData  = $userModel->findById($_SESSION['user_id']);
        $view = new Agenda(['profileUser' => $userData, 'fullWidth' => true, 'showFooter' => false, 'showHeader' => false]);
    break;

    case 'messages':
        if (!User::isLoggedIn()) { header('Location: ?page=login'); exit; }
        $userModel = new User($pdo);
        $userData  = $userModel->findById($_SESSION['user_id']);
        $view = new Messages(['profileUser' => $userData, 'fullWidth' => true, 'showFooter' => false, 'showHeader' => false]);
    break;
    
    case 'discover':
        $spotModel = new Spot($pdo);
        $allSpots  = $spotModel->getFeed(500, 0); // 500 spots max pour la map
        $view = new Discover(['page' => 'discover', 'spots' => $allSpots]);
    break;

    case 'directory':
    $view = new Directory(['page' => 'directory']);
    break;

    case 'news':
        $newsModel = new NewsModel($pdo);
        $newsItems = $newsModel->getAll();
        $view = new News(['page' => 'news', 'newsItems' => $newsItems]);
    break;

    case 'article':
        $articleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $newsModel = new NewsModel($pdo);
        $articleData = $newsModel->findById($articleId);
        
        if (!$articleData) {
            header('Location: index.php?page=news');
            exit;
        }
        
        $view = new SingleNews(['page' => 'article', 'article' => $articleData]);
    break;


    case 'connections':
        if (!User::isLoggedIn()) { header('Location: ?page=login'); exit; }
        $userModel = new User($pdo);
        $userData  = $userModel->findById($_SESSION['user_id']);
        $friendshipModel = new Friendship($pdo);
        $view = new Connections([
            'profileUser' => $userData,
            'friends'     => $friendshipModel->getFriends($_SESSION['user_id']),
            'received'    => $friendshipModel->getReceivedRequests($_SESSION['user_id']),
            'sent'        => $friendshipModel->getSentRequests($_SESSION['user_id']),
            'fullWidth'   => true,
            'showFooter'  => false,
            'showHeader'  => false
        ]);
    break;

    case 'about':
    case 'legal':
    case 'terms':
    case 'privacy':
    case 'faq':
    case 'user-agreement':
    case 'tos':
    $view = new LegalPages(['page' => $page]);
    break;

    default:
        $view = new NotFound(['page' => 'error']);
    break;
}

echo $view->render();