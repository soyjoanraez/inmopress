<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clients Endpoints - CRUD de clientes
 */
class Inmopress_Clients_Endpoints
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
        // Listar clientes
        register_rest_route($this->namespace, '/clients', array(
            'methods' => 'GET',
            'callback' => array($this, 'list_clients'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Obtener cliente
        register_rest_route($this->namespace, '/clients/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_client'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Crear cliente
        register_rest_route($this->namespace, '/clients', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_client'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Actualizar cliente
        register_rest_route($this->namespace, '/clients/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_client'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Eliminar cliente
        register_rest_route($this->namespace, '/clients/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_client'),
            'permission_callback' => array($this, 'check_auth'),
        ));
    }

    /**
     * Listar clientes
     */
    public function list_clients($request)
    {
        $rate_check = $this->rate_limiter->apply_rate_limit($request);
        if (is_wp_error($rate_check)) {
            return $this->api_manager->error_response($rate_check, 429);
        }

        $per_page = $request->get_param('per_page') ?: 20;
        $page = $request->get_param('page') ?: 1;

        $args = array(
            'post_type' => 'impress_client',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => 'publish',
        );

        $query = new WP_Query($args);
        $clients = array();

        foreach ($query->posts as $post) {
            $clients[] = $this->format_client($post);
        }

        return $this->api_manager->success_response(array(
            'clients' => $clients,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ));
    }

    /**
     * Obtener cliente
     */
    public function get_client($request)
    {
        $id = intval($request->get_param('id'));
        $post = get_post($id);

        if (!$post || $post->post_type !== 'impress_client') {
            return $this->api_manager->error_response(
                new WP_Error('not_found', 'Cliente no encontrado'),
                404
            );
        }

        return $this->api_manager->success_response($this->format_client($post));
    }

    /**
     * Crear cliente
     */
    public function create_client($request)
    {
        $schema = array(
            'name' => array('type' => 'string', 'required' => true, 'max_length' => 255),
            'email' => array('type' => 'email', 'required' => false),
            'phone' => array('type' => 'string', 'required' => false),
        );

        $data = $this->api_manager->sanitize_input($request->get_json_params(), $schema);
        if (is_wp_error($data)) {
            return $this->api_manager->error_response($data, 400);
        }

        $user_id = $request->get_param('user_id');

        $post_data = array(
            'post_title' => $data['name'],
            'post_type' => 'impress_client',
            'post_status' => 'publish',
            'post_author' => $user_id,
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return $this->api_manager->error_response($post_id, 500);
        }

        // Guardar campos ACF
        $json_params = $request->get_json_params();
        if (function_exists('update_field')) {
            if (isset($json_params['email'])) {
                update_field('impress_client_email', $json_params['email'], $post_id);
            }
            if (isset($json_params['phone'])) {
                update_field('impress_client_phone', $json_params['phone'], $post_id);
            }
        }

        // Disparar webhook
        $webhook_manager = Inmopress_Webhook_Manager::get_instance();
        $webhook_manager->trigger_webhook('client.created', array('client_id' => $post_id));

        return $this->api_manager->success_response(array(
            'id' => $post_id,
            'message' => 'Cliente creado exitosamente',
        ), 201);
    }

    /**
     * Actualizar cliente
     */
    public function update_client($request)
    {
        $id = intval($request->get_param('id'));
        $post = get_post($id);

        if (!$post || $post->post_type !== 'impress_client') {
            return $this->api_manager->error_response(
                new WP_Error('not_found', 'Cliente no encontrado'),
                404
            );
        }

        $json_params = $request->get_json_params();
        $post_data = array('ID' => $id);

        if (isset($json_params['name'])) {
            $post_data['post_title'] = sanitize_text_field($json_params['name']);
        }

        $result = wp_update_post($post_data);

        if (is_wp_error($result)) {
            return $this->api_manager->error_response($result, 500);
        }

        // Actualizar campos ACF
        if (function_exists('update_field')) {
            if (isset($json_params['email'])) {
                update_field('impress_client_email', $json_params['email'], $id);
            }
            if (isset($json_params['phone'])) {
                update_field('impress_client_phone', $json_params['phone'], $id);
            }
        }

        // Disparar webhook
        $webhook_manager = Inmopress_Webhook_Manager::get_instance();
        $webhook_manager->trigger_webhook('client.updated', array('client_id' => $id));

        return $this->api_manager->success_response(array(
            'id' => $id,
            'message' => 'Cliente actualizado exitosamente',
        ));
    }

    /**
     * Eliminar cliente
     */
    public function delete_client($request)
    {
        $id = intval($request->get_param('id'));
        $result = wp_delete_post($id, true);

        if (!$result) {
            return $this->api_manager->error_response(
                new WP_Error('delete_failed', 'Error al eliminar cliente'),
                500
            );
        }

        // Disparar webhook
        $webhook_manager = Inmopress_Webhook_Manager::get_instance();
        $webhook_manager->trigger_webhook('client.deleted', array('client_id' => $id));

        return $this->api_manager->success_response(array(
            'message' => 'Cliente eliminado exitosamente',
        ));
    }

    /**
     * Formatear cliente para respuesta
     */
    private function format_client($post)
    {
        $fields = function_exists('get_fields') ? get_fields($post->ID) : array();

        return array(
            'id' => $post->ID,
            'name' => $post->post_title,
            'email' => $fields['impress_client_email'] ?? '',
            'phone' => $fields['impress_client_phone'] ?? '',
            'created_at' => $post->post_date,
            'updated_at' => $post->post_modified,
            'fields' => $fields ?: array(),
        );
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
