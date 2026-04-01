<?php
// ─────────────────────────────────────────────────────────────
//  Messaging Model — Conversations & Messages
// ─────────────────────────────────────────────────────────────

class Messaging
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ─── CRÉER OU RETROUVER UNE CONVERSATION DIRECTE ──────────
    public function getOrCreateDirect(int $userA, int $userB): int
    {
        // Cherche si une conversation directe existe déjà entre les deux
        $stmt = $this->pdo->prepare(
            'SELECT c.id FROM conversations c
             JOIN conversation_members m1 ON m1.conversation_id = c.id AND m1.user_id = ?
             JOIN conversation_members m2 ON m2.conversation_id = c.id AND m2.user_id = ?
             WHERE c.type = "direct"
             LIMIT 1'
        );
        $stmt->execute([$userA, $userB]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return (int) $row['id'];

        // Crée la conversation
        $stmt = $this->pdo->prepare(
            'INSERT INTO conversations (type, created_by) VALUES ("direct", ?)'
        );
        $stmt->execute([$userA]);
        $convId = (int) $this->pdo->lastInsertId();

        // Ajoute les deux membres
        $stmt = $this->pdo->prepare(
            'INSERT INTO conversation_members (conversation_id, user_id) VALUES (?, ?), (?, ?)'
        );
        $stmt->execute([$convId, $userA, $convId, $userB]);

        return $convId;
    }

    // ─── CRÉER UN GROUPE ──────────────────────────────────────
    public function createGroup(int $creatorId, string $name, array $memberIds): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO conversations (type, name, created_by) VALUES ("group", ?, ?)'
        );
        $stmt->execute([mb_substr(trim($name), 0, 191), $creatorId]);
        $convId = (int) $this->pdo->lastInsertId();

        // Toujours inclure le créateur
        $allMembers = array_unique(array_merge([$creatorId], $memberIds));
        $placeholders = implode(',', array_fill(0, count($allMembers), '(?, ?)'));
        $params = [];
        foreach ($allMembers as $uid) {
            $params[] = $convId;
            $params[] = (int)$uid;
        }
        $stmt = $this->pdo->prepare(
            "INSERT IGNORE INTO conversation_members (conversation_id, user_id) VALUES $placeholders"
        );
        $stmt->execute($params);

        return $convId;
    }

    // ─── ENVOYER UN MESSAGE ───────────────────────────────────
    public function sendMessage(int $convId, int $senderId, string $content): ?array
    {
        // Vérifie que l'expéditeur est bien membre
        if (!$this->isMember($convId, $senderId)) return null;

        $content = mb_substr(trim($content), 0, 5000);
        if ($content === '') return null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO chat_messages (conversation_id, sender_id, content) VALUES (?, ?, ?)'
        );
        $stmt->execute([$convId, $senderId, $content]);
        $msgId = (int) $this->pdo->lastInsertId();

        // Met à jour updated_at de la conversation
        $this->pdo->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = ?')
                  ->execute([$convId]);

        return $this->getMessage($msgId);
    }

    // ─── MESSAGES D'UNE CONVERSATION ─────────────────────────
    public function getMessages(int $convId, int $userId, int $limit = 50, int $before = 0): array
    {
        if (!$this->isMember($convId, $userId)) return [];

        $sql = 'SELECT m.id, m.content, m.created_at, m.is_deleted,
                       m.sender_id,
                       u.username AS sender_username,
                       u.avatar AS sender_avatar
                FROM chat_messages m
                JOIN users u ON u.id = m.sender_id
                WHERE m.conversation_id = ?';
        $params = [$convId];

        if ($before > 0) {
            $sql .= ' AND m.id < ?';
            $params[] = $before;
        }

        $sql .= ' ORDER BY m.created_at DESC LIMIT ?';
        $params[] = $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $messages = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

        // Marque comme lu
        $this->markRead($convId, $userId);

        return $messages;
    }

    // ─── NOUVEAUX MESSAGES (POLLING) ─────────────────────────
    public function getNewMessages(int $convId, int $userId, int $afterId): array
    {
        if (!$this->isMember($convId, $userId)) return [];

        $stmt = $this->pdo->prepare(
            'SELECT m.id, m.content, m.created_at, m.is_deleted,
                    m.sender_id,
                    u.username AS sender_username,
                    u.avatar AS sender_avatar
             FROM chat_messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.conversation_id = ? AND m.id > ?
             ORDER BY m.created_at ASC
             LIMIT 50'
        );
        $stmt->execute([$convId, $afterId]);
        $msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($msgs)) $this->markRead($convId, $userId);

        return $msgs;
    }

    // ─── LISTE DES CONVERSATIONS D'UN USER ───────────────────
    public function getConversations(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.type, c.name, c.updated_at,
                    -- Dernier message
                    (SELECT cm2.content FROM chat_messages cm2
                     WHERE cm2.conversation_id = c.id AND cm2.is_deleted = 0
                     ORDER BY cm2.created_at DESC LIMIT 1) AS last_message,
                    (SELECT cm2.created_at FROM chat_messages cm2
                     WHERE cm2.conversation_id = c.id AND cm2.is_deleted = 0
                     ORDER BY cm2.created_at DESC LIMIT 1) AS last_message_at,
                    (SELECT cm2.sender_id FROM chat_messages cm2
                     WHERE cm2.conversation_id = c.id AND cm2.is_deleted = 0
                     ORDER BY cm2.created_at DESC LIMIT 1) AS last_sender_id,
                    -- Nombre de non-lus
                    (SELECT COUNT(*) FROM chat_messages cm3
                     JOIN conversation_members mem ON mem.conversation_id = cm3.conversation_id
                                                  AND mem.user_id = ?
                     WHERE cm3.conversation_id = c.id
                       AND cm3.is_deleted = 0
                       AND (mem.last_read_at IS NULL OR cm3.created_at > mem.last_read_at)
                       AND cm3.sender_id != ?
                    ) AS unread_count,
                    me.last_read_at
             FROM conversations c
             JOIN conversation_members me ON me.conversation_id = c.id AND me.user_id = ?
             ORDER BY c.updated_at DESC'
        );
        $stmt->execute([$userId, $userId, $userId]);
        $convs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque conversation, on récupère les membres
        foreach ($convs as &$conv) {
            $conv['members'] = $this->getMembers($conv['id'], $userId);
        }
        unset($conv);

        return $convs;
    }

    // ─── MEMBRES D'UNE CONVERSATION (sauf soi-même) ──────────
    public function getMembers(int $convId, int $exceptUserId = 0): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.username, u.avatar, u.country
             FROM conversation_members cm
             JOIN users u ON u.id = cm.user_id
             WHERE cm.conversation_id = ?
             ORDER BY u.username ASC'
        );
        $stmt->execute([$convId]);
        $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($exceptUserId) {
            $all = array_filter($all, fn($m) => (int)$m['id'] !== $exceptUserId);
        }
        return array_values($all);
    }

    // ─── DÉTAILS D'UNE CONVERSATION ──────────────────────────
    public function getConversation(int $convId, int $userId): ?array
    {
        if (!$this->isMember($convId, $userId)) return null;

        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.type, c.name, c.created_by, c.created_at, c.updated_at
             FROM conversations c WHERE c.id = ? LIMIT 1'
        );
        $stmt->execute([$convId]);
        $conv = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$conv) return null;

        $conv['members'] = $this->getMembers($convId);
        return $conv;
    }

    // ─── QUITTER / SUPPRIMER UNE CONVERSATION ────────────────
    public function leaveConversation(int $convId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM conversation_members WHERE conversation_id = ? AND user_id = ?'
        );
        return $stmt->execute([$convId, $userId]) && $stmt->rowCount() > 0;
    }

    // ─── AJOUTER UN MEMBRE À UN GROUPE ───────────────────────
    public function addMember(int $convId, int $requesterId, int $newUserId): bool
    {
        // Seuls les membres existants peuvent ajouter
        if (!$this->isMember($convId, $requesterId)) return false;

        $stmt = $this->pdo->prepare(
            'SELECT type FROM conversations WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$convId]);
        $conv = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$conv || $conv['type'] !== 'group') return false;

        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO conversation_members (conversation_id, user_id) VALUES (?, ?)'
        );
        return $stmt->execute([$convId, $newUserId]);
    }

    // ─── MARQUER COMME LU ────────────────────────────────────
    public function markRead(int $convId, int $userId): void
    {
        $this->pdo->prepare(
            'UPDATE conversation_members SET last_read_at = NOW()
             WHERE conversation_id = ? AND user_id = ?'
        )->execute([$convId, $userId]);
    }

    // ─── VÉRIFIER APPARTENANCE ───────────────────────────────
    public function isMember(int $convId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM conversation_members WHERE conversation_id = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$convId, $userId]);
        return (bool) $stmt->fetchColumn();
    }

    // ─── UN SEUL MESSAGE ─────────────────────────────────────
    private function getMessage(int $msgId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.id, m.content, m.created_at, m.sender_id, m.is_deleted,
                    u.username AS sender_username, u.avatar AS sender_avatar
             FROM chat_messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.id = ? LIMIT 1'
        );
        $stmt->execute([$msgId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ─── TOTAL NON-LUS POUR UN USER ──────────────────────────
    public function totalUnread(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM chat_messages m
             JOIN conversation_members mem ON mem.conversation_id = m.conversation_id
                                          AND mem.user_id = ?
             WHERE m.is_deleted = 0
               AND m.sender_id != ?
               AND (mem.last_read_at IS NULL OR m.created_at > mem.last_read_at)'
        );
        $stmt->execute([$userId, $userId]);
        return (int) $stmt->fetchColumn();
    }
}
