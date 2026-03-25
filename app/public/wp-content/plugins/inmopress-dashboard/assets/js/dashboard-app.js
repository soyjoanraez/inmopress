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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Inmuebles</h2>
                <button onclick="alert('Editor de nueva propiedad (Próximamente)')" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                    <span class="dashicons dashicons-plus-alt2"></span> Añadir Inmueble
                </button>
            </div>
            
            <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; display: flex; gap: 16px;">
                    <input type="text" placeholder="Buscar inmueble por título..." style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; width: 350px; font-size: 14px;">
                    <select style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; cursor: pointer;">
                        <option>Todos los estados</option>
                        <option>En Venta</option>
                        <option>En Alquiler</option>
                    </select>
                </div>
                <div id="ip-properties-grid" style="padding: 24px; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; background: #f3f4f6;">
                    <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #6b7280; font-size: 14px;">
                        <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 24px; width: 24px; height: 24px;"></span><br>
                        Cargando propiedades...
                    </div>
                </div>
            </div>
        `;

        fetchProperties();
    }

    function fetchProperties() {
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_property?_embed&per_page=20', {
            method: 'GET',
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(response => response.json())
        .then(posts => {
            const grid = document.getElementById('ip-properties-grid');
            grid.innerHTML = '';
            
            if (!posts || posts.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #6b7280; font-size: 14px;">No hay inmuebles registrados todavía.</div>';
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
                let rawPrice = ipMeta.precio || ipMeta.precio_venta || ipMeta.precio_alquiler || null;
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
                    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; display: flex; flex-direction: column;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03)'">
                        
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
        })
        .catch(err => {
            console.error(err);
            document.getElementById('ip-properties-grid').innerHTML = '<div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #ef4444; font-size: 14px;">Error al cargar las propiedades.</div>';
        });
    }

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
});
