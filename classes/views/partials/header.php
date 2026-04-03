<header>
    <style>
    .notif-bell-btn {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(255,255,255,0.07);
        border: 1px solid rgba(255,255,255,0.1);
        color: rgba(0, 0, 0, 0.8);
        text-decoration: none;
        font-size: 18px;
        transition: background 0.15s, color 0.15s;
        flex-shrink: 0;
    }
    .notif-bell-btn:hover {
        background: rgba(255,255,255,0.12);
        color: #fff;
    }
    .notif-bell-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 17px;
        height: 17px;
        padding: 0 4px;
        background: #ef4444;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        border-radius: 99px;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        border: 2px solid var(--bg, #0e0e1a);
    }
    </style>
    <nav class="site-nav">
        <a href="?page=home&lang=<?= $lang ?>" class="site-brand">
            FAV
            <span class="badge">TRAVEL</span>
        </a>
        <input type="checkbox" id="nav-toggle-input" class="nav-toggle-input" aria-hidden="true">
        <label for="nav-toggle-input" class="nav-toggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </label>
        <div class="nav-links">
            <a href="?page=home&lang=<?= $lang ?>"><?= $this->lang['nav_home'] ?></a>
            <a href="?page=discover&lang=<?= $lang ?>"><?= $this->lang['nav_discover'] ?></a>
            <a href="?page=news&lang=<?= $lang ?>"><?= $this->lang['nav_news'] ?></a>
        </div>
        <div class="action-group">
            <div class="language-selector">
                <input type="checkbox" id="language-toggle-input" class="language-toggle-input" aria-hidden="true">
                <label for="language-toggle-input" class="language-toggle">
                    <span class="language-icon"><i class="ph ph-globe"></i></span>
                    <span><?= strtoupper($lang) ?></span>
                    <span class="language-arrow"><i class="ph ph-caret-down"></i></span>
                </label>
                <div class="language-menu">
                    <div class="language-menu__header">LANGUAGE</div>
                    <a class="language-menu__item<?= ($lang === 'en') ? ' active' : '' ?>" href="?page=<?= $page ?>&lang=en">
                        <span class="item-code">🇬🇧</span>
                        <span class="item-label">English</span>
                        <span class="item-check"><?= ($lang === 'en') ? '' : '' ?></span>
                    </a>
                    <a class="language-menu__item<?= ($lang === 'fr') ? ' active' : '' ?>" href="?page=<?= $page ?>&lang=fr">
                        <span class="item-code">🇫🇷</span>
                        <span class="item-label">Français</span>
                        <span class="item-check"><?= ($lang === 'fr') ? '' : '' ?></span>
                    </a>
                    <a class="language-menu__item<?= ($lang === 'al') ? ' active' : '' ?>" href="?page=<?= $page ?>&lang=al">
                        <span class="item-code">🇦🇱</span>
                        <span class="item-label">Shqip</span>
                        <span class="item-check"><?= ($lang === 'al') ? '' : '' ?></span>
                    </a>
                    <a class="language-menu__item<?= ($lang === 'vi') ? ' active' : '' ?>" href="?page=<?= $page ?>&lang=vi">
                        <span class="item-code">🇻🇳</span>
                        <span class="item-label">Tiếng Việt</span>
                        <span class="item-check"><?= ($lang === 'vi') ? '' : '' ?></span>
                    </a>
                </div>
            </div>
            <?php if (isset($_SESSION['logged']) && $_SESSION['logged'] === true): ?>
                <?php
                    // Notification bell count
                    $__notifCount = 0;
                    if (isset($pdo) && isset($_SESSION['user_id'])) {
                        try {
                            $__stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
                            $__stmt->execute([$_SESSION['user_id']]);
                            $__notifCount = (int)$__stmt->fetchColumn();
                        } catch (Exception $e) { $__notifCount = 0; }
                    }
                ?>
                <a href="?page=notifications&lang=<?= $lang ?>" class="notif-bell-btn" title="Notifications" aria-label="Notifications">
                    <i class="ph ph-bell"></i>
                    <?php if ($__notifCount > 0): ?>
                        <span class="notif-bell-badge"><?= $__notifCount > 99 ? '99+' : $__notifCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="?page=profile&lang=<?= $lang ?>" class="button-inline secondary">
                    <i class="ph ph-user"></i> <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Profil') ?>
                </a>
                <a href="?page=logout&lang=<?= $lang ?>" class="button-inline primary danger" title="Déconnexion">
                    <i class="ph ph-sign-out"></i>
                </a>
            <?php else: ?>
                <a href="?page=login&lang=<?= $lang ?>" class="button-inline secondary"><i class="ph ph-sign-in"></i> <?= $this->lang['nav_login'] ?></a>
                <a href="?page=register&lang=<?= $lang ?>" class="button-inline primary"><i class="ph ph-user-plus"></i> <?= $this->lang['nav_register'] ?></a>
            <?php endif; ?>
        </div>
    </nav>
</header>