<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Matching Engine - Calcula scores de compatibilidad entre propiedades y clientes
 */
class Inmopress_Matching_Engine
{
    private static $instance = null;

    // Criterios y ponderación
    private $criteria_weights = array(
        'operation' => 20,        // Venta/Alquiler debe coincidir
        'city' => 15,             // Ciudad/zona
        'property_type' => 15,    // Tipo de vivienda
        'price_range' => 20,      // Dentro del presupuesto
        'bedrooms' => 10,         // Habitaciones mínimas
        'bathrooms' => 5,         // Baños
        'features' => 15,         // Características imprescindibles
    );

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
        // Recalcular matching cuando se crea/actualiza una propiedad
        add_action('acf/save_post', array($this, 'handle_property_save'), 25);
        add_action('wp_insert_post', array($this, 'handle_property_insert'), 10, 3);

        // Hook para cálculo manual
        add_action('inmopress_calculate_matching', array($this, 'calculate_matching_for_property'), 10, 2);
    }

    /**
     * Calcular score de matching entre propiedad y cliente
     */
    public function calculate_match_score($property_id, $client_id)
    {
        $score = 0;
        $breakdown = array();

        // 1. Operación (20 puntos - crítico)
        $property_operation_terms = get_the_terms($property_id, 'impress_operation');
        $property_operation = $property_operation_terms && !is_wp_error($property_operation_terms) && !empty($property_operation_terms)
            ? $property_operation_terms[0]->name
            : '';

        $client_operation_interest = get_field('operacion_interes', $client_id) ?: get_field('client_operation_interest', $client_id);

        if (empty($property_operation) || empty($client_operation_interest)) {
            // Si no hay datos, no podemos hacer matching
            return array('score' => 0, 'breakdown' => array('error' => 'Datos incompletos'));
        }

        if ($property_operation === $client_operation_interest) {
            $score += $this->criteria_weights['operation'];
            $breakdown['operation'] = $this->criteria_weights['operation'];
        } else {
            // Descarte inmediato si la operación no coincide
            return array('score' => 0, 'breakdown' => array('operation' => 0, 'reason' => 'Operación no coincide'));
        }

        // 2. Ciudad (15 puntos)
        $property_city_terms = get_the_terms($property_id, 'impress_city');
        $property_city_ids = $property_city_terms && !is_wp_error($property_city_terms)
            ? array_map(function($term) { return $term->term_id; }, $property_city_terms)
            : array();

        $client_city_interest = get_field('ciudades_interes', $client_id) ?: get_field('client_city_interest', $client_id);
        $client_city_ids = array();

        if (is_array($client_city_interest)) {
            foreach ($client_city_interest as $item) {
                if (is_object($item) && isset($item->term_id)) {
                    $client_city_ids[] = $item->term_id;
                } elseif (is_numeric($item)) {
                    $client_city_ids[] = intval($item);
                }
            }
        }

        if (!empty($property_city_ids) && !empty($client_city_ids) && array_intersect($property_city_ids, $client_city_ids)) {
            $score += $this->criteria_weights['city'];
            $breakdown['city'] = $this->criteria_weights['city'];
        }

        // 3. Tipo de propiedad (15 puntos)
        $property_type_terms = get_the_terms($property_id, 'impress_property_type');
        $property_type_ids = $property_type_terms && !is_wp_error($property_type_terms)
            ? array_map(function($term) { return $term->term_id; }, $property_type_terms)
            : array();

        $client_type_interest = get_field('tipos_interes', $client_id) ?: get_field('client_property_type_interest', $client_id);
        $client_type_ids = array();

        if (is_array($client_type_interest)) {
            foreach ($client_type_interest as $item) {
                if (is_object($item) && isset($item->term_id)) {
                    $client_type_ids[] = $item->term_id;
                } elseif (is_numeric($item)) {
                    $client_type_ids[] = intval($item);
                }
            }
        }

        if (!empty($property_type_ids) && !empty($client_type_ids) && array_intersect($property_type_ids, $client_type_ids)) {
            $score += $this->criteria_weights['property_type'];
            $breakdown['property_type'] = $this->criteria_weights['property_type'];
        }

        // 4. Precio (20 puntos)
        $property_price = $this->get_property_price($property_id, $property_operation);
        $client_budget_min = floatval(get_field('presupuesto_minimo', $client_id) ?: get_field('client_budget_min', $client_id) ?: 0);
        $client_budget_max = floatval(get_field('presupuesto_maximo', $client_id) ?: get_field('client_budget_max', $client_id) ?: 999999999);

        if ($property_price > 0 && $client_budget_max > 0) {
            if ($property_price >= $client_budget_min && $property_price <= $client_budget_max) {
                // Puntuación según proximidad al rango ideal (30% por encima del mínimo)
                $range = $client_budget_max - $client_budget_min;
                if ($range > 0) {
                    $ideal = $client_budget_min + ($range * 0.3);
                    $diff = abs($property_price - $ideal);
                    $price_score = max(0, $this->criteria_weights['price_range'] - (($diff / $range) * $this->criteria_weights['price_range']));
                    $score += round($price_score);
                    $breakdown['price_range'] = round($price_score);
                } else {
                    // Rango muy pequeño, puntuación completa si está dentro
                    $score += $this->criteria_weights['price_range'];
                    $breakdown['price_range'] = $this->criteria_weights['price_range'];
                }
            }
        }

        // 5. Dormitorios (10 puntos)
        $property_bedrooms = intval(get_field('dormitorios', $property_id) ?: 0);
        $client_min_bedrooms = intval(get_field('dormitorios_minimos', $client_id) ?: get_field('client_min_bedrooms', $client_id) ?: 0);

        if ($property_bedrooms > 0 && $client_min_bedrooms > 0) {
            if ($property_bedrooms >= $client_min_bedrooms) {
                // Puntuación proporcional: más habitaciones = más puntos (hasta el máximo)
                $bedroom_score = min($this->criteria_weights['bedrooms'], ($property_bedrooms / max($client_min_bedrooms, 1)) * ($this->criteria_weights['bedrooms'] * 0.8));
                $score += round($bedroom_score);
                $breakdown['bedrooms'] = round($bedroom_score);
            }
        }

        // 6. Baños (5 puntos)
        $property_bathrooms = intval(get_field('banos', $property_id) ?: 0);
        $client_min_bathrooms = intval(get_field('banos_minimos', $client_id) ?: get_field('client_min_bathrooms', $client_id) ?: 0);

        if ($property_bathrooms > 0 && $client_min_bathrooms > 0) {
            if ($property_bathrooms >= $client_min_bathrooms) {
                $bathroom_score = min($this->criteria_weights['bathrooms'], ($property_bathrooms / max($client_min_bathrooms, 1)) * ($this->criteria_weights['bathrooms'] * 0.8));
                $score += round($bathroom_score);
                $breakdown['bathrooms'] = round($bathroom_score);
            }
        }

        // 7. Características/Features (15 puntos)
        $property_features = $this->get_property_features($property_id);
        $client_required_features = get_field('caracteristicas_requeridas', $client_id) ?: get_field('client_required_features', $client_id);
        $client_required_features = is_array($client_required_features) ? $client_required_features : array();

        if (!empty($client_required_features)) {
            $matched_features = 0;
            foreach ($client_required_features as $required_feature) {
                $feature_key = is_object($required_feature) ? $required_feature->name : $required_feature;
                if (in_array($feature_key, $property_features)) {
                    $matched_features++;
                }
            }
            if ($matched_features > 0) {
                $feature_score = ($matched_features / count($client_required_features)) * $this->criteria_weights['features'];
                $score += round($feature_score);
                $breakdown['features'] = round($feature_score);
            }
        }

        return array(
            'score' => min(100, round($score)), // Máximo 100
            'breakdown' => $breakdown,
        );
    }

    /**
     * Obtener precio de la propiedad según operación
     */
    private function get_property_price($property_id, $operation)
    {
        if ($operation === 'Alquiler') {
            return floatval(get_field('precio_alquiler', $property_id) ?: 0);
        } else {
            return floatval(get_field('precio_venta', $property_id) ?: 0);
        }
    }

    /**
     * Obtener características de la propiedad
     */
    private function get_property_features($property_id)
    {
        $features = array();
        $feature_keys = array('piscina', 'jardin', 'terraza', 'garaje', 'ascensor', 'aire_acondicionado', 'calefaccion', 'vistas_mar', 'balcon');

        foreach ($feature_keys as $key) {
            if (get_field($key, $property_id)) {
                $features[] = $key;
            }
        }

        return $features;
    }

    /**
     * Cachear scores de matching para una propiedad
     */
    public function cache_matching_scores($property_id, $threshold = null)
    {
        global $wpdb;

        if ($threshold === null) {
            $threshold = apply_filters('inmopress_matching_threshold', 70);
        }

        // Obtener clientes activos
        $clients = get_posts(array(
            'post_type' => 'impress_client',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'estado_cliente',
                    'value' => array('activo', 'caliente', 'templado'),
                    'compare' => 'IN',
                ),
            ),
        ));

        if (empty($clients)) {
            return 0;
        }

        $table = $wpdb->prefix . 'inmopress_matching_scores';
        $cached_count = 0;

        foreach ($clients as $client_id) {
            $result = $this->calculate_match_score($property_id, $client_id);

            if ($result['score'] >= $threshold) {
                $wpdb->replace(
                    $table,
                    array(
                        'property_id' => $property_id,
                        'client_id' => $client_id,
                        'score' => $result['score'],
                        'score_breakdown' => json_encode($result['breakdown']),
                        'calculated_at' => current_time('mysql'),
                    ),
                    array('%d', '%d', '%d', '%s', '%s')
                );
                $cached_count++;

                // Notificar si es necesario (solo una vez)
                $this->maybe_notify_match($property_id, $client_id, $result['score']);
            } else {
                // Eliminar de cache si el score bajó
                $wpdb->delete(
                    $table,
                    array(
                        'property_id' => $property_id,
                        'client_id' => $client_id,
                    ),
                    array('%d', '%d')
                );
            }
        }

        return $cached_count;
    }

    /**
     * Notificar match si es necesario
     */
    private function maybe_notify_match($property_id, $client_id, $score)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'inmopress_matching_scores';
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT notified FROM {$table} WHERE property_id = %d AND client_id = %d",
            $property_id,
            $client_id
        ));

        // Solo notificar si no se ha notificado antes y el score es alto
        if (!$existing || !$existing->notified) {
            if ($score >= 70) {
                // Disparar acción para notificación
                do_action('inmopress_match_found', array(
                    'property_id' => $property_id,
                    'client_id' => $client_id,
                    'score' => $score,
                ));

                // Marcar como notificado
                $wpdb->update(
                    $table,
                    array(
                        'notified' => 1,
                        'notified_at' => current_time('mysql'),
                    ),
                    array(
                        'property_id' => $property_id,
                        'client_id' => $client_id,
                    )
                );
            }
        }
    }

    /**
     * Obtener matches para una propiedad
     */
    public function get_property_matches($property_id, $args = array())
    {
        global $wpdb;

        $defaults = array(
            'min_score' => 70,
            'limit' => 10,
            'orderby' => 'score',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);

        $table = $wpdb->prefix . 'inmopress_matching_scores';
        $query = $wpdb->prepare(
            "SELECT * FROM {$table} 
            WHERE property_id = %d AND score >= %d 
            ORDER BY {$args['orderby']} {$args['order']} 
            LIMIT %d",
            $property_id,
            $args['min_score'],
            $args['limit']
        );

        return $wpdb->get_results($query);
    }

    /**
     * Obtener matches para un cliente (optimizado con cache)
     */
    public function get_client_matches($client_id, $threshold = 70, $limit = 10, $args = array())
    {
        // Cache key
        $cache_key = 'matches_client_' . $client_id . '_' . $threshold . '_' . $limit;
        $cached = wp_cache_get($cache_key, 'inmopress_matching');

        if ($cached !== false) {
            return $cached;
        }

        global $wpdb;

        $defaults = array(
            'min_score' => $threshold,
            'limit' => $limit,
            'orderby' => 'score',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);

        $table = $wpdb->prefix . 'inmopress_matching_scores';
        $query = $wpdb->prepare(
            "SELECT ms.*, p.post_title as property_title, c.post_title as client_name
            FROM {$table} ms
            INNER JOIN {$wpdb->posts} p ON ms.property_id = p.ID
            INNER JOIN {$wpdb->posts} c ON ms.client_id = c.ID
            WHERE ms.client_id = %d 
            AND ms.score >= %d 
            AND p.post_status = 'publish'
            AND c.post_status = 'publish'
            ORDER BY ms.{$args['orderby']} {$args['order']} 
            LIMIT %d",
            $client_id,
            $args['min_score'],
            $args['limit']
        );

        $results = $wpdb->get_results($query);

        // Cachear por 5 minutos
        wp_cache_set($cache_key, $results, 'inmopress_matching', 300);

        return $results;
    }

    /**
     * Handler: Property save
     */
    public function handle_property_save($post_id)
    {
        if (get_post_type($post_id) !== 'impress_property') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Recalcular matching en background
        wp_schedule_single_event(time() + 5, 'inmopress_recalculate_matching', array($post_id));
    }

    /**
     * Handler: Property insert
     */
    public function handle_property_insert($post_id, $post, $update)
    {
        if ($update || $post->post_type !== 'impress_property') {
            return;
        }

        // Recalcular matching después de crear
        wp_schedule_single_event(time() + 10, 'inmopress_recalculate_matching', array($post_id));
    }

    /**
     * Calcular matching para una propiedad (hook)
     */
    public function calculate_matching_for_property($property_id, $config = array())
    {
        $threshold = isset($config['threshold']) ? intval($config['threshold']) : 70;
        return $this->cache_matching_scores($property_id, $threshold);
    }

    /**
     * Recalcular todos los matches (tarea pesada)
     */
    public function recalculate_all_matches()
    {
        $properties = get_posts(array(
            'post_type' => 'impress_property',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'publish',
        ));

        $count = 0;
        foreach ($properties as $property_id) {
            $this->cache_matching_scores($property_id);
            $count++;
        }

        return $count;
    }
}

// Hook para recalcular matching programado
add_action('inmopress_recalculate_matching', function($property_id) {
    $engine = Inmopress_Matching_Engine::get_instance();
    $engine->cache_matching_scores($property_id);
});
