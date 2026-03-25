<?php
if (!defined('ABSPATH')) exit;

class Inmopress_Taxonomies {
    
    public static function register() {
        // Taxonomías compartidas
        self::register_province();
        self::register_city();
        
        // Taxonomías de Inmuebles
        self::register_operation();
        self::register_property_type();
        self::register_property_group();
        self::register_features();
        self::register_condition();
        self::register_energy_rating();
        self::register_amenities();
        self::register_heating();
        self::register_orientation();
        self::register_category();
        self::register_status();
        
        // Taxonomías de Clientes/Leads
        self::register_lead_status();
        self::register_lead_source();
        self::register_language();
        
        // Taxonomías de Visitas
        self::register_visit_status();
        
        // Taxonomías de Agentes
        self::register_agent_specialty();
        
        // Taxonomías de Promociones
        self::register_promotion_status();
    }
    
    // PROVINCIA (Compartida)
    private static function register_province() {
        $labels = array(
            'name' => 'Provincias',
            'singular_name' => 'Provincia',
        );
        
        register_taxonomy('impress_province', 
            array('impress_property', 'impress_client', 'impress_agency', 'impress_owner'),
            array(
                'labels' => $labels,
                'public' => true,
                'hierarchical' => true,
                'show_ui' => true,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'provincia', 'hierarchical' => true),
            )
        );
    }
    
    // CIUDAD (Compartida, jerárquica bajo Provincia)
    private static function register_city() {
        $labels = array(
            'name' => 'Ciudades',
            'singular_name' => 'Ciudad',
        );
        
        register_taxonomy('impress_city',
            array('impress_property', 'impress_client', 'impress_agency', 'impress_owner'),
            array(
                'labels' => $labels,
                'public' => true,
                'hierarchical' => true,
                'show_ui' => true,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'ciudad', 'hierarchical' => true),
            )
        );
    }
    
    // OPERACIÓN (Inmuebles)
    private static function register_operation() {
        register_taxonomy('impress_operation', 'impress_property',
            array(
                'labels' => array('name' => 'Operaciones', 'singular_name' => 'Operación'),
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'operacion'),
            )
        );
    }
    
    // TIPO DE VIVIENDA (Inmuebles)
    private static function register_property_type() {
        register_taxonomy('impress_property_type', 'impress_property',
            array(
                'labels' => array('name' => 'Tipos de Vivienda', 'singular_name' => 'Tipo'),
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'tipo'),
            )
        );
    }
    
    // AGRUPACIÓN (Inmuebles)
    private static function register_property_group() {
        register_taxonomy('impress_property_group', 'impress_property',
            array(
                'labels' => array('name' => 'Agrupaciones', 'singular_name' => 'Agrupación'),
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'grupo'),
            )
        );
    }
    
    // CARACTERÍSTICAS PREMIUM (Inmuebles)
    private static function register_features() {
        register_taxonomy('impress_features', 'impress_property',
            array(
                'labels' => array('name' => 'Características Premium', 'singular_name' => 'Característica'),
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'caracteristica'),
            )
        );
    }
    
    // ESTADO CONSERVACIÓN (Inmuebles)
    private static function register_condition() {
        register_taxonomy('impress_condition', 'impress_property',
            array(
                'labels' => array('name' => 'Estados', 'singular_name' => 'Estado'),
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'estado'),
            )
        );
    }
    
    // CERTIFICACIÓN ENERGÉTICA (Inmuebles)
    private static function register_energy_rating() {
        register_taxonomy('impress_energy_rating', 'impress_property',
            array(
                'labels' => array('name' => 'Certificaciones Energéticas', 'singular_name' => 'Certificación'),
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'certificacion'),
            )
        );
    }
    
    // EQUIPAMIENTO (Inmuebles)
    private static function register_amenities() {
        register_taxonomy('impress_amenities', 'impress_property',
            array(
                'labels' => array('name' => 'Equipamiento', 'singular_name' => 'Equipamiento'),
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'equipamiento'),
            )
        );
    }
    
    // CALEFACCIÓN (Inmuebles)
    private static function register_heating() {
        register_taxonomy('impress_heating', 'impress_property',
            array(
                'labels' => array('name' => 'Tipos de Calefacción', 'singular_name' => 'Calefacción'),
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'calefaccion'),
            )
        );
    }
    
    // ORIENTACIÓN (Inmuebles)
    private static function register_orientation() {
        register_taxonomy('impress_orientation', 'impress_property',
            array(
                'labels' => array('name' => 'Orientaciones', 'singular_name' => 'Orientación'),
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'orientacion'),
            )
        );
    }
    
    // ESTADO LEAD (Clientes/Leads)
    private static function register_lead_status() {
        register_taxonomy('impress_lead_status',
            array('impress_client', 'impress_lead'),
            array(
                'labels' => array('name' => 'Estados Lead', 'singular_name' => 'Estado'),
                'public' => false,
                'show_ui' => true,
                'show_in_rest' => true,
            )
        );
    }
    
    // CANAL ENTRADA (Clientes/Leads)
    private static function register_lead_source() {
        register_taxonomy('impress_lead_source',
            array('impress_client', 'impress_lead'),
            array(
                'labels' => array('name' => 'Canales', 'singular_name' => 'Canal'),
                'public' => false,
                'show_ui' => true,
                'show_in_rest' => true,
            )
        );
    }
    
    // IDIOMA (Clientes/Leads)
    private static function register_language() {
        register_taxonomy('impress_language',
            array('impress_client', 'impress_lead'),
            array(
                'labels' => array('name' => 'Idiomas', 'singular_name' => 'Idioma'),
                'public' => false,
                'show_ui' => true,
                'show_in_rest' => true,
            )
        );
    }
    
    // ESTADO VISITA (Visitas)
    private static function register_visit_status() {
        register_taxonomy('impress_visit_status', 'impress_visit',
            array(
                'labels' => array('name' => 'Estados Visita', 'singular_name' => 'Estado'),
                'public' => false,
                'show_ui' => true,
                'show_in_rest' => true,
            )
        );
    }
    
    // ESPECIALIZACIÓN AGENTE (Agentes)
    private static function register_agent_specialty() {
        register_taxonomy('impress_agent_specialty', 'impress_agent',
            array(
                'labels' => array('name' => 'Especializaciones', 'singular_name' => 'Especialización'),
                'public' => false,
                'show_ui' => true,
                'show_in_rest' => true,
            )
        );
    }
    
    // ESTADO PROMOCIÓN (Promociones)
    private static function register_promotion_status() {
        register_taxonomy('impress_promotion_status', 'impress_promotion',
            array(
                'labels' => array('name' => 'Estados Promoción', 'singular_name' => 'Estado'),
                'public' => true,
                'show_ui' => true,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'estado-promocion'),
            )
        );
    }
    
    // CATEGORÍA / AGRUPACIÓN (Inmuebles)
    private static function register_category() {
        register_taxonomy('impress_category', 'impress_property',
            array(
                'labels' => array('name' => 'Categorías', 'singular_name' => 'Categoría'),
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'categoria'),
            )
        );
    }
    
    // ESTADO COMERCIAL (Inmuebles)
    private static function register_status() {
        register_taxonomy('impress_status', 'impress_property',
            array(
                'labels' => array('name' => 'Estados Comerciales', 'singular_name' => 'Estado'),
                'public' => false,
                'show_ui' => true,
                'show_in_rest' => true,
            )
        );
    }
}

