<?php
/**
 * GPCP Security Module
 *
 * Security features
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Security class
 */
class GPCP_Security
{
    /**
     * Instance of this class
     *
     * @var GPCP_Security
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Security
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
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // Implementar funcionalidades solo si están activadas
        $this->implement_security_features();
    }

    /**
     * Add query vars
     */
    public function add_query_vars($vars)
    {
        $vars[] = 'gpcp_custom_login';
        $vars[] = 'gpcp_action';
        return $vars;
    }

    /**
     * Implement security features
     */
    private function implement_security_features()
    {
        // Custom login URL
        $custom_login_url = get_option('gpcp_security_custom_login_url', '');
        if (!empty($custom_login_url)) {
            add_action('init', array($this, 'custom_login_rewrite'));
            add_action('template_redirect', array($this, 'custom_login_redirect'));
            add_filter('site_url', array($this, 'custom_login_url'), 10, 3);
            add_filter('wp_redirect', array($this, 'custom_login_redirect_url'), 10, 2);
        }

        // Disable XML-RPC
        if (get_option('gpcp_security_disable_xmlrpc', false)) {
            add_filter('xmlrpc_enabled', '__return_false');
            add_filter('wp_headers', array($this, 'remove_x_pingback'));
        }

        // Hide WordPress version
        if (get_option('gpcp_security_hide_wp_version', false)) {
            remove_action('wp_head', 'wp_generator');
            add_filter('the_generator', '__return_empty_string');
        }

        // Disable file editing
        if (get_option('gpcp_security_disable_file_editing', false)) {
            if (!defined('DISALLOW_FILE_EDIT')) {
                define('DISALLOW_FILE_EDIT', true);
            }
        }

        // Remove version from scripts/styles
        if (get_option('gpcp_security_remove_version_from_scripts', false)) {
            add_filter('script_loader_src', array($this, 'remove_version_from_scripts'), 15, 1);
            add_filter('style_loader_src', array($this, 'remove_version_from_scripts'), 15, 1);
        }

        // Limit login attempts
        if (get_option('gpcp_security_limit_login_attempts', false)) {
            add_action('wp_login_failed', array($this, 'track_failed_login'));
            add_filter('authenticate', array($this, 'check_login_attempts'), 30, 3);
        }

        // Protect sensitive files
        if (get_option('gpcp_security_protect_files', false)) {
            add_action('init', array($this, 'protect_sensitive_files'));
        }
    }

    /**
     * Custom login rewrite
     */
    public function custom_login_rewrite()
    {
        $custom_login_url = get_option('gpcp_security_custom_login_url', '');
        if (!empty($custom_login_url)) {
            add_rewrite_rule('^' . $custom_login_url . '/?$', 'index.php?gpcp_custom_login=1', 'top');
            add_rewrite_rule('^' . $custom_login_url . '/(.*)$', 'index.php?gpcp_custom_login=1&gpcp_action=$matches[1]', 'top');
        }
    }

    /**
     * Custom login redirect
     */
    public function custom_login_redirect()
    {
        $custom_login_url = get_option('gpcp_security_custom_login_url', '');
        if (empty($custom_login_url)) {
            return;
        }

        // Block access to wp-login.php
        if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false) {
            wp_die(__('Acceso denegado.', 'gpcp'), __('Error', 'gpcp'), array('response' => 403));
        }

