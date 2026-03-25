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
        
        // Implementar auto-completado si está activado
        if (get_option('gpcp_seo_auto_complete_enabled', false)) {
            add_action('save_post', array($this, 'auto_complete_seo'), 10, 2);
        }
    }

    /**
     * Auto-complete SEO metadata
     */
    public function auto_complete_seo($post_id, $post)
    {
        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Only for published posts
        if ($post->post_status !== 'publish' && $post->post_status !== 'draft') {
            return;
        }

        // Auto-complete title
        if (get_option('gpcp_seo_auto_title', false)) {
            $current_seo_title = get_post_meta($post_id, '_gpcp_seo_title', true);
            if (empty($current_seo_title)) {
                $title = get_the_title($post_id);
                if (!empty($title)) {
                    update_post_meta($post_id, '_gpcp_seo_title', $title);
                }
            }
        }

        // Auto-complete description
        if (get_option('gpcp_seo_auto_description', false)) {
            $current_seo_description = get_post_meta($post_id, '_gpcp_seo_description', true);
            if (empty($current_seo_description)) {
                $description = '';
                
                // Try excerpt first
                $excerpt = get_the_excerpt($post_id);
                if (!empty($excerpt)) {
                    $description = wp_trim_words($excerpt, 25, '');
                } else {
                    // Use content
                    $content = strip_shortcodes($post->post_content);
                    $content = wp_strip_all_tags($content);
                    $description = wp_trim_words($content, 25, '');
                }
                
                // Limit to 160 characters
                if (mb_strlen($description) > 160) {
                    $description = mb_substr($description, 0, 157) . '...';
                }
                
                if (!empty($description)) {
                    update_post_meta($post_id, '_gpcp_seo_description', $description);
                }
            }
        }

        // Auto-complete keywords
        if (get_option('gpcp_seo_auto_keywords', false)) {
            $current_seo_keywords = get_post_meta($post_id, '_gpcp_seo_keywords', true);
            if (empty($current_seo_keywords)) {
                $keywords = array();
                
                // Get tags
                $tags = get_the_tags($post_id);
                if ($tags && !is_wp_error($tags)) {
                    foreach ($tags as $tag) {
                        $keywords[] = $tag->name;
                    }
                }
                
                // Get categories
                $categories = get_the_category($post_id);
                if ($categories && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        $keywords[] = $category->name;
                    }
                }
                
                if (!empty($keywords)) {
                    update_post_meta($post_id, '_gpcp_seo_keywords', implode(', ', array_unique($keywords)));
                }
            }
        }
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_seo', 'gpcp_seo_auto_complete_enabled');
        register_setting('gpcp_seo', 'gpcp_seo_auto_title');
        register_setting('gpcp_seo', 'gpcp_seo_auto_description');
        register_setting('gpcp_seo', 'gpcp_seo_auto_keywords');
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_seo_save'])) {
            check_admin_referer('gpcp_seo_save');
            
            update_option('gpcp_seo_auto_complete_enabled', isset($_POST['gpcp_seo_auto_complete_enabled']));
            update_option('gpcp_seo_auto_title', isset($_POST['gpcp_seo_auto_title']));
            update_option('gpcp_seo_auto_description', isset($_POST['gpcp_seo_auto_description']));
            update_option('gpcp_seo_auto_keywords', isset($_POST['gpcp_seo_auto_keywords']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $auto_complete_enabled = get_option('gpcp_seo_auto_complete_enabled', false);
        $auto_title = get_option('gpcp_seo_auto_title', false);
        $auto_description = get_option('gpcp_seo_auto_description', false);
        $auto_keywords = get_option('gpcp_seo_auto_keywords', false);
        ?>
        <div class="wrap">
            <h1><?php _e('SEO', 'gpcp'); ?></h1>
            <p><?php _e('Configura el auto-completado de metadatos SEO para tus posts y páginas.', 'gpcp'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('gpcp_seo_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Auto-completado SEO', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_seo_auto_complete_enabled" value="1" <?php checked($auto_complete_enabled); ?> />
                                <?php _e('Activar auto-completado de metadatos SEO', 'gpcp'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Cuando crees o edites un post, se generarán automáticamente título SEO, descripción y keywords si están vacíos.', 'gpcp'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Opciones de Auto-completado', 'gpcp'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="gpcp_seo_auto_title" value="1" <?php checked($auto_title); ?> />
                                    <?php _e('Auto-completar título SEO', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Usa el título del post si el título SEO está vacío.', 'gpcp'); ?></p>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_seo_auto_description" value="1" <?php checked($auto_description); ?> />
                                    <?php _e('Auto-completar descripción SEO', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Genera descripción desde el excerpt o primeros 160 caracteres del contenido.', 'gpcp'); ?></p>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_seo_auto_keywords" value="1" <?php checked($auto_keywords); ?> />
                                    <?php _e('Auto-completar keywords', 'gpcp'); ?>
                                </label>
                                <p class="description"><?php _e('Genera keywords desde las etiquetas y categorías del post.', 'gpcp'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                
                <p>
                    <strong><?php _e('💡 Tip:', 'gpcp'); ?></strong>
                    <?php _e('Usa el', 'gpcp'); ?> <a href="<?php echo admin_url('admin.php?page=gpcp-seo-manager'); ?>"><?php _e('Gestor SEO', 'gpcp'); ?></a> <?php _e('para gestionar el SEO de todos tus posts desde una tabla centralizada.', 'gpcp'); ?>
                </p>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_seo_save'); ?>
            </form>
        </div>
        <?php
    }
}

