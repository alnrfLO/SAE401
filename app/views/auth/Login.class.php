<?php
class Login extends View {
    public function content() {
        return '
        <div style="max-width: 400px; margin: 100px auto;">
            <h1>Sign In</h1>
            <div>
                <label>Email</label><br>
                <input type="email" name="email" placeholder="email@example.com" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <div>
                <label>Password</label><br>
                <input type="password" name="password" placeholder="••••••••" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <button style="width:100%; padding:10px; margin-top:10px;">Sign In</button>
            <p>No account yet ? <a href="?page=register">Sign Up</a></p>
        </div>';
    }
}