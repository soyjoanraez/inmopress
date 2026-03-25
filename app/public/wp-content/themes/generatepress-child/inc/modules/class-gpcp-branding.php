<?php
/**
 * GPCP Branding Module
 *
 * Customizes WordPress admin and login page
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Branding class
 */
class GPCP_Branding
{
    /**
     * Instance of this class
     *
     * @var GPCP_Branding
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Branding
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
        
        // Login customization
        $login_enabled = get_option('gpcp_branding_login_enabled', false);
        if ($login_enabled) {
            add_action('login_enqueue_scripts', array($this, 'customize_login_styles'));
            add_filter('login_headerurl', array($this, 'customize_login_logo_url'));
            add_filter('login_headertext', array($this, 'customize_login_logo_text'));
        }

        // Admin customization
        $admin_enabled = get_option('gpcp_branding_admin_enabled', false);
        if ($admin_enabled) {
            add_action('admin_head', array($this, 'customize_admin_styles'));
            add_filter('admin_footer_text', array($this, 'customize_admin_footer'));
        }

        // Remove WordPress logo from admin bar
        if (get_option('gpcp_branding_remove_wp_logo', false)) {
            add_action('admin_bar_menu', array($this, 'remove_wp_logo'), 999);
        }
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_branding', 'gpcp_branding_theme_name');
        register_setting('gpcp_branding', 'gpcp_branding_login_logo');
        register_setting('gpcp_branding', 'gpcp_branding_admin_logo');
        register_setting('gpcp_branding', 'gpcp_branding_primary_color');
        register_setting('gpcp_branding', 'gpcp_branding_footer_text');
        register_setting('gpcp_branding', 'gpcp_branding_login_enabled');
        register_setting('gpcp_branding', 'gpcp_branding_admin_enabled');
        register_setting('gpcp_branding', 'gpcp_branding_remove_wp_logo');
    }

    /**
     * Customize login styles
     */
    public function customize_login_styles()
    {
        $logo_url = get_option('gpcp_branding_login_logo');
        $primary_color = get_option('gpcp_branding_primary_color', '#2271b1');
        ?>
        <style type="text/css">
            <?php if ($logo_url): ?>
            .login h1 a {
                background-image: url(<?php echo esc_url($logo_url); ?>);
                background-size: contain;
                width: 300px;
                height: 80px;
                background-repeat: no-repeat;
                background-position: center;
            }
            <?php endif; ?>
            
            .login form .submit input,
            .wp-core-ui .button-primary {
                background-color: <?php echo esc_attr($primary_color); ?>;
                border-color: <?php echo esc_attr($primary_color); ?>;
            }
            
            .login form .submit input:hover,
            .wp-core-ui .button-primary:hover {
                background-color: <?php echo esc_attr($this->darken_color($primary_color, 10)); ?>;
                border-color: <?php echo esc_attr($this->darken_color($primary_color, 10)); ?>;
            }
            
            a {
                color: <?php echo esc_attr($primary_color); ?>;
            }
            
            a:hover {
                color: <?php echo esc_attr($this->darken_color($primary_color, 10)); ?>;
            }
        </style>
        <?php
    }

    /**
     * Customize login logo URL
     */
    public function customize_login_logo_url()
    {
        return home_url();
    }

    /**
     * Customize login logo text
     */
    public function customize_login_logo_text()
    {
        return get_bloginfo('name');
    }

    /**
     * Customize admin styles
     */
    public function customize_admin_styles()
    {
        $admin_logo = get_option('gpcp_branding_admin_logo');
        $primary_color = get_option('gpcp_branding_primary_color', '#2271b1');
        ?>
        <style type="text/css">
            <?php if ($admin_logo): ?>
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item {
                padding: 0 7px 0 7px;
            }
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon {
                background-image: url(<?php echo esc_url($admin_logo); ?>);
                background-size: 20px 20px;
                background-repeat: no-repeat;
                background-position: center;
                width: 20px;
                height: 20px;
            }
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
                content: '';
            }
            <?php endif; ?>
            
            .wp-core-ui .button-primary {
                background-color: <?php echo esc_attr($primary_color); ?>;
                border-color: <?php echo esc_attr($primary_color); ?>;
            }
            
            .wp-core-ui .button-primary:hover {
                background-color: <?php echo esc_attr($this->darken_color($primary_color, 10)); ?>;
                border-color: <?php echo esc_attr($this->darken_color($primary_color, 10)); ?>;
            }
            
            a {
                color: <?php echo esc_attr($primary_color); ?>;
            }
            
