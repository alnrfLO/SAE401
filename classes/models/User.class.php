<?php
// ─────────────────────────────────────────────────────────────
//  User Model — Gestion des utilisateurs FAV
//  Authentification, inscription, profil, admin
// ─────────────────────────────────────────────────────────────

class User
{
    /** @var PDO */
    private $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ─── NOUVELLES MÉTHODES (STATUT & BIO) ───────────────────

    /**
     * Met à jour le petit statut de l'utilisateur
     */
    public function updateStatus(int $userId, string $status): bool 
    {
        $sql = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id'     => $userId
        ]);
    }
    public function updateAvatar(int $userId, string $filename): bool 
    {
        $sql = "UPDATE users SET avatar = :avatar WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':avatar' => $filename,
            ':id'     => $userId
        ]);
    }
    /**
     * Met à jour la bio de l'utilisateur
     */
    public function updateBio(int $userId, string $bio): bool 
    {
        $sql = "UPDATE users SET bio = :bio WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':bio' => $bio,
            ':id'  => $userId
        ]);
    }
    // ─── INSCRIPTION ─────────────────────────────────────────

    /**
     * Crée un nouveau compte utilisateur
     * Le mot de passe est hashé automatiquement (bcrypt cost 12)
     */
    /**
     * @return int|false
     */
    public function register(string $username, string $email, string $password, string $country = '', string $language = 'fr')
    {
        // Vérification doublons avant insertion
        if ($this->emailExists($email)) return false;
        if ($this->usernameExists($username)) return false;

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $sql = "INSERT INTO users (username, email, password, country, language)
                VALUES (:username, :email, :password, :country, :language)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':username' => trim($username),
            ':email'    => strtolower(trim($email)),
            ':password' => $hash,
            ':country'  => $country,
            ':language' => $language,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    // ─── CONNEXION ───────────────────────────────────────────

    /**
     * Vérifie les credentials et retourne l'utilisateur ou false
     * Protection brute-force : utiliser un rate limiter en production
     */
    /**
     * @return array|false
     */
    public function login(string $email, string $password)
    {
        $sql  = "SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        if (!$user) return false;

        // password_verify() est résistant aux timing attacks
        if (!password_verify($password, $user['password'])) return false;

        // Rehash si besoin (changement de cost en production)
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $this->updatePassword($user['id'], $password);
        }

        // Ne jamais retourner le hash dans la session
        unset($user['password']);
        return $user;
    }

    // ─── SESSION ─────────────────────────────────────────────

    /**
     * Démarre une session sécurisée après login
     */
    public static function startSession(array $user)
    {
        $wasNone = (session_status() === PHP_SESSION_NONE);
        if ($wasNone) session_start();

        // Régénérer l'ID uniquement si on vient de démarrer la session
        // (évite les warnings "headers already sent" si session déjà active)
        if ($wasNone) {
            session_regenerate_id(true);
        }

        $_SESSION['user']    = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['logged']  = true;
    }

    /**
     * Déconnexion complète et sécurisée
     */
    public static function logout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Supprimer toutes les variables de session
        $_SESSION = [];

        // Détruire le cookie de session
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Vérifie si un utilisateur est connecté
     */
    public static function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['logged']) && $_SESSION['logged'] === true;
    }

    /**
     * Vérifie si l'utilisateur connecté est admin
     */
    public static function isAdmin(): bool
    {
        if (!self::isLoggedIn()) return false;
        return ($_SESSION['role'] ?? '') === 'admin';
    }

    /**
     * Récupère les infos de l'utilisateur connecté depuis la session
     */
    /**
     * @return array|null
     */
    public static function current()
    {
        if (!self::isLoggedIn()) return null;
        return $_SESSION['user'] ?? null;
    }

    // ─── PROFIL ──────────────────────────────────────────────

    /**
     * Récupère un user par son ID (sans le password)
     */
    /**
     * @return array|false
     */
    public function findById(int $id)
    {
        $sql  = "SELECT id, username, email, role, avatar, bio, country, language,
                        status,
                        is_active, email_verified, created_at, updated_at
                FROM users WHERE id = :id LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Récupère un user par son username
     */
    /**
     * @return array|false
     */
    public function findByUsername(string $username)
    {
        $sql  = "SELECT id, username, email, role, avatar, bio, country, language,
                        is_active, created_at
                 FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Met à jour le profil utilisateur
     */
    public function updateProfile(int $id, array $data): bool
    {
        $allowed = ['username', 'bio', 'country', 'language', 'avatar'];
        $fields  = [];
        $params  = [':id' => $id];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[]        = "`$field` = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $sql  = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Change le mot de passe d'un utilisateur
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->pdo->prepare("UPDATE users SET password = :hash WHERE id = :id");
        return $stmt->execute([':hash' => $hash, ':id' => $id]);
    }

    // ─── ADMIN ───────────────────────────────────────────────

    /**
     * Liste tous les utilisateurs (pour le panel admin)
     */
    public function getAllUsers(int $limit = 50, int $offset = 0): array
    {
        $sql  = "SELECT id, username, email, role, is_active, created_at
                 FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Banni ou réactive un compte utilisateur
     */
    public function toggleBan(int $userId, bool $ban): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = :active WHERE id = :id AND role != 'admin'");
        return $stmt->execute([':active' => $ban ? 0 : 1, ':id' => $userId]);
    }

    /**
     * Promeut un user en admin
     */
    public function promoteToAdmin(int $userId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET role = 'admin' WHERE id = :id");
        return $stmt->execute([':id' => $userId]);
    }

    // ─── STATISTIQUES PROFIL ─────────────────────────────────

    /**
     * Compte les spots, followers, following d'un utilisateur
     */
    public function getStats(int $userId): array
    {
        $sql = "SELECT
            (SELECT COUNT(*) FROM spots 
                WHERE user_id = :id1 AND status = 'published') AS spots_count,

            (SELECT COUNT(*) FROM follows 
                WHERE followed_id = :id2) AS followers_count,

            (SELECT COUNT(*) FROM follows 
                WHERE follower_id = :id3) AS following_count,

            (SELECT COUNT(*) FROM likes 
                WHERE spot_id IN (
                    SELECT id FROM spots WHERE user_id = :id4
                )) AS total_likes,

            (SELECT COUNT(*) FROM friendships
                WHERE status = 'accepted'
                AND (sender_id = :id5 OR receiver_id = :id6)
            ) AS friends_count
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id1' => $userId,
            ':id2' => $userId,
            ':id3' => $userId,
            ':id4' => $userId,
            ':id5' => $userId,
            ':id6' => $userId
        ]);

        return $stmt->fetch() ?: [
            'spots_count' => 0,
            'followers_count' => 0,
            'following_count' => 0,
            'total_likes' => 0,
            'friends_count' => 0
        ];
    }

    // ─── UTILITAIRES ─────────────────────────────────────────

    private function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => strtolower(trim($email))]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function usernameExists(string $username): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return (int) $stmt->fetchColumn() > 0;
    }
    /**
     * Mise à jour complète du profil
     */
    public function updateFullProfile(int $userId, array $data): bool 
    {
        $sql = "UPDATE users SET 
                username = :username, 
                email = :email, 
                bio = :bio, 
                country = :country, 
                language = :language";
        
        $params = [
            ':username' => $data['username'],
            ':email'    => strtolower($data['email']),
            ':bio'      => $data['bio'],
            ':country'  => $data['country'],
            ':language' => $data['language'],
            ':id'       => $userId
        ];

        // On ajoute le mot de passe à la requête seulement s'il a été rempli
        if ($data['password'] !== null) {
            $sql .= ", password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $sql .= " WHERE id = :id";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false; // Probablement email ou pseudo déjà pris
        }
    }
}
