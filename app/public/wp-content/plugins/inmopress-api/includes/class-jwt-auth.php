<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * JWT Auth - Autenticación mediante JWT
 * 
 * NOTA: Requiere composer install para instalar firebase/php-jwt
 * Ejecutar: composer require firebase/php-jwt:^6.0
 */
class Inmopress_JWT_Auth
{
    private static $instance = null;
    private $secret_key;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->secret_key = get_option('inmopress_api_jwt_secret', '');
        
        if (empty($this->secret_key)) {
            $this->secret_key = wp_generate_password(64, false);
            update_option('inmopress_api_jwt_secret', $this->secret_key);
        }

        $this->load_jwt_library();
    }

    /**
     * Cargar librería JWT
     */
    private function load_jwt_library()
    {
        if (!class_exists('\Firebase\JWT\JWT')) {
            $autoload_paths = array(
                INMOPRESS_API_PATH . '../../vendor/autoload.php',
                WP_CONTENT_DIR . '/vendor/autoload.php',
                ABSPATH . 'vendor/autoload.php',
            );

            foreach ($autoload_paths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    break;
                }
            }
        }
    }

    /**
     * Generar token JWT
     */
    public function generate_token($user_id)
    {
        if (!class_exists('\Firebase\JWT\JWT')) {
            return new WP_Error('jwt_not_available', 'JWT library no está disponible. Instala con: composer require firebase/php-jwt:^6.0');
        }

        $issued_at = time();
        $expiration = $issued_at + (60 * 60 * 24 * 7); // 7 días

        $token = array(
            'iss' => get_bloginfo('url'),
            'iat' => $issued_at,
            'exp' => $expiration,
            'data' => array(
                'user_id' => $user_id,
            ),
        );

        try {
            return \Firebase\JWT\JWT::encode($token, $this->secret_key, 'HS256');
        } catch (Exception $e) {
            return new WP_Error('jwt_error', 'Error al generar token: ' . $e->getMessage());
        }
    }

    /**
     * Validar token JWT
     */
    public function validate_token($token)
    {
        if (!class_exists('\Firebase\JWT\JWT')) {
            return false;
        }

        try {
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($this->secret_key, 'HS256'));
            return isset($decoded->data->user_id) ? intval($decoded->data->user_id) : false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Middleware para validar autenticación en endpoints
     */
    public function authenticate_request($request)
    {
        $auth_header = $request->get_header('Authorization');

        if (empty($auth_header)) {
            return new WP_Error('no_auth', 'No se proporcionó token de autenticación', array('status' => 401));
        }

        // Extraer token (Bearer TOKEN)
        if (preg_match('/Bearer\s+(.+)$/i', $auth_header, $matches)) {
            $token = $matches[1];
        } else {
            return new WP_Error('invalid_auth', 'Formato de autorización inválido', array('status' => 401));
        }

        $user_id = $this->validate_token($token);

        if (!$user_id) {
            return new WP_Error('invalid_token', 'Token inválido o expirado', array('status' => 401));
        }

        return $user_id;
    }
}
