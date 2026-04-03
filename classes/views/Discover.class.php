<?php
class Discover extends View {
    public function content() {
        $spots = $this->data['spots'] ?? [];
        
        // Sécurisation des données pour le JS (XSS protection)
        $safeSpots = array_map(function($s) {
            $s['title']       = htmlspecialchars($s['title'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $s['description'] = htmlspecialchars($s['description'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $s['location']    = htmlspecialchars($s['location'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $s['category']    = htmlspecialchars($s['category'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $s['latitude']    = isset($s['latitude']) ? floatval($s['latitude']) : null;
            $s['longitude']   = isset($s['longitude']) ? floatval($s['longitude']) : null;
            $s['likes_count'] = isset($s['likes_count']) ? intval($s['likes_count']) : 0;
            return $s;
        }, $spots);

        $spotsJson = json_encode($safeSpots, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        // Check login state constraint
        $addBtn = '';
        $modalHtml = '';
        if (User::isLoggedIn()) {
            $addBtn = '<button class="discover-btn-add" onclick="openSpotModal()" style="position:fixed; bottom:30px; right:30px; z-index:9999; padding:16px 28px; font-size:18px; font-family:\'Bungee\', cursive; background:#3aa26b; color:#fff; border:4px solid #212121; border-radius:999px; box-shadow:6px 6px 0px #212121; cursor:pointer; transition:transform 0.1s;">' . $this->lang['discover_add_spot'] . '</button>';
            $modalHtml = '
            <!-- Modal Ajouter un spot -->
            <div class="modal-overlay" id="spotModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10000; align-items:center; justify-content:center; flex-direction:column;">
                <div class="modal-box" style="background:#fff; border:4px solid #212121; border-radius:16px; box-shadow:8px 8px 0px #212121; padding:32px; max-width:600px; width:90%; position:relative; max-height:90vh; overflow-y:auto;">
                    <button class="modal-close" onclick="closeSpotModal()" style="position:absolute; top:20px; right:20px; background:none; border:2px solid #212121; border-radius:50%; width:36px; height:36px; font-weight:bold; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:2px 2px 0px #212121; z-index:1;">✕</button>
                    <h2 class="modal-title" style="margin:0 0 24px 0; font-family:\'Bungee\', cursive; font-size:24px;">' . $this->lang['discover_modal_title'] . '</h2>
                    <form onsubmit="return false;" style="display:flex; flex-direction:column; gap:16px; font-family:\'Inter\', sans-serif;">
                        <!-- Titre -->
                        <input type="text" placeholder="' . $this->lang['discover_title_placeholder'] . '" id="spotTitle" style="padding:12px; border:3px solid #212121; border-radius:8px; font-size:14px; outline:none;" required>
                        
                        <!-- Location -->
                        <input type="text" placeholder="' . $this->lang['discover_location_placeholder'] . '" id="spotLocation" style="padding:12px; border:3px solid #212121; border-radius:8px; font-size:14px; outline:none;" required>
                        
                        <!-- Description -->
                        <textarea placeholder="' . $this->lang['discover_description_placeholder'] . '" id="spotDescription" rows="3" style="padding:12px; border:3px solid #212121; border-radius:8px; font-family:inherit; font-size:14px; outline:none; resize:vertical;"></textarea>
                        
                        <!-- Latitude & Longitude -->
                        <div style="display:flex; gap:12px; width:100%;">
                            <input type="number" placeholder="📍 Latitude (ex: 48.8566)" id="spotLatitude" step="0.000001" min="-90" max="90" style="flex:1; padding:12px; border:3px solid #212121; border-radius:8px; font-size:14px; outline:none;" required>
                            <input type="number" placeholder="📍 Longitude (ex: 2.3522)" id="spotLongitude" step="0.000001" min="-180" max="180" style="flex:1; padding:12px; border:3px solid #212121; border-radius:8px; font-size:14px; outline:none;" required>
                        </div>
                        
                        <!-- Catégorie & Image -->
                        <div style="display:flex; align-items:center; gap:16px; width:100%;">
                            <select id="spotCategory" style="flex:1; padding:12px; border:3px solid #212121; border-radius:8px; font-size:14px; outline:none; cursor:pointer;">
                                <option value="nature">🌿 ' . $this->lang['discover_category_nature'] . '</option>
                                <option value="ville">🏙️ ' . $this->lang['discover_category_city'] . '</option>
                                <option value="voyage">🏖️ ' . $this->lang['discover_category_beach'] . '</option>
                                <option value="aventure">⛰️ ' . $this->lang['discover_category_adventure'] . '</option>
                                <option value="gastronomie">🍜 ' . $this->lang['discover_category_food'] . '</option>
                                <option value="culture">🎭 ' . $this->lang['discover_category_culture'] . '</option>
                                <option value="autre">✨ ' . $this->lang['discover_category_other'] . '</option>
                            </select>
                            <input type="file" accept="image/*" id="spotImage" style="flex:1; min-width:0; padding:12px; border:3px solid #212121; border-radius:8px; font-size:14px; outline:none; background:#f5eedc; overflow:hidden; max-width:100%;">
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="button" onclick="saveSpot()" style="margin-top:8px; padding:14px; border:3px solid #212121; border-radius:8px; background:#fbad40; color:#212121; font-family:\'Bungee\', cursive; font-size:16px; cursor:pointer; box-shadow:4px 4px 0px #212121; transition:transform 0.1s;">' . $this->lang['discover_publish'] . '</button>
                    </form>
                </div>
            </div>
            <script>
            function openSpotModal() { document.getElementById("spotModal").style.display = "flex"; }
            function closeSpotModal() { 
                document.getElementById("spotModal").style.display = "none";
                // Réinitialiser le formulaire
                document.getElementById("spotTitle").value = "";
                document.getElementById("spotLocation").value = "";
                document.getElementById("spotDescription").value = "";
                document.getElementById("spotLatitude").value = "";
                document.getElementById("spotLongitude").value = "";
                document.getElementById("spotCategory").value = "nature";
                document.getElementById("spotImage").value = "";
            }
            
            function saveSpot() {
                const title = document.getElementById("spotTitle").value.trim();
                const location = document.getElementById("spotLocation").value.trim();
                const description = document.getElementById("spotDescription").value.trim();
                const latitude = document.getElementById("spotLatitude").value.trim();
                const longitude = document.getElementById("spotLongitude").value.trim();
                const category = document.getElementById("spotCategory").value;
                const imageFile = document.getElementById("spotImage").files[0];

                if (!title || !location) { 
                    alert("' . $this->lang['discover_error_fill'] . '"); 
                    return; 
                }

                if (!latitude || !longitude) { 
                    alert("Les coordonnées GPS (latitude et longitude) sont obligatoires !"); 
                    return; 
                }

                const lat = parseFloat(latitude);
                const lng = parseFloat(longitude);

                if (isNaN(lat) || lat < -90 || lat > 90) {
                    alert("Latitude invalide ! Doit être entre -90 et 90");
                    return;
                }

                if (isNaN(lng) || lng < -180 || lng > 180) {
                    alert("Longitude invalide ! Doit être entre -180 et 180");
                    return;
                }

                const formData = new FormData();
                formData.append("title", title);
                formData.append("location", location);
                formData.append("description", description);
                formData.append("latitude", latitude);
                formData.append("longitude", longitude);
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
                        alert("' . $this->lang['discover_success'] . '");
                        closeSpotModal();
                        window.location.reload();
                    } else { alert("' . $this->lang['discover_error'] . '" + (data.error || "Unknown")); }
                })
                .catch(err => { console.error(err); alert("' . $this->lang['discover_network_error'] . '"); });
            }
            </script>';
        } else {
            $addBtn = '<a href="?page=login" class="discover-btn-add" style="position:fixed; bottom:30px; right:30px; z-index:9999; display:flex; align-items:center; justify-content:center; padding:16px 28px; font-size:18px; font-family:\'Bungee\', cursive; background:#3aa26b; color:#fff; border:4px solid #212121; border-radius:999px; text-decoration:none; box-shadow:6px 6px 0px #212121; transition:transform 0.1s;">' . $this->lang['discover_add_spot'] . '</a>';
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
                <span class="discover-badge">' . $this->lang['discover_badge'] . '</span>
                <h1 class="discover-title">' . $this->lang['discover_title'] . '</h1>
                <p class="discover-subtitle">' . $this->lang['discover_subtitle'] . '</p>

                ' . $addBtn . '

                <!-- FILTERS -->
                <div class="discover-filters" id="discover-filters">
                    <button class="discover-filter-btn active" data-filter="all">' . $this->lang['discover_filter_all'] . '</button>
                    <button class="discover-filter-btn" data-filter="france">' . $this->lang['discover_filter_france'] . '</button>
                    <button class="discover-filter-btn" data-filter="albania">' . $this->lang['discover_filter_albania'] . '</button>
                    <button class="discover-filter-btn" data-filter="vietnam">' . $this->lang['discover_filter_vietnam'] . '</button>
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
                    ' . $this->lang['discover_empty'] . '
                </div>
                
                <div class="discover-grid" id="discover-grid">
                    <!-- Les cartes sont injectées par JS -->
                </div>
                <!-- Controls de pagination -->
                <div class="discover-pagination" id="discover-pagination" style="display: none;">
                    <button class="discover-page-btn" id="btn-prev" disabled>' . $this->lang['discover_prev'] . '</button>
                    <span id="page-indicator" style="font-family: \'Bungee\', cursive; margin: 0 15px;">' . $this->lang['discover_page'] . ' 1</span>
                    <button class="discover-page-btn" id="btn-next">' . $this->lang['discover_next'] . '</button>
                </div>
            </section>
        </main>

        ' . $modalHtml . '

        <script>
            // Expose PHP data to window object for our external script
            window.FAV_SPOTS_DATA = ' . str_replace("'", "\'", $spotsJson) . ';
            window.FAV_LANG_DATA = {
                view_details: "' . $this->lang['discover_view_details'] . '",
                no_image: "' . $this->lang['discover_no_image'] . '",
                unknown: "' . $this->lang['discover_unknown'] . '",
                no_description: "' . $this->lang['discover_no_desc'] . '",
                travelers: "' . $this->lang['discover_travelers'] . '",
                page: "' . $this->lang['discover_page'] . '",
                of: "' . ($this->lang['discover_of'] ?? 'OF') . '"
            };
        </script>
        
        <!-- ✅ CHARGER DISCOVER-MAP.JS POUR LA CARTE -->
        <script src="public/js/discover-map.js"></script>
        
        <!-- ✅ CHARGER DISCOVER.JS POUR LA GRILLE DES SPOTS -->
        <script src="public/js/discover.js"></script>
        ';
    }
}