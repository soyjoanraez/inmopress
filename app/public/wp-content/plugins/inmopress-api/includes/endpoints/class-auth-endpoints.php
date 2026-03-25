<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Auth Endpoints - Autenticación y gestión de tokens
 */
class Inmopress_Auth_Endpoints
{
    private $namespace;
    private $api_manager;
    private $jwt_auth;

    public function __construct()
    {
        $this->namespace = INMOPRESS_API_NAMESPACE;
        $this->api_manager = Inmopress_API_Manager::get_instance();
        $this->jwt_auth = Inmopress_JWT_Auth::get_instance();
    }

    public function register_routes()
    {
        // Login
        register_rest_route($this->namespace, '/auth/login', array(
            'methods' => 'POST',
            'callback' => array($this, 'login'),
            'permission_callback' => '__return_true',
        ));

        // Refresh token
        register_rest_route($this->namespace, '/auth/refresh', array(
            'methods' => 'POST',
            'callback' => array($this, 'refresh'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Logout
        register_rest_route($this->namespace, '/auth/logout', array(
            'methods' => 'POST',
            'callback' => array($this, 'logout'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Me (info del usuario autenticado)
        register_rest_route($this->namespace, '/auth/me', array(
            'methods' => 'GET',
            'callback' => array($this, 'me'),
            'permission_callback' => array($this, 'check_auth'),
        ));
    }

    /**
     * Login
     */
    public function login($request)
    {
        $username = $request->get_param('username');
        $password = $request->get_param('password');

        if (empty($username) || empty($password)) {
            return $this->api_manager->error_response(
                new WP_Error('missing_credentials', 'Usuario y contraseña requeridos'),
                400
            );
        }

        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            return $this->api_manager->error_response($user, 401);
        }

        $token = $this->jwt_auth->generate_token($user->ID);

        if (is_wp_error($token)) {
            return $this->api_manager->error_response($token, 500);
        }

        return $this->api_manager->success_response(array(
            'token' => $token,
            'user' => array(
                'id' => $user->ID,
                'username' => $user->user_login,
                'email' => $user->user_email,
                'display_name' => $user->display_name,
            ),
        ));
    }

    /**
     * Refresh token
     */
    public function refresh($request)
    {
        $user_id = $request->get_param('user_id');
        $token = $this->jwt_auth->generate_token($user_id);

        if (is_wp_error($token)) {
            return $this->api_manager->error_response($token, 500);
        }

        return $this->api_manager->success_response(array(
            'token' => $token,
        ));
    }

    /**
     * Logout
     */
    public function logout($request)
    {
        // En JWT stateless, logout es principalmente del lado del cliente
        // Aquí podríamos invalidar tokens si implementamos blacklist
        return $this->api_manager->success_response(array(
            'message' => 'Logout exitoso',
        ));
    }

    /**
     * Me - Info del usuario autenticado
     */
    public function me($request)
    {
        $user_id = $request->get_param('user_id');
        $user = get_userdata($user_id);

        if (!$user) {
            return $this->api_manager->error_response(
                new WP_Error('user_not_found', 'Usuario no encontrado'),
                404
            );
        }

        return $this->api_manager->success_response(array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'roles' => $user->roles,
        ));
    }

    /**
     * Verificar autenticación
     */
    public function check_auth($request)
    {
        $user_id = $this->jwt_auth->authenticate_request($request);
        
        if (is_wp_error($user_id)) {
            return false;
        }

        // Guardar user_id en request para uso posterior
        $request->set_param('user_id', $user_id);
        return true;
    }
}
