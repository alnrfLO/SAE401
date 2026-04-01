<?php
class Friendship
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ─── ENVOYER UNE DEMANDE D'AMI ────────────────────────────────────────
    public function sendRequest(int $senderId, int $receiverId): bool
    {
        // Vérifie qu'aucun lien n'existe déjà (dans les deux sens)
        if ($this->getRelation($senderId, $receiverId)) return false;

        $stmt = $this->pdo->prepare(
            'INSERT INTO friendships (sender_id, receiver_id, status) VALUES (?, ?, "pending")'
        );
        return $stmt->execute([$senderId, $receiverId]);
    }

    // ─── ACCEPTER UNE DEMANDE ─────────────────────────────────────────────
    public function acceptRequest(int $requestId, int $receiverId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE friendships SET status = "accepted"
             WHERE id = ? AND receiver_id = ? AND status = "pending"'
        );
        return $stmt->execute([$requestId, $receiverId]) && $stmt->rowCount() > 0;
    }

    // ─── REFUSER / ANNULER UNE DEMANDE ───────────────────────────────────
    public function deleteRequest(int $requestId, int $userId): bool
    {
        // L'envoyeur peut annuler, le destinataire peut refuser
        $stmt = $this->pdo->prepare(
            'DELETE FROM friendships WHERE id = ? AND (sender_id = ? OR receiver_id = ?)'
        );
        return $stmt->execute([$requestId, $userId, $userId]) && $stmt->rowCount() > 0;
    }

    // ─── SUPPRIMER UN AMI (relation acceptée) ────────────────────────────
    public function removeFriend(int $userId, int $friendId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM friendships
             WHERE status = "accepted"
               AND (
                 (sender_id = ? AND receiver_id = ?) OR
                 (sender_id = ? AND receiver_id = ?)
               )'
        );
        return $stmt->execute([$userId, $friendId, $friendId, $userId]);
    }

    // ─── RELATION ENTRE DEUX USERS ───────────────────────────────────────
    public function getRelation(int $userA, int $userB): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM friendships
             WHERE (sender_id = ? AND receiver_id = ?)
                OR (sender_id = ? AND receiver_id = ?)
             LIMIT 1'
        );
        $stmt->execute([$userA, $userB, $userB, $userA]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ─── LISTE DES AMIS ACCEPTÉS ─────────────────────────────────────────
    public function getFriends(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.username, u.avatar, u.country, u.bio,
                    f.id AS friendship_id, f.created_at AS friends_since
             FROM friendships f
             JOIN users u ON u.id = IF(f.sender_id = ?, f.receiver_id, f.sender_id)
             WHERE f.status = "accepted"
               AND (f.sender_id = ? OR f.receiver_id = ?)
             ORDER BY f.updated_at DESC'
        );
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── DEMANDES REÇUES (EN ATTENTE) ────────────────────────────────────
    public function getReceivedRequests(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.id AS friendship_id, f.created_at,
                    u.id, u.username, u.avatar, u.country, u.bio
             FROM friendships f
             JOIN users u ON u.id = f.sender_id
             WHERE f.receiver_id = ? AND f.status = "pending"
             ORDER BY f.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── DEMANDES ENVOYÉES (EN ATTENTE) ──────────────────────────────────
    public function getSentRequests(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.id AS friendship_id, f.created_at,
                    u.id, u.username, u.avatar, u.country, u.bio
             FROM friendships f
             JOIN users u ON u.id = f.receiver_id
             WHERE f.sender_id = ? AND f.status = "pending"
             ORDER BY f.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── RECHERCHE D'UTILISATEURS ────────────────────────────────────────
    public function searchUsers(int $currentUserId, string $query): array
    {
        $like = '%' . $query . '%';
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.username, u.avatar, u.country, u.bio,
                    f.id AS friendship_id, f.status AS friendship_status,
                    f.sender_id AS friendship_sender
             FROM users u
             LEFT JOIN friendships f ON (
                 (f.sender_id = ? AND f.receiver_id = u.id) OR
                 (f.sender_id = u.id AND f.receiver_id = ?)
             )
             WHERE u.id != ?
               AND u.is_active = 1
               AND u.username LIKE ?
             ORDER BY u.username ASC
             LIMIT 20'
        );
        $stmt->execute([$currentUserId, $currentUserId, $currentUserId, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── NOMBRE D'AMIS ───────────────────────────────────────────────────
    public function countFriends(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM friendships
             WHERE status = "accepted"
               AND (sender_id = ? OR receiver_id = ?)'
        );
        $stmt->execute([$userId, $userId]);
        return (int)$stmt->fetchColumn();
    }

    // ─── NOMBRE DE DEMANDES REÇUES NON LUES ─────────────────────────────
    public function countPendingReceived(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM friendships WHERE receiver_id = ? AND status = "pending"'
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}
