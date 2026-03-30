<?php
/**
 * WP Total Optimizer - Uninstall
 * Limpia todas las opciones y tablas cuando se elimina el plugin
 */

// Si no se llama desde WordPress, salir
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Solo limpiar si el usuario tiene permisos
if (!current_user_can('activate_plugins')) {
    return;
}

global $wpdb;

// Eliminar todas las opciones del plugin
$options_to_delete = array(
    'wpto_security_options',
    'wpto_optimization_options',
    'wpto_images_options',
    'wpto_seo_options',
    'wpto_notifications_options',
    'wpto_monitoring_db_version',
    'wpto_redirects_db_version',
);

foreach ($options_to_delete as $option) {
    delete_option($option);
}

// Eliminar opciones con prefijo wpto_ (por si hay otras)
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpto_%'");

// Eliminar tablas creadas por el plugin
$tables_to_drop = array(
    $wpdb->prefix . 'wpto_activity_log',
    $wpdb->prefix . 'wpto_health_checks',
    $wpdb->prefix . 'wpto_redirects',
);

foreach ($tables_to_drop as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

// Eliminar user meta del plugin
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'wpto_%'");

// Eliminar post meta del plugin (campos SEO)
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_wpto_%'");

// Limpiar tareas programadas
wp_clear_scheduled_hook('wpto_daily_health_check');
wp_clear_scheduled_hook('wpto_weekly_summary');
wp_clear_scheduled_hook('wpto_weekly_pagespeed_check');
wp_clear_scheduled_hook('wpto_db_cleanup');
wp_clear_scheduled_hook('wpto_file_monitor_scan');

// Limpiar transients
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpto_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wpto_%'");

// Flush rewrite rules para limpiar reglas del sitemap
flush_rewrite_rules();
