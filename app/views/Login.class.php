<?php
class Login extends View
{
    public function content()
    {
        return '
        <div style="max-width: 400px; margin: 100px auto;">
            <h1>' . $this->lang['login_title'] . '</h1>
            <div>
                <label>' . $this->lang['login_email'] . '</label><br>
                <input type="email" name="email" placeholder="email@example.com" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <div>
                <label>' . $this->lang['login_password'] . '</label><br>
                <input type="password" name="password" placeholder="••••••••" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <button style="width:100%; padding:10px; margin-top:10px;">' . $this->lang['login_submit'] . '</button>
            <p>' . $this->lang['login_no_account'] . ' <a href="?page=register">' . $this->lang['nav_register'] . '</a></p>
        </div>';
    }
}
