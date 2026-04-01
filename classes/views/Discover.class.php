<?php
class Discover extends View {
    public function content() {
        $spots = $this->data['spots'] ?? [];
        
        // Sécurisation de l'encodage JSON pour JavaScript
        $spotsJson = json_encode($spots, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        // Check login state constraint
        $addBtn = '';
        $modalHtml = '';
        if (User::isLoggedIn()) {
            $addBtn = '<button class="discover-btn-add" onclick="openSpotModal()" style="position:fixed; bottom:30px; right:30px; z-index:9999; padding:16px 28px; font-size:18px; font-family:\'Bungee\', cursive; background:#3aa26b; color:#fff; border:4px solid #212121; border-radius:999px; box-shadow:6px 6px 0px #212121; cursor:pointer; transition:transform 0.1s;">+ ADD SPOT</button>';
            $modalHtml = '
            <!-- Modal Ajouter un spot -->
            <div class="modal-overlay" id="spotModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10000; align-items:center; justify-content:center; flex-direction:column;">
                <div class="modal-box" style="background:#fff; border:4px solid #212121; border-radius:16px; box-shadow:8px 8px 0px #212121; padding:32px; max-width:500px; width:90%; position:relative;">
                    <button class="modal-close" onclick="closeSpotModal()" style="position:absolute; top:20px; right:20px; background:none; border:2px solid #212121; border-radius:50%; width:36px; height:36px; font-weight:bold; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:2px 2px 0px #212121;">✕</button>
                    <h2 class="modal-title" style="margin:0 0 24px 0; font-family:\'Bungee\', cursive; font-size:24px;">NEW SPOT 🌍</h2>
                    <form onsubmit="return false;" style="display:flex; flex-direction:column; gap:16px; font-family:\'Inter\', sans-serif;">
                        <input type="text" placeholder="SPOT TITLE (e.g. Hidden Beach)" id="spotTitle" style="padding:12px; border:3px solid #212121; border-radius:8px; font-size:14px; outline:none;" required>
                        <input type="text" placeholder="LOCATION (e.g. Bali, Indonesia)" id="spotLocation" style="padding:12px; border:3px solid #212121; border-radius:8px; font-size:14px; outline:none;" required>
                        <textarea placeholder="Describe this amazing place..." id="spotDescription" rows="3" style="padding:12px; border:3px solid #212121; border-radius:8px; font-family:inherit; font-size:14px; outline:none; resize:vertical;"></textarea>
                        <div style="display:flex; gap:16px;">
                            <select id="spotCategory" style="flex:1; padding:12px; border:3px solid #212121; border-radius:8px; font-size:14px; outline:none;">
                                <option value="nature">🌿 Nature</option>
                                <option value="ville">🏙️ City</option>
                                <option value="voyage">🏖️ Beach/Trip</option>
                                <option value="aventure">⛰️ Mountain/Adventure</option>
                                <option value="gastronomie">🍜 Food</option>
                                <option value="culture">🎭 Culture</option>
                                <option value="autre">✨ Other</option>
                            </select>
                            <input type="file" accept="image/*" id="spotImage" style="flex:1; padding:12px; border:3px solid #212121; border-radius:8px; font-size:14px; outline:none; background:#f5eedc;">
                        </div>
                        <button type="button" onclick="saveSpot()" style="margin-top:8px; padding:14px; border:3px solid #212121; border-radius:8px; background:#fbad40; color:#212121; font-family:\'Bungee\', cursive; font-size:16px; cursor:pointer; box-shadow:4px 4px 0px #212121; transition:transform 0.1s;">PUBLISH IT !</button>
                    </form>
                </div>
            </div>
            <script>
            function openSpotModal() { document.getElementById("spotModal").style.display = "flex"; }
            function closeSpotModal() { document.getElementById("spotModal").style.display = "none"; }
            function saveSpot() {
                const title = document.getElementById("spotTitle").value.trim();
                const location = document.getElementById("spotLocation").value.trim();
                const description = document.getElementById("spotDescription").value.trim();
                const category = document.getElementById("spotCategory").value;
                const imageFile = document.getElementById("spotImage").files[0];

                if (!title || !location) { alert("Please fill in title and location"); return; }

                const formData = new FormData();
                formData.append("title", title);
                formData.append("location", location);
                formData.append("description", description);
                formData.append("category", category);
                if (imageFile) {
                    formData.append("image", imageFile);
                }

                fetch("?page=spots&action=create", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Spot published with success! Reloading map...");
                        closeSpotModal();
                        window.location.reload();
                    } else { alert("Error: " + (data.error || "Unknown")); }
                })
                .catch(err => { console.error(err); alert("Network Error"); });
            }
            </script>';
        } else {
            $addBtn = '<a href="?page=login" class="discover-btn-add" style="position:fixed; bottom:30px; right:30px; z-index:9999; display:flex; align-items:center; justify-content:center; padding:16px 28px; font-size:18px; font-family:\'Bungee\', cursive; background:#3aa26b; color:#fff; border:4px solid #212121; border-radius:999px; text-decoration:none; box-shadow:6px 6px 0px #212121; transition:transform 0.1s;">+ ADD SPOT</a>';
        }

        return '
        <link rel="stylesheet" href="public/css/discover.css">
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <main class="discover-main">
            <!-- HEADING SECTION -->
            <section class="discover-header">
                <span class="discover-badge">EXPLORE</span>
                <h1 class="discover-title">MAP & SPOTS</h1>
                <p class="discover-subtitle">Discover travel destinations shared by our intergenerational community.</p>

                ' . $addBtn . '

                <!-- FILTERS -->
                <div class="discover-filters" id="discover-filters">
                    <button class="discover-filter-btn active" data-filter="all">All</button>
                    <button class="discover-filter-btn" data-filter="france">France</button>
                    <button class="discover-filter-btn" data-filter="albania">Albania</button>
                    <button class="discover-filter-btn" data-filter="vietnam">Vietnam</button>
                </div>
            </section>

            <!-- MAP SECTION -->
            <section class="discover-map-wrapper">
                <div id="discover-map" class="discover-map-container"></div>
            </section>

            <!-- SPOTS GRID -->
            <section class="discover-spots-section">
                <!-- Message éventuel si aucun spot trouvé -->
                <div id="discover-empty-state" style="display: none; text-align: center; font-family: \'Bungee\', cursive; margin-top: 40px;">
                    No spots found for this filter.
                </div>
                
                <div class="discover-grid" id="discover-grid">
                    <!-- Les cartes sont injectées par JS -->
                </div>
                <!-- Controls de pagination -->
                <div class="discover-pagination" id="discover-pagination" style="display: none;">
                    <button class="discover-page-btn" id="btn-prev" disabled>← Previous</button>
                    <span id="page-indicator" style="font-family: \'Bungee\', cursive; margin: 0 15px;">Page 1</span>
                    <button class="discover-page-btn" id="btn-next">Next →</button>
                </div>
            </section>
        </main>

        ' . $modalHtml . '

        <script>
            // Expose PHP data to window object for our external script
            window.FAV_SPOTS_DATA = ' . str_replace("'", "\'", $spotsJson) . ';
        </script>
        <script src="public/js/discover.js"></script>
        ';
    }
}
