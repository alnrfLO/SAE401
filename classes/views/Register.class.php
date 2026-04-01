<?php
class Register extends View
{
    public function content()
    {
        // Récupération des valeurs POST pour pré-remplir après erreur
        $oldPseudo  = htmlspecialchars(trim($_POST['pseudo'] ?? ''), ENT_QUOTES);
        $oldEmail   = htmlspecialchars(trim($_POST['email']  ?? ''), ENT_QUOTES);
        $oldCountry = $_POST['pays'] ?? '';

        // Message d'erreur éventuel (défini dans index.php)
        $error = $GLOBALS['registerError'] ?? null;
        $errorHtml = $error
            ? '<div class="register-alert register-alert--error">
                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                       <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                   </svg>
                   ' . htmlspecialchars($error, ENT_QUOTES) . '
               </div>'
            : '';

        // Options pays avec sélection maintenue
        $countries = ['fr' => 'France', 'al' => 'Albania', 'vi' => 'Vietnam'];
        $countryOptions = '<option value="">Select your country</option>';
        foreach ($countries as $val => $label) {
            $selected = ($oldCountry === $val) ? ' selected' : '';
            $countryOptions .= '<option value="' . $val . '"' . $selected . '>' . $label . '</option>';
        }

        return '
        <link rel="stylesheet" href="public/css/register.css">
        <div class="register-page-wrapper">
            <div class="register-container">
                <div class="register-header">
                    <h1 class="register-logo">FAV</h1>
                    <h2 class="register-title">' . $this->lang['register_title'] . '</h2>
                    <p class="register-subtitle">Join the FAV community!</p>
                </div>

                ' . $errorHtml . '
                
                <form class="register-body" action="?page=register" method="POST">
                    <div class="register-form-group">
                        <label>' . $this->lang['register_pseudo'] . '</label>
                        <div class="register-input-wrapper">
                            <span class="register-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <input type="text" name="pseudo" placeholder="Your full name" value="' . $oldPseudo . '">
                        </div>
                    </div>
                    
                    <div class="register-form-group">
                        <label>' . $this->lang['register_email'] . '</label>
                        <div class="register-input-wrapper">
                            <span class="register-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input type="email" name="email" placeholder="you@example.com" value="' . $oldEmail . '">
                        </div>
                    </div>
                    
                    <div class="register-form-group">
                        <label>' . $this->lang['register_password'] . '</label>
                        <div class="register-input-wrapper">
                            <span class="register-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" name="password" placeholder="Min. 8 characters">
                        </div>
                    </div>
                    
                    <div class="register-form-group">
                        <label>' . $this->lang['register_confirm'] . '</label>
                        <div class="register-input-wrapper">
                            <span class="register-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" name="confirm_password" placeholder="Repeat your password">
                        </div>
                    </div>
                    
                    <div class="register-form-group">
                        <label>' . $this->lang['select_country'] . '</label>
                        <div class="register-input-wrapper">
                            <span class="register-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <select name="pays">' . $countryOptions . '</select>
                        </div>
                    </div>
                    
                    <div class="register-terms">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I have read and I accept the <a href="#">User Agreement and Terms of Service</a> and the <a href="#">Privacy Policy</a>.</label>
                    </div>
                    
                    <button type="submit" class="register-submit">' . $this->lang['register_submit'] . '</button>
                    
                    <p class="register-footer">' . $this->lang['register_have_account'] . ' <a href="?page=login">' . $this->lang['nav_login'] . '</a></p>
                </form>
            </div>
        </div>';
    }
}

