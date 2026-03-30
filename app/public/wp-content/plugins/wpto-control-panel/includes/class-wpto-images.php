<?php
/**
 * WPTO Images Module
 * Implementa todas las funciones de gestión de imágenes
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPTO_Images {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('wpto_images_options', array());
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Conversión WebP/AVIF
        if (!empty($this->options['webp_conversion'])) {
            add_filter('wp_handle_upload_prefilter', array($this, 'prepare_image_conversion'));
            add_filter('wp_generate_attachment_metadata', array($this, 'convert_to_webp_avif'), 10, 2);
        }
        
        // Control de tamaños
        if (!empty($this->options['size_control'])) {
            add_filter('intermediate_image_sizes_advanced', array($this, 'control_image_sizes'));
        }
        
        // Sobrescritura de duplicados
        if (!empty($this->options['overwrite_duplicates'])) {
            add_filter('wp_handle_upload_prefilter', array($this, 'handle_duplicate_overwrite'));
        }
        
        // Eliminación completa
        if (!empty($this->options['complete_deletion'])) {
            add_action('delete_attachment', array($this, 'delete_all_image_versions'));
        }
        
        // Optimización y compresión
        if (!empty($this->options['image_optimization'])) {
            add_filter('wp_handle_upload_prefilter', array($this, 'optimize_image_on_upload'));
            add_filter('jpeg_quality', array($this, 'set_jpeg_quality'));
        }
        
        // ALT automático
        if (!empty($this->options['auto_alt'])) {
            // Usar add_attachment hook que se ejecuta después de crear el attachment
            add_action('add_attachment', array($this, 'set_auto_alt_on_attachment'), 10, 1);
        }

        // ALT contextual
        if (!empty($this->options['contextual_alt'])) {
            add_action('save_post', array($this, 'set_contextual_alt_on_post'), 20, 3);
        }
    }
    
    /**
     * Preparar conversión de imagen
     */
    public function prepare_image_conversion($file) {
        // Validar que es una imagen
        $image_types = array('image/jpeg', 'image/png', 'image/gif');
        if (!in_array($file['type'], $image_types)) {
            return $file;
        }
        
        return $file;
    }
    
    /**
     * Convertir a WebP/AVIF
     */
    public function convert_to_webp_avif($metadata, $attachment_id) {
        // Validar attachment ID
        if (empty($attachment_id) || !is_numeric($attachment_id)) {
            return $metadata;
        }
        
        $file_path = get_attached_file($attachment_id);
        if (!$file_path || !file_exists($file_path)) {
            return $metadata;
        }
        
        // Verificar que sea una imagen válida
        $image_info = @getimagesize($file_path);
        if (!$image_info) {
            return $metadata;
        }
        
        // Validar calidad (entre 1 y 100)
        $quality = !empty($this->options['conversion_quality']) ? intval($this->options['conversion_quality']) : 85;
        $quality = max(1, min(100, $quality));
        
        // Generar WebP
        if (!empty($this->options['generate_webp'])) {
            try {
                $this->convert_to_webp($file_path, $quality);
            } catch (Exception $e) {
                // Log error pero no romper el proceso
                error_log('WPTO: Error al convertir a WebP: ' . $e->getMessage());
            }
        }
        
        // Generar AVIF
        if (!empty($this->options['generate_avif'])) {
            try {
                $this->convert_to_avif($file_path, $quality);
            } catch (Exception $e) {
                // Log error pero no romper el proceso
                error_log('WPTO: Error al convertir a AVIF: ' . $e->getMessage());
            }
        }
        
        return $metadata;
    }
    
    /**
     * Convertir a WebP
     */
    public function convert_to_webp($file_path, $quality) {
        // Validar que imagewebp esté disponible
        if (!function_exists('imagewebp')) {
            return false;
        }
        
        // Validar archivo
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return false;
        }
        
        // Verificar que no sea demasiado grande (límite de 50MB)
        if (filesize($file_path) > 50 * 1024 * 1024) {
            return false;
        }
        
        $file_info = wp_check_filetype($file_path);
        $mime_type = isset($file_info['type']) ? $file_info['type'] : '';
        
        // Solo procesar imágenes soportadas
        if (!in_array($mime_type, array('image/jpeg', 'image/png', 'image/gif'))) {
            return false;
        }
        
        $webp_path = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $file_path);
        
        // Verificar que el directorio sea escribible
        $webp_dir = dirname($webp_path);
        if (!is_writable($webp_dir)) {
            return false;
        }
        
        $image = null;
        
        try {
            switch ($mime_type) {
                case 'image/jpeg':
                    if (!function_exists('imagecreatefromjpeg')) {
                        return false;
                    }
                    $image = @imagecreatefromjpeg($file_path);
                    break;
                case 'image/png':
                    if (!function_exists('imagecreatefrompng')) {
                        return false;
                    }
                    $image = @imagecreatefrompng($file_path);
                    break;
                case 'image/gif':
                    if (!function_exists('imagecreatefromgif')) {
                        return false;
                    }
                    $image = @imagecreatefromgif($file_path);
                    break;
            }
            
            if (!$image) {
                return false;
            }
            
            // Preservar transparencia para PNG
            if ($mime_type === 'image/png') {
                imagealphablending($image, false);
                imagesavealpha($image, true);
            }
            
            // Convertir a WebP
            $result = @imagewebp($image, $webp_path, $quality);
            imagedestroy($image);
            
            return $result !== false;
            
        } catch (Exception $e) {
            if ($image) {
                imagedestroy($image);
            }
            return false;
        }
    }
    
    /**
     * Convertir a AVIF
     */
    public function convert_to_avif($file_path, $quality) {
        // AVIF requiere Imagick con soporte AVIF
        if (!extension_loaded('imagick') || !class_exists('Imagick')) {
            return false;
        }
        
        // Validar archivo
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return false;
        }
        
        // Verificar que no sea demasiado grande (límite de 50MB)
        if (filesize($file_path) > 50 * 1024 * 1024) {
            return false;
        }
        
        // Verificar que Imagick soporte AVIF
        $imagick_formats = Imagick::queryFormats();
        if (!in_array('AVIF', $imagick_formats)) {
            return false;
        }
        
        $avif_path = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.avif', $file_path);
        
        // Verificar que el directorio sea escribible
        $avif_dir = dirname($avif_path);
        if (!is_writable($avif_dir)) {
            return false;
        }
        
        try {
            $imagick = new Imagick($file_path);
            
            // Verificar que sea una imagen válida
            if ($imagick->getImageFormat() === false) {
                $imagick->destroy();
                return false;
            }
            
            $imagick->setImageFormat('avif');
            $imagick->setImageCompressionQuality($quality);
            
            $result = $imagick->writeImage($avif_path);
            $imagick->clear();
            $imagick->destroy();
            
            return $result !== false;
            
        } catch (Exception $e) {
            error_log('WPTO: Error al convertir a AVIF: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Controlar tamaños de imagen
     */
    public function control_image_sizes($sizes) {
        if (!empty($this->options['disable_theme_sizes'])) {
            // Mantener solo tamaños estándar de WordPress
            $standard_sizes = array('thumbnail', 'medium', 'medium_large', 'large');
            $sizes = array_intersect_key($sizes, array_flip($standard_sizes));
        }
        
        return $sizes;
    }
    
    /**
     * Manejar sobrescritura de duplicados
     */
    public function handle_duplicate_overwrite($file) {
        $upload_dir = wp_upload_dir();
        $file_name = basename($file['name']);
        $file_path = $upload_dir['path'] . '/' . $file_name;
        
        if (file_exists($file_path)) {
            if (empty($this->options['confirm_overwrite'])) {
                // Sobrescribir directamente
                @unlink($file_path);
            }
        }
        
        return $file;
    }
    
    /**
     * Eliminar todas las versiones de imagen
     */
    public function delete_all_image_versions($attachment_id) {
        // Validar attachment ID
        if (empty($attachment_id) || !is_numeric($attachment_id)) {
            return;
        }
        
        $file_path = get_attached_file($attachment_id);
        if (!$file_path || !file_exists($file_path)) {
            return;
        }
        
        $file_dir = dirname($file_path);
        $file_name = pathinfo($file_path, PATHINFO_FILENAME);
        
        // Verificar que el directorio exista y sea escribible
        if (!is_dir($file_dir) || !is_writable($file_dir)) {
            return;
        }
        
        // Eliminar archivo original (solo si existe)
        if (file_exists($file_path) && is_writable($file_path)) {
            @unlink($file_path);
        }
        
        // Eliminar versiones WebP/AVIF
        $webp_path = $file_dir . '/' . $file_name . '.webp';
        if (file_exists($webp_path) && is_writable($webp_path)) {
            @unlink($webp_path);
        }
        
        $avif_path = $file_dir . '/' . $file_name . '.avif';
        if (file_exists($avif_path) && is_writable($avif_path)) {
            @unlink($avif_path);
        }
        
        // Eliminar todos los tamaños
        $metadata = wp_get_attachment_metadata($attachment_id);
        if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size) {
                if (!empty($size['file']) && is_string($size['file'])) {
                    $size_path = $file_dir . '/' . basename($size['file']);
                    if (file_exists($size_path) && is_writable($size_path)) {
                        @unlink($size_path);
                    }
                }
            }
        }
    }
    
    /**
     * Optimizar imagen al subir
     */
    public function optimize_image_on_upload($file) {
        // Validar que sea una imagen
        if (empty($file['type']) || strpos($file['type'], 'image/') !== 0) {
            return $file;
        }
        
        // Validar que el archivo temporal exista
        if (empty($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return $file;
        }
        
        // Validar max_width
        $max_width = !empty($this->options['max_width']) ? intval($this->options['max_width']) : 2048;
        $max_width = max(100, min(10000, $max_width)); // Límites razonables
        
        try {
            $image_info = @getimagesize($file['tmp_name']);
            if ($image_info && isset($image_info[0]) && $image_info[0] > $max_width) {
                $result = $this->resize_image($file['tmp_name'], $max_width);
                if (!$result) {
                    // Si falla el redimensionamiento, devolver archivo original
                    return $file;
                }
            }
        } catch (Exception $e) {
            // En caso de error, devolver archivo original
            error_log('WPTO: Error al optimizar imagen: ' . $e->getMessage());
            return $file;
        }
        
        return $file;
    }
    
    /**
     * Redimensionar imagen
     */
    private function resize_image($file_path, $max_width) {
        // Validar archivo
        if (!file_exists($file_path) || !is_readable($file_path) || !is_writable($file_path)) {
            return false;
        }
        
        // Verificar que no sea demasiado grande (límite de 50MB)
        if (filesize($file_path) > 50 * 1024 * 1024) {
            return false;
        }
        
        $image_info = @getimagesize($file_path);
        if (!$image_info || !isset($image_info[0]) || !isset($image_info[1])) {
            return false;
        }
        
        $width = $image_info[0];
        $height = $image_info[1];
        
        // Validar dimensiones
        if ($width <= 0 || $height <= 0) {
            return false;
        }
        
        if ($width <= $max_width) {
            return true; // No necesita redimensionar
        }
        
        $new_width = $max_width;
        $new_height = intval(($height * $max_width) / $width);
        
        // Validar nuevas dimensiones
        if ($new_width <= 0 || $new_height <= 0) {
            return false;
        }
        
        $image = null;
        $mime_type = isset($image_info['mime']) ? $image_info['mime'] : '';
        
        // Cargar imagen según tipo
        try {
            switch ($mime_type) {
                case 'image/jpeg':
                    if (!function_exists('imagecreatefromjpeg')) {
                        return false;
                    }
                    $image = @imagecreatefromjpeg($file_path);
                    break;
                case 'image/png':
                    if (!function_exists('imagecreatefrompng')) {
                        return false;
                    }
                    $image = @imagecreatefrompng($file_path);
                    break;
                case 'image/gif':
                    if (!function_exists('imagecreatefromgif')) {
                        return false;
                    }
                    $image = @imagecreatefromgif($file_path);
                    break;
                default:
                    return false; // Tipo no soportado
            }
            
            if (!$image) {
                return false;
            }
            
            $new_image = @imagecreatetruecolor($new_width, $new_height);
            if (!$new_image) {
                imagedestroy($image);
                return false;
            }
            
            // Preservar transparencia para PNG y GIF
            if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
            }
            
            // Redimensionar
            $resize_result = @imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            if (!$resize_result) {
                imagedestroy($image);
                imagedestroy($new_image);
                return false;
            }
            
            // Guardar imagen redimensionada
            $save_result = false;
            switch ($mime_type) {
                case 'image/jpeg':
                    $save_result = @imagejpeg($new_image, $file_path, $this->get_jpeg_quality());
                    break;
                case 'image/png':
                    $save_result = @imagepng($new_image, $file_path, 9);
                    break;
                case 'image/gif':
                    $save_result = @imagegif($new_image, $file_path);
                    break;
            }
            
            imagedestroy($image);
            imagedestroy($new_image);
            
            return $save_result !== false;
            
        } catch (Exception $e) {
            if ($image) {
                imagedestroy($image);
            }
            error_log('WPTO: Error al redimensionar imagen: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Establecer calidad JPEG
     */
    public function set_jpeg_quality($quality) {
        return !empty($this->options['jpeg_quality']) ? intval($this->options['jpeg_quality']) : $quality;
    }
    
    /**
     * Obtener calidad JPEG
     */
    private function get_jpeg_quality() {
        return !empty($this->options['jpeg_quality']) ? intval($this->options['jpeg_quality']) : 82;
    }
    
    /**
     * Establecer ALT automático
     * Nota: Este hook se ejecuta antes de que el attachment esté completamente creado
     * Usamos un hook diferente para asegurar que funcione correctamente
     */
    public function set_auto_alt($data, $postarr) {
        // Este método se mantiene por compatibilidad, pero usamos add_attachment hook
        return $data;
    }
    
    /**
     * Establecer ALT automático después de crear attachment
     */
    public function set_auto_alt_on_attachment($attachment_id) {
        // Verificar que sea una imagen
        $mime_type = get_post_mime_type($attachment_id);
        if (!$mime_type || strpos($mime_type, 'image/') !== 0) {
            return;
        }
        
        // Verificar que no tenga ALT ya establecido
        $existing_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        if (!empty($existing_alt)) {
            return; // Ya tiene ALT, no sobrescribir
        }
        
        // Obtener nombre de archivo
        $file_path = get_attached_file($attachment_id);
        if (!$file_path) {
            return;
        }
        
        $file_name = basename($file_path);
        
        // Generar ALT desde nombre de archivo
        $alt_text = $this->generate_alt_from_filename($file_name);
        
        if (!empty($alt_text)) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        }
    }
    
    /**
     * Generar ALT desde nombre de archivo
     */
    private function generate_alt_from_filename($filename) {
        // Remover extensión
        $name = pathinfo($filename, PATHINFO_FILENAME);
        
        // Remover guiones y números
        $name = preg_replace('/[-\d]+/', ' ', $name);
        
        // Capitalizar palabras
        $name = ucwords($name);
        
        // Limpiar espacios múltiples
        $name = preg_replace('/\s+/', ' ', $name);
        
        return trim($name);
    }

    /**
     * Establecer ALT contextual desde el post
     */
    public function set_contextual_alt_on_post($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!$update || empty($post) || !is_object($post)) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (empty($post->post_content)) {
            return;
        }

        $ids = $this->extract_attachment_ids_from_content($post->post_content);
        if (empty($ids)) {
            return;
        }

        $title = sanitize_text_field($post->post_title);
        if (empty($title)) {
            return;
        }

        foreach ($ids as $attachment_id) {
            $existing_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            if (!empty($existing_alt)) {
                continue;
            }
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $title);
        }
    }

    /**
     * Extraer IDs de attachments desde contenido
     */
    private function extract_attachment_ids_from_content($content) {
        $ids = array();
        if (preg_match_all('/wp-image-(\\d+)/', $content, $matches)) {
            foreach ($matches[1] as $id) {
                $id = intval($id);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }
        return array_values(array_unique($ids));
    }
}
