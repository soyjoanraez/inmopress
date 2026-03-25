<?php
/**
 * Dashboard Header Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();
$menu_items = $dashboard->get_menu_items();
$current_section = get_query_var('inmopress_section') ?: 'dashboard';
$user = wp_get_current_user();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html($menu_items[$current_section]['label'] ?? 'Dashboard'); ?> - Panel de Control | <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('inmopress-dashboard'); ?>>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <div class="dashboard-sidebar-header">
                <h1 class="dashboard-logo">
                    <a href="<?php echo esc_url($dashboard->get_dashboard_url()); ?>">
                        <?php bloginfo('name'); ?>
                    </a>
                </h1>
            </div>
            
            <nav class="dashboard-nav">
                <ul class="dashboard-menu">
                    <?php foreach ($menu_items as $key => $item) : ?>
                        <li class="dashboard-menu-item <?php echo $current_section === $key ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url($dashboard->get_dashboard_url($key)); ?>" class="dashboard-menu-link">
                                <span class="dashicons <?php echo esc_attr($item['icon']); ?>"></span>
                                <span class="menu-label"><?php echo esc_html($item['label']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Top Bar -->
            <header class="dashboard-header">
                <div class="dashboard-header-left">
                    <button class="dashboard-menu-toggle" aria-label="Toggle menu">
                        <span class="dashicons dashicons-menu"></span>
                    </button>
                    <h2 class="dashboard-page-title">
                        <?php echo esc_html($menu_items[$current_section]['label'] ?? 'Dashboard'); ?>
                    </h2>
                </div>
                <div class="dashboard-header-right">
                    <div class="dashboard-user-menu">
                        <span class="dashboard-user-name">
                            <?php echo get_avatar($user->ID, 32, '', '', array('class' => 'user-avatar-small')); ?>
                            <?php echo esc_html($user->display_name); ?>
                            <span class="dashicons dashicons-arrow-down-alt2" style="font-size: 14px;"></span>
                        </span>
                        <div class="dashboard-user-dropdown">
                            <a href="<?php echo esc_url($dashboard->get_dashboard_url('profile')); ?>">
                                <span class="dashicons dashicons-admin-users"></span>
                                Mi Perfil
                            </a>
                            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">
                                <span class="dashicons dashicons-migrate"></span>
                                Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="dashboard-content">

