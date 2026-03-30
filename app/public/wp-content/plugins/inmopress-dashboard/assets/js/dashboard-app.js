document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.ip-sidebar-nav li');
    const container = document.getElementById('ip-view-container');

    // Inicializar la vista por defecto
    loadView('dashboard');

    navItems.forEach(item => {
        item.addEventListener('click', function() {
            // Update active class
            navItems.forEach(n => n.classList.remove('active'));
            this.classList.add('active');

            // Render view
            const view = this.getAttribute('data-view');
            loadView(view);
        });
    });

    function loadView(view) {
        if (view === 'dashboard') {
            renderDashboard();
        } else if (view === 'properties') {
            renderProperties();
        } else if (view === 'clients') {
            renderClients();
        } else {
            container.innerHTML = `<h2 style="margin-top: 0; font-size: 24px; color: #111827;">Vista: ${view} en desarrollo...</h2>`;
        }
    }

    function renderDashboard() {
        container.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <div>
                    <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Panel General</h2>
                    <p style="color: #6b7280; margin-top: 8px;">Bienvenido al nuevo entorno del Dashboard InmoPress.</p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;">
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: transform 0.2s;">
                    <div style="color: #6b7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Propiedades Activas</div>
                    <div id="ip-stat-properties" style="font-size: 36px; font-weight: 800; color: #1e3a8a; margin-top: 8px; font-family: 'Inter', sans-serif;">
                        <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span>
                    </div>
                </div>
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="color: #6b7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Ventas del Mes</div>
                    <div style="font-size: 36px; font-weight: 800; color: #10b981; margin-top: 8px; font-family: 'Inter', sans-serif;">3</div>
                </div>
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="color: #6b7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Nuevos Clientes</div>
                    <div style="font-size: 36px; font-weight: 800; color: #f59e0b; margin-top: 8px; font-family: 'Inter', sans-serif;">12</div>
                </div>
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="color: #6b7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Facturación</div>
                    <div style="font-size: 36px; font-weight: 800; color: #8b5cf6; margin-top: 8px; font-family: 'Inter', sans-serif;">€45.2K</div>
                </div>
            </div>
        `;

        // Obtener el total de inmuebles usando la REST API de WordPress
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_property?per_page=1', {
            method: 'GET',
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(response => {
            const count = response.headers.get('X-WP-Total') || 0;
            document.getElementById('ip-stat-properties').innerText = count;
        })
        .catch(error => {
            console.error(error);
            document.getElementById('ip-stat-properties').innerText = '0';
        });
    }

    function renderProperties() {
        container.innerHTML = `
            <div id="ip-properties-list-view">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Inmuebles</h2>
                <button onclick="window.openAddPropertyModal()" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                    <span class="dashicons dashicons-plus-alt2"></span> Añadir Inmueble
                </button>
            </div>
            
            <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; display: flex; flex-wrap: wrap; gap: 16px; align-items: center;">
                    <div style="position: relative;">
                        <span class="dashicons dashicons-search" style="position: absolute; left: 10px; top: 10px; color: #9ca3af;"></span>
                        <input type="text" id="ip-filter-search" oninput="window.applyPropertyFilters()" placeholder="Buscar por título o ubicación..." style="padding: 10px 14px 10px 36px; border: 1px solid #d1d5db; border-radius: 6px; width: 300px; font-size: 14px;">
                    </div>
                    
                    <select id="ip-filter-status" onchange="window.applyPropertyFilters()" style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; cursor: pointer;">
                        <option value="">Todos los estados</option>
                    </select>

                    <select id="ip-filter-type" onchange="window.applyPropertyFilters()" style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; cursor: pointer;">
                        <option value="">Todos los tipos</option>
                    </select>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 13px; color: #6b7280; font-weight: 500;">Precio (€):</span>
                        <input type="number" id="ip-filter-price-min" oninput="window.applyPropertyFilters()" placeholder="Mínimo" style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; width: 100px; font-size: 14px;">
                        <span>-</span>
                        <input type="number" id="ip-filter-price-max" oninput="window.applyPropertyFilters()" placeholder="Máximo" style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; width: 100px; font-size: 14px;">
                    </div>
                </div>
                <!-- Fila de filtros avanzados -->
                <div style="padding: 12px 24px; border-bottom: 1px solid #e5e7eb; background: #ffffff; display: flex; flex-wrap: wrap; gap: 16px; align-items: center;">
                    <select id="ip-filter-city" onchange="window.applyPropertyFilters()" style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; cursor: pointer; min-width: 180px;">
                        <option value="">Todas las ubicaciones</option>
                    </select>

                    <select id="ip-filter-rooms" onchange="window.applyPropertyFilters()" style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; cursor: pointer;">
                        <option value="0">Habitaciones (Cualquiera)</option>
                        <option value="1">1+ Habitación</option>
                        <option value="2">2+ Habitaciones</option>
                        <option value="3">3+ Habitaciones</option>
                        <option value="4">4+ Habitaciones</option>
                    </select>

                    <select id="ip-filter-baths" onchange="window.applyPropertyFilters()" style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; cursor: pointer;">
                        <option value="0">Baños (Cualquiera)</option>
                        <option value="1">1+ Baño</option>
                        <option value="2">2+ Baños</option>
                        <option value="3">3+ Baños</option>
                    </select>
                </div>
                <div id="ip-properties-grid" style="padding: 24px; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; background: #f3f4f6;">
                    <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #6b7280; font-size: 14px;">
                        <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 24px; width: 24px; height: 24px;"></span><br>
                        Cargando propiedades...
                    </div>
                </div>
                </div>
            </div>
            <div id="ip-property-detail-view" style="display: none;"></div>
        `;

        fetchProperties();
    }

    let allPropertiesData = [];

    function fetchProperties() {
        // Pedimos hasta 100 propiedades para filtrar cómodamente en el frontal
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_property?_embed&per_page=100', {
            method: 'GET',
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(response => response.json())
        .then(posts => {
            allPropertiesData = posts || [];
            
            // Poblar los filtros de tipos, estados y ciudades según los datos obtenidos
            const typeSelect = document.getElementById('ip-filter-type');
            const statusSelect = document.getElementById('ip-filter-status');
            const citySelect = document.getElementById('ip-filter-city');
            
            if (allPropertiesData.length > 0) {
                let types = new Set();
                let statuses = new Set();
                let cities = new Set();
                
                allPropertiesData.forEach(post => {
                    if (post._embedded && post._embedded['wp:term']) {
                        post._embedded['wp:term'].forEach(taxonomy => {
                            taxonomy.forEach(term => {
                                if (term.taxonomy === 'impress_property_type') types.add(term.name);
                                if (term.taxonomy === 'impress_status') statuses.add(term.name);
                                if (term.taxonomy === 'impress_city') cities.add(term.name);
                            });
                        });
                    }
                });
                
                if (typeSelect) {
                    let optionsHTML = '<option value="">Todos los tipos</option>';
                    [...types].sort().forEach(type => {
                        optionsHTML += `<option value="${type}">${type}</option>`;
                    });
                    typeSelect.innerHTML = optionsHTML;
                }
                
                if (statusSelect) {
                    let statusHTML = '<option value="">Todos los estados</option>';
                    [...statuses].sort().forEach(status => {
                        statusHTML += `<option value="${status}">${status}</option>`;
                    });
                    statusSelect.innerHTML = statusHTML;
                }
                
                if (citySelect) {
                    let cityHTML = '<option value="">Todas las ubicaciones</option>';
                    [...cities].sort().forEach(city => {
                        cityHTML += `<option value="${city}">${city}</option>`;
                    });
                    citySelect.innerHTML = cityHTML;
                }
            }

            // Aplicar filtros iniciales (que en principio no ocultan nada) y renderizar
            window.applyPropertyFilters();
        })
        .catch(err => {
            console.error(err);
            document.getElementById('ip-properties-grid').innerHTML = '<div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #ef4444; font-size: 14px;">Error al cargar las propiedades.</div>';
        });
    }

    window.applyPropertyFilters = function() {
        const searchInput = document.getElementById('ip-filter-search');
        if(!searchInput) return;

        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = document.getElementById('ip-filter-status').value;
        const typeTerm = document.getElementById('ip-filter-type').value;
        const priceMin = parseFloat(document.getElementById('ip-filter-price-min').value);
        const priceMax = parseFloat(document.getElementById('ip-filter-price-max').value);
        
        const cityTerm = document.getElementById('ip-filter-city') ? document.getElementById('ip-filter-city').value : '';
        const roomsMin = document.getElementById('ip-filter-rooms') ? parseInt(document.getElementById('ip-filter-rooms').value) : 0;
        const bathsMin = document.getElementById('ip-filter-baths') ? parseInt(document.getElementById('ip-filter-baths').value) : 0;

        const filteredPosts = allPropertiesData.filter(post => {
            let title = (post.title.rendered || '').toLowerCase();
            let ipMeta = post.ip_meta || {};
            
            let typeName = '';
            let cityName = '';
            let statusName = '';

            if (post._embedded && post._embedded['wp:term']) {
                post._embedded['wp:term'].forEach(taxonomy => {
                    taxonomy.forEach(term => {
                        if (term.taxonomy === 'impress_property_type') typeName = term.name;
                        if (term.taxonomy === 'impress_city') cityName = term.name;
                        if (term.taxonomy === 'impress_status') statusName = term.name;
                    });
                });
            }

            let rawPrice = parseFloat(ipMeta.precio || ipMeta.precio_venta) || 0;
            let roomsCurrent = parseInt(ipMeta.habitaciones || ipMeta.dormitorios) || 0;
            let bathsCurrent = parseInt(ipMeta.banos || ipMeta.baños) || 0;

            // Filtro 1: Búsqueda de Texto
            if (searchTerm && !title.includes(searchTerm) && !cityName.toLowerCase().includes(searchTerm)) {
                return false;
            }

            // Filtro 2: Estado (Dinámico por el nombre devuelto)
            if (statusTerm && statusName !== statusTerm) return false;

            // Filtro 3: Tipo de Inmueble
            if (typeTerm && typeName !== typeTerm) return false;

            // Filtro 4: Rango de Precios
            if (!isNaN(priceMin) && priceMin > 0 && rawPrice < priceMin) return false;
            if (!isNaN(priceMax) && priceMax > 0 && rawPrice > priceMax) return false;

            // Filtro 5: Ubicación / Ciudad dinámica
            if (cityTerm && cityName !== cityTerm) return false;

            // Filtro 6: Número de habitaciones
            if (roomsMin > 0 && roomsCurrent < roomsMin) return false;

            // Filtro 7: Número de baños
            if (bathsMin > 0 && bathsCurrent < bathsMin) return false;

            return true;
        });

        renderPropertyCards(filteredPosts);
    };

    function renderPropertyCards(posts) {
        const grid = document.getElementById('ip-properties-grid');
        grid.innerHTML = '';
        
        if (!posts || posts.length === 0) {
            grid.innerHTML = '<div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #6b7280; font-size: 15px; background: white; border-radius: 12px; border: 1px solid #e5e7eb;">No hay inmuebles que coincidan con los filtros de búsqueda.</div>';
            return;
        }

        let cardsHTML = '';
        posts.forEach(post => {
            let title = post.title.rendered || '(Sin título)';
            let ipMeta = post.ip_meta || {};
            
            // Extraer Imagen Destacada
            let imageUrl = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="%23e5e7eb"/><text x="50%" y="50%" font-family="sans-serif" font-size="20" fill="%239ca3af" text-anchor="middle" dominant-baseline="middle">Sin Imagen</text></svg>';
            if (post._embedded && post._embedded['wp:featuredmedia'] && post._embedded['wp:featuredmedia'][0]) {
                let media = post._embedded['wp:featuredmedia'][0];
                if (media.media_details && media.media_details.sizes && media.media_details.sizes.medium_large) {
                    imageUrl = media.media_details.sizes.medium_large.source_url;
                } else if (media.source_url) {
                    imageUrl = media.source_url;
                }
            }

            // Extraer datos usando ip_meta o la base de datos de fallback
            let rawPrice = ipMeta.precio || ipMeta.precio_venta || null;
            let formattedPrice = 'A consultar';
            if (rawPrice) {
                formattedPrice = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(rawPrice);
            }

            let rooms = ipMeta.habitaciones || ipMeta.dormitorios || '-';
            let baths = ipMeta.banos || ipMeta.baños || '-';
            let size = ipMeta.superficie_construida || ipMeta.superficie || '-';

            // Tipo de Inmueble y Ubicación (desde taxonomías embebidas)
            let typeName = 'Inmueble';
            let cityName = 'Ubicación no especificada';
            let statusName = 'Disponible';
            let isSold = false;

            if (post._embedded && post._embedded['wp:term']) {
                post._embedded['wp:term'].forEach(taxonomy => {
                    taxonomy.forEach(term => {
                        if (term.taxonomy === 'impress_property_type') typeName = term.name;
                        if (term.taxonomy === 'impress_city') cityName = term.name;
                        if (term.taxonomy === 'impress_status' || term.taxonomy === 'impress_condition') {
                            if (term.taxonomy === 'impress_status') statusName = term.name;
                            if (term.slug.includes('vendid') || term.slug.includes('sold')) {
                                isSold = true;
                            }
                        }
                    });
                });
            }

            let statusColor = isSold ? '#ef4444' : '#10b981';
            let statusBadge = `<span style="background: ${statusColor}; color: white; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">${statusName}</span>`;

            cardsHTML += `
                <div onclick="window.viewProperty(${post.id})" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; display: flex; flex-direction: column;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03)'">
                    
                    <!-- Imagen -->
                    <div style="position: relative; height: 220px; background: #e5e7eb;">
                        <img src="${imageUrl}" alt="${title}" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; top: 12px; left: 12px;">
                            ${statusBadge}
                        </div>
                        <div style="position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.95); padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #374151; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            ${typeName}
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div style="padding: 20px; display: flex; flex-direction: column; flex-grow: 1;">
                        <div style="color: #6b7280; font-size: 13px; font-weight: 500; margin-bottom: 8px; display: flex; align-items: center; gap: 4px;">
                            <span class="dashicons dashicons-location-alt" style="font-size: 16px; width: 16px; height: 16px;"></span> ${cityName}
                        </div>
                        <h3 style="margin: 0 0 12px 0; font-size: 17px; font-weight: 700; color: #111827; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${title}</h3>
                        
                        <div style="font-size: 22px; font-weight: 800; color: #1e3a8a; margin-bottom: 20px;">
                            ${formattedPrice}
                        </div>

                        <div style="margin-top: auto; padding-top: 16px; border-top: 1px solid #f3f4f6; display: flex; justify-content: space-between; color: #6b7280; font-size: 14px; font-weight: 500;">
                            <div style="display: flex; align-items: center; gap: 6px;" title="Habitaciones">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h18"/><path d="M5 14v5"/><path d="M19 14v5"/><path d="M3 10V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4"/><path d="M7 10h10v4H7z"/></svg>
                                ${rooms}
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;" title="Baños">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><line x1="10" y1="5" x2="8" y2="7"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="7" y1="19" x2="7" y2="21"/><line x1="17" y1="19" x2="17" y2="21"/></svg>
                                ${baths}
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;" title="Superficie construida">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                ${size} m²
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        grid.innerHTML = cardsHTML;
    }

    window.viewProperty = function(postId) {
        const post = allPropertiesData.find(p => p.id === postId);
        if (!post) return;

        const listView = document.getElementById('ip-properties-list-view');
        const detailView = document.getElementById('ip-property-detail-view');
        
        listView.style.display = 'none';
        detailView.style.display = 'block';

        let ipMeta = post.ip_meta || {};
        let title = post.title.rendered || '(Sin título)';
        let description = post.content.rendered || 'Sin descripción disponible.';
        
        let typeName = 'Inmueble';
        let cityName = 'Ubicación no especificada';
        let statusName = 'Disponible';
        if (post._embedded && post._embedded['wp:term']) {
            post._embedded['wp:term'].forEach(taxonomy => {
                taxonomy.forEach(term => {
                    if (term.taxonomy === 'impress_property_type') typeName = term.name;
                    if (term.taxonomy === 'impress_city') cityName = term.name;
                    if (term.taxonomy === 'impress_status') statusName = term.name;
                });
            });
        }

        let price = ipMeta.precio || ipMeta.precio_venta || 0;
        let formattedPrice = price ? new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(price) : 'A consultar';
        let rooms = ipMeta.habitaciones || ipMeta.dormitorios || '-';
        let baths = ipMeta.banos || ipMeta.baños || '-';
        let size = ipMeta.superficie_construida || ipMeta.superficie || '-';

        // Preparar galería y miniaturas
        let mainImageUrl = '';
        if (post._embedded && post._embedded['wp:featuredmedia'] && post._embedded['wp:featuredmedia'][0]) {
            mainImageUrl = post._embedded['wp:featuredmedia'][0].source_url;
        } else if (ipMeta.gallery && ipMeta.gallery.length > 0) {
            mainImageUrl = ipMeta.gallery[0].url;
        }

        let thumbnailsHTML = '';
        if (ipMeta.gallery && ipMeta.gallery.length > 0) {
            window.currentGalleryData = ipMeta.gallery;
            window.currentGalleryIndex = 0;
            
            thumbnailsHTML = ipMeta.gallery.map((img, idx) => `
                <div style="width: 80px; height: 60px; border-radius: 6px; overflow: hidden; cursor: pointer; border: 2px solid ${idx === 0 ? '#1e3a8a' : 'transparent'}; transition: border-color 0.2s;" 
                     onclick="window.setHeroImage(${idx});" 
                     data-idx="${idx}" class="ip-thumb">
                    <img src="${img.url}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            `).join('');
        }

        detailView.innerHTML = `
            <!-- Header de Navegación -->
            <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button onclick="window.closePropertyDetail()" style="background: white; border: 1px solid #d1d5db; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <span class="dashicons dashicons-arrow-left-alt2"></span> Volver
                    </button>
                    <div>
                        <h2 style="margin: 0; font-size: 28px; font-weight: 800; color: #111827;">${title}</h2>
                        <div style="color: #6b7280; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <span class="dashicons dashicons-location" style="font-size: 16px; width: 16px; height: 16px;"></span> ${cityName}
                        </div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button onclick="window.openEditPropertyModal(${post.id})" style="background: #1e3a8a; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(30,58,138,0.2);">
                        <span class="dashicons dashicons-edit"></span> Editar Inmueble
                    </button>
                </div>
            </div>

            <!-- Sección Hero de Imagen -->
            <div style="position: relative; border-radius: 20px; overflow: hidden; background: #111827; height: 600px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin-bottom: 24px; display: flex; align-items: center; justify-content: center;">
                <!-- Fondo difuminado para rellenar huecos -->
                <div id="ip-hero-bg" style="position: absolute; inset: 0; background: url('${mainImageUrl}') center/cover no-repeat; filter: blur(20px) brightness(0.4); transform: scale(1.1); transition: background-image 0.3s ease;"></div>
                
                <img id="ip-main-hero-img" src="${mainImageUrl}" style="position: relative; max-width: 100%; max-height: 100%; object-fit: contain; z-index: 1; cursor: zoom-in; transition: opacity 0.3s ease;" onclick="window.openImageFull(this.src)">
                
                <!-- Flechas Nav -->
                ${ipMeta.gallery && ipMeta.gallery.length > 1 ? `
                    <button onclick="window.prevGalleryImage(event)" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); z-index: 10; background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); border: none; width: 50px; height: 50px; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                        <span class="dashicons dashicons-arrow-left-alt2" style="font-size: 24px; width: 24px; height: 24px;"></span>
                    </button>
                    <button onclick="window.nextGalleryImage(event)" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); z-index: 10; background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); border: none; width: 50px; height: 50px; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                        <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 24px; width: 24px; height: 24px;"></span>
                    </button>
                ` : ''}

                <!-- Overlay de miniaturas -->
                <div style="position: absolute; bottom: 20px; left: 20px; right: 20px; display: flex; gap: 10px; background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); padding: 10px; border-radius: 12px; width: fit-content; max-width: 90%; z-index: 10;">
                    ${thumbnailsHTML}
                </div>

                <!-- Tag de Estado -->
                <div style="position: absolute; top: 20px; left: 20px; background: #10b981; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    ${statusName}
                </div>

                <!-- Precio flotante -->
                <div style="position: absolute; top: 20px; right: 20px; background: white; padding: 12px 24px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); text-align: center;">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #6b7280; letter-spacing: 0.05em; margin-bottom: 2px;">Precio</div>
                    <div style="font-size: 24px; font-weight: 800; color: #1e3a8a;">${formattedPrice}</div>
                </div>
            </div>

            <!-- Stats Grid y Información -->
            <div style="background: white; border-radius: 20px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <!-- Grid de características principales -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <div style="padding: 24px; text-align: center; border-right: 1px solid #e5e7eb;">
                        <span class="dashicons dashicons-admin-home" style="color: #1e3a8a; font-size: 24px; margin-bottom: 8px; width: 100%;"></span>
                        <div style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Tipo</div>
                        <div style="font-size: 16px; font-weight: 700; color: #111827; margin-top: 4px;">${typeName}</div>
                    </div>
                    <div style="padding: 24px; text-align: center; border-right: 1px solid #e5e7eb;">
                        <span class="dashicons dashicons-layout" style="color: #1e3a8a; font-size: 24px; margin-bottom: 8px; width: 100%;"></span>
                        <div style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Superficie</div>
                        <div style="font-size: 16px; font-weight: 700; color: #111827; margin-top: 4px;">${size} m²</div>
                    </div>
                    <div style="padding: 24px; text-align: center; border-right: 1px solid #e5e7eb;">
                        <span class="dashicons dashicons-welcome-learn-more" style="color: #1e3a8a; font-size: 24px; margin-bottom: 8px; width: 100%;"></span>
                        <div style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Habitaciones</div>
                        <div style="font-size: 16px; font-weight: 700; color: #111827; margin-top: 4px;">${rooms}</div>
                    </div>
                    <div style="padding: 24px; text-align: center; border-right: 1px solid #e5e7eb;">
                        <span class="dashicons dashicons-filter" style="color: #1e3a8a; font-size: 24px; margin-bottom: 8px; width: 100%;"></span>
                        <div style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Baños</div>
                        <div style="font-size: 16px; font-weight: 700; color: #111827; margin-top: 4px;">${baths}</div>
                    </div>
                    <div style="padding: 24px; text-align: center;">
                        <span class="dashicons dashicons-calendar-alt" style="color: #1e3a8a; font-size: 24px; margin-bottom: 8px; width: 100%;"></span>
                        <div style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Añadido</div>
                        <div style="font-size: 16px; font-weight: 700; color: #111827; margin-top: 4px;">${new Date(post.date).toLocaleDateString()}</div>
                    </div>
                </div>

                <!-- Descripción Ampliada -->
                <div style="padding: 40px;">
                    <h3 style="margin: 0 0 20px 0; font-size: 20px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 4px; height: 24px; background: #1e3a8a; border-radius: 2px;"></span>
                        Descripción del inmueble
                    </h3>
                    <div style="line-height: 1.8; color: #4b5563; font-size: 16px; max-width: 900px;">
                        ${description}
                    </div>
                </div>
            </div>
        `;
        
        // Scroll to top
        window.scrollTo(0, 0);
    }

    window.setHeroImage = function(index) {
        if (!window.currentGalleryData || !window.currentGalleryData[index]) return;
        window.currentGalleryIndex = index;
        const img = window.currentGalleryData[index];
        const mainImg = document.getElementById('ip-main-hero-img');
        const bg = document.getElementById('ip-hero-bg');
        
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.src = img.url;
            bg.style.backgroundImage = `url('${img.url}')`;
            mainImg.style.opacity = '1';
        }, 150);

        // Update thumbnails
        document.querySelectorAll('.ip-thumb').forEach(thumb => {
            thumb.style.borderColor = parseInt(thumb.dataset.idx) === index ? '#1e3a8a' : 'transparent';
        });
    }

    window.nextGalleryImage = function(e) {
        if(e) e.stopPropagation();
        let next = window.currentGalleryIndex + 1;
        if (next >= window.currentGalleryData.length) next = 0;
        window.setHeroImage(next);
    }

    window.prevGalleryImage = function(e) {
        if(e) e.stopPropagation();
        let prev = window.currentGalleryIndex - 1;
        if (prev < 0) prev = window.currentGalleryData.length - 1;
        window.setHeroImage(prev);
    }

    window.closePropertyDetail = function() {
        document.getElementById('ip-properties-list-view').style.display = 'block';
        const detailView = document.getElementById('ip-property-detail-view');
        detailView.style.display = 'none';
        detailView.innerHTML = '';
    }

    window.openImageFull = function(url) {
        // Implementación simple de lightbox
        const lb = document.createElement('div');
        lb.style.cssText = 'position:fixed; inset:0; background:rgba(0,0,0,0.9); z-index:200000; display:flex; align-items:center; justify-content:center; cursor:pointer;';
        lb.innerHTML = `<img src="${url}" style="max-width:90%; max-height:90%; border-radius:8px; box-shadow:0 0 50px rgba(0,0,0,0.5);">`;
        lb.onclick = () => lb.remove();
        document.body.appendChild(lb);
    }

    // Lógica para añadir inmuebles
    window.openAddPropertyModal = function() {
        if (document.getElementById('ip-add-property-modal')) return;

        // Collect dropdown options from `allPropertiesData`
        let types = new Set();
        let statuses = new Set();
        let cities = new Set();
        
        allPropertiesData.forEach(post => {
            if (post._embedded && post._embedded['wp:term']) {
                post._embedded['wp:term'].forEach(taxonomy => {
                    taxonomy.forEach(term => {
                        if (term.taxonomy === 'impress_property_type') types.add(term.name);
                        if (term.taxonomy === 'impress_status') statuses.add(term.name);
                        if (term.taxonomy === 'impress_city') cities.add(term.name);
                    });
                });
            }
        });

        let typeOpts = [...types].sort().map(t => `<option value="${t}">${t}</option>`).join('');
        let statusOpts = [...statuses].sort().map(s => `<option value="${s}">${s}</option>`).join('');
        let cityOpts = [...cities].sort().map(c => `<option value="${c}">${c}</option>`).join('');

        const modalHTML = `
            <div id="ip-add-property-modal" style="position: fixed; inset: 0; background: rgba(17, 24, 39, 0.7); z-index: 100000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                <div style="background: white; border-radius: 12px; width: 600px; max-width: 90vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 10;">
                        <h2 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Añadir Nuevo Inmueble</h2>
                        <button onclick="document.getElementById('ip-add-property-modal').remove()" style="background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #6b7280; padding: 0;">&times;</button>
                    </div>
                    <div style="padding: 24px;">
                        <form id="ip-add-property-form" onsubmit="window.submitAddPropertyForm(event)">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div style="grid-column: 1 / -1;">
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Título del Inmueble *</label>
                                    <input type="text" name="title" required placeholder="Ej. Precioso piso en el centro" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>
                                
                                <div style="grid-column: 1 / -1;">
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Descripción</label>
                                    <textarea name="description" rows="3" placeholder="Información detallada..." style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;"></textarea>
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Precio (€) *</label>
                                    <input type="number" name="price" required min="0" placeholder="Ej. 150000" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Ubicación / Ciudad</label>
                                    <select name="city" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px; background: white;">
                                        <option value="">Seleccionar ciudad...</option>
                                        ${cityOpts}
                                    </select>
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Habitaciones</label>
                                    <input type="number" name="rooms" min="0" value="1" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Baños</label>
                                    <input type="number" name="baths" min="0" value="1" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>
                                
                                <div style="grid-column: 1 / -1;">
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Superficie construida (m²)</label>
                                    <input type="number" name="size" min="0" value="100" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Tipo de Inmueble</label>
                                    <select name="type" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px; background: white;">
                                        <option value="">Selecciona tipo...</option>
                                        ${typeOpts}
                                    </select>
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Estado</label>
                                    <select name="status" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px; background: white;">
                                        <option value="">Selecciona estado...</option>
                                        ${statusOpts}
                                    </select>
                                </div>

                                <div style="grid-column: 1 / -1; margin-top: 8px;">
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Fotos del Inmueble (Selecciona una o varias)</label>
                                    <input type="file" id="ip-images-input" name="images[]" accept="image/*" multiple style="width: 100%; border: 1px dashed #d1d5db; border-radius: 6px; padding: 16px 10px; font-size: 14px; background: #f9fafb; cursor: pointer;" onchange="window.handleImagePreview(event, 'ip-images-preview')">
                                    <div id="ip-images-preview" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px;"></div>
                                </div>
                            </div>
                            
                            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 12px;">
                                <button type="button" onclick="document.getElementById('ip-add-property-modal').remove()" style="padding: 10px 16px; border-radius: 6px; border: 1px solid #d1d5db; background: white; font-weight: 500; cursor: pointer; color: #374151;">Cancelar</button>
                                <button type="submit" id="ip-submit-btn" style="padding: 10px 24px; border-radius: 6px; border: none; background: #1e3a8a; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                    Guardar Inmueble
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    };

    window.openEditPropertyModal = function(postId) {
        if (document.getElementById('ip-add-property-modal')) document.getElementById('ip-add-property-modal').remove();

        const post = allPropertiesData.find(p => p.id === postId);
        if (!post) return;

        let ipMeta = post.ip_meta || {};
        let title = post.title.rendered || '';
        let description = post.content.rendered || '';
        let price = ipMeta.precio || ipMeta.precio_venta || '';
        let rooms = ipMeta.habitaciones || ipMeta.dormitorios || 1;
        let baths = ipMeta.banos || ipMeta.baños || 1;
        let size = ipMeta.superficie_construida || ipMeta.superficie || 100;

        // Collect dropdown options from `allPropertiesData`
        let types = new Set();
        let statuses = new Set();
        let cities = new Set();
        allPropertiesData.forEach(p => {
            if (p._embedded && p._embedded['wp:term']) {
                p._embedded['wp:term'].forEach(taxonomy => {
                    taxonomy.forEach(term => {
                        if (term.taxonomy === 'impress_property_type') types.add(term.name);
                        if (term.taxonomy === 'impress_status') statuses.add(term.name);
                        if (term.taxonomy === 'impress_city') cities.add(term.name);
                    });
                });
            }
        });

        // Current terms
        let currentType = '';
        let currentStatus = '';
        let currentCity = '';
        if (post._embedded && post._embedded['wp:term']) {
            post._embedded['wp:term'].forEach(taxonomy => {
                taxonomy.forEach(term => {
                    if (term.taxonomy === 'impress_property_type') currentType = term.name;
                    if (term.taxonomy === 'impress_status') currentStatus = term.name;
                    if (term.taxonomy === 'impress_city') currentCity = term.name;
                });
            });
        }

        let typeOpts = [...types].sort().map(t => `<option value="${t}" ${t === currentType ? 'selected' : ''}>${t}</option>`).join('');
        let statusOpts = [...statuses].sort().map(s => `<option value="${s}" ${s === currentStatus ? 'selected' : ''}>${s}</option>`).join('');
        let cityOpts = [...cities].sort().map(c => `<option value="${c}" ${c === currentCity ? 'selected' : ''}>${c}</option>`).join('');

        let currentGalleryHTML = '';
        if (ipMeta.gallery && ipMeta.gallery.length > 0) {
            currentGalleryHTML = `
                <div style="grid-column: 1 / -1; margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Imágenes Actuales (Haz clic en la papelera para borrar)</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        ${ipMeta.gallery.map(img => `
                            <div style="width: 100px; height: 80px; border-radius: 8px; overflow: hidden; position: relative; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.05); group">
                                <img src="${img.url}" style="width: 100%; height: 100%; object-fit: cover;">
                                <button type="button" onclick="window.deletePropertyImage(${postId}, ${img.id}, this)" style="position: absolute; top: 4px; right: 4px; background: rgba(239, 68, 68, 0.9); border: none; width: 24px; height: 24px; border-radius: 4px; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                    <span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        const modalHTML = `
            <div id="ip-add-property-modal" style="position: fixed; inset: 0; background: rgba(17, 24, 39, 0.7); z-index: 100000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                <div style="background: white; border-radius: 12px; width: 700px; max-width: 90vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 10;">
                        <h2 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Editar Inmueble: ${title}</h2>
                        <button onclick="document.getElementById('ip-add-property-modal').remove()" style="background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #6b7280; padding: 0;">&times;</button>
                    </div>
                    <div style="padding: 24px;">
                        <form id="ip-edit-property-form" onsubmit="window.submitEditPropertyForm(${postId}, event)">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                ${currentGalleryHTML}
                                
                                <div style="grid-column: 1 / -1;">
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Título del Inmueble *</label>
                                    <input type="text" name="title" required value="${title}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>
                                
                                <div style="grid-column: 1 / -1;">
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Descripción</label>
                                    <textarea name="description" rows="3" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">${description}</textarea>
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Precio (€) *</label>
                                    <input type="number" name="price" required min="0" value="${price}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Ubicación / Ciudad</label>
                                    <select name="city" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px; background: white;">
                                        ${cityOpts}
                                    </select>
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Habitaciones</label>
                                    <input type="number" name="rooms" min="0" value="${rooms}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Baños</label>
                                    <input type="number" name="baths" min="0" value="${baths}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>
                                
                                <div style="grid-column: 1 / -1;">
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Superficie construida (m²)</label>
                                    <input type="number" name="size" min="0" value="${size}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Tipo de Inmueble</label>
                                    <select name="type" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px; background: white;">
                                        ${typeOpts}
                                    </select>
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Estado</label>
                                    <select name="status" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px; background: white;">
                                        ${statusOpts}
                                    </select>
                                </div>

                                <div style="grid-column: 1 / -1; margin-top: 8px;">
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Añadir más fotos</label>
                                    <input type="file" id="ip-edit-images-input" name="images[]" accept="image/*" multiple style="width: 100%; border: 1px dashed #d1d5db; border-radius: 6px; padding: 16px 10px; font-size: 14px; background: #f9fafb; cursor: pointer;" onchange="window.handleImagePreview(event, 'ip-edit-images-preview')">
                                    <div id="ip-edit-images-preview" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px;"></div>
                                </div>
                            </div>
                            
                            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 12px;">
                                <button type="button" onclick="document.getElementById('ip-add-property-modal').remove()" style="padding: 10px 16px; border-radius: 6px; border: 1px solid #d1d5db; background: white; font-weight: 500; cursor: pointer; color: #374151;">Cancelar</button>
                                <button type="submit" id="ip-edit-submit-btn" style="padding: 10px 24px; border-radius: 6px; border: none; background: #1e3a8a; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    };

    window.submitEditPropertyForm = function(postId, e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const btn = document.getElementById('ip-edit-submit-btn');
        
        btn.innerHTML = '<span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span> Guardando...';
        btn.disabled = true;

        fetch(inmoPressDashboard.rest_url + 'inmopress/v1/property/update/' + postId, {
            method: 'POST',
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                document.getElementById('ip-add-property-modal').remove();
                
                // Refrescamos los inmuebles y actualizamos la vista detalle
                allPropertiesData = []; // Clear to force fresh fetch or just fetch
                fetchProperties();
                
                // Close detail and reopen after a short delay to see changes or just show a message
                window.closePropertyDetail();
                alert('Inmueble actualizado correctamente.');
            } else {
                alert('Error: ' + data.message);
                btn.innerHTML = 'Guardar Cambios';
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de red.');
            btn.innerHTML = 'Guardar Cambios';
            btn.disabled = false;
        });
    };

    window.submitAddPropertyForm = function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const btn = document.getElementById('ip-submit-btn');
        
        btn.innerHTML = '<span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span> Guardando...';
        btn.disabled = true;

        fetch(inmoPressDashboard.rest_url + 'inmopress/v1/property/add', {
            method: 'POST',
            headers: { 
                'X-WP-Nonce': inmoPressDashboard.nonce 
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success || data.post_id) { // Sometimes success comes back, sometimes just post_id depending on rest response structure
                let modal = document.getElementById('ip-add-property-modal');
                if (modal) modal.remove();
                
                // Refrescamos los inmuebles
                document.getElementById('ip-properties-grid').innerHTML = '<div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #6b7280; font-size: 14px;"><span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 24px; width: 24px; height: 24px;"></span><br>Actualizando lista...</div>';
                fetchProperties();
            } else {
                alert('Error al guardar inmueble: ' + (data.message || 'Error desconocido'));
                btn.innerHTML = 'Guardar Inmueble';
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de red al intentar guardar.');
            btn.innerHTML = 'Guardar Inmueble';
            btn.disabled = false;
        });
    };

    function renderClients() {
        container.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Clientes (CRM)</h2>
                <button onclick="alert('Funcionalidad para añadir cliente próximamente')" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                    <span class="dashicons dashicons-plus-alt2"></span> Añadir Cliente
                </button>
            </div>
            
            <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; display: flex; gap: 16px;">
                    <input type="text" placeholder="Buscar cliente por nombre o teléfono..." style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; width: 350px; font-size: 14px;">
                    <select style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; cursor: pointer;">
                        <option>Todos los tipos</option>
                        <option>Compradores</option>
                        <option>Propietarios</option>
                        <option>Inversores</option>
                    </select>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                                <th style="padding: 12px 24px; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase;">Nombre de Cliente</th>
                                <th style="padding: 12px 24px; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase;">Tipo</th>
                                <th style="padding: 12px 24px; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase;">Último contacto</th>
                                <th style="padding: 12px 24px; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase; text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="ip-clients-table-body">
                            <tr><td colspan="4" style="padding: 40px; text-align: center; color: #6b7280; font-size: 14px;">
                                <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 24px; width: 24px; height: 24px;"></span><br>
                                Cargando clientes...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        fetchClients();
    }

    function fetchClients() {
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_client?_embed&per_page=20', {
            method: 'GET',
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(response => response.json())
        .then(posts => {
            const tbody = document.getElementById('ip-clients-table-body');
            tbody.innerHTML = '';
            
            if (!posts || posts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="padding: 40px; text-align: center; color: #6b7280; font-size: 14px;">No hay clientes registrados todavía.</td></tr>';
                return;
            }

            posts.forEach(post => {
                let title = post.title.rendered || '(Sin nombre)';
                let typeBadge = '<span style="background: #eff6ff; color: #3b82f6; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid #bfdbfe;">Comprador</span>';
                
                tbody.innerHTML += `
                    <tr style="border-bottom: 1px solid #e5e7eb; transition: background 0.2s;">
                        <td style="padding: 16px 24px; font-weight: 600; color: #111827; font-size: 14px; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #6b7280; font-weight: 700;">
                                ${title.charAt(0).toUpperCase()}
                            </div>
                            ${title}
                        </td>
                        <td style="padding: 16px 24px;">${typeBadge}</td>
                        <td style="padding: 16px 24px; color: #6b7280; font-size: 14px;">Ayer</td>
                        <td style="padding: 16px 24px; text-align: right;">
                            <button onclick="viewClient(${post.id})" style="background: none; border: none; color: #3b82f6; cursor: pointer; font-weight: 600; font-size: 14px;">Ver Ficha</button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            console.error(err);
            document.getElementById('ip-clients-table-body').innerHTML = '<tr><td colspan="4" style="padding: 40px; text-align: center; color: #ef4444; font-size: 14px;">Error al cargar los clientes.</td></tr>';
        });
    }

    // Exponer métodos globales para eventos onClick y navegación
    window.ipLoadView = loadView;
    
    window.viewClient = function(clientId) {
        container.innerHTML = `
            <div style="padding: 40px; text-align: center; color: #6b7280; font-size: 14px;">
                <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 24px; width: 24px; height: 24px;"></span><br>
                Cargando datos del cliente...
            </div>
        `;

        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_client/' + clientId, {
            method: 'GET',
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(response => response.json())
        .then(client => {
            let title = client.title.rendered || '(Sin nombre)';
            
            container.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <button onclick="ipLoadView('clients')" style="background: white; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 12px; cursor: pointer; color: #374151; font-weight: 500; display: flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background 0.2s;">
                            <span class="dashicons dashicons-arrow-left-alt2"></span> Volver
                        </button>
                        <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Ficha del Cliente</h2>
                    </div>
                    <div>
                        <button onclick="editClient(${client.id})" style="background: white; border: 1px solid #e5e7eb; color: #374151; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; margin-right: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background 0.2s;">
                            <span class="dashicons dashicons-edit"></span> Editar Perfil
                        </button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
                    <!-- Perfil Izquierdo -->
                    <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: center;">
                        <div style="width: 120px; height: 120px; border-radius: 50%; background: #e5e7eb; color: #6b7280; font-size: 40px; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                            ${title.charAt(0).toUpperCase()}
                        </div>
                        <h3 style="margin: 0 0 12px 0; font-size: 22px; color: #111827;">${title}</h3>
                        <p style="margin: 0; color: #6b7280; font-size: 14px; font-weight: 500;">
                            <span style="background: #eff6ff; color: #3b82f6; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; border: 1px solid #bfdbfe; display: inline-block;">Comprador</span>
                        </p>
                        
                        <div style="margin-top: 32px; text-align: left; padding-top: 32px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 20px 0; color: #374151; font-size: 15px; display: flex; align-items: center; gap: 12px;">
                                <span class="dashicons dashicons-email-alt" style="color: #9ca3af; font-size: 20px;"></span> 
                                <span><strong>Email:</strong><br>correo@ejemplo.com</span>
                            </p>
                            <p style="margin: 0 0 20px 0; color: #374151; font-size: 15px; display: flex; align-items: center; gap: 12px;">
                                <span class="dashicons dashicons-phone" style="color: #9ca3af; font-size: 20px;"></span> 
                                <span><strong>Teléfono:</strong><br>+34 600 000 000</span>
                            </p>
                            <p style="margin: 0; color: #374151; font-size: 15px; display: flex; align-items: center; gap: 12px;">
                                <span class="dashicons dashicons-location-alt" style="color: #9ca3af; font-size: 20px;"></span> 
                                <span><strong>Ubicación:</strong><br>Madrid, España</span>
                            </p>
                        </div>
                    </div>

                    <!-- Datos Principales -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <h4 style="margin: 0 0 20px 0; font-size: 18px; color: #111827; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">Criterios de Búsqueda</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                                <div>
                                    <div style="font-size: 13px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Presupuesto Máximo</div>
                                    <div style="font-size: 18px; color: #111827; margin-top: 8px; font-weight: 500;">€ 250,000</div>
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Zonas de Interés</div>
                                    <div style="font-size: 18px; color: #111827; margin-top: 8px;">Centro, Norte</div>
                                </div>
                            </div>
                        </div>

                        <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <h4 style="margin: 0 0 20px 0; font-size: 18px; color: #111827; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">Actividad e Historial</h4>
                            <div style="color: #6b7280; font-size: 15px; line-height: 1.6;">
                                <div style="display: flex; gap: 16px; border-bottom: 1px solid #f3f4f6; padding-bottom: 16px; margin-bottom: 16px;">
                                    <div style="width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; margin-top: 6px;"></div>
                                    <div>
                                        <strong>Llamada telefónica</strong> <span style="font-size: 12px; color: #9ca3af;">- Ayer, 16:30</span>
                                        <div style="margin-top: 4px;">El cliente consultó sobre el nuevo piso en el centro. Se agendó visita.</div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 16px;">
                                    <div style="width: 12px; height: 12px; border-radius: 50%; background: #10b981; margin-top: 6px;"></div>
                                    <div>
                                        <strong>Visita a Inmueble</strong> <span style="font-size: 12px; color: #9ca3af;">- Hace 1 semana</span>
                                        <div style="margin-top: 4px;">Le gustó bastante, pero busca algo con terraza.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<div style="padding: 40px; text-align: center; color: #ef4444; font-size: 15px;">Error al cargar la ficha del cliente.</div>';
        });
    };

    window.editClient = function(clientId) {
        container.innerHTML = `
            <div style="padding: 40px; text-align: center; color: #6b7280; font-size: 14px;">
                <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 24px; width: 24px; height: 24px;"></span><br>
                Cargando editor...
            </div>
        `;

        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_client/' + clientId, {
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(response => response.json())
        .then(client => {
            let title = client.title.rendered || '';
            
            container.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <button onclick="viewClient(${client.id})" style="background: white; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 12px; cursor: pointer; color: #374151; font-weight: 500; display: flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background 0.2s;">
                            <span class="dashicons dashicons-arrow-left-alt2"></span> Cancelar
                        </button>
                        <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Editando Perfil de Cliente</h2>
                    </div>
                    <div>
                        <button id="ip-save-client-btn" onclick="saveClient(${client.id})" style="background: #3b82f6; color: white; padding: 10px 24px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 15px; box-shadow: 0 4px 6px -1px rgba(59,130,246,0.3); transition: all 0.2s;">
                            <span class="dashicons dashicons-saved"></span> Guardar Cambios
                        </button>
                    </div>
                </div>

                <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 40px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto;">
                    <form id="ip-edit-client-form">
                        <div style="margin-bottom: 24px;">
                            <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">Nombre Completo (*)</label>
                            <input type="text" id="client_title" value="${title}" placeholder="Introduce el nombre" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px 16px; font-size: 15px; background: #f9fafb;" />
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">Teléfono</label>
                                <input type="text" placeholder="+34 600 000 000" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px 16px; font-size: 15px; background: #fff;" />
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">Correo Electrónico</label>
                                <input type="email" placeholder="correo@ejemplo.com" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px 16px; font-size: 15px; background: #fff;" />
                            </div>
                        </div>

                        <div style="margin-bottom: 24px;">
                            <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">Tipo de Cliente</label>
                            <select style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px 16px; font-size: 15px; background: #fff; cursor: pointer;">
                                <option>Comprador / Interesado</option>
                                <option>Propietario</option>
                                <option>Inversor</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">Notas Privadas del Cliente</label>
                            <textarea rows="5" placeholder="Escribe aquí notas adicionales, preferencias, y otras cosas a tener en cuenta..." style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px 16px; font-size: 15px; background: #fff; line-height: 1.5;"></textarea>
                        </div>
                    </form>
                </div>
            `;
        });
    };

    window.saveClient = function(clientId) {
        const titleInput = document.getElementById('client_title');
        if(!titleInput.value.trim()) {
            alert("El nombre es obligatorio.");
            return;
        }

        const btn = document.getElementById('ip-save-client-btn');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> Guardando...';
        btn.disabled = true;

        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_client/' + clientId, {
            method: 'POST',
            headers: { 
                'X-WP-Nonce': inmoPressDashboard.nonce,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: titleInput.value
            })
        })
        .then(response => {
            if(!response.ok) throw new Error("Error en la solicitud");
            return response.json();
        })
        .then(client => {
            viewClient(client.id); // Redirigir de nuevo a la ficha al completar
        })
        .catch(err => {
            console.error(err);
            alert('Ocurrió un error guardando el perfil del cliente.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    };

    window.handleImagePreview = function(event, containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        const files = event.target.files;
        if (!files) return;
        Array.from(files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgWrap = document.createElement('div');
                imgWrap.style.cssText = 'width: 70px; height: 70px; border-radius: 6px; overflow: hidden; border: 1px solid #e5e7eb; position: relative;';
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
                imgWrap.appendChild(img);
                container.appendChild(imgWrap);
            }
            reader.readAsDataURL(file);
        });
    };

    window.deletePropertyImage = function(postId, attachmentId, btn) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta imagen?')) return;
        const icon = btn.querySelector('.dashicons');
        icon.classList.remove('dashicons-trash');
        icon.classList.add('dashicons-update');
        icon.style.animation = 'spin 2s linear infinite';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('attachment_id', attachmentId);

        fetch(inmoPressDashboard.rest_url + 'inmopress/v1/property/delete-attachment', {
            method: 'POST',
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.closest('div').parentElement.style.opacity = '0.5'; // Visual feedback
                btn.closest('div').remove();
                allPropertiesData = []; 
                fetchProperties();
            } else {
                alert('Error: ' + data.message);
                icon.classList.remove('dashicons-update');
                icon.classList.add('dashicons-trash');
                icon.style.animation = '';
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de red al eliminar la imagen.');
            icon.classList.remove('dashicons-update');
            icon.classList.add('dashicons-trash');
            icon.style.animation = '';
            btn.disabled = false;
        });
    };
});

