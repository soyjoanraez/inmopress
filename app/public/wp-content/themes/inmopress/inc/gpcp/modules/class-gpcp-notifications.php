<?php
/**
 * GPCP Notifications Module
 *
 * Notification system
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Notifications class
 */
class GPCP_Notifications
{
    /**
     * Instance of this class
     *
     * @var GPCP_Notifications
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Notifications
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'show_notifications'));
        add_action('wp_ajax_gpcp_dismiss_notification', array($this, 'dismiss_notification_ajax'));
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_notifications', 'gpcp_notifications_enabled');
        register_setting('gpcp_notifications', 'gpcp_notifications_email');
        register_setting('gpcp_notifications', 'gpcp_notifications_security');
        register_setting('gpcp_notifications', 'gpcp_notifications_updates');
    }

    /**
     * Add notification
     */
    public static function add_notification($type, $message, $dismissible = true, $priority = 'info')
    {
        $notifications = get_option('gpcp_notifications_list', array());
        
        $notifications[] = array(
            'id' => uniqid(),
            'type' => $type,
            'message' => $message,
            'dismissible' => $dismissible,
            'priority' => $priority,
            'timestamp' => current_time('mysql'),
        );

        // Keep only last 50 notifications
        if (count($notifications) > 50) {
            $notifications = array_slice($notifications, -50);
        }

        update_option('gpcp_notifications_list', $notifications);
    }

