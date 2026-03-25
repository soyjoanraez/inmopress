<?php
/**
 * GPCP Admin
 *
 * Main admin class for GP Child Pro
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Admin class
 */
class GPCP_Admin
{
    /**
     * Instance of this class
     *
     * @var GPCP_Admin
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Admin
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
    private function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        $menu_title = get_option('gpcp_branding_theme_name', 'InmoPress Pro');
        
        add_menu_page(
            $menu_title,
            $menu_title,
            'manage_options',
            'gpcp-dashboard',
            array($this, 'render_dashboard_page'),
            'dashicons-admin-generic',
            30
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Dashboard', 'gpcp'),
            __('Dashboard', 'gpcp'),
            'manage_options',
            'gpcp-dashboard',
            array($this, 'render_dashboard_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Seguridad', 'gpcp'),
            __('Seguridad', 'gpcp'),
            'manage_options',
            'gpcp-security',
            array($this, 'render_security_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('SEO', 'gpcp'),
            __('SEO', 'gpcp'),
            'manage_options',
            'gpcp-seo',
            array($this, 'render_seo_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Optimización', 'gpcp'),
            __('Optimización', 'gpcp'),
            'manage_options',
            'gpcp-optimization',
            array($this, 'render_optimization_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Imágenes', 'gpcp'),
            __('Imágenes', 'gpcp'),
            'manage_options',
            'gpcp-images',
            array($this, 'render_images_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Gestor SEO', 'gpcp'),
            __('Gestor SEO', 'gpcp'),
            'manage_options',
            'gpcp-seo-manager',
            array($this, 'render_seo_manager_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Branding', 'gpcp'),
            __('Branding', 'gpcp'),
            'manage_options',
            'gpcp-branding',
            array($this, 'render_branding_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Exportar/Importar', 'gpcp'),
            __('Exportar/Importar', 'gpcp'),
            'manage_options',
            'gpcp-export-import',
            array($this, 'render_export_import_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Mantenimiento', 'gpcp'),
            __('Mantenimiento', 'gpcp'),
            'manage_options',
            'gpcp-maintenance',
            array($this, 'render_maintenance_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('SMTP', 'gpcp'),
            __('SMTP', 'gpcp'),
            'manage_options',
            'gpcp-smtp',
            array($this, 'render_smtp_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Redirecciones', 'gpcp'),
            __('Redirecciones', 'gpcp'),
            'manage_options',
            'gpcp-redirects',
            array($this, 'render_redirects_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Schema Markup', 'gpcp'),
            __('Schema Markup', 'gpcp'),
            'manage_options',
            'gpcp-schema',
            array($this, 'render_schema_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Analytics', 'gpcp'),
            __('Analytics', 'gpcp'),
            'manage_options',
            'gpcp-analytics',
            array($this, 'render_analytics_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Logs', 'gpcp'),
            __('Logs', 'gpcp'),
            'manage_options',
            'gpcp-logs',
            array($this, 'render_logs_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Cache', 'gpcp'),
            __('Cache', 'gpcp'),
            'manage_options',
            'gpcp-cache',
            array($this, 'render_cache_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Base de Datos', 'gpcp'),
            __('Base de Datos', 'gpcp'),
            'manage_options',
            'gpcp-database',
            array($this, 'render_database_page')
        );

        add_submenu_page(
            'gpcp-dashboard',
            __('Notificaciones', 'gpcp'),
            __('Notificaciones', 'gpcp'),
            'manage_options',
            'gpcp-notifications',
            array($this, 'render_notifications_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        // Settings will be registered by individual modules
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'gpcp-') === false) {
            return;
        }

        // Enqueue GPCP admin styles (append to existing admin.css or use separate)
        wp_enqueue_style('gpcp-admin', GPCP_THEME_URI . '/assets/css/admin.css', array(), GPCP_VERSION);
        wp_enqueue_media(); // Required for media uploader
        wp_enqueue_script('gpcp-admin', GPCP_THEME_URI . '/assets/js/admin.js', array('jquery'), GPCP_VERSION, true);
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard_page()
    {
        include GPCP_THEME_DIR . '/inc/gpcp/admin/views/dashboard.php';
    }

    /**
     * Render security page
     */
    public function render_security_page()
    {
        if (class_exists('GPCP_Security')) {
            GPCP_Security::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Seguridad', 'gpcp') . '</h1><p>' . __('Módulo de seguridad no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render SEO page
     */
    public function render_seo_page()
    {
        if (class_exists('GPCP_SEO')) {
            GPCP_SEO::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('SEO', 'gpcp') . '</h1><p>' . __('Módulo de SEO no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render optimization page
     */
    public function render_optimization_page()
    {
        if (class_exists('GPCP_Optimization')) {
            GPCP_Optimization::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Optimización', 'gpcp') . '</h1><p>' . __('Módulo de optimización no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render images page
     */
    public function render_images_page()
    {
        if (class_exists('GPCP_Images')) {
            GPCP_Images::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Imágenes', 'gpcp') . '</h1><p>' . __('Módulo de imágenes no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render SEO manager page
     */
    public function render_seo_manager_page()
    {
        if (class_exists('GPCP_SEO_Manager')) {
            GPCP_SEO_Manager::get_instance()->render_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Gestor SEO', 'gpcp') . '</h1><p>' . __('Gestor SEO no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render branding page
     */
    public function render_branding_page()
    {
        if (class_exists('GPCP_Branding')) {
            GPCP_Branding::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Branding', 'gpcp') . '</h1><p>' . __('Módulo de branding no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render export/import page
     */
    public function render_export_import_page()
    {
        if (class_exists('GPCP_Export_Import')) {
            GPCP_Export_Import::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Exportar/Importar', 'gpcp') . '</h1><p>' . __('Módulo de exportar/importar no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render maintenance page
     */
    public function render_maintenance_page()
    {
        if (class_exists('GPCP_Maintenance')) {
            GPCP_Maintenance::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Mantenimiento', 'gpcp') . '</h1><p>' . __('Módulo de mantenimiento no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render SMTP page
     */
    public function render_smtp_page()
    {
        if (class_exists('GPCP_SMTP')) {
            GPCP_SMTP::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('SMTP', 'gpcp') . '</h1><p>' . __('Módulo de SMTP no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render redirects page
     */
    public function render_redirects_page()
    {
        if (class_exists('GPCP_Redirects')) {
            GPCP_Redirects::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Redirecciones', 'gpcp') . '</h1><p>' . __('Módulo de redirecciones no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render schema page
     */
    public function render_schema_page()
    {
        if (class_exists('GPCP_Schema')) {
            GPCP_Schema::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Schema Markup', 'gpcp') . '</h1><p>' . __('Módulo de schema markup no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render analytics page
     */
    public function render_analytics_page()
    {
        if (class_exists('GPCP_Analytics')) {
            GPCP_Analytics::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Analytics', 'gpcp') . '</h1><p>' . __('Módulo de analytics no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render logs page
     */
    public function render_logs_page()
    {
        if (class_exists('GPCP_Logs')) {
            GPCP_Logs::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Logs', 'gpcp') . '</h1><p>' . __('Módulo de logs no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render cache page
     */
    public function render_cache_page()
    {
        if (class_exists('GPCP_Cache')) {
            GPCP_Cache::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Cache', 'gpcp') . '</h1><p>' . __('Módulo de cache no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render database page
     */
    public function render_database_page()
    {
        if (class_exists('GPCP_Database')) {
            GPCP_Database::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Base de Datos', 'gpcp') . '</h1><p>' . __('Módulo de base de datos no disponible.', 'gpcp') . '</p></div>';
        }
    }

    /**
     * Render notifications page
     */
    public function render_notifications_page()
    {
        if (class_exists('GPCP_Notifications')) {
            GPCP_Notifications::get_instance()->render_settings_page();
        } else {
            echo '<div class="wrap"><h1>' . __('Notificaciones', 'gpcp') . '</h1><p>' . __('Módulo de notificaciones no disponible.', 'gpcp') . '</p></div>';
        }
    }
}

