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

        <div class="gpcp-dashboard-card">
            <h2><?php _e('SMTP', 'gpcp'); ?></h2>
            <p><?php _e('Configura el envío de emails a través de un servidor SMTP personalizado.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-smtp'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Redirecciones', 'gpcp'); ?></h2>
            <p><?php _e('Gestiona las redirecciones 301 de tu sitio con contador de clics.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-redirects'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Schema Markup', 'gpcp'); ?></h2>
            <p><?php _e('Schema.org JSON-LD automático para mejorar el SEO estructurado.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-schema'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Analytics', 'gpcp'); ?></h2>
            <p><?php _e('Estadísticas de visitas, posts más visitados y gráficos de tráfico.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-analytics'); ?>" class="button button-primary"><?php _e('Ver', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Logs', 'gpcp'); ?></h2>
            <p><?php _e('Registro de actividad del sistema, logins, cambios en posts y más.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-logs'); ?>" class="button button-primary"><?php _e('Ver', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Cache', 'gpcp'); ?></h2>
            <p><?php _e('Gestiona y limpia el cache de WordPress y plugins de cache.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-cache'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Base de Datos', 'gpcp'); ?></h2>
            <p><?php _e('Limpia y optimiza tu base de datos para mejorar el rendimiento.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-database'); ?>" class="button button-primary"><?php _e('Optimizar', 'gpcp'); ?></a>
        </div>

        <div class="gpcp-dashboard-card">
            <h2><?php _e('Notificaciones', 'gpcp'); ?></h2>
            <p><?php _e('Sistema de notificaciones en el admin y centro de notificaciones.', 'gpcp'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=gpcp-notifications'); ?>" class="button button-primary"><?php _e('Configurar', 'gpcp'); ?></a>
        </div>
    </div>
</div>

