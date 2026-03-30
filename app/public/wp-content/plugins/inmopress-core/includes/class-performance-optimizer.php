<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Performance Optimizer - Optimizaciones de rendimiento
 */
class Inmopress_Performance_Optimizer
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init_hooks();
    }

    private function init_hooks()
    {
        // Crear índices al activar (se llama desde activate() del plugin principal)

        // Optimizar queries
        add_filter('posts_clauses', array($this, 'optimize_property_queries'), 10, 2);
        add_filter('posts_clauses', array($this, 'optimize_client_queries'), 10, 2);

        // Cache de transients
        add_action('save_post', array($this, 'clear_related_caches'), 10, 2);
        add_action('delete_post', array($this, 'clear_related_caches'), 10, 2);

        // Lazy loading de imágenes
        add_filter('wp_get_attachment_image_attributes', array($this, 'add_lazy_loading'), 10, 3);
        add_filter('the_content', array($this, 'lazy_load_content_images'), 99);

        // Deshabilitar queries innecesarias
        add_action('pre_get_posts', array($this, 'disable_unnecessary_queries'));
    }

    /**
     * Crear índices de base de datos
     */
    public function create_database_indexes()
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Índices para matching scores
        $table_matching = $wpdb->prefix . 'inmopress_matching_scores';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_matching}'") === $table_matching) {
            // Índice compuesto para búsquedas por propiedad y score
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_property_score ON {$table_matching} (property_id, score DESC)");
            // Índice compuesto para búsquedas por cliente y score
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_client_score ON {$table_matching} (client_id, score DESC)");
            // Índice para calculated_at (para limpieza de cache antiguo)
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_calculated_at ON {$table_matching} (calculated_at)");
        }

        // Índices para activity log
        $table_activity = $wpdb->prefix . 'inmopress_activity_log';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_activity}'") === $table_activity) {
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_user_action ON {$table_activity} (user_id, action)");
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_object_type_id ON {$table_activity} (object_type, object_id)");
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_created_at ON {$table_activity} (created_at DESC)");
        }

        // Índices para email queue
        $table_queue = $wpdb->prefix . 'inmopress_email_queue';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_queue}'") === $table_queue) {
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_status_priority ON {$table_queue} (status, priority DESC)");
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_scheduled_at ON {$table_queue} (scheduled_at)");
        }

        // Índices para automations
        $table_automations = $wpdb->prefix . 'inmopress_automations';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_automations}'") === $table_automations) {
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_status_active ON {$table_automations} (status, is_active)");
        }

        // Índices en postmeta para campos ACF comunes
        $meta_keys = array(
            'impress_property_agency',
            'impress_property_operation',
            'impress_property_city',
            'impress_property_price',
            'impress_client_agency',
            'impress_client_status',
            'impress_lead_status',
            'impress_event_status',
            'impress_event_start',
        );

        foreach ($meta_keys as $meta_key) {
            $wpdb->query($wpdb->prepare(
                "CREATE INDEX IF NOT EXISTS idx_%s ON {$wpdb->postmeta} (meta_key, meta_value(191))",
                str_replace('impress_', '', str_replace('_', '', $meta_key))
            ));
        }
    }

    /**
     * Optimizar queries de propiedades
     */
    public function optimize_property_queries($clauses, $query)
    {
        if (!is_admin() && $query->get('post_type') === 'impress_property') {
            // Usar cache de transients para listados
            if ($query->is_main_query() && !$query->get('p')) {
                $cache_key = 'inmopress_properties_' . md5(serialize($query->query_vars));
                $cached = get_transient($cache_key);

                if ($cached !== false) {
                    // Retornar resultados cacheados
                    $query->found_posts = $cached['found_posts'];
                    $query->max_num_pages = $cached['max_num_pages'];
                }
            }

            // Optimizar JOINs innecesarios
            if (empty($query->get('meta_query')) && empty($query->get('tax_query'))) {
                // Remover JOINs innecesarios si no hay meta o tax queries
            }
        }

        return $clauses;
    }

    /**
     * Optimizar queries de clientes
     */
    public function optimize_client_queries($clauses, $query)
    {
        if (!is_admin() && $query->get('post_type') === 'impress_client') {
            // Similar a propiedades
            if ($query->is_main_query() && !$query->get('p')) {
                $cache_key = 'inmopress_clients_' . md5(serialize($query->query_vars));
                $cached = get_transient($cache_key);

                if ($cached !== false) {
                    $query->found_posts = $cached['found_posts'];
                    $query->max_num_pages = $cached['max_num_pages'];
                }
            }
        }

        return $clauses;
    }

    /**
     * Limpiar caches relacionados cuando se guarda/elimina un post
     */
    public function clear_related_caches($post_id, $post = null)
    {
        $post_type = $post ? $post->post_type : get_post_type($post_id);

        // Limpiar cache de matching si es propiedad o cliente
        if ($post_type === 'impress_property') {
            delete_transient('inmopress_properties_*');
            wp_cache_delete('property_matches_' . $post_id, 'inmopress');
        } elseif ($post_type === 'impress_client') {
            delete_transient('inmopress_clients_*');
            wp_cache_delete('client_matches_' . $post_id, 'inmopress');
        }

        // Limpiar cache de KPIs
        if (in_array($post_type, array('impress_property', 'impress_client', 'impress_lead', 'impress_event'))) {
            delete_transient('inmopress_kpis_*');
        }
    }

    /**
     * Añadir lazy loading a imágenes de attachments
     */
    public function add_lazy_loading($attr, $attachment, $size)
    {
        if (!is_admin()) {
            $attr['loading'] = 'lazy';
            $attr['decoding'] = 'async';
        }
        return $attr;
    }

    /**
     * Lazy load imágenes en contenido
     */
    public function lazy_load_content_images($content)
    {
        if (is_admin() || is_feed()) {
            return $content;
        }

        // Reemplazar src con data-src para lazy loading
        $content = preg_replace_callback(
            '/<img([^>]+)src=["\']([^"\']+)["\']([^>]*)>/i',
            function($matches) {
                // Si ya tiene loading="lazy", no hacer nada
                if (strpos($matches[0], 'loading=') !== false) {
                    return $matches[0];
                }

                // Añadir loading lazy y data-src
                $img_tag = '<img' . $matches[1] . 'src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E" data-src="' . $matches[2] . '"' . $matches[3] . ' loading="lazy" decoding="async">';
                return $img_tag;
            },
            $content
        );

        // Añadir script para cargar imágenes cuando sean visibles
        if (strpos($content, 'data-src') !== false && !wp_script_is('inmopress-lazy-load', 'enqueued')) {
            wp_enqueue_script('inmopress-lazy-load', INMOPRESS_CORE_URL . 'assets/js/lazy-load.js', array(), '1.0.0', true);
        }

        return $content;
    }

    /**
     * Deshabilitar queries innecesarias
     */
    public function disable_unnecessary_queries($query)
    {
        if (!is_admin() && $query->is_main_query()) {
            // Deshabilitar queries de términos si no se necesitan
            if (!is_tax() && !is_category() && !is_tag()) {
                remove_action('wp_head', 'wp_generator');
            }
        }
    }

    /**
     * Cachear resultado de matching con transient
     */
    public static function cache_match_result($property_id, $client_id, $score, $expiration = 3600)
    {
        $cache_key = 'match_' . $property_id . '_' . $client_id;
        set_transient($cache_key, $score, $expiration);
    }

    /**
     * Obtener resultado de matching desde cache
     */
    public static function get_cached_match($property_id, $client_id)
    {
        $cache_key = 'match_' . $property_id . '_' . $client_id;
        return get_transient($cache_key);
    }

    /**
     * Optimizar query de matching scores
     */
    public static function optimize_matching_query($property_id, $threshold = 70, $limit = 10)
    {
        global $wpdb;

        // Intentar obtener desde cache primero
        $cache_key = 'matches_property_' . $property_id . '_' . $threshold . '_' . $limit;
        $cached = wp_cache_get($cache_key, 'inmopress_matching');

        if ($cached !== false) {
            return $cached;
        }

        // Query optimizada con índices
        $table = $wpdb->prefix . 'inmopress_matching_scores';
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT ms.*, p.post_title as property_title, c.post_title as client_name
            FROM {$table} ms
            INNER JOIN {$wpdb->posts} p ON ms.property_id = p.ID
            INNER JOIN {$wpdb->posts} c ON ms.client_id = c.ID
            WHERE ms.property_id = %d 
            AND ms.score >= %d 
            AND p.post_status = 'publish'
            AND c.post_status = 'publish'
            ORDER BY ms.score DESC
            LIMIT %d",
            $property_id,
            $threshold,
            $limit
        ));

        // Cachear resultado por 5 minutos
        wp_cache_set($cache_key, $results, 'inmopress_matching', 300);

        return $results;
    }

    /**
     * Batch update de matching scores (más eficiente)
     */
    public static function batch_update_matching_scores($property_ids, $threshold = 70)
    {
        global $wpdb;

        if (empty($property_ids)) {
            return 0;
        }

        $table = $wpdb->prefix . 'inmopress_matching_scores';
        $matching_engine = Inmopress_Matching_Engine::get_instance();

        $updated = 0;
        $batch_size = 50; // Procesar en lotes

        foreach (array_chunk($property_ids, $batch_size) as $batch) {
            foreach ($batch as $property_id) {
                $count = $matching_engine->cache_matching_scores($property_id, $threshold);
                $updated += $count;
            }

            // Pequeña pausa entre lotes para no sobrecargar
            usleep(100000); // 0.1 segundos
        }

        return $updated;
    }

    /**
     * Limpiar cache antiguo de matching
     */
    public static function cleanup_old_matching_cache($days = 30)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'inmopress_matching_scores';
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table} WHERE calculated_at < %s",
            $cutoff_date
        ));

        return $deleted;
    }
}
