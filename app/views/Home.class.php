<?php
class Home extends View {
    public function content() {
        return '
        <!-- HERO SECTION -->
        <section style="text-align:center; padding:80px 20px;">
            <h1>' . $this->lang['home_title'] . '</h1>
            <p>' . $this->lang['home_subtitle'] . '</p>
            <a href="?page=register&lang=' . ($_GET['lang'] ?? 'en') . '">
                <button>' . $this->lang['nav_register'] . '</button>
            </a>
        </section>

        <!-- VIDEO SECTION -->
        <section style="text-align:center; padding:60px 20px; background:#f5f5f5;">
            <video controls style="width:60%; max-width:800px;">
                <source src="" type="video/mp4">
            </video>
            <br><br>
            <button>' . $this->lang['home_video_btn'] . '</button>
        </section>

        <!-- FEATURES SECTION -->
        <section style="padding:60px 20px; text-align:center;">
            <h2>' . $this->lang['home_features_title'] . '</h2>
            <p>' . $this->lang['home_features_subtitle'] . '</p>
            <div style="display:flex; justify-content:center; gap:20px; margin-top:30px;">
                <div style="border:1px solid #ccc; padding:20px; width:250px; border-radius:8px;">
                    <div style="width:100%; height:150px; background:#eee;"></div>
                    <p>' . $this->lang['home_card1'] . '</p>
                </div>
                <div style="border:1px solid #ccc; padding:20px; width:250px; border-radius:8px;">
                    <div style="width:100%; height:150px; background:#eee;"></div>
                    <p>' . $this->lang['home_card2'] . '</p>
                </div>
                <div style="border:1px solid #ccc; padding:20px; width:250px; border-radius:8px;">
                    <div style="width:100%; height:150px; background:#eee;"></div>
                    <p>' . $this->lang['home_card3'] . '</p>
                </div>
            </div>
        </section>';
    }
}