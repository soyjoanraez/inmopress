<?php
/**
 * GPCP Optimization Module
 *
 * Optimization features
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Optimization class
 */
class GPCP_Optimization
{
    /**
     * Instance of this class
     *
     * @var GPCP_Optimization
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Optimization
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
        
        // Implementar optimizaciones
        $this->implement_optimizations();
    }

    /**
     * Implement optimizations
     */
    private function implement_optimizations()
    {
        // Disable emojis
        if (get_option('gpcp_optimization_disable_emojis', false)) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter('tiny_mce_plugins', array($this, 'disable_emojis_tinymce'));
            add_filter('wp_resource_hints', array($this, 'disable_emojis_dns_prefetch'), 10, 2);
        }

        // Disable embeds
        if (get_option('gpcp_optimization_disable_embeds', false)) {
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
            remove_action('wp_head', 'wp_oembed_add_host_js');
            remove_action('rest_api_init', 'wp_oembed_register_route');
            remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
            add_filter('embed_oembed_discover', '__return_false');
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
            remove_action('wp_head', 'wp_oembed_add_host_js');
        }

        // Remove query strings
        if (get_option('gpcp_optimization_remove_query_strings', false)) {
            add_filter('script_loader_src', array($this, 'remove_query_strings'), 15, 1);
            add_filter('style_loader_src', array($this, 'remove_query_strings'), 15, 1);
        }

        // Defer JavaScript
        if (get_option('gpcp_optimization_defer_javascript', false)) {
            add_filter('script_loader_tag', array($this, 'defer_javascript'), 10, 2);
        }

        // Lazy load images
        if (get_option('gpcp_optimization_lazy_load_images', false)) {
            add_filter('the_content', array($this, 'lazy_load_images_content'), 10, 1);
            add_filter('wp_get_attachment_image_attributes', array($this, 'lazy_load_images_attributes'), 10, 3);
        }

        // Remove WP version
        if (get_option('gpcp_optimization_remove_wp_version', false)) {
            remove_action('wp_head', 'wp_generator');
            add_filter('the_generator', '__return_empty_string');
        }

        // Disable comments
        if (get_option('gpcp_optimization_disable_comments', false)) {
            add_filter('comments_open', '__return_false', 20, 2);
            add_filter('pings_open', '__return_false', 20, 2);
            add_filter('comments_array', '__return_empty_array', 10, 2);
            add_action('admin_init', array($this, 'disable_comments_admin'));
            add_action('admin_menu', array($this, 'remove_comments_menu'));
        }

