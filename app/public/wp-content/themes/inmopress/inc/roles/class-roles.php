<?php
/**
 * Roles and Capabilities
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Roles class
 */
class Roles
{

    /**
     * Instance of this class
     *
     * @var Roles
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Roles
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
        add_action('init', array($this, 'register_roles'), 5);
        add_action('init', array($this, 'add_capabilities'), 10);
    }

    /**
     * Register custom roles
     */
    public function register_roles()
    {
        // Role: Agencia
        add_role(
            'agency',
            'Agencia',
            array(
                'read' => true,
                'edit_posts' => true,
                'publish_posts' => true,
                'delete_posts' => false,
            )
        );

        // Role: Agente
        add_role(
            'agent',
            'Agente',
            array(
                'read' => true,
                'edit_posts' => true,
                'publish_posts' => true,
                'delete_posts' => false,
            )
        );

        // Role: Trabajador
        add_role(
            'trabajador',
            'Trabajador',
            array(
                'read' => true,
                'edit_posts' => true,
                'publish_posts' => true,
                'delete_posts' => false,
            )
        );

        // Role: Cliente
        add_role(
            'cliente',
            'Cliente',
            array(
                'read' => true,
            )
        );
    }

    /**
     * Add capabilities to roles
     */
    public function add_capabilities()
    {
        // Get roles
        $agency = get_role('agency');
        $agent = get_role('agent');
        $trabajador = get_role('trabajador');
        $cliente = get_role('cliente');
        $administrator = get_role('administrator');

        // CPT slugs
        $cpts = array(
            'impress_property' => 'inmueble',
            'impress_client' => 'cliente',
            'impress_lead' => 'lead',
            'impress_visit' => 'visita',
            'impress_agency' => 'agencia',
            'impress_agent' => 'agente',
            'impress_owner' => 'propietario',
            'impress_promotion' => 'promocion',
        );

        // Define capabilities for each CPT
        foreach ($cpts as $cpt => $singular) {
            // Administrator - Full access
            if ($administrator) {
                $administrator->add_cap("edit_{$cpt}");
                $administrator->add_cap("edit_{$cpt}s");
                $administrator->add_cap("edit_others_{$cpt}s");
                $administrator->add_cap("publish_{$cpt}s");
                $administrator->add_cap("read_private_{$cpt}s");
                $administrator->add_cap("delete_{$cpt}");
                $administrator->add_cap("delete_{$cpt}s");
                $administrator->add_cap("delete_private_{$cpt}s");
                $administrator->add_cap("delete_published_{$cpt}s");
                $administrator->add_cap("delete_others_{$cpt}s");
                $administrator->add_cap("edit_private_{$cpt}s");
                $administrator->add_cap("edit_published_{$cpt}s");
            }

            // Agency - View and create, but cannot edit
            if ($agency) {
                $agency->add_cap("read_{$cpt}");
                $agency->add_cap("read_{$cpt}s");
                $agency->add_cap("edit_{$cpt}");
                $agency->add_cap("edit_{$cpt}s");
                $agency->add_cap("publish_{$cpt}s");
                $agency->add_cap("read_private_{$cpt}s");
            }

            // Agent - View and create, but cannot edit
            if ($agent) {
                $agent->add_cap("read_{$cpt}");
                $agent->add_cap("read_{$cpt}s");
                $agent->add_cap("edit_{$cpt}");
                $agent->add_cap("edit_{$cpt}s");
                $agent->add_cap("publish_{$cpt}s");
                $agent->add_cap("read_private_{$cpt}s");
            }

            // Trabajador - View and create, but cannot edit
            if ($trabajador) {
                $trabajador->add_cap("read_{$cpt}");
                $trabajador->add_cap("read_{$cpt}s");
                $trabajador->add_cap("edit_{$cpt}");
                $trabajador->add_cap("edit_{$cpt}s");
                $trabajador->add_cap("publish_{$cpt}s");
                $trabajador->add_cap("read_private_{$cpt}s");
            }

            // Cliente - No access to admin CPTs
            // Cliente role only has 'read' capability
        }

        // Special permissions for Property editing
        // Only Administrator can edit properties
        if ($agent) {
            $agent->remove_cap("edit_published_impress_properties");
            $agent->remove_cap("edit_private_impress_properties");
        }
        if ($trabajador) {
            $trabajador->remove_cap("edit_published_impress_properties");
            $trabajador->remove_cap("edit_private_impress_properties");
        }
        if ($agency) {
            $agency->remove_cap("edit_published_impress_properties");
            $agency->remove_cap("edit_private_impress_properties");
        }

        // Users management - Only Administrator
        if ($administrator) {
            $administrator->add_cap('list_users');
            $administrator->add_cap('edit_users');
            $administrator->add_cap('create_users');
            $administrator->add_cap('delete_users');
        }

        // Remove user management from other roles
        if ($agent) {
            $agent->remove_cap('list_users');
            $agent->remove_cap('edit_users');
            $agent->remove_cap('create_users');
            $agent->remove_cap('delete_users');
        }
        if ($trabajador) {
            $trabajador->remove_cap('list_users');
            $trabajador->remove_cap('edit_users');
            $trabajador->remove_cap('create_users');
            $trabajador->remove_cap('delete_users');
        }
        if ($agency) {
            $agency->remove_cap('list_users');
            $agency->remove_cap('edit_users');
            $agency->remove_cap('create_users');
            $agency->remove_cap('delete_users');
        }
    }
}






