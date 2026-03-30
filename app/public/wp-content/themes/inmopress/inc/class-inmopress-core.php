<?php
/**
 * Core class for InmoPress Theme
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core class
 */
class Core
{

    /**
     * Instance of this class
     *
     * @var Core
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Core
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
        // Private constructor for singleton pattern.
    }

    /**
     * Initialize the theme
     */
    public function init()
    {
        // Load includes.
        $this->load_includes();

        // Initialize components.
        $this->init_components();
    }

    /**
     * Load required files
     */
    private function load_includes()
    {
        // Post Types.
        require_once INMOPRESS_THEME_DIR . '/inc/post-types/class-post-types.php';
        
        // Property Hooks
        require_once INMOPRESS_THEME_DIR . '/inc/post-types/class-property-hooks.php';

        // Taxonomies.
        require_once INMOPRESS_THEME_DIR . '/inc/taxonomies/class-taxonomies.php';

        // ACF Integration
        require_once INMOPRESS_THEME_DIR . '/inc/acf/class-acf-integration.php';
        
        // ACF Fields Loader
        require_once INMOPRESS_THEME_DIR . '/inc/acf/class-acf-fields-loader.php';
        
        // Roles
        require_once INMOPRESS_THEME_DIR . '/inc/roles/class-roles.php';
        
        // Dashboard
        require_once INMOPRESS_THEME_DIR . '/inc/dashboard/class-dashboard.php';
        
        // Properties System
        require_once INMOPRESS_THEME_DIR . '/inc/properties/class-property-settings.php';
        require_once INMOPRESS_THEME_DIR . '/inc/properties/class-property-query.php';
        require_once INMOPRESS_THEME_DIR . '/inc/properties/class-property-shortcode.php';
        require_once INMOPRESS_THEME_DIR . '/inc/properties/class-property-filters.php';
        require_once INMOPRESS_THEME_DIR . '/inc/properties/class-property-ajax.php';
    }

    /**
     * Initialize components
     */
    private function init_components()
    {
        // Register post types.
        Post_Types::get_instance();
        
        // Initialize property hooks
        Property_Hooks::get_instance();

        // Register taxonomies.
        Taxonomies::get_instance();

        // Initialize ACF integration
        ACF_Integration::get_instance();
        
        // Load ACF field groups
        ACF_Fields_Loader::get_instance();
        
        // Register roles and capabilities
        Roles::get_instance();
        
        // Initialize dashboard
        Dashboard::get_instance();
        
        // Initialize property settings
        Property_Settings::get_instance();
        
        // Initialize property shortcode
        Property_Shortcode::get_instance();
        
        // Initialize property filters
        Property_Filters::get_instance();
        
        // Initialize property AJAX
        Property_Ajax::get_instance();
    }
}
