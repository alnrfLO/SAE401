<?php
class Login extends View
{
    public function content()
    {
        $oldEmail  = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES);
        $error     = $GLOBALS['loginError'] ?? null;
        $errorHtml = $error
            ? '<div class="register-alert register-alert--error">
                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                       <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                   </svg>
                   ' . htmlspecialchars($error, ENT_QUOTES) . '
               </div>'
            : '';

        return '
        <link rel="stylesheet" href="public/css/register.css">
        <div class="register-page-wrapper">
            <div class="register-container">
                <div class="register-header login-header">
                    <h1 class="register-logo">FAV</h1>
                    <h2 class="register-title">' . $this->lang['login_title'] . '</h2>
                    <p class="register-subtitle">Welcome back to FAV!</p>
                </div>

                ' . $errorHtml . '
                
                <form class="register-body" action="?page=login" method="POST">
                    <div class="register-form-group">
                        <label>' . $this->lang['login_email'] . '</label>
                        <div class="register-input-wrapper">
                            <span class="register-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input type="email" name="email" placeholder="email@example.com" value="' . $oldEmail . '">
                        </div>
                    </div>
                    
                    <div class="register-form-group">
                        <label>' . $this->lang['login_password'] . '</label>
                        <div class="register-input-wrapper">
                            <span class="register-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" name="password" placeholder="••••••••">
                        </div>
                    </div>
                    
                    <button type="submit" class="register-submit">' . $this->lang['login_submit'] . '</button>
                    
                    <p class="register-footer">' . $this->lang['login_no_account'] . ' <a href="?page=register">' . $this->lang['nav_register'] . '</a></p>
                </form>
            </div>
        </div>';
    }
}
