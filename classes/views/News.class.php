<?php
class News extends View
{
    public function content()
    {
        $currentLang = $_GET['lang'] ?? 'en';
        $newsModel = new NewsModel();
        $newsItems = $newsModel->getAll();

        $html = '
        <link rel="stylesheet" href="public/css/news.css">
        <div class="news-container">
            <div class="news-top">
                <span class="news-badge">' . ($this->lang['news_badge'] ?? 'FAV UPDATES') . '</span>
                <div class="news-title-wrap">
                    <h1 class="news-title">' . ($this->lang['news_page_title'] ?? 'Latest News') . '</h1>
                </div>
                <p class="news-subtitle">' . ($this->lang['news_page_subtitle'] ?? 'Keep up with the latest stories, updates, and travel tips from the FAV community.') . '</p>
            </div>

            <section class="news-featured">
                <div class="featured-card">
                    <div class="featured-image">
                        <img src="https://images.unsplash.com/photo-1526772662000-3f88f10405ff?auto=format&fit=crop&q=80&w=1200" alt="Featured News">
                        <span class="card-tag featured-tag">FEATURED</span>
                    </div>
                    <div class="featured-content">
                        <div class="card-meta">
                            <span class="card-category">PLATFORM</span>
                            <span class="card-date">April 01, 2024</span>
                        </div>
                        <h2 class="card-title">Welcome to the New FAV Experience</h2>
                        <p class="card-text">We\'ve completely redesigned the way you discover and share travel spots. Our new neo-brutalism interface is faster, bolder, and more intuitive.</p>
                        <a href="index.php?page=article&id=1" class="news-btn primary">' . ($this->lang['news_read_more'] ?? 'Read More') . ' <i class="ph ph-arrow-right"></i></a>
                    </div>
                </div>
            </section>

            <section class="news-grid">';

        foreach ($newsItems as $id => $item) {
            $accentClass = ($id % 3 == 0) ? 'accent-orange' : (($id % 3 == 1) ? 'accent-green' : 'accent-blue');
            
            $html .= '
                <article class="news-card ' . $accentClass . '">
                    <div class="card-image">
                        <img src="' . $item['image'] . '" alt="' . $item['title'] . '">
                        <span class="card-tag">' . $item['category'] . '</span>
                    </div>
                    <div class="card-body">
                        <div class="card-meta">
                            <i class="ph ph-calendar"></i> ' . date("M d, Y", strtotime($item['date'])) . '
                        </div>
                        <h3 class="card-title">' . $item['title'] . '</h3>
                        <p class="card-text">' . $item['summary'] . '</p>
                        <a href="index.php?page=article&id=' . $id . '" class="card-link">' . ($this->lang['news_read_more'] ?? 'Read more') . ' <i class="ph ph-plus"></i></a>
                    </div>
                </article>';
        }

        $html .= '
            </section>
            
            <section class="news-newsletter">
                <div class="newsletter-box">
                    <div class="newsletter-content">
                        <h2 class="newsletter-title">' . ($this->lang['news_newsletter_title'] ?? 'Don’t miss any updates') . '</h2>
                        <p class="newsletter-text">' . ($this->lang['news_newsletter_subtitle'] ?? 'Subscribe to our newsletter and get the latest travel spots in your inbox.') . '</p>
                    </div>
                    <form class="newsletter-form">
                        <input type="email" placeholder="your@email.com" required>
                        <button type="submit" class="news-btn dark">' . ($this->lang['news_subscribe'] ?? 'Subscribe') . '</button>
                    </form>
                </div>
            </section>
        </div>';

        return $html;
    }
}
