<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * API Manager - Gestiona la API REST
 */
class Inmopress_API_Manager
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
     * Verificar si API está habilitada
     */
    public function is_api_enabled()
    {
        return get_option('inmopress_api_enabled', 1) === 1;
    }

    /**
     * Validar y sanitizar input
     */
    public function sanitize_input($data, $schema)
    {
        $sanitized = array();

        foreach ($schema as $field => $rules) {
            if (!isset($data[$field])) {
                if (isset($rules['required']) && $rules['required']) {
                    return new WP_Error('missing_field', "Campo requerido: {$field}");
                }
                continue;
            }

            $value = $data[$field];

            // Sanitizar según tipo
            switch ($rules['type']) {
                case 'string':
                    $value = sanitize_text_field($value);
                    if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                        return new WP_Error('invalid_field', "{$field} excede longitud máxima");
                    }
                    break;

                case 'email':
                    $value = sanitize_email($value);
                    if (!is_email($value)) {
                        return new WP_Error('invalid_field', "{$field} no es un email válido");
                    }
                    break;

                case 'integer':
                    $value = intval($value);
                    if (isset($rules['min']) && $value < $rules['min']) {
                        return new WP_Error('invalid_field', "{$field} es menor que el mínimo");
                    }
                    if (isset($rules['max']) && $value > $rules['max']) {
                        return new WP_Error('invalid_field', "{$field} es mayor que el máximo");
                    }
                    break;

                case 'array':
                    if (!is_array($value)) {
                        return new WP_Error('invalid_field', "{$field} debe ser un array");
                    }
                    break;
            }

            $sanitized[$field] = $value;
        }

        return $sanitized;
    }

    /**
     * Formatear respuesta de error
     */
    public function error_response($error, $status = 400)
    {
        return new WP_REST_Response(array(
            'success' => false,
            'error' => array(
                'code' => $error->get_error_code(),
                'message' => $error->get_error_message(),
            ),
        ), $status);
    }

    /**
     * Formatear respuesta de éxito
     */
    public function success_response($data, $status = 200)
    {
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $data,
        ), $status);
    }
}