            .postbox .hndle {
                border-left-color: <?php echo esc_attr($primary_color); ?>;
            }
        </style>
        <?php
    }

    /**
     * Customize admin footer
     */
    public function customize_admin_footer($text)
    {
        $footer_text = get_option('gpcp_branding_footer_text');
        if ($footer_text) {
            return $footer_text;
        }
        return $text;
    }

    /**
     * Remove WordPress logo from admin bar
     */
    public function remove_wp_logo($wp_admin_bar)
    {
        $wp_admin_bar->remove_node('wp-logo');
    }

    /**
     * Darken color
     */
    private function darken_color($color, $percent)
    {
        $color = str_replace('#', '', $color);
        $rgb = array_map('hexdec', str_split($color, 2));
        foreach ($rgb as &$c) {
            $c = max(0, min(255, $c - ($c * $percent / 100)));
        }
        return '#' . implode('', array_map(function($c) {
            return str_pad(dechex(round($c)), 2, '0', STR_PAD_LEFT);
        }, $rgb));
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_branding_save'])) {
            check_admin_referer('gpcp_branding_save');
            
            update_option('gpcp_branding_theme_name', sanitize_text_field($_POST['gpcp_branding_theme_name']));
            update_option('gpcp_branding_login_logo', esc_url_raw($_POST['gpcp_branding_login_logo']));
            update_option('gpcp_branding_admin_logo', esc_url_raw($_POST['gpcp_branding_admin_logo']));
            update_option('gpcp_branding_primary_color', sanitize_hex_color($_POST['gpcp_branding_primary_color']));
            update_option('gpcp_branding_footer_text', wp_kses_post($_POST['gpcp_branding_footer_text']));
            update_option('gpcp_branding_login_enabled', isset($_POST['gpcp_branding_login_enabled']));
            update_option('gpcp_branding_admin_enabled', isset($_POST['gpcp_branding_admin_enabled']));
            update_option('gpcp_branding_remove_wp_logo', isset($_POST['gpcp_branding_remove_wp_logo']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $theme_name = get_option('gpcp_branding_theme_name', 'GP Child Pro');
        $login_logo = get_option('gpcp_branding_login_logo');
        $admin_logo = get_option('gpcp_branding_admin_logo');
        $primary_color = get_option('gpcp_branding_primary_color', '#2271b1');
        $footer_text = get_option('gpcp_branding_footer_text');
        $login_enabled = get_option('gpcp_branding_login_enabled', false);
        $admin_enabled = get_option('gpcp_branding_admin_enabled', false);
        $remove_wp_logo = get_option('gpcp_branding_remove_wp_logo', false);
        ?>
        <div class="wrap">
            <h1><?php _e('Branding', 'gpcp'); ?></h1>
            <p><?php _e('Personaliza completamente la apariencia del panel de WordPress y la página de login para que coincida con tu marca.', 'gpcp'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('gpcp_branding_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="gpcp_branding_theme_name"><?php _e('Nombre del Tema', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gpcp_branding_theme_name" name="gpcp_branding_theme_name" value="<?php echo esc_attr($theme_name); ?>" class="regular-text" />
                            <p class="description"><?php _e('Este nombre aparecerá en los menús y en el panel de administración.', 'gpcp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_branding_login_logo"><?php _e('Logo de Login', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="gpcp_branding_login_logo" name="gpcp_branding_login_logo" value="<?php echo esc_url($login_logo); ?>" class="regular-text" />
                            <button type="button" class="button gpcp-upload-button" data-target="gpcp_branding_login_logo"><?php _e('Subir Logo', 'gpcp'); ?></button>
                            <p class="description"><?php _e('URL del logo para la página de login (recomendado: 300x80px).', 'gpcp'); ?></p>
                            <?php if ($login_logo): ?>
                            <p><img src="<?php echo esc_url($login_logo); ?>" style="max-width: 300px; height: auto;" /></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_branding_admin_logo"><?php _e('Logo del Panel de Admin', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="gpcp_branding_admin_logo" name="gpcp_branding_admin_logo" value="<?php echo esc_url($admin_logo); ?>" class="regular-text" />
                            <button type="button" class="button gpcp-upload-button" data-target="gpcp_branding_admin_logo"><?php _e('Subir Logo', 'gpcp'); ?></button>
                            <p class="description"><?php _e('URL del logo para la barra superior del admin (recomendado: 20x20px).', 'gpcp'); ?></p>
                            <?php if ($admin_logo): ?>
                            <p><img src="<?php echo esc_url($admin_logo); ?>" style="max-width: 20px; height: auto;" /></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_branding_primary_color"><?php _e('Color Principal', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="gpcp_branding_primary_color" name="gpcp_branding_primary_color" value="<?php echo esc_attr($primary_color); ?>" />
                            <p class="description"><?php _e('Color principal para botones, enlaces y elementos destacados.', 'gpcp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_branding_footer_text"><?php _e('Footer Personalizado', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <textarea id="gpcp_branding_footer_text" name="gpcp_branding_footer_text" rows="3" class="large-text"><?php echo esc_textarea($footer_text); ?></textarea>
                            <p class="description"><?php _e('Texto personalizado para el footer del admin. Puedes usar HTML básico.', 'gpcp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Opciones', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_branding_login_enabled" value="1" <?php checked($login_enabled); ?> />
                                <?php _e('Activar personalización de login', 'gpcp'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="gpcp_branding_admin_enabled" value="1" <?php checked($admin_enabled); ?> />
                                <?php _e('Activar personalización de admin', 'gpcp'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="gpcp_branding_remove_wp_logo" value="1" <?php checked($remove_wp_logo); ?> />
                                <?php _e('Remover logo de WordPress de la barra superior', 'gpcp'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_branding_save'); ?>
            </form>
        </div>
        <?php
    }
}



