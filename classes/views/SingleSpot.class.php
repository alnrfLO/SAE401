<?php
class SingleSpot extends View
{
    public function content()
    {
        $spot = $this->data['spot'] ?? null;
        if (!$spot) {
            return '<div style="padding-top: 150px; text-align:center;"><h2>Spot introuvable.</h2><br><a href="?page=home" style="color:#212121; text-decoration:underline;">Retour à l\'accueil</a></div>';
        }

        $image = !empty($spot['image']) ? htmlspecialchars($spot['image'], ENT_QUOTES) : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23f0ebe0%22 width=%22400%22 height=%22300%22/%3E%3C/svg%3E';
        $title = htmlspecialchars($spot['title'] ?? 'Untitled', ENT_QUOTES);
        $description = nl2br(htmlspecialchars($spot['description'] ?? '', ENT_QUOTES));
        $location = htmlspecialchars($spot['location'] ?? 'Unknown location', ENT_QUOTES);
        $category = htmlspecialchars($spot['category'] ?? 'other', ENT_QUOTES);
        $createdAt = date('F j, Y', strtotime($spot['created_at']));
        $authorName = htmlspecialchars($spot['username'] ?? 'User', ENT_QUOTES);
        $likes = $spot['likes_count'] ?? 0;
        $views = $spot['views_count'] ?? 0;
        $tags = $spot['tags'] ?? [];

        $avatarSrc = !empty($spot['avatar']) ? htmlspecialchars($spot['avatar'], ENT_QUOTES) : '';
        $avatarInlineStyle = $avatarSrc ? "background-image: url('$avatarSrc'); background-size: cover; background-position: center;" : "background: #ccc;";

        // Formatting tags
        $tagsHtml = '';
        foreach ($tags as $tag) {
            $tagsHtml .= '<span class="spot-detail-tag">#' . htmlspecialchars($tag, ENT_QUOTES) . '</span>';
        }

        return '
        <style>
            .spot-detail-container {
                max-width: 900px;
                margin: 120px auto 60px;
                padding: 0 20px;
            }
            .spot-detail-card {
                background: #fbfbfb;
                border: 4px solid #212121;
                border-radius: 16px;
                box-shadow: 8px 8px 0px #212121;
                overflow: hidden;
            }
            .spot-detail-hero {
                width: 100%;
                height: 400px;
                background-color: #fbad40;
                background-size: cover;
                background-position: center;
                border-bottom: 4px solid #212121;
                position: relative;
            }
            .spot-detail-category {
                position: absolute;
                top: 20px;
                left: 20px;
                background: #fbad40;
                color: #212121;
                font-family: \'Bungee\', cursive;
                padding: 6px 16px;
                border: 3px solid #212121;
                border-radius: 999px;
                text-transform: uppercase;
                box-shadow: 4px 4px 0px #212121;
            }
            .spot-detail-body {
                padding: 40px;
            }
            .spot-detail-title {
                font-family: \'Bungee\', cursive;
                font-size: 3rem;
                color: #212121;
                margin-bottom: 10px;
                line-height: 1.1;
                text-transform: uppercase;
                word-wrap: break-word;
            }
            .spot-detail-meta {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 20px;
                margin-bottom: 30px;
                font-family: \'Inter\', sans-serif;
                font-weight: 600;
                color: #555;
            }
            .spot-detail-meta span {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .spot-detail-desc {
                font-size: 1.15rem;
                line-height: 1.8;
                color: #333;
                margin-bottom: 40px;
                font-family: \'Inter\', sans-serif;
            }
            .spot-detail-tags {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 40px;
            }
            .spot-detail-tag {
                background: #e0e0e0;
                border: 2px solid #212121;
                color: #212121;
                padding: 4px 12px;
                border-radius: 999px;
                font-weight: 700;
                font-size: 0.9rem;
            }
            .spot-detail-footer {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
                border-top: 4px solid #212121;
                padding-top: 30px;
                gap: 20px;
            }
            .spot-author {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .spot-author-avatar {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                border: 3px solid #212121;
            }
            .spot-author-name {
                font-family: \'Bungee\', cursive;
                font-size: 1.2rem;
                color: #212121;
            }
            .spot-actions {
                display: flex;
                gap: 15px;
            }
            .spot-action-btn {
                background: #fff;
                border: 3px solid #212121;
                border-radius: 999px;
                padding: 10px 20px;
                font-family: \'Bungee\', cursive;
                color: #212121;
                cursor: pointer;
                box-shadow: 4px 4px 0px #212121;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: transform 0.1s, box-shadow 0.1s;
                text-decoration: none;
                font-size: 1rem;
            }
            .spot-action-btn:hover {
                background: #f4f4f4;
            }
            .spot-action-btn:active {
                transform: translate(4px, 4px);
                box-shadow: 0px 0px 0px #212121;
            }
        </style>
        
        <div class="spot-detail-container">
            <div class="spot-detail-card">
                <div class="spot-detail-hero" style="background-image: url(\'' . $image . '\');">
                    <div class="spot-detail-category">' . $category . '</div>
                </div>
                
                <div class="spot-detail-body">
                    <h1 class="spot-detail-title">' . $title . '</h1>
                    
                    <div class="spot-detail-meta">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M128,64a40,40,0,1,0,40,40A40,40,0,0,0,128,64Zm0,64a24,24,0,1,1,24-24A24,24,0,0,1,128,128Zm0-112a88.1,88.1,0,0,0-88,88c0,31.4,14.51,64.41,42.29,96.25a254.19,254.19,0,0,0,41.45,38.3,8,8,0,0,0,8.52,0,254.19,254.19,0,0,0,41.45-38.3C201.49,168.41,216,135.4,216,104A88.1,88.1,0,0,0,128,16Zm0,192.42c-23.12-19.79-72-68.58-72-104.42a72,72,0,0,1,144,0C200,140,151.12,188.63,128,208.42Z"></path></svg>
                            ' . $location . '
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M228.92,49.69a8,8,0,0,0-6.86-1.45L160.93,63.52,99.58,32.84a8,8,0,0,0-5.52-.6l-64,16A8,8,0,0,0,24,56V200a8,8,0,0,0,9.94,7.76l61.13-15.28,61.35,30.68A8.15,8.15,0,0,0,160,224a8,8,0,0,0,1.94-.24l64-16A8,8,0,0,0,232,200V56A8,8,0,0,0,228.92,49.69ZM96,203.21,40,217.2V66.85l56-14ZM160,205.8l-48-24V68.27l48,24ZM216,189.15l-40,10V48.8l40-10Z"></path></svg>
                            ' . $createdAt . '
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M247.31,124.76c-.35-.79-8.82-19.58-27.65-38.41C194.57,61.26,162.88,48,128,48S61.43,61.26,36.34,86.35C17.51,105.18,9,124,8.69,124.76a8,8,0,0,0,0,6.48c.35.79,8.82,19.58,27.65,38.41C61.43,194.74,93.12,208,128,208s66.57-13.26,91.66-38.35c18.83-18.83,27.3-37.62,27.65-38.41A8,8,0,0,0,247.31,124.76ZM128,192c-30.78,0-57.67-11.19-79.93-33.25A133.47,133.47,0,0,1,25,128,133.33,133.33,0,0,1,48.07,97.25C70.33,75.19,97.22,64,128,64s57.67,11.19,79.93,33.25A133.46,133.46,0,0,1,231.05,128C223.84,141.46,192.43,192,128,192Zm0-112a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160Z"></path></svg>
                            ' . $views . ' views
                        </span>
                    </div>
                    
                    <div class="spot-detail-desc">
                        ' . $description . '
                    </div>
                    
                    ' . ($tagsHtml ? '<div class="spot-detail-tags">' . $tagsHtml . '</div>' : '') . '
                    
                    <div class="spot-detail-footer">
                        <div class="spot-author">
                            <div class="spot-author-avatar" style="' . $avatarInlineStyle . '"></div>
                            <div class="spot-author-name">Posted by ' . $authorName . '</div>
                        </div>
                        
                        <div class="spot-actions">
                            <button class="spot-action-btn" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M240,94c0,70-104,122.34-108.58,124.54a8,8,0,0,1-6.84,0C120,216.34,16,164,16,94A62.07,62.07,0,0,1,78,32c22.59,0,41.94,11.83,50,29.93C136.06,43.83,155.41,32,178,32A62.07,62.07,0,0,1,240,94Z"></path></svg>
                                ' . $likes . '
                            </button>
                            <button class="spot-action-btn" onclick="window.history.back()">
                                Back
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 40px; margin-bottom: 20px;">
                <h2 style="font-family:\'Bungee\', cursive; font-size:2rem; color:#212121;">COMMENTS</h2>
            </div>
            <div class="spot-detail-card" style="padding: 30px;">
                <p style="font-family:\'Inter\', sans-serif; color:#555; font-size:1.1rem; text-align:center;">
                    Comment section coming soon!
                </p>
            </div>
        </div>
        ';
    }
}
