<?php
// ─────────────────────────────────────────────────────────────
//  Spot Model — Gestion des publications FAV
//  CRUD, feed, recherche, likes, tags
// ─────────────────────────────────────────────────────────────

class Spot
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ─── CRÉATION ────────────────────────────────────────────

    /**
     * Crée un nouveau spot
     * Retourne l'ID du spot créé
     * @return int|false
     */
    public function create(int $userId, array $data)
    {
        $sql = "INSERT INTO spots (user_id, title, description, location, latitude, longitude, image, category, status)
                VALUES (:user_id, :title, :description, :location, :latitude, :longitude, :image, :category, :status)";

        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([
            ':user_id'     => $userId,
            ':title'       => trim($data['title']),
            ':description' => trim($data['description']),
            ':location'    => $data['location']  ?? null,
            ':latitude'    => $data['latitude']  ?? null,
            ':longitude'   => $data['longitude'] ?? null,
            ':image'       => $data['image']     ?? null,
            ':category'    => $data['category']  ?? 'autre',
            ':status'      => $data['status']    ?? 'published',
        ]);

        if (!$ok) return false;

        $spotId = (int) $this->pdo->lastInsertId();

        // Attacher des tags si fournis
        if (!empty($data['tags']) && is_array($data['tags'])) {
            $this->attachTags($spotId, $data['tags']);
        }

        return $spotId;
    }

    // ─── LECTURE ─────────────────────────────────────────────

    /**
     * Récupère un spot par son ID avec infos auteur et compteurs
     * @return array|false
     */
    public function findById(int $id)
    {
        $sql = "SELECT s.*,
                       u.username, u.avatar,
                       (SELECT COUNT(*) FROM likes    WHERE spot_id = s.id) AS likes_count,
                       (SELECT COUNT(*) FROM comments WHERE spot_id = s.id AND is_hidden = 0) AS comments_count
                FROM spots s
                JOIN users u ON u.id = s.user_id
                WHERE s.id = :id AND s.status = 'published'
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $spot = $stmt->fetch();

        if ($spot) {
            // Incrémenter les vues
            $this->incrementViews($id);
            // Récupérer les tags
            $spot['tags'] = $this->getTagsForSpot($id);
        }

        return $spot;
    }

    /**
     * Feed principal — tous les spots publiés (ordre chronologique inversé)
     */
    public function getFeed(int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT s.id, s.title, s.description, s.image, s.location, s.category,
                       s.views_count, s.created_at,
                       u.id AS user_id, u.username, u.avatar,
                       (SELECT COUNT(*) FROM likes    WHERE spot_id = s.id) AS likes_count,
                       (SELECT COUNT(*) FROM comments WHERE spot_id = s.id AND is_hidden = 0) AS comments_count
                FROM spots s
                JOIN users u ON u.id = s.user_id
                WHERE s.status = 'published' AND u.is_active = 1
                ORDER BY s.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Feed personnalisé — spots des users suivis
     */
    public function getFollowingFeed(int $userId, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT s.id, s.title, s.description, s.image, s.location, s.category,
                       s.views_count, s.created_at,
                       u.id AS user_id, u.username, u.avatar,
                       (SELECT COUNT(*) FROM likes    WHERE spot_id = s.id) AS likes_count,
                       (SELECT COUNT(*) FROM comments WHERE spot_id = s.id AND is_hidden = 0) AS comments_count
                FROM spots s
                JOIN users u ON u.id = s.user_id
                JOIN follows f ON f.followed_id = s.user_id AND f.follower_id = :user_id
                WHERE s.status = 'published' AND u.is_active = 1
                ORDER BY s.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit',   $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',  $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Spots d'un utilisateur spécifique (profil public)
     */
    public function getByUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT s.id, s.title, s.description, s.image, s.location, s.category,
                       s.views_count, s.created_at,
                       (SELECT COUNT(*) FROM likes    WHERE spot_id = s.id) AS likes_count,
                       (SELECT COUNT(*) FROM comments WHERE spot_id = s.id AND is_hidden = 0) AS comments_count
                FROM spots s
                WHERE s.user_id = :user_id AND s.status = 'published'
                ORDER BY s.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit',   $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',  $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Recherche full-text dans les spots
     */
    public function search(string $query, int $limit = 20): array
    {
        $term = '%' . $query . '%';
        $sql  = "SELECT s.id, s.title, s.description, s.image, s.location, s.category, s.created_at,
                        u.username, u.avatar,
                        (SELECT COUNT(*) FROM likes WHERE spot_id = s.id) AS likes_count
                 FROM spots s
                 JOIN users u ON u.id = s.user_id
                 WHERE s.status = 'published' AND u.is_active = 1
                   AND (s.title LIKE :term OR s.description LIKE :term OR s.location LIKE :term)
                 ORDER BY s.created_at DESC
                 LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':term',  $term,  PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Spots par catégorie
     */
    public function getByCategory(string $category, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT s.id, s.title, s.description, s.image, s.location, s.created_at,
                       u.username, u.avatar,
                       (SELECT COUNT(*) FROM likes WHERE spot_id = s.id) AS likes_count
                FROM spots s JOIN users u ON u.id = s.user_id
                WHERE s.category = :cat AND s.status = 'published' AND u.is_active = 1
                ORDER BY s.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cat',    $category, PDO::PARAM_STR);
        $stmt->bindValue(':limit',  $limit,    PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─── MODIFICATION ────────────────────────────────────────

    /**
     * Modifie un spot (uniquement par son auteur ou un admin)
     */
    public function update(int $spotId, int $userId, bool $isAdmin, array $data): bool
    {
        // Vérification propriétaire OU admin
        if (!$isAdmin) {
            $owner = $this->getOwnerId($spotId);
            if ($owner !== $userId) return false;
        }

        $allowed = ['title', 'description', 'location', 'latitude', 'longitude', 'image', 'category', 'status'];
        $fields  = [];
        $params  = [':id' => $spotId];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[]          = "`$field` = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $stmt = $this->pdo->prepare("UPDATE spots SET " . implode(', ', $fields) . " WHERE id = :id");
        return $stmt->execute($params);
    }

    /**
     * Supprime un spot (auteur ou admin)
     */
    public function delete(int $spotId, int $userId, bool $isAdmin): bool
    {
        if (!$isAdmin) {
            $owner = $this->getOwnerId($spotId);
            if ($owner !== $userId) return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM spots WHERE id = :id");
        return $stmt->execute([':id' => $spotId]);
    }

    // ─── LIKES ───────────────────────────────────────────────

    /**
     * Like/Unlike un spot (toggle)
     * Retourne true si liké, false si unliké
     */
    public function toggleLike(int $spotId, int $userId): bool
    {
        // Vérifie si le like existe déjà
        $stmt = $this->pdo->prepare("SELECT id FROM likes WHERE spot_id = :spot AND user_id = :user");
        $stmt->execute([':spot' => $spotId, ':user' => $userId]);

        if ($stmt->fetch()) {
            // Unlike
            $del = $this->pdo->prepare("DELETE FROM likes WHERE spot_id = :spot AND user_id = :user");
            $del->execute([':spot' => $spotId, ':user' => $userId]);
            return false;
        } else {
            // Like
            $ins = $this->pdo->prepare("INSERT INTO likes (spot_id, user_id) VALUES (:spot, :user)");
            $ins->execute([':spot' => $spotId, ':user' => $userId]);
            return true;
        }
    }

    /**
     * Vérifie si un user a liké un spot
     */
    public function isLikedBy(int $spotId, int $userId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM likes WHERE spot_id = :spot AND user_id = :user");
        $stmt->execute([':spot' => $spotId, ':user' => $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ─── TAGS ─────────────────────────────────────────────────

    private function attachTags(int $spotId, array $tagNames)
    {
        foreach ($tagNames as $name) {
            $name = strtolower(trim($name));
            if (!$name) continue;

            // Insère le tag s'il n'existe pas
            $ins = $this->pdo->prepare("INSERT IGNORE INTO tags (name) VALUES (:name)");
            $ins->execute([':name' => $name]);

            $sel = $this->pdo->prepare("SELECT id FROM tags WHERE name = :name");
            $sel->execute([':name' => $name]);
            $tagId = $sel->fetchColumn();

            // Liaison spot <-> tag
            $pivot = $this->pdo->prepare("INSERT IGNORE INTO spot_tags (spot_id, tag_id) VALUES (:spot, :tag)");
            $pivot->execute([':spot' => $spotId, ':tag' => $tagId]);
        }
    }

    public function getTagsForSpot(int $spotId): array
    {
        $sql  = "SELECT t.name FROM tags t JOIN spot_tags st ON st.tag_id = t.id WHERE st.spot_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $spotId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ─── ADMIN ───────────────────────────────────────────────

    /**
     * Tous les spots pour l'admin (y compris drafts et archivés)
     */
    public function getAllAdmin(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT s.id, s.title, s.status, s.category, s.created_at, u.username
                FROM spots s JOIN users u ON u.id = s.user_id
                ORDER BY s.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─── UTILITAIRES ─────────────────────────────────────────

    /**
     * @return int|false
     */
    private function getOwnerId(int $spotId)
    {
        $stmt = $this->pdo->prepare("SELECT user_id FROM spots WHERE id = :id");
        $stmt->execute([':id' => $spotId]);
        return $stmt->fetchColumn();
    }

    private function incrementViews(int $spotId)
    {
        $stmt = $this->pdo->prepare("UPDATE spots SET views_count = views_count + 1 WHERE id = :id");
        $stmt->execute([':id' => $spotId]);
    }
}