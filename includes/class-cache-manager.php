<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cache Manager - Gestiona cache de transients y object cache
 */
class Inmopress_Cache_Manager
{
    /**
     * Obtener o generar valor con cache
     */
    public static function get_or_set($key, $callback, $expiration = 3600, $group = 'inmopress')
    {
        $cached = wp_cache_get($key, $group);

        if ($cached !== false) {
            return $cached;
        }

        $value = call_user_func($callback);

        if ($value !== null && $value !== false) {
            wp_cache_set($key, $value, $group, $expiration);
        }

        return $value;
    }

    /**
     * Invalidar cache por patrón
     */
    public static function invalidate_pattern($pattern, $group = 'inmopress')
    {
        // WordPress object cache no soporta patrones directamente
        // Usar transients con prefijo para mejor control
        global $wpdb;

        $prefix = '_transient_' . $group . '_';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $prefix . $pattern . '%'
        ));
    }

    /**
     * Limpiar todo el cache de Inmopress
     */
    public static function flush_all()
    {
        // Limpiar object cache
        wp_cache_flush_group('inmopress');
        wp_cache_flush_group('inmopress_matching');

        // Limpiar transients relacionados
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_inmopress_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_inmopress_%'");
    }

    /**
     * Cachear resultado de query
     */
    public static function cache_query($key, $query_args, $expiration = 300)
    {
        $cache_key = 'query_' . md5(serialize($query_args));
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $query = new WP_Query($query_args);
        $result = array(
            'posts' => $query->posts,
            'found_posts' => $query->found_posts,
            'max_num_pages' => $query->max_num_pages,
        );

        set_transient($cache_key, $result, $expiration);

        return $result;
    }
}
