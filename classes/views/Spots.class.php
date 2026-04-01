<?php
class Spots extends Dashboard {
    public function content() {
        $user = $this->data['profileUser'] ?? [];
        $spots = $this->data['spots'] ?? [];

        if (!$user) {
            return '<div style="text-align:center;padding:200px 20px;"><h2>User not found.</h2></div>';
        }

        $initials = strtoupper(substr($user['username'] ?? '??', 0, 2));
        $avatar = !empty($user['avatar'])
            ? '<img src="' . htmlspecialchars($user['avatar'], ENT_QUOTES) . '" alt="Avatar" class="sidebar-avatar-img">'
            : '<div class="sidebar-avatar-placeholder">' . $initials . '</div>';

        return '
        <link rel="stylesheet" href="public/css/dashboard.css">
        <link rel="stylesheet" href="public/css/spots.css">
        <div class="dash-layout">

            ' . $this->sidebar($user, $avatar, 'spots') . '

            <div class="dash-main">
                <div class="dash-topbar">
                    <div>
                        <h1 class="dash-title">DISCOVER SPOTS</h1>
                        <p class="dash-subtitle">Explore amazing places shared by travelers around the world.</p>
                    </div>
                    <button class="dash-new-event-btn" onclick="openSpotModal()" style="position:fixed; bottom:30px; right:30px; z-index:9999; padding:16px 28px; font-size:18px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 01.75.75v6.75h6.75a.75.75 0 010 1.5h-6.75v6.75a.75.75 0 01-1.5 0v-6.75H4.5a.75.75 0 010-1.5h6.75V4.5a.75.75 0 01.75-.75z" clip-rule="evenodd"/></svg>
                        ADD SPOT
                    </button>
                </div>

                <!-- SPOTS GRID -->
                <div class="spots-grid">
                    ' . $this->renderSpotsList($spots) . '
                </div>

                <!-- MAP SECTION -->
                <div class="spots-map-section">
                    <h2 class="spots-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-2.003 3.5-4.697 3.5-8.327a8 8 0 1 0-16 0c0 3.63 1.556 6.324 3.5 8.327a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.144.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                        </svg>
                        Spots Around the World
                    </h2>
                    <div class="spots-map-wrapper">
                        <svg id="world-map" class="world-map" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2000 857">
                            <!-- SVG du monde sera chargé ici -->
                        </svg>
                        <div class="map-legend">
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #3aa26b;"></span>
                                <span>Spots Posted</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #fbad40;"></span>
                                <span>Popular Areas</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Ajouter un spot -->
        <div class="modal-overlay" id="spotModal" style="display:none">
            <div class="modal-box">
                <div class="modal-header">
                    <h2 class="modal-title">NEW SPOT</h2>
                    <button class="modal-close" onclick="closeSpotModal()">✕</button>
                </div>
                <form class="modal-form" onsubmit="return false;">
                    <div class="modal-field">
                        <label>SPOT TITLE</label>
                        <input type="text" placeholder="e.g. Hidden Beach Paradise" id="spotTitle">
                    </div>
                    <div class="modal-field">
                        <label>LOCATION</label>
                        <input type="text" placeholder="e.g. Bali, Indonesia" id="spotLocation">
                    </div>
                    <div class="modal-field">
                        <label>DESCRIPTION</label>
                        <textarea placeholder="Describe this amazing spot..." id="spotDescription" rows="3"></textarea>
                    </div>
                    <div class="modal-row">
                        <div class="modal-field">
                            <label>CATEGORY</label>
                            <select id="spotCategory">
                                <option value="nature">🌿 Nature</option>
                                <option value="ville">🏙️ City</option>
                                <option value="voyage">🏖️ Beach/Trip</option>
                                <option value="aventure">⛰️ Mountain/Adventure</option>
                                <option value="gastronomie">🍜 Food</option>
                                <option value="culture">🎭 Culture</option>
                                <option value="autre">✨ Other</option>
                            </select>
                        </div>
                        <div class="modal-field">
                            <label>IMAGE</label>
                            <input type="file" accept="image/*" id="spotImage">
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="prof-btn prof-btn--outline" onclick="closeSpotModal()">Cancel</button>
                        <button type="button" class="prof-btn prof-btn--primary" onclick="saveSpot()">Publish Spot</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function openSpotModal() {
            document.getElementById("spotModal").style.display = "flex";
        }

        function closeSpotModal() {
            document.getElementById("spotModal").style.display = "none";
        }

        function saveSpot() {
            const title = document.getElementById("spotTitle").value.trim();
            const location = document.getElementById("spotLocation").value.trim();
            const description = document.getElementById("spotDescription").value.trim();
            const category = document.getElementById("spotCategory").value;
            const imageFile = document.getElementById("spotImage").files[0];

            if (!title || !location) {
                alert("Please fill in title and location");
                return;
            }

            const formData = new FormData();
            formData.append("title", title);
            formData.append("location", location);
            formData.append("description", description);
            formData.append("category", category);
            if (imageFile) {
                formData.append("image", imageFile);
            }

            // Fetch API call to save spot
            fetch("?page=spots&action=create", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast("✅ Spot published!");
                    closeSpotModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast("❌ Error creating spot");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("⚠️ Error");
            });
        }

        function showToast(msg) {
            const toast = document.createElement("div");
            toast.textContent = msg;
            toast.style.cssText = `
                position: fixed;
                bottom: 32px;
                left: 50%;
                transform: translateX(-50%);
                background: #212121;
                color: #fff;
                font-family: "Bungee", cursive;
                font-size: 14px;
                padding: 12px 24px;
                border-radius: 999px;
                border: 3px solid #fbad40;
                box-shadow: 4px 4px 0px #fbad40;
                z-index: 9999;
                animation: fadeInUp 0.3s ease;
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        </script>';
    }

    private function renderSpotsList($spots) {
        if (empty($spots)) {
            return '<div class="spots-empty">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="48" height="48">
                    <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-2.003 3.5-4.697 3.5-8.327a8 8 0 1 0-16 0c0 3.63 1.556 6.324 3.5 8.327a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.144.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                </svg>
                <p>No spots yet. Be the first to share!</p>
            </div>';
        }

        $html = '';
        foreach ($spots as $spot) {
            $image = $spot['image'] ? htmlspecialchars($spot['image'], ENT_QUOTES) : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23f0ebe0%22 width=%22400%22 height=%22300%22/%3E%3C/svg%3E';
            $title = htmlspecialchars($spot['title'] ?? 'Untitled', ENT_QUOTES);
            $location = htmlspecialchars($spot['location'] ?? 'Unknown', ENT_QUOTES);
            $category = $spot['category'] ?? 'other';
            $likes = $spot['likes_count'] ?? 0;

            $html .= '
            <div class="spot-card">
                <div class="spot-card-image" style="background-image: url(' . $image . ')">
                    <span class="spot-category spot-category--' . htmlspecialchars($category) . '">' . ucfirst($category) . '</span>
                </div>
                <div class="spot-card-body">
                    <h3 class="spot-card-title">' . $title . '</h3>
                    <p class="spot-card-location">📍 ' . $location . '</p>
                    <div class="spot-card-footer">
                        <button class="spot-like-btn" onclick="toggleLike(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                            </svg>
                            <span>' . $likes . '</span>
                        </button>
                    </div>
                </div>
            </div>';
        }

        return $html;
    }
}