<?php
// ─────────────────────────────────────────────────────────────
//  Notification Model — Gestion des notifications FAV
//  Types : friend_request, friend_accepted, new_message,
//          event_invitation, event_accepted, event_declined
// ─────────────────────────────────────────────────────────────

class Notification
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ─── CRÉER UNE NOTIFICATION ───────────────────────────────

    /**
     * Crée une notification.
     * On évite les doublons pour certains types (ex: friend_request déjà envoyé).
     */
    public function create(int $userId, int $actorId, string $type, $referenceId = null): bool
    {
        // Ne pas notifier soi-même
        if ($userId === $actorId) return false;

        // Anti-doublon pour friend_request et event_invitation
        if (in_array($type, ['friend_request', 'event_invitation'])) {
            $stmt = $this->pdo->prepare(
                'SELECT id FROM notifications
                 WHERE user_id = ? AND actor_id = ? AND type = ? AND reference_id = ? AND is_read = 0
                 LIMIT 1'
            );
            $stmt->execute([$userId, $actorId, $type, $referenceId]);
            if ($stmt->fetchColumn()) return false; // déjà présent
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO notifications (user_id, actor_id, type, reference_id)
             VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$userId, $actorId, $type, $referenceId]);
    }

    // ─── LIRE LES NOTIFICATIONS D'UN USER ────────────────────

    public function getForUser(int $userId, int $limit = 30): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT n.id, n.type, n.reference_id, n.is_read, n.created_at,
                    u.id AS actor_id, u.username AS actor_username, u.avatar AS actor_avatar
             FROM notifications n
             JOIN users u ON u.id = n.actor_id
             WHERE n.user_id = ?
             ORDER BY n.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── COMPTER LES NON LUES ────────────────────────────────

    public function countUnread(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    // ─── MARQUER COMME LUES ──────────────────────────────────

    public function markAllRead(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0'
        );
        return $stmt->execute([$userId]);
    }

    public function markOneRead(int $notifId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$notifId, $userId]);
    }

    // ─── SUPPRIMER UNE NOTIFICATION ──────────────────────────

    public function delete(int $notifId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM notifications WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$notifId, $userId]);
    }

    // ─── HELPERS MÉTIER ──────────────────────────────────────

    /** Appelé quand A envoie une demande d'ami à B */
    public function onFriendRequest(int $receiverId, int $senderId, int $friendshipId): bool
    {
        return $this->create($receiverId, $senderId, 'friend_request', $friendshipId);
    }

    /** Appelé quand B accepte la demande de A */
    public function onFriendAccepted(int $senderId, int $acceptorId, int $friendshipId): bool
    {
        return $this->create($senderId, $acceptorId, 'friend_accepted', $friendshipId);
    }

    /** Appelé quand un message est reçu */
    public function onNewMessage(int $receiverId, int $senderId, int $convId): bool
    {
        return $this->create($receiverId, $senderId, 'new_message', $convId);
    }

    /** Appelé quand on invite un user à un événement */
    public function onEventInvitation(int $inviteeId, int $inviterId, int $eventId): bool
    {
        return $this->create($inviteeId, $inviterId, 'event_invitation', $eventId);
    }

    /** Appelé quand l'invité accepte l'événement */
    public function onEventAccepted(int $organizerId, int $acceptorId, int $eventId): bool
    {
        return $this->create($organizerId, $acceptorId, 'event_accepted', $eventId);
    }

    /** Appelé quand l'invité refuse l'événement */
    public function onEventDeclined(int $organizerId, int $declinerId, int $eventId): bool
    {
        return $this->create($organizerId, $declinerId, 'event_declined', $eventId);
    }

    // ─── FORMATAGE POUR L'UI ─────────────────────────────────

    /**
     * Retourne le texte, l'icône et l'URL de destination selon le type
     */
    public static function format(array $notif): array
    {
        $actor = htmlspecialchars($notif['actor_username'] ?? 'Someone', ENT_QUOTES);
        $refId = (int)($notif['reference_id'] ?? 0);

        switch ($notif['type']) {
            case 'friend_request':
                return [
                    'icon'  => '👋',
                    'text'  => "<strong>$actor</strong> sent you a friend request.",
                    'url'   => '?page=connections',
                    'color' => 'orange',
                ];
            case 'friend_accepted':
                return [
                    'icon'  => '🤝',
                    'text'  => "<strong>$actor</strong> accepted your friend request.",
                    'url'   => '?page=connections',
                    'color' => 'green',
                ];
            case 'new_message':
                return [
                    'icon'  => '✉️',
                    'text'  => "<strong>$actor</strong> sent you a message.",
                    'url'   => '?page=messages' . ($refId ? '&conv=' . $refId : ''),
                    'color' => 'blue',
                ];
            case 'event_invitation':
                return [
                    'icon'  => '📅',
                    'text'  => "<strong>$actor</strong> invited you to an event.",
                    'url'   => '?page=agenda',
                    'color' => 'yellow',
                ];
            case 'event_accepted':
                return [
                    'icon'  => '✅',
                    'text'  => "<strong>$actor</strong> accepted your event invitation.",
                    'url'   => '?page=agenda',
                    'color' => 'green',
                ];
            case 'event_declined':
                return [
                    'icon'  => '❌',
                    'text'  => "<strong>$actor</strong> declined your event invitation.",
                    'url'   => '?page=agenda',
                    'color' => 'red',
                ];
            default:
                return [
                    'icon'  => '🔔',
                    'text'  => "New notification from <strong>$actor</strong>.",
                    'url'   => '?page=dashboard',
                    'color' => 'grey',
                ];
        }
    }
}