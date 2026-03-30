<?php
if (!defined('ABSPATH'))
    exit;

class Inmopress_CPTs
{

    public static function register()
    {
        self::register_property();
        self::register_client();
        self::register_lead();
        self::register_visit();
        self::register_agency();
        self::register_agent();
        self::register_owner();
        self::register_promotion();
        self::register_transaction();
        self::register_email_template();
        self::register_event();
    }

    // INMUEBLES
    private static function register_property()
    {
        $labels = array(
            'name' => 'Inmuebles',
            'singular_name' => 'Inmueble',
            'add_new' => 'Añadir Inmueble',
            'add_new_item' => 'Añadir Nuevo Inmueble',
            'edit_item' => 'Editar Inmueble',
            'new_item' => 'Nuevo Inmueble',
            'view_item' => 'Ver Inmueble',
            'search_items' => 'Buscar Inmuebles',
            'not_found' => 'No se encontraron inmuebles',
            'menu_name' => 'Inmuebles'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true, // Gutenberg
            'menu_icon' => 'dashicons-admin-home',
            'menu_position' => 5,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'rewrite' => array('slug' => 'inmuebles', 'with_front' => false),
            'capability_type' => 'post',
            'hierarchical' => false,
        );

        register_post_type('impress_property', $args);
    }

    // CLIENTES
    private static function register_client()
    {
        $labels = array(
            'name' => 'Clientes',
            'singular_name' => 'Cliente',
            'menu_name' => 'Clientes'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-groups',
            'menu_position' => 6,
            'supports' => array('title'),
            'capability_type' => 'post',
        );

        register_post_type('impress_client', $args);
    }

    // LEADS
    private static function register_lead()
    {
        $labels = array(
            'name' => 'Leads',
            'singular_name' => 'Lead',
            'menu_name' => 'Leads'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-megaphone',
            'menu_position' => 7,
            'supports' => array('title'),
            'capability_type' => 'post',
        );

        register_post_type('impress_lead', $args);
    }

    // VISITAS
    private static function register_visit()
    {
        $labels = array(
            'name' => 'Visitas',
            'singular_name' => 'Visita',
            'menu_name' => 'Visitas'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-calendar-alt',
            'menu_position' => 8,
            'supports' => array('title'),
            'capability_type' => 'post',
        );

        register_post_type('impress_visit', $args);
    }

    // AGENCIAS
    private static function register_agency()
    {
        $labels = array(
            'name' => 'Agencias',
            'singular_name' => 'Agencia',
            'menu_name' => 'Agencias'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-building',
            'menu_position' => 9,
            'supports' => array('title'),
            'capability_type' => 'post',
        );

        register_post_type('impress_agency', $args);
    }

    // AGENTES
    private static function register_agent()
    {
        $labels = array(
            'name' => 'Agentes',
            'singular_name' => 'Agente',
            'menu_name' => 'Agentes'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-businessman',
            'menu_position' => 10,
            'supports' => array('title'),
            'capability_type' => 'post',
        );

        register_post_type('impress_agent', $args);
    }

    // PROPIETARIOS
    private static function register_owner()
    {
        $labels = array(
            'name' => 'Propietarios',
            'singular_name' => 'Propietario',
            'menu_name' => 'Propietarios'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-users',
            'menu_position' => 11,
            'supports' => array('title'),
            'capability_type' => 'post',
        );

        register_post_type('impress_owner', $args);
    }

    // PROMOCIONES
    private static function register_promotion()
    {
        $labels = array(
            'name' => 'Promociones',
            'singular_name' => 'Promoción',
            'menu_name' => 'Promociones'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-star-filled',
            'menu_position' => 12,
            'supports' => array('title', 'editor', 'thumbnail'),
            'rewrite' => array('slug' => 'promociones'),
            'capability_type' => 'post',
        );

        register_post_type('impress_promotion', $args);
    }
    // TRANSACTIONS
    private static function register_transaction()
    {
        $labels = array(
            'name' => 'Transacciones',
            'singular_name' => 'Transacción',
            'menu_name' => 'Transacciones',
            'add_new' => 'Añadir Transacción',
            'add_new_item' => 'Añadir Nueva Transacción',
            'edit_item' => 'Editar Transacción',
            'new_item' => 'Nueva Transacción',
            'view_item' => 'Ver Transacción',
            'search_items' => 'Buscar Transacciones',
            'not_found' => 'No se encontraron transacciones',
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-chart-line',
            'menu_position' => 13,
            'supports' => array('title'),
            'capability_type' => 'post',
        );

        register_post_type('impress_transaction', $args);
    }

    // EMAIL TEMPLATES
    private static function register_email_template()
    {
        $labels = array(
            'name' => 'Plantillas Email',
            'singular_name' => 'Plantilla Email',
            'add_new' => 'Añadir Plantilla',
            'add_new_item' => 'Añadir Nueva Plantilla',
            'edit_item' => 'Editar Plantilla',
            'new_item' => 'Nueva Plantilla',
            'view_item' => 'Ver Plantilla',
            'search_items' => 'Buscar Plantillas',
            'not_found' => 'No se encontraron plantillas',
            'menu_name' => 'Emails',
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-email-alt',
            'menu_position' => 14,
            'supports' => array('title'),
            'capability_type' => 'post',
        );

        register_post_type('impress_email_tpl', $args);
    }

    public static function maybe_migrate_email_templates()
    {
        if (get_option('inmopress_email_tpl_migrated')) {
            return;
        }

        global $wpdb;
        $old = 'impress_email_template';
        $new = 'impress_email_tpl';

        $wpdb->update(
            $wpdb->posts,
            array('post_type' => $new),
            array('post_type' => $old)
        );

        update_option('inmopress_email_tpl_migrated', time());
    }

    // EVENTOS (Tareas/Calendario)
    private static function register_event()
    {
        $labels = array(
            'name' => 'Eventos',
            'singular_name' => 'Evento',
            'add_new' => 'Añadir Evento',
            'add_new_item' => 'Añadir Nuevo Evento',
            'edit_item' => 'Editar Evento',
            'new_item' => 'Nuevo Evento',
            'view_item' => 'Ver Evento',
            'search_items' => 'Buscar Eventos',
            'not_found' => 'No se encontraron eventos',
            'menu_name' => 'Eventos',
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-schedule',
            'menu_position' => 15,
            'supports' => array('title'),
            'capability_type' => 'post',
        );

        register_post_type('impress_event', $args);
    }
}
