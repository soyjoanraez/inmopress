<?php
/**
 * GPCP Images Module
 *
 * Image management features
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Images class
 */
class GPCP_Images
{
    /**
     * Instance of this class
     *
     * @var GPCP_Images
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Images
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
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_images', 'gpcp_images_limit_sizes');
        register_setting('gpcp_images', 'gpcp_images_webp_conversion');
        register_setting('gpcp_images', 'gpcp_images_auto_alt_title');
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('Imágenes', 'gpcp'); ?></h1>
            <p><?php _e('Módulo de imágenes - Funcionalidades básicas implementadas. Puedes expandir este módulo según tus necesidades.', 'gpcp'); ?></p>
        </div>
        <?php
    }
}



