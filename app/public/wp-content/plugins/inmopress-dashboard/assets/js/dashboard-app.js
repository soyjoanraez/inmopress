document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.ip-sidebar-nav li');
    const container = document.getElementById('ip-view-container');

    // Inicializar la vista por defecto
    loadView('dashboard');
    fetchClients(); // Cargar clientes al inicio para mapear propietarios

    // Estado Global (Persistente vía API)
    window.allDealsData = [];
    window.allOffersData = [];
    window.allInvoicesData = [];
    window.allEventsData = [];
    window.allTasksData = [];

    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            window.ipSwitchView(view);
        });
    });

    window.ipSwitchView = function(view) {
        // Update sidebar active class
        navItems.forEach(n => {
            if (n.getAttribute('data-view') === view) {
                n.classList.add('active');
            } else {
                n.classList.remove('active');
            }
        });

        // Render view
        loadView(view);
    };

    function loadView(view) {
        if (view === 'dashboard') {
            renderDashboard();
        } else if (view === 'properties') {
            renderProperties();
        } else if (view === 'pipeline') {
            fetchDeals();
        } else if (view === 'offers') {
            fetchOffers();
        } else if (view === 'agenda') {
            fetchEvents();
        } else if (view === 'tasks') {
            fetchTasks();
        } else if (view === 'clients') {
            renderClients();
        } else if (view === 'billing') {
            fetchInvoices();
        } else {
            container.innerHTML = `<h2 style="margin-top: 0; font-size: 24px; color: #111827;">Vista: ${view} en desarrollo...</h2>`;
        }
    }

    function renderDashboard() {
        container.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <div>
                    <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Panel General</h2>
                    <p style="color: #6b7280; margin-top: 8px;">Vista rápida del estado de tu inmobiliaria.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button onclick="window.seedDashboardData()" style="background: white; border: 1px solid #d1d5db; color: #6b7280; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background 0.2s;">
                        <span class="dashicons dashicons-database-add"></span> Demo
                    </button>
                    <button onclick="window.openAddPropertyModal()" style="background: white; border: 1px solid #d1d5db; color: #374151; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background 0.2s;">
                        <span class="dashicons dashicons-plus-alt"></span> Nuevo Inmueble
                    </button>
                    <button onclick="window.openAddClientModal()" style="background: #1e3a8a; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px; box-shadow: 0 4px 6px -1px rgba(30,58,138,0.25); transition: opacity 0.2s;">
                        <span class="dashicons dashicons-admin-users"></span> Nuevo Cliente
                    </button>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 40px;">
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="color: #6b7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Propiedades Totales</div>
                    <div id="ip-stat-properties" style="font-size: 36px; font-weight: 800; color: #1e3a8a; margin-top: 8px; font-family: 'Inter', sans-serif;">
                        <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span>
                    </div>
                </div>
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="color: #6b7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Clientes Registrados</div>
                    <div id="ip-stat-clients" style="font-size: 36px; font-weight: 800; color: #10b981; margin-top: 8px; font-family: 'Inter', sans-serif;">
                        <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span>
                    </div>
                </div>
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="color: #6b7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Visitas Pendientes</div>
                    <div style="font-size: 36px; font-weight: 800; color: #f59e0b; margin-top: 8px; font-family: 'Inter', sans-serif;">--</div>
                </div>
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="color: #6b7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Mensajes</div>
                    <div style="font-size: 36px; font-weight: 800; color: #8b5cf6; margin-top: 8px; font-family: 'Inter', sans-serif;">--</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
                <div>
                    <h3 style="margin: 0 0 20px 0; font-size: 20px; color: #111827; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                        <span class="dashicons dashicons-admin-home" style="color: #3b82f6;"></span> Últimos Inmuebles Añadidos
                    </h3>
                    <div id="ip-recent-properties" style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 4px;">
                        <!-- Loader -->
                        <div style="padding: 30px; text-align: center; color: #9ca3af;">
                            <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span> Cargando inmuebles...
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 style="margin: 0 0 20px 0; font-size: 20px; color: #111827; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                        <span class="dashicons dashicons-admin-users" style="color: #10b981;"></span> Últimos Clientes
                    </h3>
                    <div id="ip-recent-clients" style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 4px;">
                        <div style="padding: 20px; text-align: center; color: #9ca3af;">
                            <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span> Cargando...
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Fetch Stats
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_property?per_page=1', { headers: { 'X-WP-Nonce': inmoPressDashboard.nonce } })
        .then(res => res.headers.get('X-WP-Total') || 0)
        .then(count => document.getElementById('ip-stat-properties').innerText = count);

        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_client?per_page=1', { headers: { 'X-WP-Nonce': inmoPressDashboard.nonce } })
        .then(res => res.headers.get('X-WP-Total') || 0)
        .then(count => document.getElementById('ip-stat-clients').innerText = count);

        // Fetch Recent Properties
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_property?_embed&per_page=5', { headers: { 'X-WP-Nonce': inmoPressDashboard.nonce } })
        .then(res => res.json())
        .then(posts => {
            const list = document.getElementById('ip-recent-properties');
            list.innerHTML = '';
            posts.forEach(post => {
                const img = post._embedded && post._embedded['wp:featuredmedia'] ? post._embedded['wp:featuredmedia'][0].media_details.sizes.thumbnail ? post._embedded['wp:featuredmedia'][0].media_details.sizes.thumbnail.source_url : post._embedded['wp:featuredmedia'][0].source_url : 'https://via.placeholder.com/60';
                const meta = post.ip_meta || {};
                const price = meta.precio || meta._precio || 'Consul.';
                const location = meta.zona || meta.direccion || 'Ubicación no especificada';
                list.innerHTML += `
                    <div onclick="viewProperty(${post.id})" style="padding: 10px 16px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: 16px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">
                        <div style="width: 50px; height: 50px; border-radius: 8px; background: url('${img}') center/cover no-repeat; border: 1px solid #e5e7eb;"></div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; color: #1f2937; font-size: 14px;">${post.title.rendered}</div>
                            <div style="color: #6b7280; font-size: 12px; margin-top: 2px;">${location} • <span style="color: #3b82f6; font-weight: 600;">${price}€</span></div>
                        </div>
                        <span class="dashicons dashicons-arrow-right-alt2" style="color: #d1d5db; font-size: 16px;"></span>
                    </div>
                `;
            });
            if (posts.length === 0) list.innerHTML = '<div style="padding: 20px; text-align: center; color: #9ca3af;">No hay inmuebles registrados.</div>';
        });

        // Fetch Recent Clients
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_client?_embed&per_page=5', { headers: { 'X-WP-Nonce': inmoPressDashboard.nonce } })
        .then(res => res.json())
        .then(posts => {
            const list = document.getElementById('ip-recent-clients');
            list.innerHTML = '';
            posts.forEach(post => {
                const name = post.title.rendered;
                const meta = post.ip_meta || {};
                const rawType = meta.type || meta.client_type || 'Comprador';
                let typeLabel = rawType === 'Propietario' ? 'Vendedor' : (rawType === 'Interesado' ? 'Comprador' : rawType);
                
                list.innerHTML += `
                    <div onclick="viewClient(${post.id})" style="padding: 12px 16px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">${name.charAt(0).toUpperCase()}</div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #1f2937; font-size: 14px;">${name}</div>
                            <div style="font-size: 12px; color: #6b7280;">${typeLabel}</div>
                        </div>
                        <span class="dashicons dashicons-arrow-right-alt2" style="color: #d1d5db; font-size: 16px;"></span>
                    </div>
                `;
            });
            if (posts.length === 0) list.innerHTML = '<div style="padding: 20px; text-align: center; color: #9ca3af;">No hay actividad reciente.</div>';
        });
    }

    function renderPipeline() {
        const stages = [
            { id: 'prospeccion', name: 'Prospección', color: '#f3f4f6' },
            { id: 'visita', name: 'Visita', color: '#f3f4f6' },
            { id: 'oferta', name: 'Oferta', color: '#f3f4f6' },
            { id: 'arras', name: 'Arras', color: '#f3f4f6' },
            { id: 'cerrado', name: 'Cerrado', color: '#ecfdf5', borderColor: '#a7f3d0', headerColor: '#065f46' }
        ];

        let stagesHTML = stages.map(stage => {
            const stageDeals = window.allDealsData.filter(d => d.stage === stage.id);
            
            let cardsHTML = stageDeals.map(deal => `
                <div draggable="true" ondragstart="window.handleIPDragStart(event, ${deal.id})" style="background: ${deal.stage === 'oferta' ? '#eff6ff' : 'white'}; padding: 12px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid ${deal.stage === 'oferta' ? '#bfdbfe' : '#e5e7eb'}; ${deal.labelColor ? `border-left: 3px solid ${deal.labelColor};` : ''} cursor: grab; transition: transform 0.1s;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)'">
                    ${deal.amount ? `<div style="font-size: 10px; font-weight: 700; color: ${deal.stage === 'oferta' ? '#3b82f6' : '#6b7280'}; text-transform: uppercase; margin-bottom: 2px;">${deal.amount}</div>` : ''}
                    ${deal.label ? `<div style="font-size: 10px; font-weight: 700; color: ${deal.labelColor}; text-transform: uppercase; margin-bottom: 2px;">${deal.label}</div>` : ''}
                    <div style="font-weight: 700; color: #111827; font-size: 13px; line-height: 1.2;">${deal.title}</div>
                    <div style="font-size: 12px; color: #4b5563; margin-top: 2px;">${deal.client}</div>
                    ${deal.notes ? `<div style="font-size: 11px; color: #9ca3af; margin-top: 6px; line-height: 1.3;">${deal.notes}</div>` : ''}
                    ${deal.stage === 'cerrado' ? `
                        <button onclick="window.generateInvoiceFromDeal(${deal.id})" style="margin-top: 10px; width: 100%; padding: 6px; background: #ecfdf5; color: #10b981; border: 1px solid #10b981; border-radius: 4px; font-size: 11px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px;">
                            <span class="dashicons dashicons-media-document" style="font-size: 14px; width: 14px; height: 14px;"></span> FACTURAR
                        </button>
                    ` : ''}
                </div>
            `).join('');

            if (stageDeals.length === 0 && stage.id !== 'cerrado') {
                cardsHTML = `<div style="padding: 15px; text-align: center; color: #9ca3af; font-size: 12px; border: 2px dashed #d1d5db; border-radius: 8px;">Vacío</div>`;
            }

            return `
                <div ondragover="window.handleIPDragOver(event)" ondrop="window.handleIPDrop(event, '${stage.id}')" style="flex: 1; min-width: 180px; background: ${stage.color}; border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 10px; height: fit-content; ${stage.borderColor ? `border: 1px solid ${stage.borderColor};` : ''}">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <h4 style="margin: 0; font-size: 11px; font-weight: 700; color: ${stage.headerColor || '#4b5563'}; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${stage.name}</h4>
                        <span style="background: ${stage.headerColor ? '#a7f3d0' : '#e5e7eb'}; color: ${stage.headerColor || '#4b5563'}; padding: 1px 6px; border-radius: 999px; font-size: 10px; font-weight: 700;">${stageDeals.length}</span>
                    </div>
                    ${cardsHTML}
                </div>
            `;
        }).join('');

        container.innerHTML = `
            <div style="margin-bottom: 32px;">
                <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Pipeline de Ventas</h2>
                <p style="color: #6b7280; margin-top: 8px;">Gestiona tus negociaciones arrastrando los negocios entre fases.</p>
            </div>

            <div style="display: flex; gap: 12px; padding-bottom: 40px; align-items: flex-start; min-height: calc(100vh - 250px); width: 100%;">
                ${stagesHTML}
            </div>
        `;
    }

    // Handlers globales para Drag & Drop del Pipeline
    window.handleIPDragStart = function(e, dealId) {
        e.dataTransfer.setData('text/plain', dealId);
        e.target.style.opacity = '0.5';
    };

    window.handleIPDragOver = function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    };

    window.handleIPDrop = function(e, newStage) {
        e.preventDefault();
        const dealId = parseInt(e.dataTransfer.getData('text/plain'));
        const deal = window.allDealsData.find(d => d.id === dealId);
        if (deal) {
            deal.stage = newStage;
            renderPipeline(); // Re-renderizar tablero inmediato
            
            // Persistir cambio
            const formData = new FormData();
            formData.append('id', deal.id);
            formData.append('stage', newStage);
            
            fetch(inmoPressDashboard.rest_url + 'inmopress/v1/deal/save', {
                method: 'POST',
                headers: { 'X-WP-Nonce': inmoPressDashboard.nonce },
                body: formData
            });
        }
    };

    function renderOffers() {
        let rowsHTML = window.allOffersData.map(offer => {
            const isPending = offer.status === 'PENDIENTE';
            return `
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 16px 24px;">
                        <div style="font-weight: 700; color: #111827;">${offer.propertyTitle}</div>
                        <div style="font-size: 12px; color: #6b7280;">ID: ${offer.propertyId}</div>
                    </td>
                    <td style="padding: 16px 24px; color: #374151;">${offer.client}</td>
                    <td style="padding: 16px 24px; font-weight: 700; color: #1e3a8a;">${new Intl.NumberFormat('es-ES').format(offer.amount)} €</td>
                    <td style="padding: 16px 24px;">
                        <span style="background: ${offer.status === 'PENDIENTE' ? '#fef3c7' : '#ecfdf5'}; color: ${offer.status === 'PENDIENTE' ? '#d97706' : '#059669'}; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700;">${offer.status}</span>
                    </td>
                    <td style="padding: 16px 24px; text-align: right;">
                        ${isPending ? `
                            <button onclick="window.acceptIPOffer(${offer.id})" style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">Aceptar</button>
                            <button onclick="window.rejectIPOffer(${offer.id})" style="background: white; border: 1px solid #d1d5db; color: #4b5563; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; margin-left: 8px;">Rechazar</button>
                        ` : `
                            <span style="font-size: 12px; color: #9ca3af;">Ver contrato</span>
                        `}
                    </td>
                </tr>
            `;
        }).join('');

        container.innerHTML = `
            <div style="margin-bottom: 32px;">
                <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Gestión de Ofertas</h2>
                <p style="color: #6b7280; margin-top: 8px;">Lleva el control de todas las negociaciones activas.</p>
            </div>

            <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                        <tr>
                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase;">Inmueble</th>
                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase;">Cliente</th>
                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase;">Oferta</th>
                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase;">Estado</th>
                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase; text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHTML}
                    </tbody>
                </table>
            </div>
        `;
    }

    window.acceptIPOffer = function(offerId) {
        const offer = window.allOffersData.find(o => o.id === offerId);
        if (offer) {
            offer.status = 'ACEPTADA';
            
            // 1. Guardar Oferta
            const formDataOffer = new FormData();
            formDataOffer.append('id', offer.id);
            formDataOffer.append('status', 'ACEPTADA');
            
            fetch(inmoPressDashboard.rest_url + 'inmopress/v1/offer/save', {
                method: 'POST',
                headers: { 'X-WP-Nonce': inmoPressDashboard.nonce },
                body: formDataOffer
            });

            // 2. Crear negocio automático en Pipeline
            const formDataDeal = new FormData();
            formDataDeal.append('title', offer.propertyTitle);
            formDataDeal.append('client', offer.client);
            formDataDeal.append('stage', 'arras');
            formDataDeal.append('amount', new Intl.NumberFormat('es-ES').format(offer.amount) + '€');
            formDataDeal.append('notes', 'Generado automáticamente desde Ofertas.');

            fetch(inmoPressDashboard.rest_url + 'inmopress/v1/deal/save', {
                method: 'POST',
                headers: { 'X-WP-Nonce': inmoPressDashboard.nonce },
                body: formDataDeal
            }).then(() => {
                alert('Oferta aceptada. Se ha creado un nuevo negocio en la fase de ARRAS.');
                window.ipSwitchView('pipeline');
            });
        }
    };

    window.rejectIPOffer = function(offerId) {
        const offer = window.allOffersData.find(o => o.id === offerId);
        if (offer) {
            offer.status = 'RECHAZADA';
            
            const formData = new FormData();
            formData.append('id', offer.id);
            formData.append('status', 'RECHAZADA');
            
            fetch(inmoPressDashboard.rest_url + 'inmopress/v1/offer/save', {
                method: 'POST',
                headers: { 'X-WP-Nonce': inmoPressDashboard.nonce },
                body: formData
            }).then(() => renderOffers());
        }
    };

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
            
            let ownerName = 'Sin asignar';
            if (ipMeta.owner_id && window.allClientsData) {
                let owner = window.allClientsData.find(c => c.id == ipMeta.owner_id);
                if (owner) ownerName = owner.title.rendered;
            }
            
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
                        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed #e5e7eb; display: flex; align-items: center; gap: 8px; color: #4b5563; font-size: 13px; font-weight: 500;">
                            <span class="dashicons dashicons-admin-users" style="font-size: 16px; width: 16px; height: 16px; color: #3b82f6;"></span>
                            <span>Propietario: <strong>${ownerName}</strong></span>
                        </div>
                    </div>
                </div>
            `;
        });
        grid.innerHTML = cardsHTML;
    }

    window.viewProperty = function(postId) {
        // Si no estamos en la vista de propiedades, cambiamos a ella primero
        const listViewExists = document.getElementById('ip-properties-list-view');
        if (!listViewExists) {
            window.ipSwitchView('properties');
        }

        // Buscar el post en la data cargada o hacer un solo fetch si es necesario
        let post = allPropertiesData.find(p => p.id === postId);
        
        if (!post) {
            // Mostrar un mini loader en el detailView mientras traemos la data
            const detailView = document.getElementById('ip-property-detail-view');
            const listView = document.getElementById('ip-properties-list-view');
            listView.style.display = 'none';
            detailView.style.display = 'block';
            detailView.innerHTML = '<div style="padding: 100px; text-align: center;"><span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 30px; width: 30px; height: 30px;"></span><br>Cargando detalles del inmueble...</div>';

            fetch(inmoPressDashboard.rest_url + `wp/v2/impress_property/${postId}?_embed`, {
                method: 'GET',
                headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
            })
            .then(res => res.json())
            .then(fetchedPost => {
                renderPropertyDetail(fetchedPost);
            })
            .catch(err => {
                detailView.innerHTML = '<div style="padding: 100px; text-align: center; color: #ef4444;">Error al cargar el inmueble.</div>';
            });
            return;
        }

        renderPropertyDetail(post);
    };

    function renderPropertyDetail(post) {
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
                    <div style="padding: 24px; text-align: center; border-right: 1px solid #e5e7eb;">
                        <span class="dashicons dashicons-admin-users" style="color: #1e3a8a; font-size: 24px; margin-bottom: 8px; width: 100%;"></span>
                        <div style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Propietario</div>
                        <div style="font-size: 16px; font-weight: 700; color: #3b82f6; margin-top: 4px; cursor: pointer;" onclick="window.viewClient(${ipMeta.owner_id})">
                            ${(window.allClientsData && ipMeta.owner_id) ? (window.allClientsData.find(c => c.id == ipMeta.owner_id)?.title.rendered || 'Ver Ficha') : 'Sin asignar'}
                        </div>
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
    window.openAddPropertyModal = function(preselectedOwnerId = null) {
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
        let ownerOpts = (window.allClientsData || []).map(c => `<option value="${c.id}" ${preselectedOwnerId == c.id ? 'selected' : ''}>${c.title.rendered || '(Sin nombre)'}</option>`).join('');
        if (!ownerOpts) ownerOpts = '<option value="">Sin propietarios registrados</option>';

        const modalHTML = `
            <div id="ip-add-property-modal" style="position: fixed; inset: 0; background: rgba(17, 24, 39, 0.7); z-index: 100000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                <div style="background: white; border-radius: 12px; width: 700px; max-width: 90vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 10;">
                        <h2 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Añadir Nuevo Inmueble</h2>
                        <button onclick="document.getElementById('ip-add-property-modal').remove()" style="background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #6b7280; padding: 0;">&times;</button>
                    </div>
                    <div style="padding: 24px;">
                        <form id="ip-add-property-form" onsubmit="window.submitAddPropertyForm(event)">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div style="grid-column: 1 / -1;">
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Propietario / Cliente Asociado</label>
                                    <div style="display: flex; gap: 8px;">
                                        <select id="ip-prop-owner-select" name="owner_id" style="flex: 1; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px; background: white; border-left: 3px solid #3b82f6;">
                                            <option value="">-- Selecciona un propietario --</option>
                                            ${ownerOpts}
                                        </select>
                                        <button type="button" onclick="window.openAddClientQuickModal()" style="background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 12px; color: #374151; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px;" title="Añadir nuevo cliente">+</button>
                                    </div>
                                </div>

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
                                    <span class="dashicons dashicons-plus-alt2"></span> Publicar Inmueble
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

        let ownerOpts = (window.allClientsData || []).map(c => `<option value="${c.id}" ${c.id == ipMeta.owner_id ? 'selected' : ''}>${c.title.rendered || '(Sin nombre)'}</option>`).join('');
        if (!ownerOpts) ownerOpts = '<option value="">Sin propietarios registrados</option>';

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
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Propietario / Cliente Asociado</label>
                                    <div style="display: flex; gap: 8px;">
                                        <select id="ip-prop-owner-select" name="owner_id" style="flex: 1; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px; background: white; border-left: 3px solid #3b82f6;">
                                            <option value="">-- Selecciona un propietario --</option>
                                            ${ownerOpts}
                                        </select>
                                        <button type="button" onclick="window.openAddClientQuickModal()" style="background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 12px; color: #374151; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px;" title="Añadir nuevo cliente">+</button>
                                    </div>
                                </div>

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
                allPropertiesData = []; 
                fetchProperties();
                
                // --- AUTOMATIZACIÓN: Sincronizar con Pipeline ---
                const selectedStatus = formData.get('status') || '';
                if (selectedStatus.toLowerCase().includes('vend')) {
                    // Mover todos los negocios de este inmueble a "Cerrado"
                    window.allDealsData.forEach(deal => {
                        if (deal.propertyId === postId) {
                            deal.stage = 'cerrado';
                        }
                    });
                }
                // ------------------------------------------------

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
                <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Clientes</h2>
                <button onclick="window.openAddClientModal()" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                    <span class="dashicons dashicons-plus-alt2"></span> Añadir Cliente
                </button>
            </div>
            
            <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; display: flex; gap: 16px;">
                    <div style="position: relative;">
                        <span class="dashicons dashicons-search" style="position: absolute; left: 10px; top: 10px; color: #9ca3af;"></span>
                        <input type="text" id="ip-client-filter-search" oninput="window.applyClientFilters()" placeholder="Buscar por nombre, teléfono o email..." style="padding: 10px 14px 10px 36px; border: 1px solid #d1d5db; border-radius: 6px; width: 350px; font-size: 14px;">
                    </div>
                    <select id="ip-client-filter-type" onchange="window.applyClientFilters()" style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; cursor: pointer;">
                        <option value="">Todos los tipos</option>
                        <option value="Comprador">Compradores</option>
                        <option value="Inversor">Inversores</option>
                        <option value="Propietario">Vendedores / Propietarios</option>
                    </select>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                                <th style="padding: 12px 24px; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase;">Nombre de Cliente</th>
                                <th style="padding: 12px 24px; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase;">Email</th>
                                <th style="padding: 12px 24px; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase;">Tipo</th>
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

    let allClientsData = [];

    function fetchClients() {
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_client?_embed&per_page=100', {
            method: 'GET',
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(response => response.json())
        .then(posts => {
            allClientsData = posts || [];
            window.renderClientTableRows(allClientsData);
        })
        .catch(err => {
            console.error(err);
            document.getElementById('ip-clients-table-body').innerHTML = '<tr><td colspan="4" style="padding: 40px; text-align: center; color: #ef4444; font-size: 14px;">Error al cargar los clientes.</td></tr>';
        });
    }

    window.renderClientTableRows = function(clients) {
        const tbody = document.getElementById('ip-clients-table-body');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        if (!clients || clients.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="padding: 40px; text-align: center; color: #6b7280; font-size: 14px;">No se encontraron clientes con esos filtros.</td></tr>';
            return;
        }

        clients.forEach(post => {
            let title = post.title.rendered || '(Sin nombre)';
            let meta = post.ip_meta || {};
            let rawType = meta.type || meta.client_type || 'Comprador';
            let email = meta.email || '-';
            
            // Unificar Comprador e Interesado
            let type = rawType;
            if (rawType === 'Interesado') type = 'Comprador';

            let typeBadge = '';
            if (type === 'Propietario') {
                typeBadge = `<span style="background: #ecfdf5; color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid #a7f3d0;">Vendedor</span>`;
            } else if (type === 'Inversor') {
                typeBadge = `<span style="background: #fff7ed; color: #f59e0b; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid #fed7aa;">${type}</span>`;
            } else {
                typeBadge = `<span style="background: #eff6ff; color: #3b82f6; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid #bfdbfe;">${type}</span>`;
            }
            
            tbody.innerHTML += `
                <tr style="border-bottom: 1px solid #e5e7eb; transition: background 0.2s;">
                    <td style="padding: 16px 24px; font-weight: 600; color: #111827; font-size: 14px; display: flex; align-items: center; gap: 12px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #6b7280; font-weight: 700;">
                            ${title.charAt(0).toUpperCase()}
                        </div>
                        ${title}
                    </td>
                    <td style="padding: 16px 24px; color: #374151; font-size: 14px;">${email}</td>
                    <td style="padding: 16px 24px;">${typeBadge}</td>
                    <td style="padding: 16px 24px; text-align: right;">
                        <button onclick="viewClient(${post.id})" style="background: none; border: none; color: #3b82f6; cursor: pointer; font-weight: 600; font-size: 14px;">Ver Ficha</button>
                    </td>
                </tr>
            `;
        });
    };

    window.applyClientFilters = function() {
        const searchText = document.getElementById('ip-client-filter-search').value.toLowerCase();
        const selectedType = document.getElementById('ip-client-filter-type').value;

        const filtered = allClientsData.filter(post => {
            const title = post.title.rendered.toLowerCase();
            const meta = post.ip_meta || {};
            const rawType = meta.type || meta.client_type || 'Comprador';
            const email = (meta.email || '').toLowerCase();
            const phone = (meta.phone || meta.telefono || '').toLowerCase();

            // Unificar tipos para el filtro
            let type = rawType;
            if (rawType === 'Interesado') type = 'Comprador';

            const matchesSearch = title.includes(searchText) || phone.includes(searchText) || email.includes(searchText);
            const matchesType = !selectedType || type === selectedType;

            return matchesSearch && matchesType;
        });

        window.renderClientTableRows(filtered);
    };

    // Exponer métodos globales para eventos onClick y navegación
    window.ipLoadView = loadView;
    
    window.viewClient = function(clientId) {
        // Si no estamos en la vista de clientes, cambiamos a ella primero
        const clientViewExists = document.getElementById('ip-clients-table-body');
        if (!clientViewExists) {
            window.ipSwitchView('clients');
        }

        let post = allClientsData.find(p => p.id === clientId);

        if (!post) {
            // Fetch individual client if not in cache
            container.innerHTML = `<div style="padding: 100px; text-align: center;"><span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 30px; width: 30px; height: 30px;"></span><br>Cargando ficha de cliente...</div>`;
            
            fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_client/' + clientId + '?_embed', {
                method: 'GET',
                headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
            })
            .then(res => res.json())
            .then(fetchedPost => {
                renderClientDetail(fetchedPost);
            })
            .catch(err => {
                container.innerHTML = '<div style="padding: 100px; text-align: center; color: #ef4444;">Error al cargar el cliente.</div>';
            });
            return;
        }

        renderClientDetail(post);
    };

    function renderClientDetail(client) {
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
                            <span style="background: #eff6ff; color: #3b82f6; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; border: 1px solid #bfdbfe; display: inline-block;">${client.ip_meta.type || 'Cliente'}</span>
                        </p>
                        
                        <div style="margin-top: 24px;">
                            <button onclick="window.openAddPropertyModal(${client.id})" style="width: 100%; background: #3b82f6; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px;">
                                <span class="dashicons dashicons-plus-alt2"></span> Añadir Inmueble
                            </button>
                        </div>
                        
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
                        <!-- Tabs Navigation -->
                        <div style="display: flex; gap: 24px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; margin-bottom: 8px;">
                            <button onclick="window.switchClientTab('info')" class="client-tab active" id="tab-info" style="background: none; border: none; font-size: 15px; font-weight: 700; color: #3b82f6; cursor: pointer; border-bottom: 2px solid #3b82f6; padding-bottom: 12px; margin-bottom: -14px;">Información</button>
                            <button onclick="window.switchClientTab('demands')" class="client-tab" id="tab-demands" style="background: none; border: none; font-size: 15px; font-weight: 600; color: #6b7280; cursor: pointer; padding-bottom: 12px;">Demandas (Matching)</button>
                            <button onclick="window.switchClientTab('docs')" class="client-tab" id="tab-docs" style="background: none; border: none; font-size: 15px; font-weight: 600; color: #6b7280; cursor: pointer; padding-bottom: 12px;">Documentación</button>
                        </div>

                        <div id="client-tab-content">
                            <!-- Inmuebles Vinculados -->
                            <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
                                <h4 style="margin: 0 0 20px 0; font-size: 18px; color: #111827; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                                    Inmuebles en Propiedad
                                    <span style="background: #f3f4f6; color: #6b7280; padding: 2px 8px; border-radius: 999px; font-size: 12px;">${window.allPropertiesData.filter(p => p.ip_meta.owner_id == client.id).length}</span>
                                </h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
                                    ${window.allPropertiesData.filter(p => p.ip_meta.owner_id == client.id).map(p => `
                                        <div onclick="window.viewProperty(${p.id})" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">
                                            <div style="font-weight: 700; font-size: 13px; color: #111827;">${p.title.rendered}</div>
                                            <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">${p.ip_meta.zona || p.ip_meta.direccion || 'Ubicación...'}</div>
                                        </div>
                                    `).join('') || '<div style="grid-column: 1 / -1; color: #9ca3af; font-size: 14px; text-align: center; padding: 10px;">No hay inmuebles asignados.</div>'}
                                </div>
                            </div>

                            <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
                                <h4 style="margin: 0 0 20px 0; font-size: 18px; color: #111827; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">Actividad e Historial</h4>
                                <div style="color: #6b7280; font-size: 15px; line-height: 1.6;">
                                    <div style="display: flex; gap: 16px; border-bottom: 1px solid #f3f4f6; padding-bottom: 16px; margin-bottom: 16px;">
                                        <div style="width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; margin-top: 6px;"></div>
                                        <div>
                                            <strong>Llamada telefónica</strong> <span style="font-size: 12px; color: #9ca3af;">- Reciente</span>
                                            <div style="margin-top: 4px;">Consulta sobre estado de su inmueble.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Tab switching logic for this specific view (localized)
            window.switchClientTab = function(tabName) {
                const buttons = document.querySelectorAll('.client-tab');
                buttons.forEach(b => {
                    b.style.color = '#6b7280';
                    b.style.borderBottom = 'none';
                    b.style.fontWeight = '600';
                });
                const activeBtn = document.getElementById('tab-' + tabName);
                activeBtn.style.color = '#3b82f6';
                activeBtn.style.borderBottom = '2px solid #3b82f6';
                activeBtn.style.fontWeight = '700';

                const tabContent = document.getElementById('client-tab-content');
                if (tabName === 'info') {
                    renderClientDetail(client); // Just re-render the whole thing or build internal fragments
                } else if (tabName === 'demands') {
                    renderClientDemands(client, tabContent);
                } else if (tabName === 'docs') {
                    renderClientDocuments(client, tabContent);
                }
            };
        }

        function renderClientDemands(client, tabContent) {
            // Buscamos demandas previas para este cliente
            fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_demand?per_page=1&search=' + client.title.rendered, {
                headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
            })
            .then(res => res.json())
            .then(demands => {
                const demand = demands[0] || null;
                const minPrice = demand?.ip_meta?.min_price || 0;
                const maxPrice = demand?.ip_meta?.max_price || 300000;
                const rooms = demand?.ip_meta?.rooms || 2;
                const city = demand?.ip_meta?.city || '';
                const parking = demand?.ip_meta?.parking == '1';

                // Matching logic
                const matches = window.allPropertiesData.filter(p => {
                    const pPrice = parseFloat(p.ip_meta?.precio || p.ip_meta?.precio_venta || 0);
                    const pRooms = parseInt(p.ip_meta?.habitaciones || p.ip_meta?.dormitorios || 0);
                    const pCity = (p.ip_meta?.ciudad || p.ip_meta?.poblacion || '').toLowerCase();
                    const hasParking = p.ip_meta?.garaje == '1' || p.ip_meta?.parking == '1';

                    const priceMatch = pPrice >= minPrice && pPrice <= maxPrice;
                    const roomMatch = pRooms >= rooms;
                    const cityMatch = !city || pCity.includes(city.toLowerCase());
                    const parkingMatch = !parking || hasParking;

                    return priceMatch && roomMatch && cityMatch && parkingMatch;
                });

                tabContent.innerHTML = `
                    <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
                        <h4 style="margin: 0 0 20px 0; font-size: 18px; color: #111827; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                            Criterios de Búsqueda
                            <button onclick="window.saveClientDemand(${client.id})" style="background: #1e3a8a; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">Guardar Criterios</button>
                        </h4>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Precio Máximo</label>
                                <input type="number" id="dem-max-price" value="${maxPrice}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px; margin-top: 4px;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Zona / Ciudad</label>
                                <input type="text" id="dem-city" value="${city}" placeholder="Ej: Barcelona" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px; margin-top: 4px;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Mín. Habitaciones</label>
                                <input type="number" id="dem-rooms" value="${rooms}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px; margin-top: 4px;">
                            </div>
                            <div style="display: flex; align-items: flex-end; padding-bottom: 10px;">
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #374151; cursor: pointer;">
                                    <input type="checkbox" id="dem-parking" ${parking ? 'checked' : ''} style="width: 18px; height: 18px;"> Requiere Parking
                                </label>
                            </div>
                        </div>
                    </div>

                    <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <h4 style="margin: 0 0 20px 0; font-size: 18px; color: #111827; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
                            Matching Inmuebles (${matches.length})
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            ${matches.map(p => `
                                <div onclick="window.viewProperty(${p.id})" style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; cursor: pointer; display: flex; gap: 16px; align-items: center;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'">
                                    <div style="width: 80px; height: 60px; background: #f3f4f6; border-radius: 8px; overflow: hidden;">
                                        ${p.ip_meta.gallery_ids ? `<img src="${p.preview_image}" style="width: 100%; height: 100%; object-fit: cover;">` : '<span class="dashicons dashicons-admin-home" style="font-size: 30px; margin: 15px;"></span>'}
                                    </div>
                                    <div style="flex-grow: 1;">
                                        <div style="font-weight: 700; color: #1e3a8a;">${p.title.rendered}</div>
                                        <div style="font-size: 12px; color: #6b7280;">${p.ip_meta.precio || p.ip_meta.precio_venta} € · ${p.ip_meta.habitaciones} hab · ${p.ip_meta.zona || p.ip_meta.poblacion}</div>
                                    </div>
                                    <span class="dashicons dashicons-arrow-right-alt2" style="color: #bfdbfe;"></span>
                                </div>
                            `).join('') || '<div style="text-align: center; color: #9ca3af; padding: 20px;">No hay inmuebles que encajen exactamente con estos criterios en este momento.</div>'}
                        </div>
                    </div>
                `;
            });
        }

        window.saveClientDemand = function(clientId) {
            const fd = new FormData();
            fd.append('title', 'Demanda - ' + clientId);
            fd.append('client_id', clientId);
            fd.append('min_price', 0);
            fd.append('max_price', document.getElementById('dem-max-price').value);
            fd.append('city', document.getElementById('dem-city').value);
            fd.append('rooms', document.getElementById('dem-rooms').value);
            fd.append('parking', document.getElementById('dem-parking').checked ? '1' : '0');

            fetch(inmoPressDashboard.rest_url + 'inmopress/v1/demand/save', {
                method: 'POST',
                headers: { 'X-WP-Nonce': inmoPressDashboard.nonce },
                body: fd
            }).then(() => {
                alert('Criterios de búsqueda guardados correctamente.');
                window.switchClientTab('demands');
            });
        };

        function renderClientDocuments(client, tabContent) {
            tabContent.innerHTML = `
                <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
                        <h4 style="margin: 0; font-size: 18px; color: #111827;">Documentos del Cliente</h4>
                        <button onclick="alert('Subida de archivos en desarrollo (Simulado)')" style="background: #1e3a8a; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 13px;">
                            <span class="dashicons dashicons-upload"></span> Subir Documento
                        </button>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; display: flex; align-items: center; gap: 16px;">
                            <span class="dashicons dashicons-media-document" style="font-size: 32px; width: 32px; height: 32px; color: #d1d5db;"></span>
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 700; color: #374151;">DNI_Frontal.pdf</div>
                                <div style="font-size: 12px; color: #6b7280;">Documento de identidad · 1.2 MB</div>
                            </div>
                            <button style="background: none; border: none; color: #3b82f6; cursor: pointer;"><span class="dashicons dashicons-download"></span></button>
                        </div>
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; display: flex; align-items: center; gap: 16px;">
                            <span class="dashicons dashicons-media-spreadsheet" style="font-size: 32px; width: 32px; height: 32px; color: #d1d5db;"></span>
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 700; color: #374151;">Contrato_Arras_Firmado.pdf</div>
                                <div style="font-size: 12px; color: #6b7280;">Contrato Legal · 3.5 MB</div>
                            </div>
                            <button style="background: none; border: none; color: #3b82f6; cursor: pointer;"><span class="dashicons dashicons-download"></span></button>
                        </div>
                    </div>
                    <p style="margin-top: 20px; font-size: 13px; color: #6b7280; text-align: center;">Formatos aceptados: PDF, JPG, PNG, DOCX.</p>
                </div>
            `;
        }

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

    window.ipQuickAddActive = false;

    window.openAddClientQuickModal = function() {
        window.ipQuickAddActive = true;
        window.openAddClientModal();
        
        // Cambiar título del modal para dar contexto
        const modal = document.getElementById('ip-add-client-modal');
        if (modal) {
            modal.querySelector('h2').innerText = 'Añadir Dueño Rápidamente';
        }
    };

    window.openAddClientModal = function() {
        const modalHTML = `
            <div id="ip-add-client-modal" style="position: fixed; inset: 0; background: rgba(17, 24, 39, 0.7); z-index: 100000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                <div style="background: white; border-radius: 12px; width: 500px; max-width: 90vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 10;">
                        <h2 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Añadir Nuevo Cliente</h2>
                        <button onclick="document.getElementById('ip-add-client-modal').remove()" style="background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #6b7280; padding: 0;">&times;</button>
                    </div>
                    <div style="padding: 24px;">
                        <form id="ip-add-client-form" onsubmit="window.submitAddClientForm(event)">
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Nombre Completo *</label>
                                    <input type="text" name="name" required placeholder="Ej. Juan Pérez" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                </div>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                    <div>
                                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Teléfono</label>
                                        <input type="text" name="phone" placeholder="+34 600..." style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Email</label>
                                        <input type="email" name="email" placeholder="correo@ejemplo.com" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;">
                                    </div>
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Tipo de Cliente</label>
                                    <select name="type" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px; background: white;">
                                        <option value="Comprador">Comprador / Interesado</option>
                                        <option value="Propietario">Propietario</option>
                                        <option value="Inversor">Inversor</option>
                                    </select>
                                </div>

                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">Notas</label>
                                    <textarea name="notes" rows="3" placeholder="Preferencias, presupuesto, zonas de interés..." style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 14px;"></textarea>
                                </div>
                            </div>
                            
                            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 12px;">
                                <button type="button" onclick="document.getElementById('ip-add-client-modal').remove()" style="padding: 10px 16px; border-radius: 6px; border: 1px solid #d1d5db; background: white; font-weight: 500; cursor: pointer; color: #374151;">Cancelar</button>
                                <button type="submit" id="ip-submit-client-btn" style="padding: 10px 24px; border-radius: 6px; border: none; background: #1e3a8a; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                    Guardar Cliente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    };

    window.submitAddClientForm = function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const btn = document.getElementById('ip-submit-client-btn');
        
        btn.innerHTML = '<span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span> Guardando...';
        btn.disabled = true;

        fetch(inmoPressDashboard.rest_url + 'inmopress/v1/client/add', {
            method: 'POST',
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                document.getElementById('ip-add-client-modal').remove();
                fetchClients(); // Refrescar la tabla
                
                // --- AUTOMATIZACIÓN: Crear lead en Pipeline ---
                const clientName = formData.get('name');
                const newLead = {
                    id: Date.now(),
                    title: 'Nueva solicitud',
                    client: clientName,
                    stage: 'prospeccion',
                    notes: 'Lead creado automáticamente al registrar al cliente.'
                };
                window.allDealsData.push(newLead);
                // ----------------------------------------------

                // --- AUTOMATIZACIÓN: Retorno rápido a Inmueble ---
                if (window.ipQuickAddActive) {
                    const ownerSelect = document.getElementById('ip-prop-owner-select');
                    if (ownerSelect && data.client_id) {
                        const newOpt = document.createElement('option');
                        newOpt.value = data.client_id;
                        newOpt.text = clientName;
                        newOpt.selected = true;
                        ownerSelect.add(newOpt);
                    }
                    window.ipQuickAddActive = false;
                }
                // ----------------------------------------------
            } else {
                alert('Error: ' + data.message);
                btn.innerHTML = 'Guardar Cliente';
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de red al intentar guardar cliente.');
            btn.innerHTML = 'Guardar Cliente';
            btn.disabled = false;
        });
    };
    window.renderBilling = function() {
        const totalInvoiced = window.allInvoicesData.reduce((acc, inv) => acc + inv.amount, 0);
        const totalPaid = window.allInvoicesData.filter(i => i.status === 'PAGADA').reduce((acc, inv) => acc + inv.amount, 0);
        const totalPending = totalInvoiced - totalPaid;

        let rowsHTML = window.allInvoicesData.map(inv => `
            <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">
                <td style="padding: 16px 24px;">
                    <div style="font-weight: 700; color: #111827;">${inv.id}</div>
                    <div style="font-size: 12px; color: #6b7280;">${inv.date}</div>
                </td>
                <td style="padding: 16px 24px;">
                    <div style="font-weight: 600; color: #374151;">${inv.client}</div>
                    <div style="font-size: 12px; color: #6b7280;">${inv.property}</div>
                </td>
                <td style="padding: 16px 24px; color: #6b7280; font-size: 14px;">${inv.type}</td>
                <td style="padding: 16px 24px; font-weight: 700; color: #1e3a8a;">${new Intl.NumberFormat('es-ES').format(inv.amount)} €</td>
                <td style="padding: 16px 24px;">
                    <span style="background: ${inv.status === 'PAGADA' ? '#ecfdf5' : '#fef3c7'}; color: ${inv.status === 'PAGADA' ? '#059669' : '#d97706'}; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; border: 1px solid ${inv.status === 'PAGADA' ? '#a7f3d0' : '#fed7aa'};">${inv.status}</span>
                </td>
                <td style="padding: 16px 24px; text-align: right;">
                    <button onclick="alert('Descargando factura ${inv.id} en PDF (Simulado)...')" style="background: white; border: 1px solid #d1d5db; color: #374151; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                        <span class="dashicons dashicons-download" style="font-size: 14px; width: 14px; height: 14px;"></span> PDF
                    </button>
                </td>
            </tr>
        `).join('');

        if (window.allInvoicesData.length === 0) {
            rowsHTML = '<tr><td colspan="6" style="padding: 40px; text-align: center; color: #9ca3af;">No hay facturas emitidas todavía.</td></tr>';
        }

        container.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <div>
                    <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Facturación</h2>
                    <p style="color: #6b7280; margin-top: 8px;">Control de ingresos y comisiones de la agencia.</p>
                </div>
                <button style="background: #1e3a8a; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                    <span class="dashicons dashicons-plus-alt"></span> Emitir Factura Manual
                </button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px;">
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-left: 4px solid #10b981;">
                    <div style="color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;">Total Facturado</div>
                    <div style="font-size: 32px; font-weight: 800; color: #111827; margin-top: 8px;">${new Intl.NumberFormat('es-ES').format(totalInvoiced)} €</div>
                </div>
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-left: 4px solid #f59e0b;">
                    <div style="color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;">Pendiente de Cobro</div>
                    <div style="font-size: 32px; font-weight: 800; color: #111827; margin-top: 8px;">${new Intl.NumberFormat('es-ES').format(totalPending)} €</div>
                </div>
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-left: 4px solid #3b82f6;">
                    <div style="color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;">Promedio por Venta</div>
                    <div style="font-size: 32px; font-weight: 800; color: #111827; margin-top: 8px;">${window.allInvoicesData.length ? new Intl.NumberFormat('es-ES').format(totalInvoiced / window.allInvoicesData.length) : 0} €</div>
                </div>
            </div>

            <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #374151;">Historial de Facturas</h3>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" placeholder="Filtrar facturas..." style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; width: 200px;">
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase;">Factura</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase;">Cliente / Inmueble</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase;">Concepto</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase;">Importe</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase;">Estado</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase; text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHTML}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    };

    window.generateInvoiceFromDeal = function(dealId) {
        const deal = window.allDealsData.find(d => d.id === dealId);
        if (!deal) return;

        const commission = prompt("Introduce el importe de la comisión para " + deal.title + " (€):", "3000");
        if (commission === null) return;

        const formData = new FormData();
        formData.append('id', 'INV-' + Date.now().toString().slice(-4));
        formData.append('date', new Date().toISOString().split('T')[0]);
        formData.append('client', deal.client);
        formData.append('property', deal.title);
        formData.append('amount', parseFloat(commission) || 0);
        formData.append('status', 'PENDIENTE');
        formData.append('type', 'Comisión Venta');

        fetch(inmoPressDashboard.rest_url + 'inmopress/v1/invoice/save', {
            method: 'POST',
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce },
            body: formData
        }).then(() => {
            alert('Factura generada correctamente.');
            window.ipSwitchView('billing');
        });
    };

    function fetchOffers() {
        container.innerHTML = '<div style="padding: 100px; text-align: center;"><span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 30px; width: 30px; height: 30px;"></span><br>Cargando ofertas...</div>';
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_offer?per_page=100&_embed', {
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(res => res.json())
        .then(posts => {
            if (!posts || posts.length === 0) {
                container.innerHTML = `
                    <div style="padding: 100px; text-align: center; background: white; border-radius: 12px; border: 1px solid #e5e7eb;">
                        <span class="dashicons dashicons-tag" style="font-size: 48px; width: 48px; height: 48px; color: #d1d5db; margin-bottom: 20px;"></span>
                        <h3 style="margin: 0; color: #374151;">No hay ofertas registradas</h3>
                        <p style="color: #6b7280; margin: 10px 0 20px 0;">Empieza a gestionar ofertas o carga los datos de ejemplo.</p>
                        <button onclick="window.seedDashboardData()" style="background: #1e3a8a; color: white; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; cursor: pointer;">Cargar datos de ejemplo</button>
                    </div>
                `;
                return;
            }
            window.allOffersData = posts.map(p => ({
                id: p.id,
                propertyTitle: p.title?.rendered || 'Inmueble no especificado',
                propertyId: p.ip_meta?.propertyId || 'N/A',
                client: p.ip_meta?.client || 'Cliente desconocido',
                amount: parseFloat(p.ip_meta?.amount) || 0,
                status: p.ip_meta?.status || 'PENDIENTE'
            }));
            renderOffers();
        });
    }

    function fetchDeals() {
        container.innerHTML = '<div style="padding: 100px; text-align: center;"><span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 30px; width: 30px; height: 30px;"></span><br>Cargando pipeline...</div>';
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_deal?per_page=100&_embed', {
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(res => res.json())
        .then(posts => {
            if (!posts || posts.length === 0) {
                container.innerHTML = `
                    <div style="padding: 100px; text-align: center; background: white; border-radius: 12px; border: 1px solid #e5e7eb;">
                        <span class="dashicons dashicons-chart-line" style="font-size: 48px; width: 48px; height: 48px; color: #d1d5db; margin-bottom: 20px;"></span>
                        <h3 style="margin: 0; color: #374151;">Pipeline de ventas vacío</h3>
                        <p style="color: #6b7280; margin: 10px 0 20px 0;">Cierra negocios y haz seguimiento o carga ejemplos.</p>
                        <button onclick="window.seedDashboardData()" style="background: #1e3a8a; color: white; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; cursor: pointer;">Cargar datos de ejemplo</button>
                    </div>
                `;
                return;
            }
            window.allDealsData = posts.map(p => ({
                id: p.id,
                title: p.title?.rendered || 'Negocio sin título',
                client: p.ip_meta?.client || 'Sin cliente',
                stage: p.ip_meta?.stage || 'prospeccion',
                amount: p.ip_meta?.amount || '-',
                notes: p.ip_meta?.notes || '',
                label: p.ip_meta?.label || '',
                labelColor: p.ip_meta?.labelColor || ''
            }));
            renderPipeline();
        });
    }

    function fetchInvoices() {
        container.innerHTML = '<div style="padding: 100px; text-align: center;"><span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 30px; width: 30px; height: 30px;"></span><br>Cargando facturación...</div>';
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_invoice?per_page=100&_embed', {
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(res => res.json())
        .then(posts => {
            if (!posts || posts.length === 0) {
                container.innerHTML = `
                    <div style="padding: 100px; text-align: center; background: white; border-radius: 12px; border: 1px solid #e5e7eb;">
                        <span class="dashicons dashicons-media-document" style="font-size: 48px; width: 48px; height: 48px; color: #d1d5db; margin-bottom: 20px;"></span>
                        <h3 style="margin: 0; color: #374151;">No hay facturas emitidas</h3>
                        <p style="color: #6b7280; margin: 10px 0 20px 0;">Lleva el control de tus comisiones o carga ejemplos.</p>
                        <button onclick="window.seedDashboardData()" style="background: #1e3a8a; color: white; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; cursor: pointer;">Cargar datos de ejemplo</button>
                    </div>
                `;
                return;
            }
            window.allInvoicesData = posts.map(p => ({
                id: p.title?.rendered || 'Factura',
                date: p.ip_meta?.date || '',
                client: p.ip_meta?.client || 'Sin cliente',
                property: p.ip_meta?.property || 'Sin inmueble',
                amount: parseFloat(p.ip_meta?.amount) || 0,
                status: p.ip_meta?.status || 'PENDIENTE',
                type: p.ip_meta?.type || 'Comisión Venta'
            }));
            renderBilling();
        });
    }

    function fetchEvents() {
        container.innerHTML = '<div style="padding: 100px; text-align: center;"><span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 30px; width: 30px; height: 30px;"></span><br>Cargando agenda...</div>';
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_event?per_page=100&_embed', {
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(res => res.json())
        .then(posts => {
            window.allEventsData = posts.map(p => ({
                id: p.id,
                title: p.title?.rendered || 'Evento',
                date: p.ip_meta?.date || '',
                time: p.ip_meta?.time || '',
                clientId: p.ip_meta?.client_id || '',
                propertyId: p.ip_meta?.property_id || '',
                type: p.ip_meta?.type || 'Visita'
            }));
            renderAgenda();
        });
    }

    function fetchTasks() {
        container.innerHTML = '<div style="padding: 100px; text-align: center;"><span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 30px; width: 30px; height: 30px;"></span><br>Cargando tareas...</div>';
        fetch(inmoPressDashboard.rest_url + 'wp/v2/impress_task?per_page=100&_embed', {
            headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }
        })
        .then(res => res.json())
        .then(posts => {
            window.allTasksData = posts.map(p => ({
                id: p.id,
                title: p.title?.rendered || 'Tarea',
                status: p.ip_meta?.status || 'pending',
                dueDate: p.ip_meta?.due_date || '',
                linkedPostId: p.ip_meta?.linked_post_id || ''
            }));
            renderTasks();
        });
    }

    window.allCalendarData = {
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear()
    };

    function renderAgenda() {
        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        const { currentMonth, currentYear } = window.allCalendarData;

        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        
        let startingDay = (firstDay === 0) ? 6 : firstDay - 1;

        let calendarHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <div>
                    <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Agenda de Visitas</h2>
                    <p style="color: #6b7280; margin-top: 8px;">Calendario mensual de citas y notaría.</p>
                </div>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <button onclick="window.changeIPMonth(-1)" style="background: white; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; cursor: pointer;"><span class="dashicons dashicons-arrow-left-alt2"></span></button>
                    <span style="font-size: 18px; font-weight: 700; width: 140px; text-align: center;">${monthNames[currentMonth]} ${currentYear}</span>
                    <button onclick="window.changeIPMonth(1)" style="background: white; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; cursor: pointer;"><span class="dashicons dashicons-arrow-right-alt2"></span></button>
                    <button onclick="window.openAddEventModal()" style="margin-left: 12px; background: #1e3a8a; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">+ Nueva Cita</button>
                </div>
            </div>

            <div style="background: white; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <div style="padding: 12px; font-size: 12px; font-weight: 700; color: #6b7280; text-align: center;">LUN</div>
                    <div style="padding: 12px; font-size: 12px; font-weight: 700; color: #6b7280; text-align: center;">MAR</div>
                    <div style="padding: 12px; font-size: 12px; font-weight: 700; color: #6b7280; text-align: center;">MIÉ</div>
                    <div style="padding: 12px; font-size: 12px; font-weight: 700; color: #6b7280; text-align: center;">JUE</div>
                    <div style="padding: 12px; font-size: 12px; font-weight: 700; color: #6b7280; text-align: center;">VIE</div>
                    <div style="padding: 12px; font-size: 12px; font-weight: 700; color: #6b7280; text-align: center; border-left: 1px solid #f3f4f6;">SÁB</div>
                    <div style="padding: 12px; font-size: 12px; font-weight: 700; color: #ef4444; text-align: center; border-left: 1px solid #f3f4f6;">DOM</div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0;">
        `;

        for (let i = 0; i < startingDay; i++) {
            calendarHTML += `<div style="min-height: 120px; background: #fcfcfc; border-right: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;"></div>`;
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const fullDateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const isToday = new Date().toISOString().split('T')[0] === fullDateStr;
            const eventsToday = window.allEventsData.filter(e => e.date === fullDateStr);

            calendarHTML += `
                <div style="min-height: 120px; background: white; border-right: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; padding: 12px; transition: background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <span style="font-size: 14px; font-weight: ${isToday ? '800' : '600'}; color: ${isToday ? '#3b82f6' : '#374151'}; ${isToday ? 'background: #eff6ff; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid #bfdbfe;' : ''}">${d}</span>
                    </div>
                    <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 4px;">
                        ${eventsToday.map(ev => `
                            <div onclick="alert('Evento: ${ev.title}')" style="background: ${ev.type === 'Visita' ? '#eff6ff' : '#fef3c7'}; color: ${ev.type === 'Visita' ? '#1e40af' : '#d97706'}; font-size: 10px; padding: 4px 6px; border-radius: 4px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; border-left: 3px solid ${ev.type === 'Visita' ? '#3b82f6' : '#f59e0b'};">
                                ${ev.time} - ${ev.title}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        calendarHTML += `</div></div>`;
        container.innerHTML = calendarHTML;
    }

    window.changeIPMonth = function(delta) {
        window.allCalendarData.currentMonth += delta;
        if (window.allCalendarData.currentMonth > 11) {
            window.allCalendarData.currentMonth = 0;
            window.allCalendarData.currentYear++;
        } else if (window.allCalendarData.currentMonth < 0) {
            window.allCalendarData.currentMonth = 11;
            window.allCalendarData.currentYear--;
        }
        renderAgenda();
    };

    function renderTasks() {
        const pending = window.allTasksData.filter(t => t.status === 'pending');
        const completed = window.allTasksData.filter(t => t.status === 'completed');

        container.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <div>
                    <h2 style="margin: 0; font-size: 28px; color: #111827; font-weight: 700;">Gestión de Tareas</h2>
                    <p style="color: #6b7280; margin-top: 8px;">Checklist colaborativa de la inmobiliaria.</p>
                </div>
                <button onclick="window.openAddTaskModal()" style="background: #1e3a8a; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">+ Nueva Tarea</button>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #374151; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; background: #f59e0b; border-radius: 50%;"></span> Pendientes (${pending.length})
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        ${pending.map(t => `
                            <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div>
                                    <div style="font-weight: 700; color: #111827;">${t.title}</div>
                                    <div style="font-size: 12px; color: #6b7280; margin-top: 4px;"><span class="dashicons dashicons-calendar-alt" style="font-size: 14px; width: 14px; height: 14px;"></span> Vence: ${t.dueDate || 'Sin fecha'}</div>
                                </div>
                                <button onclick="window.completeIPTask(${t.id})" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">Completar</button>
                            </div>
                        `).join('')}
                    </div>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #374151; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; background: #10b981; border-radius: 50%;"></span> Completadas (${completed.length})
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        ${completed.map(t => `
                            <div style="background: #f9fafb; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; opacity: 0.7;">
                                <div style="text-decoration: line-through; color: #6b7280;">
                                    <div style="font-weight: 700;">${t.title}</div>
                                    <div style="font-size: 12px; margin-top: 4px;">Completado hace poco</div>
                                </div>
                                <span class="dashicons dashicons-yes-alt" style="color: #10b981; font-size: 24px; width: 24px; height: 24px;"></span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    }

    window.completeIPTask = function(taskId) {
        const task = window.allTasksData.find(t => t.id === taskId);
        if (task) {
            task.status = 'completed';
            renderTasks();
            const fd = new FormData();
            fd.append('id', taskId);
            fd.append('status', 'completed');
            fetch(inmoPressDashboard.rest_url + 'inmopress/v1/task/save', { method: 'POST', headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }, body: fd });
        }
    };

    window.seedDashboardData = function() {
        // Tareas de ejemplo
        const tasks = [
            { title: 'Revisar Nota Simple - Ref: 2024-001', due_date: '2026-04-05' },
            { title: 'Publicar Piso en Centro en Portales', due_date: '2026-04-02' },
            { title: 'Llamar a Leads de Idealista (Ayer)', due_date: '2026-03-31' },
            { title: 'Preparar contrato de Arras - Calle Mayor', due_date: '2026-04-10' }
        ];

        // Eventos de ejemplo (Agenda)
        const events = [
            { title: 'Visita Piso Moderno - Cliente: Juan R.', date: '2026-04-01', time: '11:00', type: 'Visita' },
            { title: 'Captación Chalet con Piscina - Juan P.', date: '2026-04-03', time: '17:30', type: 'Captación' },
            { title: 'Firma Notaría - Venta Ático', date: '2026-04-15', time: '10:00', type: 'Firma' }
        ];

        // Guardar tareas y eventos con promesas para saber cuándo terminan
        const taskPromises = tasks.map(t => {
            const fd = new FormData();
            fd.append('title', t.title);
            fd.append('due_date', t.due_date);
            fd.append('status', 'pending');
            return fetch(inmoPressDashboard.rest_url + 'inmopress/v1/task/save', { method: 'POST', headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }, body: fd });
        });

        const eventPromises = events.map(e => {
            const fd = new FormData();
            fd.append('title', e.title);
            fd.append('date', e.date);
            fd.append('time', e.time);
            fd.append('type', e.type);
            return fetch(inmoPressDashboard.rest_url + 'inmopress/v1/event/save', { method: 'POST', headers: { 'X-WP-Nonce': inmoPressDashboard.nonce }, body: fd });
        });

        Promise.all([...taskPromises, ...eventPromises]).then(() => {
            alert('¡Datos de demostración generados con éxito! El sistema se recargará para mostrar los cambios.');
            location.reload();
        }).catch(err => {
            console.error('Error seeding data:', err);
            alert('Hubo un problema al generar algunos datos, pero el proceso ha terminado.');
            location.reload();
        });
    };
});


