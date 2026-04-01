<?php
class Home extends View
{
    public function content()
    {
        $currentLang = $_GET['lang'] ?? 'en';

        return '
        <link rel="stylesheet" href="public/css/home.css">

        <!-- HERO SECTION -->
        <section class="hero">
            <!-- Floating decorative emojis -->
            <div class="hero-floats">
                <span class="hero-float">🌿</span>
                <span class="hero-float">🌺</span>
                <span class="hero-float">🍃</span>
                <span class="hero-float">🌴</span>
                <span class="hero-float">✨</span>
                <span class="hero-float">🦋</span>
            </div>

            <div class="hero-content">
                <div class="hero-badge">
                    <i class="ph ph-globe-hemisphere-west"></i>
                    ' . $this->lang['hero_badge'] . '
                </div>
                <h1 class="hero-title">' . $this->lang['hero_title_line1'] . '<br><span class="highlight">' . $this->lang['hero_title_line2'] . '</span></h1>
                <p class="hero-subtitle">' . $this->lang['hero_subtitle'] . '</p>
                <a href="?page=discover&lang=' . $currentLang . '" class="hero-cta">
                    ' . $this->lang['hero_cta'] . '
                    <i class="ph ph-arrow-right"></i>
                </a>

                <!-- Country flags -->
                <div class="hero-countries">
                    <div class="hero-country">
                        <div class="hero-country-flag">🇫🇷</div>
                        <span class="hero-country-name">France</span>
                    </div>
                    <div class="hero-country">
                        <div class="hero-country-flag">🇦🇱</div>
                        <span class="hero-country-name">Albania</span>
                    </div>
                    <div class="hero-country">
                        <div class="hero-country-flag">🇻🇳</div>
                        <span class="hero-country-name">Vietnam</span>
                    </div>
                   
                </div>

                <!-- Stats -->
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-number">4</span>
                        <span class="hero-stat-label">' . $this->lang['hero_stat_countries'] . '</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">3</span>
                        <span class="hero-stat-label">' . $this->lang['hero_stat_continents'] . '</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">∞</span>
                        <span class="hero-stat-label">' . $this->lang['hero_stat_memories'] . '</span>
                    </div>
                </div>
            </div>


        </section>

        <!-- VIDEO SECTION -->
        <section id="video" class="video-section">
            <div class="video-wrapper">
                <video controls>
                    <source src="" type="video/mp4">
                </video>
            </div>
            <button class="video-btn">
                <i class="ph ph-play-circle"></i>
                ' . $this->lang['home_video_btn'] . '
            </button>
        </section>

        <!-- FEATURES SECTION -->
        <section class="features-section">
            <h2 class="features-title">' . $this->lang['home_features_title'] . '</h2>
            <p class="features-subtitle">' . $this->lang['home_features_subtitle'] . '</p>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-card-icon">📍</div>
                    <div class="feature-card-body">
                        <p>' . $this->lang['home_card1'] . '</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-card-icon">🗺️</div>
                    <div class="feature-card-body">
                        <p>' . $this->lang['home_card2'] . '</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-card-icon">🤝</div>
                    <div class="feature-card-body">
                        <p>' . $this->lang['home_card3'] . '</p>
                    </div>
                </div>
            </div>
        </section>';
    }
}
