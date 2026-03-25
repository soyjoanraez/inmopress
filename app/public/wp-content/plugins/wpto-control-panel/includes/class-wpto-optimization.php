<?php
/**
 * WPTO Optimization Module
 * Implementa todas las optimizaciones de rendimiento
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPTO_Optimization {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('wpto_optimization_options', array());
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Optimización de Base de Datos
        if (!empty($this->options['database_optimization'])) {
            $frequency = !empty($this->options['db_cleanup_frequency']) ? $this->options['db_cleanup_frequency'] : 'weekly';
            if (!wp_next_scheduled('wpto_db_cleanup')) {
                $schedule = $frequency === 'daily' ? 'daily' : ($frequency === 'monthly' ? 'monthly' : 'weekly');
                wp_schedule_event(time(), $schedule, 'wpto_db_cleanup');
            }
            add_action('wpto_db_cleanup', array($this, 'cleanup_database'));
        }
        
        // Desactivar recursos innecesarios
        if (!empty($this->options['disable_unnecessary'])) {
            if (!empty($this->options['disable_emojis'])) {
                remove_action('wp_head', 'print_emoji_detection_script', 7);
                remove_action('wp_print_styles', 'print_emoji_styles');
                remove_action('admin_print_scripts', 'print_emoji_detection_script');
                remove_action('admin_print_styles', 'print_emoji_styles');
            }
            
            if (!empty($this->options['disable_embeds'])) {
                remove_action('wp_head', 'wp_oembed_add_discovery_links');
                remove_action('wp_head', 'wp_oembed_add_host_js');
                remove_action('rest_api_init', 'wp_oembed_register_route');
                remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
            }
            
            if (!empty($this->options['disable_jquery_migrate'])) {
                add_action('wp_default_scripts', array($this, 'remove_jquery_migrate'));
            }
            
            if (!empty($this->options['disable_dashicons'])) {
                add_action('wp_enqueue_scripts', array($this, 'remove_dashicons'), 100);
            }
        }
        
        // Minificación
        if (!empty($this->options['minification'])) {
            add_action('wp_enqueue_scripts', array($this, 'minify_assets'), 999);
        }
        
        // Lazy Loading
        if (!empty($this->options['lazy_loading'])) {
            if (!empty($this->options['lazy_images'])) {
                add_filter('wp_get_attachment_image_attributes', array($this, 'add_lazy_loading'), 10, 3);
                add_filter('the_content', array($this, 'add_lazy_loading_to_content'), 10);
            }
            if (!empty($this->options['lazy_iframes'])) {
                add_filter('the_content', array($this, 'add_lazy_loading_to_iframes'), 10);
            }
        }
        
        // DNS Prefetch
        if (!empty($this->options['dns_prefetch'])) {
            add_action('wp_head', array($this, 'add_dns_prefetch'), 1);
        }
        
        // Optimización Gutenberg
        if (!empty($this->options['gutenberg_optimization'])) {
            add_action('enqueue_block_editor_assets', array($this, 'optimize_gutenberg'));
        }
        
        // Object Caching
        if (!empty($this->options['object_caching'])) {
            add_action('init', array($this, 'init_object_cache'));
        }
        
        // Limpieza de código
        if (!empty($this->options['code_cleanup'])) {
            add_action('template_redirect', array($this, 'start_output_buffering'));
        }
        
        // Control de Heartbeat API
        if (!empty($this->options['heartbeat_control'])) {
            if (!empty($this->options['disable_heartbeat_frontend'])) {
                add_action('init', array($this, 'disable_heartbeat_frontend'), 1);
            }
            if (!empty($this->options['heartbeat_frequency'])) {
                add_filter('heartbeat_settings', array($this, 'configure_heartbeat_frequency'));
            }
        }
        
        // Defer/Async de Scripts
        if (!empty($this->options['script_defer_async'])) {
            add_filter('script_loader_tag', array($this, 'add_defer_async_to_scripts'), 10, 2);
        }
        
        // Preload de Recursos Críticos
        if (!empty($this->options['resource_preload'])) {
            add_action('wp_head', array($this, 'add_resource_preload'), 1);
        }
        
        // Compresión GZIP/Brotli
        if (!empty($this->options['compression'])) {
            add_action('template_redirect', array($this, 'start_compression'), 1);
        }
        
        // Control de WP Cron
        if (!empty($this->options['wp_cron_control'])) {
            if (!empty($this->options['disable_wp_cron'])) {
                add_action('init', array($this, 'disable_wp_cron'), 1);
            }
        }
        
        // Optimización de Fuentes
        if (!empty($this->options['font_optimization'])) {
            add_action('wp_head', array($this, 'add_font_preload'), 1);
            add_filter('style_loader_tag', array($this, 'add_font_display_swap'), 10, 2);
        }
        
        // Critical CSS
        if (!empty($this->options['critical_css'])) {
            add_action('wp_head', array($this, 'inject_critical_css'), 1);
        }
        
        // CDN Integration
        if (!empty($this->options['cdn_enabled']) && !empty($this->options['cdn_url'])) {
            add_filter('wp_get_attachment_url', array($this, 'replace_url_with_cdn'), 10, 2);
            add_filter('style_loader_src', array($this, 'replace_url_with_cdn'), 10, 1);
            add_filter('script_loader_src', array($this, 'replace_url_with_cdn'), 10, 1);
        }
    }
    
    /**
     * Limpiar base de datos
     */
    public function cleanup_database() {
        global $wpdb;

        // Los cron jobs se ejecutan sin usuario, así que verificamos si es cron o usuario admin
        $is_cron = defined('DOING_CRON') && DOING_CRON;
        if (!$is_cron && !current_user_can('manage_options')) {
            return;
        }

        try {
            if (!empty($this->options['clean_revisions'])) {
                $deleted = $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'");
                if ($deleted !== false) {
                    do_action('wpto_activity_logged', 'db_cleanup', sprintf('Eliminadas %d revisiones', $deleted), 'success');
                }
            }

            if (!empty($this->options['clean_autodrafts'])) {
                $deleted = $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_status = 'auto-draft'");
                if ($deleted !== false) {
                    do_action('wpto_activity_logged', 'db_cleanup', sprintf('Eliminados %d autodrafts', $deleted), 'success');
                }
            }

            if (!empty($this->options['clean_spam'])) {
                $deleted = $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
                if ($deleted !== false) {
                    do_action('wpto_activity_logged', 'db_cleanup', sprintf('Eliminados %d comentarios spam', $deleted), 'success');
                }
            }

            // Optimizar tablas - obtener lista de tablas de la BD actual
            $db_name = DB_NAME;
            $tables = $wpdb->get_col("SHOW TABLES FROM `" . esc_sql($db_name) . "`");
            if ($tables && is_array($tables)) {
                foreach ($tables as $table_name) {
                    // Validar que el nombre de tabla solo contenga caracteres seguros
                    if (!empty($table_name) && preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
                        // Usar query directa con nombre de tabla sanitizado (no se puede usar prepare para nombres de tabla)
                        $wpdb->query("OPTIMIZE TABLE `" . esc_sql($table_name) . "`");
                    }
                }
            }
        } catch (Exception $e) {
            do_action('wpto_activity_logged', 'db_cleanup', 'Error en limpieza de BD: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Remover jQuery Migrate
     */
    public function remove_jquery_migrate($scripts) {
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];
            if ($script->deps) {
                $script->deps = array_diff($script->deps, array('jquery-migrate'));
            }
        }
    }
    
    /**
     * Remover Dashicons en frontend
     */
    public function remove_dashicons() {
        if (!is_admin()) {
            wp_deregister_style('dashicons');
        }
    }
    
    /**
     * Minificar assets (implementación básica)
     */
    public function minify_assets() {
        // Nota: Minificación real requiere bibliotecas externas
        // Esta es una implementación básica que solo remueve espacios en línea
        if (!empty($this->options['minify_css'])) {
            add_filter('style_loader_tag', array($this, 'minify_css_output'), 10, 2);
        }
        if (!empty($this->options['minify_js'])) {
            add_filter('script_loader_tag', array($this, 'minify_js_output'), 10, 2);
        }
    }
    
    /**
     * Minificar CSS output
     * Nota: Esta función intercepta el tag HTML del CSS y puede procesar archivos externos
     */
    public function minify_css_output($tag, $handle) {
        // No minificar en admin
        if (is_admin()) {
            return $tag;
        }
        
        // Extraer URL del CSS del tag
        if (preg_match('/href=["\']([^"\']+)["\']/', $tag, $matches)) {
            $css_url = $matches[1];
            
            // Solo procesar URLs locales
            if (strpos($css_url, home_url()) === false && strpos($css_url, '//') === 0) {
                return $tag; // URL externa o relativa, no procesar
            }
            
            // Convertir URL a ruta de archivo
            $css_path = $this->url_to_path($css_url);
            
            if ($css_path && file_exists($css_path)) {
                // Generar nombre de archivo minificado
                $minified_path = $this->get_minified_path($css_path, 'css');
                
                // Si el archivo minificado no existe o el original es más reciente, minificar
                if (!file_exists($minified_path) || filemtime($css_path) > filemtime($minified_path)) {
                    $this->minify_css_file($css_path, $minified_path);
                }
                
                // Si el archivo minificado existe, reemplazar URL en el tag
                if (file_exists($minified_path)) {
                    $minified_url = $this->path_to_url($minified_path);
                    $tag = str_replace($css_url, $minified_url, $tag);
                }
            }
        }
        
        return $tag;
    }
    
    /**
     * Minificar JS output
     * Nota: Esta función intercepta el tag HTML del JS y puede procesar archivos externos
     */
    public function minify_js_output($tag, $handle) {
        // No minificar en admin
        if (is_admin()) {
            return $tag;
        }
        
        // Scripts críticos que no deben minificarse
        $excluded = array('jquery', 'jquery-core', 'jquery-migrate');
        if (in_array($handle, $excluded)) {
            return $tag;
        }
        
        // Extraer URL del JS del tag
        if (preg_match('/src=["\']([^"\']+)["\']/', $tag, $matches)) {
            $js_url = $matches[1];
            
            // Solo procesar URLs locales
            if (strpos($js_url, home_url()) === false && strpos($js_url, '//') === 0) {
                return $tag; // URL externa o relativa, no procesar
            }
            
            // Convertir URL a ruta de archivo
            $js_path = $this->url_to_path($js_url);
            
            if ($js_path && file_exists($js_path)) {
                // Generar nombre de archivo minificado
                $minified_path = $this->get_minified_path($js_path, 'js');
                
                // Si el archivo minificado no existe o el original es más reciente, minificar
                if (!file_exists($minified_path) || filemtime($js_path) > filemtime($minified_path)) {
                    $this->minify_js_file($js_path, $minified_path);
                }
                
                // Si el archivo minificado existe, reemplazar URL en el tag
                if (file_exists($minified_path)) {
                    $minified_url = $this->path_to_url($minified_path);
                    $tag = str_replace($js_url, $minified_url, $tag);
                }
            }
        }
        
        return $tag;
    }
    
    /**
     * Minificar archivo CSS
     */
    private function minify_css_file($input_path, $output_path) {
        // Validaciones de seguridad
        if (empty($input_path) || empty($output_path)) {
            return false;
        }
        
        // Verificar que el archivo de entrada exista y sea legible
        if (!file_exists($input_path) || !is_readable($input_path)) {
            return false;
        }
        
        // Verificar que el archivo no sea demasiado grande (límite de 5MB)
        if (filesize($input_path) > 5 * 1024 * 1024) {
            return false;
        }
        
        // Leer contenido
        $css_content = @file_get_contents($input_path);
        if ($css_content === false) {
            return false;
        }
        
        // Minificación básica de CSS
        $minified = $this->minify_css_string($css_content);
        
        // Crear directorio si no existe
        $output_dir = dirname($output_path);
        if (!file_exists($output_dir)) {
            if (!wp_mkdir_p($output_dir)) {
                return false;
            }
        }
        
        // Verificar permisos de escritura
        if (!is_writable($output_dir)) {
            return false;
        }
        
        // Escribir archivo minificado
        $result = @file_put_contents($output_path, $minified, LOCK_EX);
        
        return $result !== false;
    }
    
    /**
     * Minificar string CSS
     */
    private function minify_css_string($css) {
        // Remover comentarios
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remover espacios en blanco innecesarios
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
        
        // Remover espacios antes de }
        $css = preg_replace('/\s*}\s*/', '}', $css);
        
        // Remover espacios después de {
        $css = preg_replace('/{\s*/', '{', $css);
        
        // Remover último punto y coma antes de }
        $css = preg_replace('/;}/', '}', $css);
        
        // Remover espacios alrededor de operadores
        $css = preg_replace('/\s*([>+~])\s*/', '$1', $css);
        
        return trim($css);
    }
    
    /**
     * Minificar archivo JS
     */
    private function minify_js_file($input_path, $output_path) {
        // Validaciones de seguridad
        if (empty($input_path) || empty($output_path)) {
            return false;
        }
        
        // Verificar que el archivo de entrada exista y sea legible
        if (!file_exists($input_path) || !is_readable($input_path)) {
            return false;
        }
        
        // Verificar que el archivo no sea demasiado grande (límite de 5MB)
        if (filesize($input_path) > 5 * 1024 * 1024) {
            return false;
        }
        
        // Leer contenido
        $js_content = @file_get_contents($input_path);
        if ($js_content === false) {
            return false;
        }
        
        // Minificación básica de JS
        $minified = $this->minify_js_string($js_content);
        
        // Crear directorio si no existe
        $output_dir = dirname($output_path);
        if (!file_exists($output_dir)) {
            if (!wp_mkdir_p($output_dir)) {
                return false;
            }
        }
        
        // Verificar permisos de escritura
        if (!is_writable($output_dir)) {
            return false;
        }
        
        // Escribir archivo minificado
        $result = @file_put_contents($output_path, $minified, LOCK_EX);
        
        return $result !== false;
    }
    
    /**
     * Minificar string JS
     * Enfoque conservador para evitar romper código
     */
    private function minify_js_string($js) {
        // Preservar strings y expresiones regulares antes de procesar
        $preserved = array();
        $index = 0;

        // Preservar template literals (backticks)
        $js = preg_replace_callback('/`(?:[^`\\\\]|\\\\.)*`/s', function($match) use (&$preserved, &$index) {
            $placeholder = "___WPTO_PRESERVED_{$index}___";
            $preserved[$placeholder] = $match[0];
            $index++;
            return $placeholder;
        }, $js);

        // Preservar strings con comillas dobles
        $js = preg_replace_callback('/"(?:[^"\\\\]|\\\\.)*"/s', function($match) use (&$preserved, &$index) {
            $placeholder = "___WPTO_PRESERVED_{$index}___";
            $preserved[$placeholder] = $match[0];
            $index++;
            return $placeholder;
        }, $js);

        // Preservar strings con comillas simples
        $js = preg_replace_callback("/\'(?:[^\'\\\\]|\\\\.)*\'/s", function($match) use (&$preserved, &$index) {
            $placeholder = "___WPTO_PRESERVED_{$index}___";
            $preserved[$placeholder] = $match[0];
            $index++;
            return $placeholder;
        }, $js);

        // Preservar expresiones regulares (simplificado - solo las más comunes)
        $js = preg_replace_callback('/\/(?![*\/])(?:[^\/\\\\\n]|\\\\.)+\/[gimsuy]*/s', function($match) use (&$preserved, &$index) {
            $placeholder = "___WPTO_PRESERVED_{$index}___";
            $preserved[$placeholder] = $match[0];
            $index++;
            return $placeholder;
        }, $js);

        // Remover comentarios de línea (pero no dentro de URLs u otros contextos)
        $js = preg_replace('/(?<!:)\/\/[^\n]*$/m', '', $js);

        // Remover comentarios de bloque
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);

        // Colapsar múltiples espacios/tabs en uno solo
        $js = preg_replace('/[ \t]+/', ' ', $js);

        // Remover espacios al inicio y final de líneas
        $js = preg_replace('/^[ \t]+|[ \t]+$/m', '', $js);

        // Colapsar múltiples líneas vacías en una sola
        $js = preg_replace('/\n{2,}/', "\n", $js);

        // Remover espacios alrededor de ciertos caracteres (conservador)
        // NO remover espacios alrededor de + y - porque pueden ser operadores unarios o parte de palabras
        $js = preg_replace('/\s*([{}();,:])\s*/', '$1', $js);

        // Remover saltos de línea innecesarios (pero mantener los que podrían ser necesarios)
        $js = preg_replace('/\n(?=[{}();,])/', '', $js);
        $js = preg_replace('/([{}();,])\n/', '$1', $js);

        // Restaurar strings y regex preservados
        foreach ($preserved as $placeholder => $original) {
            $js = str_replace($placeholder, $original, $js);
        }

        return trim($js);
    }
    
    /**
     * Convertir URL a ruta de archivo
     */
    private function url_to_path($url) {
        if (empty($url)) {
            return false;
        }
        
        // Limpiar query strings y fragmentos
        $url = strtok($url, '?#');
        
        // Convertir URL absoluta a ruta
        $home_url = home_url();
        if (strpos($url, $home_url) === 0) {
            $path = str_replace($home_url, ABSPATH, $url);
            // Normalizar separadores de ruta
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            return $path;
        }
        
        // Si es URL relativa, construir ruta completa
        if (strpos($url, '/') === 0) {
            $path = ABSPATH . ltrim($url, '/');
            // Normalizar separadores de ruta
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            return $path;
        }
        
        return false;
    }
    
    /**
     * Convertir ruta de archivo a URL
     */
    private function path_to_url($path) {
        $url = str_replace(ABSPATH, home_url('/'), $path);
        return $url;
    }
    
    /**
     * Obtener ruta del archivo minificado
     */
    private function get_minified_path($original_path, $extension) {
        $path_info = pathinfo($original_path);
        $minified_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.min.' . $extension;
        return $minified_path;
    }
    
    /**
     * Añadir lazy loading a imágenes
     */
    public function add_lazy_loading($attr, $attachment, $size) {
        if (!is_admin()) {
            $attr['loading'] = 'lazy';
        }
        return $attr;
    }
    
    /**
     * Añadir lazy loading a contenido
     */
    public function add_lazy_loading_to_content($content) {
        if (is_admin()) {
            return $content;
        }
        
        $content = preg_replace_callback(
            '/<img([^>]+?)>/i',
            function($matches) {
                if (strpos($matches[1], 'loading=') === false) {
                    return '<img' . $matches[1] . ' loading="lazy">';
                }
                return $matches[0];
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * Añadir lazy loading a iframes
     */
    public function add_lazy_loading_to_iframes($content) {
        if (is_admin()) {
            return $content;
        }
        
        $content = preg_replace_callback(
            '/<iframe([^>]+?)>/i',
            function($matches) {
                if (strpos($matches[1], 'loading=') === false) {
                    return '<iframe' . $matches[1] . ' loading="lazy">';
                }
                return $matches[0];
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * Añadir DNS Prefetch
     */
    public function add_dns_prefetch() {
        $domains = !empty($this->options['prefetch_domains']) ? $this->options['prefetch_domains'] : '';
        $domains = explode("\n", $domains);

        foreach ($domains as $domain) {
            $domain = trim($domain);
            if (!empty($domain) && $this->is_valid_domain($domain)) {
                echo '<link rel="dns-prefetch" href="//' . esc_attr($domain) . '">' . "\n";
            }
        }
    }

    /**
     * Validar dominio
     * FILTER_VALIDATE_DOMAIN no existe en PHP, usamos validación manual
     */
    private function is_valid_domain($domain) {
        // Eliminar protocolo si existe
        $domain = preg_replace('#^https?://#', '', $domain);
        // Eliminar trailing slash
        $domain = rtrim($domain, '/');

        // Validar formato de dominio
        // Permite: ejemplo.com, sub.ejemplo.com, ejemplo.co.uk, etc.
        $pattern = '/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/';

        return preg_match($pattern, $domain) === 1;
    }
    
    /**
     * Optimizar Gutenberg
     */
    public function optimize_gutenberg() {
        // Cargar solo bloques usados - implementación básica
        wp_dequeue_style('wp-block-library-theme');
    }
    
    /**
     * Inicializar object cache
     */
    public function init_object_cache() {
        // Detectar Redis o Memcached
        if (class_exists('Redis')) {
            // Redis disponible
            return true;
        } elseif (class_exists('Memcached')) {
            // Memcached disponible
            return true;
        }
        
        // Usar transients como fallback
        return true;
    }
    
    /**
     * Iniciar output buffering para limpieza
     */
    public function start_output_buffering() {
        // No ejecutar en admin, AJAX, o si ya hay output buffering activo
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX) || ob_get_level() > 0) {
            return;
        }
        
        // Verificar que no haya conflictos con otros plugins de optimización
        if (defined('WP_ROCKET_VERSION') || defined('W3TC') || defined('WPFC')) {
            // No iniciar si hay otros plugins de optimización activos que usan OB
            return;
        }
        
        // Iniciar output buffering
        ob_start(array($this, 'clean_output'));
    }
    
    /**
     * Limpiar output HTML
     */
    public function clean_output($buffer) {
        // Validar que el buffer no esté vacío
        if (empty($buffer) || !is_string($buffer)) {
            return $buffer;
        }
        
        // No procesar si parece ser JSON o XML puro
        if (strpos(trim($buffer), '{') === 0 || strpos(trim($buffer), '<?xml') === 0) {
            return $buffer;
        }
        
        try {
            // Remover comentarios HTML (excepto condicionales IE y DOCTYPE)
            $buffer = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $buffer);
            
            // Remover espacios en blanco excesivos, pero preservar espacios en <pre>, <textarea>, <script>
            $preserve_areas = array();
            $preserve_patterns = array(
                '/<pre[^>]*>.*?<\/pre>/is',
                '/<textarea[^>]*>.*?<\/textarea>/is',
                '/<script[^>]*>.*?<\/script>/is',
                '/<style[^>]*>.*?<\/style>/is'
            );
            
            $i = 0;
            foreach ($preserve_patterns as $pattern) {
                $buffer = preg_replace_callback($pattern, function($matches) use (&$preserve_areas, &$i) {
                    $key = '___PRESERVE_' . $i . '___';
                    $preserve_areas[$key] = $matches[0];
                    $i++;
                    return $key;
                }, $buffer);
            }
            
            // Remover espacios en blanco excesivos
            $buffer = preg_replace('/\s+/', ' ', $buffer);
            $buffer = preg_replace('/>\s+</', '><', $buffer);
            
            // Restaurar áreas preservadas
            foreach ($preserve_areas as $key => $content) {
                $buffer = str_replace($key, $content, $buffer);
            }
            
        } catch (Exception $e) {
            // En caso de error, devolver buffer original
            return $buffer;
        }
        
        return $buffer;
    }
    
    /**
     * Desactivar Heartbeat en frontend
     */
    public function disable_heartbeat_frontend() {
        if (!is_admin()) {
            wp_deregister_script('heartbeat');
        }
    }
    
    /**
     * Configurar frecuencia de Heartbeat
     */
    public function configure_heartbeat_frequency($settings) {
        $frequency = !empty($this->options['heartbeat_frequency']) ? intval($this->options['heartbeat_frequency']) : 60;
        $settings['interval'] = $frequency;
        return $settings;
    }
    
    /**
     * Añadir defer/async a scripts
     */
    public function add_defer_async_to_scripts($tag, $handle) {
        // Scripts a excluir (no deben tener defer/async)
        $excluded = array('jquery', 'jquery-core', 'jquery-migrate', 'admin-bar', 'wp-embed');
        
        if (in_array($handle, $excluded)) {
            return $tag;
        }
        
        // Determinar si usar defer o async
        $method = !empty($this->options['script_defer_async_method']) ? $this->options['script_defer_async_method'] : 'defer';
        
        // Si ya tiene defer o async, no añadir
        if (strpos($tag, 'defer') !== false || strpos($tag, 'async') !== false) {
            return $tag;
        }
        
        // Añadir atributo
        if ($method === 'async') {
            return str_replace(' src', ' async src', $tag);
        } else {
            return str_replace(' src', ' defer src', $tag);
        }
    }
    
    /**
     * Añadir preload de recursos críticos
     */
    public function add_resource_preload() {
        // Preload de fuentes (se maneja en font_optimization)
        // Preload de CSS crítico
        if (!empty($this->options['preload_critical_css'])) {
            $critical_css_url = !empty($this->options['critical_css_url']) ? $this->options['critical_css_url'] : '';
            if ($critical_css_url) {
                echo '<link rel="preload" href="' . esc_url($critical_css_url) . '" as="style">' . "\n";
            }
        }
        
        // Preload de imagen hero (primera imagen del post)
        if (!empty($this->options['preload_hero_image']) && is_singular()) {
            global $post;
            $first_image = $this->get_first_image($post->ID);
            if ($first_image) {
                echo '<link rel="preload" href="' . esc_url($first_image) . '" as="image">' . "\n";
            }
        }
    }
    
    /**
     * Obtener primera imagen del post
     */
    private function get_first_image($post_id) {
        // Intentar obtener imagen destacada
        $thumbnail = get_the_post_thumbnail_url($post_id, 'full');
        if ($thumbnail) {
            return $thumbnail;
        }
        
        // Buscar primera imagen en el contenido
        $post = get_post($post_id);
        if ($post) {
            preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches);
            if (!empty($matches[1])) {
                return $matches[1];
            }
        }
        
        return false;
    }
    
    /**
     * Iniciar compresión
     */
    public function start_compression() {
        // No comprimir en admin, AJAX, o si ya hay compresión activa
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            return;
        }
        
        // Verificar si el servidor ya comprime (evitar doble compresión)
        if (ini_get('zlib.output_compression') === '1' || 
            (function_exists('apache_get_modules') && in_array('mod_deflate', apache_get_modules()))) {
            // El servidor ya comprime, no hacer nada
            return;
        }
        
        // Verificar si ya hay output buffering activo
        if (ob_get_level() > 0) {
            return;
        }
        
        // Verificar que zlib esté disponible
        if (!extension_loaded('zlib')) {
            return;
        }
        
        // Verificar que el cliente acepte gzip
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
            return;
        }
        
        // Intentar comprimir con ob_gzhandler
        try {
            ob_start('ob_gzhandler');
        } catch (Exception $e) {
            // Si falla, no hacer nada
            return;
        }
        
        // Intentar añadir reglas a .htaccess si es posible (solo una vez)
        if (!empty($this->options['add_htaccess_rules']) && function_exists('insert_with_markers')) {
            // Verificar que no se haya añadido ya
            static $htaccess_processed = false;
            if (!$htaccess_processed) {
                $this->add_compression_htaccess_rules();
                $htaccess_processed = true;
            }
        }
    }
    
    /**
     * Añadir reglas de compresión a .htaccess
     */
    private function add_compression_htaccess_rules() {
        $htaccess_file = ABSPATH . '.htaccess';
        
        // Verificar que el archivo exista o sea escribible
        if (!file_exists($htaccess_file)) {
            // Intentar crear el archivo si no existe
            if (!is_writable(ABSPATH)) {
                return;
            }
            @touch($htaccess_file);
        }
        
        if (!is_writable($htaccess_file)) {
            return;
        }
        
        // Verificar que insert_with_markers esté disponible
        if (!function_exists('insert_with_markers')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        if (!function_exists('insert_with_markers')) {
            return;
        }
        
        $rules = array(
            '<IfModule mod_deflate.c>',
            '    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json',
            '</IfModule>'
        );
        
        try {
            insert_with_markers($htaccess_file, 'WPTO Compression', $rules);
        } catch (Exception $e) {
            // Error al escribir .htaccess, no hacer nada
            return;
        }
    }
    
    /**
     * Desactivar WP Cron
     */
    public function disable_wp_cron() {
        if (!defined('DISABLE_WP_CRON')) {
            define('DISABLE_WP_CRON', true);
        }
    }
    
    /**
     * Añadir preload de fuentes
     */
    public function add_font_preload() {
        if (empty($this->options['font_preload_urls'])) {
            return;
        }
        
        $font_urls = explode("\n", $this->options['font_preload_urls']);
        foreach ($font_urls as $url) {
            $url = trim($url);
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                echo '<link rel="preload" href="' . esc_url($url) . '" as="font" type="font/woff2" crossorigin>' . "\n";
            }
        }
    }
    
    /**
     * Añadir font-display: swap
     */
    public function add_font_display_swap($tag, $handle) {
        // Buscar @font-face en el tag y añadir font-display: swap
        if (strpos($tag, '@font-face') !== false || strpos($tag, 'font-family') !== false) {
            $tag = str_replace('@font-face {', '@font-face { font-display: swap; ', $tag);
        }
        return $tag;
    }
    
    /**
     * Inyectar CSS crítico
     */
    public function inject_critical_css() {
        if (empty($this->options['critical_css_content'])) {
            return;
        }
        
        $critical_css = $this->options['critical_css_content'];
        echo '<style id="wpto-critical-css">' . "\n";
        echo wp_strip_all_tags($critical_css) . "\n";
        echo '</style>' . "\n";
    }
    
    /**
     * Reemplazar URL con CDN
     */
    public function replace_url_with_cdn($url) {
        if (empty($this->options['cdn_url'])) {
            return $url;
        }
        
        $cdn_url = rtrim($this->options['cdn_url'], '/');
        $site_url = home_url();
        
        // Extensiones a reemplazar
        $extensions = !empty($this->options['cdn_extensions']) ? explode(',', $this->options['cdn_extensions']) : array('css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'woff', 'woff2', 'ttf', 'eot');
        
        // Verificar si la URL tiene una extensión válida
        $url_path = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($url_path, PATHINFO_EXTENSION));
        
        if (in_array($extension, $extensions)) {
            // Reemplazar URL del sitio con CDN
            $url = str_replace($site_url, $cdn_url, $url);
        }
        
        return $url;
    }
}

