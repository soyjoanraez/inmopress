<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * License Validator - Valida licencias local y remotamente
 */
class Inmopress_License_Validator
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Validar licencia remotamente
     */
    public function validate_remote($license_key)
    {
        if (empty($license_key)) {
            return new WP_Error('empty_key', 'Clave de licencia vacía');
        }

        $installation_id = get_option('inmopress_installation_id');
        $site_url = get_site_url();
        $domain = parse_url($site_url, PHP_URL_HOST);

        $response = wp_remote_post(INMOPRESS_LICENSE_SERVER . '/api/v1/licenses/validate', array(
            'timeout' => 10,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'license_key' => $license_key,
                'installation_id' => $installation_id,
                'domain' => $domain,
            )),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('validation_error', $body['error']);
        }

        return $body;
    }

    /**
     * Validar licencia localmente (usando cache)
     */
    public function validate_local()
    {
        $license_manager = Inmopress_License_Manager::get_instance();
        return $license_manager->is_license_valid();
    }
}