    /**
     * Show notifications
     */
    public function show_notifications()
    {
        if (!get_option('gpcp_notifications_enabled', true)) {
            return;
        }

        $notifications = get_option('gpcp_notifications_list', array());
        $dismissed = get_user_meta(get_current_user_id(), 'gpcp_dismissed_notifications', true);
        if (!is_array($dismissed)) {
            $dismissed = array();
        }

        // Check for system notifications
        $this->check_system_notifications();

        foreach ($notifications as $notification) {
            if (in_array($notification['id'], $dismissed)) {
                continue;
            }

            $class = 'notice notice-' . $notification['priority'];
            if ($notification['dismissible']) {
                $class .= ' is-dismissible';
            }

            echo '<div class="' . esc_attr($class) . '" data-notification-id="' . esc_attr($notification['id']) . '">';
            echo '<p>' . wp_kses_post($notification['message']) . '</p>';
            if ($notification['dismissible']) {
                echo '<button type="button" class="notice-dismiss gpcp-dismiss-notification" data-id="' . esc_attr($notification['id']) . '"><span class="screen-reader-text">' . __('Descartar', 'gpcp') . '</span></button>';
            }
            echo '</div>';
        }
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.gpcp-dismiss-notification').on('click', function() {
                var id = $(this).data('id');
                var $notice = $(this).closest('.notice');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gpcp_dismiss_notification',
                        id: id,
                        nonce: '<?php echo wp_create_nonce('gpcp_dismiss_notification'); ?>'
                    },
                    success: function() {
                        $notice.fadeOut();
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Check system notifications
     */
    private function check_system_notifications()
    {
        // Check for WordPress updates
        if (get_option('gpcp_notifications_updates', true)) {
            $update_count = 0;
            if (current_user_can('update_core')) {
                $core_updates = get_core_updates();
                if (!empty($core_updates) && !isset($core_updates[0]->response) || $core_updates[0]->response == 'upgrade') {
                    $update_count++;
                }
            }

            if ($update_count > 0) {
                self::add_notification('updates', __('Hay actualizaciones de WordPress disponibles.', 'gpcp'), true, 'warning');
            }
        }

        // Check for security issues
        if (get_option('gpcp_notifications_security', true)) {
            // Check if custom login URL is set but not being used
            $custom_login = get_option('gpcp_security_custom_login_url', '');
            if (!empty($custom_login)) {
                // This is just an example - you could add more security checks
            }
        }
    }

    /**
     * Dismiss notification via AJAX
     */
    public function dismiss_notification_ajax()
    {
        check_ajax_referer('gpcp_dismiss_notification', 'nonce');

        $id = sanitize_text_field($_POST['id']);
        $dismissed = get_user_meta(get_current_user_id(), 'gpcp_dismissed_notifications', true);
        
        if (!is_array($dismissed)) {
            $dismissed = array();
        }

        if (!in_array($id, $dismissed)) {
            $dismissed[] = $id;
            update_user_meta(get_current_user_id(), 'gpcp_dismissed_notifications', $dismissed);
        }

        wp_send_json_success();
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_notifications_save'])) {
            check_admin_referer('gpcp_notifications_save');
            
            update_option('gpcp_notifications_enabled', isset($_POST['gpcp_notifications_enabled']));
            update_option('gpcp_notifications_email', isset($_POST['gpcp_notifications_email']));
            update_option('gpcp_notifications_security', isset($_POST['gpcp_notifications_security']));
            update_option('gpcp_notifications_updates', isset($_POST['gpcp_notifications_updates']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        if (isset($_POST['gpcp_notifications_test'])) {
            check_admin_referer('gpcp_notifications_test');
            self::add_notification('test', __('Esta es una notificación de prueba.', 'gpcp'), true, 'info');
            echo '<div class="notice notice-success"><p>' . __('Notificación de prueba creada. Recarga la página para verla.', 'gpcp') . '</p></div>';
        }

        $enabled = get_option('gpcp_notifications_enabled', true);
        $email = get_option('gpcp_notifications_email', false);
        $security = get_option('gpcp_notifications_security', true);
        $updates = get_option('gpcp_notifications_updates', true);
        $notifications = get_option('gpcp_notifications_list', array());
        ?>
        <div class="wrap">
            <h1><?php _e('Sistema de Notificaciones', 'gpcp'); ?></h1>
            <p><?php _e('Gestiona las notificaciones del sistema y configura qué eventos quieres recibir.', 'gpcp'); ?></p>
            
            <form method="post" action="" style="margin-bottom: 20px;">
                <?php wp_nonce_field('gpcp_notifications_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Activar Notificaciones', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_notifications_enabled" value="1" <?php checked($enabled); ?> />
                                <?php _e('Mostrar notificaciones en el admin', 'gpcp'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Tipos de Notificaciones', 'gpcp'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="gpcp_notifications_email" value="1" <?php checked($email); ?> />
                                    <?php _e('Notificaciones por email', 'gpcp'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_notifications_security" value="1" <?php checked($security); ?> />
                                    <?php _e('Notificaciones de seguridad', 'gpcp'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="gpcp_notifications_updates" value="1" <?php checked($updates); ?> />
                                    <?php _e('Notificaciones de actualizaciones', 'gpcp'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_notifications_save'); ?>
            </form>

            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle"><?php _e('Centro de Notificaciones', 'gpcp'); ?></h2>
                <div class="inside">
                    <p>
                        <form method="post" style="display: inline;">
                            <?php wp_nonce_field('gpcp_notifications_test'); ?>
                            <button type="submit" name="gpcp_notifications_test" class="button">
                                <?php _e('Crear Notificación de Prueba', 'gpcp'); ?>
                            </button>
                        </form>
                    </p>
                    
                    <?php if (empty($notifications)): ?>
                        <p><?php _e('No hay notificaciones registradas.', 'gpcp'); ?></p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 15%;"><?php _e('Fecha', 'gpcp'); ?></th>
                                    <th style="width: 10%;"><?php _e('Tipo', 'gpcp'); ?></th>
                                    <th style="width: 60%;"><?php _e('Mensaje', 'gpcp'); ?></th>
                                    <th style="width: 15%;"><?php _e('Prioridad', 'gpcp'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice(array_reverse($notifications), 0, 20) as $notification): ?>
                                    <tr>
                                        <td><?php echo esc_html($notification['timestamp']); ?></td>
                                        <td><?php echo esc_html(ucfirst($notification['type'])); ?></td>
                                        <td><?php echo wp_kses_post($notification['message']); ?></td>
                                        <td>
                                            <span class="dashicons dashicons-<?php echo $notification['priority'] === 'error' ? 'warning' : ($notification['priority'] === 'warning' ? 'flag' : 'info'); ?>"></span>
                                            <?php echo esc_html(ucfirst($notification['priority'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}



