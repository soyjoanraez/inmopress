<?php
/**
 * Plugin Name: Inmopress Printables
 * Description: Plantillas imprimibles para inmuebles
 * Version: 1.0.0
 * Author: Inmopress
 */

if (!defined('ABSPATH'))
    exit;

define('INMOPRESS_PRINTABLES_PATH', plugin_dir_path(__FILE__));
define('INMOPRESS_PRINTABLES_URL', plugin_dir_url(__FILE__));
define('INMOPRESS_PRINTABLES_VERSION', '1.0.0');

class Inmopress_Printables
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies()
    {
        require_once INMOPRESS_PRINTABLES_PATH . 'includes/class-printables.php';
    }

    private function init_hooks()
    {
        add_action('init', array('Inmopress_Printables_Handler', 'init'));
    }
}

function inmopress_printables()
{
    return Inmopress_Printables::get_instance();
}
add_action('plugins_loaded', 'inmopress_printables');


