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
        
        // Implementar funcionalidades de imágenes
        $this->implement_image_features();
    }

    /**
     * Implement image features
     */
    private function implement_image_features()
    {
        // Limit image sizes
        if (get_option('gpcp_images_limit_sizes', false)) {
            add_filter('intermediate_image_sizes_advanced', array($this, 'limit_image_sizes'), 10, 1);
        }

        // WebP conversion
        if (get_option('gpcp_images_webp_conversion', false)) {
            $gd_available = extension_loaded('gd') && function_exists('imagewebp');
            if ($gd_available) {
                add_filter('wp_handle_upload', array($this, 'convert_to_webp'), 10, 2);
                add_filter('wp_get_attachment_image_src', array($this, 'webp_image_src'), 10, 4);
            }
        }

        // Auto-complete alt and title
        if (get_option('gpcp_images_auto_alt_title', false)) {
            add_action('add_attachment', array($this, 'auto_complete_alt_title_on_upload'), 10, 1);
            add_filter('attachment_fields_to_save', array($this, 'auto_complete_alt_title'), 10, 2);
        }

        // Compress on upload
        if (get_option('gpcp_images_compress_on_upload', false)) {
            add_filter('wp_handle_upload_prefilter', array($this, 'compress_image_on_upload'));
        }
    }

    /**
     * Limit image sizes
     */
    public function limit_image_sizes($sizes)
    {
        // Get only the sizes defined in Settings > Media
        $allowed_sizes = get_intermediate_image_sizes();
        $filtered_sizes = array();
        
        foreach ($allowed_sizes as $size) {
            if (isset($sizes[$size])) {
                $filtered_sizes[$size] = $sizes[$size];
            }
        }
        
        return $filtered_sizes;
    }

    /**
     * Convert to WebP
     */
    public function convert_to_webp($file, $filename)
    {
        if (!isset($file['type']) || !in_array($file['type'], array('image/jpeg', 'image/png'))) {
            return $file;
        }

        $image_path = $file['file'];
        $upload_dir = wp_upload_dir();
        $full_path = $upload_dir['basedir'] . '/' . $image_path;
        
        if (!file_exists($full_path)) {
            return $file;
        }

        // Create WebP version
        $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $full_path);
        
        if ($file['type'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($full_path);
        } else {
            $image = imagecreatefrompng($full_path);
        }

        if ($image && function_exists('imagewebp')) {
            imagewebp($image, $webp_path, 85);
            imagedestroy($image);
        }

        return $file;
    }

    /**
     * WebP image src
     */
    public function webp_image_src($image, $attachment_id, $size, $icon)
    {
        if (!$image || $icon) {
            return $image;
        }

        $upload_dir = wp_upload_dir();
        $image_path = $image[0];
        $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image_path);
        $webp_file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $webp_path);

        if (file_exists($webp_file)) {
            $image[0] = $webp_path;
        }

        return $image;
    }

    /**
     * Auto-complete alt and title when saving
     */
    public function auto_complete_alt_title($post, $attachment)
    {
        $attachment_id = $attachment->ID;
        
        // Get current alt and title
        $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        $title = get_the_title($attachment_id);

        // If alt is empty, use filename or title
        if (empty($alt)) {
            $file = get_attached_file($attachment_id);
            if ($file) {
                $filename = basename($file);
                $filename = pathinfo($filename, PATHINFO_FILENAME);
                $alt = ucwords(str_replace(array('-', '_'), ' ', $filename));
            } else {
                $alt = $title;
            }
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
        }

        // If title is empty, use alt
        if (empty($title)) {
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_title' => $alt
            ));
        }

        return $post;
    }

    /**
     * Auto-complete alt and title on upload
     */
    public function auto_complete_alt_title_on_upload($attachment_id)
    {
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return;
        }

        $file = get_attached_file($attachment_id);
        if (!$file) {
            return;
        }

        $filename = basename($file);
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $clean_name = ucwords(str_replace(array('-', '_'), ' ', $filename));

        // Update title if empty
        if (empty($attachment->post_title)) {
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_title' => $clean_name
            ));
        }

        // Update alt if empty
        $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        if (empty($alt)) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $clean_name);
        }
    }

    /**
     * Compress image on upload
     */
    public function compress_image_on_upload($file)
    {
        if (!isset($file['type']) || !in_array($file['type'], array('image/jpeg', 'image/png'))) {
            return $file;
        }

        $image_path = $file['tmp_name'];
        
        if ($file['type'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($image_path);
            if ($image) {
                // Compress JPEG with 85% quality
                imagejpeg($image, $image_path, 85);
                imagedestroy($image);
            }
        } elseif ($file['type'] == 'image/png') {
            $image = imagecreatefrompng($image_path);
            if ($image) {
                // Compress PNG
                imagepng($image, $image_path, 8);
                imagedestroy($image);
            }
        }

        return $file;
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_images', 'gpcp_images_limit_sizes');
        register_setting('gpcp_images', 'gpcp_images_webp_conversion');
        register_setting('gpcp_images', 'gpcp_images_auto_alt_title');
        register_setting('gpcp_images', 'gpcp_images_compress_on_upload');
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_images_save'])) {
            check_admin_referer('gpcp_images_save');
            
            update_option('gpcp_images_limit_sizes', isset($_POST['gpcp_images_limit_sizes']));
            update_option('gpcp_images_webp_conversion', isset($_POST['gpcp_images_webp_conversion']));
            update_option('gpcp_images_auto_alt_title', isset($_POST['gpcp_images_auto_alt_title']));
            update_option('gpcp_images_compress_on_upload', isset($_POST['gpcp_images_compress_on_upload']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $limit_sizes = get_option('gpcp_images_limit_sizes', false);
        $webp_conversion = get_option('gpcp_images_webp_conversion', false);
        $auto_alt_title = get_option('gpcp_images_auto_alt_title', false);
        $compress_on_upload = get_option('gpcp_images_compress_on_upload', false);
        
        // Check if GD extension is available for WebP
        $gd_available = extension_loaded('gd') && function_exists('imagewebp');
        ?>
        <div class="wrap">
            <h1><?php _e('Imágenes', 'gpcp'); ?></h1>
            <p><?php _e('Optimiza y gestiona las imágenes de tu sitio con estas configuraciones.', 'gpcp'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('gpcp_images_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Gestión de Tamaños', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_images_limit_sizes" value="1" <?php checked($limit_sizes); ?> />
                                <?php _e('Limitar tamaños de imagen generados', 'gpcp'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Solo crea los tamaños de imagen definidos en Ajustes > Medios. Reduce el espacio usado.', 'gpcp'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Conversión WebP', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_images_webp_conversion" value="1" <?php checked($webp_conversion); ?> <?php echo !$gd_available ? 'disabled' : ''; ?> />
                                <?php _e('Convertir imágenes a WebP automáticamente', 'gpcp'); ?>
                            </label>
                            <?php if (!$gd_available): ?>
                                <p class="description" style="color: #dc3232;">
                                    <strong><?php _e('⚠️ Requisito:', 'gpcp'); ?></strong> <?php _e('La extensión GD de PHP no está disponible. WebP requiere GD con soporte WebP.', 'gpcp'); ?>
                                </p>
                            <?php else: ?>
                                <p class="description">
                                    <?php _e('Convierte automáticamente imágenes JPG/PNG a WebP para reducir el tamaño. Incluye fallback para navegadores que no soportan WebP.', 'gpcp'); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Auto-completado', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_images_auto_alt_title" value="1" <?php checked($auto_alt_title); ?> />
                                <?php _e('Auto-completar alt y título de imágenes', 'gpcp'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Usa el nombre del archivo o el título del post para completar automáticamente los campos alt y título de las imágenes.', 'gpcp'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Compresión', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_images_compress_on_upload" value="1" <?php checked($compress_on_upload); ?> />
                                <?php _e('Comprimir imágenes al subir', 'gpcp'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Reduce la calidad de las imágenes al subirlas para ahorrar espacio (mantiene calidad visual aceptable).', 'gpcp'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_images_save'); ?>
            </form>
        </div>
        <?php
    }
}

