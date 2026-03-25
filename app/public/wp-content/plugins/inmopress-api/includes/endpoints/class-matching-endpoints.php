<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Matching Endpoints - Endpoints para matching de propiedades y clientes
 */
class Inmopress_Matching_Endpoints
{
    private $namespace;
    private $api_manager;
    private $jwt_auth;
    private $rate_limiter;

    public function __construct()
    {
        $this->namespace = INMOPRESS_API_NAMESPACE;
        $this->api_manager = Inmopress_API_Manager::get_instance();
        $this->jwt_auth = Inmopress_JWT_Auth::get_instance();
        $this->rate_limiter = Inmopress_Rate_Limiter::get_instance();
    }

    public function register_routes()
    {
        // Obtener matches de una propiedad
        register_rest_route($this->namespace, '/matching/property/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_property_matches'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Obtener matches de un cliente
        register_rest_route($this->namespace, '/matching/client/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_client_matches'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Calcular matching para una propiedad
        register_rest_route($this->namespace, '/matching/property/(?P<id>\d+)/calculate', array(
            'methods' => 'POST',
            'callback' => array($this, 'calculate_property_matching'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Calcular matching para un cliente
        register_rest_route($this->namespace, '/matching/client/(?P<id>\d+)/calculate', array(
            'methods' => 'POST',
            'callback' => array($this, 'calculate_client_matching'),
            'permission_callback' => array($this, 'check_auth'),
        ));
    }

    /**
     * Obtener matches de una propiedad
     */
    public function get_property_matches($request)
    {
        $rate_check = $this->rate_limiter->apply_rate_limit($request);
        if (is_wp_error($rate_check)) {
            return $this->api_manager->error_response($rate_check, 429);
        }

        $property_id = intval($request->get_param('id'));
        $threshold = floatval($request->get_param('threshold')) ?: 70;
        $limit = intval($request->get_param('limit')) ?: 10;

        if (!class_exists('Inmopress_Matching_Engine')) {
            return $this->api_manager->error_response(
                new WP_Error('matching_not_available', 'Motor de matching no disponible'),
                503
            );
        }

        $matching_engine = Inmopress_Matching_Engine::get_instance();
        $matches = $matching_engine->get_property_matches($property_id, $threshold, $limit);

        return $this->api_manager->success_response(array(
            'property_id' => $property_id,
            'matches' => $matches,
            'total' => count($matches),
        ));
    }

    /**
     * Obtener matches de un cliente
     */
    public function get_client_matches($request)
    {
        $rate_check = $this->rate_limiter->apply_rate_limit($request);
        if (is_wp_error($rate_check)) {
            return $this->api_manager->error_response($rate_check, 429);
        }

        $client_id = intval($request->get_param('id'));
        $threshold = floatval($request->get_param('threshold')) ?: 70;
        $limit = intval($request->get_param('limit')) ?: 10;

        if (!class_exists('Inmopress_Matching_Engine')) {
            return $this->api_manager->error_response(
                new WP_Error('matching_not_available', 'Motor de matching no disponible'),
                503
            );
        }

        $matching_engine = Inmopress_Matching_Engine::get_instance();
        $matches = $matching_engine->get_client_matches($client_id, $threshold, $limit);

        return $this->api_manager->success_response(array(
            'client_id' => $client_id,
            'matches' => $matches,
            'total' => count($matches),
        ));
    }

    /**
     * Calcular matching para una propiedad
     */
    public function calculate_property_matching($request)
    {
        $property_id = intval($request->get_param('id'));
        $threshold = floatval($request->get_param('threshold')) ?: 70;

        if (!class_exists('Inmopress_Matching_Engine')) {
            return $this->api_manager->error_response(
                new WP_Error('matching_not_available', 'Motor de matching no disponible'),
                503
            );
        }

        $matching_engine = Inmopress_Matching_Engine::get_instance();
        $matching_engine->cache_matching_scores($property_id, $threshold);

        return $this->api_manager->success_response(array(
            'property_id' => $property_id,
            'message' => 'Matching calculado exitosamente',
        ));
    }

    /**
     * Calcular matching para un cliente
     */
    public function calculate_client_matching($request)
    {
        $client_id = intval($request->get_param('id'));
        $threshold = floatval($request->get_param('threshold')) ?: 70;

        if (!class_exists('Inmopress_Matching_Engine')) {
            return $this->api_manager->error_response(
                new WP_Error('matching_not_available', 'Motor de matching no disponible'),
                503
            );
        }

        // Obtener todas las propiedades activas y calcular matching
        $properties = get_posts(array(
            'post_type' => 'impress_property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ));

        $matching_engine = Inmopress_Matching_Engine::get_instance();
        $calculated = 0;

        foreach ($properties as $property_id) {
            $score = $matching_engine->calculate_match_score($property_id, $client_id);
            if ($score >= $threshold) {
                $calculated++;
            }
        }

        return $this->api_manager->success_response(array(
            'client_id' => $client_id,
            'properties_checked' => count($properties),
            'matches_found' => $calculated,
            'message' => 'Matching calculado exitosamente',
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

        $request->set_param('user_id', $user_id);
        return true;
    }
}
