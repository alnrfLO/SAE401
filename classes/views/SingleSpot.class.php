<?php
class SingleSpot extends View
{
    public function content()
    {
        $spot = $this->data['spot'] ?? null;
        if (!$spot) {
            return '<div style="padding-top: 150px; text-align:center;"><h2>' . $this->lang['spot_not_found'] . '</h2><br><a href="?page=home" style="color:#212121; text-decoration:underline;">' . $this->lang['spot_back_home'] . '</a></div>';
        }

        $image = !empty($spot['image']) ? htmlspecialchars($spot['image'], ENT_QUOTES) : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23f0ebe0%22 width=%22400%22 height=%22300%22/%3E%3C/svg%3E';
        $title = htmlspecialchars($spot['title'] ?? 'Untitled', ENT_QUOTES);
        $description = nl2br(htmlspecialchars($spot['description'] ?? '', ENT_QUOTES));
        $location = htmlspecialchars($spot['location'] ?? 'Unknown location', ENT_QUOTES);
        $category = htmlspecialchars($spot['category'] ?? 'other', ENT_QUOTES);
        
        // Localized category name mapping
        $catMap = [
            'nature'      => 'nature',
            'ville'       => 'city',
            'voyage'      => 'beach',
            'aventure'    => 'adventure',
            'gastronomie' => 'food',
            'culture'     => 'culture',
            'autre'       => 'other'
        ];
        $catKey = $catMap[$category] ?? 'other';
        $categoryLabel = $this->lang['discover_category_' . $catKey] ?? $category;

        $createdAt = date($this->lang['spot_date_format'] ?? 'F j, Y', strtotime($spot['created_at']));
        $authorName = htmlspecialchars($spot['username'] ?? 'User', ENT_QUOTES);
        $likes = $spot['likes_count'] ?? 0;
        $views = $spot['views_count'] ?? 0;
        $tags = $spot['tags'] ?? [];

        $avatarSrc = !empty($spot['avatar']) ? htmlspecialchars($spot['avatar'], ENT_QUOTES) : 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($authorName);
        $avatarInlineStyle = "background-image: url('$avatarSrc'); background-size: cover; background-position: center; border-radius: 50%;";

        $isLiked = $this->data['isLiked'] ?? false;
        $comments = $this->data['comments'] ?? [];
        $isLoggedIn = User::isLoggedIn();

        // Formatting tags
        $tagsHtml = '';
        foreach ($tags as $tag) {
            $tagsHtml .= '<span class="spot-detail-tag">#' . htmlspecialchars($tag, ENT_QUOTES) . '</span>';
        }

        // Formatting comments
        $commentsHtml = '';
        if (empty($comments)) {
            $commentsHtml = '<p style="text-align:center; color:#555; padding: 20px;">' . $this->lang['spot_no_comments'] . '</p>';
        } else {
            foreach ($comments as $comment) {
                $cAvatar = !empty($comment['avatar']) ? htmlspecialchars($comment['avatar'], ENT_QUOTES) : 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($comment['username']);
                $cDate = date($this->lang['spot_date_format'] ?? 'M j, Y', strtotime($comment['created_at']));
                $commentsHtml .= '
                <div class="comment-item">
                    <div class="comment-header">
                        <img src="' . $cAvatar . '" class="comment-avatar">
                        <span class="comment-author">' . htmlspecialchars($comment['username'], ENT_QUOTES) . '</span>
                        <span class="comment-date">' . $cDate . '</span>
                    </div>
                    <div class="comment-body">
                        ' . nl2br(htmlspecialchars($comment['content'], ENT_QUOTES)) . '
                    </div>
                </div>';
            }
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
                z-index: 10;
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
                transition: transform 0.1s, box-shadow 0.1s, background 0.2s;
                text-decoration: none;
                font-size: 1rem;
            }
            .spot-action-btn:hover {
                background: #f4f4f4;
            }
            .spot-action-btn:active {
                transform: translate(2px, 2px);
                box-shadow: 2px 2px 0px #212121;
            }
            .spot-action-btn.is-liked {
                background: #ff5252;
                color: #fff;
            }
            .spot-action-btn.is-liked svg {
                fill: #fff;
            }
            
            /* LIKE ANIMATION */
            @keyframes like-burst {
                0% { transform: scale(1); }
                50% { transform: scale(1.4); }
                100% { transform: scale(1); }
            }
            .anim-like {
                animation: like-burst 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }

            /* COMMENTS STYLES */
            .comment-item {
                border-bottom: 2px dashed #ddd;
                padding: 20px 0;
            }
            .comment-item:last-child {
                border-bottom: none;
            }
            .comment-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }
            .comment-avatar {
                width: 35px;
                height: 35px;
                border-radius: 50%;
                border: 2px solid #212121;
            }
            .comment-author {
                font-family: \'Bungee\', cursive;
                font-size: 0.9rem;
            }
            .comment-date {
                font-size: 0.8rem;
                color: #888;
            }
            .comment-body {
                font-family: \'Inter\', sans-serif;
                font-size: 1rem;
                color: #333;
                line-height: 1.5;
            }
            
            .comment-form-container {
                margin-top: 30px;
            }
            .comment-textarea {
                width: 100%;
                min-height: 100px;
                border: 3px solid #212121;
                border-radius: 12px;
                padding: 15px;
                font-family: \'Inter\', sans-serif;
                font-size: 1rem;
                resize: vertical;
                box-shadow: 4px 4px 0px #212121;
                margin-bottom: 15px;
                box-sizing: border-box;
            }
            .comment-textarea:focus {
                outline: none;
                background-color: #f0f7ff;
            }
            .submit-comment-btn {
                background: #fbad40;
                border: 3px solid #212121;
                border-radius: 12px;
                padding: 12px 25px;
                font-family: \'Bungee\', cursive;
                color: #212121;
                cursor: pointer;
                box-shadow: 4px 4px 0px #212121;
                transition: all 0.1s;
            }
            .submit-comment-btn:hover {
                background: #ffc26b;
            }
            .submit-comment-btn:active {
                transform: translate(2px, 2px);
                box-shadow: 2px 2px 0px #212121;
            }
            .submit-comment-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
        </style>
        
