<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Query Optimizer - Optimiza queries específicas de Inmopress
 */
class Inmopress_Query_Optimizer
{
    /**
     * Optimizar query de propiedades con cache
     */
    public static function get_properties($args = array())
    {
        $defaults = array(
            'post_type' => 'impress_property',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'paged' => 1,
        );

        $args = wp_parse_args($args, $defaults);
        $cache_key = 'properties_' . md5(serialize($args));
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $query = new WP_Query($args);
        $results = array(
            'posts' => $query->posts,
            'found_posts' => $query->found_posts,
            'max_num_pages' => $query->max_num_pages,
        );

        // Cachear por 5 minutos
        set_transient($cache_key, $results, 300);

        return $results;
    }

    /**
     * Optimizar query de clientes con cache
     */
    public static function get_clients($args = array())
    {
        $defaults = array(
            'post_type' => 'impress_client',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'paged' => 1,
        );

        $args = wp_parse_args($args, $defaults);
        $cache_key = 'clients_' . md5(serialize($args));
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $query = new WP_Query($args);
        $results = array(
            'posts' => $query->posts,
            'found_posts' => $query->found_posts,
            'max_num_pages' => $query->max_num_pages,
        );

        // Cachear por 5 minutos
        set_transient($cache_key, $results, 300);

        return $results;
    }

    /**
     * Optimizar query de propiedades con meta_query
     */
    public static function get_properties_by_meta($meta_key, $meta_value, $compare = '=', $limit = 20)
    {
        $cache_key = 'properties_meta_' . $meta_key . '_' . md5(serialize($meta_value)) . '_' . $limit;
        $cached = wp_cache_get($cache_key, 'inmopress');

        if ($cached !== false) {
            return $cached;
        }

        $args = array(
            'post_type' => 'impress_property',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => $meta_key,
                    'value' => $meta_value,
                    'compare' => $compare,
                ),
            ),
            'fields' => 'ids', // Solo IDs para mejor performance
        );

        $posts = get_posts($args);

        // Cachear por 10 minutos
        wp_cache_set($cache_key, $posts, 'inmopress', 600);

        return $posts;
    }

    /**
     * Batch get de propiedades (más eficiente que múltiples queries)
     */
    public static function batch_get_properties($property_ids)
    {
        if (empty($property_ids)) {
            return array();
        }

        $cache_key = 'batch_properties_' . md5(implode(',', $property_ids));
        $cached = wp_cache_get($cache_key, 'inmopress');

        if ($cached !== false) {
            return $cached;
        }

        $args = array(
            'post_type' => 'impress_property',
            'post__in' => $property_ids,
            'posts_per_page' => -1,
            'orderby' => 'post__in', // Mantener orden original
        );

        $query = new WP_Query($args);
        $results = $query->posts;

        // Cachear por 5 minutos
        wp_cache_set($cache_key, $results, 'inmopress', 300);

        return $results;
    }
}
