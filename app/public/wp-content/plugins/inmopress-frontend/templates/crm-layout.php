<?php
if (!defined('ABSPATH')) exit;

// Get current user data
$current_user = wp_get_current_user();
$current_url = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Determine active items
$menu_items = array(
    'dashboard' => array('label' => 'Panel', 'icon' => 'dashicons-dashboard', 'url' => Inmopress_Shortcodes::panel_url()),
    'properties' => array('label' => 'Inmuebles', 'icon' => 'dashicons-admin-home', 'url' => Inmopress_Shortcodes::panel_url('properties')),
    'leads' => array('label' => 'Prospectos', 'icon' => 'dashicons-megaphone', 'url' => Inmopress_Shortcodes::panel_url('leads')),
    'clients' => array('label' => 'Clientes', 'icon' => 'dashicons-admin-users', 'url' => Inmopress_Shortcodes::panel_url('clients')),
    'opportunities' => array('label' => 'Oportunidades', 'icon' => 'dashicons-chart-line', 'url' => Inmopress_Shortcodes::panel_url('opportunities')),
    'agencies' => array('label' => 'Agencias', 'icon' => 'dashicons-building', 'url' => Inmopress_Shortcodes::panel_url('agencies')),
    'agents' => array('label' => 'Agentes', 'icon' => 'dashicons-businessperson', 'url' => Inmopress_Shortcodes::panel_url('agents')),
    'visits' => array('label' => 'Visitas', 'icon' => 'dashicons-calendar-alt', 'url' => Inmopress_Shortcodes::panel_url('visits')),
    'owners' => array('label' => 'Propietarios', 'icon' => 'dashicons-id', 'url' => Inmopress_Shortcodes::panel_url('owners')),
    'transactions' => array('label' => 'Transacciones', 'icon' => 'dashicons-money-alt', 'url' => Inmopress_Shortcodes::panel_url('transactions')),
    'events' => array('label' => 'Eventos', 'icon' => 'dashicons-schedule', 'url' => Inmopress_Shortcodes::panel_url('events')),
);
?>

