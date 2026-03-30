<?php if (!defined('ABSPATH'))
    exit;
// Available variables: $stats, $recent_activity, $user

$current_user = (isset($user) && $user instanceof WP_User) ? $user : wp_get_current_user();
$current_user_id = $current_user ? $current_user->ID : get_current_user_id();
$agency_id = 0;
if (class_exists('Inmopress_Shortcodes')) {
    $agency_id = Inmopress_Shortcodes::get_agency_id_by_user($current_user_id);
}

$agency_field = function ($key, $fallback = '—') use ($agency_id) {
    if (!$agency_id) {
        return $fallback;
    }

    if (function_exists('get_field')) {
        $value = get_field($key, $agency_id);
    } else {
        $value = get_post_meta($agency_id, $key, true);
    }

    if ($value === null || $value === '') {
        return $fallback;
    }
    if (is_array($value) || is_object($value)) {
        return $fallback;
    }
    return $value;
};

$agency_name = $agency_field('agency_name', '');
if ($agency_name === '') {
    $agency_name = $agency_field('nombre_comercial', '');
}
if ($agency_name === '' && $agency_id) {
    $agency_name = get_the_title($agency_id);
}
if ($agency_name === '') {
    $agency_name = 'Agencia';
}

$format_limit = function ($value) {
    if ($value === null || $value === '' || $value === '—') {
        return '—';
    }
    if (is_numeric($value) && intval($value) === -1) {
        return '∞';
    }
    return $value;
};

$plan = $agency_field('agency_plan', '—');
$license_status = $agency_field('agency_license_status', '—');
$expiry_date = $agency_field('agency_expiry_date', '—');

$limit_properties = $format_limit($agency_field('agency_limit_properties', '—'));
$limit_agents = $format_limit($agency_field('agency_limit_agents', '—'));
$limit_ai = $format_limit($agency_field('agency_limit_ai_generations', '—'));

$total_properties = $agency_field('agency_total_properties', '—');
$total_agents = $agency_field('agency_total_agents', '—');
$ai_usage = $agency_field('agency_ai_usage_current', '—');

$active_properties = $agency_field('agency_active_properties', '—');
$total_clients = $agency_field('agency_total_clients', '—');
$last_activity = $agency_field('agency_last_activity', '—');

$agency_edit_url = $agency_id ? get_edit_post_link($agency_id, '') : '#';

$counts = array(
    'properties' => (int) ($stats['inmuebles'] ?? 0),
    'leads' => (int) (wp_count_posts('impress_lead')->publish ?? 0),
    'clients' => (int) ($stats['clientes'] ?? 0),
    'opportunities' => 0,
    'agencies' => (int) (wp_count_posts('impress_agency')->publish ?? 0),
    'visits' => (int) ($stats['visitas'] ?? 0),
    'owners' => (int) ($stats['propietarios'] ?? 0),
    'transactions' => (int) (wp_count_posts('impress_transaction')->publish ?? 0),
    'events' => (int) (wp_count_posts('impress_event')->publish ?? 0),
    'agents' => (int) (wp_count_posts('impress_agent')->publish ?? 0),
);

$latest_property = get_posts(array(
    'post_type' => 'impress_property',
    'posts_per_page' => 1,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'fields' => 'ids',
));
$latest_client = get_posts(array(
    'post_type' => 'impress_client',
    'posts_per_page' => 1,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'fields' => 'ids',
));

if (!empty($latest_property) && class_exists('Inmopress_Shortcodes')) {
    $counts['opportunities'] += count(Inmopress_Shortcodes::get_opportunity_matches_for_property($latest_property[0], 5));
}
if (!empty($latest_client) && class_exists('Inmopress_Shortcodes')) {
    $counts['opportunities'] += count(Inmopress_Shortcodes::get_opportunity_matches_for_client($latest_client[0], 8));
}

