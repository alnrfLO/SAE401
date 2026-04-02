<?php
class Notifications extends Dashboard
{
    public function content()
    {
        $user  = $this->data['profileUser'] ?? [];
        $notifs = $this->data['notifications'] ?? [];

        if (!$user) {
            return '<div style="text-align:center;padding:200px 20px;"><h2>User not found.</h2></div>';
        }

        $initials = strtoupper(substr($user['username'] ?? '??', 0, 2));
        $avatar = !empty($user['avatar'])
            ? '<img src="' . htmlspecialchars($user['avatar'], ENT_QUOTES) . '" alt="Avatar" class="sidebar-avatar-img">'
            : '<div class="sidebar-avatar-placeholder">' . $initials . '</div>';

        $sidebar = $this->sidebar($user, $avatar, 'notifications');

        // Build notifications HTML
        $notifItems = '';
        if (empty($notifs)) {
            $notifItems = '<div class="notif-empty">
                <div class="notif-empty-icon">🔔</div>
                <p>No notifications yet.</p>
                <p class="notif-empty-sub">When friends send requests, messages or event invitations, they\'ll appear here.</p>
            </div>';
        } else {
            foreach ($notifs as $n) {
                $fmt     = Notification::format($n);
                $unread  = !$n['is_read'] ? ' notif-item--unread' : '';
                $timeAgo = $this->timeAgo($n['created_at']);
                $initActor = strtoupper(substr($n['actor_username'] ?? '?', 0, 2));
                $avatarActor = !empty($n['actor_avatar'])
                    ? '<img src="' . htmlspecialchars($n['actor_avatar'], ENT_QUOTES) . '" alt="" class="notif-avatar-img">'
                    : '<div class="notif-avatar-placeholder">' . $initActor . '</div>';

                $notifItems .= '
                <a href="' . htmlspecialchars($fmt['url'], ENT_QUOTES) . '" 
                   class="notif-item' . $unread . '" 
                   data-id="' . (int)$n['id'] . '"
                   onclick="markRead(' . (int)$n['id'] . ')">
                    <div class="notif-avatar">' . $avatarActor . '</div>
                    <div class="notif-body">
                        <div class="notif-icon notif-icon--' . htmlspecialchars($fmt['color'], ENT_QUOTES) . '">' . $fmt['icon'] . '</div>
                        <div class="notif-text">' . $fmt['text'] . '</div>
                        <div class="notif-time">' . $timeAgo . '</div>
                    </div>
                    ' . (!$n['is_read'] ? '<div class="notif-dot"></div>' : '') . '
                </a>';
            }
        }

        $hasUnread = !empty(array_filter($notifs, fn($n) => !$n['is_read']));
        $markAllBtn = $hasUnread
            ? '<button class="dash-edit-btn" onclick="markAllRead()">Mark all as read</button>'
            : '';

        return <<<HTML
        <link rel="stylesheet" href="public/css/dashboard.css">
        <div class="dash-layout">

            {$sidebar}

            <div class="dash-main">
                <div class="dash-topbar">
                    <div>
                        <h1 class="dash-title">NOTIFICATIONS</h1>
                        <p class="dash-subtitle">Stay up to date with your friend requests, messages and event invitations.</p>
                    </div>
                    {$markAllBtn}
                </div>

                <div class="notif-list" id="notifList">
                    {$notifItems}
                </div>
            </div>
        </div>

        <style>
        .notif-list { display: flex; flex-direction: column; gap: 2px; margin-top: 16px; }

        .notif-item {
            display: flex; align-items: center; gap: 14px;
            padding: 16px 20px; border-radius: 10px;
            background: var(--card-bg, #1a1a2e);
            border: 1px solid rgba(255,255,255,0.06);
            text-decoration: none; color: inherit;
            transition: background 0.15s, border-color 0.15s;
            position: relative;
        }
        .notif-item:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.12); }
        .notif-item--unread { background: rgba(99,102,241,0.08); border-color: rgba(99,102,241,0.2); }
        .notif-item--unread:hover { background: rgba(99,102,241,0.12); }

        .notif-avatar { flex-shrink: 0; }
        .notif-avatar-img { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
        .notif-avatar-placeholder {
            width: 44px; height: 44px; border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: #fff;
        }

        .notif-body { flex: 1; min-width: 0; }
        .notif-icon { font-size: 16px; margin-bottom: 2px; }
        .notif-text { font-size: 14px; line-height: 1.4; color: rgba(255,255,255,0.85); }
        .notif-text strong { color: #fff; font-weight: 600; }
        .notif-time { font-size: 12px; color: rgba(255,255,255,0.4); margin-top: 4px; }

        .notif-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #6366f1; flex-shrink: 0;
        }

        .notif-empty {
            text-align: center; padding: 80px 20px;
            color: rgba(255,255,255,0.4);
        }
        .notif-empty-icon { font-size: 48px; margin-bottom: 16px; }
        .notif-empty p { margin: 4px 0; font-size: 15px; }
        .notif-empty-sub { font-size: 13px; }
        </style>

        <script>
        async function markRead(id) {
            await fetch('?page=notifications&action=markRead', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'notif_id=' + id
            });
            // Remove unread styling
            const el = document.querySelector('[data-id="' + id + '"]');
            if (el) {
                el.classList.remove('notif-item--unread');
                const dot = el.querySelector('.notif-dot');
                if (dot) dot.remove();
            }
            updateBellCount(-1);
        }

        async function markAllRead() {
            await fetch('?page=notifications&action=markAllRead', {
                method: 'POST'
            });
            document.querySelectorAll('.notif-item--unread').forEach(function(el) {
                el.classList.remove('notif-item--unread');
                const dot = el.querySelector('.notif-dot');
                if (dot) dot.remove();
            });
            const btn = document.querySelector('button[onclick="markAllRead()"]');
            if (btn) btn.remove();
            updateBellCount(0, true);
        }

        function updateBellCount(delta, reset) {
            const badge = document.querySelector('.notif-bell-badge');
            if (!badge) return;
            if (reset) { badge.style.display = 'none'; return; }
            let count = parseInt(badge.textContent || '0', 10);
            count = Math.max(0, count + delta);
            if (count === 0) badge.style.display = 'none';
            else badge.textContent = count;
        }
        </script>
HTML;
    }

    private function timeAgo(string $datetime): string
    {
        $now  = new DateTime();
        $past = new DateTime($datetime);
        $diff = $now->getTimestamp() - $past->getTimestamp();

        if ($diff < 60)      return 'Just now';
        if ($diff < 3600)    return (int)($diff / 60) . 'm ago';
        if ($diff < 86400)   return (int)($diff / 3600) . 'h ago';
        if ($diff < 604800)  return (int)($diff / 86400) . 'd ago';
        return $past->format('M j, Y');
    }
}
