<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InmoPress_Dashboard_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

	public function add_menu_page() {
		add_menu_page(
			__( 'InmoPress Dashboard', 'inmopress-dashboard' ),
			__( 'Dashboard', 'inmopress-dashboard' ),
			'manage_options',
			'inmopress-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-chart-pie',
			2
		);
	}

    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_inmopress-dashboard' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'inmopress-dashboard-app', INMOPRESS_DASHBOARD_URL . 'assets/js/dashboard-app.js', array(), INMOPRESS_DASHBOARD_VERSION, true );
        wp_localize_script( 'inmopress-dashboard-app', 'inmoPressDashboard', array(
            'rest_url' => esc_url_raw( rest_url() ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
        ) );
    }

	public function render_dashboard_page() {
		require_once INMOPRESS_DASHBOARD_DIR . 'templates/dashboard-app.php';
	}
}

new InmoPress_Dashboard_Admin_Menu();
