<?php
class Register extends View {
    public function content() {
        return '
        <div style="max-width: 400px; margin: 100px auto;">
            <h1>' . $this->lang['register_title'] . '</h1>
            <div>
                <label>' . $this->lang['register_pseudo'] . '</label><br>
                <input type="text" name="pseudo" placeholder="johndoe" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <div>
                <label>' . $this->lang['register_email'] . '</label><br>
                <input type="email" name="email" placeholder="email@example.com" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <div>
                <label>' . $this->lang['register_password'] . '</label><br>
                <input type="password" name="password" placeholder="••••••••" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <div>
                <label>' . $this->lang['register_confirm'] . '</label><br>
                <input type="password" name="confirm_password" placeholder="••••••••" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <div>
                <label>' . $this->lang['register_country'] . '</label><br>
                <select name="pays" style="width:100%; padding:8px; margin:8px 0;">
                    <option value="">Select your country</option>
                    <option value="fr">France</option>
                    <option value="al">Albania</option>
                    <option value="vi">Vietnam</option>
                </select>
            </div>
            <button style="width:100%; padding:10px; margin-top:10px;">' . $this->lang['register_submit'] . '</button>
            <p>' . $this->lang['register_have_account'] . ' <a href="?page=login">' . $this->lang['nav_login'] . '</a></p>
        </div>';
    }
}