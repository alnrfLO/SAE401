// public/js/discover.js

document.addEventListener("DOMContentLoaded", () => {
    // 1. Initialiser la carte avec CartoDB Positron (clair et lisse, parfait pour le style neo-brutalisme beige)
    const map = L.map('discover-map', {
        zoomControl: true, // We will naturally use default zoom but we styled it in CSS
    }).setView([20, 10], 2);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    let allSpots = window.FAV_SPOTS_DATA || [];
    let currentFilter = 'all'; // valeu par défaut du dataset (all, france, albania, vietnam)
    let currentPage = 1;
    const itemsPerPage = 6; // Grille de 3 par ligne x 2 lignes = 6 cartes par page
    let filteredSpots = [...allSpots];

    let markersLayer = L.layerGroup().addTo(map);

    const gridEl = document.getElementById('discover-grid');
    const paginationEl = document.getElementById('discover-pagination');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const pageInd = document.getElementById('page-indicator');
    const emptyState = document.getElementById('discover-empty-state');

    function applyFilter(filter) {
        currentFilter = filter;
        currentPage = 1;

        if (filter === 'all') {
            filteredSpots = [...allSpots];
        } else {
            // Filtrer selon la location, categorie ou le titre
            filteredSpots = allSpots.filter(s => {
                const loc = (s.location || '').toLowerCase();
                const cat = (s.category || '').toLowerCase();
                const title = (s.title || '').toLowerCase();
                return loc.includes(filter) || cat.includes(filter) || title.includes(filter);
            });
        }

        renderMap();
        renderGrid();
    }

    function renderMap() {
        markersLayer.clearLayers();
        const bounds = L.latLngBounds();
        let hasValidCoords = false;

        filteredSpots.forEach(s => {
            const lat = parseFloat(s.latitude);
            const lng = parseFloat(s.longitude);

            if (!isNaN(lat) && !isNaN(lng)) {
                hasValidCoords = true;
                const point = [lat, lng];

                // Identifier le pays pour coloriser le marqueur
                let countryClass = "other";
                const locLower = (s.location || "").toLowerCase();
                if (locLower.includes("france")) countryClass = "france";
                else if (locLower.includes("albania")) countryClass = "albania";
                else if (locLower.includes("vietnam")) countryClass = "vietnam";

                const iconHtml = `<div class="map-marker" data-country="${countryClass}"></div>`;
                const customIcon = L.divIcon({
                    html: iconHtml,
                    className: '', // Ne pas hériter du style Leaflet par défaut
                    iconSize: [20, 20],
                    iconAnchor: [10, 10], 
                    popupAnchor: [0, -10]
                });

                const marker = L.marker(point, { icon: customIcon });

                const imgSrc = s.image ? s.image : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23212121%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%23fff%22 font-family=%22sans-serif%22 font-size=%2224%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3E' + (window.FAV_LANG_DATA?.no_image || 'NO IMAGE') + '%3C/text%3E%3C/svg%3E';
                const infoHtml = `
                    <div class="custom-popup">
                        <img src="${imgSrc}" style="width:100%; height:80px; object-fit:cover; border-radius:4px; margin-bottom:5px; border:2px solid #212121;">
                        <h4>${s.title}</h4>
                        <p>📍 ${s.location || (window.FAV_LANG_DATA?.unknown || 'Unknown')}</p>
                        <a href="?page=spot&id=${s.id}" style="display:inline-block; margin-top:5px; padding:2px 6px; border:2px solid #212121; background:#fff; border-radius:4px; color:#212121; text-decoration:none; font-family:'Bungee', cursive; font-size:10px;">${window.FAV_LANG_DATA?.view_details || 'View details'}</a>
                    </div>
                `;
                marker.bindPopup(infoHtml);
                marker.addTo(markersLayer);
                
                bounds.extend(point);
            }
        });

        if (hasValidCoords && currentFilter !== 'all') {
            // Si on filtre sur un pays spécifique, recentrer fermement la carte
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 6 });
        } else if (currentFilter === 'all') {
            // Vue mondiale par défaut
            map.flyTo([30, 10], 2, { duration: 1 });
        }
    }

    function renderGrid() {
        gridEl.innerHTML = '';

        if (filteredSpots.length === 0) {
            emptyState.style.display = 'block';
            paginationEl.style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';

        const totalPages = Math.ceil(filteredSpots.length / itemsPerPage);
        
        // Boundaries de la page actuelle
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageSpots = filteredSpots.slice(start, end);

        pageSpots.forEach(s => {
            // Déduire le badge rouge/bleu/vert
            let countryClass = "OTHER";
            const locLower = (s.location || "").toLowerCase();
            const words = locLower.split(",");
            let extracted = words[words.length-1].trim().toUpperCase() || "WORLD";

            if (locLower.includes("france")) { countryClass = "FRANCE"; extracted = "FRANCE"; }
            else if (locLower.includes("albania")) { countryClass = "ALBANIA"; extracted = "ALBANIA"; }
            else if (locLower.includes("vietnam")) { countryClass = "VIETNAM"; extracted = "VIETNAM"; }
            
            let cssClass = countryClass.toLowerCase();
            if (!['france', 'albania', 'vietnam'].includes(cssClass)) cssClass = "other"; 

            const likes = s.likes_count || 0;
            // Fausse logique de note (car inexistant dans le modèle original ?)
            const rating = Math.min((4.2 + (likes * 0.1)), 5.0).toFixed(1);
            
            // Si le nombre de followers ou de comments était dispo on l'utiliserait, là on donne une estimation fictive basée sur likes
            const travelers = Math.max(1, likes * 3 + Math.floor(Math.random() * 10));

            const imgSrc = s.image ? s.image : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23212121%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%23fff%22 font-family=%22sans-serif%22 font-size=%2224%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3E' + (window.FAV_LANG_DATA?.no_image || 'NO IMAGE') + '%3C/text%3E%3C/svg%3E';

            const card = document.createElement('a');
            card.href = `?page=spot&id=${s.id}`;
            card.className = "discover-card";
            card.innerHTML = `
                <div class="discover-card-img-wrap">
                    <img src="${imgSrc}" loading="lazy" alt="Spot Image">
                </div>
                <div class="discover-card-badges">
                    <span class="card-country-badge badge-${cssClass}">${extracted.substring(0, 12)}</span>
                    <div class="card-rating">
                        <span>★</span> ${rating}
                    </div>
                </div>
                <h3 class="discover-card-title">${s.title}</h3>
                <p class="discover-card-desc">${s.description || (window.FAV_LANG_DATA?.no_description || 'No description available for this place.')}</p>
                <div class="discover-card-footer">
                    <svg width="12" height="12" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    &nbsp;${travelers} ${window.FAV_LANG_DATA?.travelers || 'travelers'}
                </div>
            `;
            gridEl.appendChild(card);
        });

        // Pagination buttons state
        if (totalPages > 1) {
            paginationEl.style.display = 'flex';
            pageInd.textContent = `${window.FAV_LANG_DATA?.page || 'PAGE'} ${currentPage} ${window.FAV_LANG_DATA?.of || 'OF'} ${totalPages}`;
            btnPrev.disabled = currentPage === 1;
            btnNext.disabled = currentPage === totalPages;
        } else {
            paginationEl.style.display = 'none';
        }
    }

    // Event listeners for pagination
    if(btnPrev) {
        btnPrev.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderGrid();
                const section = document.querySelector('.discover-spots-section');
                const offset = section.getBoundingClientRect().top + window.scrollY - 100;
                window.scrollTo({ top: offset, behavior: 'smooth' });
            }
        });
    }

    if(btnNext) {
        btnNext.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredSpots.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderGrid();
                const section = document.querySelector('.discover-spots-section');
                const offset = section.getBoundingClientRect().top + window.scrollY - 100;
                window.scrollTo({ top: offset, behavior: 'smooth' });
            }
        });
    }

    // Listeners filtres
    const filterBtns = document.querySelectorAll('.discover-filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            filterBtns.forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');

            const filterVal = e.target.getAttribute('data-filter');
            applyFilter(filterVal);
        });
    });

    // Initialisation
    renderMap();
    renderGrid();
});
