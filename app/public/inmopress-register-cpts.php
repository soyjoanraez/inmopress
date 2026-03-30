<?php
/**
 * Registro de Custom Post Types y Taxonomías - Inmopress
 * 
 * @package Inmopress
 * @version 1.0.0
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar todos los CPTs de Inmopress
 */
function inmopress_register_cpts() {
    
    // 1. CPT: INMUEBLES
    register_post_type('impress_property', [
        'labels' => [
            'name' => _x('Inmuebles', 'Post type general name', 'inmopress'),
            'singular_name' => _x('Inmueble', 'Post type singular name', 'inmopress'),
            'menu_name' => _x('Inmuebles', 'Admin Menu text', 'inmopress'),
            'add_new' => __('Añadir Nuevo', 'inmopress'),
            'add_new_item' => __('Añadir Nuevo Inmueble', 'inmopress'),
            'edit_item' => __('Editar Inmueble', 'inmopress'),
            'new_item' => __('Nuevo Inmueble', 'inmopress'),
            'view_item' => __('Ver Inmueble', 'inmopress'),
            'view_items' => __('Ver Inmuebles', 'inmopress'),
            'search_items' => __('Buscar Inmuebles', 'inmopress'),
            'not_found' => __('No se encontraron inmuebles', 'inmopress'),
            'not_found_in_trash' => __('No se encontraron inmuebles en la papelera', 'inmopress'),
            'all_items' => __('Todos los Inmuebles', 'inmopress'),
        ],
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-admin-home',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'editor', 'thumbnail', 'author', 'revisions'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'inmuebles', 'with_front' => false],
        'show_in_rest' => true,
    ]);
    
    // 2. CPT: CLIENTES
    register_post_type('impress_client', [
        'labels' => [
            'name' => _x('Clientes', 'Post type general name', 'inmopress'),
            'singular_name' => _x('Cliente', 'Post type singular name', 'inmopress'),
            'menu_name' => _x('Clientes', 'Admin Menu text', 'inmopress'),
            'add_new' => __('Añadir Nuevo', 'inmopress'),
            'add_new_item' => __('Añadir Nuevo Cliente', 'inmopress'),
            'edit_item' => __('Editar Cliente', 'inmopress'),
            'new_item' => __('Nuevo Cliente', 'inmopress'),
            'view_item' => __('Ver Cliente', 'inmopress'),
            'search_items' => __('Buscar Clientes', 'inmopress'),
            'not_found' => __('No se encontraron clientes', 'inmopress'),
            'all_items' => __('Todos los Clientes', 'inmopress'),
        ],
        'public' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => false,
        'show_in_admin_bar' => true,
        'menu_position' => 6,
        'menu_icon' => 'dashicons-groups',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'author', 'revisions'],
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => true,
    ]);
    
    // 3. CPT: LEADS
    register_post_type('impress_lead', [
        'labels' => [
            'name' => _x('Leads', 'Post type general name', 'inmopress'),
            'singular_name' => _x('Lead', 'Post type singular name', 'inmopress'),
            'menu_name' => _x('Leads', 'Admin Menu text', 'inmopress'),
            'add_new' => __('Añadir Nuevo', 'inmopress'),
            'add_new_item' => __('Añadir Nuevo Lead', 'inmopress'),
            'edit_item' => __('Editar Lead', 'inmopress'),
            'new_item' => __('Nuevo Lead', 'inmopress'),
            'view_item' => __('Ver Lead', 'inmopress'),
            'search_items' => __('Buscar Leads', 'inmopress'),
            'not_found' => __('No se encontraron leads', 'inmopress'),
            'all_items' => __('Todos los Leads', 'inmopress'),
        ],
        'public' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => false,
        'show_in_admin_bar' => true,
        'menu_position' => 7,
        'menu_icon' => 'dashicons-megaphone',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'author', 'revisions'],
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => true,
    ]);
    
    // 4. CPT: VISITAS
    register_post_type('impress_visit', [
        'labels' => [
            'name' => _x('Visitas', 'Post type general name', 'inmopress'),
            'singular_name' => _x('Visita', 'Post type singular name', 'inmopress'),
            'menu_name' => _x('Visitas', 'Admin Menu text', 'inmopress'),
            'add_new' => __('Añadir Nueva', 'inmopress'),
            'add_new_item' => __('Añadir Nueva Visita', 'inmopress'),
            'edit_item' => __('Editar Visita', 'inmopress'),
            'new_item' => __('Nueva Visita', 'inmopress'),
            'view_item' => __('Ver Visita', 'inmopress'),
            'search_items' => __('Buscar Visitas', 'inmopress'),
            'not_found' => __('No se encontraron visitas', 'inmopress'),
            'all_items' => __('Todas las Visitas', 'inmopress'),
        ],
        'public' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => false,
        'show_in_admin_bar' => true,
        'menu_position' => 8,
        'menu_icon' => 'dashicons-calendar-alt',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'author', 'revisions'],
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => true,
    ]);
    
    // 5. CPT: AGENCIAS
    register_post_type('impress_agency', [
        'labels' => [
            'name' => _x('Agencias', 'Post type general name', 'inmopress'),
            'singular_name' => _x('Agencia', 'Post type singular name', 'inmopress'),
            'menu_name' => _x('Agencias', 'Admin Menu text', 'inmopress'),
            'add_new' => __('Añadir Nueva', 'inmopress'),
            'add_new_item' => __('Añadir Nueva Agencia', 'inmopress'),
            'edit_item' => __('Editar Agencia', 'inmopress'),
            'new_item' => __('Nueva Agencia', 'inmopress'),
            'view_item' => __('Ver Agencia', 'inmopress'),
            'search_items' => __('Buscar Agencias', 'inmopress'),
            'not_found' => __('No se encontraron agencias', 'inmopress'),
            'all_items' => __('Todas las Agencias', 'inmopress'),
        ],
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 9,
        'menu_icon' => 'dashicons-building',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'thumbnail', 'revisions'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'agencias', 'with_front' => false],
        'show_in_rest' => true,
    ]);
    
    // 6. CPT: AGENTES
    register_post_type('impress_agent', [
        'labels' => [
            'name' => _x('Agentes', 'Post type general name', 'inmopress'),
            'singular_name' => _x('Agente', 'Post type singular name', 'inmopress'),
            'menu_name' => _x('Agentes', 'Admin Menu text', 'inmopress'),
            'add_new' => __('Añadir Nuevo', 'inmopress'),
            'add_new_item' => __('Añadir Nuevo Agente', 'inmopress'),
            'edit_item' => __('Editar Agente', 'inmopress'),
            'new_item' => __('Nuevo Agente', 'inmopress'),
            'view_item' => __('Ver Agente', 'inmopress'),
            'search_items' => __('Buscar Agentes', 'inmopress'),
            'not_found' => __('No se encontraron agentes', 'inmopress'),
            'all_items' => __('Todos los Agentes', 'inmopress'),
        ],
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 10,
        'menu_icon' => 'dashicons-businessman',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'thumbnail', 'revisions'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'agentes', 'with_front' => false],
        'show_in_rest' => true,
    ]);
    
    // 7. CPT: PROPIETARIOS
    register_post_type('impress_owner', [
        'labels' => [
            'name' => _x('Propietarios', 'Post type general name', 'inmopress'),
            'singular_name' => _x('Propietario', 'Post type singular name', 'inmopress'),
            'menu_name' => _x('Propietarios', 'Admin Menu text', 'inmopress'),
            'add_new' => __('Añadir Nuevo', 'inmopress'),
            'add_new_item' => __('Añadir Nuevo Propietario', 'inmopress'),
            'edit_item' => __('Editar Propietario', 'inmopress'),
            'new_item' => __('Nuevo Propietario', 'inmopress'),
            'view_item' => __('Ver Propietario', 'inmopress'),
            'search_items' => __('Buscar Propietarios', 'inmopress'),
            'not_found' => __('No se encontraron propietarios', 'inmopress'),
            'all_items' => __('Todos los Propietarios', 'inmopress'),
        ],
        'public' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => false,
        'show_in_admin_bar' => true,
        'menu_position' => 11,
        'menu_icon' => 'dashicons-admin-users',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'revisions'],
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => true,
    ]);
    
    // 8. CPT: PROMOCIONES
    register_post_type('impress_promotion', [
        'labels' => [
            'name' => _x('Promociones', 'Post type general name', 'inmopress'),
            'singular_name' => _x('Promoción', 'Post type singular name', 'inmopress'),
            'menu_name' => _x('Promociones', 'Admin Menu text', 'inmopress'),
            'add_new' => __('Añadir Nueva', 'inmopress'),
            'add_new_item' => __('Añadir Nueva Promoción', 'inmopress'),
            'edit_item' => __('Editar Promoción', 'inmopress'),
            'new_item' => __('Nueva Promoción', 'inmopress'),
            'view_item' => __('Ver Promoción', 'inmopress'),
            'search_items' => __('Buscar Promociones', 'inmopress'),
            'not_found' => __('No se encontraron promociones', 'inmopress'),
            'all_items' => __('Todas las Promociones', 'inmopress'),
        ],
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 12,
        'menu_icon' => 'dashicons-megaphone',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'editor', 'thumbnail', 'revisions'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'promociones', 'with_front' => false],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'inmopress_register_cpts');

