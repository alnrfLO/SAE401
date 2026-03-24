<?php
class Register extends View {
    public function content() {
        return '
        <div style="max-width: 400px; margin: 100px auto;">
            <h1>Sign Up</h1>
            <div>
                <label>Pseudo</label><br>
                <input type="text" name="pseudo" placeholder="johndoe" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <div>
                <label>Email</label><br>
                <input type="email" name="email" placeholder="email@example.com" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <div>
                <label>Password</label><br>
                <input type="password" name="password" placeholder="••••••••" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <div>
                <label>Confirm Password</label><br>
                <input type="password" name="confirm_password" placeholder="••••••••" style="width:100%; padding:8px; margin:8px 0;">
            </div>
            <div>
                <label>Country</label><br>
                <select name="pays" style="width:100%; padding:8px; margin:8px 0;">
                    <option value="">Select your country</option>
                    <option value="fr">France</option>
                    <option value="al">Albania</option>
                    <option value="vi">Vietnam</option>
                </select>
            </div>
            <button style="width:100%; padding:10px; margin-top:10px;">Sign Up</button>
            <p>Already have an account ? <a href="?page=login">Sign In</a></p>
        </div>';
    }
}