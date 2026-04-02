<?php
class Profile extends View
{
    public function content()
    {
        $user      = $this->data['profileUser']  ?? [];
        $stats     = $this->data['profileStats'] ?? [];
        $userSpots = $this->data['userSpots']    ?? [];

        if (!$user) {
            return '<div style="text-align:center;padding:200px 20px;">
                        <h2>User not found.</h2>
                        <a href="?page=home">Back to home</a>
                    </div>';
        }

        // --- CORRECTION : Définition des variables manquantes ---
        
        // 1. Est-ce que c'est le profil de l'utilisateur connecté ?
        //    Le controller passe maintenant 'isOwn' explicitement.
        $isOwn          = $this->data['isOwn'] ?? (isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($user['id'] ?? 0));
        $friendRelation = $this->data['friendRelation'] ?? null;   // array|null
        $currentUserId  = (int)($this->data['currentUserId'] ?? ($_SESSION['user_id'] ?? 0));

        // 2. Extraction des compteurs depuis le tableau $stats (ton index.php les envoie via 'profileStats')
        $spotsCount     = $stats['spots_count']     ?? 0;
        $followersCount = $stats['followers_count'] ?? 0;
        $followingCount = $stats['following_count'] ?? 0;
        $likesCount     = $stats['total_likes']     ?? 0;

        // --- Fin des corrections de variables ---

        // Avatar initiales si pas d'avatar
        $initials = strtoupper(substr($user['username'] ?? '??', 0, 2));
        $avatar = !empty($user['avatar'])
            ? '<img src="' . htmlspecialchars($user['avatar'], ENT_QUOTES) . '" alt="Avatar" class="prof-avatar-img">'
            : '<div class="prof-avatar-placeholder">' . $initials . '</div>';

        // Badge rôle
        $roleBadge = ($user['role'] ?? 'member') === 'admin'
            ? '<span class="prof-role-badge prof-role-badge--admin">Admin</span>'
            : '<span class="prof-role-badge">Member</span>';

        // Pays flag emoji
        $flags   = ['fr' => '🇫🇷', 'al' => '🇦🇱', 'vi' => '🇻🇳', 'us' => '🇺🇸'];
        $country = $user['country'] ?? '';
        $flag    = $flags[strtolower($country)] ?? '🌍';

        // Date inscription
        $joined = date('F Y', strtotime($user['created_at'] ?? 'now'));

        // Build spots grid HTML
        $spotsHtml = '<style>
            .prof-spot-card { border: 2px solid rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; background: rgba(255,255,255,0.05); transition: all 0.2s; display: block; text-decoration: none; color: inherit; }
            .prof-spot-card:hover { border-color: #fbad40; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.5); }
        </style>';
        
        if ($spotsCount > 0 && !empty($userSpots)) {
            $spotsHtml .= '<div class="prof-spots-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-top: 16px;">';
            foreach ($userSpots as $spot) {
                $imgSrc = !empty($spot['image']) ? htmlspecialchars($spot['image'], ENT_QUOTES) : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23212121%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%23fff%22 font-family=%22sans-serif%22 font-size=%2224%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3ENO IMAGE%3C/text%3E%3C/svg%3E';
                $title = htmlspecialchars($spot['title'], ENT_QUOTES);
                $location = htmlspecialchars($spot['location'] ?? 'Unknown location', ENT_QUOTES);
                $spotId = (int)$spot['id'];
                
                $spotsHtml .= '
                <a href="?page=spot&id=' . $spotId . '" class="prof-spot-card">
                    <div style="aspect-ratio: 4/3; overflow: hidden; position: relative;">
                        <img src="' . $imgSrc . '" alt="' . $title . '" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.6); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                            ♥ ' . ($spot['likes_count'] ?? 0) . '
                        </div>
                    </div>
                    <div style="padding: 12px;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: \'Bungee\', cursive;">' . $title . '</h3>
                        <p style="margin: 4px 0 0 0; font-size: 14px; color: #ccc;">📍 ' . $location . '</p>
                    </div>
                </a>';
            }
            $spotsHtml .= '</div>';
            
            // Add a "view all" button if there are more spots than shown
            if ($spotsCount > count($userSpots)) {
                $spotsHtml .= '<div style="margin-top: 24px; text-align: center;">
                                <a href="?page=spots&user=' . $user['id'] . '" class="prof-btn prof-btn--outline">View all ' . $spotsCount . ' spots</a>
                               </div>';
            }
        }

        // Bio et statut 
        $bio    = htmlspecialchars($user['bio'] ?? '', ENT_QUOTES);
        $status = (!empty($user['status'])) 
            ? htmlspecialchars($user['status'], ENT_QUOTES) 
            : "Hey, I'm on FAV!";

        return'
        <link rel="stylesheet" href="public/css/profile.css">
        <div class="prof-wrapper">

            <!-- ── BANNIÈRE + AVATAR ── -->
            <div class="prof-banner">
                <div class="prof-banner-bg"></div>
                <div class="prof-banner-content">
                    <div class="prof-avatar-wrap">
                        ' . $avatar . '
                        ' . ($isOwn ? '
                            <label class="prof-avatar-edit" for="avatarInput" title="Modifier l\'avatar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            </label>
                            <input type="file" id="avatarInput" style="display:none" accept="image/jpeg,image/png,image/webp">
                        ' : '') . '
                    </div>
                    <div class="prof-identity">
                        <div class="prof-name-row">
                            <h1 class="prof-username">' . htmlspecialchars($user['username'], ENT_QUOTES) . '</h1>
                            ' . $roleBadge . '
                        </div>
                        <p class="prof-meta">
                            <span>' . $flag . ' ' . htmlspecialchars($country, ENT_QUOTES) . '</span>
                            <span class="prof-meta-sep">·</span>
                            <span>Joined ' . $joined . '</span>
                        </p>

                        <!-- ── STATUT MODIFIABLE ── -->
                        ' . ($isOwn ? '
                        <div class="prof-status-wrap">
                            <span class="prof-status-display" id="statusDisplay">💬 ' . $status . '</span>
                            <input type="text" class="prof-status-input" id="statusInput"
                                   value="' . $status . '"
                                   maxlength="100"
                                   placeholder="Your status..."
                                   style="display:none;">
                            <button class="prof-status-edit-btn" id="statusEditBtn" title="Edit status">✏️</button>
                            <button class="prof-status-save-btn" id="statusSaveBtn" style="display:none;">✅</button>
                        </div>
                        ' : '<p class="prof-status-display">💬 ' . $status . '</p>') . '
                    </div>

                    <div class="prof-actions">
                        ' . ($isOwn
                            ? '
                               <!-- Groupe navigation rapide -->
                               <div class="prof-nav-group">
                                   <a href="?page=dashboard" class="prof-nav-btn" title="Dashboard">
                                       <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="16" height="16"><path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z"/><path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z"/></svg>
                                       Dashboard
                                   </a>
                                   <a href="?page=messages" class="prof-nav-btn" title="Messages">
                                       <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="16" height="16"><path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z"/><path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z"/></svg>
                                       Messages
                                   </a>
                                   <a href="?page=agenda" class="prof-nav-btn" title="Agenda">
                                       <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="16" height="16"><path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd"/></svg>
                                       Agenda
                                   </a>
                               </div>

                               <!-- Bouton Edit Profil -->
                               <a href="?page=profile&action=edit" class="prof-btn prof-btn--primary">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="16" height="16">
                                    <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z"/>
                                </svg>
                                Edit
                               </a>

                               <!-- Bouton Rouage → Account Info -->
                               <button class="prof-btn prof-btn--icon" id="toggleAccountInfo" title="Account settings">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="18" height="18">
                                    <path fill-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 0 0-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 0 0-2.282.819l-.922 1.597a1.875 1.875 0 0 0 .432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 0 0 0 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 0 0-.432 2.385l.922 1.597a1.875 1.875 0 0 0 2.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 0 0 2.28-.819l.923-1.597a1.875 1.875 0 0 0-.432-2.385l-.84-.692c-.095-.078-.17-.229-.154-.43a7.614 7.614 0 0 0 0-1.139c-.016-.2.059-.352.153-.43l.84-.692c.708-.582.891-1.59.433-2.385l-.922-1.597a1.875 1.875 0 0 0-2.282-.818l-1.02.382c-.114.043-.282.031-.449-.083a7.49 7.49 0 0 0-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 0 0-1.85-1.567h-1.843ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clip-rule="evenodd" />
                                </svg>
                               </button>

                               <!-- Bouton Sign Out -->
                               <a href="?page=logout" class="prof-btn prof-btn--danger">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="16" height="16">
                                    <path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 0 0 6 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5h6a1.5 1.5 0 0 0 1.5-1.5V15a.75.75 0 0 1 1.5 0v3.75a3 3 0 0 1-3 3h-6a3 3 0 0 1-3-3V5.25a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3V9A.75.75 0 0 1 15 9V5.25a1.5 1.5 0 0 0-1.5-1.5h-6Zm5.03 4.72a.75.75 0 0 1 0 1.06l-1.72 1.72h10.94a.75.75 0 0 1 0 1.5H10.81l1.72 1.72a.75.75 0 1 1-1.06 1.06l-3-3a.75.75 0 0 1 0-1.06l3-3a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                                </svg>
                                Sign Out
                               </a>'
                            : $this->renderOtherProfileActions($user['id'], $currentUserId, $friendRelation)
                        ) . '
                    </div>
                </div>
            </div>

