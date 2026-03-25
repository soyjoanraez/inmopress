<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * License Manager - Gestiona activación, estados y validación de licencias
 */
class Inmopress_License_Manager
{
    private static $instance = null;
    private $option_key = 'inmopress_license_data';
    private $cache_key = 'inmopress_license_cache';

    // Estados de licencia
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_EXPIRED = 'expired';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_GRACE = 'grace';
    const STATUS_BLOCKED = 'blocked';

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener datos de licencia
     */
    public function get_license_data()
    {
        $data = get_option($this->option_key, array());
        
        // Valores por defecto
        return wp_parse_args($data, array(
            'license_key' => '',
            'status' => self::STATUS_INACTIVE,
            'plan' => 'starter',
            'expires_at' => null,
            'features' => array(),
            'limits' => array(),
            'last_validated' => null,
        ));
    }

    /**
     * Activar licencia
     */
    public function activate_license($license_key)
    {
        if (empty($license_key)) {
            return new WP_Error('empty_key', 'La clave de licencia no puede estar vacía');
        }

        $installation_id = get_option('inmopress_installation_id');
        if (empty($installation_id)) {
            $installation_id = wp_generate_uuid4();
            update_option('inmopress_installation_id', $installation_id);
        }

        $site_url = get_site_url();
        $domain = parse_url($site_url, PHP_URL_HOST);

        // Llamar al servidor de licencias
        $response = wp_remote_post(INMOPRESS_LICENSE_SERVER . '/api/v1/licenses/activate', array(
            'timeout' => 15,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'license_key' => $license_key,
                'installation_id' => $installation_id,
                'domain' => $domain,
                'site_url' => $site_url,
            )),
        ));

        if (is_wp_error($response)) {
            return new WP_Error('server_error', 'Error al conectar con el servidor de licencias: ' . $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('activation_failed', $body['error']);
        }

        if (!isset($body['success']) || !$body['success']) {
            return new WP_Error('activation_failed', 'La activación falló. Verifica tu clave de licencia.');
        }

        // Guardar datos de licencia
        $license_data = array(
            'license_key' => $license_key,
            'status' => self::STATUS_ACTIVE,
            'plan' => isset($body['plan']) ? $body['plan'] : 'starter',
            'expires_at' => isset($body['expires_at']) ? $body['expires_at'] : null,
            'features' => isset($body['features']) ? $body['features'] : array(),
            'limits' => isset($body['limits']) ? $body['limits'] : array(),
            'last_validated' => current_time('mysql'),
        );

        update_option($this->option_key, $license_data);
        
        // Cache de validación (12 horas)
        set_transient($this->cache_key, $license_data, 12 * HOUR_IN_SECONDS);

        // Disparar acción
        do_action('inmopress_license_activated', $license_key, $license_data);

        return true;
    }

    /**
     * Desactivar licencia
     */
    public function deactivate_license()
    {
        $license_data = $this->get_license_data();
        
        if (empty($license_data['license_key'])) {
            return new WP_Error('no_license', 'No hay licencia activa para desactivar');
        }

        $installation_id = get_option('inmopress_installation_id');
        $site_url = get_site_url();
        $domain = parse_url($site_url, PHP_URL_HOST);

        // Notificar al servidor
        wp_remote_post(INMOPRESS_LICENSE_SERVER . '/api/v1/licenses/deactivate', array(
            'timeout' => 10,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'license_key' => $license_data['license_key'],
                'installation_id' => $installation_id,
                'domain' => $domain,
            )),
        ));

        // Limpiar datos locales
        delete_option($this->option_key);
        delete_transient($this->cache_key);

        do_action('inmopress_license_deactivated');

        return true;
    }

    /**
     * Validar licencia (local y remota)
     */
    public function validate_license()
    {
        $license_data = $this->get_license_data();

        if (empty($license_data['license_key'])) {
            return false;
        }

        // Verificar cache (12 horas)
        $cached = get_transient($this->cache_key);
        if ($cached !== false) {
            return $cached['status'] === self::STATUS_ACTIVE;
        }

        // Validación remota
        $validator = Inmopress_License_Validator::get_instance();
        $result = $validator->validate_remote($license_data['license_key']);

        if (is_wp_error($result)) {
            // En caso de error, usar datos locales y marcar como pendiente de validación
            return $license_data['status'] === self::STATUS_ACTIVE;
        }

        // Actualizar estado según respuesta
        if (isset($result['status'])) {
            $license_data['status'] = $result['status'];
            $license_data['last_validated'] = current_time('mysql');
            
            if (isset($result['expires_at'])) {
                $license_data['expires_at'] = $result['expires_at'];
            }

            update_option($this->option_key, $license_data);
        }

        // Cache por 12 horas
        set_transient($this->cache_key, $license_data, 12 * HOUR_IN_SECONDS);

        return $license_data['status'] === self::STATUS_ACTIVE;
    }

    /**
     * Verificar si la licencia es válida
     */
    public function is_license_valid()
    {
        $license_data = $this->get_license_data();

        if ($license_data['status'] !== self::STATUS_ACTIVE) {
            return false;
        }

        // Verificar expiración
        if (!empty($license_data['expires_at'])) {
            $expires = strtotime($license_data['expires_at']);
            if ($expires && $expires < current_time('timestamp')) {
                $this->update_status(self::STATUS_EXPIRED);
                return false;
            }
        }

        return true;
    }

    /**
     * Actualizar estado de licencia
     */
    public function update_status($status)
    {
        $license_data = $this->get_license_data();
        $license_data['status'] = $status;
        update_option($this->option_key, $license_data);
        delete_transient($this->cache_key);
    }

    /**
     * Obtener plan actual
     */
    public function get_current_plan()
    {
        $license_data = $this->get_license_data();
        return $license_data['plan'];
    }
}
