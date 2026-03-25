<?php
/**
 * Plugin Name: Inmopress Licensing
 * Description: Sistema de licencias SaaS y suscripciones con Stripe
 * Version: 1.0.0
 * Author: Inmopress
 */

if (!defined('ABSPATH')) {
    exit;
}

define('INMOPRESS_LICENSING_PATH', plugin_dir_path(__FILE__));
define('INMOPRESS_LICENSING_URL', plugin_dir_url(__FILE__));
define('INMOPRESS_LICENSING_VERSION', '1.0.0');

// Constante para servidor de licencias (configurar según entorno)
if (!defined('INMOPRESS_LICENSE_SERVER')) {
    define('INMOPRESS_LICENSE_SERVER', 'https://licenses.inmopress.com');
}

class Inmopress_Licensing
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
        require_once INMOPRESS_LICENSING_PATH . 'includes/class-license-manager.php';
        require_once INMOPRESS_LICENSING_PATH . 'includes/class-license-validator.php';
        require_once INMOPRESS_LICENSING_PATH . 'includes/class-feature-manager.php';
        require_once INMOPRESS_LICENSING_PATH . 'includes/class-admin-notices.php';
        
        // Inicializar Admin Notices
        Inmopress_Admin_Notices::get_instance();
        
        // Stripe (solo si está disponible)
        if (file_exists(INMOPRESS_LICENSING_PATH . 'includes/stripe/class-stripe-client.php')) {
            require_once INMOPRESS_LICENSING_PATH . 'includes/stripe/class-stripe-client.php';
            require_once INMOPRESS_LICENSING_PATH . 'includes/stripe/class-stripe-checkout.php';
            require_once INMOPRESS_LICENSING_PATH . 'includes/stripe/class-stripe-webhook.php';
            require_once INMOPRESS_LICENSING_PATH . 'includes/stripe/class-stripe-portal.php';
        }
    }

    private function init_hooks()
    {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Heartbeat de licencia (cada 12 horas)
        add_action('inmopress_license_heartbeat', array($this, 'license_heartbeat'));
        $this->schedule_heartbeat();

        // Generar installation_id único si no existe
        add_action('admin_init', array($this, 'ensure_installation_id'));

        // AJAX handlers
        add_action('wp_ajax_inmopress_activate_license', array($this, 'ajax_activate_license'));
        add_action('wp_ajax_inmopress_deactivate_license', array($this, 'ajax_deactivate_license'));
    }

    /**
     * Añadir menú admin
     */
    public function add_admin_menu()
    {
        add_menu_page(
            'Inmopress License',
            'Licencia',
            'manage_options',
            'inmopress-license',
            array($this, 'render_license_page'),
            'dashicons-admin-network',
            30
        );
    }

    /**
     * Renderizar página de licencia
     */
    public function render_license_page()
    {
        $license_manager = Inmopress_License_Manager::get_instance();
        $license_data = $license_manager->get_license_data();
        $feature_manager = Inmopress_Feature_Manager::get_instance();

        include INMOPRESS_LICENSING_PATH . 'admin/views/license-page.php';
    }

    /**
     * Programar heartbeat
     */
    private function schedule_heartbeat()
    {
        if (!wp_next_scheduled('inmopress_license_heartbeat')) {
            wp_schedule_event(time(), 'twicedaily', 'inmopress_license_heartbeat');
        }
    }

    /**
     * Heartbeat de licencia
     */
    public function license_heartbeat()
    {
        $license_manager = Inmopress_License_Manager::get_instance();
        $license_manager->validate_license();
    }

    /**
     * Asegurar installation_id único
     */
    public function ensure_installation_id()
    {
        $installation_id = get_option('inmopress_installation_id');
        if (empty($installation_id)) {
            $installation_id = wp_generate_uuid4();
            update_option('inmopress_installation_id', $installation_id);
        }
    }

    /**
     * AJAX: Activar licencia
     */
    public function ajax_activate_license()
    {
        check_ajax_referer('inmopress_license_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $license_key = sanitize_text_field($_POST['license_key']);
        $license_manager = Inmopress_License_Manager::get_instance();
        $result = $license_manager->activate_license($license_key);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => 'Licencia activada correctamente'));
    }

    /**
     * AJAX: Desactivar licencia
     */
    public function ajax_deactivate_license()
    {
        check_ajax_referer('inmopress_license_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $license_manager = Inmopress_License_Manager::get_instance();
        $result = $license_manager->deactivate_license();

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => 'Licencia desactivada'));
    }
}

function inmopress_licensing()
{
    return Inmopress_Licensing::get_instance();
}
add_action('plugins_loaded', 'inmopress_licensing');
