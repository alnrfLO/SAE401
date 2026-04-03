<?php

class LegalPages extends View
{
    public function content()
    {
        $page = isset($this->data['page']) ? $this->data['page'] : 'legal';
        $html = '<link rel="stylesheet" href="public/css/legal.css">';
        $html .= '<div class="legal-container">';
        $html .= '<div class="legal-card shadow-brutalist">';

        switch ($page) {
            case 'faq':
                $html .= $this->renderFaq();
                break;
            case 'terms':
                $html .= $this->renderTerms();
                break;
            case 'tos':
                $html .= $this->renderTos();
                break;
            case 'user-agreement':
                $html .= $this->renderAgreement();
                break;
            case 'privacy':
                $html .= $this->renderPrivacy();
                break;
            case 'about':
                $html .= $this->renderAbout();
                break;
            case 'legal':
            default:
                $html .= $this->renderLegal();
                break;
        }

        $html .= '</div></div>';
        return $html;
    }

    private function renderLegal()
    {
        $l = $this->lang;

        // Build GDPR rights list
        $rightsList = '';
        if (!empty($l['legal_s5_rights']) && is_array($l['legal_s5_rights'])) {
            $rightsList .= '<ul class="legal-rights-list">';
            foreach ($l['legal_s5_rights'] as $right) {
                $rightsList .= '<li><i class="ph ph-check-circle"></i> ' . $right . '</li>';
            }
            $rightsList .= '</ul>';
        }

        return '
        <div class="legal-header">
            <h1 class="legal-title">' . $l['legal_title'] . '</h1>
            <p class="legal-intro-law">' . $l['legal_full_intro'] . '</p>
        </div>

        <section class="legal-section">
            <h2><i class="ph ph-identification-card"></i> ' . $l['legal_s1_title'] . '</h2>
            <p>' . $l['legal_s1_text'] . '</p>
            <div class="legal-info-block">
                <p><strong>' . $l['legal_s1_group'] . '</strong></p>
                <p>' . $l['legal_s1_school'] . '</p>
                <p>' . $l['legal_s1_address'] . '</p>
                <p><strong>' . $l['legal_s1_responsible'] . ' :</strong> ' . $l['legal_s1_responsible_val'] . '</p>
                <p><strong>' . $l['legal_s1_contact'] . ' :</strong>
                    <a href="mailto:' . $l['legal_s1_contact_val'] . '">' . $l['legal_s1_contact_val'] . '</a>
                </p>
            </div>
        </section>

        <section class="legal-section">
            <h2><i class="ph ph-hard-drive"></i> ' . $l['legal_s2_title'] . '</h2>
            <p>' . $l['legal_s2_text'] . '</p>
            <div class="legal-info-block">
                <p><strong>' . $l['legal_s2_name'] . '</strong></p>
                <p>' . $l['legal_s2_address'] . '</p>
                <p><strong>' . $l['legal_s2_phone'] . ' </strong>' . $l['legal_s2_phone_val'] . '</p>
                <p><strong>' . $l['legal_s2_site'] . ' : </strong>
                    <a href="https://' . $l['legal_s2_site_val'] . '" target="_blank" rel="noopener">' . $l['legal_s2_site_val'] . '</a>
                </p>
                <p><strong>' . $l['legal_s2_url'] . ' : </strong>
                    <a href="' . $l['legal_s2_url_val'] . '" target="_blank" rel="noopener">' . $l['legal_s2_url_val'] . '</a>
                </p>
            </div>
        </section>

        <section class="legal-section">
            <h2><i class="ph ph-book-open"></i> ' . $l['legal_s3_title'] . '</h2>
            <p>' . $l['legal_s3_text'] . '</p>
        </section>

        <section class="legal-section">
            <h2><i class="ph ph-copyright"></i> ' . $l['legal_s4_title'] . '</h2>
            <p>' . $l['legal_s4_text'] . '</p>
        </section>

        <section class="legal-section">
            <h2><i class="ph ph-shield-check"></i> ' . $l['legal_s5_title'] . '</h2>
            <p>' . $l['legal_s5_text'] . '</p>
            <p>' . $l['legal_s5_intro'] . '</p>
            ' . $rightsList . '
            <p>' . $l['legal_s5_contact'] . '
                <a href="mailto:' . $l['legal_s1_contact_val'] . '">' . $l['legal_s1_contact_val'] . '</a>
            </p>
            <p>' . $l['legal_s5_cnil'] . '
                <a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>
            </p>
        </section>

        <section class="legal-section">
            <h2><i class="ph ph-cookie"></i> ' . $l['legal_s6_title'] . '</h2>
            <p>' . $l['legal_s6_text'] . '</p>
        </section>

        <section class="legal-section">
            <h2><i class="ph ph-link"></i> ' . $l['legal_s7_title'] . '</h2>
            <p>' . $l['legal_s7_text'] . '</p>
        </section>

        <section class="legal-section">
            <h2><i class="ph ph-warning-circle"></i> ' . $l['legal_s8_title'] . '</h2>
            <p>' . $l['legal_s8_text'] . '</p>
        </section>

        <section class="legal-section">
            <h2><i class="ph ph-scales"></i> ' . $l['legal_s9_title'] . '</h2>
            <p>' . $l['legal_s9_text'] . '</p>
        </section>

        <div class="legal-update">
            <i class="ph ph-clock"></i> ' . $l['legal_last_update'] . '
        </div>';
    }