<div class="inmopress-crm-wrapper">
    <!-- Sidebar -->
    <aside class="crm-sidebar">
        <div class="crm-brand">
            <div class="crm-brand-icon">
                <span class="dashicons dashicons-building"></span>
            </div>
            <span>Inmopress</span>
        </div>

        <nav class="crm-nav">
            <?php foreach ($menu_items as $key => $item): 
                $is_active = ($active_tab === $key) || ($key === 'dashboard' && empty($active_tab));
            ?>
                <a href="<?php echo esc_url($item['url']); ?>" class="crm-nav-item <?php echo $is_active ? 'active' : ''; ?>">
                    <span class="dashicons <?php echo esc_attr($item['icon']); ?>"></span>
                    <?php echo esc_html($item['label']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        
        <div class="crm-user-profile">
            <div class="crm-user-avatar">
                <?php echo get_avatar($current_user->ID, 40); ?>
            </div>
            <div class="crm-user-info">
                <span class="crm-user-name"><?php echo esc_html($current_user->display_name); ?></span>
                <span class="crm-user-role">Agente</span>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="crm-main-content">
        <!-- Top Bar -->
        <header class="crm-top-bar">
            <?php if ($active_tab === 'properties'): ?>
                 <h1>Inventario de Inmuebles</h1>
            <?php elseif ($active_tab === 'events'): ?>
                 <h1>Calendario de Eventos</h1>
            <?php elseif ($active_tab === 'transactions'): ?>
                 <h1>Transacciones</h1>
            <?php elseif ($active_tab === 'clients'): ?>
                 <h1>Clientes</h1>
            <?php elseif ($active_tab === 'agencies'): ?>
                 <h1>Agencias</h1>
            <?php elseif ($active_tab === 'leads'): ?>
                 <h1>Prospectos</h1>
            <?php elseif ($active_tab === 'opportunities'): ?>
                 <h1>Oportunidades</h1>
            <?php elseif ($active_tab === 'visits'): ?>
                 <h1>Visitas</h1>
            <?php else: ?>
                 <form class="crm-search-bar" method="get" action="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties')); ?>">
                    <span class="dashicons dashicons-search"></span>
                    <input type="text" name="s" class="crm-search-input" placeholder="Buscar por referencia o nombre...">
                </form>
            <?php endif; ?>

            <div class="crm-top-actions">
                <span class="dashicons dashicons-bell"></span>
                <?php if ($active_tab === 'properties'): ?>
                     <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties', array('new' => 1))); ?>" class="btn-crm primary">
                        <span class="dashicons dashicons-plus"></span> Añadir Inmueble
                     </a>
                <?php elseif ($active_tab === 'dashboard' || empty($active_tab)): ?>
                     <div class="crm-date-display"><?php echo esc_html(date_i18n('l, j \\d\\e F Y')); ?></div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Dynamic Content Injection -->
        <div class="crm-content-body">
            <?php 
                if ($active_tab === 'dashboard' || empty($active_tab)) {
                    // Include the Stats/Pipeline/Agenda Dashboard
                    // We need to ensure variables are passed. They are available in the scope if included.
                    // But explicitly calling getters is safer if we are in a pure include context.
                    if (!isset($stats)) $stats = Inmopress_Shortcodes::get_dashboard_stats(); 
                    if (!isset($recent_activity)) $recent_activity = Inmopress_Shortcodes::get_recent_activity();
                    
                    include INMOPRESS_FRONTEND_PATH . 'templates/crm-dashboard-home.php';
                }
                elseif ($active_tab === 'properties') {
                    if (isset($_GET['edit']) || isset($_GET['new']) || isset($_GET['property_id'])) {
                        echo do_shortcode('[inmopress_inmueble_form]');
                    } else {
                        echo do_shortcode('[inmopress_inmuebles_list]');
                    }
                }
                elseif ($active_tab === 'leads' || $active_tab === 'clients') {
                    if (isset($_GET['edit']) || isset($_GET['new'])) {
                        $return_tab = ($active_tab === 'leads') ? 'leads' : 'clients';
                        $return_url = esc_url_raw(Inmopress_Shortcodes::panel_url($return_tab));
                        echo do_shortcode('[inmopress_cliente_form return="' . $return_url . '"]');
                    } else {
                        $context = ($active_tab === 'leads') ? 'leads' : 'clients';
                        echo do_shortcode('[inmopress_clientes_list context="' . esc_attr($context) . '"]');
                    }
                }
                elseif ($active_tab === 'agencies') {
                    $current_user_id = get_current_user_id();
                    $agency_id = Inmopress_Shortcodes::get_agency_id_by_user($current_user_id);
                    $is_admin = current_user_can('administrator');

                    $args = array(
                        'post_type'      => 'impress_agency',
                        'posts_per_page' => $is_admin ? 10 : 1,
                        'post__in'       => (!$is_admin && $agency_id) ? array($agency_id) : array(),
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                    );

                    if (!$is_admin && !$agency_id) {
                        echo '<div class="crm-card">No tienes una agencia asignada.</div>';
                    } else {
                        $agencies = get_posts($args);
                        echo '<div class="crm-card">';
                        echo '<h3 class="crm-card-header-title">Listado de Agencias</h3>';
                        if (empty($agencies)) {
                            echo '<p class="crm-empty-message">No hay agencias para mostrar.</p>';
                        } else {
                            echo '<div class="crm-agencies-list">';
                            foreach ($agencies as $agency) {
                                $agency_name = get_the_title($agency->ID);
                                if (function_exists('get_field')) {
                                    $acf_name = get_field('agency_name', $agency->ID);
                                    if (empty($acf_name)) {
                                        $acf_name = get_field('nombre_comercial', $agency->ID);
                                    }
                                    if (!empty($acf_name)) {
                                        $agency_name = $acf_name;
                                    }
                                }

                                $plan = function_exists('get_field') ? get_field('agency_plan', $agency->ID) : get_post_meta($agency->ID, 'agency_plan', true);
                                $status = function_exists('get_field') ? get_field('agency_license_status', $agency->ID) : get_post_meta($agency->ID, 'agency_license_status', true);
                                $email = function_exists('get_field') ? get_field('agency_email_main', $agency->ID) : get_post_meta($agency->ID, 'agency_email_main', true);
                                if (empty($email)) {
                                    $email = function_exists('get_field') ? get_field('email', $agency->ID) : get_post_meta($agency->ID, 'email', true);
                                }

                                $edit_url = get_edit_post_link($agency->ID, '');
                                
                                // Contar agentes de esta agencia
                                $agents_query = new WP_Query(array(
                                    'post_type' => 'impress_agent',
                                    'posts_per_page' => -1,
                                    'meta_query' => array(
                                        array(
                                            'key' => 'agencia_relacionada',
                                            'value' => $agency->ID,
                                            'compare' => '=',
                                        ),
                                    ),
                                    'fields' => 'ids',
                                ));
                                $agents_count = $agents_query->found_posts;
                                $agents_url = Inmopress_Shortcodes::panel_url('agents', array('agency' => $agency->ID));

                                echo '<div class="crm-agency-item">';
                                echo '<div class="crm-agency-main"><strong>' . esc_html($agency_name) . '</strong><div class="crm-agency-email">' . esc_html($email ?: '—') . '</div></div>';
                                echo '<div class="crm-agency-info">Plan: <strong>' . esc_html($plan ?: '—') . '</strong></div>';
                                echo '<div class="crm-agency-info">Licencia: <strong>' . esc_html($status ?: '—') . '</strong></div>';
                                echo '<div class="crm-agency-info">';
                                echo '<a href="' . esc_url($agents_url) . '" class="crm-agency-agents-link">';
                                echo '<span class="dashicons dashicons-groups"></span> ';
                                echo esc_html(sprintf(_n('%d agente', '%d agentes', $agents_count, 'inmopress'), $agents_count));
                                echo '</a>';
                                echo '</div>';
                                echo '<div class="crm-agency-actions">';
                                if ($edit_url) {
                                    echo '<a href="' . esc_url($edit_url) . '" class="btn-crm ghost small">Editar</a> ';
                                }
                                if ($agents_count > 0) {
                                    echo '<a href="' . esc_url($agents_url) . '" class="btn-crm ghost small">Ver Agentes</a>';
                                }
                                echo '</div>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                }
                elseif ($active_tab === 'visits') {
                    if (isset($_GET['edit']) || isset($_GET['new'])) {
                        echo do_shortcode('[inmopress_visita_form]');
                    } else {
                        echo do_shortcode('[inmopress_visitas_list]');
                    }
                }
                elseif ($active_tab === 'owners') {
                    if (isset($_GET['edit']) || isset($_GET['new'])) {
                        echo do_shortcode('[inmopress_propietario_form]');
                    } else {
                        echo do_shortcode('[inmopress_propietarios_list]');
                    }
                }
                elseif ($active_tab === 'transactions') {
                     if (isset($_GET['edit']) || isset($_GET['new'])) {
                        echo do_shortcode('[inmopress_transaction_form]');
                     } else {
                        echo do_shortcode('[inmopress_transactions_list]');
                     }
                }
                elseif ($active_tab === 'events') {
                    if (isset($_GET['edit']) || isset($_GET['new'])) {
                        echo do_shortcode('[inmopress_event_form]');
                    } else {
                        echo do_shortcode('[inmopress_events_list]');
                    }
                }
                elseif ($active_tab === 'opportunities') {
                    include INMOPRESS_FRONTEND_PATH . 'templates/crm-opportunities.php';
                }
                elseif ($active_tab === 'agents') {
                    if (isset($_GET['edit']) || isset($_GET['new'])) {
                        echo do_shortcode('[inmopress_agent_form]');
                    } else {
                        echo do_shortcode('[inmopress_agents_list]');
                    }
                }
                else {
                    echo 'Content not found.';
                }
            ?>
        </div>
    </main>
</div>
