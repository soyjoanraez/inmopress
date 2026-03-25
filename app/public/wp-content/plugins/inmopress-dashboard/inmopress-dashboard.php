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