    private function renderFaq()
    {
        return '
        <div class="legal-header">
            <h1 class="legal-title">' . $this->lang['faq_title'] . '</h1>
        </div>
        <div class="faq-grid">
            <div class="faq-item">
                <div class="faq-question"><i class="ph ph-question"></i> ' . $this->lang['faq_q1'] . '</div>
                <div class="faq-answer">' . $this->lang['faq_a1'] . '</div>
            </div>
            <div class="faq-item">
                <div class="faq-question"><i class="ph ph-question"></i> ' . $this->lang['faq_q2'] . '</div>
                <div class="faq-answer">' . $this->lang['faq_a2'] . '</div>
            </div>
            <div class="faq-item">
                <div class="faq-question"><i class="ph ph-question"></i> ' . $this->lang['faq_q3'] . '</div>
                <div class="faq-answer">' . $this->lang['faq_a3'] . '</div>
            </div>
        </div>';
    }

    private function renderTerms()
    {
        return '
        <div class="legal-header">
            <h1 class="legal-title">' . $this->lang['terms_title'] . '</h1>
        </div>
        <section class="legal-section">
            <p>' . $this->lang['terms_intro'] . '</p>
        </section>
        <section class="legal-section">
            <p>' . $this->lang['terms_content'] . '</p>
        </section>';
    }

    private function renderTos()
    {
        return '
        <div class="legal-header">
            <h1 class="legal-title">' . $this->lang['tos_title'] . '</h1>
        </div>
        <section class="legal-section">
            <p>' . $this->lang['tos_text'] . '</p>
        </section>';
    }

    private function renderAgreement()
    {
        return '
        <div class="legal-header">
            <h1 class="legal-title">' . $this->lang['agreement_title'] . '</h1>
        </div>
        <section class="legal-section">
            <p>' . $this->lang['agreement_text'] . '</p>
        </section>';
    }

    private function renderPrivacy()
    {
        return '
        <div class="legal-header">
            <h1 class="legal-title">' . $this->lang['footer_privacy'] . '</h1>
        </div>
        <section class="legal-section">
            <p>' . $this->lang['legal_privacy_text'] . '</p>
        </section>';
    }

    private function renderAbout()
    {
        return '
        <div class="legal-header">
            <h1 class="legal-title">' . $this->lang['nav_about'] . '</h1>
        </div>
        <section class="legal-section">
            <p>' . $this->lang['footer_subtext'] . '</p>
        </section>';
    }
}