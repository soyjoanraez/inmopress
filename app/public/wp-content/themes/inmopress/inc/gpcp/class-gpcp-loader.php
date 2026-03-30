<?php
/**
 * GPCP Loader
 *
 * Loads all modules and initializes the theme
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Loader class
 */
class GPCP_Loader
{
    /**
     * Instance of this class
     *
     * @var GPCP_Loader
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Loader
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
        $this->load_files();
        $this->init_modules();
    }

    /**
     * Load required files
     */
    private function load_files()
    {
        // Admin class
        require_once GPCP_THEME_DIR . '/inc/gpcp/admin/class-gpcp-admin.php';

        // Load modules
        $modules = array(
            'security' => 'class-gpcp-security.php',
            'seo' => 'class-gpcp-seo.php',
            'optimization' => 'class-gpcp-optimization.php',
            'images' => 'class-gpcp-images.php',
            'branding' => 'class-gpcp-branding.php',
            'export-import' => 'class-gpcp-export-import.php',
            'dashboard-widgets' => 'class-gpcp-dashboard-widgets.php',
            'maintenance' => 'class-gpcp-maintenance.php',
            'smtp' => 'class-gpcp-smtp.php',
            'redirects' => 'class-gpcp-redirects.php',
            'schema' => 'class-gpcp-schema.php',
            'analytics' => 'class-gpcp-analytics.php',
            'logs' => 'class-gpcp-logs.php',
            'cache' => 'class-gpcp-cache.php',
            'database' => 'class-gpcp-database.php',
            'notifications' => 'class-gpcp-notifications.php',
        );

        foreach ($modules as $module => $file) {
            $file_path = GPCP_THEME_DIR . '/inc/gpcp/modules/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }

        // SEO Manager
        if (file_exists(GPCP_THEME_DIR . '/inc/gpcp/admin/class-gpcp-seo-manager.php')) {
            require_once GPCP_THEME_DIR . '/inc/gpcp/admin/class-gpcp-seo-manager.php';
        }
    }

    /**
     * Initialize modules
     */
    private function init_modules()
    {
        // Initialize admin
        GPCP_Admin::get_instance();

        // Initialize modules if they exist
        if (class_exists('GPCP_Security')) {
            GPCP_Security::get_instance();
        }
        if (class_exists('GPCP_SEO')) {
            GPCP_SEO::get_instance();
        }
        if (class_exists('GPCP_Optimization')) {
            GPCP_Optimization::get_instance();
        }
        if (class_exists('GPCP_Images')) {
            GPCP_Images::get_instance();
        }
        if (class_exists('GPCP_Branding')) {
            GPCP_Branding::get_instance();
        }
        if (class_exists('GPCP_Export_Import')) {
            GPCP_Export_Import::get_instance();
        }
        if (class_exists('GPCP_Dashboard_Widgets')) {
            GPCP_Dashboard_Widgets::get_instance();
        }
        if (class_exists('GPCP_Maintenance')) {
            GPCP_Maintenance::get_instance();
        }
        if (class_exists('GPCP_SMTP')) {
            GPCP_SMTP::get_instance();
        }
        if (class_exists('GPCP_Redirects')) {
            GPCP_Redirects::get_instance();
        }
        if (class_exists('GPCP_Schema')) {
            GPCP_Schema::get_instance();
        }
        if (class_exists('GPCP_Analytics')) {
            GPCP_Analytics::get_instance();
        }
        if (class_exists('GPCP_Logs')) {
            GPCP_Logs::get_instance();
        }
        if (class_exists('GPCP_Cache')) {
            GPCP_Cache::get_instance();
        }
        if (class_exists('GPCP_Database')) {
            GPCP_Database::get_instance();
        }
        if (class_exists('GPCP_Notifications')) {
            GPCP_Notifications::get_instance();
        }
    }
}

