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
<header style="width:100%; padding:20px 270px; box-sizing:border-box; position:sticky; top:0; z-index:100;">
    <nav style="max-width:1024px; margin:0 auto; padding:3px; background:#F4F0E6; box-shadow:6px 6px 0px #222222; border-radius:9999px; outline:3px #222222 solid; display:flex; justify-content:space-between; align-items:center; padding:0 28px; height:71px;">
        
        <!-- LOGO -->
        <div style="display:flex; align-items:center; gap:7px;">
            <a href="?page=home&lang=' . ($_GET['lang'] ?? 'en') . '" style="color:#3AA26B; font-family:Bungee,sans-serif; font-size:27px; letter-spacing:2px; text-decoration:none;">FAV</a>
            <span style="background:#FBAD40; color:white; font-family:Bungee,sans-serif; font-size:9px; padding:2px 8px; border-radius:999px; transform:rotate(-8deg); display:inline-block; letter-spacing:2px;">TRAVEL</span>
        </div>

        <!-- LIENS -->
        <div style="display:flex; gap:28px;">
            <a href="?page=home&lang=' . ($_GET['lang'] ?? 'en') . '" style="color:#222; font-family:Inter,sans-serif; font-size:16px; text-decoration:none; font-weight:500;">Home</a>
            <a href="?page=spots&lang=' . ($_GET['lang'] ?? 'en') . '" style="color:#222; font-family:Inter,sans-serif; font-size:16px; text-decoration:none; font-weight:500;">Spots</a>
            <a href="?page=about&lang=' . ($_GET['lang'] ?? 'en') . '" style="color:#222; font-family:Inter,sans-serif; font-size:16px; text-decoration:none; font-weight:500;">About</a>
        </div>

        <!-- BOUTONS -->
        <div style="display:flex; align-items:center; gap:10px;">
            <!-- Sélecteur langue -->
            <div style="background:white; box-shadow:2px 2px 0px #222; border-radius:999px; outline:2px #222 solid; padding:8px 16px; font-family:Inter,sans-serif; font-size:14px; font-weight:600; display:flex; gap:8px;">
                <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=fr" style="text-decoration:none; color:#222;">FR</a> |
                <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=en" style="text-decoration:none; color:#222;">EN</a> |
                <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=al" style="text-decoration:none; color:#222;">AL</a> |
                <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=vi" style="text-decoration:none; color:#222;">VI</a>
            </div>
            <!-- Sign In -->
            <a href="?page=login&lang=' . ($_GET['lang'] ?? 'en') . '" style="background:white; box-shadow:3px 3px 0px #222; border-radius:999px; outline:2px #222 solid; padding:9px 18px; font-family:Bungee,sans-serif; font-size:12px; text-decoration:none; color:#222;">SIGN IN</a>
            <!-- Sign Up -->
            <a href="?page=register&lang=' . ($_GET['lang'] ?? 'en') . '" style="background:#FBAD40; box-shadow:3px 3px 0px #222; border-radius:999px; outline:2px #222 solid; padding:9px 18px; font-family:Bungee,sans-serif; font-size:12px; text-decoration:none; color:white;">SIGN UP</a>
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