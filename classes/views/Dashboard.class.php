<?php
class Dashboard extends View
{
    public function content()
    {
        $user  = $this->data['profileUser'] ?? [];
        $stats = $this->data['profileStats'] ?? [];

        if (!$user) {
            return '<div style="text-align:center;padding:200px 20px;"><h2>User not found.</h2><a href="?page=home">Back to home</a></div>';
        }

        $initials = strtoupper(substr($user['username'] ?? '??', 0, 2));
        $avatar = !empty($user['avatar'])
            ? '<img src="' . htmlspecialchars($user['avatar'], ENT_QUOTES) . '" alt="Avatar" class="sidebar-avatar-img">'
            : '<div class="sidebar-avatar-placeholder">' . $initials . '</div>';

        $spotsCount     = $stats['spots_count']     ?? 0;
        $followersCount = $stats['followers_count'] ?? 0;
        $followingCount = $stats['following_count'] ?? 0;
        $friendsCount = $stats['friends_count'] ?? 0;
        $likesCount     = $stats['total_likes']     ?? 0;

        $username = htmlspecialchars($user['username'], ENT_QUOTES);

        return '
        <link rel="stylesheet" href="public/css/dashboard.css">
        <div class="dash-layout">

            <!-- ── SIDEBAR ── -->
            ' . $this->sidebar($user, $avatar, "dashboard") . '

            <!-- ── MAIN CONTENT ── -->
            <div class="dash-main">
                <div class="dash-topbar">
                    <div>
                        <h1 class="dash-title">WELCOME, ' . strtoupper($username) . '!</h1>
                        <p class="dash-subtitle">Manage your profile, check messages, and plan your next cultural exchange.</p>
                    </div>
                </div>

                <!-- Stats rapides -->
                <div class="dash-cards">
                    <a href="?page=messages" class="dash-card dash-card--green">
                        <div class="dash-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z"/><path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z"/></svg>
                        </div>
                        <div class="dash-card-body">
                            <div class="dash-card-label">Messages</div>
                            <div class="dash-card-value">0 NEW</div>
                        </div>
                    </a>
                    <a href="?page=agenda" class="dash-card dash-card--yellow">
                        <div class="dash-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M12.75 12.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM7.5 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM8.25 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM9.75 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM10.5 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM12.75 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM14.25 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM15 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM16.5 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM15 12.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM16.5 13.5a.75.75 0 100-1.5.75.75 0 000 1.5z"/><path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="dash-card-body">
                            <div class="dash-card-label">Upcoming Events</div>
                            <div class="dash-card-value">0 THIS WEEK</div>
                        </div>
                    </a>
                    <a href="?page=connections" class="dash-card dash-card--orange">
                        <div class="dash-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                        </div>
                        <div class="dash-card-body">
                            <div class="dash-card-label">Connections</div>
                            <div class="dash-card-value">' . $friendsCount . ' FRIENDS</div>
                        </div>
                    </a>
                </div>

                <!-- Profil rapide -->
                <div class="dash-section">
                    <div class="dash-section-header">
                        <h2 class="dash-section-title">MY PROFILE</h2>
                        <a href="?page=profile&action=edit" class="dash-edit-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z"/></svg>
                            Edit
                        </a>
                    </div>
                    <div class="dash-profile-grid">
                        <div class="dash-profile-field">
                            <label>ABOUT ME</label>
                            <div class="dash-profile-value">' . (empty($user['bio']) ? '<span class="dash-empty-field">No bio yet…</span>' : htmlspecialchars($user['bio'], ENT_QUOTES)) . '</div>
                        </div>
                        <div class="dash-profile-field">
                            <label>MY LOCATION</label>
                            <div class="dash-profile-value">' . (empty($user['country']) ? '<span class="dash-empty-field">Not set</span>' : htmlspecialchars($user['country'], ENT_QUOTES)) . '</div>
                        </div>
                        <div class="dash-profile-field">
                            <label>LANGUAGE I SPEAK</label>
                            <div class="dash-tags">
                                ' . (!empty($user['language']) ? '<span class="dash-tag dash-tag--green">' . htmlspecialchars($user['language'], ENT_QUOTES) . '</span>' : '<span class="dash-empty-field">Not set</span>') . '
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /.dash-main -->
        </div><!-- /.dash-layout -->
        ';
    }

