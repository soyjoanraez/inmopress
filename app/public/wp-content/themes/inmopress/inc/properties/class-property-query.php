<?php
/**
 * Property Query Builder
 *
 * Construye queries WP_Query para propiedades con filtros
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Property Query class
 */
class Property_Query
{

    /**
     * Build WP_Query args from filters
     *
     * @param array $filters Filter parameters
     * @return array WP_Query arguments
     */
    public static function build_query_args($filters = array())
    {
        $args = array(
            'post_type' => 'impress_property',
            'post_status' => 'publish',
            'posts_per_page' => isset($filters['posts_per_page']) ? absint($filters['posts_per_page']) : 9,
            'paged' => isset($filters['paged']) ? absint($filters['paged']) : 1,
        );

        // Meta query for ACF fields
        $meta_query = array('relation' => 'AND');

        // Filter by publicada
        $meta_query[] = array(
            'key' => 'publicada',
            'value' => '1',
            'compare' => '='
        );

        // Determine precio key based on proposito
        $precio_key = 'precio_alquiler'; // Default
        if (!empty($filters['proposito'])) {
            if ($filters['proposito'] === 'venta') {
                $precio_key = 'precio_venta';
            }
            $meta_query[] = array(
                'key' => 'proposito',
                'value' => sanitize_text_field($filters['proposito']),
                'compare' => '='
            );
        }

        if (!empty($filters['precio_min'])) {
            $meta_query[] = array(
                'key' => $precio_key,
                'value' => absint($filters['precio_min']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }

        if (!empty($filters['precio_max'])) {
            $meta_query[] = array(
                'key' => $precio_key,
                'value' => absint($filters['precio_max']),
                'compare' => '<=',
                'type' => 'NUMERIC'
            );
        }

        // Filter by dormitorios
        if (!empty($filters['dormitorios_min'])) {
            $meta_query[] = array(
                'key' => 'dormitorios',
                'value' => absint($filters['dormitorios_min']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }

        // Filter by banos
        if (!empty($filters['banos_min'])) {
            $meta_query[] = array(
                'key' => 'banos',
                'value' => absint($filters['banos_min']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }

        // Filter by superficie
        if (!empty($filters['superficie_min'])) {
            $meta_query[] = array(
                'key' => 'superficie_util',
                'value' => absint($filters['superficie_min']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }

        // Filter by estado
        if (!empty($filters['estado'])) {
            $meta_query[] = array(
                'key' => 'estado',
                'value' => sanitize_text_field($filters['estado']),
                'compare' => '='
            );
        }

        // Filter by agrupacion
        if (!empty($filters['agrupacion'])) {
            $meta_query[] = array(
                'key' => 'agrupacion',
                'value' => sanitize_text_field($filters['agrupacion']),
                'compare' => '='
            );
        }

        // Filter by características (all true_false fields from características especiales)
        $characteristics = array(
            'aire_acondicionado', 'barbacoa', 'lavabajillas', 'ascensor', 'gimnasio',
            'encimera_granito', 'lavanderia', 'solar', 'spa', 'minusvalidos',
            'luminoso', 'horno', 'puerta_blindada', 'patio', 'conserje',
            'buhardilla', 'chimenea', 'agua_potable', 'alarma', 'armarios_empotrados',
            'porche', 'despensa', 'portero_automatico', 'jacuzzi', 'sotano',
            'vistas_mar', 'vistas_montana', 'suelo_radiante', 'aislamiento_termico',
            'sistema_riego_automatico', 'internet', 'sat', 'vitroceramica',
            'frigorifico', 'microondas', 'zona_infantil', 'tenis', 'padel', 'muebles_jardin'
        );
        foreach ($characteristics as $char) {
            if (isset($filters[$char]) && $filters[$char] !== '' && $filters[$char] !== '0') {
                $meta_query[] = array(
                    'key' => $char,
                    'value' => '1',
                    'compare' => '='
                );
            }
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        // Tax query for taxonomies
        $tax_query = array('relation' => 'AND');

        // Filter by provincia
        if (!empty($filters['provincia'])) {
            $tax_query[] = array(
                'taxonomy' => 'impress_province',
                'field' => is_numeric($filters['provincia']) ? 'term_id' : 'slug',
                'terms' => $filters['provincia']
            );
        }

        // Filter by poblacion
        if (!empty($filters['poblacion'])) {
            $tax_query[] = array(
                'taxonomy' => 'impress_municipality',
                'field' => is_numeric($filters['poblacion']) ? 'term_id' : 'slug',
                'terms' => $filters['poblacion']
            );
        }

        // Filter by tipo_vivienda (can be array)
        if (!empty($filters['tipo_vivienda'])) {
            $tipo_vivienda = is_array($filters['tipo_vivienda']) ? $filters['tipo_vivienda'] : array($filters['tipo_vivienda']);
            $tax_query[] = array(
                'taxonomy' => 'impress_property_type',
                'field' => is_numeric($tipo_vivienda[0]) ? 'term_id' : 'slug',
                'terms' => $tipo_vivienda,
                'operator' => 'IN'
            );
        }

        // Filter by operacion
        if (!empty($filters['operacion'])) {
            $tax_query[] = array(
                'taxonomy' => 'impress_operation',
                'field' => is_numeric($filters['operacion']) ? 'term_id' : 'slug',
                'terms' => $filters['operacion']
            );
        }

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        // Order by
        if (!empty($filters['orderby'])) {
            switch ($filters['orderby']) {
                case 'precio':
                    // For precio, we need to handle both venta and alquiler
                    // Use a more complex meta_query or default to one
                    if (!empty($filters['proposito'])) {
                        $args['meta_key'] = $precio_key;
                        $args['orderby'] = 'meta_value_num';
                    } else {
                        // If no proposito, order by precio_alquiler as default
                        $args['meta_key'] = 'precio_alquiler';
                        $args['orderby'] = 'meta_value_num';
                    }
                    break;
                case 'superficie':
                    $args['meta_key'] = 'superficie_util';
                    $args['orderby'] = 'meta_value_num';
                    break;
                case 'fecha':
                    $args['orderby'] = 'date';
                    break;
                case 'titulo':
                    $args['orderby'] = 'title';
                    break;
                case 'referencia':
                    $args['meta_key'] = 'referencia';
                    $args['orderby'] = 'meta_value';
                    break;
                default:
                    $args['meta_key'] = $precio_key;
                    $args['orderby'] = 'meta_value_num';
            }
        } else {
            // Default: order by precio
            $args['meta_key'] = $precio_key;
            $args['orderby'] = 'meta_value_num';
        }

        // Order direction
        $args['order'] = isset($filters['order']) && strtoupper($filters['order']) === 'DESC' ? 'DESC' : 'ASC';

        return $args;
    }
}

