<?php
/**
 * GPCP Logs Module
 *
 * Activity and error logging
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Logs class
 */
class GPCP_Logs
{
    /**
     * Instance of this class
     *
     * @var GPCP_Logs
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Logs
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
        
        if (get_option('gpcp_logs_enabled', true)) {
            add_action('wp_login', array($this, 'log_login'), 10, 2);
            add_action('wp_logout', array($this, 'log_logout'));
            add_action('save_post', array($this, 'log_post_change'), 10, 3);
        }
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_logs', 'gpcp_logs_enabled');
        register_setting('gpcp_logs', 'gpcp_logs_max_entries');
    }

    /**
     * Log activity
     */
    private function log_activity($type, $message, $data = array())
    {
        $logs = get_option('gpcp_logs_entries', array());
        $max_entries = get_option('gpcp_logs_max_entries', 1000);

        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'type' => $type,
            'message' => $message,
            'user_id' => get_current_user_id(),
            'ip' => $this->get_user_ip(),
            'data' => $data,
        );

        array_unshift($logs, $log_entry);

        // Limit entries
        if (count($logs) > $max_entries) {
            $logs = array_slice($logs, 0, $max_entries);
        }

        update_option('gpcp_logs_entries', $logs);
    }

    /**
     * Log login
     */
    public function log_login($user_login, $user)
    {
        $this->log_activity('login', sprintf(__('Usuario %s ha iniciado sesión', 'gpcp'), $user_login), array(
            'user_id' => $user->ID,
            'user_login' => $user_login,
        ));
    }

    /**
     * Log logout
     */
    public function log_logout()
    {
        $user = wp_get_current_user();
        if ($user->ID) {
            $this->log_activity('logout', sprintf(__('Usuario %s ha cerrado sesión', 'gpcp'), $user->user_login), array(
                'user_id' => $user->ID,
            ));
        }
    }

    /**
     * Log post change
     */
    public function log_post_change($post_id, $post, $update)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $action = $update ? 'updated' : 'created';
        $this->log_activity('post_' . $action, sprintf(__('Post "%s" %s', 'gpcp'), $post->post_title, $update ? __('actualizado', 'gpcp') : __('creado', 'gpcp')), array(
            'post_id' => $post_id,
            'post_type' => $post->post_type,
        ));
    }

    /**
     * Get user IP
     */
    private function get_user_ip()
    {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_logs_clear'])) {
            check_admin_referer('gpcp_logs_clear');
            update_option('gpcp_logs_entries', array());
            echo '<div class="notice notice-success"><p>' . __('Logs eliminados.', 'gpcp') . '</p></div>';
        }

        if (isset($_POST['gpcp_logs_save'])) {
            check_admin_referer('gpcp_logs_save');
            
            update_option('gpcp_logs_enabled', isset($_POST['gpcp_logs_enabled']));
            update_option('gpcp_logs_max_entries', intval($_POST['gpcp_logs_max_entries']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $enabled = get_option('gpcp_logs_enabled', true);
        $max_entries = get_option('gpcp_logs_max_entries', 1000);
        $logs = get_option('gpcp_logs_entries', array());
        $filter_type = isset($_GET['filter_type']) ? sanitize_text_field($_GET['filter_type']) : 'all';
        
        // Filter logs
        if ($filter_type !== 'all') {
            $logs = array_filter($logs, function($log) use ($filter_type) {
                return isset($log['type']) && $log['type'] === $filter_type;
            });
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Logs del Sistema', 'gpcp'); ?></h1>
            <p><?php _e('Registro de actividad y eventos del sistema.', 'gpcp'); ?></p>
            
            <form method="post" action="" style="margin-bottom: 20px;">
                <?php wp_nonce_field('gpcp_logs_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Activar Logs', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_logs_enabled" value="1" <?php checked($enabled); ?> />
                                <?php _e('Registrar actividad del sistema', 'gpcp'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_logs_max_entries"><?php _e('Máximo de Entradas', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="gpcp_logs_max_entries" name="gpcp_logs_max_entries" value="<?php echo esc_attr($max_entries); ?>" class="small-text" />
                            <p class="description"><?php _e('Número máximo de entradas de log a mantener.', 'gpcp'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_logs_save'); ?>
            </form>

            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle">
                    <?php _e('Registro de Actividad', 'gpcp'); ?>
                    <span style="float: right;">
                        <form method="get" style="display: inline;">
                            <input type="hidden" name="page" value="gpcp-logs" />
                            <select name="filter_type" onchange="this.form.submit()">
                                <option value="all" <?php selected($filter_type, 'all'); ?>><?php _e('Todos', 'gpcp'); ?></option>
                                <option value="login" <?php selected($filter_type, 'login'); ?>><?php _e('Login', 'gpcp'); ?></option>
                                <option value="logout" <?php selected($filter_type, 'logout'); ?>><?php _e('Logout', 'gpcp'); ?></option>
                                <option value="post_created" <?php selected($filter_type, 'post_created'); ?>><?php _e('Posts Creados', 'gpcp'); ?></option>
                                <option value="post_updated" <?php selected($filter_type, 'post_updated'); ?>><?php _e('Posts Actualizados', 'gpcp'); ?></option>
                            </select>
                        </form>
                        <form method="post" style="display: inline; margin-left: 10px;">
                            <?php wp_nonce_field('gpcp_logs_clear'); ?>
                            <button type="submit" name="gpcp_logs_clear" class="button" onclick="return confirm('<?php _e('¿Estás seguro de eliminar todos los logs?', 'gpcp'); ?>');">
                                <?php _e('Limpiar Logs', 'gpcp'); ?>
                            </button>
                        </form>
                    </span>
                </h2>
                <div class="inside">
                    <?php if (empty($logs)): ?>
                        <p><?php _e('No hay logs registrados.', 'gpcp'); ?></p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 15%;"><?php _e('Fecha/Hora', 'gpcp'); ?></th>
                                    <th style="width: 10%;"><?php _e('Tipo', 'gpcp'); ?></th>
                                    <th style="width: 40%;"><?php _e('Mensaje', 'gpcp'); ?></th>
                                    <th style="width: 15%;"><?php _e('Usuario', 'gpcp'); ?></th>
                                    <th style="width: 20%;"><?php _e('IP', 'gpcp'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($logs, 0, 100) as $log): ?>
                                    <tr>
                                        <td><?php echo esc_html($log['timestamp']); ?></td>
                                        <td>
                                            <span class="dashicons dashicons-<?php echo $this->get_log_icon($log['type']); ?>"></span>
                                            <?php echo esc_html(ucfirst($log['type'])); ?>
                                        </td>
                                        <td><?php echo esc_html($log['message']); ?></td>
                                        <td>
                                            <?php
                                            if (isset($log['user_id']) && $log['user_id']) {
                                                $user = get_userdata($log['user_id']);
                                                echo $user ? esc_html($user->display_name) : __('Desconocido', 'gpcp');
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><code><?php echo esc_html($log['ip']); ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (count($logs) > 100): ?>
                            <p><?php printf(__('Mostrando 100 de %d entradas. Usa los filtros para ver más.', 'gpcp'), count($logs)); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get log icon
     */
    private function get_log_icon($type)
    {
        $icons = array(
            'login' => 'admin-users',
            'logout' => 'exit',
            'post_created' => 'plus-alt',
            'post_updated' => 'edit',
        );
        return isset($icons[$type]) ? $icons[$type] : 'info';
    }
}



