<?php
class NotFound extends View {
    public function content() {
        return '
        <div style="text-align:center; margin-top:150px;">
            <h1>404</h1>
            <p>' . $this->lang['error_404'] . '</p>
            <a href="?page=home&lang=' . ($_GET['lang'] ?? 'en') . '">' . $this->lang['error_back'] . '</a>
        </div>';
    }
}