<?php
/**
 * GPCP Export/Import Module
 *
 * Export and import theme configurations
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Export Import class
 */
class GPCP_Export_Import
{
    /**
     * Instance of this class
     *
     * @var GPCP_Export_Import
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Export_Import
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
        add_action('admin_init', array($this, 'handle_export'));
        add_action('admin_init', array($this, 'handle_import'));
    }

    /**
     * Handle export
     */
    public function handle_export()
    {
        if (!isset($_POST['gpcp_export']) || !current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('gpcp_export');

        $include_seo = isset($_POST['gpcp_export_include_seo']);

        $data = array(
            'version' => GPCP_VERSION,
            'export_date' => current_time('mysql'),
            'site_url' => home_url(),
            'settings' => $this->export_settings(),
            'seo_data' => $include_seo ? $this->export_seo_data() : null,
        );

        $filename = 'gpcp-config-' . date('Y-m-d-His') . '.json';

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Export settings
     */
    private function export_settings()
    {
        $settings = array();

        // Branding
        $settings['branding'] = array(
            'theme_name' => get_option('gpcp_branding_theme_name'),
            'login_logo' => get_option('gpcp_branding_login_logo'),
            'admin_logo' => get_option('gpcp_branding_admin_logo'),
            'primary_color' => get_option('gpcp_branding_primary_color'),
            'footer_text' => get_option('gpcp_branding_footer_text'),
            'login_enabled' => get_option('gpcp_branding_login_enabled'),
            'admin_enabled' => get_option('gpcp_branding_admin_enabled'),
            'remove_wp_logo' => get_option('gpcp_branding_remove_wp_logo'),
        );

        // Security (if module exists)
        if (class_exists('GPCP_Security')) {
            $settings['security'] = array(
                'custom_login_url' => get_option('gpcp_security_custom_login_url'),
                'protect_files' => get_option('gpcp_security_protect_files'),
                'disable_xmlrpc' => get_option('gpcp_security_disable_xmlrpc'),
                'hide_wp_version' => get_option('gpcp_security_hide_wp_version'),
                'limit_login_attempts' => get_option('gpcp_security_limit_login_attempts'),
            );
        }

        // SEO (if module exists)
        if (class_exists('GPCP_SEO')) {
            $settings['seo'] = array(
                'auto_complete_enabled' => get_option('gpcp_seo_auto_complete_enabled'),
            );
        }

        // Optimization (if module exists)
        if (class_exists('GPCP_Optimization')) {
            $settings['optimization'] = array(
                'disable_emojis' => get_option('gpcp_optimization_disable_emojis'),
                'disable_embeds' => get_option('gpcp_optimization_disable_embeds'),
                'remove_query_strings' => get_option('gpcp_optimization_remove_query_strings'),
                'defer_javascript' => get_option('gpcp_optimization_defer_javascript'),
                'lazy_load_images' => get_option('gpcp_optimization_lazy_load_images'),
            );
        }

        // Images (if module exists)
        if (class_exists('GPCP_Images')) {
            $settings['images'] = array(
                'limit_image_sizes' => get_option('gpcp_images_limit_sizes'),
                'webp_conversion' => get_option('gpcp_images_webp_conversion'),
                'auto_alt_title' => get_option('gpcp_images_auto_alt_title'),
            );
        }

        // Maintenance (if module exists)
        if (class_exists('GPCP_Maintenance')) {
            $settings['maintenance'] = array(
                'enabled' => get_option('gpcp_maintenance_enabled'),
                'title' => get_option('gpcp_maintenance_title'),
                'message' => get_option('gpcp_maintenance_message'),
                'logo' => get_option('gpcp_maintenance_logo'),
                'background_color' => get_option('gpcp_maintenance_bg_color'),
                'text_color' => get_option('gpcp_maintenance_text_color'),
                'countdown_enabled' => get_option('gpcp_maintenance_countdown_enabled'),
                'countdown_date' => get_option('gpcp_maintenance_countdown_date'),
                'allowed_ips' => get_option('gpcp_maintenance_allowed_ips'),
                'social_links' => get_option('gpcp_maintenance_social_links'),
            );
        }

        // SMTP (if module exists)
        if (class_exists('GPCP_SMTP')) {
            $settings['smtp'] = array(
                'enabled' => get_option('gpcp_smtp_enabled'),
                'host' => get_option('gpcp_smtp_host'),
                'port' => get_option('gpcp_smtp_port'),
                'encryption' => get_option('gpcp_smtp_encryption'),
                'auth' => get_option('gpcp_smtp_auth'),
                'username' => get_option('gpcp_smtp_username'),
                'from_name' => get_option('gpcp_smtp_from_name'),
                'from_email' => get_option('gpcp_smtp_from_email'),
            );
        }

        // Redirects (if module exists)
        if (class_exists('GPCP_Redirects')) {
            $settings['redirects'] = get_option('gpcp_redirects_list', array());
        }

        // Schema (if module exists)
        if (class_exists('GPCP_Schema')) {
            $settings['schema'] = array(
                'enabled' => get_option('gpcp_schema_enabled'),
                'organization_name' => get_option('gpcp_schema_organization_name'),
                'organization_logo' => get_option('gpcp_schema_organization_logo'),
                'organization_url' => get_option('gpcp_schema_organization_url'),
                'organization_phone' => get_option('gpcp_schema_organization_phone'),
                'organization_email' => get_option('gpcp_schema_organization_email'),
                'organization_address' => get_option('gpcp_schema_organization_address'),
            );
        }

        // Analytics (if module exists)
        if (class_exists('GPCP_Analytics')) {
            $settings['analytics'] = array(
                'enabled' => get_option('gpcp_analytics_enabled'),
            );
        }

        // Cache (if module exists)
        if (class_exists('GPCP_Cache')) {
            $settings['cache'] = array(
                'auto_clear' => get_option('gpcp_cache_auto_clear'),
                'clear_on_post_update' => get_option('gpcp_cache_clear_on_post_update'),
            );
        }

        // Database (if module exists)
        if (class_exists('GPCP_Database')) {
            $settings['database'] = array(
                'auto_cleanup' => get_option('gpcp_database_auto_cleanup'),
            );
        }

        // Notifications (if module exists)
        if (class_exists('GPCP_Notifications')) {
            $settings['notifications'] = array(
                'enabled' => get_option('gpcp_notifications_enabled'),
                'email' => get_option('gpcp_notifications_email'),
                'security' => get_option('gpcp_notifications_security'),
                'updates' => get_option('gpcp_notifications_updates'),
            );
        }

        return $settings;
    }

    /**
     * Export SEO data
     */
    private function export_seo_data()
    {
        $seo_data = array();

        $posts = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));

        foreach ($posts as $post) {
            $seo_title = get_post_meta($post->ID, '_gpcp_seo_title', true);
            $seo_description = get_post_meta($post->ID, '_gpcp_seo_description', true);
            $seo_keywords = get_post_meta($post->ID, '_gpcp_seo_keywords', true);

            if ($seo_title || $seo_description || $seo_keywords) {
                $seo_data[] = array(
                    'post_name' => $post->post_name,
                    'post_title' => $post->post_title,
                    'post_type' => $post->post_type,
                    'seo_title' => $seo_title,
                    'seo_description' => $seo_description,
                    'seo_keywords' => $seo_keywords,
                );
            }
        }

        return $seo_data;
    }

