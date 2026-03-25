<?php
/**
 * Plugin Name: Inmopress API
 * Description: API REST personalizada con autenticación JWT, rate limiting y webhooks
 * Version: 1.0.0
 * Author: Inmopress
 */

if (!defined('ABSPATH')) {
    exit;
}

define('INMOPRESS_API_PATH', plugin_dir_path(__FILE__));
define('INMOPRESS_API_URL', plugin_dir_url(__FILE__));
define('INMOPRESS_API_VERSION', '1.0.0');
define('INMOPRESS_API_NAMESPACE', 'inmopress/v1');

class Inmopress_API
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
        require_once INMOPRESS_API_PATH . 'includes/class-api-manager.php';
        require_once INMOPRESS_API_PATH . 'includes/class-jwt-auth.php';
        require_once INMOPRESS_API_PATH . 'includes/class-rate-limiter.php';
        require_once INMOPRESS_API_PATH . 'includes/class-webhook-manager.php';
        
        // Endpoints
        require_once INMOPRESS_API_PATH . 'includes/endpoints/class-auth-endpoints.php';
        require_once INMOPRESS_API_PATH . 'includes/endpoints/class-properties-endpoints.php';
        require_once INMOPRESS_API_PATH . 'includes/endpoints/class-clients-endpoints.php';
        require_once INMOPRESS_API_PATH . 'includes/endpoints/class-leads-endpoints.php';
        require_once INMOPRESS_API_PATH . 'includes/endpoints/class-matching-endpoints.php';
    }

    private function init_hooks()
    {
        // Registrar rutas REST
        add_action('rest_api_init', array($this, 'register_routes'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Crear tablas al activar
        register_activation_hook(__FILE__, array($this, 'create_tables'));
    }

    /**
     * Registrar rutas REST
     */
    public function register_routes()
    {
        $auth_endpoints = new Inmopress_Auth_Endpoints();
        $auth_endpoints->register_routes();

        $properties_endpoints = new Inmopress_Properties_Endpoints();
        $properties_endpoints->register_routes();

        $clients_endpoints = new Inmopress_Clients_Endpoints();
        $clients_endpoints->register_routes();

        $leads_endpoints = new Inmopress_Leads_Endpoints();
        $leads_endpoints->register_routes();

        $matching_endpoints = new Inmopress_Matching_Endpoints();
        $matching_endpoints->register_routes();
    }

    /**
     * Añadir menú admin
     */
    public function add_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=impress_property',
            'API Settings',
            'API',
            'manage_options',
            'inmopress-api',
            array($this, 'render_api_page')
        );
    }

    /**
     * Renderizar página de API
     */
    public function render_api_page()
    {
        // Guardar configuración
        if (isset($_POST['save_api_settings']) && check_admin_referer('inmopress_api_settings')) {
            update_option('inmopress_api_enabled', isset($_POST['api_enabled']) ? 1 : 0);
            update_option('inmopress_api_jwt_secret', sanitize_text_field($_POST['jwt_secret']));
            update_option('inmopress_api_rate_limit', intval($_POST['rate_limit']));
            
            echo '<div class="notice notice-success"><p>Configuración guardada.</p></div>';
        }

        $api_enabled = get_option('inmopress_api_enabled', 1);
        $jwt_secret = get_option('inmopress_api_jwt_secret', '');
        $rate_limit = get_option('inmopress_api_rate_limit', 100);

        if (empty($jwt_secret)) {
            $jwt_secret = wp_generate_password(64, false);
            update_option('inmopress_api_jwt_secret', $jwt_secret);
        }

        include INMOPRESS_API_PATH . 'admin/views/api-settings.php';
    }

    /**
     * Crear tablas
     */
    public function create_tables()
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabla de webhooks
        $table_webhooks = $wpdb->prefix . 'inmopress_webhooks';
        $sql_webhooks = "CREATE TABLE IF NOT EXISTS {$table_webhooks} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            url varchar(500) NOT NULL,
            events text NOT NULL,
            secret varchar(255) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            last_triggered_at datetime,
            created_at datetime NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_webhooks);
    }
}

function inmopress_api()
{
    return Inmopress_API::get_instance();
}
add_action('plugins_loaded', 'inmopress_api');
