<?php
class SingleNews extends View
{
    public function content()
    {
        $article = $this->data['article'];
        
        $html = '
        <link rel="stylesheet" href="public/css/singlenews.css">
        <div class="article-container">
            <div class="article-header-nav">
                <a href="index.php?page=news" class="back-btn">
                    <i class="ph ph-arrow-left"></i> ' . ($this->lang['news_back'] ?? 'Back to updates') . '
                </a>
            </div>

            <article class="full-article">
                <header class="article-meta-section">
                    <div class="article-tags">
                        <span class="article-tag">' . $article['category'] . '</span>
                    </div>
                    <h1 class="article-title">' . $article['title'] . '</h1>
                    <div class="article-info">
                        <span class="article-date">
                            <i class="ph ph-calendar"></i> ' . date("F d, Y", strtotime($article['date'])) . '
                        </span>
                        <span class="article-reading-time">
                            <i class="ph ph-clock"></i> 5 min read
                        </span>
                    </div>
                </header>

                <div class="article-hero-image">
                    <img src="' . $article['image'] . '" alt="' . $article['title'] . '">
                </div>

                <div class="article-body-content">
                    <p class="article-lead">' . $article['summary'] . '</p>
                    <div class="article-main-text">
                        ' . nl2br($article['content']) . '
                    </div>
                </div>

                <footer class="article-footer-section">
                    <div class="share-article">
                        <span>' . ($this->lang['news_share'] ?? 'Share this article') . '</span>
                        <div class="share-btns">
                            <a href="#"><i class="ph ph-twitter-logo"></i></a>
                            <a href="#"><i class="ph ph-facebook-logo"></i></a>
                            <a href="#"><i class="ph ph-link"></i></a>
                        </div>
                    </div>
                </footer>
            </article>

            <section class="related-articles">
                <h3 class="section-title">' . ($this->lang['news_related'] ?? 'You might also like') . '</h3>
                <div class="related-grid" id="related-placeholder">
                    <!-- Related articles could be loaded here via Model if needed -->
                </div>
            </section>
        </div>';

        return $html;
    }
}
