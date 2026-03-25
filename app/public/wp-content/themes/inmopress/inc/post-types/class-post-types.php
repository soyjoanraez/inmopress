<?php
/**
 * Post Types Registration
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Post Types class
 */
class Post_Types
{

    /**
     * Instance of this class
     *
     * @var Post_Types
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Post_Types
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
        add_action('init', array($this, 'register_post_types'), 5);
    }

    /**
     * Register all post types
     */
    public function register_post_types()
    {
        // CPT Inmuebles
        $this->register_cpt('impress_property', 'Inmueble', 'Inmuebles', 'dashicons-building', array('title', 'editor', 'thumbnail', 'excerpt'));
        
        // CPT Clientes
        $this->register_cpt('impress_client', 'Cliente', 'Clientes', 'dashicons-groups', array('title', 'editor', 'thumbnail'));
        
        // CPT Leads
        $this->register_cpt('impress_lead', 'Lead', 'Leads', 'dashicons-email-alt', array('title', 'editor', 'thumbnail'));
        
        // CPT Visitas
        $this->register_cpt('impress_visit', 'Visita', 'Visitas', 'dashicons-calendar', array('title', 'editor', 'thumbnail'));
        
        // CPT Agencias
        $this->register_cpt('impress_agency', 'Agencia', 'Agencias', 'dashicons-store', array('title', 'editor', 'thumbnail'));
        
        // CPT Agentes
        $this->register_cpt('impress_agent', 'Agente', 'Agentes', 'dashicons-businessperson', array('title', 'editor', 'thumbnail'));
        
        // CPT Propietarios
        $this->register_cpt('impress_owner', 'Propietario', 'Propietarios', 'dashicons-admin-users', array('title', 'editor', 'thumbnail'));
        
        // CPT Promociones
        $this->register_cpt('impress_promotion', 'Promoción', 'Promociones', 'dashicons-megaphone', array('title', 'editor', 'thumbnail', 'excerpt'));
    }

    /**
     * Helper to register a CPT
     */
    private function register_cpt($slug, $singular, $plural, $icon, $supports)
    {
        $labels = array(
            'name' => $plural,
            'singular_name' => $singular,
            'menu_name' => $plural,
            'add_new' => 'Añadir Nuevo',
            'add_new_item' => 'Añadir Nuevo ' . $singular,
            'edit_item' => 'Editar ' . $singular,
            'new_item' => 'Nuevo ' . $singular,
            'view_item' => 'Ver ' . $singular,
            'view_items' => 'Ver ' . $plural,
            'search_items' => 'Buscar ' . $plural,
            'not_found' => 'No se encontraron ' . strtolower($plural),
            'not_found_in_trash' => 'No se encontraron ' . strtolower($plural) . ' en la papelera',
            'all_items' => 'Todos los ' . $plural,
            'archives' => 'Archivo de ' . $plural,
            'attributes' => 'Atributos de ' . $singular,
            'insert_into_item' => 'Insertar en ' . strtolower($singular),
            'uploaded_to_this_item' => 'Subido a este ' . strtolower($singular),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'menu_icon' => $icon,
            'supports' => $supports,
            'show_in_rest' => true,
            'rewrite' => array('slug' => str_replace('impress_', '', $slug)),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        );

        register_post_type($slug, $args);
    }
}