$summary_cards = array(
    array(
        'title' => 'Inmuebles',
        'count' => $counts['properties'],
        'meta' => 'Inventario activo',
        'icon' => 'dashicons-admin-home',
        'color' => '#FDE047',
        'url' => Inmopress_Shortcodes::panel_url('properties'),
    ),
    array(
        'title' => 'Prospectos',
        'count' => $counts['leads'],
        'meta' => 'Leads registrados',
        'icon' => 'dashicons-megaphone',
        'color' => '#93C5FD',
        'url' => Inmopress_Shortcodes::panel_url('leads'),
    ),
    array(
        'title' => 'Clientes',
        'count' => $counts['clients'],
        'meta' => 'Clientes activos',
        'icon' => 'dashicons-admin-users',
        'color' => '#6EE7B7',
        'url' => Inmopress_Shortcodes::panel_url('clients'),
    ),
    array(
        'title' => 'Oportunidades',
        'count' => $counts['opportunities'],
        'meta' => 'Matches detectados',
        'icon' => 'dashicons-chart-line',
        'color' => '#FBCFE8',
        'url' => Inmopress_Shortcodes::panel_url('opportunities'),
    ),
    array(
        'title' => 'Agencias',
        'count' => $counts['agencies'],
        'meta' => 'Agencias en CRM',
        'icon' => 'dashicons-building',
        'color' => '#C7D2FE',
        'url' => Inmopress_Shortcodes::panel_url('agencies'),
    ),
    array(
        'title' => 'Visitas',
        'count' => $counts['visits'],
        'meta' => 'Visitas programadas',
        'icon' => 'dashicons-calendar-alt',
        'color' => '#FDBA74',
        'url' => Inmopress_Shortcodes::panel_url('visits'),
    ),
    array(
        'title' => 'Propietarios',
        'count' => $counts['owners'],
        'meta' => 'Propietarios activos',
        'icon' => 'dashicons-id',
        'color' => '#A7F3D0',
        'url' => Inmopress_Shortcodes::panel_url('owners'),
    ),
    array(
        'title' => 'Transacciones',
        'count' => $counts['transactions'],
        'meta' => 'Operaciones en curso',
        'icon' => 'dashicons-money-alt',
        'color' => '#FDE68A',
        'url' => Inmopress_Shortcodes::panel_url('transactions'),
    ),
    array(
        'title' => 'Eventos',
        'count' => $counts['events'],
        'meta' => 'Tareas y citas',
        'icon' => 'dashicons-schedule',
        'color' => '#BFDBFE',
        'url' => Inmopress_Shortcodes::panel_url('events'),
    ),
    array(
        'title' => 'Agentes',
        'count' => $counts['agents'],
        'meta' => 'Equipo activo',
        'icon' => 'dashicons-businessperson',
        'color' => '#DDD6FE',
        'url' => Inmopress_Shortcodes::panel_url('agents'),
    ),
);

$recent_properties = get_posts(array(
    'post_type' => 'impress_property',
    'posts_per_page' => 3,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
));
?>

