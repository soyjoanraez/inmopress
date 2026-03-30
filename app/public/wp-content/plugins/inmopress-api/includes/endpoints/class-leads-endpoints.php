<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Leads Endpoints - CRUD de leads
 */
class Inmopress_Leads_Endpoints
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
        // Listar leads
        register_rest_route($this->namespace, '/leads', array(
            'methods' => 'GET',
            'callback' => array($this, 'list_leads'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Obtener lead
        register_rest_route($this->namespace, '/leads/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_lead'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Crear lead
        register_rest_route($this->namespace, '/leads', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_lead'),
            'permission_callback' => '__return_true', // Permite crear leads sin auth
        ));

        // Actualizar lead
        register_rest_route($this->namespace, '/leads/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_lead'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Convertir lead a cliente
        register_rest_route($this->namespace, '/leads/(?P<id>\d+)/convert', array(
            'methods' => 'POST',
            'callback' => array($this, 'convert_lead'),
            'permission_callback' => array($this, 'check_auth'),
        ));
    }

    /**
     * Listar leads
     */
    public function list_leads($request)
    {
        $rate_check = $this->rate_limiter->apply_rate_limit($request);
        if (is_wp_error($rate_check)) {
            return $this->api_manager->error_response($rate_check, 429);
        }

        $per_page = $request->get_param('per_page') ?: 20;
        $page = $request->get_param('page') ?: 1;
        $status = $request->get_param('status');

        $args = array(
            'post_type' => 'impress_lead',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => 'publish',
        );

        if ($status) {
            $args['meta_query'][] = array(
                'key' => 'impress_lead_status',
                'value' => $status,
            );
        }

        $query = new WP_Query($args);
        $leads = array();

        foreach ($query->posts as $post) {
            $leads[] = $this->format_lead($post);
        }

        return $this->api_manager->success_response(array(
            'leads' => $leads,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ));
    }

    /**
     * Obtener lead
     */
    public function get_lead($request)
    {
        $id = intval($request->get_param('id'));
        $post = get_post($id);

        if (!$post || $post->post_type !== 'impress_lead') {
            return $this->api_manager->error_response(
                new WP_Error('not_found', 'Lead no encontrado'),
                404
            );
        }

        return $this->api_manager->success_response($this->format_lead($post));
    }

    /**
     * Crear lead
     */
    public function create_lead($request)
    {
        $schema = array(
            'name' => array('type' => 'string', 'required' => true, 'max_length' => 255),
            'email' => array('type' => 'email', 'required' => true),
            'phone' => array('type' => 'string', 'required' => false),
            'message' => array('type' => 'string', 'required' => false),
            'property_id' => array('type' => 'integer', 'required' => false),
        );

        $data = $this->api_manager->sanitize_input($request->get_json_params(), $schema);
        if (is_wp_error($data)) {
            return $this->api_manager->error_response($data, 400);
        }

        $post_data = array(
            'post_title' => $data['name'],
            'post_type' => 'impress_lead',
            'post_status' => 'publish',
            'post_content' => $data['message'] ?? '',
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return $this->api_manager->error_response($post_id, 500);
        }

        // Guardar campos ACF
        if (function_exists('update_field')) {
            update_field('impress_lead_email', $data['email'], $post_id);
            if (isset($data['phone'])) {
                update_field('impress_lead_phone', $data['phone'], $post_id);
            }
            if (isset($data['property_id'])) {
                update_field('impress_lead_property', $data['property_id'], $post_id);
            }
            update_field('impress_lead_status', 'new', $post_id);
        }

        // Disparar webhook
        $webhook_manager = Inmopress_Webhook_Manager::get_instance();
        $webhook_manager->trigger_webhook('lead.created', array('lead_id' => $post_id));

        return $this->api_manager->success_response(array(
            'id' => $post_id,
            'message' => 'Lead creado exitosamente',
        ), 201);
    }

    /**
     * Actualizar lead
     */
    public function update_lead($request)
    {
        $id = intval($request->get_param('id'));
        $post = get_post($id);

        if (!$post || $post->post_type !== 'impress_lead') {
            return $this->api_manager->error_response(
                new WP_Error('not_found', 'Lead no encontrado'),
                404
            );
        }

        $json_params = $request->get_json_params();
        $post_data = array('ID' => $id);

        if (isset($json_params['name'])) {
            $post_data['post_title'] = sanitize_text_field($json_params['name']);
        }
        if (isset($json_params['message'])) {
            $post_data['post_content'] = wp_kses_post($json_params['message']);
        }

        $result = wp_update_post($post_data);

        if (is_wp_error($result)) {
            return $this->api_manager->error_response($result, 500);
        }

        // Actualizar campos ACF
        if (function_exists('update_field')) {
            if (isset($json_params['email'])) {
                update_field('impress_lead_email', $json_params['email'], $id);
            }
            if (isset($json_params['phone'])) {
                update_field('impress_lead_phone', $json_params['phone'], $id);
            }
            if (isset($json_params['status'])) {
                update_field('impress_lead_status', $json_params['status'], $id);
            }
        }

        // Disparar webhook
        $webhook_manager = Inmopress_Webhook_Manager::get_instance();
        $webhook_manager->trigger_webhook('lead.updated', array('lead_id' => $id));

        return $this->api_manager->success_response(array(
            'id' => $id,
            'message' => 'Lead actualizado exitosamente',
        ));
    }

    /**
     * Convertir lead a cliente
     */
    public function convert_lead($request)
    {
        $id = intval($request->get_param('id'));
        $lead = get_post($id);

        if (!$lead || $lead->post_type !== 'impress_lead') {
            return $this->api_manager->error_response(
                new WP_Error('not_found', 'Lead no encontrado'),
                404
            );
        }

        $user_id = $request->get_param('user_id');
        $fields = function_exists('get_fields') ? get_fields($id) : array();

        // Crear cliente
        $client_data = array(
            'post_title' => $lead->post_title,
            'post_type' => 'impress_client',
            'post_status' => 'publish',
            'post_author' => $user_id,
        );

        $client_id = wp_insert_post($client_data);

        if (is_wp_error($client_id)) {
            return $this->api_manager->error_response($client_id, 500);
        }

        // Copiar campos
        if (function_exists('update_field')) {
            if (isset($fields['impress_lead_email'])) {
                update_field('impress_client_email', $fields['impress_lead_email'], $client_id);
            }
            if (isset($fields['impress_lead_phone'])) {
                update_field('impress_client_phone', $fields['impress_lead_phone'], $client_id);
            }
        }

        // Actualizar estado del lead
        if (function_exists('update_field')) {
            update_field('impress_lead_status', 'converted', $id);
            update_field('impress_lead_converted_to', $client_id, $id);
        }

        // Disparar webhook
        $webhook_manager = Inmopress_Webhook_Manager::get_instance();
        $webhook_manager->trigger_webhook('lead.converted', array(
            'lead_id' => $id,
            'client_id' => $client_id,
        ));

        return $this->api_manager->success_response(array(
            'lead_id' => $id,
            'client_id' => $client_id,
            'message' => 'Lead convertido a cliente exitosamente',
        ));
    }

    /**
     * Formatear lead para respuesta
     */
    private function format_lead($post)
    {
        $fields = function_exists('get_fields') ? get_fields($post->ID) : array();

        return array(
            'id' => $post->ID,
            'name' => $post->post_title,
            'email' => $fields['impress_lead_email'] ?? '',
            'phone' => $fields['impress_lead_phone'] ?? '',
            'message' => $post->post_content,
            'status' => $fields['impress_lead_status'] ?? 'new',
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
