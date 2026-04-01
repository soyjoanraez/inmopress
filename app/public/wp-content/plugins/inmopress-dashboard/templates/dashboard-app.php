<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
/* Aislar el dashboard ocultando los elementos nativos de WP */
#wpadminbar { display: none !important; }
#adminmenumain { display: none !important; }
#wpcontent, #wpfooter { margin-left: 0 !important; }
html.wp-toolbar { padding-top: 0 !important; }
#wpbody-content { padding-bottom: 0 !important; }
.auto-fold #wpcontent { padding-left: 0 !important; }
#screen-meta-links, .notice, .updated, .error { display: none !important; } /* Ocultar notificaciones nativas */

/* Estilos base del Dashboard SPA */
body.toplevel_page_inmopress-dashboard {
    background-color: #f3f4f6;
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

#inmopress-dashboard-app {
    display: flex;
    min-height: 100vh;
    width: 100%;
}

.ip-sidebar {
    width: 260px;
    background-color: #111827;
    color: #fff;
    display: flex;
    flex-direction: column;
}

.ip-sidebar-header {
    padding: 24px;
    font-size: 22px;
    font-weight: 700;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    gap: 12px;
}

.ip-sidebar-nav {
    flex-grow: 1;
    padding: 24px 0;
}

.ip-sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.ip-sidebar-nav li {
    padding: 14px 24px;
    cursor: pointer;
    transition: background 0.2s;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.ip-sidebar-nav li:hover, .ip-sidebar-nav li.active {
    background-color: #1f2937;
    border-left: 4px solid #3b82f6;
}

.ip-main-content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.ip-topbar {
    height: 72px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    padding: 0 32px;
    justify-content: space-between;
}

.ip-content-area {
    padding: 32px;
    flex-grow: 1;
    overflow-y: auto;
}
</style>

<div id="inmopress-dashboard-app">
    <!-- Sidebar -->
    <aside class="ip-sidebar">
        <div class="ip-sidebar-header">
            <span class="dashicons dashicons-building"></span>
            InmoPress
        </div>
        <nav class="ip-sidebar-nav">
            <ul>
                <li class="active" data-view="dashboard"><span class="dashicons dashicons-chart-pie"></span> Resumen</li>
                <li data-view="properties"><span class="dashicons dashicons-admin-home"></span> Inmuebles</li>
                <li data-view="owners"><span class="dashicons dashicons-admin-users"></span> Propietarios</li>
                <li data-view="leads"><span class="dashicons dashicons-megaphone"></span> Leads <span id="ip-leads-badge" style="display:none; background:#ef4444; color:white; border-radius:50%; width:8px; height:8px; margin-left:auto;"></span></li>
                <li data-view="offers"><span class="dashicons dashicons-money-alt"></span> Ofertas</li>
                <li data-view="agenda"><span class="dashicons dashicons-calendar-alt"></span> Agenda</li>
                <li data-view="tasks"><span class="dashicons dashicons-editor-ul"></span> Tareas</li>
                <li data-view="clients"><span class="dashicons dashicons-groups"></span> Clientes</li>
                <li data-view="agents"><span class="dashicons dashicons-businessman"></span> Agentes</li>
                <li data-view="agencies"><span class="dashicons dashicons-building"></span> Agencias</li>
                <li data-view="communication"><span class="dashicons dashicons-email-alt"></span> Comunicación</li>
                <li data-view="billing"><span class="dashicons dashicons-media-document"></span> Facturación</li>
                <li data-view="settings"><span class="dashicons dashicons-admin-generic"></span> Ajustes</li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="ip-main-content">
        <!-- Topbar -->
        <header class="ip-topbar">
            <div class="ip-topbar-search">
                <input type="text" placeholder="Buscar inmuebles o clientes..." style="padding: 10px 16px; border: 1px solid #e5e7eb; border-radius: 8px; width: 300px; font-size: 14px; outline: none;">
            </div>
            <div class="ip-topbar-user" style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 14px; font-weight: 500;">Hola, Administrador</span>
                <div style="width: 36px; height: 36px; background: #3b82f6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    A
                </div>
            </div>
        </header>

        <!-- Dynamic Content Area -->
        <div class="ip-content-area" id="ip-view-container">
            <!-- Renderizado por JS -->
            <div style="padding: 40px; text-align: center; color: #6b7280;">Cargando Dashboard...</div>
        </div>
    </main>
</div>
