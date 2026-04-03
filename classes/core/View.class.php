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

        $showHeader = $this->data['showHeader'] ?? true;
        $headerHtml = '';
        if ($showHeader) {
            ob_start();
            require __DIR__ . '/../views/partials/header.php';
            $headerHtml = ob_get_clean();
        }

        ob_start();
        require __DIR__ . '/../views/partials/footer.php';
        $footerHtml = ob_get_clean();

        $showFooter = $this->data['showFooter'] ?? true;
        $fullWidth  = $this->data['fullWidth']  ?? false;

        return
            '<!DOCTYPE html>
            <html lang="' . $lang . '">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>FAV</title>
                <link rel="icon" type="image/x-icon" href="/public/img/favicon.ico">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Bungee:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
                <script src="https://unpkg.com/@phosphor-icons/web"></script>
                <link rel="stylesheet" href="public/css/view.css">
            </head>
            <body class="' . ($showHeader ? '' : 'no-header') . '">
                ' . $headerHtml . '
                <main class="' . ($fullWidth ? 'main--full' : '') . '">
                    ' . $this->content() . '
                </main>
                ' . ($showFooter ? $footerHtml : '') . '
            </body>
            </html>';
    }

    public function content()
    {
        return '';
    }
}