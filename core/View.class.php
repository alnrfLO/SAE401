<?php
class View {
    protected $data;
    protected $lang;
    
    public function __construct($data = []) {
        global $lang;
        $this->data = $data;
        $this->lang = $lang;
    }
    
    public function render() {
        return '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAV</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>
<header style="background:#fff; padding:15px 30px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
    <nav style="display:flex; align-items:center; gap:20px;">
        <a href="?page=home&lang=' . ($_GET['lang'] ?? 'en') . '" style="font-weight:bold; font-size:20px; text-decoration:none; color:#000;">FAV</a>
        <a href="?page=spots&lang=' . ($_GET['lang'] ?? 'en') . '" style="text-decoration:none; color:#333;">' . $this->lang['nav_spots'] . '</a>
    </nav>
    <nav style="display:flex; align-items:center; gap:15px;">
        <a href="?page=login&lang=' . ($_GET['lang'] ?? 'en') . '" style="text-decoration:none; color:#333;">' . $this->lang['nav_login'] . '</a>
        <a href="?page=register&lang=' . ($_GET['lang'] ?? 'en') . '" style="text-decoration:none; background:#000; color:#fff; padding:8px 16px; border-radius:5px;">' . $this->lang['nav_register'] . '</a>
        <div style="display:flex; gap:8px;">
            <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=fr" style="text-decoration:none; color:#333; font-size:13px;">FR</a>
            <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=en" style="text-decoration:none; color:#333; font-size:13px;">EN</a>
            <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=al" style="text-decoration:none; color:#333; font-size:13px;">AL</a>
            <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=vi" style="text-decoration:none; color:#333; font-size:13px;">VI</a>
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
    
    public function content() {
        return '';
    }
}