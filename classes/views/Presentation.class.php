<?php
class Presentation extends View {
    public function content() {
        return '
        <link rel="stylesheet" href="public/css/presentation.css">
        <main class="pres-main">
            <!-- HERO SECTION -->
            <section class="pres-hero">
                <div class="pres-hero-content">
                    <h1 class="pres-title">' . $this->lang['presentation_title'] . '</h1>
                    <p class="pres-subtitle">' . $this->lang['presentation_subtitle'] . '</p>
                </div>
            </section>

            <!-- PRINCIPLE SECTION -->
            <section class="pres-principle">
                <div class="container">
                    <div class="pres-card">
                        <div class="pres-icon">💡</div>
                        <h2 class="pres-section-title">' . $this->lang['presentation_principle_title'] . '</h2>
                        <p class="pres-text">' . $this->lang['presentation_principle_text'] . '</p>
                    </div>
                </div>
            </section>

            <!-- TEAM SECTION -->
            <section class="pres-team">
                <div class="container">
                    <h2 class="pres-section-title center">' . $this->lang['presentation_team_title'] . '</h2>
                    <p class="pres-section-subtitle">' . $this->lang['presentation_team_subtitle'] . '</p>
                    
                    <div class="team-grid">
                        <!-- France -->
                        <div class="team-item">
                            <div class="team-flag">🇫🇷</div>
                            <h3 class="team-name">' . $this->lang['presentation_team_france'] . '</h3>
                            <p class="team-desc">' . $this->lang['presentation_team_desc_fr'] . '</p>
                        </div>
                        
                        <!-- Albania -->
                        <div class="team-item">
                            <div class="team-flag">🇦🇱</div>
                            <h3 class="team-name">' . $this->lang['presentation_team_albania'] . '</h3>
                            <p class="team-desc">' . $this->lang['presentation_team_desc_al'] . '</p>
                        </div>
                        
                        <!-- Vietnam -->
                        <div class="team-item">
                            <div class="team-flag">🇻🇳</div>
                            <h3 class="team-name">' . $this->lang['presentation_team_vietnam'] . '</h3>
                            <p class="team-desc">' . $this->lang['presentation_team_desc_vi'] . '</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        ';
    }
}
