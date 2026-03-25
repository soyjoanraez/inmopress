<?php
if (!defined('ABSPATH')) exit;

class Inmopress_Roles {
    
    public static function create_roles() {
        self::create_agencia_role();
        self::create_agente_role();
        self::create_trabajador_role();
    }
    
    private static function create_agencia_role() {
        add_role('agencia', 'Agencia', array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => false,
            'publish_posts' => false, // Los inmuebles quedan en borrador
        ));
    }
    
    private static function create_agente_role() {
        add_role('agente', 'Agente', array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => false,
            'publish_posts' => true,
        ));
    }
    
    private static function create_trabajador_role() {
        add_role('trabajador', 'Trabajador', array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => false,
        ));
    }
    
    public static function remove_roles() {
        remove_role('agencia');
        remove_role('agente');
        remove_role('trabajador');
    }
}