<div class="crm-dashboard-grid">

    <!-- Left Column: Stats & Pipeline -->
    <div class="crm-left-col">

        <!-- Hero -->
        <div class="crm-dashboard-hero">
            <div>
                <div class="crm-hero-kicker">Panel comercial</div>
                <h2 class="crm-hero-title">Buenos días, <?php echo esc_html($current_user->display_name ?? ''); ?></h2>
                <p class="crm-hero-subtitle">Resumen en tiempo real de tu negocio inmobiliario.</p>
            </div>
            <div class="crm-hero-actions">
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties', array('new' => 1))); ?>" class="btn-crm primary">Nuevo inmueble</a>
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('clients', array('new' => 1))); ?>" class="btn-crm ghost">Nuevo cliente</a>
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('events', array('new' => 1))); ?>" class="btn-crm ghost">Nueva tarea</a>
            </div>
        </div>

        <!-- Búsqueda Global -->
        <div class="crm-section">
            <div class="crm-global-search-wrapper">
                <input type="text" 
                       id="inmopress-global-search" 
                       placeholder="Buscar propiedades, clientes, leads..." 
                       class="crm-search-input">
                <div id="inmopress-search-results" class="inmopress-search-results"></div>
            </div>
        </div>

        <!-- Resumen por apartado -->
        <div class="crm-section">
            <div class="crm-section-header">
                <h3 class="crm-section-title">Resumen por apartado</h3>
                <span class="crm-section-meta">Actualizado ahora</span>
            </div>
            <div class="crm-summary-grid">
                <?php foreach ($summary_cards as $card): ?>
                    <a class="crm-summary-card" href="<?php echo esc_url($card['url']); ?>" style="--summary-icon-color: <?php echo esc_attr($card['color']); ?>;">
                        <div class="crm-summary-icon">
                            <span class="dashicons <?php echo esc_attr($card['icon']); ?>"></span>
                        </div>
                        <div>
                            <div class="crm-summary-title"><?php echo esc_html($card['title']); ?></div>
                            <div class="crm-summary-count"><?php echo esc_html($card['count']); ?></div>
                            <div class="crm-summary-meta"><?php echo esc_html($card['meta']); ?></div>
                        </div>
                        <div class="crm-summary-link">Ver</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Stats Row -->
        <div class="crm-stats-row">
            <!-- Commission -->
            <div class="crm-card stat-card">
                <div class="stat-card-header">
                    <div class="icon-circle" style="background: #ECFDF5; color: #10B981;">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <?php if (isset($stats['new_properties']) && $stats['new_properties'] > 0): ?>
                    <span class="badge badge-success small">+<?php echo $stats['new_properties']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="stat-label">Comisión total</div>
                <div class="stat-value">
                    €<?php echo number_format(isset($stats['commission_total']) ? $stats['commission_total'] : 0, 0, ',', '.'); ?>
                </div>
            </div>

            <!-- Visits -->
            <div class="crm-card stat-card">
                <div class="stat-card-header">
                    <div class="icon-circle" style="background: #EFF6FF; color: #3B82F6;">
                        <span class="dashicons dashicons-visibility"></span>
                    </div>
                    <?php if (isset($stats['new_clients']) && $stats['new_clients'] > 0): ?>
                    <span class="badge badge-warning small">+<?php echo $stats['new_clients']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="stat-label">Visitas pendientes</div>
                <div class="stat-value">
                    <?php echo number_format($stats['visitas']); ?>
                </div>
            </div>

            <!-- Oportunidades -->
            <div class="crm-card stat-card">
                <div class="stat-card-header">
                    <div class="icon-circle" style="background: #FFF7ED; color: #F97316;">
                        <span class="dashicons dashicons-portfolio"></span>
                    </div>
                </div>
                <div class="stat-label">Oportunidades</div>
                <div class="stat-value">
                    <?php echo number_format(isset($stats['opportunities']) ? $stats['opportunities'] : 0); ?>
                </div>
            </div>

            <!-- Conversión -->
            <div class="crm-card stat-card">
                <div class="stat-card-header">
                    <div class="icon-circle" style="background: #F3E8FF; color: #8B5CF6;">
                        <span class="dashicons dashicons-star-filled"></span>
                    </div>
                </div>
                <div class="stat-label">Tasa de conversión</div>
                <div class="stat-value">
                    <?php echo isset($stats['conversion_rate']) ? $stats['conversion_rate'] : 0; ?>%
                </div>
            </div>
        </div>

        <!-- Gráficas -->
        <div class="crm-section">
            <h3 class="crm-section-title" style="margin-bottom: var(--spacing-lg);">Actividad Reciente</h3>
            <div class="crm-charts-grid">
                <div class="crm-card crm-chart-container">
                    <canvas id="inmopress-chart-activity"></canvas>
                </div>
                <div class="crm-card crm-chart-container">
                    <canvas id="inmopress-chart-operations"></canvas>
                </div>
            </div>
        </div>

        <!-- Pipeline Section -->
        <div class="crm-section">
            <div class="crm-section-header">
                <h3 class="crm-section-title">Embudo de Prospectos</h3>
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('leads')); ?>" class="crm-section-link">Ver todos</a>
            </div>

            <div class="crm-pipeline-grid">
                <!-- Nuevos -->
                <div class="crm-card pipeline-card">
                    <div class="pipeline-header">
                        <span class="pipeline-label">Nuevos</span>
                        <span class="pipeline-indicator" style="background: #3B82F6;"></span>
                    </div>
                    <div class="pipeline-value">
                        <?php echo $stats['clientes']; ?>
                    </div>
                    <div class="pipeline-meta">+3 hoy</div>
                </div>

                <!-- Contactados -->
                <div class="crm-card pipeline-card">
                    <div class="pipeline-header">
                        <span class="pipeline-label">Contactados</span>
                        <span class="pipeline-indicator" style="background: #F59E0B;"></span>
                    </div>
                    <div class="pipeline-value">8</div>
                    <div class="pipeline-meta">Esperando respuesta</div>
                </div>

                <!-- Visitas -->
                <div class="crm-card pipeline-card">
                    <div class="pipeline-header">
                        <span class="pipeline-label">Visitas</span>
                        <span class="pipeline-indicator" style="background: #FDE047;"></span>
                    </div>
                    <div class="pipeline-value">5</div>
                    <div class="pipeline-meta">Alta prioridad</div>
                </div>

                <!-- Oferta -->
                <div class="crm-card pipeline-card">
                    <div class="pipeline-header">
                        <span class="pipeline-label">Oferta</span>
                        <span class="pipeline-indicator" style="background: #10B981;"></span>
                    </div>
                    <div class="pipeline-value">2</div>
                    <div class="pipeline-meta">En negociación</div>
                </div>
            </div>
        </div>

        <!-- Propiedades recientes -->
        <div class="crm-section">
            <div class="crm-section-header">
                <h3 class="crm-section-title">Propiedades recientes</h3>
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties')); ?>" class="crm-section-link">Ver todas</a>
            </div>
            <?php if (!empty($recent_properties)): ?>
                <div class="crm-recent-properties-grid">
                    <?php foreach ($recent_properties as $property): ?>
                        <?php
                        $property_id = $property->ID;
                        $ref = function_exists('get_field') ? get_field('referencia', $property_id) : '';
                        $price = function_exists('get_field') ? get_field('precio_venta', $property_id) : '';
                        if (!$price && function_exists('get_field')) {
                            $price = get_field('precio_alquiler', $property_id);
                        }
                        $price_label = $price ? number_format($price, 0, ',', '.') . ' €' : '—';
                        $thumb = get_the_post_thumbnail_url($property_id, 'medium');
                        ?>
                        <div class="crm-card crm-property-card">
                            <div class="crm-property-image">
                                <?php if ($thumb): ?>
                                    <img src="<?php echo esc_url($thumb); ?>" alt="">
                                <?php endif; ?>
                            </div>
                            <div class="crm-property-title"><?php echo esc_html(get_the_title($property_id)); ?></div>
                            <div class="crm-property-ref"><?php echo esc_html($ref ?: 'Sin referencia'); ?></div>
                            <div class="crm-property-footer">
                                <span class="crm-property-price"><?php echo esc_html($price_label); ?></span>
                                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties', array('edit' => $property_id))); ?>" class="btn-crm ghost small">Abrir</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="crm-recent-props-placeholder">
                    No hay propiedades recientes
                </div>
            <?php endif; ?>
        </div>

        <!-- Agencias, Promociones y Tareas (placeholders) -->
        <div class="crm-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Agencias, Promociones y Tareas</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div class="crm-card">
                    <div style="font-size: 14px; color: #6B7280; margin-bottom: 6px;">Agencias</div>
                    <div style="font-size: 18px; font-weight: 700; margin-bottom: 10px;"><?php echo esc_html($agency_name); ?></div>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 10px; font-size: 12px; color: #6B7280;">
                        <div>
                            <div style="font-weight: 600; color: #111827; margin-bottom: 4px;">Estado de Suscripción</div>
                            <div>Plan: <strong><?php echo esc_html($plan); ?></strong></div>
                            <div>Licencia: <strong><?php echo esc_html($license_status); ?></strong></div>
                            <div>Renueva: <strong><?php echo esc_html($expiry_date); ?></strong></div>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: #111827; margin-bottom: 4px;">Uso de Recursos</div>
                            <div>Propiedades: <strong><?php echo esc_html($total_properties); ?></strong> / <?php echo esc_html($limit_properties); ?></div>
                            <div>Agentes: <strong><?php echo esc_html($total_agents); ?></strong> / <?php echo esc_html($limit_agents); ?></div>
                            <div>IA: <strong><?php echo esc_html($ai_usage); ?></strong> / <?php echo esc_html($limit_ai); ?></div>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: #111827; margin-bottom: 4px;">Métricas Rápidas</div>
                            <div>Publicadas: <strong><?php echo esc_html($active_properties); ?></strong></div>
                            <div>Clientes: <strong><?php echo esc_html($total_clients); ?></strong></div>
                            <div>Última actividad: <strong><?php echo esc_html($last_activity); ?></strong></div>
                        </div>
                    </div>
                    <div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="<?php echo esc_url($agency_edit_url); ?>" class="btn-crm ghost" style="padding: 6px 12px; font-size: 12px;">Editar Agencia</a>
                        <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('agents')); ?>" class="btn-crm ghost" style="padding: 6px 12px; font-size: 12px;">Gestionar Agentes</a>
                        <button type="button" class="btn-crm ghost is-disabled" style="padding: 6px 12px; font-size: 12px;" disabled>Integraciones (Próximamente)</button>
                    </div>
                </div>

                <div class="crm-card">
                    <div style="font-size: 14px; color: #6B7280; margin-bottom: 6px;">Promociones</div>
                    <div style="font-size: 18px; font-weight: 700; margin-bottom: 10px;">Campañas activas</div>
                    <div style="font-size: 12px; color: #9CA3AF; margin-bottom: 14px;">Pendiente de definir fuente y estado.</div>
                    <button type="button" class="btn-crm ghost is-disabled" style="padding: 6px 12px; font-size: 12px;" disabled>Configurar (Próximamente)</button>
                </div>

                <div class="crm-card">
                    <div style="font-size: 14px; color: #6B7280; margin-bottom: 6px;">Tareas</div>
                    <div style="font-size: 18px; font-weight: 700; margin-bottom: 10px;">Pendientes del día</div>
                    <div style="font-size: 12px; color: #9CA3AF; margin-bottom: 14px;">Gestiona tus tareas y eventos del día.</div>
                    <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('events')); ?>" class="btn-crm ghost" style="padding: 6px 12px; font-size: 12px;">Ver tareas</a>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Column: Tasks -->
    <div class="crm-right-col">
        <?php echo do_shortcode('[inmopress_today_tasks limit="6"]'); ?>
    </div>
</div>
