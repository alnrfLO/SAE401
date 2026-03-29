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
        $lang = $_GET['lang'] ?? 'en';
        $page = $this->data['page'] ?? 'home';

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
                        <a href="?page=home&lang=' . $lang . '" class="site-brand">
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
                            <a href="?page=home&lang=' . $lang . '">' . $this->lang['nav_home'] . '</a>
                            <a href="?page=spots&lang=' . $lang . '">' . $this->lang['nav_discover'] . '</a>
                            <a href="?page=about&lang=' . $lang . '">' . $this->lang['nav_presentation'] . '</a>
                            <a href="?page=about&lang=' . $lang . '">' . $this->lang['nav_about'] . '</a>
                            <a href="?page=about&lang=' . $lang . '">' . $this->lang['nav_news'] . '</a>
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
                                    <span>' . strtoupper($lang) . '</span>
                                    <span class="language-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3.5 5.25L7 8.75L10.5 5.25" stroke="#222222" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                </label>
                                <div class="language-menu">
                                    <div class="language-menu__header">LANGUAGE</div>
                                    <a class="language-menu__item' . (($lang === 'en') ? ' active' : '') . '" href="?page=' . $page . '&lang=en">
                                        <span class="item-code">GB</span>
                                        <span class="item-label">English</span>
                                        <span class="item-check">' . (($lang === 'en') ? '✓' : '') . '</span>
                                    </a>
                                    <a class="language-menu__item' . (($lang === 'fr') ? ' active' : '') . '" href="?page=' . $page . '&lang=fr">
                                        <span class="item-code">FR</span>
                                        <span class="item-label">Français</span>
                                        <span class="item-check">' . (($lang === 'fr') ? '✓' : '') . '</span>
                                    </a>
                                    <a class="language-menu__item' . (($lang === 'al') ? ' active' : '') . '" href="?page=' . $page . '&lang=al">
                                        <span class="item-code">AL</span>
                                        <span class="item-label">Shqip</span>
                                        <span class="item-check">' . (($lang === 'al') ? '✓' : '') . '</span>
                                    </a>
                                    <a class="language-menu__item' . (($lang === 'vi') ? ' active' : '') . '" href="?page=' . $page . '&lang=vi">
                                        <span class="item-code">VN</span>
                                        <span class="item-label">Tiếng Việt</span>
                                        <span class="item-check">' . (($lang === 'vi') ? '✓' : '') . '</span>
                                    </a>
                                </div>
                            </div>
                            <a href="?page=login&lang=' . $lang . '" class="button-inline secondary">' . $this->lang['nav_login'] . '</a>
                            <a href="?page=register&lang=' . $lang . '" class="button-inline primary">' . $this->lang['nav_register'] . '</a>
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
