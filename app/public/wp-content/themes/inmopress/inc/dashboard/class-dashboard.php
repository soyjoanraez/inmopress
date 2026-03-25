<?php
/**
 * Dashboard Frontend
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard class
 */
class Dashboard
{

    /**
     * Instance of this class
     *
     * @var Dashboard
     */
    private static $instance = null;

    /**
     * Dashboard slug
     *
     * @var string
     */
    private $dashboard_slug = 'panel';

    /**
     * Get instance
     *
     * @return Dashboard
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_dashboard_request'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dashboard_assets'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_shortcode('inmopress_dashboard_link', array($this, 'dashboard_link_shortcode'));
        
        // Flush rewrite rules on activation (only once)
        if (get_option('inmopress_flush_rewrite_rules') !== '1') {
            add_action('init', array($this, 'flush_rewrite_rules_once'), 999);
        }
    }
    
    /**
     * Flush rewrite rules once
     */
    public function flush_rewrite_rules_once()
    {
        flush_rewrite_rules();
        update_option('inmopress_flush_rewrite_rules', '1');
    }
    
    /**
     * Dashboard link shortcode
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function dashboard_link_shortcode($atts)
    {
        if (!is_user_logged_in()) {
            return '';
        }
        
        $atts = shortcode_atts(array(
            'text' => 'Acceder al Panel',
            'class' => 'btn btn-primary',
        ), $atts);
        
        $url = $this->get_dashboard_url();
        return '<a href="' . esc_url($url) . '" class="' . esc_attr($atts['class']) . '">' . esc_html($atts['text']) . '</a>';
    }

    /**
     * Add rewrite rules for dashboard
     */
    public function add_rewrite_rules()
    {
        add_rewrite_rule(
            '^' . $this->dashboard_slug . '/?$',
            'index.php?inmopress_dashboard=1',
            'top'
        );
        add_rewrite_rule(
            '^' . $this->dashboard_slug . '/([^/]+)/?$',
            'index.php?inmopress_dashboard=1&inmopress_section=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^' . $this->dashboard_slug . '/([^/]+)/([^/]+)/?$',
            'index.php?inmopress_dashboard=1&inmopress_section=$matches[1]&inmopress_action=$matches[2]',
            'top'
        );
    }

    /**
     * Add query vars
     *
     * @param array $vars Query vars.
     * @return array
     */
    public function add_query_vars($vars)
    {
        $vars[] = 'inmopress_dashboard';
        $vars[] = 'inmopress_section';
        $vars[] = 'inmopress_action';
        return $vars;
    }

    /**
     * Handle dashboard request
     */
    public function handle_dashboard_request()
    {
        $is_dashboard = get_query_var('inmopress_dashboard');

        if (!$is_dashboard) {
            return;
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(home_url($this->dashboard_slug)));
            exit;
        }

        // Check user capabilities
        $user = wp_get_current_user();
        $allowed_roles = array('administrator', 'agency', 'agent', 'trabajador', 'cliente');

        if (!array_intersect($allowed_roles, $user->roles)) {
            wp_die('No tienes permisos para acceder al panel de control.', 'Acceso denegado', array('response' => 403));
        }

