<?php
/**
 * Plugin Name: Inmopress Frontend
 * Description: Panel frontend con shortcodes para Inmopress
 * Version: 1.0.0
 * Author: Inmopress
 */

if (!defined('ABSPATH'))
    exit;

define('INMOPRESS_FRONTEND_PATH', plugin_dir_path(__FILE__));
define('INMOPRESS_FRONTEND_URL', plugin_dir_url(__FILE__));
define('INMOPRESS_FRONTEND_VERSION', '1.0.0');

class Inmopress_Frontend
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies()
    {
        require_once INMOPRESS_FRONTEND_PATH . 'includes/class-acf-forms.php';
        require_once INMOPRESS_FRONTEND_PATH . 'includes/class-shortcodes.php';
        require_once INMOPRESS_FRONTEND_PATH . 'includes/class-events-ajax.php';
        require_once INMOPRESS_FRONTEND_PATH . 'includes/class-page-generator.php';
        require_once INMOPRESS_FRONTEND_PATH . 'includes/class-dashboard-kpis.php';
    }

    private function init_hooks()
    {
        // Registrar shortcodes
        add_action('init', array('Inmopress_Shortcodes', 'register'));
        add_action('init', array('Inmopress_Events_Ajax', 'init'));

        // Ocultar admin bar y sidebar de WordPress en el dashboard
        add_filter('show_admin_bar', array($this, 'hide_admin_bar_in_dashboard'));
        add_action('admin_head', array($this, 'hide_wp_sidebar_in_dashboard'));

        // Enqueue estilos
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));

        // Generar títulos automáticamente para clientes
        add_action('acf/save_post', array($this, 'auto_generate_client_title'), 20);

        // Generar títulos automáticamente para visitas
        add_action('acf/save_post', array($this, 'auto_generate_visit_title'), 20);
        // Generar títulos automáticamente para eventos
        add_action('acf/save_post', array($this, 'auto_generate_event_title'), 20);
        // Generar títulos automáticamente para agentes
        add_action('acf/save_post', array($this, 'auto_generate_agent_title'), 20);

        // Asignar agente actual si es agente
        add_action('acf/save_post', array($this, 'auto_assign_current_agent'), 10);
        // Asignar creador en eventos
        add_action('acf/save_post', array($this, 'auto_assign_event_creator'), 15);
        add_action('acf/save_post', array($this, 'save_property_taxonomies_and_thumbnail'), 30);
        add_action('acf/save_post', array($this, 'save_agent_taxonomies'), 30);

        add_filter('acf/load_value/name=impress_event_start', array($this, 'prefill_event_start'), 10, 3);
        add_filter('acf/load_value/name=impress_event_end', array($this, 'prefill_event_end'), 10, 3);

        // Panel en admin
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_init', array($this, 'maybe_acf_form_head'));

        // Redirigir el panel frontend a admin
        add_action('template_redirect', array($this, 'maybe_redirect_panel_page'));

        // Acciones rápidas de inmuebles desde el panel
        add_action('admin_post_inmopress_property_action', array($this, 'handle_property_action'));

        // Acciones de oportunidades (matching)
        add_action('admin_post_inmopress_opportunity_action', array($this, 'handle_opportunity_action'));

        // AJAX handlers
        add_action('wp_ajax_inmopress_global_search', array($this, 'ajax_global_search'));
    }

    public function prefill_event_start($value, $post_id, $field)
    {
        if ($post_id !== 'new_post' || !empty($value)) {
            return $value;
        }

        if (empty($_GET['start'])) {
            return $value;
        }

        $start = sanitize_text_field(wp_unslash($_GET['start']));
        $timestamp = strtotime($start);
        if (!$timestamp) {
            return $value;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    public function prefill_event_end($value, $post_id, $field)
    {
        if ($post_id !== 'new_post' || !empty($value)) {
            return $value;
        }

        if (empty($_GET['start'])) {
            return $value;
        }

        $start = sanitize_text_field(wp_unslash($_GET['start']));
        $timestamp = strtotime($start);
        if (!$timestamp) {
            return $value;
        }

        return date('Y-m-d H:i:s', $timestamp + 1800);
    }

    /**
     * Enqueue estilos del panel frontend
     */
    public function enqueue_styles()
    {
        // Solo cargar en páginas del panel
        if (
            is_page() && (
                is_page('mi-panel') ||
                is_page('inmuebles') ||
                is_page('clientes') ||
                is_page('visitas') ||
                is_page('propietarios') ||
                is_page('nuevo-inmueble') ||
                is_page('editar-inmueble') ||
                is_page('nuevo-cliente') ||
                is_page('editar-cliente') ||
                is_page('nueva-visita') ||
                is_page('nuevo-propietario')
            )
        ) {
            wp_enqueue_style(
                'inmopress-frontend',
                plugin_dir_url(__FILE__) . 'assets/css/frontend.css',
                array(),
                INMOPRESS_FRONTEND_VERSION
            );
        }
    }

    /**
     * Ocultar admin bar de WordPress cuando estamos en el dashboard
     */
    public function hide_admin_bar_in_dashboard($show)
    {
        if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'inmopress-panel') {
            return false;
        }
        return $show;
    }

    /**
     * Ocultar sidebar de WordPress cuando estamos en el dashboard
     */
    public function hide_wp_sidebar_in_dashboard()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'inmopress-panel') {
            return;
        }
        ?>
        <style>
            /* Ocultar sidebar de WordPress */
            #wpcontent {
                margin-left: 0 !important;
            }
            
            #adminmenuback,
            #adminmenuwrap,
            #adminmenu {
                display: none !important;
            }
            
            /* Ocultar admin bar superior */
            #wpadminbar {
                display: none !important;
            }
            
            /* Ajustar contenido para ocupar todo el ancho */
            .inmopress-admin-panel {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            
            /* Ocultar notices de WordPress que puedan aparecer */
            .wrap {
                margin: 0;
            }
            
            /* Asegurar que el wrapper ocupe toda la pantalla */
            #wpbody-content {
                padding: 0;
                margin: 0;
            }
            
            /* Ocultar cualquier elemento del header de WordPress admin */
            #wpbody-content > .wrap:first-child {
                display: none;
            }
        </style>
        <?php
    }

    /**
     * Enqueue estilos del panel en admin
     */
    public function enqueue_admin_styles($hook)
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'inmopress-panel') {
            return;
        }

        // Nuevo sistema de diseño CSS modular
        wp_enqueue_style(
            'inmopress-dashboard',
            plugin_dir_url(__FILE__) . 'assets/css/dashboard.css',
            array(),
            INMOPRESS_FRONTEND_VERSION
        );

        // Mantener estilos legacy por compatibilidad (se pueden deprecar después)
        wp_enqueue_style(
            'inmopress-crm-styles',
            plugin_dir_url(__FILE__) . 'assets/css/crm-styles.css',
            array('inmopress-dashboard'),
            INMOPRESS_FRONTEND_VERSION
        );

        wp_enqueue_style(
            'inmopress-frontend',
            plugin_dir_url(__FILE__) . 'assets/css/frontend.css',
            array('inmopress-dashboard'),
            INMOPRESS_FRONTEND_VERSION
        );

        // Cargar JavaScript del sidebar para mobile toggle
        wp_enqueue_script(
            'inmopress-sidebar',
            plugin_dir_url(__FILE__) . 'assets/js/sidebar.js',
            array(),
            INMOPRESS_FRONTEND_VERSION,
            true
        );

        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        // ACF assets para formularios dentro del panel
        if (function_exists('acf_enqueue_scripts')) {
            acf_enqueue_scripts();
        }
        if (function_exists('acf_enqueue_uploader')) {
            acf_enqueue_uploader();
        }

        if (isset($_GET['tab']) && $_GET['tab'] === 'events') {
            wp_enqueue_script(
                'inmopress-crm-calendar',
                plugin_dir_url(__FILE__) . 'assets/js/crm-calendar.js',
                array(),
                INMOPRESS_FRONTEND_VERSION,
                true
            );

            wp_localize_script('inmopress-crm-calendar', 'inmopressCalendar', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('inmopress_events_nonce'),
                'newBaseUrl' => Inmopress_Shortcodes::panel_url('events', array('new' => 1, 'view' => 'calendar')),
                'editBaseUrl' => Inmopress_Shortcodes::panel_url('events', array('view' => 'calendar')),
            ));
        }

        // Cargar Chart.js y scripts del dashboard en la pestaña dashboard
        if (!isset($_GET['tab']) || $_GET['tab'] === 'dashboard') {
            wp_enqueue_script(
                'chart-js',
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
                array(),
                '4.4.0',
                true
            );

            wp_enqueue_script(
                'inmopress-dashboard',
                plugin_dir_url(__FILE__) . 'assets/js/dashboard.js',
                array('chart-js', 'jquery'),
                INMOPRESS_FRONTEND_VERSION,
                true
            );

            // Pasar datos de KPIs al JavaScript
            $user_id = get_current_user_id();
            $agency_id = class_exists('Inmopress_Shortcodes') ? Inmopress_Shortcodes::get_agency_id_by_user($user_id) : null;
            $kpis = Inmopress_Dashboard_KPIs::get_kpis($user_id, $agency_id);
            $chart_data = Inmopress_Dashboard_KPIs::get_chart_data('30days', $user_id, $agency_id);
            $operations_data = Inmopress_Dashboard_KPIs::get_operations_data($user_id, $agency_id);

            wp_localize_script('inmopress-dashboard', 'inmopressDashboard', array(
                'kpis' => $kpis,
                'chartData' => $chart_data,
                'operationsData' => $operations_data,
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('inmopress_dashboard_nonce'),
            ));
        }
    }

    /**
     * Registrar el panel CRM en el dashboard de WordPress
     */
    public function register_admin_menu()
    {
        add_menu_page(
            __('Inmopress CRM', 'inmopress'),
            __('Inmopress', 'inmopress'),
            'read',
            'inmopress-panel',
            array($this, 'render_admin_panel'),
            'dashicons-building',
            3
        );
    }

    /**
     * Render del panel en admin
     */
    public function render_admin_panel()
    {
        if (!current_user_can('read')) {
            wp_die(esc_html__('No tienes permisos para ver este panel.', 'inmopress'));
        }

        echo '<div class="inmopress-admin-panel">';
        echo Inmopress_Shortcodes::dashboard(array());
        echo '</div>';
    }

    /**
     * Ejecutar acf_form_head en admin cuando se muestran formularios
     */
    public function maybe_acf_form_head()
    {
        if (!is_admin()) {
            return;
        }

        if (!isset($_GET['page']) || $_GET['page'] !== 'inmopress-panel') {
            return;
        }

        $this->ensure_acf_field_groups();

        if (function_exists('acf_form_head') && !did_action('acf/form_head')) {
            acf_form_head();
        }
    }

    /**
     * Asegura que los field groups de ACF existen
     */
    private function ensure_acf_field_groups()
    {
        if (!function_exists('acf_get_field_group')) {
            return;
        }

        if (!class_exists('Inmopress_ACF_Fields')) {
            return;
        }

        // Si no existe un grupo clave de inmuebles, crear todos.
        if (!acf_get_field_group('group_property_info')) {
            Inmopress_ACF_Fields::create_all_field_groups();
        }
    }

    /**
     * Guardar taxonomías y imagen destacada desde el panel
     *
     * @param int|string $post_id
     */
    public function save_property_taxonomies_and_thumbnail($post_id)
    {
        if (!is_admin()) {
            return;
        }

        if (!isset($_GET['page']) || $_GET['page'] !== 'inmopress-panel') {
            return;
        }

        if (get_post_type($post_id) !== 'impress_property') {
            return;
        }

        if (!isset($_POST['inmopress_property_nonce']) || !wp_verify_nonce($_POST['inmopress_property_nonce'], 'inmopress_property_form')) {
            return;
        }

        // Imagen destacada
        if (isset($_POST['inmopress_featured_image_id'])) {
            $featured_id = absint($_POST['inmopress_featured_image_id']);
            if ($featured_id) {
                set_post_thumbnail($post_id, $featured_id);
            } else {
                delete_post_thumbnail($post_id);
            }
        }

        // Taxonomías
        $tax_fields = apply_filters('inmopress_property_tax_fields', array(
            'impress_province' => array('type' => 'single'),
            'impress_city' => array('type' => 'single'),
            'impress_operation' => array('type' => 'single'),
            'impress_property_type' => array('type' => 'single'),
            'impress_property_group' => array('type' => 'single'),
            'impress_category' => array('type' => 'single'),
            'impress_status' => array('type' => 'single'),
            'impress_condition' => array('type' => 'single'),
            'impress_energy_rating' => array('type' => 'single'),
            'impress_orientation' => array('type' => 'single'),
            'impress_heating' => array('type' => 'single'),
            'impress_features' => array('type' => 'multi'),
            'impress_amenities' => array('type' => 'multi'),
        ));

        $submitted = isset($_POST['inmopress_tax']) && is_array($_POST['inmopress_tax']) ? $_POST['inmopress_tax'] : array();
        foreach ($tax_fields as $taxonomy => $config) {
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }
            $type = isset($config['type']) ? $config['type'] : 'single';
            $value = isset($submitted[$taxonomy]) ? $submitted[$taxonomy] : ($type === 'multi' ? array() : '');

            if ($type === 'multi') {
                $term_ids = array_map('absint', is_array($value) ? $value : array());
            } else {
                $term_ids = $value !== '' ? array(absint($value)) : array();
            }

            wp_set_object_terms($post_id, $term_ids, $taxonomy, false);
        }
    }

    /**
     * Guardar taxonomías de agentes
     */
    public function save_agent_taxonomies($post_id)
    {
        if (!is_admin()) {
            return;
        }

        if (!isset($_GET['page']) || $_GET['page'] !== 'inmopress-panel') {
            return;
        }

        if (get_post_type($post_id) !== 'impress_agent') {
            return;
        }

        if (!isset($_POST['inmopress_agent_nonce']) || !wp_verify_nonce($_POST['inmopress_agent_nonce'], 'inmopress_agent_form')) {
            return;
        }

        // Taxonomías de agentes
        $tax_fields = apply_filters('inmopress_agent_tax_fields', array(
            'impress_agent_specialty' => array('type' => 'multi'),
        ));

        $submitted = isset($_POST['inmopress_tax']) && is_array($_POST['inmopress_tax']) ? $_POST['inmopress_tax'] : array();
        foreach ($tax_fields as $taxonomy => $config) {
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }
            $type = isset($config['type']) ? $config['type'] : 'single';
            $value = isset($submitted[$taxonomy]) ? $submitted[$taxonomy] : ($type === 'multi' ? array() : '');

            if ($type === 'multi') {
                $term_ids = array_map('absint', is_array($value) ? $value : array());
            } else {
                $term_ids = $value !== '' ? array(absint($value)) : array();
            }

            wp_set_object_terms($post_id, $term_ids, $taxonomy, false);
        }
    }

    /**
     * Redirigir /mi-panel/ al dashboard de WordPress
     */
    public function maybe_redirect_panel_page()
    {
        if (!is_page()) {
            return;
        }

        if (!is_user_logged_in()) {
            auth_redirect();
        }

        $page = get_queried_object();
        if (!($page instanceof WP_Post)) {
            return;
        }

        $slug = $page->post_name;
        $map = array(
            'mi-panel' => array('tab' => 'dashboard'),
            'inmuebles' => array('tab' => 'properties'),
            'nuevo-inmueble' => array('tab' => 'properties', 'new' => 1),
            'editar-inmueble' => array('tab' => 'properties'),
            'clientes' => array('tab' => 'clients'),
            'nuevo-cliente' => array('tab' => 'clients', 'new' => 1),
            'editar-cliente' => array('tab' => 'clients'),
            'visitas' => array('tab' => 'visits'),
            'nueva-visita' => array('tab' => 'visits', 'new' => 1),
            'propietarios' => array('tab' => 'owners'),
            'nuevo-propietario' => array('tab' => 'owners', 'new' => 1),
            'transacciones' => array('tab' => 'transactions'),
            'nueva-transaccion' => array('tab' => 'transactions', 'new' => 1),
        );

        if (!isset($map[$slug])) {
            return;
        }

        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : $map[$slug]['tab'];
        $url = admin_url('admin.php?page=inmopress-panel');

        if (!empty($tab) && $tab !== 'dashboard') {
            $url = add_query_arg('tab', $tab, $url);
        }
        if (isset($_GET['edit'])) {
            $url = add_query_arg('edit', absint($_GET['edit']), $url);
        } elseif (!empty($map[$slug]['new'])) {
            $url = add_query_arg('new', 1, $url);
        }
        if (isset($_GET['new'])) {
            $url = add_query_arg('new', 1, $url);
        }

        wp_safe_redirect($url);
        exit;
    }

    /**
     * Acciones rápidas para inmuebles desde el panel
     */
    public function handle_property_action()
    {
        if (!is_user_logged_in()) {
            auth_redirect();
        }

        $property_id = isset($_GET['property_id']) ? absint($_GET['property_id']) : 0;
        $action_type = isset($_GET['action_type']) ? sanitize_key($_GET['action_type']) : '';

        if (!$property_id || !$action_type) {
            wp_safe_redirect(Inmopress_Shortcodes::panel_url('properties'));
            exit;
        }

        if (!current_user_can('edit_post', $property_id)) {
            wp_die(esc_html__('No tienes permisos para modificar este inmueble.', 'inmopress'));
        }

        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'inmopress_property_action_' . $property_id)) {
            wp_die(esc_html__('Nonce inválido. Recarga la página e inténtalo de nuevo.', 'inmopress'));
        }

        if (get_post_type($property_id) !== 'impress_property') {
            wp_safe_redirect(Inmopress_Shortcodes::panel_url('properties'));
            exit;
        }

        switch ($action_type) {
            case 'draft':
                wp_update_post(array(
                    'ID' => $property_id,
                    'post_status' => 'draft',
                ));
                break;
            case 'delete':
                wp_trash_post($property_id);
                break;
            case 'featured':
                $current = (int) get_post_meta($property_id, '_inmopress_destacada', true);
                if ($current) {
                    delete_post_meta($property_id, '_inmopress_destacada');
                } else {
                    update_post_meta($property_id, '_inmopress_destacada', 1);
                }
                break;
            case 'kyero':
                $current = (int) get_post_meta($property_id, '_inmopress_kyero_feed', true);
                if ($current) {
                    delete_post_meta($property_id, '_inmopress_kyero_feed');
                } else {
                    update_post_meta($property_id, '_inmopress_kyero_feed', 1);
                }
                break;
            default:
                break;
        }

        $redirect = wp_get_referer();
        if (!$redirect) {
            $redirect = Inmopress_Shortcodes::panel_url('properties');
        }
        wp_safe_redirect($redirect);
        exit;
    }

    /**
     * Acciones para oportunidades (notificar, enviar selección, descartar)
     */
    public function handle_opportunity_action()
    {
        if (!is_user_logged_in()) {
            auth_redirect();
        }

        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'inmopress_opportunity_action')) {
            wp_die(esc_html__('Nonce inválido. Recarga la página e inténtalo de nuevo.', 'inmopress'));
        }

        $action = isset($_POST['op_action']) ? sanitize_key(wp_unslash($_POST['op_action'])) : '';
        $property_id = isset($_POST['property_id']) ? absint($_POST['property_id']) : 0;
        $client_id = isset($_POST['client_id']) ? absint($_POST['client_id']) : 0;
        $client_ids = $this->parse_opportunity_ids(isset($_POST['client_ids']) ? wp_unslash($_POST['client_ids']) : '');
        $property_ids = $this->parse_opportunity_ids(isset($_POST['property_ids']) ? wp_unslash($_POST['property_ids']) : '');

        $redirect = wp_get_referer();
        if (!$redirect) {
            $redirect = Inmopress_Shortcodes::panel_url('opportunities');
        }

        $notice = 'error';
        $count = 0;

        if ($action === 'notify_all' && $property_id && !empty($client_ids)) {
            foreach ($client_ids as $cid) {
                if (!$this->can_manage_match($cid, $property_id)) {
                    continue;
                }
                if ($this->create_match_event($cid, $property_id, 'Notificar match')) {
                    $count++;
                }
            }
            $notice = $count ? 'notified' : 'no_matches';
        } elseif ($action === 'send_selection' && $client_id && !empty($property_ids)) {
            foreach ($property_ids as $pid) {
                if (!$this->can_manage_match($client_id, $pid)) {
                    continue;
                }
                if ($this->create_match_event($client_id, $pid, 'Enviar seleccion')) {
                    $count++;
                }
            }
            $notice = $count ? 'selection' : 'no_matches';
        } elseif ($action === 'dismiss') {
            $entity_type = isset($_POST['entity_type']) ? sanitize_key(wp_unslash($_POST['entity_type'])) : '';
            $entity_id = isset($_POST['entity_id']) ? absint($_POST['entity_id']) : 0;
            if ($entity_type && $entity_id) {
                $this->dismiss_opportunity($entity_type, $entity_id);
                $notice = 'dismissed';
            }
        }

        $redirect = add_query_arg(array(
            'op_notice' => $notice,
            'op_count' => $count,
        ), $redirect);

        wp_safe_redirect($redirect);
        exit;
    }

    private function parse_opportunity_ids($raw)
    {
        if (empty($raw)) {
            return array();
        }

        $parts = array_filter(array_map('trim', explode(',', $raw)));
        $ids = array();
        foreach ($parts as $part) {
            $value = absint($part);
            if ($value) {
                $ids[] = $value;
            }
        }
        return array_values(array_unique($ids));
    }

    private function can_manage_match($client_id, $property_id)
    {
        if ($client_id && !current_user_can('edit_post', $client_id)) {
            return false;
        }
        if ($property_id && !current_user_can('edit_post', $property_id)) {
            return false;
        }
        return true;
    }

    private function create_match_event($client_id, $property_id, $context = '')
    {
        $client_id = absint($client_id);
        $property_id = absint($property_id);
        if (!$client_id || !$property_id) {
            return false;
        }

        $client_name = get_the_title($client_id);
        $property_ref = get_field('referencia', $property_id);
        $property_title = get_the_title($property_id);
        $property_label = $property_ref ? $property_ref : $property_title;

        $event_id = wp_insert_post(array(
            'post_type' => 'impress_event',
            'post_status' => 'publish',
            'post_title' => sprintf('Match: %s - %s', $client_name, $property_label),
        ), true);

        if (is_wp_error($event_id)) {
            return false;
        }

        $now = current_time('Y-m-d H:i:s');
        $user_id = get_current_user_id();
        $agent_id = Inmopress_Shortcodes::get_agent_id_by_user($user_id);

        $this->set_event_field($event_id, 'impress_event_type', 'email');
        $this->set_event_field($event_id, 'impress_event_status', 'pendiente');
        $this->set_event_field($event_id, 'impress_event_priority', 'media');
        $this->set_event_field($event_id, 'impress_event_start', $now);
        $this->set_event_field($event_id, 'impress_event_end', $now);
        $this->set_event_field($event_id, 'impress_event_title', sprintf('Enviar propiedad %s', $property_label));
        $this->set_event_field($event_id, 'impress_event_notes', trim($context . ' | Cliente: ' . $client_name . ' | Propiedad: ' . $property_label));
        $this->set_event_field($event_id, 'impress_event_client_rel', $client_id);
        $this->set_event_field($event_id, 'impress_event_property_rel', $property_id);
        $this->set_event_field($event_id, 'impress_event_created_by', $user_id);
        if ($agent_id) {
            $this->set_event_field($event_id, 'impress_event_agent_rel', $agent_id);
        }

        return true;
    }

    private function set_event_field($event_id, $field, $value)
    {
        if (function_exists('update_field')) {
            update_field($field, $value, $event_id);
        } else {
            update_post_meta($event_id, $field, $value);
        }
    }

    private function dismiss_opportunity($type, $entity_id)
    {
        $user_id = get_current_user_id();
        $meta = get_user_meta($user_id, 'inmopress_opportunities_dismissed', true);
        if (!is_array($meta)) {
            $meta = array(
                'properties' => array(),
                'clients' => array(),
            );
        }

        if ($type === 'property') {
            $meta['properties'][] = $entity_id;
        } elseif ($type === 'client') {
            $meta['clients'][] = $entity_id;
        }

        $meta['properties'] = array_values(array_unique(array_filter(array_map('absint', $meta['properties']))));
        $meta['clients'] = array_values(array_unique(array_filter(array_map('absint', $meta['clients']))));

        update_user_meta($user_id, 'inmopress_opportunities_dismissed', $meta);
    }

    /**
     * Generar título automático para clientes (nombre + apellidos)
     */
    public function auto_generate_client_title($post_id)
    {
        if (get_post_type($post_id) !== 'impress_client') {
            return;
        }

        $nombre = get_field('nombre', $post_id);
        $apellidos = get_field('apellidos', $post_id);

        if ($nombre || $apellidos) {
            $title = trim(($nombre ?: '') . ' ' . ($apellidos ?: ''));
            if (!empty($title)) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $title,
                ));
            }
        }
    }

    /**
     * Generar título automático para visitas
     */
    public function auto_generate_visit_title($post_id)
    {
        if (get_post_type($post_id) !== 'impress_visit') {
            return;
        }

        $inmueble = get_field('inmueble', $post_id);
        $cliente = get_field('cliente', $post_id);
        $fecha = get_field('fecha_hora', $post_id);

        $parts = array();
        if ($inmueble) {
            $ref = get_field('referencia', $inmueble->ID);
            $parts[] = $ref ?: $inmueble->post_title;
        }
        if ($cliente) {
            $parts[] = $cliente->post_title;
        }
        if ($fecha) {
            $parts[] = $fecha;
        }

        if (!empty($parts)) {
            $title = 'Visita ' . implode(' - ', $parts);
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $title,
            ));
        }
    }

    /**
     * Asignar automáticamente el agente actual si el usuario es agente
     */
    public function auto_assign_current_agent($post_id)
    {
        if (!is_user_logged_in()) {
            return;
        }

        $user = wp_get_current_user();
        if (!in_array('agente', $user->roles)) {
            return;
        }

        // Solo para visitas, inmuebles y eventos nuevos
        if (in_array(get_post_type($post_id), array('impress_visit', 'impress_property', 'impress_event'), true)) {
            $agent_id = Inmopress_Shortcodes::get_agent_id_by_user($user->ID);
            if ($agent_id) {
                // Solo asignar si no tiene agente ya asignado
                if (get_post_type($post_id) === 'impress_event') {
                    $current_agent = get_field('impress_event_agent_rel', $post_id);
                    if (!$current_agent) {
                        update_field('impress_event_agent_rel', $agent_id, $post_id);
                    }
                } else {
                    $current_agent = get_field('agente', $post_id);
                    if (!$current_agent) {
                        update_field('agente', $agent_id, $post_id);
                    }
                }
            }
        }
    }

    /**
     * Generar título automático para eventos
     */
    public function auto_generate_event_title($post_id)
    {
        if (get_post_type($post_id) !== 'impress_event') {
            return;
        }

        $custom_title = get_field('impress_event_title', $post_id);
        $start = get_field('impress_event_start', $post_id);
        $type = get_field('impress_event_type', $post_id);

        if ($custom_title) {
            $title = $custom_title;
        } else {
            $type_labels = Inmopress_Shortcodes::get_event_type_labels();
            $type_label = $type_labels[$type] ?? 'Evento';
            $time = $start ? date_i18n('d/m/Y H:i', strtotime($start)) : '';
            $title = trim($type_label . ' ' . $time);
        }

        if (!empty($title)) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $title,
            ));
        }
    }

    /**
     * Generar título automático para agentes (nombre + apellidos)
     */
    public function auto_generate_agent_title($post_id)
    {
        if (get_post_type($post_id) !== 'impress_agent') {
            return;
        }

        $nombre = get_field('nombre', $post_id);
        $apellidos = get_field('apellidos', $post_id);

        if ($nombre || $apellidos) {
            $title = trim(($nombre ?: '') . ' ' . ($apellidos ?: ''));
            if (!empty($title)) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $title,
                ));
            }
        }
    }

    /**
     * Asignar usuario creador en eventos
     */
    public function auto_assign_event_creator($post_id)
    {
        if (get_post_type($post_id) !== 'impress_event') {
            return;
        }
        if (!is_user_logged_in()) {
            return;
        }

        $current = get_field('impress_event_created_by', $post_id);
        if (!$current) {
            update_field('impress_event_created_by', get_current_user_id(), $post_id);
        }
    }

    /**
     * Handler AJAX para búsqueda global en el dashboard
     * Busca en propiedades, clientes y leads
     */
    public function ajax_global_search()
    {
        // Verificar nonce
        check_ajax_referer('inmopress_dashboard_nonce', 'nonce');

        // Verificar permisos
        if (!current_user_can('read')) {
            wp_send_json_error(array('message' => 'No tienes permisos para realizar búsquedas.'), 403);
        }

        // Obtener y sanitizar query
        $query = isset($_POST['query']) ? sanitize_text_field(wp_unslash($_POST['query'])) : '';

        if (empty($query) || strlen($query) < 2) {
            wp_send_json_success(array());
        }

        $results = array();
        $limit_per_type = 5; // Límite de resultados por tipo

        // Buscar en Propiedades
        $properties = get_posts(array(
            'post_type' => 'impress_property',
            's' => $query,
            'posts_per_page' => $limit_per_type,
            'post_status' => 'any',
            'orderby' => 'relevance',
            'order' => 'DESC',
        ));

        foreach ($properties as $property) {
            $ref = get_field('referencia', $property->ID);
            $title = get_the_title($property->ID);
            $display_title = $ref ? $ref . ' - ' . $title : $title;

            $results[] = array(
                'type' => 'Propiedad',
                'title' => $display_title,
                'url' => Inmopress_Shortcodes::panel_url('properties', array('edit' => $property->ID, 'property_id' => $property->ID)),
            );
        }

        // Buscar en Clientes
        $clients = get_posts(array(
            'post_type' => 'impress_client',
            's' => $query,
            'posts_per_page' => $limit_per_type,
            'post_status' => 'any',
            'orderby' => 'relevance',
            'order' => 'DESC',
        ));

        foreach ($clients as $client) {
            $results[] = array(
                'type' => 'Cliente',
                'title' => get_the_title($client->ID),
                'url' => Inmopress_Shortcodes::panel_url('clients', array('edit' => $client->ID)),
            );
        }

        // Buscar en Leads
        $leads = get_posts(array(
            'post_type' => 'impress_lead',
            's' => $query,
            'posts_per_page' => $limit_per_type,
            'post_status' => 'any',
            'orderby' => 'relevance',
            'order' => 'DESC',
        ));

        foreach ($leads as $lead) {
            $results[] = array(
                'type' => 'Lead',
                'title' => get_the_title($lead->ID),
                'url' => Inmopress_Shortcodes::panel_url('leads', array('edit' => $lead->ID)),
            );
        }

        // Ordenar resultados por relevancia (propiedades primero, luego clientes, luego leads)
        usort($results, function($a, $b) {
            $order = array('Propiedad' => 1, 'Cliente' => 2, 'Lead' => 3);
            return ($order[$a['type']] ?? 99) - ($order[$b['type']] ?? 99);
        });

        // Limitar resultados totales a 15
        $results = array_slice($results, 0, 15);

        wp_send_json_success($results);
    }
}

function inmopress_frontend()
{
    return Inmopress_Frontend::get_instance();
}
add_action('plugins_loaded', 'inmopress_frontend');

/**
 * Hook de activación del plugin - Crear páginas del panel
 */
function inmopress_frontend_activation()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-page-generator.php';
    $created = Inmopress_Page_Generator::create_all_pages();

    // Opcional: guardar mensaje de activación
    if ($created > 0) {
        add_option(
            'inmopress_frontend_activation_notice',
            sprintf('Se crearon %d páginas del panel frontend correctamente.', $created)
        );
    }
}
register_activation_hook(__FILE__, 'inmopress_frontend_activation');
