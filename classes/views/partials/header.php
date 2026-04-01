<header>
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
                        <span class="item-code">GB</span>
                        <span class="item-label">English</span>
                        <span class="item-check"><?= ($lang === 'en') ? '✓' : '' ?></span>
                    </a>
                    <a class="language-menu__item<?= ($lang === 'fr') ? ' active' : '' ?>" href="?page=<?= $page ?>&lang=fr">
                        <span class="item-code">FR</span>
                        <span class="item-label">Français</span>
                        <span class="item-check"><?= ($lang === 'fr') ? '✓' : '' ?></span>
                    </a>
                    <a class="language-menu__item<?= ($lang === 'al') ? ' active' : '' ?>" href="?page=<?= $page ?>&lang=al">
                        <span class="item-code">AL</span>
                        <span class="item-label">Shqip</span>
                        <span class="item-check"><?= ($lang === 'al') ? '✓' : '' ?></span>
                    </a>
                    <a class="language-menu__item<?= ($lang === 'vi') ? ' active' : '' ?>" href="?page=<?= $page ?>&lang=vi">
                        <span class="item-code">VN</span>
                        <span class="item-label">Tiếng Việt</span>
                        <span class="item-check"><?= ($lang === 'vi') ? '✓' : '' ?></span>
                    </a>
                </div>
            </div>
            <?php if (isset($_SESSION['logged']) && $_SESSION['logged'] === true): ?>
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