        // Handle custom login URL
        if (get_query_var('gpcp_custom_login')) {
            $action = get_query_var('gpcp_action');
            if (empty($action)) {
                $action = '';
            }
            
            // Load WordPress login
            require_once ABSPATH . 'wp-login.php';
            exit;
        }
    }

    /**
     * Custom login URL filter
     */
    public function custom_login_url($url, $path, $scheme)
    {
        $custom_login_url = get_option('gpcp_security_custom_login_url', '');
        if (!empty($custom_login_url) && $path == 'wp-login.php') {
            return home_url($custom_login_url, $scheme);
        }
        return $url;
    }

    /**
     * Custom login redirect URL
     */
    public function custom_login_redirect_url($location, $status)
    {
        $custom_login_url = get_option('gpcp_security_custom_login_url', '');
        if (!empty($custom_login_url) && strpos($location, 'wp-login.php') !== false) {
            $location = str_replace('wp-login.php', $custom_login_url, $location);
        }
        return $location;
    }

    /**
     * Remove X-Pingback header
     */
    public function remove_x_pingback($headers)
    {
        unset($headers['X-Pingback']);
        return $headers;
    }

    /**
     * Remove version from scripts/styles
     */
    public function remove_version_from_scripts($src)
    {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }

    /**
     * Track failed login attempts
     */
    public function track_failed_login($username)
    {
        $ip = $this->get_user_ip();
        $transient_key = 'gpcp_login_attempts_' . md5($ip);
        $attempts = get_transient($transient_key);
        
        if ($attempts === false) {
            $attempts = 0;
        }
        
        $attempts++;
        set_transient($transient_key, $attempts, 15 * MINUTE_IN_SECONDS);
    }

    /**
     * Check login attempts
     */
    public function check_login_attempts($user, $username, $password)
    {
        if (empty($username) || empty($password)) {
            return $user;
        }

        $ip = $this->get_user_ip();
        $transient_key = 'gpcp_login_attempts_' . md5($ip);
        $attempts = get_transient($transient_key);

        if ($attempts !== false && $attempts >= 5) {
            return new WP_Error('too_many_attempts', 
                __('Demasiados intentos de login. Por favor, intenta de nuevo en 15 minutos.', 'gpcp'));
        }

        return $user;
    }

    /**
     * Get user IP
     */
    private function get_user_ip()
    {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }

    /**
     * Protect sensitive files
     */
    public function protect_sensitive_files()
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        
        // Block access to sensitive files
        $sensitive_files = array(
            'wp-config.php',
            '.htaccess',
            'readme.html',
            'license.txt'
        );

        foreach ($sensitive_files as $file) {
            if (strpos($request_uri, $file) !== false) {
                wp_die(__('Acceso denegado.', 'gpcp'), __('Error', 'gpcp'), array('response' => 403));
            }
        }
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_security', 'gpcp_security_custom_login_url');
        register_setting('gpcp_security', 'gpcp_security_protect_files');
        register_setting('gpcp_security', 'gpcp_security_disable_xmlrpc');
        register_setting('gpcp_security', 'gpcp_security_hide_wp_version');
        register_setting('gpcp_security', 'gpcp_security_limit_login_attempts');
        register_setting('gpcp_security', 'gpcp_security_disable_file_editing');
        register_setting('gpcp_security', 'gpcp_security_remove_version_from_scripts');
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_security_save'])) {
            check_admin_referer('gpcp_security_save');
            
            $old_custom_login_url = get_option('gpcp_security_custom_login_url', '');
            update_option('gpcp_security_custom_login_url', sanitize_text_field($_POST['gpcp_security_custom_login_url']));
            update_option('gpcp_security_protect_files', isset($_POST['gpcp_security_protect_files']));
            update_option('gpcp_security_disable_xmlrpc', isset($_POST['gpcp_security_disable_xmlrpc']));
            update_option('gpcp_security_hide_wp_version', isset($_POST['gpcp_security_hide_wp_version']));
            update_option('gpcp_security_limit_login_attempts', isset($_POST['gpcp_security_limit_login_attempts']));
            update_option('gpcp_security_disable_file_editing', isset($_POST['gpcp_security_disable_file_editing']));
            update_option('gpcp_security_remove_version_from_scripts', isset($_POST['gpcp_security_remove_version_from_scripts']));
            
            // Flush rewrite rules if custom login URL changed
            if (get_option('gpcp_security_custom_login_url') != $custom_login_url) {
                flush_rewrite_rules();
            }
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $custom_login_url = get_option('gpcp_security_custom_login_url', '');
        $protect_files = get_option('gpcp_security_protect_files', false);
        $disable_xmlrpc = get_option('gpcp_security_disable_xmlrpc', false);
        $hide_wp_version = get_option('gpcp_security_hide_wp_version', false);
        $limit_login_attempts = get_option('gpcp_security_limit_login_attempts', false);
        $disable_file_editing = get_option('gpcp_security_disable_file_editing', false);
        $remove_version_from_scripts = get_option('gpcp_security_remove_version_from_scripts', false);
        ?>
        <div class="wrap">
            <h1><?php _e('Seguridad', 'gpcp'); ?></h1>
            <p><?php _e('Protege tu sitio con estas configuraciones de seguridad avanzadas.', 'gpcp'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('gpcp_security_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="gpcp_security_custom_login_url"><?php _e('URL de Login Personalizada', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gpcp_security_custom_login_url" name="gpcp_security_custom_login_url" value="<?php echo esc_attr($custom_login_url); ?>" class="regular-text" placeholder="mi-panel-admin" />
                            <p class="description">
                                <?php _e('Cambia wp-login.php por una URL personalizada. Ejemplo: mi-panel-admin', 'gpcp'); ?>
                                <br>
                                <strong><?php _e('⚠️ Importante:', 'gpcp'); ?></strong> <?php _e('Guarda esta URL en un lugar seguro. Si la olvidas, tendrás que acceder por FTP.', 'gpcp'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Protecciones', 'gpcp'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="gpcp_security_protect_files" value="1" <?php checked($protect_files); ?> />
                                    <?php _e('Proteger archivos sensibles (.htaccess, wp-config.php)', 'gpcp'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_security_disable_xmlrpc" value="1" <?php checked($disable_xmlrpc); ?> />
                                    <?php _e('Deshabilitar XML-RPC', 'gpcp'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_security_hide_wp_version" value="1" <?php checked($hide_wp_version); ?> />
                                    <?php _e('Ocultar versión de WordPress', 'gpcp'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_security_disable_file_editing" value="1" <?php checked($disable_file_editing); ?> />
                                    <?php _e('Deshabilitar edición de archivos desde el admin', 'gpcp'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_security_remove_version_from_scripts" value="1" <?php checked($remove_version_from_scripts); ?> />
                                    <?php _e('Remover versión de scripts y estilos', 'gpcp'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_security_limit_login_attempts" value="1" <?php checked($limit_login_attempts); ?> />
                                    <?php _e('Limitar intentos de login (5 intentos / 15 minutos)', 'gpcp'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_security_save'); ?>
            </form>
        </div>
        <?php
    }
}

