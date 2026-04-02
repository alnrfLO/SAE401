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
        return '
        <div class="legal-header">
            <h1 class="legal-title">' . $this->lang['legal_title'] . '</h1>
        </div>
        <section class="legal-section">
            <h2><i class="ph ph-identification-card"></i> ' . $this->lang['legal_editor_title'] . '</h2>
            <p>' . $this->lang['legal_editor_text'] . '</p>
        </section>
        <section class="legal-section">
            <h2><i class="ph ph-hard-drive"></i> ' . $this->lang['legal_host_title'] . '</h2>
            <p>' . $this->lang['legal_host_text'] . '</p>
        </section>
        <section class="legal-section">
            <h2><i class="ph ph-shield-check"></i> ' . $this->lang['legal_privacy_title'] . '</h2>
            <p>' . $this->lang['legal_privacy_text'] . '</p>
        </section>';
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
