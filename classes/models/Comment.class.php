<?php
// ─────────────────────────────────────────────────────────────
//  Comment Model — Commentaires sous les spots FAV
//  Support des réponses imbriquées + modération
// ─────────────────────────────────────────────────────────────

class Comment
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ─── CRÉATION ────────────────────────────────────────────

    /**
     * Ajoute un commentaire (ou une réponse si parent_id fourni)
     */
    /**
     * @return int|false
     */
    public function create(int $spotId, int $userId, string $content, int $parentId = null)
    {
        $sql = "INSERT INTO comments (spot_id, user_id, content, parent_id)
                VALUES (:spot_id, :user_id, :content, :parent_id)";

        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([
            ':spot_id'   => $spotId,
            ':user_id'   => $userId,
            ':content'   => trim($content),
            ':parent_id' => $parentId,
        ]);

        return $ok ? (int) $this->pdo->lastInsertId() : false;
    }

    // ─── LECTURE ─────────────────────────────────────────────

    /**
     * Récupère tous les commentaires d'un spot (thread racine + replies)
     * Retourne une structure à plat — à imbriquer côté vue si besoin
     */
    public function getBySpot(int $spotId): array
    {
        $sql = "SELECT c.id, c.content, c.parent_id, c.is_hidden, c.created_at,
                       c.updated_at,
                       u.id AS user_id, u.username, u.avatar,
                       (SELECT COUNT(*) FROM likes WHERE comment_id = c.id) AS likes_count
                FROM comments c
                JOIN users u ON u.id = c.user_id
                WHERE c.spot_id = :spot_id AND c.is_hidden = 0
                ORDER BY c.created_at ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':spot_id' => $spotId]);
        $comments = $stmt->fetchAll();

        // Organiser en arbre (commentaires + leurs replies)
        return $this->buildTree($comments);
    }

    /**
     * Récupère tous les commentaires (y compris masqués) pour l'admin
     */
    public function getAllAdmin(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT c.id, c.content, c.is_hidden, c.created_at,
                       c.spot_id, s.title AS spot_title,
                       u.username
                FROM comments c
                JOIN users u ON u.id = c.user_id
                JOIN spots  s ON s.id = c.spot_id
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─── MODIFICATION ────────────────────────────────────────

    /**
     * Édite un commentaire (auteur uniquement)
     */
    public function update(int $commentId, int $userId, string $content): bool
    {
        $sql  = "UPDATE comments SET content = :content
                 WHERE id = :id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':content' => trim($content),
            ':id'      => $commentId,
            ':user_id' => $userId,
        ]);
    }

    /**
     * Supprime un commentaire (auteur ou admin)
     */
    public function delete(int $commentId, int $userId, bool $isAdmin): bool
    {
        if ($isAdmin) {
            $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = :id");
            return $stmt->execute([':id' => $commentId]);
        }

        // User normal : vérification propriétaire
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $commentId, ':user_id' => $userId]);
    }

    // ─── MODÉRATION ──────────────────────────────────────────

    /**
     * Masque ou affiche un commentaire (admin uniquement)
     */
    public function toggleHide(int $commentId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE comments SET is_hidden = NOT is_hidden WHERE id = :id");
        return $stmt->execute([':id' => $commentId]);
    }

    // ─── LIKES ───────────────────────────────────────────────

    /**
     * Like/Unlike un commentaire (toggle)
     */
    public function toggleLike(int $commentId, int $userId): bool
    {
        $stmt = $this->pdo->prepare("SELECT id FROM likes WHERE comment_id = :cid AND user_id = :uid");
        $stmt->execute([':cid' => $commentId, ':uid' => $userId]);

        if ($stmt->fetch()) {
            $del = $this->pdo->prepare("DELETE FROM likes WHERE comment_id = :cid AND user_id = :uid");
            $del->execute([':cid' => $commentId, ':uid' => $userId]);
            return false;
        } else {
            $ins = $this->pdo->prepare("INSERT INTO likes (comment_id, user_id) VALUES (:cid, :uid)");
            $ins->execute([':cid' => $commentId, ':uid' => $userId]);
            return true;
        }
    }

    // ─── UTILITAIRES ─────────────────────────────────────────

    /**
     * Organise une liste plate en arbre parent/enfants
     */
    private function buildTree(array $comments): array
    {
        $indexed = [];
        $tree    = [];

        foreach ($comments as &$comment) {
            $comment['replies'] = [];
            $indexed[$comment['id']] = &$comment;
        }
        unset($comment);

        foreach ($indexed as &$comment) {
            if ($comment['parent_id'] !== null && isset($indexed[$comment['parent_id']])) {
                $indexed[$comment['parent_id']]['replies'][] = &$comment;
            } else {
                $tree[] = &$comment;
            }
        }

        return $tree;
    }
}
