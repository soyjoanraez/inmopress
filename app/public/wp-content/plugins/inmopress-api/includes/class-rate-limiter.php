<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rate Limiter - Limita peticiones por usuario
 */
class Inmopress_Rate_Limiter
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
     * Verificar rate limit
     */
    public function check_rate_limit($user_id)
    {
        $limit = get_option('inmopress_api_rate_limit', 100);
        $window = 3600; // 1 hora

        $key = 'inmopress_api_rate_' . $user_id;
        $count = get_transient($key);

        if ($count === false) {
            set_transient($key, 1, $window);
            return true;
        }

        if ($count >= $limit) {
            return new WP_Error(
                'rate_limit_exceeded',
                'Has excedido el límite de peticiones. Límite: ' . $limit . ' por hora.',
                array('status' => 429)
            );
        }

        set_transient($key, $count + 1, $window);
        return true;
    }

    /**
     * Middleware para aplicar rate limiting
     */
    public function apply_rate_limit($request)
    {
        $user_id = $request->get_param('user_id');
        
        if (empty($user_id)) {
            // Intentar obtener desde JWT
            $jwt_auth = Inmopress_JWT_Auth::get_instance();
            $user_id = $jwt_auth->authenticate_request($request);
            
            if (is_wp_error($user_id)) {
                return $user_id;
            }
        }

        return $this->check_rate_limit($user_id);
    }
}