    protected function sidebar($user, $avatar, $active = 'dashboard')
    {
        $username = htmlspecialchars($user['username'] ?? '', ENT_QUOTES);
        $flags   = ['fr' => '🇫🇷', 'al' => '🇦🇱', 'vi' => '🇻🇳', 'us' => '🇺🇸'];
        $country = $user['country'] ?? '';
        $flag    = $flags[strtolower($country)] ?? '🌍';

        // Unread notification count from data or session
        $unreadNotifs = (int)($this->data['unreadNotifs'] ?? 0);

        $items = [
            'dashboard'     => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z"/><path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z"/></svg>', 'label' => 'Dashboard',     'page' => 'dashboard',     'badge' => 0],
            'notifications' => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M5.25 9a6.75 6.75 0 0113.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 01-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 11-7.48 0 24.585 24.585 0 01-4.831-1.244.75.75 0 01-.298-1.205A8.217 8.217 0 005.25 9.75V9zm4.502 8.9a2.25 2.25 0 104.496 0 25.057 25.057 0 01-4.496 0z" clip-rule="evenodd"/></svg>', 'label' => 'Notifications', 'page' => 'notifications', 'badge' => $unreadNotifs],
            'connections'   => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z"/></svg>', 'label' => 'Connections', 'page' => 'connections', 'badge' => 0],
            'messages'      => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z"/><path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z"/></svg>', 'label' => 'Messages',      'page' => 'messages',      'badge' => 0],
            'agenda'        => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd"/></svg>', 'label' => 'Agenda',        'page' => 'agenda',        'badge' => 0],
        ];

        $navItems = '';
        foreach ($items as $key => $item) {
            $isActive = $key === $active ? 'sidebar-nav-item--active' : '';
            $badge = (int)($item['badge'] ?? 0);
            $badgeHtml = $badge > 0
                ? '<span class="sidebar-nav-badge">' . ($badge > 99 ? '99+' : $badge) . '</span>'
                : '';
            $navItems .= '
                <a href="?page=' . $item['page'] . '" class="sidebar-nav-item ' . $isActive . '">
                    ' . $item['icon'] . '
                    <span>' . $item['label'] . '</span>
                    ' . $badgeHtml . '
                </a>';
        }

        return '
        <aside class="dash-sidebar">
            <a href="?page=profile" class="sidebar-user sidebar-user--link" title="View my profile" style="text-decoration:none;display:flex;align-items:center;gap:12px;padding:20px 16px;border-bottom:2px solid rgba(255,255,255,0.08);transition:background 0.15s;" onmouseover="this.style.background=\'rgba(255,255,255,0.06)\'" onmouseout="this.style.background=\'\'">
                <div class="sidebar-avatar">' . $avatar . '</div>
                <div class="sidebar-user-info">
                    <div class="sidebar-username">' . $username . '</div>
                    <div class="sidebar-country">' . $flag . ' ' . htmlspecialchars($country, ENT_QUOTES) . '</div>
                </div>
            </a>
            <nav class="sidebar-nav">
                ' . $navItems . '
            </nav>
            <div class="sidebar-footer">
                <a href="?page=profile" class="sidebar-footer-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M15.75 2.25H21a.75.75 0 01.75.75v5.25a.75.75 0 01-1.5 0V4.81L8.03 17.03a.75.75 0 01-1.06-1.06L19.19 3.75h-3.44a.75.75 0 010-1.5zm-10.5 4.5a1.5 1.5 0 00-1.5 1.5v10.5a1.5 1.5 0 001.5 1.5h10.5a1.5 1.5 0 001.5-1.5V10.5a.75.75 0 011.5 0v8.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V8.25a3 3 0 013-3H13.5a.75.75 0 010 1.5H5.25z" clip-rule="evenodd"/></svg>
                    Back to Site
                </a>
                <a href="?page=logout" class="sidebar-footer-link sidebar-footer-link--danger">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 006 5.25v13.5a1.5 1.5 0 001.5 1.5h6a1.5 1.5 0 001.5-1.5V15a.75.75 0 011.5 0v3.75a3 3 0 01-3 3h-6a3 3 0 01-3-3V5.25a3 3 0 013-3h6a3 3 0 013 3V9A.75.75 0 0115 9V5.25a1.5 1.5 0 00-1.5-1.5h-6zm5.03 4.72a.75.75 0 010 1.06l-1.72 1.72h10.94a.75.75 0 010 1.5H10.81l1.72 1.72a.75.75 0 11-1.06 1.06l-3-3a.75.75 0 010-1.06l3-3a.75.75 0 011.06 0z" clip-rule="evenodd"/></svg>
                    Sign Out
                </a>
            </div>
        </aside>';
    }
}