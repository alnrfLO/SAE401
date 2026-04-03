<?php
class Directory extends View
{
    public function content()
    {
        $currentLang = $_GET['lang'] ?? 'en';
        
        return '
        <link rel="stylesheet" href="public/css/directory.css">
        <div class="directory-wrapper">
            
            <!-- En-tête -->
            <div class="directory-header">
                <div class="directory-header-content">
                    <h1 class="directory-title">' . $this->lang['directory_title'] . '</h1>
                    <p class="directory-subtitle">' . $this->lang['directory_subtitle'] . '</p>
                </div>
            </div>

            <!-- Grille principale -->
            <div class="directory-container">
                
                <!-- SIDEBAR FILTRES -->
                <aside class="directory-sidebar">
                    <div class="filter-card">
                        <h3 class="filter-title">🌐 ' . $this->lang['directory_filter_language'] . '</h3>
                        <div class="filter-options">
                            <label class="filter-checkbox">
                                <input type="checkbox" class="lang-filter" value="fr" checked>
                                🇫🇷 Français
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" class="lang-filter" value="al" checked>
                                🇦🇱 Shqip
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" class="lang-filter" value="vi" checked>
                                🇻🇳 Tiếng Việt
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" class="lang-filter" value="us" checked>
                                🇺🇸 English
                            </label>
                        </div>
                    </div>

                    <div class="filter-card">
                        <h3 class="filter-title">🏷️ ' . $this->lang['directory_filter_skills'] . '</h3>
                        <input type="text" class="filter-search" id="skillsSearch" placeholder="Search skills...">
                        <div class="filter-tags" id="skillsTags">
                            <!-- Tags dynamiques -->
                        </div>
                    </div>

                    <button class="filter-reset-btn" onclick="resetFilters()">
                        🔄 ' . $this->lang['directory_reset'] . '
                    </button>
                </aside>

                <!-- CONTENU PRINCIPAL -->
                <main class="directory-main">
                    
                    <!-- ONGLETS -->
                    <div class="directory-tabs">
                        <button class="tab-btn tab-btn--active" onclick="switchTab(\'map\')">
                            🗺️ ' . $this->lang['directory_tab_map'] . '
                        </button>
                        <button class="tab-btn" onclick="switchTab(\'list\')">
                            👥 ' . $this->lang['directory_tab_list'] . '
                        </button>
                    </div>

                    <!-- TAB: MAP -->
                    <div class="tab-content tab-content--active" id="mapTab">
                        <div id="directoryMap" class="directory-map"></div>
                        <div class="map-legend">
                            <span class="map-legend-item">
                                <span class="map-dot" style="background: #3aa26b;"></span>
                                ' . $this->lang['directory_legend_active'] . '
                            </span>
                        </div>
                    </div>

                    <!-- TAB: LIST -->
                    <div class="tab-content" id="listTab">
                        <div class="users-grid" id="usersGrid">
                            <!-- Users dynamiques -->
                        </div>
                    </div>

                </main>

            </div><!-- /.directory-container -->
        </div><!-- /.directory-wrapper -->

        <script src="public/js/map.js"></script>
        <script>
        // Données mock (remplacer par une requête API plus tard)
        const mockUsers = [
            { id: 1, username: "Sophie", country: "France", language: "fr", avatar: "🇫🇷", bio: "Voyageuse passionnée" },
            { id: 2, username: "Artan", country: "Albania", language: "al", avatar: "🇦🇱", bio: "Digital nomad" },
            { id: 3, username: "Linh", country: "Vietnam", language: "vi", avatar: "🇻🇳", bio: "Foodie & traveler" },
            { id: 4, username: "Mike", country: "US", language: "us", avatar: "🇺🇸", bio: "Explorer" }
        ];

        function switchTab(tabName) {
            document.querySelectorAll(\".tab-btn\").forEach(btn => btn.classList.remove(\"tab-btn--active\"));
            document.querySelectorAll(\".tab-content\").forEach(tab => tab.classList.remove(\"tab-content--active\"));
            
            event.target.classList.add(\"tab-btn--active\");
            document.getElementById(tabName + \"Tab\").classList.add(\"tab-content--active\");
            
            if (tabName === \"map\") {
                setTimeout(() => initMap(), 100);
            }
        }

        function renderUsersList() {
            const grid = document.getElementById(\"usersGrid\");
            grid.innerHTML = mockUsers.map(user => `
                <div class="user-card">
                    <div class="user-avatar" style="font-size: 40px;">\${user.avatar}</div>
                    <div class="user-info">
                        <h3 class="user-name">\${user.username}</h3>
                        <p class="user-bio">\${user.bio}</p>
                        <div class="user-meta">
                            <span class="user-country">\${user.country}</span>
                            <span class="user-lang">\${user.language.toUpperCase()}</span>
                        </div>
                    </div>
                    <a href="?page=profile&id=\${user.id}" class="user-view-btn">View →</a>
                </div>
            \`).join("");
        }

        function resetFilters() {
            document.querySelectorAll(\".lang-filter\").forEach(cb => cb.checked = true);
            renderUsersList();
        }

        // Init
        renderUsersList();
        </script>
        ';
    }
}