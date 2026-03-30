<?php
/**
 * Favorites Dashboard Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();
$user = wp_get_current_user();

// TODO: Implement favorites functionality
// For now, show a placeholder
?>

<div class="dashboard-section-header">
    <div class="section-header-left">
        <h3>Mis Favoritos</h3>
        <p class="section-description">Inmuebles que has guardado como favoritos</p>
    </div>
</div>

<div class="dashboard-empty-state">
    <div class="empty-state-icon">
        <span class="dashicons dashicons-heart" style="font-size: 64px; color: #adb5bd;"></span>
    </div>
    <h3>No tienes favoritos aún</h3>
    <p>Explora nuestros inmuebles y guarda tus favoritos para acceder fácilmente más tarde.</p>
    <a href="<?php echo esc_url(home_url()); ?>" class="btn btn-primary">
        <span class="dashicons dashicons-search"></span>
        Explorar Inmuebles
    </a>
</div>

