<?php
/**
 * WPTO Redirects Module
 * Gestión de redirecciones 301, 302, 307
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPTO_Redirects {

    private $table_name;
    private static $db_version = '1.0';

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpto_redirects';
        $this->init_hooks();
        $this->maybe_create_table();
    }

    private function init_hooks() {
        add_action('template_redirect', array($this, 'process_redirects'), 1);
    }

    /**
     * Crear tabla solo si no existe o necesita actualización
     */
    private function maybe_create_table() {
        $installed_version = get_option('wpto_redirects_db_version', '0');

        if (version_compare($installed_version, self::$db_version, '<')) {
            $this->create_table();
            update_option('wpto_redirects_db_version', self::$db_version);
        }
    }

    /**
     * Crear tabla de redirecciones
     */
    public static function create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpto_redirects';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            from_url varchar(255) NOT NULL,
            to_url varchar(255) NOT NULL,
            status_code int(3) DEFAULT 301,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY from_url (from_url(191))
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Procesar redirecciones
     */
    public function process_redirects() {
        global $wpdb;
        
        $request_uri = $_SERVER['REQUEST_URI'];
        $request_uri = rtrim($request_uri, '/');
        
        // Buscar redirección exacta
        $redirect = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE from_url = %s LIMIT 1",
            $request_uri
        ));
        
        if ($redirect) {
            $status_code = intval($redirect->status_code);
            if (!in_array($status_code, array(301, 302, 307))) {
                $status_code = 301;
            }
            
            wp_redirect($redirect->to_url, $status_code);
            exit;
        }
        
        // Buscar redirección con wildcard (si la URL empieza con from_url)
        $redirects = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE from_url LIKE '%*%' ORDER BY LENGTH(from_url) DESC"
        );
        
        foreach ($redirects as $redirect) {
            $pattern = str_replace('*', '.*', preg_quote($redirect->from_url, '/'));
            if (preg_match('/^' . $pattern . '/', $request_uri)) {
                $to_url = str_replace('*', '', $redirect->to_url);
                $status_code = intval($redirect->status_code);
                if (!in_array($status_code, array(301, 302, 307))) {
                    $status_code = 301;
                }
                
                wp_redirect($to_url, $status_code);
                exit;
            }
        }
    }
    
    /**
     * Añadir redirección
     */
    public function add_redirect($from_url, $to_url, $status_code = 301) {
        global $wpdb;
        
        $from_url = sanitize_text_field($from_url);
        $to_url = esc_url_raw($to_url);
        $status_code = intval($status_code);
        
        if (!in_array($status_code, array(301, 302, 307))) {
            $status_code = 301;
        }
        
        // Verificar si ya existe
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE from_url = %s",
            $from_url
        ));
        
        if ($existing) {
            return false; // Ya existe
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'from_url' => $from_url,
                'to_url' => $to_url,
                'status_code' => $status_code,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Actualizar redirección
     */
    public function update_redirect($id, $from_url, $to_url, $status_code = 301) {
        global $wpdb;
        
        $id = intval($id);
        $from_url = sanitize_text_field($from_url);
        $to_url = esc_url_raw($to_url);
        $status_code = intval($status_code);
        
        if (!in_array($status_code, array(301, 302, 307))) {
            $status_code = 301;
        }
        
        $result = $wpdb->update(
            $this->table_name,
            array(
                'from_url' => $from_url,
                'to_url' => $to_url,
                'status_code' => $status_code
            ),
            array('id' => $id),
            array('%s', '%s', '%d'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Eliminar redirección
     */
    public function delete_redirect($id) {
        global $wpdb;
        
        $id = intval($id);
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Obtener todas las redirecciones
     */
    public function get_all_redirects() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC",
            ARRAY_A
        );
    }
    
    /**
     * Obtener redirección por ID
     */
    public function get_redirect($id) {
        global $wpdb;
        
        $id = intval($id);
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ), ARRAY_A);
    }
}