            <!-- ── STATS ── -->
            <div class="prof-stats-bar">
                <div class="prof-stat">
                    <span class="prof-stat-value">' . $spotsCount . '</span>
                    <span class="prof-stat-label">Spots</span>
                </div>
                <div class="prof-stat">
                    <span class="prof-stat-value">' . $followersCount . '</span>
                    <span class="prof-stat-label">Followers</span>
                </div>
                <div class="prof-stat">
                    <span class="prof-stat-value">' . $followingCount . '</span>
                    <span class="prof-stat-label">Following</span>
                </div>
                <div class="prof-stat">
                    <span class="prof-stat-value">' . $likesCount . '</span>
                    <span class="prof-stat-label">Likes</span>
                </div>
            </div>

            <!-- ── ACCOUNT INFO PANEL (caché par défaut) ── -->
            <div id="accountInfoPanel" style="display:none; max-width:1000px; margin:24px auto; padding:0 24px;">
                <div class="prof-card">
                    <h2 class="prof-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                        </svg>
                        Account Info
                    </h2>
                    <ul class="prof-info-list">
                        <li>
                            <span class="prof-info-key">Email</span>
                            <span class="prof-info-val">' . htmlspecialchars($user['email'], ENT_QUOTES) . '</span>
                        </li>
                        <li>
                            <span class="prof-info-key">Country</span>
                            <span class="prof-info-val">' . $flag . ' ' . htmlspecialchars($country ?: '—', ENT_QUOTES) . '</span>
                        </li>
                        <li>
                            <span class="prof-info-key">Language</span>
                            <span class="prof-info-val">' . htmlspecialchars(strtoupper($user['language'] ?? '—'), ENT_QUOTES) . '</span>
                        </li>
                        <li>
                            <span class="prof-info-key">Role</span>
                            <span class="prof-info-val">' . ucfirst($user['role']) . '</span>
                        </li>
                        <li>
                            <span class="prof-info-key">Status</span>
                            <span class="prof-info-val prof-status ' . ($user['is_active'] ? 'prof-status--active' : 'prof-status--banned') . '">
                                ' . ($user['is_active'] ? '✅ Active' : '🚫 Banned') . '
                            </span>
                        </li>
                        <li>
                            <span class="prof-info-key">Member since</span>
                            <span class="prof-info-val">' . $joined . '</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- ── CORPS (2 colonnes) ── -->
            <div class="prof-body">