        // Limit revisions
        if (get_option('gpcp_optimization_limit_revisions', false)) {
            if (!defined('WP_POST_REVISIONS')) {
                define('WP_POST_REVISIONS', 3);
            }
        }
    }

    /**
     * Disable emojis in TinyMCE
     */
    public function disable_emojis_tinymce($plugins)
    {
        if (is_array($plugins)) {
            return array_diff($plugins, array('wpemoji'));
        } else {
            return array();
        }
    }

    /**
     * Disable emoji DNS prefetch
     */
    public function disable_emojis_dns_prefetch($urls, $relation_type)
    {
        if ('dns-prefetch' == $relation_type) {
            $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/');
            $urls = array_diff($urls, array($emoji_svg_url));
        }
        return $urls;
    }

    /**
     * Remove query strings
     */
    public function remove_query_strings($src)
    {
        if (strpos($src, '?ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }

    /**
     * Defer JavaScript
     */
    public function defer_javascript($tag, $handle)
    {
        // Skip defer for specific scripts that need to load immediately
        $skip_defer = array('jquery-core', 'jquery-migrate');
        
        if (in_array($handle, $skip_defer)) {
            return $tag;
        }
        
        // Add defer attribute
        return str_replace(' src', ' defer src', $tag);
    }

    /**
     * Lazy load images in content
     */
    public function lazy_load_images_content($content)
    {
        if (is_admin() || is_feed() || is_preview()) {
            return $content;
        }

        // Replace img tags with lazy loading
        $content = preg_replace_callback(
            '/<img([^>]+?)>/i',
            function($matches) {
                $img = $matches[0];
                // Skip if already has loading attribute
                if (strpos($img, 'loading=') !== false) {
                    return $img;
                }
                // Add loading="lazy"
                return str_replace('<img', '<img loading="lazy"', $img);
            },
            $content
        );

        return $content;
    }

    /**
     * Lazy load images attributes
     */
    public function lazy_load_images_attributes($attr, $attachment, $size)
    {
        if (!isset($attr['loading'])) {
            $attr['loading'] = 'lazy';
        }
        return $attr;
    }

    /**
     * Disable comments in admin
     */
    public function disable_comments_admin()
    {
        $post_types = get_post_types();
        foreach ($post_types as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }

    /**
     * Remove comments menu
     */
    public function remove_comments_menu()
    {
        remove_menu_page('edit-comments.php');
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_optimization', 'gpcp_optimization_disable_emojis');
        register_setting('gpcp_optimization', 'gpcp_optimization_disable_embeds');
        register_setting('gpcp_optimization', 'gpcp_optimization_remove_query_strings');
        register_setting('gpcp_optimization', 'gpcp_optimization_defer_javascript');
        register_setting('gpcp_optimization', 'gpcp_optimization_lazy_load_images');
        register_setting('gpcp_optimization', 'gpcp_optimization_remove_wp_version');
        register_setting('gpcp_optimization', 'gpcp_optimization_disable_comments');
        register_setting('gpcp_optimization', 'gpcp_optimization_limit_revisions');
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_optimization_save'])) {
            check_admin_referer('gpcp_optimization_save');
            
            update_option('gpcp_optimization_disable_emojis', isset($_POST['gpcp_optimization_disable_emojis']));
            update_option('gpcp_optimization_disable_embeds', isset($_POST['gpcp_optimization_disable_embeds']));
            update_option('gpcp_optimization_remove_query_strings', isset($_POST['gpcp_optimization_remove_query_strings']));
            update_option('gpcp_optimization_defer_javascript', isset($_POST['gpcp_optimization_defer_javascript']));
            update_option('gpcp_optimization_lazy_load_images', isset($_POST['gpcp_optimization_lazy_load_images']));
            update_option('gpcp_optimization_remove_wp_version', isset($_POST['gpcp_optimization_remove_wp_version']));
            update_option('gpcp_optimization_disable_comments', isset($_POST['gpcp_optimization_disable_comments']));
            update_option('gpcp_optimization_limit_revisions', isset($_POST['gpcp_optimization_limit_revisions']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $disable_emojis = get_option('gpcp_optimization_disable_emojis', false);
        $disable_embeds = get_option('gpcp_optimization_disable_embeds', false);
        $remove_query_strings = get_option('gpcp_optimization_remove_query_strings', false);
        $defer_javascript = get_option('gpcp_optimization_defer_javascript', false);
        $lazy_load_images = get_option('gpcp_optimization_lazy_load_images', false);
        $remove_wp_version = get_option('gpcp_optimization_remove_wp_version', false);
        $disable_comments = get_option('gpcp_optimization_disable_comments', false);
        $limit_revisions = get_option('gpcp_optimization_limit_revisions', false);
        ?>
        <div class="wrap">
            <h1><?php _e('Optimización', 'gpcp'); ?></h1>
            <p><?php _e('Mejora la velocidad y rendimiento de tu sitio con estas optimizaciones.', 'gpcp'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('gpcp_optimization_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Optimizaciones de Rendimiento', 'gpcp'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="gpcp_optimization_disable_emojis" value="1" <?php checked($disable_emojis); ?> />
                                    <?php _e('Deshabilitar emojis de WordPress', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Elimina scripts y estilos de emojis que no necesitas.', 'gpcp'); ?></p>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_optimization_disable_embeds" value="1" <?php checked($disable_embeds); ?> />
                                    <?php _e('Deshabilitar embeds oEmbed', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Elimina scripts de embeds si no los usas.', 'gpcp'); ?></p>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_optimization_remove_query_strings" value="1" <?php checked($remove_query_strings); ?> />
                                    <?php _e('Remover query strings de recursos estáticos', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Mejora el caching eliminando ?ver= de CSS/JS.', 'gpcp'); ?></p>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_optimization_defer_javascript" value="1" <?php checked($defer_javascript); ?> />
                                    <?php _e('Defer JavaScript', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Carga JavaScript de forma diferida para mejorar velocidad.', 'gpcp'); ?></p>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_optimization_lazy_load_images" value="1" <?php checked($lazy_load_images); ?> />
                                    <?php _e('Lazy load de imágenes', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Carga imágenes solo cuando son visibles en pantalla.', 'gpcp'); ?></p>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_optimization_remove_wp_version" value="1" <?php checked($remove_wp_version); ?> />
                                    <?php _e('Remover versión de WordPress del header', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Oculta la versión de WordPress por seguridad.', 'gpcp'); ?></p>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_optimization_disable_comments" value="1" <?php checked($disable_comments); ?> />
                                    <?php _e('Deshabilitar comentarios globalmente', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Desactiva comentarios en todo el sitio si no los usas.', 'gpcp'); ?></p>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_optimization_limit_revisions" value="1" <?php checked($limit_revisions); ?> />
                                    <?php _e('Limitar revisiones de posts', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Mantiene solo las últimas 3 revisiones para reducir el tamaño de la BD.', 'gpcp'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_optimization_save'); ?>
            </form>
        </div>
        <?php
    }
}

