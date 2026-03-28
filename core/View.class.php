<?php
class View
{
    protected $data;
    protected $lang;

    public function __construct($data = [])
    {
        global $lang;
        $this->data = $data;
        $this->lang = $lang;
    }

    public function render()
    {
        return
            '<!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>FAV</title>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Bungee:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="/public/assets/css/view.css">
            </head>
            <body>
                <header>
                    <nav class="site-nav">
                        <a href="?page=home&lang=' . ($_GET['lang'] ?? 'en') . '" class="site-brand">
                            FAV
                            <span class="badge">TRAVEL</span>
                        </a>
                        <div class="nav-links">
                            <a href="?page=home&lang=' . ($_GET['lang'] ?? 'en') . '">Home</a>
                            <a href="?page=spots&lang=' . ($_GET['lang'] ?? 'en') . '">Discover</a>
                            <a href="?page=about&lang=' . ($_GET['lang'] ?? 'en') . '">Presentation</a>
                            <a href="?page=about&lang=' . ($_GET['lang'] ?? 'en') . '">About</a>
                            <a href="?page=about&lang=' . ($_GET['lang'] ?? 'en') . '">News</a>
                        </div>
                        <div class="action-group">
                            <div class="language-selector">
                                <input type="checkbox" id="language-toggle-input" class="language-toggle-input" aria-hidden="true">
                                <label for="language-toggle-input" class="language-toggle">
                                    <span class="language-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <g clip-path="url(#clip0_139_394)">
                                                <path d="M8.00016 14.6666C11.6821 14.6666 14.6668 11.6818 14.6668 7.99992C14.6668 4.31802 11.6821 1.33325 8.00016 1.33325C4.31826 1.33325 1.3335 4.31802 1.3335 7.99992C1.3335 11.6818 4.31826 14.6666 8.00016 14.6666Z" stroke="#222222" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M8.00016 1.33325C6.28832 3.13069 5.3335 5.51775 5.3335 7.99992C5.3335 10.4821 6.28832 12.8691 8.00016 14.6666C9.71201 12.8691 10.6668 10.4821 10.6668 7.99992C10.6668 5.51775 9.71201 3.13069 8.00016 1.33325Z" stroke="#222222" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M1.3335 8H14.6668" stroke="#222222" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_139_394">
                                                    <rect width="16" height="16" fill="white"/>
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </span>
                                    <span>' . strtoupper($_GET['lang'] ?? 'en') . '</span>
                                    <span class="language-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3.5 5.25L7 8.75L10.5 5.25" stroke="#222222" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                </label>
                                <div class="language-menu">
                                    <div class="language-menu__header">LANGUAGE</div>
                                    <a class="language-menu__item' . (((($_GET['lang'] ?? 'en') === 'en') ? ' active' : '')) . '" href="?page=' . ($this->data['page'] ?? 'home') . '&lang=en">
                                        <span class="item-code">GB</span>
                                        <span class="item-label">English</span>
                                        <span class="item-check">' . (((($_GET['lang'] ?? 'en') === 'en') ? '✓' : '')) . '</span>
                                    </a>
                                    <a class="language-menu__item' . (((($_GET['lang'] ?? 'en') === 'fr') ? ' active' : '')) . '" href="?page=' . ($this->data['page'] ?? 'home') . '&lang=fr">
                                        <span class="item-code">FR</span>
                                        <span class="item-label">Français</span>
                                        <span class="item-check">' . (((($_GET['lang'] ?? 'en') === 'fr') ? '✓' : '')) . '</span>
                                    </a>
                                    <a class="language-menu__item' . (((($_GET['lang'] ?? 'en') === 'al') ? ' active' : '')) . '" href="?page=' . ($this->data['page'] ?? 'home') . '&lang=al">
                                        <span class="item-code">AL</span>
                                        <span class="item-label">Shqip</span>
                                        <span class="item-check">' . (((($_GET['lang'] ?? 'en') === 'al') ? '✓' : '')) . '</span>
                                    </a>
                                    <a class="language-menu__item' . (((($_GET['lang'] ?? 'en') === 'vi') ? ' active' : '')) . '" href="?page=' . ($this->data['page'] ?? 'home') . '&lang=vi">
                                        <span class="item-code">VN</span>
                                        <span class="item-label">Tiếng Việt</span>
                                        <span class="item-check">' . (((($_GET['lang'] ?? 'en') === 'vi') ? '✓' : '')) . '</span>
                                    </a>
                                </div>
                            </div>
                            <a href="?page=login&lang=' . ($_GET['lang'] ?? 'en') . '" class="button-inline secondary">SIGN IN</a>
                            <a href="?page=register&lang=' . ($_GET['lang'] ?? 'en') . '" class="button-inline primary">SIGN UP</a>
                        </div>
                    </nav>
                </header>
                <main>
                    ' . $this->content() . '
                </main>
                <footer>
                    <a href="?page=legal">' . $this->lang['footer_legal'] . '</a>
                    <a href="?page=terms">' . $this->lang['footer_terms'] . '</a>
                    <a href="?page=privacy">' . $this->lang['footer_privacy'] . '</a>
                </footer>
            </body>
            </html>';
    }

    public function content()
    {
        return '';
    }
}
