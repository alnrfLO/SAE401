<?php
class Home extends View {
    public function content() {
        return '
        <h1>' . $this->lang['home_title'] . '</h1>
        <p>' . $this->lang['home_subtitle'] . '</p>';
    }
}