<?php
/**
 * GPCP Maintenance Module
 *
 * Maintenance mode functionality
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Maintenance class
 */
class GPCP_Maintenance
{
    /**
     * Instance of this class
     *
     * @var GPCP_Maintenance
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Maintenance
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
        add_action('template_redirect', array($this, 'show_maintenance_page'));
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_maintenance', 'gpcp_maintenance_enabled');
        register_setting('gpcp_maintenance', 'gpcp_maintenance_title');
        register_setting('gpcp_maintenance', 'gpcp_maintenance_message');
        register_setting('gpcp_maintenance', 'gpcp_maintenance_logo');
        register_setting('gpcp_maintenance', 'gpcp_maintenance_bg_color');
        register_setting('gpcp_maintenance', 'gpcp_maintenance_text_color');
        register_setting('gpcp_maintenance', 'gpcp_maintenance_countdown_enabled');
        register_setting('gpcp_maintenance', 'gpcp_maintenance_countdown_date');
        register_setting('gpcp_maintenance', 'gpcp_maintenance_allowed_ips');
        register_setting('gpcp_maintenance', 'gpcp_maintenance_social_links');
    }

    /**
     * Show maintenance page
     */
    public function show_maintenance_page()
    {
        $enabled = get_option('gpcp_maintenance_enabled', false);
        
        if (!$enabled) {
            return;
        }

        // Allow administrators
        if (current_user_can('manage_options')) {
            return;
        }

        // Check allowed IPs
        $allowed_ips = get_option('gpcp_maintenance_allowed_ips', '');
        if (!empty($allowed_ips)) {
            $current_ip = $this->get_user_ip();
            $ips = array_map('trim', explode("\n", $allowed_ips));
            if (in_array($current_ip, $ips)) {
                return;
            }
        }

        // Don't show on admin or login pages
        if (is_admin()) {
            return;
        }
        
        // Don't show on login page
        $pagenow = isset($GLOBALS['pagenow']) ? $GLOBALS['pagenow'] : '';
        if (in_array($pagenow, array('wp-login.php', 'wp-register.php'))) {
            return;
        }
        
        // Don't show on AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        // Don't show on cron
        if (defined('DOING_CRON') && DOING_CRON) {
            return;
        }

        // Set headers
        status_header(503);
        header('Retry-After: 3600');

        // Render maintenance page
        $this->render_maintenance_page();
        exit;
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
     * Render maintenance page
     */
    private function render_maintenance_page()
    {
        $title = get_option('gpcp_maintenance_title', __('Sitio en Mantenimiento', 'gpcp'));
        $message = get_option('gpcp_maintenance_message', __('Estamos trabajando en mejoras para ofrecerte una mejor experiencia. Volveremos pronto.', 'gpcp'));
        $logo = get_option('gpcp_maintenance_logo');
        $bg_color = get_option('gpcp_maintenance_bg_color', '#ffffff');
        $text_color = get_option('gpcp_maintenance_text_color', '#333333');
        $countdown_enabled = get_option('gpcp_maintenance_countdown_enabled', false);
        $countdown_date = get_option('gpcp_maintenance_countdown_date');
        $social_links = get_option('gpcp_maintenance_social_links', array());

        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo esc_html($title); ?></title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    background-color: <?php echo esc_attr($bg_color); ?>;
                    color: <?php echo esc_attr($text_color); ?>;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    padding: 20px;
                }
                .maintenance-container {
                    text-align: center;
                    max-width: 600px;
                    width: 100%;
                }
                .maintenance-logo {
                    margin-bottom: 30px;
                }
                .maintenance-logo img {
                    max-width: 300px;
                    height: auto;
                }
                .maintenance-title {
                    font-size: 36px;
                    font-weight: 700;
                    margin-bottom: 20px;
                    color: <?php echo esc_attr($text_color); ?>;
                }
                .maintenance-message {
                    font-size: 18px;
                    line-height: 1.6;
                    margin-bottom: 30px;
                    color: <?php echo esc_attr($text_color); ?>;
                }
                .maintenance-countdown {
                    font-size: 48px;
                    font-weight: 700;
                    margin: 30px 0;
                    color: <?php echo esc_attr($text_color); ?>;
                }
                .maintenance-social {
                    margin-top: 40px;
                }
                .maintenance-social a {
                    display: inline-block;
                    margin: 0 10px;
                    font-size: 24px;
                    color: <?php echo esc_attr($text_color); ?>;
                    text-decoration: none;
                    transition: opacity 0.3s;
                }
                .maintenance-social a:hover {
                    opacity: 0.7;
                }
            </style>
            <?php if ($countdown_enabled && $countdown_date): ?>
            <script>
                function updateCountdown() {
                    var countdownDate = new Date('<?php echo esc_js($countdown_date); ?>').getTime();
                    var now = new Date().getTime();
                    var distance = countdownDate - now;

                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    var countdownEl = document.getElementById('countdown');
                    if (countdownEl) {
                        if (distance < 0) {
                            countdownEl.innerHTML = '00:00:00:00';
                        } else {
                            countdownEl.innerHTML = 
                                String(days).padStart(2, '0') + ':' +
                                String(hours).padStart(2, '0') + ':' +
                                String(minutes).padStart(2, '0') + ':' +
                                String(seconds).padStart(2, '0');
                        }
                    }
                }
                setInterval(updateCountdown, 1000);
                updateCountdown();
            </script>
            <?php endif; ?>
        </head>
        <body>
            <div class="maintenance-container">
                <?php if ($logo): ?>
                    <div class="maintenance-logo">
                        <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($title); ?>" />
                    </div>
                <?php endif; ?>

                <h1 class="maintenance-title"><?php echo esc_html($title); ?></h1>
                
                <div class="maintenance-message">
                    <?php echo wp_kses_post($message); ?>
                </div>

                <?php if ($countdown_enabled && $countdown_date): ?>
                    <div class="maintenance-countdown" id="countdown">
                        00:00:00:00
                    </div>
                <?php endif; ?>

                <?php if (!empty($social_links)): ?>
                    <div class="maintenance-social">
                        <?php if (!empty($social_links['facebook'])): ?>
                            <a href="<?php echo esc_url($social_links['facebook']); ?>" target="_blank" rel="noopener">📘</a>
                        <?php endif; ?>
                        <?php if (!empty($social_links['twitter'])): ?>
                            <a href="<?php echo esc_url($social_links['twitter']); ?>" target="_blank" rel="noopener">🐦</a>
                        <?php endif; ?>
                        <?php if (!empty($social_links['instagram'])): ?>
                            <a href="<?php echo esc_url($social_links['instagram']); ?>" target="_blank" rel="noopener">📷</a>
                        <?php endif; ?>
                        <?php if (!empty($social_links['linkedin'])): ?>
                            <a href="<?php echo esc_url($social_links['linkedin']); ?>" target="_blank" rel="noopener">💼</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_maintenance_save'])) {
            check_admin_referer('gpcp_maintenance_save');
            
            update_option('gpcp_maintenance_enabled', isset($_POST['gpcp_maintenance_enabled']));
            update_option('gpcp_maintenance_title', sanitize_text_field($_POST['gpcp_maintenance_title']));
            update_option('gpcp_maintenance_message', wp_kses_post($_POST['gpcp_maintenance_message']));
            update_option('gpcp_maintenance_logo', esc_url_raw($_POST['gpcp_maintenance_logo']));
            update_option('gpcp_maintenance_bg_color', sanitize_hex_color($_POST['gpcp_maintenance_bg_color']));
            update_option('gpcp_maintenance_text_color', sanitize_hex_color($_POST['gpcp_maintenance_text_color']));
            update_option('gpcp_maintenance_countdown_enabled', isset($_POST['gpcp_maintenance_countdown_enabled']));
            update_option('gpcp_maintenance_countdown_date', sanitize_text_field($_POST['gpcp_maintenance_countdown_date']));
            update_option('gpcp_maintenance_allowed_ips', sanitize_textarea_field($_POST['gpcp_maintenance_allowed_ips']));
            
            $social_links = array(
                'facebook' => esc_url_raw($_POST['gpcp_maintenance_social_facebook'] ?? ''),
                'twitter' => esc_url_raw($_POST['gpcp_maintenance_social_twitter'] ?? ''),
                'instagram' => esc_url_raw($_POST['gpcp_maintenance_social_instagram'] ?? ''),
                'linkedin' => esc_url_raw($_POST['gpcp_maintenance_social_linkedin'] ?? ''),
            );
            update_option('gpcp_maintenance_social_links', $social_links);
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $enabled = get_option('gpcp_maintenance_enabled', false);
        $title = get_option('gpcp_maintenance_title', __('Sitio en Mantenimiento', 'gpcp'));
        $message = get_option('gpcp_maintenance_message', __('Estamos trabajando en mejoras para ofrecerte una mejor experiencia. Volveremos pronto.', 'gpcp'));
        $logo = get_option('gpcp_maintenance_logo');
        $bg_color = get_option('gpcp_maintenance_bg_color', '#ffffff');
        $text_color = get_option('gpcp_maintenance_text_color', '#333333');
        $countdown_enabled = get_option('gpcp_maintenance_countdown_enabled', false);
        $countdown_date = get_option('gpcp_maintenance_countdown_date');
        $allowed_ips = get_option('gpcp_maintenance_allowed_ips', '');
        $social_links = get_option('gpcp_maintenance_social_links', array());
        $current_ip = $this->get_user_ip();

        ?>
        <div class="wrap">
            <h1><?php _e('Modo Mantenimiento', 'gpcp'); ?></h1>
            <p><?php _e('Muestra una página profesional de "sitio en mantenimiento" mientras trabajas en tu sitio.', 'gpcp'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('gpcp_maintenance_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Activar Modo Mantenimiento', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_maintenance_enabled" value="1" <?php checked($enabled); ?> />
                                <?php _e('Activar modo mantenimiento', 'gpcp'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Los administradores siempre pueden ver el sitio normalmente.', 'gpcp'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_maintenance_title"><?php _e('Título', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gpcp_maintenance_title" name="gpcp_maintenance_title" value="<?php echo esc_attr($title); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_maintenance_message"><?php _e('Mensaje', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <textarea id="gpcp_maintenance_message" name="gpcp_maintenance_message" rows="5" class="large-text"><?php echo esc_textarea($message); ?></textarea>
                            <p class="description"><?php _e('Puedes usar HTML básico.', 'gpcp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_maintenance_logo"><?php _e('Logo', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="gpcp_maintenance_logo" name="gpcp_maintenance_logo" value="<?php echo esc_url($logo); ?>" class="regular-text" />
                            <button type="button" class="button gpcp-upload-button" data-target="gpcp_maintenance_logo"><?php _e('Subir Logo', 'gpcp'); ?></button>
                            <?php if ($logo): ?>
                            <p><img src="<?php echo esc_url($logo); ?>" style="max-width: 300px; height: auto;" /></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_maintenance_bg_color"><?php _e('Color de Fondo', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="gpcp_maintenance_bg_color" name="gpcp_maintenance_bg_color" value="<?php echo esc_attr($bg_color); ?>" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_maintenance_text_color"><?php _e('Color de Texto', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="gpcp_maintenance_text_color" name="gpcp_maintenance_text_color" value="<?php echo esc_attr($text_color); ?>" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Cuenta Regresiva', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_maintenance_countdown_enabled" value="1" <?php checked($countdown_enabled); ?> />
                                <?php _e('Mostrar cuenta regresiva', 'gpcp'); ?>
                            </label>
                            <br>
                            <label for="gpcp_maintenance_countdown_date" style="margin-top: 10px; display: block;">
                                <?php _e('Fecha y Hora de Finalización:', 'gpcp'); ?>
                                <input type="datetime-local" id="gpcp_maintenance_countdown_date" name="gpcp_maintenance_countdown_date" value="<?php echo esc_attr($countdown_date); ?>" style="margin-left: 10px;" />
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_maintenance_allowed_ips"><?php _e('IPs Permitidas', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <textarea id="gpcp_maintenance_allowed_ips" name="gpcp_maintenance_allowed_ips" rows="5" class="large-text"><?php echo esc_textarea($allowed_ips); ?></textarea>
                            <p class="description">
                                <?php _e('Una IP por línea. Estas IPs podrán ver el sitio normalmente.', 'gpcp'); ?>
                                <br>
                                <strong><?php _e('Tu IP actual:', 'gpcp'); ?></strong> <?php echo esc_html($current_ip); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Redes Sociales', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <?php _e('Facebook:', 'gpcp'); ?>
                                <input type="url" name="gpcp_maintenance_social_facebook" value="<?php echo esc_url($social_links['facebook'] ?? ''); ?>" class="regular-text" style="margin-left: 10px;" />
                            </label>
                            <br>
                            <label style="margin-top: 10px; display: block;">
                                <?php _e('Twitter:', 'gpcp'); ?>
                                <input type="url" name="gpcp_maintenance_social_twitter" value="<?php echo esc_url($social_links['twitter'] ?? ''); ?>" class="regular-text" style="margin-left: 10px;" />
                            </label>
                            <br>
                            <label style="margin-top: 10px; display: block;">
                                <?php _e('Instagram:', 'gpcp'); ?>
                                <input type="url" name="gpcp_maintenance_social_instagram" value="<?php echo esc_url($social_links['instagram'] ?? ''); ?>" class="regular-text" style="margin-left: 10px;" />
                            </label>
                            <br>
                            <label style="margin-top: 10px; display: block;">
                                <?php _e('LinkedIn:', 'gpcp'); ?>
                                <input type="url" name="gpcp_maintenance_social_linkedin" value="<?php echo esc_url($social_links['linkedin'] ?? ''); ?>" class="regular-text" style="margin-left: 10px;" />
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_maintenance_save'); ?>
            </form>
        </div>
        <?php
    }
}

