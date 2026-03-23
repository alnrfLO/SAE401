<?php
class View {
    protected $data;
    
    public function __construct($data = []) {
        $this->data = $data;
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
            <a href="?page=spots">Spots</a>
            <a href="?page=login">Login</a>
            <a href="?page=register">Register</a>
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
        <a href="?page=legal">Mentions légales</a>
        <a href="?page=terms">CGU</a>
        <a href="?page=privacy">Politique de confidentialité</a>
    </footer>
</body>
</html>';
    }
    
    public function content() {
        return '';
    }
}