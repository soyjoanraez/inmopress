<?php
/**
 * Dashboard View
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap gpcp-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="gpcp-dashboard-grid">
        <div class="gpcp-dashboard-card">
            <h2><?php _e('Seguridad', 'gpcp'); ?></h2>
            <p><?php _e('Protege tu sitio con URL de login personalizada, límite de intentos y más.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-security'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('SEO', 'gpcp'); ?></h2>
            <p><?php _e('Auto-completado de metadatos SEO y gestor centralizado de SEO.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-seo'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Optimización', 'gpcp'); ?></h2>
            <p><?php _e('12 optimizaciones para mejorar la velocidad y rendimiento del sitio.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-optimization'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Imágenes', 'gpcp'); ?></h2>
            <p><?php _e('Conversión automática a WebP y gestión inteligente de imágenes.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-images'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Branding', 'gpcp'); ?></h2>
            <p><?php _e('Personaliza el panel de WordPress y la página de login con tu marca.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-branding'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Exportar/Importar', 'gpcp'); ?></h2>
            <p><?php _e('Exporta e importa todas las configuraciones del tema en un clic.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-export-import'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Mantenimiento', 'gpcp'); ?></h2>
            <p><?php _e('Página profesional de mantenimiento con cuenta regresiva y personalización.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-maintenance'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Gestor SEO', 'gpcp'); ?></h2>
            <p><?php _e('Gestiona el SEO de todos tus posts desde una tabla centralizada.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-seo-manager'); ?>" class="button button-primary"><?php _e('Abrir', 'gpcp'); ?></a>
        </div>
    </div>
</div>



