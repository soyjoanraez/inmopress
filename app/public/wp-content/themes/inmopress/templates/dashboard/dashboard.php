<?php
/**
 * Dashboard Home Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();
$stats = $dashboard->get_dashboard_stats();
$user = wp_get_current_user();
$user_role = $user->roles[0] ?? 'cliente';
?>

<div class="dashboard-stats">
    <?php if (in_array($user_role, array('administrator', 'agency', 'agent', 'trabajador'), true)) : ?>
        <div class="stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-building"></span>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo esc_html($stats['properties'] ?? 0); ?></h3>
                <p class="stat-label">Inmuebles</p>
            </div>
            <a href="<?php echo esc_url($dashboard->get_dashboard_url('properties')); ?>" class="stat-link">
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo esc_html($stats['clients'] ?? 0); ?></h3>
                <p class="stat-label">Clientes</p>
            </div>
            <a href="<?php echo esc_url($dashboard->get_dashboard_url('clients')); ?>" class="stat-link">
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-email-alt"></span>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo esc_html($stats['leads'] ?? 0); ?></h3>
                <p class="stat-label">Leads</p>
            </div>
            <a href="<?php echo esc_url($dashboard->get_dashboard_url('leads')); ?>" class="stat-link">
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-calendar"></span>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo esc_html($stats['visits'] ?? 0); ?></h3>
                <p class="stat-label">Visitas</p>
            </div>
            <a href="<?php echo esc_url($dashboard->get_dashboard_url('visits')); ?>" class="stat-link">
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </a>
        </div>
    <?php else : ?>
        <div class="stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-heart"></span>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo esc_html($stats['favorites'] ?? 0); ?></h3>
                <p class="stat-label">Favoritos</p>
            </div>
            <a href="<?php echo esc_url($dashboard->get_dashboard_url('favorites')); ?>" class="stat-link">
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-welcome">
    <h2>Bienvenido, <?php echo esc_html($user->display_name); ?></h2>
    <p>Gestiona tus inmuebles y clientes desde este panel de control.</p>
</div>

