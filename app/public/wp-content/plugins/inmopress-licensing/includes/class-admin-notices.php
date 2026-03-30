<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Notices - Muestra avisos sobre el estado de la licencia
 */
class Inmopress_Admin_Notices
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_notices', array($this, 'show_license_notices'));
    }

    /**
     * Mostrar avisos de licencia
     */
    public function show_license_notices()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $license_manager = Inmopress_License_Manager::get_instance();
        $license_data = $license_manager->get_license_data();

        // Sin licencia
        if ($license_data['status'] === Inmopress_License_Manager::STATUS_INACTIVE) {
            ?>
            <div class="notice notice-warning">
                <p><strong>Inmopress:</strong> No hay licencia activa. <a href="<?php echo admin_url('admin.php?page=inmopress-license'); ?>">Activa tu licencia</a> para usar todas las funcionalidades.</p>
            </div>
            <?php
            return;
        }

        // Licencia expirada
        if ($license_data['status'] === Inmopress_License_Manager::STATUS_EXPIRED) {
            ?>
            <div class="notice notice-error">
                <p><strong>Inmopress:</strong> Tu licencia ha expirado. <a href="<?php echo admin_url('admin.php?page=inmopress-license'); ?>">Renueva tu suscripción</a> para continuar usando el sistema.</p>
            </div>
            <?php
            return;
        }

        // Licencia suspendida
        if ($license_data['status'] === Inmopress_License_Manager::STATUS_SUSPENDED) {
            ?>
            <div class="notice notice-error">
                <p><strong>Inmopress:</strong> Tu licencia está suspendida. Contacta con soporte para resolver el problema.</p>
            </div>
            <?php
            return;
        }

        // Licencia en periodo de gracia
        if ($license_data['status'] === Inmopress_License_Manager::STATUS_GRACE) {
            ?>
            <div class="notice notice-warning">
                <p><strong>Inmopress:</strong> Estás en periodo de gracia. Renueva tu suscripción pronto para evitar la desactivación.</p>
            </div>
            <?php
            return;
        }

        // Aviso de expiración próxima
        if (!empty($license_data['expires_at'])) {
            $expires = strtotime($license_data['expires_at']);
            $days_until_expiry = ($expires - current_time('timestamp')) / DAY_IN_SECONDS;

            if ($days_until_expiry > 0 && $days_until_expiry <= 7) {
                ?>
                <div class="notice notice-info">
                    <p><strong>Inmopress:</strong> Tu licencia expira en <?php echo round($days_until_expiry); ?> días. <a href="<?php echo admin_url('admin.php?page=inmopress-license'); ?>">Renueva ahora</a></p>
                </div>
                <?php
            }
        }
    }
}
