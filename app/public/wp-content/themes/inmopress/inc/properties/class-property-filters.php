<?php
/**
 * Property Filters
 *
 * Sistema de filtros para propiedades
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Property Filters class
 */
class Property_Filters
{

    /**
     * Instance of this class
     *
     * @var Property_Filters
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Property_Filters
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
    public function __construct()
    {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets()
    {
        wp_enqueue_script(
            'inmopress-property-filters',
            get_template_directory_uri() . '/assets/js/property-filters.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('inmopress-property-filters', 'inmopressFilters', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('inmopress_filters_nonce'),
        ));

        wp_enqueue_style(
            'inmopress-property-cards',
            get_template_directory_uri() . '/assets/css/property-cards.css',
            array(),
            '1.0.0'
        );
    }

    /**
     * Get filter values from request
     *
     * @return array
     */
    public static function get_filter_values()
    {
        $filters = array();

        // Taxonomies
        if (!empty($_GET['provincia'])) {
            $filters['provincia'] = sanitize_text_field($_GET['provincia']);
        }
        if (!empty($_GET['poblacion'])) {
            $filters['poblacion'] = sanitize_text_field($_GET['poblacion']);
        }
        if (!empty($_GET['tipo_vivienda'])) {
            $filters['tipo_vivienda'] = sanitize_text_field($_GET['tipo_vivienda']);
        }
        if (!empty($_GET['operacion'])) {
            $filters['operacion'] = sanitize_text_field($_GET['operacion']);
        }

        // ACF fields
        if (!empty($_GET['proposito'])) {
            $filters['proposito'] = sanitize_text_field($_GET['proposito']);
        }
        if (!empty($_GET['precio_min'])) {
            $filters['precio_min'] = absint($_GET['precio_min']);
        }
        if (!empty($_GET['precio_max'])) {
            $filters['precio_max'] = absint($_GET['precio_max']);
        }
        if (!empty($_GET['dormitorios_min'])) {
            $filters['dormitorios_min'] = absint($_GET['dormitorios_min']);
        }
        if (!empty($_GET['banos_min'])) {
            $filters['banos_min'] = absint($_GET['banos_min']);
        }
        if (!empty($_GET['superficie_min'])) {
            $filters['superficie_min'] = absint($_GET['superficie_min']);
        }
        if (!empty($_GET['superficie_max'])) {
            $filters['superficie_max'] = absint($_GET['superficie_max']);
        }
        if (!empty($_GET['estado'])) {
            $filters['estado'] = sanitize_text_field($_GET['estado']);
        }
        if (!empty($_GET['agrupacion'])) {
            $filters['agrupacion'] = sanitize_text_field($_GET['agrupacion']);
        }

        // Characteristics (all true_false fields from características especiales)
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
            if (isset($_GET[$char]) && $_GET[$char] !== '' && $_GET[$char] !== '0') {
                $filters[$char] = '1';
            }
        }

        // Order
        if (!empty($_GET['orderby'])) {
            $filters['orderby'] = sanitize_text_field($_GET['orderby']);
        }
        if (!empty($_GET['order'])) {
            $filters['order'] = sanitize_text_field($_GET['order']);
        }

        return $filters;
    }

    /**
     * Get provinces for filter
     *
     * @return array
     */
    public static function get_provinces()
    {
        $terms = get_terms(array(
            'taxonomy' => 'impress_province',
            'hide_empty' => true,
        ));

        if (is_wp_error($terms)) {
            return array();
        }

        return $terms;
    }

    /**
     * Get municipalities for filter
     *
     * @param int $province_id Province term ID
     * @return array
     */
    public static function get_municipalities($province_id = null)
    {
        $args = array(
            'taxonomy' => 'impress_municipality',
            'hide_empty' => true,
        );

        if ($province_id) {
            $args['parent'] = $province_id;
        }

        $terms = get_terms($args);

        if (is_wp_error($terms)) {
            return array();
        }

        return $terms;
    }

    /**
     * Get property types for filter
     *
     * @return array
     */
    public static function get_property_types()
    {
        $terms = get_terms(array(
            'taxonomy' => 'impress_property_type',
            'hide_empty' => true,
        ));

        if (is_wp_error($terms)) {
            return array();
        }

        return $terms;
    }

    /**
     * Get price range
     *
     * @param string $proposito Purpose (alquiler/venta)
     * @return array Min and max prices
     */
    public static function get_price_range($proposito = '')
    {
        global $wpdb;

        $meta_key = ($proposito === 'venta') ? 'precio_venta' : 'precio_alquiler';

        $query = $wpdb->prepare("
            SELECT MIN(CAST(meta_value AS UNSIGNED)) as min_price,
                   MAX(CAST(meta_value AS UNSIGNED)) as max_price
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.post_type = 'impress_property'
            AND p.post_status = 'publish'
            AND pm.meta_key = %s
            AND pm.meta_value != ''
            AND pm.meta_value IS NOT NULL
        ", $meta_key);

        $result = $wpdb->get_row($query);

        return array(
            'min' => $result->min_price ? (int) $result->min_price : 0,
            'max' => $result->max_price ? (int) $result->max_price : 0,
        );
    }
}

