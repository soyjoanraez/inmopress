<?php
/**
 * Plugin Name: Inmopress Price Alerts
 * Description: Automatic price drop alerts for properties.
 * Version: 1.0.0
 * Author: Inmopress
 */

if (!defined('ABSPATH')) {
    exit;
}

define('INMOPRESS_PRICE_ALERTS_PATH', plugin_dir_path(__FILE__));
define('INMOPRESS_PRICE_ALERTS_VERSION', '1.0.0');

class Inmopress_Price_Alerts
{
    public static function init()
    {
        self::load_dependencies();

        add_action('init', array('Inmopress_Price_Tracker', 'init'));
        add_action('init', array('Inmopress_Price_Alert_Sender', 'init'));
        add_action('init', array('Inmopress_Price_Alert_Matcher', 'init'));
        add_action('init', array('Inmopress_Price_Alert_Logger', 'init'));
        add_action('init', array('Inmopress_Price_Alerts_ACF', 'init'));
    }

    private static function load_dependencies()
    {
        require_once INMOPRESS_PRICE_ALERTS_PATH . 'includes/class-acf-fields.php';
        require_once INMOPRESS_PRICE_ALERTS_PATH . 'includes/class-price-tracker.php';
        require_once INMOPRESS_PRICE_ALERTS_PATH . 'includes/class-alert-matcher.php';
        require_once INMOPRESS_PRICE_ALERTS_PATH . 'includes/class-alert-logger.php';
        require_once INMOPRESS_PRICE_ALERTS_PATH . 'includes/class-alert-sender.php';
    }

    public static function activate()
    {
        if (class_exists('Inmopress_Price_Alert_Logger')) {
            Inmopress_Price_Alert_Logger::create_table();
        }
    }

    public static function deactivate()
    {
        wp_clear_scheduled_hook('inmopress_price_alerts_send');
    }
}

register_activation_hook(__FILE__, array('Inmopress_Price_Alerts', 'activate'));
register_deactivation_hook(__FILE__, array('Inmopress_Price_Alerts', 'deactivate'));

Inmopress_Price_Alerts::init();
