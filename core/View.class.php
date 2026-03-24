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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAV</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="?page=home">FAV</a>
            <a href="?page=spots">' . $this->lang['nav_spots'] . '</a>
            <a href="?page=login">' . $this->lang['nav_login'] . '</a>
            <a href="?page=register">' . $this->lang['nav_register'] . '</a>
            <div>
                <a href="?lang=fr">FR</a>
                <a href="?lang=en">EN</a>
                <a href="?lang=al">AL</a>
                <a href="?lang=vi">VI</a>
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