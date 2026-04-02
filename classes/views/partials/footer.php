<footer class="site-footer">
    <div class="site-water">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1564 45" preserveAspectRatio="none">
            <path d="M0 0C260.667 37.5 521.333 -15 782 15C1042.67 45 1303.33 -7.5 1564 7.5V45H0V0Z" fill="#425ACB"/>
        </svg>
    </div>
    <div class="footer-grid">
        <div class="footer-col footer-col-brand">
            <a href="?page=home&lang=<?= $lang ?>" class="footer-logo">FAV</a>
            <p class="footer-location">France · Albania · Vietnam</p>
            <p class="footer-description"><?= $this->lang['footer_subtext'] ?></p>
            
            <form class="footer-newsletter" action="#" method="POST">
                <input type="email" name="newsletter_email" placeholder="<?= $this->lang['newsletter_placeholder'] ?? 'Votre adresse email' ?>" required>
                <button type="submit" aria-label="S'abonner"><i class="ph ph-paper-plane-right"></i></button>
            </form>

            <div class="footer-socials">
                <a href="https://instagram.com" target="_blank" rel="noreferrer" aria-label="Instagram">
                    <i class="ph ph-instagram-logo"></i>
                </a>
                <a href="https://facebook.com" target="_blank" rel="noreferrer" aria-label="Facebook">
                    <i class="ph ph-facebook-logo"></i>
                </a>
                <a href="https://twitter.com" target="_blank" rel="noreferrer" aria-label="Twitter">
                    <i class="ph ph-twitter-logo"></i>
                </a>
            </div>
        </div>
        <div class="footer-col footer-col-contact">
            <h4><?= $this->lang['footer_contact_title'] ?></h4>
            <a class="footer-contact-email" href="mailto:hello@fav-travel.com">
                <span class="footer-contact-icon" aria-hidden="true">
                    <i class="ph ph-envelope-simple"></i>
                </span>
                hello@fav-travel.com
            </a>
        </div>
        <div class="footer-col footer-col-links">
            <h4><?= $this->lang['footer_links_title'] ?></h4>
            <div class="footer-links">
                <a href="?page=presentation&lang=<?= $lang ?>"><?= $this->lang['nav_presentation'] ?></a>
                <a href="?page=faq&lang=<?= $lang ?>"><?= $this->lang['footer_questions'] ?></a>
                <a href="?page=legal&lang=<?= $lang ?>"><?= $this->lang['footer_legal'] ?></a>
                <a href="?page=user-agreement&lang=<?= $lang ?>"><?= $this->lang['footer_user_agreement'] ?></a>
                <a href="?page=terms&lang=<?= $lang ?>"><?= $this->lang['footer_terms'] ?></a>
                <a href="?page=tos&lang=<?= $lang ?>"><?= $this->lang['footer_tos'] ?></a>
                <a href="?page=privacy&lang=<?= $lang ?>"><?= $this->lang['footer_privacy'] ?></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© <?= date('Y') ?> FAV Travel. <?= $this->lang['footer_bottom'] ?></span>
    </div>
</footer>
