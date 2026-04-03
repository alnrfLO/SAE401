// public/js/discover.js
// ⚠️ ATTENTION: La carte est gérée par discover-map.js
// Ce script gère UNIQUEMENT la grille des spots et les filtres

document.addEventListener("DOMContentLoaded", () => {
    console.log('📋 Initialisation de la grille Discover...');

    let allSpots = window.FAV_SPOTS_DATA || [];
    let currentFilter = 'all';
    let currentPage = 1;
    const itemsPerPage = 6;
    let filteredSpots = [...allSpots];

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
            filteredSpots = allSpots.filter(s => {
                const loc = (s.location || '').toLowerCase();
                const cat = (s.category || '').toLowerCase();
                const title = (s.title || '').toLowerCase();
                return loc.includes(filter) || cat.includes(filter) || title.includes(filter);
            });
        }

        renderGrid();
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
        
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageSpots = filteredSpots.slice(start, end);

        pageSpots.forEach(s => {
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
            const rating = Math.min((4.2 + (likes * 0.1)), 5.0).toFixed(1);
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
                if (section) {
                    const offset = section.getBoundingClientRect().top + window.scrollY - 100;
                    window.scrollTo({ top: offset, behavior: 'smooth' });
                }
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
                if (section) {
                    const offset = section.getBoundingClientRect().top + window.scrollY - 100;
                    window.scrollTo({ top: offset, behavior: 'smooth' });
                }
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

    // Initialisation - afficher la grille
    renderGrid();
    console.log('✅ Grille Discover initialisée avec', allSpots.length, 'spots');
});