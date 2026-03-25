<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_Price_Alert_Matcher
{
    public static function init()
    {
        // No hooks yet. Reserved for future.
    }

    public static function get_interested_clients($property_id)
    {
        $property_id = absint($property_id);
        if (!$property_id) {
            return array();
        }

        $candidates = array();

        $favorite_clients = self::get_favorite_clients($property_id);
        foreach ($favorite_clients as $client_id) {
            self::add_candidate($candidates, $client_id, 100, 'favorite');
        }

        $visit_clients = self::get_visit_clients($property_id);
        foreach ($visit_clients as $visit) {
            self::add_candidate($candidates, $visit['client_id'], $visit['score'], 'visit');
        }

        $match_clients = self::get_match_clients($property_id);
        foreach ($match_clients as $match) {
            self::add_candidate($candidates, $match['client_id'], $match['score'], 'matching');
        }

        foreach ($candidates as $client_id => $data) {
            $temp_score = self::score_client_temperature($client_id);
            if ($temp_score > 0) {
                $candidates[$client_id]['score'] += $temp_score;
                $candidates[$client_id]['reasons'][] = 'temperature';
            }
        }

        $results = array();
        foreach ($candidates as $client_id => $data) {
            $score = (int) min(200, $data['score']);
            $results[] = array(
                'client_id' => $client_id,
                'score' => $score,
                'reasons' => array_values(array_unique($data['reasons'])),
            );
        }

        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $results;
    }

    public static function client_allows_alerts($client_id)
    {
        $client_id = absint($client_id);
        if (!$client_id) {
            return false;
        }

        $enabled = get_field('alertas_bajada_precio', $client_id);
        if ($enabled === null || $enabled === '') {
            return true;
        }

        return (bool) $enabled;
    }

    public static function get_client_frequency($client_id)
    {
        $freq = get_field('alertas_frecuencia', $client_id);
        if (empty($freq)) {
            return 'inmediata';
        }

        return $freq;
    }

    private static function add_candidate(&$candidates, $client_id, $score, $reason)
    {
        $client_id = absint($client_id);
        if (!$client_id || $score <= 0) {
            return;
        }

        if (!isset($candidates[$client_id])) {
            $candidates[$client_id] = array(
                'score' => 0,
                'reasons' => array(),
            );
        }

        $candidates[$client_id]['score'] += (int) $score;
        $candidates[$client_id]['reasons'][] = $reason;
    }

    private static function get_favorite_clients($property_id)
    {
        $results = get_posts(array(
            'post_type' => 'impress_client',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'publish',
            'no_found_rows' => true,
            'meta_query' => array(
                array(
                    'key' => 'favoritos',
                    'value' => '"' . $property_id . '"',
                    'compare' => 'LIKE',
                ),
            ),
        ));

        return $results ? $results : array();
    }

    private static function get_visit_clients($property_id)
    {
        $events = get_posts(array(
            'post_type' => 'impress_event',
            'posts_per_page' => 200,
            'fields' => 'ids',
            'post_status' => 'publish',
            'no_found_rows' => true,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'impress_event_type',
                    'value' => 'visita',
                    'compare' => '=',
                ),
                array(
                    'key' => 'impress_event_status',
                    'value' => 'completada',
                    'compare' => '=',
                ),
                array(
                    'key' => 'impress_event_property_rel',
                    'value' => $property_id,
                    'compare' => '=',
                ),
            ),
        ));

        $results = array();
        foreach ($events as $event_id) {
            $client_id = (int) get_field('impress_event_client_rel', $event_id);
            if (!$client_id) {
                continue;
            }

            $score = 50;
            $start = get_field('impress_event_start', $event_id);
            if (!empty($start)) {
                $ts = strtotime($start);
                if ($ts) {
                    $days = (current_time('timestamp') - $ts) / DAY_IN_SECONDS;
                    if ($days <= 30) {
                        $score = 80;
                    } elseif ($days <= 90) {
                        $score = 50;
                    } else {
                        $score = 30;
                    }
                }
            }

            $results[] = array(
                'client_id' => $client_id,
                'score' => $score,
            );
        }

        return $results;
    }

    private static function get_match_clients($property_id)
    {
        $limit = (int) apply_filters('inmopress_price_alerts_matching_limit', 500, $property_id);
        $limit = max(50, $limit);

        $clients = get_posts(array(
            'post_type' => 'impress_client',
            'posts_per_page' => $limit,
            'fields' => 'ids',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ));

        $results = array();
        foreach ($clients as $client_id) {
            $score = self::score_property_client_match($property_id, $client_id);
            if ($score > 0) {
                $results[] = array(
                    'client_id' => $client_id,
                    'score' => $score,
                );
            }
        }

        return $results;
    }

    private static function score_property_client_match($property_id, $client_id)
    {
        $property_id = absint($property_id);
        $client_id = absint($client_id);

        if (!$property_id || !$client_id) {
            return 0;
        }

        $score = 0;

        $purpose = get_field('proposito', $property_id);
        $interest = get_field('interes', $client_id);

        if (!empty($interest) && !empty($purpose)) {
            $valid_interest = ($purpose === 'venta' && in_array($interest, array('compra', 'inversion'), true))
                || ($purpose === 'alquiler' && $interest === 'alquiler');

            if (!$valid_interest) {
                return 0;
            }
        }

        $city_terms = get_the_terms($property_id, 'impress_city');
        $property_city_ids = array();
        if ($city_terms && !is_wp_error($city_terms)) {
            foreach ($city_terms as $term) {
                $property_city_ids[] = (int) $term->term_id;
            }
        }

        $client_zones = get_field('zona_interes', $client_id);
        if (!empty($client_zones)) {
            $client_zone_ids = is_array($client_zones) ? array_map('absint', $client_zones) : array(absint($client_zones));
            $zone_match = array_intersect($client_zone_ids, $property_city_ids);
            if (empty($zone_match)) {
                return 0;
            }
            $score += 30;
        }

        $price = get_field('precio_venta', $property_id);
        if (!$price) {
            $price = get_field('precio_alquiler', $property_id);
        }
        $price = $price ? (float) $price : 0;

        $budget_min = (float) get_field('presupuesto_min', $client_id);
        $budget_max = (float) get_field('presupuesto_max', $client_id);

        if ($price > 0 && $budget_max > 0) {
            if ($price <= $budget_max) {
                $score += ($price <= ($budget_max * 0.9)) ? 25 : 20;
            } elseif ($price <= ($budget_max * 1.1)) {
                $score += 10;
            } else {
                return 0;
            }
        }

        if ($budget_min > 0 && $price > 0 && $price < $budget_min) {
            $score += 5;
        }

        $bedrooms_min = (int) get_field('dormitorios_min', $client_id);
        $bedrooms = (int) get_field('dormitorios', $property_id);
        if ($bedrooms_min > 0 && $bedrooms > 0) {
            if ($bedrooms >= $bedrooms_min) {
                $score += 10;
            } else {
                return 0;
            }
        }

        $bath_min = (int) get_field('banos_min', $client_id);
        $bathrooms = (int) get_field('banos', $property_id);
        if ($bath_min > 0 && $bathrooms > 0) {
            if ($bathrooms >= $bath_min) {
                $score += 5;
            } else {
                return 0;
            }
        }

        $surface_min = (int) get_field('superficie_min', $client_id);
        $surface = (int) get_field('superficie_construida', $property_id);
        if ($surface_min > 0 && $surface > 0) {
            if ($surface >= $surface_min) {
                $score += 5;
            } else {
                return 0;
            }
        }

        return (int) min(100, $score);
    }

    private static function score_client_temperature($client_id)
    {
        $status = get_field('semaforo_estado', $client_id);
        if ($status === 'hot') {
            return 20;
        }
        if ($status === 'warm') {
            return 10;
        }
        return 0;
    }
}
