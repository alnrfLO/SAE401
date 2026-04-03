/**
 * Leaflet Map Manager for Discover Page
 * Displays all spots with markers and popups
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('📍 Initializing Discover map...');

    // ─── INITIALIZE MAP ─────────────────────────────────────────
    const map = L.map('discover-map', {
        center: [54.5973, 15.2551],  // ✅ Centered on Europe
        zoom: 4,
        minZoom: 2,
        maxZoom: 18
    });

    // ─── ADD OPENSTREETMAP TILES ────────────────────────────────
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors © CARTO',
        maxZoom: 19,
        crossOrigin: true
    }).addTo(map);

    // ─── MARKER GROUP (manage multiple spots) ───────────────────
    const markerGroup = L.featureGroup().addTo(map);

    // ─── FETCH SPOTS WITH COORDINATES ──────────────────────────
    fetch('?action=getSpotsForMap')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Spots received:', data.spots?.length || 0);
            console.log('📊 Data:', data);

            if (data.success && data.spots && data.spots.length > 0) {
                // Create marker for each spot
                data.spots.forEach(spot => {
                    if (spot.latitude && spot.longitude) {
                        createMarker(map, spot, markerGroup);
                    } else {
                        console.warn('⚠️ Spot without coordinates:', spot.id, spot.title);
                    }
                });

                // Adjust view to show all markers
                if (markerGroup.getLayers().length > 0) {
                    map.fitBounds(markerGroup.getBounds(), { 
                        padding: [80, 80],
                        maxZoom: 5
                    });
                    console.log('✅ Map adjusted with', markerGroup.getLayers().length, 'markers');
                } else {
                    console.warn('⚠️ No markers created despite spots');
                }
            } else {
                console.warn('⚠️ No spots found or API error');
                showMapMessage(map, 'No spots found on the map');
            }
        })
        .catch(error => {
            console.error('❌ Error loading spots:', error);
            showMapMessage(map, 'Error loading spots');
        });

    // ─── FUNCTION: CREATE MARKER ───────────────────────────────
    function createMarker(map, spot, markerGroup) {
        const lat = parseFloat(spot.latitude);
        const lng = parseFloat(spot.longitude);

        if (isNaN(lat) || isNaN(lng)) {
            console.warn('⚠️ Invalid coordinates for spot:', spot.id);
            return;
        }

        // Determine icon color based on category
        const iconColor = getCategoryColor(spot.category);
        const icon = createCustomIcon(iconColor, spot.category);

        // Create marker
        const marker = L.marker([lat, lng], { icon }).addTo(markerGroup);
        
        // Add classes for styling
        marker.getElement()?.classList.add('fav-spot-marker');
        marker.getElement()?.setAttribute('data-category', spot.category);

        // Create popup content
        const popupContent = createPopupContent(spot);

        // Track if popup was clicked
        let popupClickedManually = false;

        // Bind popup to marker
        marker.bindPopup(popupContent, {
            maxWidth: 320,
            className: 'fav-spot-popup-custom',
            closeButton: true,
            autoPan: true,
            autoClose: false
        });

        // Hover handling
        marker.on('mouseover', function() {
            this.getElement()?.classList.add('fav-marker-hover');
            if (!popupClickedManually) {
                this.openPopup();
            }
        });
        
        marker.on('mouseout', function() {
            this.getElement()?.classList.remove('fav-marker-hover');
            if (!popupClickedManually) {
                this.closePopup();
            }
        });

        // Click handling
        marker.on('click', function() {
            popupClickedManually = !popupClickedManually;
            if (popupClickedManually) {
                this.openPopup();
            } else {
                this.closePopup();
            }
        });
    }

    // ─── FUNCTION: CREATE CUSTOM ICON ──────────────────────────
    function createCustomIcon(color, category) {
        const svg = `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48" height="48">
                <!-- Shadow -->
                <defs>
                    <filter id="shadow-${Math.random()}" x="-50%" y="-50%" width="200%" height="200%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.3"/>
                    </filter>
                </defs>
                
                <!-- Main pin -->
                <g filter="url(#shadow-${Math.random()})">
                    <!-- Pin body -->
                    <path d="M 24 2 C 14 2 6 10 6 20 C 6 32 24 46 24 46 C 24 46 42 32 42 20 C 42 10 34 2 24 2 Z" 
                          fill="${color}" stroke="#212121" stroke-width="1.5"/>
                    
                    <!-- Inner circle (white dot) -->
                    <circle cx="24" cy="20" r="6" fill="white" stroke="#212121" stroke-width="1"/>
                </g>
            </svg>
        `;
        
        return L.icon({
            iconUrl: 'data:image/svg+xml;utf8,' + encodeURIComponent(svg),
            iconSize: [48, 48],
            iconAnchor: [24, 48],
            popupAnchor: [0, -48],
            className: 'fav-custom-icon'
        });
    }

    // ─── FUNCTION: GET COLOR BY CATEGORY ───────────────────────
    function getCategoryColor(category) {
        const colors = {
            'restaurant': '#FF6B6B',
            'bar': '#4ECDC4',
            'cafe': '#FFE66D',
            'shopping': '#FF85E3',
            'park': '#95E1D3',
            'monument': '#F38181',
            'beach': '#A8E6CF',
            'museum': '#FFD3B6',
            'sport': '#8FD3F4',
            'nature': '#C1FFD7',
            'city': '#FF85E3',
            'travel': '#A8E6CF',
            'adventure': '#FFD3B6',
            'food': '#FF6B6B',
            'culture': '#8FD3F4',
            'other': '#3aa26b'
        };
        return colors[category?.toLowerCase()] || '#3aa26b';
    }

    // ─── FUNCTION: CREATE POPUP CONTENT ────────────────────────
    function createPopupContent(spot) {
        return `
            <div class="fav-popup-wrapper">
                <!-- Image -->
                ${spot.image ? `
                    <div class="fav-popup-image">
                        <img src="${escapeHtml(spot.image)}" 
                             alt="${escapeHtml(spot.title)}"
                             onerror="this.style.display='none'">
                    </div>
                ` : ''}

                <!-- Content -->
                <div class="fav-popup-content">
                    <!-- Title -->
                    <h3 class="fav-popup-title">
                        ${escapeHtml(spot.title)}
                    </h3>

                    <!-- Location -->
                    ${spot.location ? `
                        <p class="fav-popup-location">
                            📍 ${escapeHtml(spot.location)}
                        </p>
                    ` : ''}

                    <!-- Category -->
                    ${spot.category ? `
                        <span class="fav-popup-category fav-cat-${spot.category.toLowerCase()}">
                            ${escapeHtml(spot.category)}
                        </span>
                    ` : ''}

                    <!-- Description -->
                    ${spot.description ? `
                        <p class="fav-popup-description">
                            ${escapeHtml(spot.description.substring(0, 120))}${spot.description.length > 120 ? '...' : ''}
                        </p>
                    ` : ''}

                    <!-- Likes -->
                    ${spot.likes_count ? `
                        <div class="fav-popup-stats">
                            <span class="fav-popup-likes">❤️ ${spot.likes_count}</span>
                        </div>
                    ` : ''}

                    <!-- Button -->
                    <a href="?page=spot&id=${spot.id}" class="fav-popup-btn">
                        👁️ VIEW DETAILS
                    </a>
                </div>
            </div>
        `;
    }

    // ─── FUNCTION: SHOW MESSAGE ON MAP ────────────────────────
    function showMapMessage(map, message) {
        const messageDiv = document.createElement('div');
        messageDiv.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(33, 33, 33, 0.9);
            color: white;
            padding: 20px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-family: 'Bungee', cursive;
            z-index: 1000;
            pointer-events: none;
            border: 3px solid #fbad40;
        `;
        messageDiv.textContent = message;
        document.getElementById('discover-map').appendChild(messageDiv);
    }

    // ─── FUNCTION: ESCAPE HTML ────────────────────────────────
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // ─── ADD CUSTOM CSS STYLES ────────────────────────────────
    addMapStyles();

    function addMapStyles() {
        const style = document.createElement('style');
        style.textContent = `
            /* ══════════════════════════════════════════════════════
               MARKER STYLES
               ══════════════════════════════════════════════════════ */
            
            .fav-custom-icon {
                transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.25));
            }

            .fav-spot-marker.fav-marker-hover .leaflet-marker-icon {
                transform: scale(1.3) translateY(-8px) !important;
                filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.35)) brightness(1.1) !important;
            }

            /* ══════════════════════════════════════════════════════
               POPUP STYLES
               ══════════════════════════════════════════════════════ */

            .fav-spot-popup-custom .leaflet-popup-content-wrapper {
                background: #ffffff;
                border: 4px solid #212121;
                border-radius: 16px;
                box-shadow: 6px 6px 0px rgba(33, 33, 33, 0.2);
                padding: 0;
                overflow: hidden;
                font-family: 'Inter', sans-serif;
            }

            .fav-spot-popup-custom .leaflet-popup-content {
                margin: 0;
                padding: 0;
                width: 280px;
            }

            .fav-spot-popup-custom .leaflet-popup-tip {
                background: #ffffff;
                border-left: 3px solid #212121;
                border-right: 3px solid #212121;
            }

            .fav-spot-popup-custom .leaflet-popup-close-button {
                background: #fbad40;
                color: #212121;
                font-size: 20px;
                font-weight: bold;
                width: 32px;
                height: 32px;
                line-height: 32px;
                border-radius: 50%;
                border: 2px solid #212121;
                position: absolute;
                top: 12px;
                right: 12px;
                z-index: 100;
                transition: all 0.2s ease;
            }

            .fav-spot-popup-custom .leaflet-popup-close-button:hover {
                background: #ff9500;
                transform: scale(1.1) rotate(90deg);
            }

            /* ─── POPUP WRAPPER ──────────────────────────────── */
            .fav-popup-wrapper {
                padding: 0;
            }

            /* ─── IMAGE ──────────────────────────────────────── */
            .fav-popup-image {
                width: 100%;
                height: 160px;
                overflow: hidden;
                background: linear-gradient(135deg, #f5eedc 0%, #e8dcc8 100%);
                position: relative;
            }

            .fav-popup-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }

            .fav-popup-image img:hover {
                transform: scale(1.05);
            }

            /* ─── CONTENT ────────────────────────────────────── */
            .fav-popup-content {
                padding: 16px;
            }

            /* ─── TITLE ──────────────────────────────────────── */
            .fav-popup-title {
                margin: 0 0 8px 0;
                font-size: 16px;
                font-weight: 700;
                font-family: 'Bungee', cursive;
                color: #212121;
                line-height: 1.2;
            }

            /* ─── LOCATION ───────────────────────────────────── */
            .fav-popup-location {
                margin: 0 0 8px 0;
                font-size: 13px;
                color: #666;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            /* ─── CATEGORY ───────────────────────────────────── */
            .fav-popup-category {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 600;
                color: white;
                border: 2px solid #212121;
                margin-bottom: 8px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .fav-cat-nature { background: #C1FFD7; color: #212121; }
            .fav-cat-city { background: #FF85E3; color: white; }
            .fav-cat-travel { background: #A8E6CF; color: #212121; }
            .fav-cat-adventure { background: #FFD3B6; color: #212121; }
            .fav-cat-food { background: #FF6B6B; color: white; }
            .fav-cat-culture { background: #8FD3F4; color: #212121; }
            .fav-cat-other { background: #3aa26b; color: white; }

            /* ─── DESCRIPTION ────────────────────────────────── */
            .fav-popup-description {
                margin: 8px 0;
                font-size: 12px;
                color: #555;
                line-height: 1.4;
                max-height: 70px;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* ─── STATS ──────────────────────────────────────── */
            .fav-popup-stats {
                margin: 8px 0;
                display: flex;
                gap: 12px;
                font-size: 12px;
            }

            .fav-popup-likes {
                color: #ff6b9d;
                font-weight: 600;
            }

            /* ─── BUTTON ─────────────────────────────────────── */
            .fav-popup-btn {
                display: inline-block;
                width: 100%;
                margin-top: 12px;
                padding: 12px;
                background: linear-gradient(135deg, #3aa26b 0%, #2d8659 100%);
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 700;
                font-family: 'Bungee', cursive;
                font-size: 12px;
                border: 2px solid #212121;
                text-align: center;
                box-shadow: 4px 4px 0px rgba(33, 33, 33, 0.15);
                transition: all 0.2s ease;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .fav-popup-btn:hover {
                background: linear-gradient(135deg, #2d8659 0%, #1f5a3d 100%);
                transform: translateY(-2px);
                box-shadow: 6px 6px 0px rgba(33, 33, 33, 0.25);
            }

            .fav-popup-btn:active {
                transform: translateY(0);
                box-shadow: 2px 2px 0px rgba(33, 33, 33, 0.15);
            }

            /* ══════════════════════════════════════════════════════
               RESPONSIVE
               ══════════════════════════════════════════════════════ */
            
            @media (max-width: 768px) {
                .fav-spot-popup-custom .leaflet-popup-content {
                    width: 250px;
                }

                .fav-popup-content {
                    padding: 12px;
                }

                .fav-popup-title {
                    font-size: 14px;
                }

                .fav-popup-image {
                    height: 140px;
                }
            }
        `;
        document.head.appendChild(style);
    }
});