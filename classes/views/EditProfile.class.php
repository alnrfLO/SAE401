<?php
class EditProfile extends View {
    public function content() {
        $user = $this->data['user'] ?? [];

        return '
        <link rel="stylesheet" href="public/css/editprofile.css">
        <div class="prof-wrapper">
        <div class="prof-header">
            <h2 class="prof-card-title">Account Settings</h2>
            <div class="prof-subtitle-container">
                <p class="prof-subtitle">Manage your public profile and account details</p>
            </div>
        </div>

            <form action="?page=profile&action=saveFullProfile" method="POST" class="edit-grid-form">
                
                <div class="form-card grid-col-2">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="' . htmlspecialchars($user['username']) . '" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="' . htmlspecialchars($user['email']) . '" required>
                    </div>
                </div>

                <div class="form-card grid-full">
                    <div class="form-group">
                        <label>About Me (Bio)</label>
                        <textarea name="bio" rows="4" placeholder="Tell us something cool about yourself...">' . htmlspecialchars($user['bio'] ?? '') . '</textarea>
                    </div>
                </div>

                <div class="form-card grid-col-2">
                    <div class="form-group">
                        <label>Country</label>
                        <select name="country">
                            <option value="fr" ' . ($user['country'] == 'fr' ? 'selected' : '') . '>France 🇫🇷</option>
                            <option value="us" ' . ($user['country'] == 'vi' ? 'selected' : '') . '>Vietnam 🇻🇳</option>
                            <option value="al" ' . ($user['country'] == 'al' ? 'selected' : '') . '>Albania 🇦🇱</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Preferred Language</label>
                        <select name="language">
                            <option value="fr" ' . ($user['country'] == 'fr' ? 'selected' : '') . '>France 🇫🇷</option>
                            <option value="us" ' . ($user['country'] == 'vi' ? 'selected' : '') . '>Vietnam 🇻🇳</option>
                            <option value="al" ' . ($user['country'] == 'al' ? 'selected' : '') . '>Albania 🇦🇱</option>
                        </select>
                    </div>
                </div>

                <div class="form-card grid-full security-section">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="Leave blank to keep current password">
                    </div>
                </div>

                <div class="prof-actions">
                    <a href="?page=profile" class="prof-btn prof-btn--outline">Discard Changes</a>
                    <button type="submit" class="prof-btn prof-btn--primary">Update Profile</button>
                </div>
            </form>
        </div>
        ';
    }
}