                <!-- Bio de la personne -->
                <div class="prof-card prof-card--bio">
                    <h2 class="prof-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path fill-rule="evenodd" d="M4.848 2.771A49.144 49.144 0 0 1 12 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 0 1-3.476.383.39.39 0 0 0-.297.17l-2.755 4.133a.75.75 0 0 1-1.248 0l-2.755-4.133a.39.39 0 0 0-.297-.17 48.9 48.9 0 0 1-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.68 3.348-3.97Z" clip-rule="evenodd" />
                        </svg>
                        Bio
                    </h2>
                    ' . ($bio
                        ? '<p class="prof-bio-text">' . nl2br($bio) . '</p>'
                        : '<p class="prof-bio-text prof-bio--empty">No bio yet.</p>'
                    ) . '
                </div>

                <!-- Spots du user -->
                <div class="prof-card prof-card--spots">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
                        <h2 class="prof-card-title" style="margin-bottom:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-2.003 3.5-4.697 3.5-8.327a8 8 0 1 0-16 0c0 3.63 1.556 6.324 3.5 8.327a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.144.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                            </svg>
                            ' . ($isOwn ? 'My' : htmlspecialchars($user['username'], ENT_QUOTES) . "'s") . ' Spots <span class="prof-count-badge">' . $spotsCount . '</span>
                        </h2>
                        ' . ($isOwn ? '<button class="prof-btn prof-btn--primary" onclick="openSpotModal()" style="padding: 6px 14px; font-size: 13px;">+ Add Spot</button>' : '') . '
                    </div>
                    ' . ($spotsCount == 0
                        ? '<div class="prof-empty">
                              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="48" height="48">
                                  <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-2.003 3.5-4.697 3.5-8.327a8 8 0 1 0-16 0c0 3.63 1.556 6.324 3.5 8.327a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.144.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                              </svg>
                              <p>No spots published yet.</p>
                              <a href="?page=spots" class="prof-btn prof-btn--outline">Discover Spots</a>
                           </div>'
                        : $spotsHtml
                    ) . '
                </div>

            </div><!-- /.prof-body -->

        </div><!-- /.prof-wrapper -->

        <style>
            /* ── MODAL ── */
            .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center; }
            .modal-box { background: #fff; border: 4px solid #212121; border-radius: 20px; box-shadow: 8px 8px 0 #212121; width: 100%; max-width: 420px; overflow: hidden; }
            .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; background: #3aa26b; border-bottom: 3px solid #212121; }
            .modal-title { font-family: \'Bungee\', cursive; font-size: 18px; color: #fff; margin: 0; }
            .modal-close { background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.4); border-radius: 8px; color: #fff; width: 32px; height: 32px; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
            .modal-form { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; }
            .modal-field { display: flex; flex-direction: column; gap: 6px; flex: 1; text-align: left; }
            .modal-row { display: flex; gap: 12px; }
            .modal-field label { font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.07em; }
            .modal-field input, .modal-field textarea, .modal-field select { border: 2px solid #212121; border-radius: 10px; padding: 10px 14px; font-size: 14px; font-family: \'Inter\', sans-serif; background: #f5eedc; color: #212121; outline: none; transition: border-color 0.15s; }
            .modal-field input:focus, .modal-field textarea:focus, .modal-field select:focus { border-color: #3aa26b; }
            .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 4px; }
        </style>

        <!-- Modal Ajouter un spot -->
        <div class="modal-overlay" id="spotModal" style="display:none">
            <div class="modal-box">
                <div class="modal-header">
                    <h2 class="modal-title">NEW SPOT</h2>
                    <button class="modal-close" onclick="closeSpotModal()">✕</button>
                </div>
                <form class="modal-form" onsubmit="return false;">
                    <div class="modal-field">
                        <label>SPOT TITLE</label>
                        <input type="text" placeholder="e.g. Hidden Beach Paradise" id="spotTitle">
                    </div>
                    <div class="modal-field">
                        <label>LOCATION</label>
                        <input type="text" placeholder="e.g. Bali, Indonesia" id="spotLocation">
                    </div>
                    <div class="modal-field">
                        <label>DESCRIPTION</label>
                        <textarea placeholder="Describe this amazing spot..." id="spotDescription" rows="3"></textarea>
                    </div>
                    <div class="modal-row">
                        <div class="modal-field">
                            <label>CATEGORY</label>
                            <select id="spotCategory">
                                <option value="nature">🌿 Nature</option>
                                <option value="ville">🏙️ City</option>
                                <option value="voyage">🏖️ Beach/Trip</option>
                                <option value="aventure">⛰️ Mountain/Adventure</option>
                                <option value="gastronomie">🍜 Food</option>
                                <option value="culture">🎭 Culture</option>
                                <option value="autre">✨ Other</option>
                            </select>
                        </div>
                        <div class="modal-field">
                            <label>IMAGE</label>
                            <input type="file" accept="image/*" id="spotImage">
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="prof-btn prof-btn--outline" onclick="closeSpotModal()">Cancel</button>
                        <button type="button" class="prof-btn prof-btn--primary" onclick="saveSpot()">Publish Spot</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        // ── Toggle Account Info Panel via rouage ──
        const toggleBtn    = document.getElementById("toggleAccountInfo");
        const accountPanel = document.getElementById("accountInfoPanel");

        if (toggleBtn && accountPanel) {
            toggleBtn.addEventListener("click", function () {
                const isVisible = accountPanel.style.display !== "none";
                accountPanel.style.display = isVisible ? "none" : "block";
                toggleBtn.style.opacity    = isVisible ? "1" : "0.5";
            });
        }

        // ── Statut modifiable inline ──
        const statusDisplay = document.getElementById("statusDisplay");
        const statusInput   = document.getElementById("statusInput");
        const editBtn       = document.getElementById("statusEditBtn");
        const saveBtn       = document.getElementById("statusSaveBtn");

        if (editBtn) {
            editBtn.addEventListener("click", function () {
                statusDisplay.style.display = "none";
                editBtn.style.display       = "none";
                statusInput.style.display   = "inline-block";
                saveBtn.style.display       = "inline-block";
                statusInput.focus();
            });
        }

        if (saveBtn) {
            saveBtn.addEventListener("click", function () {
                const newStatus = statusInput.value.trim() || "Hey, I\'m on FAV!";

                // Appel API pour sauvegarder en BDD
                fetch("?page=profile&action=updateStatus", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "status=" + encodeURIComponent(newStatus)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        statusDisplay.textContent = "💬 " + newStatus;
                        showToast("✅ Status updated!");
                    } else {
                        showToast("❌ Error saving status.");
                    }
                })
                .catch(() => {
                    // Fallback si le fetch échoue : on met quand même à jour l\'affichage
                    statusDisplay.textContent = "💬 " + newStatus;
                    showToast("⚠️ Saved locally only.");
                });

                statusDisplay.style.display = "inline";
                statusInput.style.display   = "none";
                saveBtn.style.display       = "none";
                editBtn.style.display       = "inline-block";
            });

            // Sauvegarder aussi avec la touche Entrée
            statusInput.addEventListener("keydown", function (e) {
                if (e.key === "Enter") saveBtn.click();
                if (e.key === "Escape") {
                    statusDisplay.style.display = "inline";
                    statusInput.style.display   = "none";
                    saveBtn.style.display       = "none";
                    editBtn.style.display       = "inline-block";
                }
            });
        }

        // ── Toast notification ──
        function showToast(msg) {
            const toast = document.createElement("div");
            toast.textContent = msg;
            toast.style.cssText = `
                position: fixed;
                bottom: 32px;
                left: 50%;
                transform: translateX(-50%);
                background: #212121;
                color: #fff;
                font-family: "Bungee", cursive;
                font-size: 14px;
                padding: 12px 24px;
                border-radius: 999px;
                border: 3px solid #fbad40;
                box-shadow: 4px 4px 0px #fbad40;
                z-index: 9999;
                animation: fadeInUp 0.3s ease;
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        const avatarInput = document.getElementById("avatarInput");
if (avatarInput) {
    avatarInput.addEventListener("change", function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append("avatar", file);

        fetch("?page=profile&action=updateAvatar", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload(); // On recharge pour voir la nouvelle image
            } else {
                alert("Erreur : " + data.error);
            }
        })
        .catch(err => console.error("Erreur upload:", err));
    });
}

        // ── Boutons profil étranger ──
        ' . (!$isOwn ? $this->renderOtherProfileJs() : '') . '

        function openSpotModal() {
            document.getElementById("spotModal").style.display = "flex";
        }

        function closeSpotModal() {
            document.getElementById("spotModal").style.display = "none";
        }

        function saveSpot() {
            const title = document.getElementById("spotTitle").value.trim();
            const location = document.getElementById("spotLocation").value.trim();
            const description = document.getElementById("spotDescription").value.trim();
            const category = document.getElementById("spotCategory").value;
            const imageFile = document.getElementById("spotImage").files[0];

            if (!title || !location) {
                alert("Please fill in title and location");
                return;
            }

            const formData = new FormData();
            formData.append("title", title);
            formData.append("location", location);
            formData.append("description", description);
            formData.append("category", category);
            if (imageFile) {
                formData.append("image", imageFile);
            }

            fetch("?page=spots&action=create", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast("✅ Spot published!");
                    closeSpotModal();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast("❌ Error creating spot");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("⚠️ Error");
            });
        }
        </script>';
    }

    // ──────────────────────────────────────────────────────────────
    //  Boutons d'action pour le profil d'un autre utilisateur
    // ──────────────────────────────────────────────────────────────
    private function renderOtherProfileActions(int $profileUserId, int $currentUserId, $relation): string
    {
        $sendMsgBtn = '
            <button class="prof-btn prof-btn--primary" id="sendMsgBtn" data-uid="' . $profileUserId . '">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="16" height="16">
                    <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z"/>
                    <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z"/>
                </svg>
                Message
            </button>';

        // Determine friend button state
        if (!$relation) {
            // No relation → Add Friend
            $friendBtn = '
                <button class="prof-btn prof-btn--outline" id="friendActionBtn"
                        data-action="add" data-uid="' . $profileUserId . '">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                        <path d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"/>
                    </svg>
                    Add Friend
                </button>';
        } elseif ($relation['status'] === 'accepted') {
            // Already friends → Remove Friend
            $friendBtn = '
                <button class="prof-btn prof-btn--outline prof-btn--friend" id="friendActionBtn"
                        data-action="remove" data-fid="' . $relation['id'] . '">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                        <path d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM18.75 10.5a.75.75 0 000 1.5h4.5a.75.75 0 000-1.5h-4.5z"/>
                    </svg>
                    Friends ✓
                </button>';
        } elseif ($relation['status'] === 'pending' && (int)$relation['sender_id'] === $currentUserId) {
            // Current user sent the request → Cancel
            $friendBtn = '
                <button class="prof-btn prof-btn--outline" id="friendActionBtn"
                        data-action="cancel" data-fid="' . $relation['id'] . '">
                    ↩️ Cancel Request
                </button>';
        } elseif ($relation['status'] === 'pending' && (int)$relation['receiver_id'] === $currentUserId) {
            // Other user sent a request → Accept or Decline
            $friendBtn = '
                <div style="display:flex;gap:8px;">
                    <button class="prof-btn prof-btn--primary" id="acceptFriendBtn"
                            data-fid="' . $relation['id'] . '">
                        ✅ Accept
                    </button>
                    <button class="prof-btn prof-btn--danger" id="declineFriendBtn"
                            data-fid="' . $relation['id'] . '">
                        ❌ Decline
                    </button>
                </div>';
        } else {
            $friendBtn = '';
        }

        return $friendBtn . $sendMsgBtn;
    }

    // JS for other-profile buttons (injected only on other profiles)
    private function renderOtherProfileJs(): string
    {
        return '
        // ── Send Message button ──
        const sendMsgBtn = document.getElementById("sendMsgBtn");
        if (sendMsgBtn) {
            sendMsgBtn.addEventListener("click", function () {
                const uid = this.dataset.uid;
                fetch("?action=openDirect", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "target_id=" + uid
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        window.location.href = "?page=messages&conv=" + d.conv_id;
                    } else {
                        showToast("❌ Could not open conversation.");
                    }
                });
            });
        }

        // ── Add / Cancel / Remove friend button ──
        const friendBtn = document.getElementById("friendActionBtn");
        if (friendBtn) {
            friendBtn.addEventListener("click", function () {
                const action = this.dataset.action;
                const uid    = this.dataset.uid  || "";
                const fid    = this.dataset.fid  || "";

                if (action === "add") {
                    fetch("?action=sendFriendRequest", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "receiver_id=" + uid
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            friendBtn.textContent = "↩️ Cancel Request";
                            friendBtn.dataset.action = "cancel";
                            showToast("✅ Friend request sent!");
                            // Re-fetch to get friendship id for cancel
                            location.reload();
                        } else {
                            showToast("❌ " + (d.error || "Error"));
                        }
                    });
                } else if (action === "cancel") {
                    fetch("?action=friendAction", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "type=cancel&friendship_id=" + fid
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            friendBtn.innerHTML = \'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"/></svg> Add Friend\';
                            friendBtn.dataset.action = "add";
                            friendBtn.dataset.uid    = \'\' + (friendBtn.dataset.uid || "");
                            showToast("↩️ Request cancelled.");
                        }
                    });
                } else if (action === "remove") {
                    if (!confirm("Remove this friend?")) return;
                    fetch("?action=friendAction", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "type=remove&friendship_id=" + fid
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            friendBtn.innerHTML = \'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"/></svg> Add Friend\';
                            friendBtn.dataset.action = "add";
                            delete friendBtn.dataset.fid;
                            showToast("👋 Friend removed.");
                        }
                    });
                }
            });
        }

        // ── Accept / Decline buttons (incoming request) ──
        const acceptBtn  = document.getElementById("acceptFriendBtn");
        const declineBtn = document.getElementById("declineFriendBtn");

        if (acceptBtn) {
            acceptBtn.addEventListener("click", function () {
                const fid = this.dataset.fid;
                fetch("?action=friendAction", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "type=accept&friendship_id=" + fid
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        showToast("✅ You are now friends!");
                        location.reload();
                    }
                });
            });
        }

        if (declineBtn) {
            declineBtn.addEventListener("click", function () {
                const fid = this.dataset.fid;
                fetch("?action=friendAction", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "type=decline&friendship_id=" + fid
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        location.reload();
                    }
                });
            });
        }
        ';
    }
}