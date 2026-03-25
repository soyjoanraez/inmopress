<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Properties Endpoints - CRUD de propiedades
 */
class Inmopress_Properties_Endpoints
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
        // Listar propiedades
        register_rest_route($this->namespace, '/properties', array(
            'methods' => 'GET',
            'callback' => array($this, 'list_properties'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Obtener propiedad
        register_rest_route($this->namespace, '/properties/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_property'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Crear propiedad
        register_rest_route($this->namespace, '/properties', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_property'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Actualizar propiedad
        register_rest_route($this->namespace, '/properties/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_property'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Eliminar propiedad
        register_rest_route($this->namespace, '/properties/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_property'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Buscar propiedades
        register_rest_route($this->namespace, '/properties/search', array(
            'methods' => 'GET',
            'callback' => array($this, 'search_properties'),
            'permission_callback' => array($this, 'check_auth'),
        ));
    }

    /**
     * Listar propiedades
     */
    public function list_properties($request)
    {
        $rate_check = $this->rate_limiter->apply_rate_limit($request);
        if (is_wp_error($rate_check)) {
            return $this->api_manager->error_response($rate_check, 429);
        }

        $per_page = $request->get_param('per_page') ?: 20;
        $page = $request->get_param('page') ?: 1;
        $status = $request->get_param('status') ?: 'publish';

        $args = array(
            'post_type' => 'impress_property',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => $status,
        );

        $query = new WP_Query($args);
        $properties = array();

        foreach ($query->posts as $post) {
            $properties[] = $this->format_property($post);
        }

        return $this->api_manager->success_response(array(
            'properties' => $properties,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ));
    }

    /**
     * Obtener propiedad
     */
    public function get_property($request)
    {
        $id = intval($request->get_param('id'));
        $post = get_post($id);

        if (!$post || $post->post_type !== 'impress_property') {
            return $this->api_manager->error_response(
                new WP_Error('not_found', 'Propiedad no encontrada'),
                404
            );
        }

        return $this->api_manager->success_response($this->format_property($post));
    }

    /**
     * Crear propiedad
     */
    public function create_property($request)
    {
        $schema = array(
            'title' => array('type' => 'string', 'required' => true, 'max_length' => 255),
            'content' => array('type' => 'string', 'required' => false),
            'status' => array('type' => 'string', 'required' => false),
        );

        $data = $this->api_manager->sanitize_input($request->get_json_params(), $schema);
        if (is_wp_error($data)) {
            return $this->api_manager->error_response($data, 400);
        }

        $user_id = $request->get_param('user_id');

        $post_data = array(
            'post_title' => $data['title'],
            'post_content' => $data['content'] ?? '',
            'post_type' => 'impress_property',
            'post_status' => $data['status'] ?? 'draft',
            'post_author' => $user_id,
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return $this->api_manager->error_response($post_id, 500);
        }

        // Guardar campos ACF si existen
        $json_params = $request->get_json_params();
        if (isset($json_params['fields']) && function_exists('update_field')) {
            foreach ($json_params['fields'] as $field_key => $value) {
                update_field($field_key, $value, $post_id);
            }
        }

        // Disparar webhook
        $webhook_manager = Inmopress_Webhook_Manager::get_instance();
        $webhook_manager->trigger_webhook('property.created', array('property_id' => $post_id));

        return $this->api_manager->success_response(array(
            'id' => $post_id,
            'message' => 'Propiedad creada exitosamente',
        ), 201);
    }

    /**
     * Actualizar propiedad
     */
    public function update_property($request)
    {
        $id = intval($request->get_param('id'));
        $post = get_post($id);

        if (!$post || $post->post_type !== 'impress_property') {
            return $this->api_manager->error_response(
                new WP_Error('not_found', 'Propiedad no encontrada'),
                404
            );
        }

        $json_params = $request->get_json_params();
        $post_data = array('ID' => $id);

        if (isset($json_params['title'])) {
            $post_data['post_title'] = sanitize_text_field($json_params['title']);
        }
        if (isset($json_params['content'])) {
            $post_data['post_content'] = wp_kses_post($json_params['content']);
        }
        if (isset($json_params['status'])) {
            $post_data['post_status'] = sanitize_text_field($json_params['status']);
        }

        $result = wp_update_post($post_data);

        if (is_wp_error($result)) {
            return $this->api_manager->error_response($result, 500);
        }

        // Actualizar campos ACF
        if (isset($json_params['fields']) && function_exists('update_field')) {
            foreach ($json_params['fields'] as $field_key => $value) {
                update_field($field_key, $value, $id);
            }
        }

        // Disparar webhook
        $webhook_manager = Inmopress_Webhook_Manager::get_instance();
        $webhook_manager->trigger_webhook('property.updated', array('property_id' => $id));

        return $this->api_manager->success_response(array(
            'id' => $id,
            'message' => 'Propiedad actualizada exitosamente',
        ));
    }

    /**
     * Eliminar propiedad
     */
    public function delete_property($request)
    {
        $id = intval($request->get_param('id'));
        $result = wp_delete_post($id, true);

        if (!$result) {
            return $this->api_manager->error_response(
                new WP_Error('delete_failed', 'Error al eliminar propiedad'),
                500
            );
        }

        // Disparar webhook
        $webhook_manager = Inmopress_Webhook_Manager::get_instance();
        $webhook_manager->trigger_webhook('property.deleted', array('property_id' => $id));

        return $this->api_manager->success_response(array(
            'message' => 'Propiedad eliminada exitosamente',
        ));
    }

    /**
     * Buscar propiedades
     */
    public function search_properties($request)
    {
        $rate_check = $this->rate_limiter->apply_rate_limit($request);
        if (is_wp_error($rate_check)) {
            return $this->api_manager->error_response($rate_check, 429);
        }

        $query = $request->get_param('q');
        $operation = $request->get_param('operation');
        $city = $request->get_param('city');
        $min_price = $request->get_param('min_price');
        $max_price = $request->get_param('max_price');

        $args = array(
            'post_type' => 'impress_property',
            'posts_per_page' => 20,
            's' => $query,
            'meta_query' => array(),
        );

        if ($operation) {
            $args['meta_query'][] = array(
                'key' => 'impress_property_operation',
                'value' => $operation,
            );
        }

        if ($city) {
            $args['meta_query'][] = array(
                'key' => 'impress_property_city',
                'value' => $city,
                'compare' => 'LIKE',
            );
        }

        if ($min_price || $max_price) {
            $price_query = array(
                'key' => 'impress_property_price',
                'type' => 'NUMERIC',
            );
            if ($min_price) {
                $price_query['value'] = intval($min_price);
                $price_query['compare'] = '>=';
            }
            if ($max_price) {
                $price_query['value'] = intval($max_price);
                $price_query['compare'] = '<=';
            }
            $args['meta_query'][] = $price_query;
        }

        $query_obj = new WP_Query($args);
        $properties = array();

        foreach ($query_obj->posts as $post) {
            $properties[] = $this->format_property($post);
        }

        return $this->api_manager->success_response(array(
            'properties' => $properties,
            'total' => $query_obj->found_posts,
        ));
    }

    /**
     * Formatear propiedad para respuesta
     */
    private function format_property($post)
    {
        $fields = function_exists('get_fields') ? get_fields($post->ID) : array();

        return array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'status' => $post->post_status,
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
