<?php
/**
 * GeneratePress Child Pro Functions
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants.
define('GPCP_VERSION', '2.0.0');
define('GPCP_THEME_DIR', get_stylesheet_directory());
define('GPCP_THEME_URI', get_stylesheet_directory_uri());

/**
 * Load the theme loader
 */
require_once GPCP_THEME_DIR . '/inc/class-gpcp-loader.php';

/**
 * Initialize the theme
 */
function gpcp_init() {
    GPCP_Loader::get_instance();
}
add_action('after_setup_theme', 'gpcp_init');

/**
 * Enqueue theme styles
 */
function gpcp_scripts() {
    wp_enqueue_style('gpcp-style', get_stylesheet_uri(), array('generate-style'), GPCP_VERSION);
}
add_action('wp_enqueue_scripts', 'gpcp_scripts');



