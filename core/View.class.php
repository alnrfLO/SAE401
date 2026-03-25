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
    <header>
        <nav>
            <a href="?page=home&lang=' . ($_GET['lang'] ?? 'en') . '">FAV</a>
            <a href="?page=spots&lang=' . ($_GET['lang'] ?? 'en') . '">spots</a>
            <a href="?page=login&lang=' . ($_GET['lang'] ?? 'en') . '">login</a>
            <a href="?page=register&lang=' . ($_GET['lang'] ?? 'en') . '">register</a>
            <div>
                <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=fr">FR</a>
                <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=en">EN</a>
                <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=al">AL</a>
                <a href="?page=' . ($this->data['page'] ?? 'home') . '&lang=vi">VI</a>
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