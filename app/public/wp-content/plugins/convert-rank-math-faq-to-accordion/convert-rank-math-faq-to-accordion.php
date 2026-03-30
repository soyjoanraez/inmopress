<?php
/**
* Plugin Name:          Convert Rank Math FAQ to Accordion
* Plugin URI:           https://inbdigital.com/documentation/convert-rank-math-faq-to-accordion-plugin-docs/
* Description:          Transforms Rank Math FAQ blocks into an interactive accordion. Easily customize colors, fonts, and styles in RankMath FAQ Blocks..
* Version:              1.0.6
* Requires at least:    5.2
* Requires PHP:         7.4
* Author:               INB Digital
* Author URI:           https://inbdigital.com/
* Requires Plugins:     seo-by-rank-math
* License:              GPLv2 or later
* License URI:          http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define the plugin constants
define( 'INBRMFA_CURRENT_VERSION', '1.0.6' );
define( 'INBRMFA_PLUGIN_DIR', plugin_dir_path(__FILE__) );
define( 'INBRMFA_PLUGIN_URL', plugin_dir_url(__FILE__) );

// Includes the plugin dependency files
require_once INBRMFA_PLUGIN_DIR . 'includes/inbrmfa-admin.php';
require_once INBRMFA_PLUGIN_DIR . 'includes/inbrmfa-frontend.php';

// Redirect to plugin's settings page after activation the plugin
function INBRMFA_plugin_activation() {
    add_option( 'INBRMFA_plugin_do_activation_redirect', true );
}
register_activation_hook( __FILE__, 'INBRMFA_plugin_activation' );

function INBRMFA_plugin_redirect() {
    if(get_option( 'INBRMFA_plugin_do_activation_redirect', false) ) {
        delete_option('INBRMFA_plugin_do_activation_redirect');
        wp_safe_redirect( admin_url( 'options-general.php?page=inb-rmfa-settings' ) );
        exit;
    }
}
add_action( 'admin_init', 'INBRMFA_plugin_redirect' );

// Settings link on plugin page
function INBRMFA_plugin_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=inb-rmfa-settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'INBRMFA_plugin_settings_link' );

// Activate plugin
function INBRMFA_activate_plugin() {
    if (get_option('INBRMFA_options') === false) {
        $default_options = INBRMFA_get_default_options();
        add_option('INBRMFA_options', $default_options);
    }
}
register_activation_hook(__FILE__, 'INBRMFA_activate_plugin');