/**
 * Registrar todas las Taxonomías de Inmopress
 */
function inmopress_register_taxonomies() {
    
    // 1. TAXONOMÍA: PROVINCIA
    register_taxonomy('impress_province', 
        ['impress_property', 'impress_client', 'impress_agency', 'impress_owner'], 
        [
            'labels' => [
                'name' => _x('Provincias', 'taxonomy general name', 'inmopress'),
                'singular_name' => _x('Provincia', 'taxonomy singular name', 'inmopress'),
                'search_items' => __('Buscar Provincias', 'inmopress'),
                'all_items' => __('Todas las Provincias', 'inmopress'),
                'parent_item' => null,
                'parent_item_colon' => null,
                'edit_item' => __('Editar Provincia', 'inmopress'),
                'update_item' => __('Actualizar Provincia', 'inmopress'),
                'add_new_item' => __('Añadir Nueva Provincia', 'inmopress'),
                'new_item_name' => __('Nuevo Nombre de Provincia', 'inmopress'),
                'menu_name' => __('Provincias', 'inmopress'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'provincia'],
            'show_in_rest' => true,
        ]
    );
    
    // 2. TAXONOMÍA: POBLACIÓN (Jerárquica bajo Provincia)
    register_taxonomy('impress_city', 
        ['impress_property', 'impress_client', 'impress_agency', 'impress_owner'], 
        [
            'labels' => [
                'name' => _x('Poblaciones', 'taxonomy general name', 'inmopress'),
                'singular_name' => _x('Población', 'taxonomy singular name', 'inmopress'),
                'search_items' => __('Buscar Poblaciones', 'inmopress'),
                'all_items' => __('Todas las Poblaciones', 'inmopress'),
                'parent_item' => __('Provincia Superior', 'inmopress'),
                'parent_item_colon' => __('Provincia Superior:', 'inmopress'),
                'edit_item' => __('Editar Población', 'inmopress'),
                'update_item' => __('Actualizar Población', 'inmopress'),
                'add_new_item' => __('Añadir Nueva Población', 'inmopress'),
                'new_item_name' => __('Nuevo Nombre de Población', 'inmopress'),
                'menu_name' => __('Poblaciones', 'inmopress'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'poblacion'],
            'show_in_rest' => true,
        ]
    );
    
    // 3. TAXONOMÍA: TIPO DE VIVIENDA
    register_taxonomy('impress_property_type', 
        ['impress_property'], 
        [
            'labels' => [
                'name' => _x('Tipos de Vivienda', 'taxonomy general name', 'inmopress'),
                'singular_name' => _x('Tipo de Vivienda', 'taxonomy singular name', 'inmopress'),
                'search_items' => __('Buscar Tipos', 'inmopress'),
                'all_items' => __('Todos los Tipos', 'inmopress'),
                'parent_item' => __('Tipo Superior', 'inmopress'),
                'parent_item_colon' => __('Tipo Superior:', 'inmopress'),
                'edit_item' => __('Editar Tipo', 'inmopress'),
                'update_item' => __('Actualizar Tipo', 'inmopress'),
                'add_new_item' => __('Añadir Nuevo Tipo', 'inmopress'),
                'new_item_name' => __('Nuevo Nombre de Tipo', 'inmopress'),
                'menu_name' => __('Tipos', 'inmopress'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'tipo'],
            'show_in_rest' => true,
        ]
    );
    
    // 4. TAXONOMÍA: OPERACIÓN
    register_taxonomy('impress_operation', 
        ['impress_property'], 
        [
            'labels' => [
                'name' => _x('Operaciones', 'taxonomy general name', 'inmopress'),
                'singular_name' => _x('Operación', 'taxonomy singular name', 'inmopress'),
                'search_items' => __('Buscar Operaciones', 'inmopress'),
                'all_items' => __('Todas las Operaciones', 'inmopress'),
                'edit_item' => __('Editar Operación', 'inmopress'),
                'update_item' => __('Actualizar Operación', 'inmopress'),
                'add_new_item' => __('Añadir Nueva Operación', 'inmopress'),
                'new_item_name' => __('Nuevo Nombre de Operación', 'inmopress'),
                'menu_name' => __('Operaciones', 'inmopress'),
            ],
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'operacion'],
            'show_in_rest' => true,
        ]
    );
}
add_action('init', 'inmopress_register_taxonomies');

/**
 * Insertar términos por defecto en taxonomías
 * Solo se ejecuta en la activación del plugin
 */
function inmopress_insert_default_terms() {
    
    // Provincias de ejemplo (personalizar según necesidad)
    $provincias = ['Valencia', 'Alicante', 'Castellón', 'Madrid', 'Barcelona'];
    foreach ($provincias as $provincia) {
        if (!term_exists($provincia, 'impress_province')) {
            wp_insert_term($provincia, 'impress_province');
        }
    }
    
    // Poblaciones de Valencia (personalizar según necesidad)
    $poblaciones_valencia = ['Paterna', 'Manises', 'Valencia', 'Torrent', 'Burjassot'];
    $valencia_term = get_term_by('name', 'Valencia', 'impress_province');
    if ($valencia_term) {
        foreach ($poblaciones_valencia as $poblacion) {
            if (!term_exists($poblacion, 'impress_city')) {
                wp_insert_term($poblacion, 'impress_city', ['parent' => $valencia_term->term_id]);
            }
        }
    }
    
    // Tipos de Vivienda
    $tipos = [
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
    ];
    foreach ($tipos as $tipo) {
        if (!term_exists($tipo, 'impress_property_type')) {
            wp_insert_term($tipo, 'impress_property_type');
        }
    }
    
    // Operaciones
    $operaciones = ['Alquiler', 'Venta', 'Alquiler vacacional'];
    foreach ($operaciones as $operacion) {
        if (!term_exists($operacion, 'impress_operation')) {
            wp_insert_term($operacion, 'impress_operation');
        }
    }
}

/**
 * Hook de activación del plugin
 * Registra CPTs, Taxonomías, inserta términos y hace flush
 */
function inmopress_activate() {
    // Registrar CPTs y Taxonomías
    inmopress_register_cpts();
    inmopress_register_taxonomies();
    
    // Insertar términos por defecto
    inmopress_insert_default_terms();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'inmopress_activate');

/**
 * Hook de desactivación del plugin
 * Limpia las rewrite rules
 */
function inmopress_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'inmopress_deactivate');