    /**
     * Handle import
     */
    public function handle_import()
    {
        if (!isset($_POST['gpcp_import']) || !current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('gpcp_import');

        if (!isset($_FILES['gpcp_import_file']) || $_FILES['gpcp_import_file']['error'] !== UPLOAD_ERR_OK) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Error al subir el archivo.', 'gpcp') . '</p></div>';
            });
            return;
        }

        $file_content = file_get_contents($_FILES['gpcp_import_file']['tmp_name']);
        $data = json_decode($file_content, true);

        if (!$data || !isset($data['settings'])) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Archivo de importación inválido.', 'gpcp') . '</p></div>';
            });
            return;
        }

        // Import settings
        $this->import_settings($data['settings']);

        // Import SEO data if requested
        if (isset($_POST['gpcp_import_include_seo']) && isset($data['seo_data']) && is_array($data['seo_data'])) {
            $this->import_seo_data($data['seo_data']);
        }

        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>' . __('Configuración importada correctamente.', 'gpcp') . '</p></div>';
        });
    }

    /**
     * Import settings
     */
    private function import_settings($settings)
    {
        if (isset($settings['branding'])) {
            foreach ($settings['branding'] as $key => $value) {
                update_option('gpcp_branding_' . $key, $value);
            }
        }

        if (isset($settings['security']) && class_exists('GPCP_Security')) {
            foreach ($settings['security'] as $key => $value) {
                update_option('gpcp_security_' . $key, $value);
            }
        }

        if (isset($settings['seo']) && class_exists('GPCP_SEO')) {
            foreach ($settings['seo'] as $key => $value) {
                update_option('gpcp_seo_' . $key, $value);
            }
        }

        if (isset($settings['optimization']) && class_exists('GPCP_Optimization')) {
            foreach ($settings['optimization'] as $key => $value) {
                update_option('gpcp_optimization_' . $key, $value);
            }
        }

        if (isset($settings['images']) && class_exists('GPCP_Images')) {
            foreach ($settings['images'] as $key => $value) {
                update_option('gpcp_images_' . $key, $value);
            }
        }

        if (isset($settings['maintenance']) && class_exists('GPCP_Maintenance')) {
            foreach ($settings['maintenance'] as $key => $value) {
                update_option('gpcp_maintenance_' . $key, $value);
            }
        }

        if (isset($settings['smtp']) && class_exists('GPCP_SMTP')) {
            foreach ($settings['smtp'] as $key => $value) {
                update_option('gpcp_smtp_' . $key, $value);
            }
        }

        if (isset($settings['redirects']) && class_exists('GPCP_Redirects')) {
            update_option('gpcp_redirects_list', $settings['redirects']);
        }

        if (isset($settings['schema']) && class_exists('GPCP_Schema')) {
            foreach ($settings['schema'] as $key => $value) {
                update_option('gpcp_schema_' . $key, $value);
            }
        }

        if (isset($settings['analytics']) && class_exists('GPCP_Analytics')) {
            foreach ($settings['analytics'] as $key => $value) {
                update_option('gpcp_analytics_' . $key, $value);
            }
        }

        if (isset($settings['cache']) && class_exists('GPCP_Cache')) {
            foreach ($settings['cache'] as $key => $value) {
                update_option('gpcp_cache_' . $key, $value);
            }
        }

        if (isset($settings['database']) && class_exists('GPCP_Database')) {
            foreach ($settings['database'] as $key => $value) {
                update_option('gpcp_database_' . $key, $value);
            }
        }

        if (isset($settings['notifications']) && class_exists('GPCP_Notifications')) {
            foreach ($settings['notifications'] as $key => $value) {
                update_option('gpcp_notifications_' . $key, $value);
            }
        }
    }

    /**
     * Import SEO data
     */
    private function import_seo_data($seo_data)
    {
        foreach ($seo_data as $item) {
            // Try to find post by slug first
            $post = get_page_by_path($item['post_name'], OBJECT, $item['post_type']);

            // If not found, try by title
            if (!$post) {
                $posts = get_posts(array(
                    'title' => $item['post_title'],
                    'post_type' => $item['post_type'],
                    'posts_per_page' => 1,
                ));
                if (!empty($posts)) {
                    $post = $posts[0];
                }
            }

            if ($post) {
                if (!empty($item['seo_title'])) {
                    update_post_meta($post->ID, '_gpcp_seo_title', sanitize_text_field($item['seo_title']));
                }
                if (!empty($item['seo_description'])) {
                    update_post_meta($post->ID, '_gpcp_seo_description', sanitize_textarea_field($item['seo_description']));
                }
                if (!empty($item['seo_keywords'])) {
                    update_post_meta($post->ID, '_gpcp_seo_keywords', sanitize_text_field($item['seo_keywords']));
                }
            }
        }
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('Exportar/Importar Configuraciones', 'gpcp'); ?></h1>
            <p><?php _e('Exporta todas las configuraciones del tema en un archivo JSON e impórtalas en otros sitios con un clic.', 'gpcp'); ?></p>

            <div class="gpcp-export-import-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Exportar Configuraciones', 'gpcp'); ?></h2>
                    <div class="inside">
                        <form method="post" action="">
                            <?php wp_nonce_field('gpcp_export'); ?>
                            <p><?php _e('Descarga un archivo JSON con todas las configuraciones del tema.', 'gpcp'); ?></p>
                            <p>
                                <label>
                                    <input type="checkbox" name="gpcp_export_include_seo" value="1" />
                                    <?php _e('Incluir datos SEO de posts', 'gpcp'); ?>
                                </label>
                            </p>
                            <p>
                                <button type="submit" name="gpcp_export" class="button button-primary">
                                    <?php _e('Descargar Configuraciones', 'gpcp'); ?>
                                </button>
                            </p>
                        </form>
                    </div>
                </div>

                <div class="postbox">
                    <h2 class="hndle"><?php _e('Importar Configuraciones', 'gpcp'); ?></h2>
                    <div class="inside">
                        <form method="post" action="" enctype="multipart/form-data">
                            <?php wp_nonce_field('gpcp_import'); ?>
                            <p><?php _e('Sube un archivo JSON para importar configuraciones.', 'gpcp'); ?></p>
                            <p>
                                <input type="file" name="gpcp_import_file" accept=".json" required />
                            </p>
                            <p>
                                <label>
                                    <input type="checkbox" name="gpcp_import_include_seo" value="1" />
                                    <?php _e('Importar datos SEO de posts', 'gpcp'); ?>
                                </label>
                            </p>
                            <p class="description">
                                <?php _e('⚠️ Advertencia: Esto sobrescribirá tu configuración actual.', 'gpcp'); ?>
                            </p>
                            <p>
                                <button type="submit" name="gpcp_import" class="button button-primary">
                                    <?php _e('Importar Configuraciones', 'gpcp'); ?>
                                </button>
                            </p>
                        </form>
                    </div>
                </div>
            </div>

            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle"><?php _e('¿Qué se exporta/importa?', 'gpcp'); ?></h2>
                <div class="inside">
                    <ul>
                        <li><?php _e('Configuración de Branding (logos, colores, footer)', 'gpcp'); ?></li>
                        <li><?php _e('Configuración de Seguridad', 'gpcp'); ?></li>
                        <li><?php _e('Configuración de SEO', 'gpcp'); ?></li>
                        <li><?php _e('Configuración de Optimización', 'gpcp'); ?></li>
                        <li><?php _e('Configuración de Imágenes', 'gpcp'); ?></li>
                        <li><?php _e('Configuración de Mantenimiento', 'gpcp'); ?></li>
                        <li><?php _e('Datos SEO de posts (opcional)', 'gpcp'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
}

