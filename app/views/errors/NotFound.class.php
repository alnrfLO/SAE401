<?php
class NotFound extends View {
    public function content() {
        return '
        <div style="text-align:center; margin-top:150px;">
            <h1>404</h1>
            <p>Page not found</p>
            <a href="?page=home&lang=' . ($_GET['lang'] ?? 'en') . '">Go back home</a>
        </div>';
    }
}