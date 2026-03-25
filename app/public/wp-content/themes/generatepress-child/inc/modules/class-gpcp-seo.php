<?php
/**
 * GPCP SEO Module
 *
 * SEO features
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP SEO class
 */
class GPCP_SEO
{
    /**
     * Instance of this class
     *
     * @var GPCP_SEO
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_SEO
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
        register_setting('gpcp_seo', 'gpcp_seo_auto_complete_enabled');
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('SEO', 'gpcp'); ?></h1>
            <p><?php _e('Módulo de SEO - Funcionalidades básicas implementadas. Puedes expandir este módulo según tus necesidades.', 'gpcp'); ?></p>
        </div>
        <?php
    }
}



