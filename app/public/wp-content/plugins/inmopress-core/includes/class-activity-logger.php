<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Activity Logger - Sistema de registro de actividad para auditoría
 */
class Inmopress_Activity_Logger
{
    private static $instance = null;

    // Tipos de acciones registradas
    private $action_types = array(
        'property_created',
        'property_updated',
        'property_status_changed',
        'property_deleted',
        'client_created',
        'client_updated',
        'lead_created',
        'lead_converted',
        'event_created',
        'event_completed',
        'email_sent',
        'email_received',
        'automation_triggered',
        'ai_generation',
        'user_login',
        'settings_updated',
        'matching_calculated',
    );

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init_hooks();
    }

    private function init_hooks()
    {
        // Property hooks
        add_action('wp_insert_post', array($this, 'log_property_created'), 10, 3);
        add_action('post_updated', array($this, 'log_property_updated'), 10, 3);
        add_action('before_delete_post', array($this, 'log_property_deleted'), 10, 1);
        add_action('acf/save_post', array($this, 'log_property_status_change'), 20);

        // Client hooks
        add_action('wp_insert_post', array($this, 'log_client_created'), 10, 3);
        add_action('post_updated', array($this, 'log_client_updated'), 10, 3);

        // Lead hooks
        add_action('wp_insert_post', array($this, 'log_lead_created'), 10, 3);

        // Event hooks
        add_action('wp_insert_post', array($this, 'log_event_created'), 10, 3);
        add_action('acf/save_post', array($this, 'log_event_completed'), 30);

        // Email hooks (cuando se implemente)
        add_action('inmopress_email_sent', array($this, 'log_email_sent'), 10, 1);
        add_action('inmopress_email_received', array($this, 'log_email_received'), 10, 1);

        // Automation hooks
        add_action('inmopress_automation_executed', array($this, 'log_automation_triggered'), 10, 1);

        // AI hooks
        add_action('inmopress_ai_generation', array($this, 'log_ai_generation'), 10, 1);

        // User login
        add_action('wp_login', array($this, 'log_user_login'), 10, 2);

        // Matching hooks
        add_action('inmopress_match_found', array($this, 'log_matching_calculated'), 10, 1);

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    /**
     * Registrar actividad
     */
    public function log($action, $object_type, $object_id, $data = array())
    {
        global $wpdb;

        if (!in_array($action, $this->action_types)) {
            return false;
        }

        $ip_address = $this->get_client_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '';

        $result = $wpdb->insert(
            $wpdb->prefix . 'inmopress_activity_log',
            array(
                'user_id' => get_current_user_id(),
                'action' => $action,
                'object_type' => $object_type,
                'object_id' => $object_id,
                'data' => !empty($data) ? json_encode($data) : null,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
        );

        return $result !== false;
    }

    /**
     * Obtener IP del cliente
     */
    private function get_client_ip()
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
     * Log: Property Created
     */
    public function log_property_created($post_id, $post, $update)
    {
        if ($update || $post->post_type !== 'impress_property') {
            return;
        }

        $this->log('property_created', 'impress_property', $post_id, array(
            'title' => $post->post_title,
        ));
    }

    /**
     * Log: Property Updated
     */
    public function log_property_updated($post_id, $post_after, $post_before)
    {
        if ($post_after->post_type !== 'impress_property') {
            return;
        }

        $changes = array();
        if ($post_after->post_title !== $post_before->post_title) {
            $changes['title'] = array('old' => $post_before->post_title, 'new' => $post_after->post_title);
        }
        if ($post_after->post_status !== $post_before->post_status) {
            $changes['status'] = array('old' => $post_before->post_status, 'new' => $post_after->post_status);
        }

        if (!empty($changes)) {
            $this->log('property_updated', 'impress_property', $post_id, $changes);
        }
    }

    /**
     * Log: Property Deleted
     */
    public function log_property_deleted($post_id)
    {
        $post = get_post($post_id);
        if ($post && $post->post_type === 'impress_property') {
            $this->log('property_deleted', 'impress_property', $post_id, array(
                'title' => $post->post_title,
            ));
        }
    }

    /**
     * Log: Property Status Change
     */
    public function log_property_status_change($post_id)
    {
        if (get_post_type($post_id) !== 'impress_property') {
            return;
        }

        $old_status = get_post_meta($post_id, '_inmopress_old_status', true);
        $new_status = get_field('impress_status', $post_id);

        if ($old_status && $old_status !== $new_status) {
            $this->log('property_status_changed', 'impress_property', $post_id, array(
                'old_status' => $old_status,
                'new_status' => $new_status,
            ));
        }
    }

    /**
     * Log: Client Created
     */
    public function log_client_created($post_id, $post, $update)
    {
        if ($update || $post->post_type !== 'impress_client') {
            return;
        }

        $this->log('client_created', 'impress_client', $post_id, array(
            'title' => $post->post_title,
        ));
    }

    /**
     * Log: Client Updated
     */
    public function log_client_updated($post_id, $post_after, $post_before)
    {
        if ($post_after->post_type !== 'impress_client') {
            return;
        }

        $this->log('client_updated', 'impress_client', $post_id);
    }

    /**
     * Log: Lead Created
     */
    public function log_lead_created($post_id, $post, $update)
    {
        if ($update || $post->post_type !== 'impress_lead') {
            return;
        }

        $source = get_field('lead_source', $post_id);
        $this->log('lead_created', 'impress_lead', $post_id, array(
            'source' => $source,
        ));
    }

    /**
     * Log: Event Created
     */
    public function log_event_created($post_id, $post, $update)
    {
        if ($update || $post->post_type !== 'impress_event') {
            return;
        }

        $this->log('event_created', 'impress_event', $post_id);
    }

    /**
     * Log: Event Completed
     */
    public function log_event_completed($post_id)
    {
        if (get_post_type($post_id) !== 'impress_event') {
            return;
        }

        $status = get_field('impress_event_status', $post_id);
        $old_status = get_post_meta($post_id, '_inmopress_event_old_status', true);

        if ($status === 'completada' && $old_status !== 'completada') {
            $this->log('event_completed', 'impress_event', $post_id);
        }
    }

    /**
     * Log: Email Sent
     */
    public function log_email_sent($email_data)
    {
        $this->log('email_sent', 'impress_message', isset($email_data['email_id']) ? $email_data['email_id'] : 0, $email_data);
    }

    /**
     * Log: Email Received
     */
    public function log_email_received($email_data)
    {
        $this->log('email_received', 'impress_message', isset($email_data['email_id']) ? $email_data['email_id'] : 0, $email_data);
    }

    /**
     * Log: Automation Triggered
     */
    public function log_automation_triggered($automation_data)
    {
        $this->log('automation_triggered', 'automation', isset($automation_data['automation_id']) ? $automation_data['automation_id'] : 0, $automation_data);
    }

    /**
     * Log: AI Generation
     */
    public function log_ai_generation($generation_data)
    {
        $this->log('ai_generation', isset($generation_data['object_type']) ? $generation_data['object_type'] : 'impress_property', isset($generation_data['object_id']) ? $generation_data['object_id'] : 0, $generation_data);
    }

    /**
     * Log: User Login
     */
    public function log_user_login($user_login, $user)
    {
        $this->log('user_login', 'user', $user->ID, array(
            'username' => $user_login,
        ));
    }

    /**
     * Log: Matching Calculated
     */
    public function log_matching_calculated($matching_data)
    {
        $this->log('matching_calculated', 'matching', isset($matching_data['property_id']) ? $matching_data['property_id'] : 0, $matching_data);
    }

    /**
     * Añadir menú admin
     */
    public function add_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=impress_property',
            'Activity Log',
            'Activity Log',
            'manage_options',
            'inmopress-activity-log',
            array($this, 'render_activity_log_page')
        );
    }

    /**
     * Renderizar página de Activity Log
     */
    public function render_activity_log_page()
    {
        global $wpdb;

        // Filtros
        $action_filter = isset($_GET['action_filter']) ? sanitize_text_field($_GET['action_filter']) : '';
        $object_type_filter = isset($_GET['object_type_filter']) ? sanitize_text_field($_GET['object_type_filter']) : '';
        $user_filter = isset($_GET['user_filter']) ? intval($_GET['user_filter']) : 0;
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 50;

        // Construir query
        $where = array('1=1');
        $where_values = array();

        if ($action_filter) {
            $where[] = 'action = %s';
            $where_values[] = $action_filter;
        }

        if ($object_type_filter) {
            $where[] = 'object_type = %s';
            $where_values[] = $object_type_filter;
        }

        if ($user_filter) {
            $where[] = 'user_id = %d';
            $where_values[] = $user_filter;
        }

        if ($date_from) {
            $where[] = 'created_at >= %s';
            $where_values[] = $date_from . ' 00:00:00';
        }

        if ($date_to) {
            $where[] = 'created_at <= %s';
            $where_values[] = $date_to . ' 23:59:59';
        }

        $where_clause = implode(' AND ', $where);
        $table = $wpdb->prefix . 'inmopress_activity_log';

        // Contar total
        $count_query = "SELECT COUNT(*) FROM {$table} WHERE {$where_clause}";
        if (!empty($where_values)) {
            $count_query = $wpdb->prepare($count_query, $where_values);
        }
        $total_items = $wpdb->get_var($count_query);

        // Obtener logs
        $offset = ($paged - 1) * $per_page;
        $query = "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $query_values = array_merge($where_values, array($per_page, $offset));
        $query = $wpdb->prepare($query, $query_values);

        $logs = $wpdb->get_results($query);

        // Obtener usuarios para filtro
        $users = get_users(array('fields' => array('ID', 'display_name')));

        ?>
        <div class="wrap">
            <h1>Activity Log</h1>

            <!-- Filtros -->
            <form method="get" action="" style="margin: 20px 0;">
                <input type="hidden" name="post_type" value="impress_property">
                <input type="hidden" name="page" value="inmopress-activity-log">

                <table class="form-table">
                    <tr>
                        <th><label>Acción</label></th>
                        <td>
                            <select name="action_filter">
                                <option value="">Todas</option>
                                <?php foreach ($this->action_types as $action): ?>
                                    <option value="<?php echo esc_attr($action); ?>" <?php selected($action_filter, $action); ?>>
                                        <?php echo esc_html(ucwords(str_replace('_', ' ', $action))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Tipo de Objeto</label></th>
                        <td>
                            <select name="object_type_filter">
                                <option value="">Todos</option>
                                <option value="impress_property" <?php selected($object_type_filter, 'impress_property'); ?>>Propiedades</option>
                                <option value="impress_client" <?php selected($object_type_filter, 'impress_client'); ?>>Clientes</option>
                                <option value="impress_lead" <?php selected($object_type_filter, 'impress_lead'); ?>>Leads</option>
                                <option value="impress_event" <?php selected($object_type_filter, 'impress_event'); ?>>Eventos</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Usuario</label></th>
                        <td>
                            <select name="user_filter">
                                <option value="0">Todos</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($user_filter, $user->ID); ?>>
                                        <?php echo esc_html($user->display_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Desde</label></th>
                        <td><input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>"></td>
                    </tr>
                    <tr>
                        <th><label>Hasta</label></th>
                        <td><input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>"></td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">Filtrar</button>
                    <a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-activity-log'); ?>" class="button">Limpiar</a>
                    <a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-activity-log&export=csv'); ?>" class="button">Exportar CSV</a>
                </p>
            </form>

            <!-- Tabla de logs -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Objeto</th>
                        <th>IP</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6">No hay registros que mostrar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $user = get_userdata($log->user_id);
                            $object_link = '';
                            if ($log->object_id) {
                                $edit_link = get_edit_post_link($log->object_id);
                                if ($edit_link) {
                                    $object_link = '<a href="' . esc_url($edit_link) . '">' . esc_html($log->object_type) . ' #' . $log->object_id . '</a>';
                                } else {
                                    $object_link = esc_html($log->object_type) . ' #' . $log->object_id;
                                }
                            }
                            $data = $log->data ? json_decode($log->data, true) : array();
                            ?>
                            <tr>
                                <td><?php echo esc_html($log->created_at); ?></td>
                                <td><?php echo $user ? esc_html($user->display_name) : 'Sistema'; ?></td>
                                <td><?php echo esc_html(ucwords(str_replace('_', ' ', $log->action))); ?></td>
                                <td><?php echo $object_link; ?></td>
                                <td><?php echo esc_html($log->ip_address); ?></td>
                                <td>
                                    <?php if (!empty($data)): ?>
                                        <details>
                                            <summary>Ver detalles</summary>
                                            <pre style="font-size: 11px; max-width: 400px; overflow: auto;"><?php echo esc_html(json_encode($data, JSON_PRETTY_PRINT)); ?></pre>
                                        </details>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Paginación -->
            <?php
            $total_pages = ceil($total_items / $per_page);
            if ($total_pages > 1):
                $base_url = admin_url('edit.php?post_type=impress_property&page=inmopress-activity-log');
                $base_url .= $action_filter ? '&action_filter=' . urlencode($action_filter) : '';
                $base_url .= $object_type_filter ? '&object_type_filter=' . urlencode($object_type_filter) : '';
                $base_url .= $user_filter ? '&user_filter=' . $user_filter : '';
                $base_url .= $date_from ? '&date_from=' . urlencode($date_from) : '';
                $base_url .= $date_to ? '&date_to=' . urlencode($date_to) : '';
                ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => $base_url . '%_%',
                            'format' => '&paged=%#%',
                            'current' => $paged,
                            'total' => $total_pages,
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php

        // Exportar CSV si se solicita
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $this->export_csv($where, $where_values);
        }
    }

    /**
     * Exportar logs a CSV
     */
    private function export_csv($where, $where_values)
    {
        global $wpdb;

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=activity-log-' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Fecha', 'Usuario', 'Acción', 'Tipo Objeto', 'ID Objeto', 'IP', 'Datos'));

        $table = $wpdb->prefix . 'inmopress_activity_log';
        $where_clause = implode(' AND ', $where);
        $query = "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY created_at DESC LIMIT 10000";
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }

        $logs = $wpdb->get_results($query);

        foreach ($logs as $log) {
            $user = get_userdata($log->user_id);
            $row = array(
                $log->created_at,
                $user ? $user->display_name : 'Sistema',
                $log->action,
                $log->object_type,
                $log->object_id,
                $log->ip_address,
                $log->data ?: '',
            );
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
