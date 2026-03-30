<?php
/**
 * Taxonomies Registration
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Taxonomies class
 */
class Taxonomies
{

    /**
     * Instance of this class
     *
     * @var Taxonomies
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Taxonomies
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
        add_action('init', array($this, 'register_taxonomies'), 5);
    }

    /**
     * Register all taxonomies
     */
    public function register_taxonomies()
    {
        // Taxonomía: Operación
        $this->register_tax('impress_operation', 'Operación', 'Operaciones', array('impress_property'), false);
        
        // Taxonomía: Tipo de Vivienda
        $this->register_tax('impress_property_type', 'Tipo de Vivienda', 'Tipos de Vivienda', array('impress_property'), false);
        
        // Taxonomía: Provincia (Ciudad)
        $this->register_tax('impress_province', 'Provincia', 'Provincias', array('impress_property', 'impress_client', 'impress_owner', 'impress_agency'), false);
        
        // Taxonomía: Población (Municipio) - Jerárquica bajo Provincia
        $this->register_tax('impress_municipality', 'Población', 'Poblaciones', array('impress_property', 'impress_client', 'impress_owner', 'impress_agency'), true);
        
        // Agregar términos iniciales después de registrar taxonomías
        add_action('init', array($this, 'register_default_terms'), 20);
    }
    
    /**
     * Register default taxonomy terms
     */
    public function register_default_terms()
    {
        // Términos para Operación
        $this->insert_term_if_not_exists('Alquiler', 'impress_operation');
        $this->insert_term_if_not_exists('Venta', 'impress_operation');
        $this->insert_term_if_not_exists('Alquiler vacacional', 'impress_operation');
        
        // Términos para Tipo de Vivienda
        $property_types = array(
            'Apartamento',
            'Casa',
            'Chalet',
            'Piso',
            'Loft',
            'Ático',
            'Estudio',
            'Dúplex',
            'Bungalow',
            'Finca',
            'Adosado',
            'Casa de Campo',
            'Mansión'
        );
        foreach ($property_types as $type) {
            $this->insert_term_if_not_exists($type, 'impress_property_type');
        }
        
        // Términos para Provincia
        $this->insert_term_if_not_exists('Alicante', 'impress_province');
        $this->insert_term_if_not_exists('Valencia', 'impress_province');
        
        // Términos para Población (bajo Provincia)
        $valencia_id = get_term_by('slug', 'valencia', 'impress_province');
        if ($valencia_id) {
            $this->insert_term_if_not_exists('Paterna', 'impress_municipality', array('parent' => $valencia_id->term_id));
            $this->insert_term_if_not_exists('Manises', 'impress_municipality', array('parent' => $valencia_id->term_id));
        }
    }
    
    /**
     * Insert term if it doesn't exist
     */
    private function insert_term_if_not_exists($term_name, $taxonomy, $args = array())
    {
        if (!term_exists($term_name, $taxonomy)) {
            wp_insert_term($term_name, $taxonomy, $args);
        }
    }

    /**
     * Helper to register a Taxonomy
     */
    private function register_tax($slug, $singular, $plural, $post_types, $hierarchical = true)
    {
        $labels = array(
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => 'Buscar ' . $plural,
            'all_items' => 'Todos los ' . $plural,
            'edit_item' => 'Editar ' . $singular,
            'update_item' => 'Actualizar ' . $singular,
            'add_new_item' => 'Añadir Nuevo ' . $singular,
            'new_item_name' => 'Nombre del Nuevo ' . $singular,
            'menu_name' => $plural,
            'parent_item' => $hierarchical ? 'Padre ' . $singular : null,
            'parent_item_colon' => $hierarchical ? 'Padre ' . $singular . ':' : null,
        );

        $args = array(
            'hierarchical' => $hierarchical,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => str_replace('impress_', '', $slug)),
        );

        register_taxonomy($slug, $post_types, $args);
    }
}