        // Load dashboard template
        $this->load_dashboard_template();
        exit;
    }

    /**
     * Load dashboard template
     */
    private function load_dashboard_template()
    {
        $section = get_query_var('inmopress_section') ?: 'dashboard';
        $action = get_query_var('inmopress_action') ?: 'list';

        // Get user role
        $user = wp_get_current_user();
        $user_role = $user->roles[0] ?? 'cliente';

        // Check if user has access to this section
        if (!$this->user_can_access_section($user_role, $section)) {
            wp_die('No tienes permisos para acceder a esta sección.', 'Acceso denegado', array('response' => 403));
        }

        // Include dashboard header
        include INMOPRESS_THEME_DIR . '/templates/dashboard/header.php';

        // Include section template
        $template_file = INMOPRESS_THEME_DIR . '/templates/dashboard/' . $section . '.php';
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            include INMOPRESS_THEME_DIR . '/templates/dashboard/dashboard.php';
        }

        // Include dashboard footer
        include INMOPRESS_THEME_DIR . '/templates/dashboard/footer.php';
    }

    /**
     * Check if user can access section
     *
     * @param string $user_role User role.
     * @param string $section Section name.
     * @return bool
     */
    private function user_can_access_section($user_role, $section)
    {
        $permissions = array(
            'administrator' => array('dashboard', 'properties', 'clients', 'leads', 'visits', 'agencies', 'agents', 'owners', 'promotions', 'profile', 'settings'),
            'agency' => array('dashboard', 'properties', 'clients', 'leads', 'visits', 'agents', 'profile'),
            'agent' => array('dashboard', 'properties', 'clients', 'leads', 'visits', 'profile'),
            'trabajador' => array('dashboard', 'properties', 'clients', 'leads', 'visits', 'profile'),
            'cliente' => array('dashboard', 'profile', 'favorites'),
        );

        return isset($permissions[$user_role]) && in_array($section, $permissions[$user_role], true);
    }

    /**
     * Get dashboard menu items
     *
     * @return array
     */
    public function get_menu_items()
    {
        $user = wp_get_current_user();
        $user_role = $user->roles[0] ?? 'cliente';

        $menu_items = array(
            'dashboard' => array(
                'label' => 'Dashboard',
                'icon' => 'dashicons-dashboard',
                'roles' => array('administrator', 'agency', 'agent', 'trabajador', 'cliente'),
            ),
            'properties' => array(
                'label' => 'Inmuebles',
                'icon' => 'dashicons-building',
                'roles' => array('administrator', 'agency', 'agent', 'trabajador'),
            ),
            'clients' => array(
                'label' => 'Clientes',
                'icon' => 'dashicons-groups',
                'roles' => array('administrator', 'agency', 'agent', 'trabajador'),
            ),
            'leads' => array(
                'label' => 'Leads',
                'icon' => 'dashicons-email-alt',
                'roles' => array('administrator', 'agency', 'agent', 'trabajador'),
            ),
            'visits' => array(
                'label' => 'Visitas',
                'icon' => 'dashicons-calendar',
                'roles' => array('administrator', 'agency', 'agent', 'trabajador'),
            ),
            'agencies' => array(
                'label' => 'Agencias',
                'icon' => 'dashicons-store',
                'roles' => array('administrator', 'agency'),
            ),
            'agents' => array(
                'label' => 'Agentes',
                'icon' => 'dashicons-businessperson',
                'roles' => array('administrator', 'agency'),
            ),
            'owners' => array(
                'label' => 'Propietarios',
                'icon' => 'dashicons-admin-users',
                'roles' => array('administrator'),
            ),
            'promotions' => array(
                'label' => 'Promociones',
                'icon' => 'dashicons-megaphone',
                'roles' => array('administrator', 'agency', 'agent', 'trabajador'),
            ),
            'favorites' => array(
                'label' => 'Favoritos',
                'icon' => 'dashicons-heart',
                'roles' => array('cliente'),
            ),
            'profile' => array(
                'label' => 'Mi Perfil',
                'icon' => 'dashicons-admin-users',
                'roles' => array('administrator', 'agency', 'agent', 'trabajador', 'cliente'),
            ),
        );

        // Filter menu items by user role
        $filtered_menu = array();
        foreach ($menu_items as $key => $item) {
            if (in_array($user_role, $item['roles'], true)) {
                $filtered_menu[$key] = $item;
            }
        }

        return $filtered_menu;
    }

    /**
     * Get dashboard URL
     *
     * @param string $section Section name.
     * @param string $action Action name.
     * @return string
     */
    public function get_dashboard_url($section = 'dashboard', $action = '')
    {
        $url = home_url($this->dashboard_slug);
        if ($section !== 'dashboard') {
            $url .= '/' . $section;
            if ($action) {
                $url .= '/' . $action;
            }
        }
        return $url;
    }

    /**
     * Enqueue dashboard assets
     */
    public function enqueue_dashboard_assets()
    {
        if (!get_query_var('inmopress_dashboard')) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'inmopress-dashboard',
            INMOPRESS_THEME_URI . '/assets/css/dashboard.css',
            array(),
            INMOPRESS_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'inmopress-dashboard',
            INMOPRESS_THEME_URI . '/assets/js/dashboard.js',
            array('jquery'),
            INMOPRESS_VERSION,
            true
        );

        // Localize script
        wp_localize_script('inmopress-dashboard', 'inmopressDashboard', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('inmopress_dashboard_nonce'),
            'dashboardUrl' => $this->get_dashboard_url(),
        ));
    }

    /**
     * Get dashboard stats
     *
     * @return array
     */
    public function get_dashboard_stats()
    {
        $user = wp_get_current_user();
        $user_role = $user->roles[0] ?? 'cliente';

        $stats = array();

        if (in_array($user_role, array('administrator', 'agency', 'agent', 'trabajador'), true)) {
            $stats['properties'] = wp_count_posts('impress_property')->publish;
            $stats['clients'] = wp_count_posts('impress_client')->publish;
            $stats['leads'] = wp_count_posts('impress_lead')->publish;
            $stats['visits'] = wp_count_posts('impress_visit')->publish;
        }

        if ($user_role === 'cliente') {
            // Stats for clients
            $stats['favorites'] = 0; // TODO: Implement favorites count
        }

        return $stats;
    }
}

