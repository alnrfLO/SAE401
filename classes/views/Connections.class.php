<?php
class Connections extends View
{
    public function content()
    {
        $user    = $this->data['profileUser']   ?? [];
        $friends = $this->data['friends']       ?? [];
        $received = $this->data['received']     ?? [];
        $sent    = $this->data['sent']          ?? [];

        if (!$user) {
            return '<div style="text-align:center;padding:200px 20px;"><h2>User not found.</h2><a href="?page=home">Back to home</a></div>';
        }

        $initials = strtoupper(substr($user['username'] ?? '??', 0, 2));
        $avatar = !empty($user['avatar'])
            ? '<img src="' . htmlspecialchars($user['avatar'], ENT_QUOTES) . '" alt="Avatar" class="sidebar-avatar-img">'
            : '<div class="sidebar-avatar-placeholder">' . $initials . '</div>';

        $friendCount   = count($friends);
        $receivedCount = count($received);
        $sentCount     = count($sent);

        // ── Helper : rend une user-card ──────────────────────────────────
        $renderCard = function(array $u, string $mode) use ($user): string {
            $uid      = (int)$u['id'];
            $uname    = htmlspecialchars($u['username'], ENT_QUOTES);
            $uavatar  = !empty($u['avatar'])
                ? '<img src="' . htmlspecialchars($u['avatar'], ENT_QUOTES) . '" alt="" class="conn-card-avatar-img">'
                : '<div class="conn-card-avatar-ph">' . strtoupper(substr($u['username'], 0, 2)) . '</div>';
            $ubio     = htmlspecialchars(mb_substr($u['bio'] ?? '', 0, 80), ENT_QUOTES);
            $flags    = ['fr' => '🇫🇷', 'al' => '🇦🇱', 'vi' => '🇻🇳', 'us' => '🇺🇸'];
            $flag     = $flags[strtolower($u['country'] ?? '')] ?? '🌍';
            $fid      = (int)($u['friendship_id'] ?? 0);

                switch ($mode) {
                    case 'friend':
                        $actions = '
                            <button class="conn-btn conn-btn--danger"
                                onclick="friendAction(\'remove\', ' . $fid . ', this.closest(\'.conn-card\'))"
                                title="Remove friend">
                                Remove
                            </button>';
                        break;

                    case 'received':
                        $actions = '
                            <button class="conn-btn conn-btn--green"
                                onclick="friendAction(\'accept\', ' . $fid . ', this.closest(\'.conn-card\'))">
                                Accept
                            </button>
                            <button class="conn-btn conn-btn--danger"
                                onclick="friendAction(\'decline\', ' . $fid . ', this.closest(\'.conn-card\'))">
                                Decline
                            </button>';
                        break;

                    case 'sent':
                        $actions = '
                            <button class="conn-btn conn-btn--outline"
                                onclick="friendAction(\'cancel\', ' . $fid . ', this.closest(\'.conn-card\'))">
                                Cancel
                            </button>';
                        break;

                    default:
                        $actions = '';
                }

            return '
            <div class="conn-card">
                <a href="?page=profile&id=' . $uid . '" class="conn-card-avatar conn-card-avatar--link" title="View profile">' . $uavatar . '</a>
                <div class="conn-card-info">
                    <a href="?page=profile&id=' . $uid . '" class="conn-card-name conn-card-name--link">' . $uname . '</a>
                    <span class="conn-card-country">' . $flag . ' ' . htmlspecialchars($u['country'] ?? '', ENT_QUOTES) . '</span>
                    ' . ($ubio ? '<span class="conn-card-bio">' . $ubio . '</span>' : '') . '
                </div>
                <div class="conn-card-actions">' . $actions . '</div>
            </div>';
        };

        // ── HTML des 3 listes ────────────────────────────────────────────
        $friendsHtml = '';
        if ($friendCount === 0) {
            $friendsHtml = '
            <div class="conn-empty">
                <div class="conn-empty-icon">🌍</div>
                <h3>No connections yet!</h3>
                <p>Start building your travel network — search for users above and send friend requests.</p>
            </div>';
        } else {
            $friendsHtml = '<div class="conn-list">';
            foreach ($friends as $f) {
                $friendsHtml .= $renderCard($f, 'friend');
            }
            $friendsHtml .= '</div>';
        }

        $receivedHtml = '';
        if ($receivedCount === 0) {
            $receivedHtml = '<div class="conn-empty"><div class="conn-empty-icon">📬</div><p>No pending requests received.</p></div>';
        } else {
            $receivedHtml = '<div class="conn-list">';
            foreach ($received as $r) {
                $receivedHtml .= $renderCard($r, 'received');
            }
            $receivedHtml .= '</div>';
        }

        $sentHtml = '';
        if ($sentCount === 0) {
            $sentHtml = '<div class="conn-empty"><div class="conn-empty-icon">📤</div><p>No pending requests sent.</p></div>';
        } else {
            $sentHtml = '<div class="conn-list">';
            foreach ($sent as $s) {
                $sentHtml .= $renderCard($s, 'sent');
            }
            $sentHtml .= '</div>';
        }

        return '
        <link rel="stylesheet" href="public/css/dashboard.css">
        <link rel="stylesheet" href="public/css/connections.css">
        <style>
            .conn-card-avatar--link { display: contents; }
            .conn-card-name--link { text-decoration: none; color: white; font-family: "Bungee", cursive; font-size: 1rem; }
            .conn-card-name--link:hover { text-decoration: underline; }
            .conn-sr-avatar--link { display: contents; }
            .conn-sr-name--link { text-decoration: none; color: white; font-weight: 700; }
            .conn-sr-name--link:hover { text-decoration: underline; }
        </style>
        <div class="dash-layout">

            ' . $this->sidebar($user, $avatar, "connections") . '

            <div class="dash-main">
                <div class="dash-topbar">
                    <div>
                        <h1 class="dash-title">MY CONNECTIONS</h1>
                        <p class="dash-subtitle">Manage your travel network — friends, requests, and discoveries.</p>
                    </div>
                    <div class="conn-counter">
                        <span class="conn-counter-number">' . $friendCount . '</span>
                        <span class="conn-counter-label">friends</span>
                    </div>
                </div>

                <!-- ── BARRE DE RECHERCHE ── -->
                <div class="conn-search-box">
                    <div class="conn-search-wrap">
                        <svg class="conn-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd"/></svg>
                        <input type="text" class="conn-search-input" id="searchInput"
                               placeholder="Search users by name…"
                               autocomplete="off">
                    </div>
                    <div class="conn-search-results" id="searchResults"></div>
                </div>

                <!-- ── ONGLETS ── -->
                <div class="conn-tabs">
                    <button class="conn-tab conn-tab--active" data-tab="friends" onclick="switchTab(\'friends\', this)">
                        Friends
                        <span class="conn-tab-badge">' . $friendCount . '</span>
                    </button>
                    <button class="conn-tab" data-tab="received" onclick="switchTab(\'received\', this)">
                        Received
                        ' . ($receivedCount > 0 ? '<span class="conn-tab-badge conn-tab-badge--alert">' . $receivedCount . '</span>' : '') . '
                    </button>
                    <button class="conn-tab" data-tab="sent" onclick="switchTab(\'sent\', this)">
                        Sent
                        ' . ($sentCount > 0 ? '<span class="conn-tab-badge">' . $sentCount . '</span>' : '') . '
                    </button>
                </div>

                <div id="tab-friends" class="conn-tab-panel">' . $friendsHtml . '</div>
                <div id="tab-received" class="conn-tab-panel" style="display:none">' . $receivedHtml . '</div>
                <div id="tab-sent" class="conn-tab-panel" style="display:none">' . $sentHtml . '</div>

            </div>
        </div>

        <script>
        // ── Onglets ──────────────────────────────────────────────────────
        function switchTab(tabName, btn) {
            document.querySelectorAll(".conn-tab-panel").forEach(p => p.style.display = "none");
            document.querySelectorAll(".conn-tab").forEach(t => t.classList.remove("conn-tab--active"));
            document.getElementById("tab-" + tabName).style.display = "block";
            btn.classList.add("conn-tab--active");
        }

        // ── Recherche avec debounce ──────────────────────────────────────
        document.addEventListener("DOMContentLoaded", function() {
            let searchTimer;
            const searchInput   = document.getElementById("searchInput");
            const searchResults = document.getElementById("searchResults");

            if (!searchInput || !searchResults) return;

            searchInput.addEventListener("input", function() {
                clearTimeout(searchTimer);
                const q = this.value.trim();
                if (q.length < 2) {
                    searchResults.innerHTML = "";
                    searchResults.classList.remove("active");
                    return;
                }
                searchTimer = setTimeout(() => doSearch(q), 300);
            });

        });

        // Fermer les résultats si on clique ailleurs
        document.addEventListener("click", function(e) {
            if (!e.target.closest(".conn-search-box")) {
                searchResults.innerHTML = "";
                searchResults.classList.remove("active");
            }
        });

        function doSearch(q) {
            searchResults.innerHTML = "<div class=\"conn-search-loading\">Searching…</div>";
            searchResults.classList.add("active");
            fetch("?action=searchUsers&q=" + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => renderSearchResults(data))
                .catch(() => { searchResults.innerHTML = "<div class=\"conn-search-loading\">Error.</div>"; });
        }

        function renderSearchResults(users) {
            if (!users.length) {
                searchResults.innerHTML = "<div class=\"conn-search-loading\">No users found.</div>";
                return;
            }
            searchResults.innerHTML = users.map(u => {
                const initials = u.username.substring(0, 2).toUpperCase();
                const avatarHtml = u.avatar
                    ? `<img src="${u.avatar}" class="conn-sr-avatar-img" alt="">`
                    : `<div class="conn-sr-avatar-ph">${initials}</div>`;

                let actionHtml = "";
                const fs = u.friendship_status;
                const fid = u.friendship_id;

                if (!fs) {
                    actionHtml = `<button class="conn-btn conn-btn--green conn-btn--sm" onclick="sendRequest(${u.id}, this)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"/></svg>
                        Add friend
                    </button>`;
                } else if (fs === "pending" && u.friendship_sender == u.id) {
                    // ils nous ont envoyé une demande
                    actionHtml = `<span class="conn-badge conn-badge--received">Request received</span>`;
                } else if (fs === "pending") {
                    actionHtml = `<span class="conn-badge conn-badge--pending">Request sent</span>`;
                } else if (fs === "accepted") {
                    actionHtml = `<span class="conn-badge conn-badge--friend">✓ Friend</span>`;
                }

                return `<div class="conn-sr-item">
                    <a href="?page=profile&id=${u.id}" class="conn-sr-avatar conn-sr-avatar--link">${avatarHtml}</a>
                    <div class="conn-sr-info">
                        <a href="?page=profile&id=${u.id}" class="conn-sr-name conn-sr-name--link">${u.username}</a>
                        <span class="conn-sr-country">${u.country || ""}</span>
                    </div>
                    <div class="conn-sr-action">${actionHtml}</div>
                </div>`;
            }).join("");
        }

        function sendRequest(receiverId, btn) {
            btn.disabled = true;
            fetch("?action=sendFriendRequest", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "receiver_id=" + receiverId
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    btn.closest(".conn-sr-item").querySelector(".conn-sr-action").innerHTML =
                        `<span class="conn-badge conn-badge--pending">Request sent</span>`;
                    showToast("✅ Friend request sent!");
                } else {
                    showToast("❌ " + (d.error || "Error"));
                    btn.disabled = false;
                }
            });
        }

        // ── Actions sur les cartes (accept / decline / remove / cancel) ──
        function friendAction(action, friendshipId, card) {
            fetch("?action=friendAction", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "type=" + action + "&friendship_id=" + friendshipId
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    card.style.opacity = "0";
                    card.style.transform = "scale(0.95)";
                    setTimeout(() => card.remove(), 300);
                    showToast(d.message || "Done!");
                    // Mettre à jour le badge de l\'onglet Friends si accept
                    if (action === "accept") {
                        const badge = document.querySelector("[data-tab=\"friends\"] .conn-tab-badge");
                        if (badge) badge.textContent = parseInt(badge.textContent || 0) + 1;
                    }
                } else {
                    showToast("❌ " + (d.error || "Error"));
                }
            });
        }

        // ── Toast ────────────────────────────────────────────────────────
        function showToast(msg) {
            const t = document.createElement("div");
            t.textContent = msg;
            t.style.cssText = `position:fixed;bottom:32px;left:50%;transform:translateX(-50%);
                background:#212121;color:#fff;font-family:"Bungee",cursive;font-size:14px;
                padding:12px 24px;border-radius:999px;border:3px solid #fbad40;
                box-shadow:4px 4px 0 #fbad40;z-index:9999;`;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }
        </script>';
    }

    // ── Sidebar héritée de Dashboard ────────────────────────────────────
    protected function sidebar($user, $avatar, $active = 'connections')
    {
        $username = htmlspecialchars($user['username'] ?? '', ENT_QUOTES);
        $flags    = ['fr' => '🇫🇷', 'al' => '🇦🇱', 'vi' => '🇻🇳', 'us' => '🇺🇸'];
        $country  = $user['country'] ?? '';
        $flag     = $flags[strtolower($country)] ?? '🌍';

        $items = [
            'dashboard'   => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z"/><path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z"/></svg>', 'label' => 'Dashboard', 'page' => 'dashboard'],
            'connections' => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z"/></svg>', 'label' => 'Connections', 'page' => 'connections'],
            'messages'    => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z"/><path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z"/></svg>', 'label' => 'Messages', 'page' => 'messages'],
            'agenda'      => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd"/></svg>', 'label' => 'Agenda', 'page' => 'agenda'],
        ];

        $navItems = '';
        foreach ($items as $key => $item) {
            $isActive = $key === $active ? 'sidebar-nav-item--active' : '';
            $navItems .= '
                <a href="?page=' . $item['page'] . '" class="sidebar-nav-item ' . $isActive . '">
                    ' . $item['icon'] . '
                    <span>' . $item['label'] . '</span>
                </a>';
        }

        return '
        <aside class="dash-sidebar">
            <div class="sidebar-user">
                <div class="sidebar-avatar">' . $avatar . '</div>
                <div class="sidebar-user-info">
                    <div class="sidebar-username">' . $username . '</div>
                    <div class="sidebar-country">' . $flag . ' ' . htmlspecialchars($country, ENT_QUOTES) . '</div>
                </div>
            </div>
            <nav class="sidebar-nav">' . $navItems . '</nav>
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