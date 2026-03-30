<?php
/**
 * GPCP Cache Module
 *
 * Cache management
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Cache class
 */
class GPCP_Cache
{
    /**
     * Instance of this class
     *
     * @var GPCP_Cache
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Cache
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
        add_action('wp_ajax_gpcp_clear_cache', array($this, 'clear_cache_ajax'));
        
        // Auto clear cache on post update
        if (get_option('gpcp_cache_clear_on_post_update', false)) {
            add_action('save_post', array($this, 'clear_cache_on_post_update'), 10, 2);
        }
    }

    /**
     * Clear cache on post update
     */
    public function clear_cache_on_post_update($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        $this->clear_all_cache();
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_cache', 'gpcp_cache_auto_clear');
        register_setting('gpcp_cache', 'gpcp_cache_clear_on_post_update');
    }

    /**
     * Clear all cache
     */
    public function clear_all_cache()
    {
        // Clear WordPress object cache
        wp_cache_flush();

        // Clear transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");

        // Clear opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        // Clear popular cache plugins
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }

        // W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }

        // WP Super Cache
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }

        // WP Rocket
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }

        // LiteSpeed Cache
        if (class_exists('LiteSpeed_Cache_API')) {
            LiteSpeed_Cache_API::purge_all();
        }
    }

    /**
     * Clear cache via AJAX
     */
    public function clear_cache_ajax()
    {
        check_ajax_referer('gpcp_clear_cache', 'nonce');

        $this->clear_all_cache();

        wp_send_json_success(array('message' => __('Cache limpiado correctamente.', 'gpcp')));
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_cache_save'])) {
            check_admin_referer('gpcp_cache_save');
            
            update_option('gpcp_cache_auto_clear', isset($_POST['gpcp_cache_auto_clear']));
            update_option('gpcp_cache_clear_on_post_update', isset($_POST['gpcp_cache_clear_on_post_update']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        if (isset($_POST['gpcp_cache_clear_now'])) {
            check_admin_referer('gpcp_cache_clear');
            $this->clear_all_cache();
            echo '<div class="notice notice-success"><p>' . __('Cache limpiado correctamente.', 'gpcp') . '</p></div>';
        }

        $auto_clear = get_option('gpcp_cache_auto_clear', false);
        $clear_on_post_update = get_option('gpcp_cache_clear_on_post_update', false);

        // Get cache stats
        global $wpdb;
        $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");
        ?>
        <div class="wrap">
            <h1><?php _e('Gestión de Cache', 'gpcp'); ?></h1>
            <p><?php _e('Gestiona y limpia el cache de WordPress y plugins de cache.', 'gpcp'); ?></p>
            
            <form method="post" action="" style="margin-bottom: 20px;">
                <?php wp_nonce_field('gpcp_cache_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Opciones de Cache', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_cache_auto_clear" value="1" <?php checked($auto_clear); ?> />
                                <?php _e('Limpiar cache automáticamente cada 24 horas', 'gpcp'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="gpcp_cache_clear_on_post_update" value="1" <?php checked($clear_on_post_update); ?> />
                                <?php _e('Limpiar cache al actualizar posts', 'gpcp'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_cache_save'); ?>
            </form>

            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle"><?php _e('Limpiar Cache', 'gpcp'); ?></h2>
                <div class="inside">
                    <p><?php _e('Limpia todo el cache del sitio: WordPress object cache, transients y cache de plugins.', 'gpcp'); ?></p>
                    <form method="post" action="">
                        <?php wp_nonce_field('gpcp_cache_clear'); ?>
                        <button type="submit" name="gpcp_cache_clear_now" class="button button-primary">
                            <?php _e('Limpiar Todo el Cache Ahora', 'gpcp'); ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle"><?php _e('Estadísticas de Cache', 'gpcp'); ?></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Transients Activos', 'gpcp'); ?></th>
                            <td><?php echo number_format($transient_count); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('OPcache', 'gpcp'); ?></th>
                            <td>
                                <?php if (function_exists('opcache_get_status')): ?>
                                    <?php $opcache = opcache_get_status(); ?>
                                    <?php if ($opcache && $opcache['opcache_enabled']): ?>
                                        <span style="color: #46b450;"><?php _e('Activo', 'gpcp'); ?></span>
                                    <?php else: ?>
                                        <span style="color: #dc3232;"><?php _e('Inactivo', 'gpcp'); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #666;"><?php _e('No disponible', 'gpcp'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
}

