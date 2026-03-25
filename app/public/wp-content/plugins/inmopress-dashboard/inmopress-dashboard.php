<?php
/**
 * Plugin Name: InmoPress Dashboard
 * Description: Panel de control personalizado y CRM para InmoPress (Web App / SPA).
 * Version: 1.0.0
 * Author: Jimmy
 * Text Domain: inmopress-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'INMOPRESS_DASHBOARD_VERSION', '1.0.0' );
define( 'INMOPRESS_DASHBOARD_DIR', plugin_dir_path( __FILE__ ) );
define( 'INMOPRESS_DASHBOARD_URL', plugin_dir_url( __FILE__ ) );

final class InmoPress_Dashboard {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->includes();
	}

	private function includes() {
		require_once INMOPRESS_DASHBOARD_DIR . 'includes/class-admin-menu.php';
	}
}

function InmoPress_Dashboard_Init() {
    return InmoPress_Dashboard::instance();
}

add_action( 'plugins_loaded', 'InmoPress_Dashboard_Init' );

// Expose property metadata to REST API
add_action( 'rest_api_init', 'inmopress_dashboard_register_rest_fields' );
function inmopress_dashboard_register_rest_fields() {
    register_rest_field( 'impress_property', 'ip_meta', array(
        'get_callback'    => 'inmopress_dashboard_get_property_meta',
        'update_callback' => null,
        'schema'          => null,
    ) );
}

function inmopress_dashboard_get_property_meta( $object, $field_name, $request ) {
    $meta = get_post_meta( $object['id'] );
    $data = array();
    $useful_hidden_keys = array('_precio', '_habitaciones', '_banos', '_precio_venta', '_precio_alquiler', '_superficie_construida');
    
    foreach ( $meta as $key => $value ) {
        if ( strpos( $key, '_' ) !== 0 ) {
            $data[$key] = $value[0];
        } elseif ( in_array( $key, $useful_hidden_keys ) ) {
            $data[ltrim($key, '_')] = $value[0];
        }
    }
    return $data;
}