        <div class="spot-detail-container">
            <div class="spot-detail-card">
                <div class="spot-detail-hero" style="background-image: url(\'' . $image . '\');">
                    <div class="spot-detail-category">' . $categoryLabel . '</div>
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
                            ' . $views . ' ' . $this->lang['spot_views'] . '
                        </span>
                    </div>
                    
                    <div class="spot-detail-desc">
                        ' . $description . '
                    </div>
                    
                    ' . ($tagsHtml ? '<div class="spot-detail-tags">' . $tagsHtml . '</div>' : '') . '
                    
                    <div class="spot-detail-footer">
                        <div class="spot-author">
                            <div class="spot-author-avatar" style="' . $avatarInlineStyle . '"></div>
                            <div class="spot-author-name">' . $this->lang['spot_posted_by'] . ' ' . $authorName . '</div>
                        </div>
                        
                        <div class="spot-actions">
                            <button id="like-btn" class="spot-action-btn ' . ($isLiked ? 'is-liked' : '') . '" ' . (!$isLoggedIn ? 'disabled title="' . $this->lang['spot_login_to_like'] . '"' : '') . ' onclick="handleLike()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="' . ($isLiked ? '#fff' : 'currentColor') . '" viewBox="0 0 256 256"><path d="M240,94c0,70-104,122.34-108.58,124.54a8,8,0,0,1-6.84,0C120,216.34,16,164,16,94A62.07,62.07,0,0,1,78,32c22.59,0,41.94,11.83,50,29.93C136.06,43.83,155.41,32,178,32A62.07,62.07,0,0,1,240,94Z"></path></svg>
                                <span id="likes-count">' . $likes . '</span>
                            </button>
                            <button class="spot-action-btn" onclick="window.history.back()">
                                ' . $this->lang['spot_back'] . '
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 40px; margin-bottom: 20px;">
                <h2 style="font-family:\'Bungee\', cursive; font-size:2rem; color:#212121;">' . $this->lang['spot_comments'] . '</h2>
            </div>
            
            <div class="spot-detail-card" style="padding: 40px;">
                <div id="comments-list">
                    ' . $commentsHtml . '
                </div>
                
                <div class="comment-form-container">
                    ' . ($isLoggedIn ? '
                        <h3 style="font-family:\'Bungee\', cursive; margin-bottom: 20px;">' . $this->lang['spot_leave_comment'] . '</h3>
                        <textarea id="comment-content" class="comment-textarea" placeholder="' . $this->lang['spot_comment_placeholder'] . '"></textarea>
                        <button id="submit-comment" class="submit-comment-btn" onclick="handleComment()">' . $this->lang['spot_post_comment'] . '</button>
                    ' : '
                        <p style="text-align:center; padding: 20px; font-family:\'Inter\', sans-serif;">
                            <a href="?page=login" style="color: #fbad40; font-weight: 800; text-decoration: underline;">Login</a> ' . $this->lang['spot_login_to_comment'] . '
                        </p>
                    ') . '
                </div>
            </div>
        </div>

        <script>
            async function handleLike() {
                const btn = document.getElementById(\'like-btn\');
                const countSpan = document.getElementById(\'likes-count\');
                const spotId = ' . ($spot['id'] ?? 0) . ';
                
                // Add temporary animation class
                btn.classList.add(\'anim-like\');
                setTimeout(() => btn.classList.remove(\'anim-like\'), 400);

                try {
                    const formData = new FormData();
                    formData.append(\'spot_id\', spotId);
                    
                    const response = await fetch(\'?action=toggleLikeSpot\', {
                        method: \'POST\',
                        body: formData
                    });
                    
                    const res = await response.json();
                    if (res.success) {
                        countSpan.innerText = res.likes_count;
                        if (res.isLiked) {
                            btn.classList.add(\'is-liked\');
                            btn.querySelector(\'svg\').setAttribute(\'fill\', \'#fff\');
                        } else {
                            btn.classList.remove(\'is-liked\');
                            btn.querySelector(\'svg\').setAttribute(\'fill\', \'currentColor\');
                        }
                    }
                } catch (e) {
                    console.error(\'Like error:\', e);
                }
            }

            async function handleComment() {
                const content = document.getElementById(\'comment-content\').value.trim();
                const btn = document.getElementById(\'submit-comment\');
                
                if (!content) return;
                
                btn.disabled = true;
                const originalText = btn.innerText;
                btn.innerText = "' . ($this->lang['spot_posting'] ?? 'POSTING...') . '";
                
                try {
                    const formData = new FormData();
                    formData.append(\'spot_id\', ' . ($spot['id'] ?? 0) . ');
                    formData.append(\'content\', content);
                    
                    const response = await fetch(\'?action=postComment\', {
                        method: \'POST\',
                        body: formData
                    });
                    
                    const res = await response.json();
                    if (res.success) {
                        window.location.reload();
                    } else {
                        alert("' . ($this->lang['spot_error_comment'] ?? 'Error') . ' " + (res.error || ""));
                    }
                } catch (e) {
                    console.error(\'Comment error:\', e);
                    alert("' . ($this->lang['spot_system_error'] ?? 'System error') . '");
                } finally {
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            }
        </script>
        ';
    }
}