<?php
/**
 * WPTO Monitoring Module
 * Health Check automático y monitoreo del sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPTO_Monitoring {

    private $table_name;
    private static $db_version = '1.0';

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpto_health_checks';
        $this->init_hooks();
        $this->maybe_create_table();
    }

    private function init_hooks() {
        // Programar verificación diaria
        if (!wp_next_scheduled('wpto_daily_health_check')) {
            wp_schedule_event(time(), 'daily', 'wpto_daily_health_check');
        }
        add_action('wpto_daily_health_check', array($this, 'run_health_checks'));
    }

    /**
     * Crear tabla solo si no existe o necesita actualización
     */
    private function maybe_create_table() {
        $installed_version = get_option('wpto_monitoring_db_version', '0');

        if (version_compare($installed_version, self::$db_version, '<')) {
            $this->create_table();
            update_option('wpto_monitoring_db_version', self::$db_version);
        }
    }

    /**
     * Crear tabla de health checks
     */
    public static function create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpto_health_checks';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            check_type varchar(50) NOT NULL,
            status varchar(20) DEFAULT 'success',
            details text,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY check_type (check_type),
            KEY timestamp (timestamp)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Ejecutar verificaciones de salud
     */
    public function run_health_checks() {
        $checks = array(
            'uptime' => $this->check_uptime(),
            'speed' => $this->check_speed(),
            'errors' => $this->check_errors(),
            'plugins' => $this->check_plugins(),
            'database' => $this->check_database(),
            'disk_space' => $this->check_disk_space()
        );
        
        foreach ($checks as $type => $result) {
            $this->save_health_check($type, $result['status'], $result['details']);
        }
    }
    
    /**
     * Verificar uptime
     */
    private function check_uptime() {
        $response = wp_remote_get(home_url(), array('timeout' => 5));
        
        if (is_wp_error($response)) {
            return array(
                'status' => 'error',
                'details' => 'El sitio no responde: ' . $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code >= 200 && $status_code < 400) {
            return array(
                'status' => 'success',
                'details' => 'El sitio está funcionando correctamente (HTTP ' . $status_code . ')'
            );
        }
        
        return array(
            'status' => 'warning',
            'details' => 'El sitio responde con código HTTP ' . $status_code
        );
    }
    
    /**
     * Verificar velocidad
     */
    private function check_speed() {
        $start_time = microtime(true);
        $response = wp_remote_get(home_url(), array('timeout' => 10));
        $end_time = microtime(true);
        
        $load_time = ($end_time - $start_time) * 1000; // en milisegundos
        
        if ($load_time < 1000) {
            return array(
                'status' => 'success',
                'details' => 'Tiempo de carga excelente: ' . round($load_time, 2) . 'ms'
            );
        } elseif ($load_time < 3000) {
            return array(
                'status' => 'warning',
                'details' => 'Tiempo de carga aceptable: ' . round($load_time, 2) . 'ms'
            );
        } else {
            return array(
                'status' => 'error',
                'details' => 'Tiempo de carga lento: ' . round($load_time, 2) . 'ms'
            );
        }
    }
    
    /**
     * Verificar errores
     */
    private function check_errors() {
        global $wpdb;

        // Verificar que la tabla de activity_log existe antes de consultar
        $table_name = $wpdb->prefix . 'wpto_activity_log';
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table_name
        ));

        if (!$table_exists) {
            return array(
                'status' => 'warning',
                'details' => 'Tabla de logs no inicializada aún'
            );
        }

        // Verificar errores en logs recientes
        $recent_errors = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table_name}
             WHERE status = 'error' AND timestamp > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );

        if ($recent_errors === null) {
            return array(
                'status' => 'warning',
                'details' => 'No se pudo verificar el estado de errores'
            );
        }

        $recent_errors = intval($recent_errors);

        if ($recent_errors == 0) {
            return array(
                'status' => 'success',
                'details' => 'No se han detectado errores en las últimas 24 horas'
            );
        } elseif ($recent_errors < 5) {
            return array(
                'status' => 'warning',
                'details' => 'Se han detectado ' . $recent_errors . ' errores en las últimas 24 horas'
            );
        } else {
            return array(
                'status' => 'error',
                'details' => 'Se han detectado ' . $recent_errors . ' errores en las últimas 24 horas'
            );
        }
    }
    
    /**
     * Verificar plugins desactualizados
     */
    private function check_plugins() {
        $updates = get_plugin_updates();
        $count = count($updates);
        
        if ($count == 0) {
            return array(
                'status' => 'success',
                'details' => 'Todos los plugins están actualizados'
            );
        } elseif ($count < 3) {
            return array(
                'status' => 'warning',
                'details' => $count . ' plugin(s) necesitan actualización'
            );
        } else {
            return array(
                'status' => 'error',
                'details' => $count . ' plugins necesitan actualización urgente'
            );
        }
    }
    
    /**
     * Verificar base de datos
     */
    private function check_database() {
        global $wpdb;
        
        // Verificar tamaño de la base de datos
        $db_size = $wpdb->get_var(
            "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) 
             FROM information_schema.tables 
             WHERE table_schema = DATABASE()"
        );
        
        if ($db_size < 100) {
            return array(
                'status' => 'success',
                'details' => 'Base de datos en buen estado (' . $db_size . ' MB)'
            );
        } elseif ($db_size < 500) {
            return array(
                'status' => 'warning',
                'details' => 'Base de datos grande: ' . $db_size . ' MB (considera optimizar)'
            );
        } else {
            return array(
                'status' => 'error',
                'details' => 'Base de datos muy grande: ' . $db_size . ' MB (optimización urgente)'
            );
        }
    }
    
    /**
     * Verificar espacio en disco
     */
    private function check_disk_space() {
        $free_space = disk_free_space(ABSPATH);
        $total_space = disk_total_space(ABSPATH);
        
        if ($free_space && $total_space) {
            $percent_free = ($free_space / $total_space) * 100;
            
            if ($percent_free > 20) {
                return array(
                    'status' => 'success',
                    'details' => 'Espacio en disco suficiente: ' . round($percent_free, 1) . '% libre'
                );
            } elseif ($percent_free > 10) {
                return array(
                    'status' => 'warning',
                    'details' => 'Espacio en disco bajo: ' . round($percent_free, 1) . '% libre'
                );
            } else {
                return array(
                    'status' => 'error',
                    'details' => 'Espacio en disco crítico: ' . round($percent_free, 1) . '% libre'
                );
            }
        }
        
        return array(
            'status' => 'warning',
            'details' => 'No se pudo verificar el espacio en disco'
        );
    }
    
    /**
     * Guardar resultado de health check
     */
    private function save_health_check($check_type, $status, $details) {
        global $wpdb;
        
        $wpdb->insert(
            $this->table_name,
            array(
                'check_type' => $check_type,
                'status' => $status,
                'details' => $details,
                'timestamp' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Obtener últimos health checks
     */
    public function get_recent_checks($limit = 20) {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} ORDER BY timestamp DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
    }
    
    /**
     * Obtener estado general
     */
    public function get_overall_status() {
        global $wpdb;
        
        $checks = $wpdb->get_results(
            "SELECT check_type, status, details, timestamp 
             FROM {$this->table_name} 
             WHERE timestamp > DATE_SUB(NOW(), INTERVAL 24 HOUR)
             ORDER BY timestamp DESC",
            ARRAY_A
        );
        
        $statuses = array('success' => 0, 'warning' => 0, 'error' => 0);
        $latest = array();
        
        foreach ($checks as $check) {
            if (!isset($latest[$check['check_type']])) {
                $latest[$check['check_type']] = $check;
                $statuses[$check['status']]++;
            }
        }
        
        $overall = 'success';
        if ($statuses['error'] > 0) {
            $overall = 'error';
        } elseif ($statuses['warning'] > 0) {
            $overall = 'warning';
        }
        
        return array(
            'overall' => $overall,
            'counts' => $statuses,
            'checks' => $latest
        );
    }
}


