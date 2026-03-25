<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_Shortcodes
{
    /**
     * Caché estática para IDs de agente/agencia
     */
    private static $agent_cache = array();
    private static $agency_cache = array();
    private static $repeater_meta_keys = array();
    private static $action_notice = '';

    public static function register()
    {
        add_shortcode('inmopress_dashboard', array(__CLASS__, 'dashboard'));
        add_shortcode('inmopress_inmuebles_list', array(__CLASS__, 'inmuebles_list'));
        add_shortcode('inmopress_inmueble_form', array(__CLASS__, 'inmueble_form'));
        add_shortcode('inmopress_clientes_list', array(__CLASS__, 'clientes_list'));
        add_shortcode('inmopress_cliente_form', array(__CLASS__, 'cliente_form'));
        add_shortcode('inmopress_visitas_list', array(__CLASS__, 'visitas_list'));
        add_shortcode('inmopress_visita_form', array(__CLASS__, 'visita_form'));
        add_shortcode('inmopress_propietarios_list', array(__CLASS__, 'propietarios_list'));
        add_shortcode('inmopress_propietario_form', array(__CLASS__, 'propietario_form'));
        add_shortcode('inmopress_transactions_list', array(__CLASS__, 'transactions_list'));
        add_shortcode('inmopress_transaction_form', array(__CLASS__, 'transaction_form'));
        add_shortcode('inmopress_events_list', array(__CLASS__, 'events_list'));
        add_shortcode('inmopress_event_form', array(__CLASS__, 'event_form'));
        add_shortcode('inmopress_agents_list', array(__CLASS__, 'agents_list'));
        add_shortcode('inmopress_agent_form', array(__CLASS__, 'agent_form'));
        add_shortcode('inmopress_today_tasks', array(__CLASS__, 'today_tasks'));

        // Enqueue styles when shortcodes are used (or globally for now)
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_styles'));
    }

    public static function enqueue_styles()
    {
        wp_enqueue_style('inmopress-crm-styles', INMOPRESS_FRONTEND_URL . 'assets/css/crm-styles.css', array(), '1.0.0');
    }

    /**
     * Helpers de eventos
     */
    public static function get_event_type_labels()
    {
        return array(
            'tarea' => 'Tarea',
            'visita' => 'Visita',
            'llamada' => 'Llamada',
            'email' => 'Email',
            'reunion' => 'Reunión',
            'seguimiento' => 'Seguimiento',
            'valoracion' => 'Valoración',
            'firma' => 'Firma',
            'tarea_general' => 'Tarea general',
            'revisar_cliente' => 'Revisar cliente',
            'contactar_cliente' => 'Contactar cliente',
            'actualizar_propiedad' => 'Actualizar propiedad',
            'captacion' => 'Captación',
        );
    }

    public static function get_event_status_labels()
    {
        return array(
            'pendiente' => 'Pendiente',
            'en_curso' => 'En curso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
            'no_presentado' => 'No presentado',
            'vencida' => 'Vencida',
        );
    }

    public static function get_event_priority_labels()
    {
        return array(
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        );
    }

    public static function get_event_priority_colors()
    {
        return array(
            'baja' => '#94a3b8',
            'media' => '#3b82f6',
            'alta' => '#f59e0b',
            'urgente' => '#ef4444',
        );
    }

    public static function get_today_events($limit = 6)
    {
        $user = wp_get_current_user();
        $agent_id = null;
        if (in_array('agente', $user->roles)) {
            $agent_id = self::get_agent_id_by_user($user->ID);
        }

        $today_start = date('Y-m-d 00:00:00', current_time('timestamp'));
        $today_end = date('Y-m-d 23:59:59', current_time('timestamp'));

        $args = array(
            'post_type' => 'impress_event',
            'posts_per_page' => $limit,
            'orderby' => 'meta_value',
            'meta_key' => 'impress_event_start',
            'meta_type' => 'DATETIME',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'impress_event_status',
                    'value' => array('cancelada'),
                    'compare' => 'NOT IN',
                ),
                array(
                    'key' => 'impress_event_start',
                    'value' => array($today_start, $today_end),
                    'compare' => 'BETWEEN',
                    'type' => 'DATETIME',
                ),
            ),
        );

        if ($agent_id) {
            $args['meta_query'][] = array(
                'key' => 'impress_event_agent_rel',
                'value' => $agent_id,
                'compare' => '=',
            );
        }

        return new WP_Query($args);
    }

    private static function maybe_handle_event_action()
    {
        if (empty($_GET['event_action']) || empty($_GET['event_id'])) {
            return;
        }

        $action = sanitize_key(wp_unslash($_GET['event_action']));
        $event_id = absint($_GET['event_id']);
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if (!$event_id || !wp_verify_nonce($nonce, 'inmopress_event_action_' . $event_id)) {
            return;
        }

        if (!current_user_can('edit_post', $event_id)) {
            return;
        }

        $updated = false;
        if ($action === 'complete') {
            update_field('impress_event_status', 'completada', $event_id);
            $completion = get_field('impress_event_completion_time', $event_id);
            if (empty($completion)) {
                update_field('impress_event_completion_time', current_time('Y-m-d H:i:s'), $event_id);
            }
            $updated = true;
            self::$action_notice = 'Evento marcado como completado.';
        } elseif ($action === 'start') {
            update_field('impress_event_status', 'en_curso', $event_id);
            $updated = true;
            self::$action_notice = 'Evento marcado como en curso.';
        } elseif ($action === 'cancel') {
            update_field('impress_event_status', 'cancelada', $event_id);
            $updated = true;
            self::$action_notice = 'Evento cancelado.';
        }

        if ($updated) {
            $redirect_url = remove_query_arg(array('event_action', 'event_id', '_wpnonce'));
            if (!headers_sent()) {
                wp_safe_redirect($redirect_url);
                exit;
            }
        }
    }

    public static function get_action_notice()
    {
        return self::$action_notice;
    }

    /**
     * URL base del panel (admin o frontend)
     *
     * @param string $tab
     * @param array $args
     * @return string
     */
    public static function panel_url($tab = '', $args = array())
    {
        $base = admin_url('admin.php?page=inmopress-panel');
        $query = array();

        if (!empty($tab)) {
            $query['tab'] = $tab;
        }
        if (!empty($args)) {
            $query = array_merge($query, $args);
        }

        if (!empty($query)) {
            $base = add_query_arg($query, $base);
        }

        return $base;
    }

    /**
     * Activar filtro para meta_key LIKE en repetidores ACF
     *
     * @param array $keys
     * @return void
     */
    private static function enable_repeater_meta_key_like($keys)
    {
        self::$repeater_meta_keys = $keys;
        add_filter('posts_where', array(__CLASS__, 'filter_repeater_meta_key_like'), 10, 2);
    }

    /**
     * Desactivar filtro para meta_key LIKE
     *
     * @return void
     */
    private static function disable_repeater_meta_key_like()
    {
        remove_filter('posts_where', array(__CLASS__, 'filter_repeater_meta_key_like'), 10);
        self::$repeater_meta_keys = array();
    }

    /**
     * Cambia la comparación meta_key = por meta_key LIKE para keys de repetidores
     *
     * @param string $where
     * @return string
     */
    public static function filter_repeater_meta_key_like($where)
    {
        if (empty(self::$repeater_meta_keys)) {
            return $where;
        }

        global $wpdb;
        foreach (self::$repeater_meta_keys as $key) {
            $where = str_replace("{$wpdb->postmeta}.meta_key = '{$key}'", "{$wpdb->postmeta}.meta_key LIKE '{$key}'", $where);
            $where = str_replace("meta_key = '{$key}'", "meta_key LIKE '{$key}'", $where);
        }

        return $where;
    }

    /**
     * Verifica si el usuario está logueado y devuelve mensaje de error si no
     *
     * @param string $action Acción que requiere login
     * @return string|false Mensaje de error o false si está logueado
     */
    private static function require_login($action = 'ver este contenido')
    {
        if (!is_user_logged_in()) {
            return sprintf(
                '<p class="inmopress-login-required">%s</p>',
                sprintf(esc_html__('Debes estar logueado para %s.', 'inmopress'), esc_html($action))
            );
        }
        return false;
    }

    /**
     * Verifica si ACF está disponible
     *
     * @return string|false Mensaje de error o false si está disponible
     */
    private static function require_acf()
    {
        if (!function_exists('acf_form')) {
            return '<p class="inmopress-error">' . esc_html__('ACF Pro es requerido para usar este formulario.', 'inmopress') . '</p>';
        }
        return false;
    }

    /**
     * Dashboard principal
     */
    public static function dashboard($atts)
    {
        if ($error = self::require_login('ver el panel')) {
            return $error;
        }

        $user = wp_get_current_user();
        $stats = self::get_dashboard_stats();
        $recent_activity = self::get_recent_activity();

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/crm-layout.php';
        return ob_get_clean();
    }

    /**
     * Listado de inmuebles
     */
    public static function inmuebles_list($atts)
    {
        if ($error = self::require_login('ver los inmuebles')) {
            return $error;
        }

        $paged = max(1, get_query_var('paged'));
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $operation = isset($_GET['operation']) ? sanitize_key($_GET['operation']) : '';
        $city = isset($_GET['city']) ? sanitize_key($_GET['city']) : '';
        $property_type = isset($_GET['type']) ? sanitize_key($_GET['type']) : '';
        $price_min = isset($_GET['price_min']) ? floatval(str_replace(',', '.', wp_unslash($_GET['price_min']))) : 0;
        $price_max = isset($_GET['price_max']) ? floatval(str_replace(',', '.', wp_unslash($_GET['price_max']))) : 0;
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'date';
        $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

        $args = array(
            'post_type' => 'impress_property',
            'posts_per_page' => 20,
            'paged' => $paged,
            'orderby' => $orderby,
            'order' => $order,
        );

        // Filtro por búsqueda
        if (!empty($search)) {
            $args['s'] = $search;
            $args['inmopress_ref_search'] = true;
        }

        // Filtro por operación
        if (!empty($operation)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'impress_operation',
                'field' => 'slug',
                'terms' => $operation,
            );
        }

        // Filtro por ciudad
        if (!empty($city)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'impress_city',
                'field' => 'slug',
                'terms' => $city,
            );
        }

        // Filtro por tipo de propiedad
        if (!empty($property_type)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'impress_property_type',
                'field' => 'slug',
                'terms' => $property_type,
            );
        }

        // Filtro por rango de precio (venta o alquiler)
        if ($price_min || $price_max) {
            $price_clause = array('relation' => 'OR');
            $price_bounds = array();

            if ($price_min && $price_max) {
                $price_bounds = array($price_min, $price_max);
                $compare = 'BETWEEN';
            } elseif ($price_min) {
                $price_bounds = $price_min;
                $compare = '>=';
            } else {
                $price_bounds = $price_max;
                $compare = '<=';
            }

            $price_clause[] = array(
                'key' => 'precio_venta',
                'value' => $price_bounds,
                'compare' => $compare,
                'type' => 'NUMERIC',
            );
            $price_clause[] = array(
                'key' => 'precio_alquiler',
                'value' => $price_bounds,
                'compare' => $compare,
                'type' => 'NUMERIC',
            );

            if (!isset($args['meta_query'])) {
                $args['meta_query'] = array('relation' => 'AND');
            } elseif (!isset($args['meta_query']['relation'])) {
                $args['meta_query'] = array_merge(array('relation' => 'AND'), $args['meta_query']);
            }

            $args['meta_query'][] = $price_clause;
        }

        // Filtros por rol
        $user = wp_get_current_user();
        if (in_array('agente', $user->roles)) {
            // Agentes solo ven sus inmuebles asignados
            $agent_id = self::get_agent_id_by_user($user->ID);
            if ($agent_id) {
                $args['meta_query'][] = array(
                    'key' => 'agente',
                    'value' => $agent_id,
                    'compare' => '=',
                );
            } else {
                // Si no tiene agente asignado, no ver nada
                $args['post__in'] = array(0);
            }
        } elseif (in_array('agencia', $user->roles)) {
            // Agencias solo ven sus inmuebles
            $agency_id = self::get_agency_id_by_user($user->ID);
            if ($agency_id) {
                $args['meta_query'][] = array(
                    'key' => 'agencia_colaboradora',
                    'value' => $agency_id,
                    'compare' => '=',
                );
            } else {
                $args['post__in'] = array(0);
            }
        }

        if (!empty($search)) {
            self::add_ref_search_filters();
        }

        $query = new WP_Query($args);

        if (!empty($search)) {
            self::remove_ref_search_filters();
        }

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/crm-properties-list.php';
        return ob_get_clean();
    }

    /**
     * Formulario de inmueble
     */
    public static function inmueble_form($atts)
    {
        if ($error = self::require_login('crear inmuebles')) {
            return $error;
        }

        if ($error = self::require_acf()) {
            return $error;
        }

        $atts = shortcode_atts(array(
            'post_id' => 'new_post',
            'return'  => self::panel_url('properties'),
        ), $atts, 'inmopress_inmueble_form');

        // Si hay un ID en la URL, estamos editando
        $post_id = isset($_GET['edit']) ? absint($_GET['edit']) : $atts['post_id'];

        // Validación de permisos movida al template para evitar duplicación

        // ACF requiere que acf_form_head() se llame antes de cualquier output
        // Esto normalmente se maneja en el tema o con un hook temprano
        if (function_exists('acf_form_head') && !did_action('acf/form_head')) {
            acf_form_head();
        }

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/crm-property-form.php';
        return ob_get_clean();
    }

    /**
     * Listado de clientes
     */
    public static function clientes_list($atts)
    {
        if ($error = self::require_login('ver los clientes')) {
            return $error;
        }

        $atts = shortcode_atts(array(
            'context' => 'clients', // clients | leads
        ), $atts, 'inmopress_clientes_list');

        $paged = max(1, get_query_var('paged'));
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $status = isset($_GET['status']) ? sanitize_key($_GET['status']) : '';
        $property_id = isset($_GET['property']) ? absint($_GET['property']) : 0;

        $context = sanitize_key($atts['context']);
        $post_type = ($context === 'leads') ? 'impress_lead' : 'impress_client';
        $list_title = ($context === 'leads') ? __('Prospectos', 'inmopress') : __('Clientes', 'inmopress');
        $new_label = ($context === 'leads') ? __('Nuevo Prospecto', 'inmopress') : __('Nuevo Cliente', 'inmopress');
        $new_url = ($context === 'leads')
            ? admin_url('post-new.php?post_type=impress_lead')
            : self::panel_url('clients', array('new' => 1));
        $edit_tab = ($context === 'leads') ? 'leads' : 'clients';
        $hide_new_button = ($context === 'leads');

        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => 20,
            'paged' => $paged,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $meta_query_base = array('relation' => 'AND');
        $meta_query_listing = array('relation' => 'AND');
        $repeater_meta_keys = array(
            'solicitudes_%_inmueble',
            'visitas_realizadas_%_inmueble',
        );

        // Filtro por búsqueda (ACF)
        if (!empty($search)) {
            $search_clause = array(
                'relation' => 'OR',
                array(
                    'key' => 'nombre',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'apellidos',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'correo',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'email',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'telefono',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'movil',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'direccion',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'zona_interes',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
            );
            $meta_query_base[] = $search_clause;
            $meta_query_listing[] = $search_clause;
        }

        // Filtro por semáforo (ACF)
        if (!empty($status)) {
            $meta_query_listing[] = array(
                'key' => 'semaforo_estado',
                'value' => $status,
                'compare' => '=',
            );
        }

        // Filtros por rol
        $user = wp_get_current_user();
        if (in_array('agente', $user->roles)) {
            // Agentes solo ven sus clientes asignados
            $agent_id = self::get_agent_id_by_user($user->ID);
            if ($agent_id) {
                $agent_clause = array(
                    'key' => 'agente_asignado',
                    'value' => $agent_id,
                    'compare' => '=',
                );
                $meta_query_base[] = $agent_clause;
                $meta_query_listing[] = $agent_clause;
            } else {
                $args['post__in'] = array(0);
            }
        }

        if ($property_id) {
            $property_clause = array(
                'relation' => 'OR',
                array(
                    'key' => $repeater_meta_keys[0],
                    'value' => $property_id,
                    'compare' => '=',
                ),
                array(
                    'key' => $repeater_meta_keys[1],
                    'value' => $property_id,
                    'compare' => '=',
                ),
            );
            $meta_query_base[] = $property_clause;
            $meta_query_listing[] = $property_clause;
            self::enable_repeater_meta_key_like($repeater_meta_keys);
        }

        if (count($meta_query_listing) > 1) {
            $args['meta_query'] = $meta_query_listing;
        }

        $query = new WP_Query($args);

        $semaforo_labels = array(
            'hot' => 'HOT',
            'warm' => 'WARM',
            'cold' => 'COLD',
        );

        $base_count_args = array(
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'fields' => 'ids',
            'orderby' => 'date',
            'order' => 'DESC',
        );

        if (count($meta_query_base) > 1) {
            $base_count_args['meta_query'] = $meta_query_base;
        }

        $total_query = new WP_Query($base_count_args);
        $total_count = (int) $total_query->found_posts;
        wp_reset_postdata();

        $status_counts = array();
        foreach ($semaforo_labels as $status_key => $status_label) {
            $count_args = $base_count_args;
            $count_meta = $meta_query_base;
            $count_meta[] = array(
                'key' => 'semaforo_estado',
                'value' => $status_key,
                'compare' => '=',
            );
            $count_args['meta_query'] = $count_meta;
            $count_query = new WP_Query($count_args);
            $status_counts[$status_key] = (int) $count_query->found_posts;
            wp_reset_postdata();
        }

        if ($property_id) {
            self::disable_repeater_meta_key_like();
        }

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/clientes-list.php';
        return ob_get_clean();
    }

    /**
     * Formulario de cliente
     */
    public static function cliente_form($atts)
    {
        if ($error = self::require_login('crear clientes')) {
            return $error;
        }

        if ($error = self::require_acf()) {
            return $error;
        }

        $atts = shortcode_atts(array(
            'post_id' => 'new_post',
            'return'  => self::panel_url('clients'),
        ), $atts, 'inmopress_cliente_form');

        $post_id = isset($_GET['edit']) ? absint($_GET['edit']) : $atts['post_id'];

        if ($post_id !== 'new_post' && !current_user_can('edit_post', $post_id)) {
            return '<p class="inmopress-error">' . esc_html__('No tienes permisos para editar este cliente.', 'inmopress') . '</p>';
        }

        $return_url = esc_url($atts['return']);

        if (function_exists('acf_form_head') && !did_action('acf/form_head')) {
            acf_form_head();
        }

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/cliente-form.php';
        return ob_get_clean();
    }

    /**
     * Listado de visitas
     */
    public static function visitas_list($atts)
    {
        if ($error = self::require_login('ver las visitas')) {
            return $error;
        }

        $action = isset($_GET['visit_action']) ? sanitize_key($_GET['visit_action']) : '';
        if ($action === 'mark_status') {
            $visit_id = isset($_GET['visit_id']) ? absint($_GET['visit_id']) : 0;
            $status_slug = isset($_GET['visit_status']) ? sanitize_key($_GET['visit_status']) : '';
            $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
            $allowed_statuses = array('realizada', 'cancelada', 'confirmada', 'no-asistio', 'reagendada');
            $redirect_to = isset($_GET['redirect_to']) ? esc_url_raw(wp_unslash($_GET['redirect_to'])) : '';

            if ($visit_id && $status_slug && in_array($status_slug, $allowed_statuses, true)) {
                $can_edit = current_user_can('edit_post', $visit_id);

                // Agentes solo pueden actualizar sus visitas
                $user = wp_get_current_user();
                if (in_array('agente', $user->roles, true)) {
                    $agent_id = self::get_agent_id_by_user($user->ID);
                    $visit_agent = get_field('agente', $visit_id);
                    $visit_agent_id = is_object($visit_agent) ? $visit_agent->ID : absint($visit_agent);
                    if (!$agent_id || ($visit_agent_id && $visit_agent_id !== $agent_id)) {
                        $can_edit = false;
                    }
                }

                if ($can_edit && wp_verify_nonce($nonce, 'visit_status_' . $visit_id)) {
                    $term = get_term_by('slug', $status_slug, 'impress_visit_status');
                    if ($term && !is_wp_error($term)) {
                        wp_set_post_terms($visit_id, array($term->term_id), 'impress_visit_status', false);
                    }
                }
            }

            if (!headers_sent()) {
                $redirect_url = remove_query_arg(array('visit_action', 'visit_id', 'visit_status', '_wpnonce', 'redirect_to'), wp_unslash($_SERVER['REQUEST_URI']));
                if (!empty($redirect_to)) {
                    $redirect_url = wp_validate_redirect($redirect_to, $redirect_url);
                }
                wp_safe_redirect($redirect_url);
                exit;
            }
        }

        $paged = max(1, get_query_var('paged'));
        $status = isset($_GET['status']) ? sanitize_key($_GET['status']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        $agent = isset($_GET['agent']) ? absint($_GET['agent']) : 0;
        $client = isset($_GET['client']) ? absint($_GET['client']) : 0;
        $property = isset($_GET['property']) ? absint($_GET['property']) : 0;
        $property_search = isset($_GET['property_search']) ? sanitize_text_field(wp_unslash($_GET['property_search'])) : '';

        $args = array(
            'post_type' => 'impress_visit',
            'posts_per_page' => 20,
            'paged' => $paged,
            'orderby' => 'meta_value',
            'meta_key' => 'fecha_hora',
            'meta_type' => 'DATETIME',
            'order' => 'DESC'
        );

        $meta_query = array('relation' => 'AND');

        // Filtro por estado
        if (!empty($status)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'impress_visit_status',
                'field' => 'slug',
                'terms' => $status,
            );
        }

        // Filtro por fecha
        if (!empty($date_from) || !empty($date_to)) {
            $from = !empty($date_from) ? $date_from . ' 00:00:00' : '1970-01-01 00:00:00';
            $to = !empty($date_to) ? $date_to . ' 23:59:59' : '2099-12-31 23:59:59';
            $meta_query[] = array(
                'key' => 'fecha_hora',
                'value' => array($from, $to),
                'compare' => 'BETWEEN',
                'type' => 'DATETIME'
            );
        }

        // Filtros por rol
        $user = wp_get_current_user();
        if (in_array('agente', $user->roles)) {
            // Agentes solo ven sus visitas
            $agent_id = self::get_agent_id_by_user($user->ID);
            if ($agent_id) {
                $agent = $agent_id;
            } else {
                $args['post__in'] = array(0);
            }
        }

        if ($agent) {
            $meta_query[] = array(
                'key' => 'agente',
                'value' => $agent,
                'compare' => '=',
            );
        }

        if ($client) {
            $meta_query[] = array(
                'key' => 'cliente',
                'value' => $client,
                'compare' => '=',
            );
        }

        if ($property) {
            $meta_query[] = array(
                'key' => 'inmueble',
                'value' => $property,
                'compare' => '=',
            );
        } elseif (!empty($property_search)) {
            $property_ids = get_posts(array(
                'post_type' => 'impress_property',
                'posts_per_page' => 200,
                'fields' => 'ids',
                's' => $property_search,
            ));

            $property_ids_by_ref = get_posts(array(
                'post_type' => 'impress_property',
                'posts_per_page' => 200,
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'referencia',
                        'value' => $property_search,
                        'compare' => 'LIKE',
                    ),
                ),
            ));

            $property_ids = array_values(array_unique(array_merge($property_ids, $property_ids_by_ref)));

            if (!empty($property_ids)) {
                $meta_query[] = array(
                    'key' => 'inmueble',
                    'value' => $property_ids,
                    'compare' => 'IN',
                );
            } else {
                $args['post__in'] = array(0);
            }
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        $query = new WP_Query($args);

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/visitas-list.php';
        return ob_get_clean();
    }

    /**
     * Listado de eventos
     */
    public static function events_list($atts)
    {
        if ($error = self::require_login('ver los eventos')) {
            return $error;
        }

        self::maybe_handle_event_action();

        $paged = max(1, get_query_var('paged'));
        $status = isset($_GET['status']) ? sanitize_key($_GET['status']) : '';
        $type = isset($_GET['type']) ? sanitize_key($_GET['type']) : '';
        $priority = isset($_GET['priority']) ? sanitize_key($_GET['priority']) : '';
        $agent_filter = isset($_GET['agent']) ? absint($_GET['agent']) : 0;
        $date_from = isset($_GET['date_from']) ? sanitize_text_field(wp_unslash($_GET['date_from'])) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field(wp_unslash($_GET['date_to'])) : '';
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $view = isset($_GET['view']) ? sanitize_key($_GET['view']) : 'list';

        $args = array(
            'post_type' => 'impress_event',
            'posts_per_page' => 20,
            'paged' => $paged,
            'orderby' => 'meta_value',
            'meta_key' => 'impress_event_start',
            'meta_type' => 'DATETIME',
            'order' => 'DESC',
        );

        if (!empty($search)) {
            $args['s'] = $search;
        }

        $meta_query = array('relation' => 'AND');
        if (!empty($status)) {
            $meta_query[] = array(
                'key' => 'impress_event_status',
                'value' => $status,
                'compare' => '=',
            );
        }
        if (!empty($type)) {
            $meta_query[] = array(
                'key' => 'impress_event_type',
                'value' => $type,
                'compare' => '=',
            );
        }
        if (!empty($priority)) {
            $meta_query[] = array(
                'key' => 'impress_event_priority',
                'value' => $priority,
                'compare' => '=',
            );
        }

        if (!empty($date_from) || !empty($date_to)) {
            $from = !empty($date_from) ? date('Y-m-d 00:00:00', strtotime($date_from)) : null;
            $to = !empty($date_to) ? date('Y-m-d 23:59:59', strtotime($date_to)) : null;
            if ($from && $to) {
                $meta_query[] = array(
                    'key' => 'impress_event_start',
                    'value' => array($from, $to),
                    'compare' => 'BETWEEN',
                    'type' => 'DATETIME',
                );
            } elseif ($from) {
                $meta_query[] = array(
                    'key' => 'impress_event_start',
                    'value' => $from,
                    'compare' => '>=',
                    'type' => 'DATETIME',
                );
            } elseif ($to) {
                $meta_query[] = array(
                    'key' => 'impress_event_start',
                    'value' => $to,
                    'compare' => '<=',
                    'type' => 'DATETIME',
                );
            }
        }

        // Filtros por rol
        $user = wp_get_current_user();
        if (in_array('agente', $user->roles)) {
            $agent_id = self::get_agent_id_by_user($user->ID);
            if ($agent_id) {
                $meta_query[] = array(
                    'key' => 'impress_event_agent_rel',
                    'value' => $agent_id,
                    'compare' => '=',
                );
            } else {
                $args['post__in'] = array(0);
            }
        } elseif ($agent_filter) {
            $meta_query[] = array(
                'key' => 'impress_event_agent_rel',
                'value' => $agent_filter,
                'compare' => '=',
            );
        }

        $agents = array();
        if (!in_array('agente', $user->roles)) {
            $agents = get_posts(array(
                'post_type' => 'impress_agent',
                'posts_per_page' => 200,
                'orderby' => 'title',
                'order' => 'ASC',
            ));
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        $query = new WP_Query($args);

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/events-list.php';
        return ob_get_clean();
    }

    /**
     * Formulario de evento
     */
    public static function event_form($atts)
    {
        if ($error = self::require_login('crear eventos')) {
            return $error;
        }

        if ($error = self::require_acf()) {
            return $error;
        }

        $atts = shortcode_atts(array(
            'post_id' => 'new_post',
            'return'  => self::panel_url('events'),
        ), $atts, 'inmopress_event_form');

        $post_id = isset($_GET['edit']) ? absint($_GET['edit']) : $atts['post_id'];

        if ($post_id !== 'new_post' && !current_user_can('edit_post', $post_id)) {
            return '<p class="inmopress-error">' . esc_html__('No tienes permisos para editar este evento.', 'inmopress') . '</p>';
        }

        $return_url = esc_url($atts['return']);
        $field_groups = array('group_event_info');

        if (function_exists('acf_form_head') && !did_action('acf/form_head')) {
            acf_form_head();
        }

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/event-form.php';
        return ob_get_clean();
    }

    /**
     * Tareas de hoy (dashboard)
     */
    public static function today_tasks($atts)
    {
        if ($error = self::require_login('ver las tareas')) {
            return $error;
        }

        self::maybe_handle_event_action();

        $atts = shortcode_atts(array(
            'limit' => 6,
        ), $atts, 'inmopress_today_tasks');

        $query = self::get_today_events((int) $atts['limit']);
        $type_labels = self::get_event_type_labels();
        $priority_labels = self::get_event_priority_labels();
        $priority_colors = self::get_event_priority_colors();

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/today-tasks.php';
        return ob_get_clean();
    }

    /**
     * Formulario de visita
     */
    public static function visita_form($atts)
    {
        if ($error = self::require_login('crear visitas')) {
            return $error;
        }

        if ($error = self::require_acf()) {
            return $error;
        }

        $atts = shortcode_atts(array(
            'post_id' => 'new_post',
            'return'  => self::panel_url('visits'),
        ), $atts, 'inmopress_visita_form');

        $post_id = isset($_GET['edit']) ? absint($_GET['edit']) : $atts['post_id'];

        if ($post_id !== 'new_post' && !current_user_can('edit_post', $post_id)) {
            return '<p class="inmopress-error">' . esc_html__('No tienes permisos para editar esta visita.', 'inmopress') . '</p>';
        }

        $return_url = esc_url($atts['return']);

        if (function_exists('acf_form_head') && !did_action('acf/form_head')) {
            acf_form_head();
        }

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/visita-form.php';
        return ob_get_clean();
    }

    /**
     * Listado de propietarios
     */
    public static function propietarios_list($atts)
    {
        if ($error = self::require_login('ver los propietarios')) {
            return $error;
        }

        $paged = max(1, get_query_var('paged'));
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

        $args = array(
            'post_type' => 'impress_owner',
            'posts_per_page' => 20,
            'paged' => $paged,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        // Filtro por búsqueda
        if (!empty($search)) {
            $args['s'] = $search;
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => 'nombre',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'apellidos',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'email',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'correo',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'telefono',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'dni_cif',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
            );
        }

        // Filtros por rol
        $user = wp_get_current_user();
        if (in_array('agente', $user->roles)) {
            // Agentes solo ven propietarios de sus inmuebles
            $agent_id = self::get_agent_id_by_user($user->ID);
            if ($agent_id) {
                // Obtener inmuebles del agente
                $properties = get_posts(array(
                    'post_type' => 'impress_property',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => 'agente',
                            'value' => $agent_id,
                            'compare' => '=',
                        ),
                    ),
                    'fields' => 'ids',
                ));

                if (!empty($properties)) {
                    // Obtener propietarios de esos inmuebles
                    $owner_ids = array();
                    foreach ($properties as $prop_id) {
                        $owner = get_field('propietario', $prop_id);
                        if ($owner && is_object($owner)) {
                            $owner_ids[] = $owner->ID;
                        }
                    }
                    $owner_ids = array_unique($owner_ids);
                    if (!empty($owner_ids)) {
                        $args['post__in'] = $owner_ids;
                    } else {
                        $args['post__in'] = array(0);
                    }
                } else {
                    $args['post__in'] = array(0);
                }
            } else {
                $args['post__in'] = array(0);
            }
        }

        $query = new WP_Query($args);

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/propietarios-list.php';
        return ob_get_clean();
    }

    // Helper methods
    private static function get_dashboard_stats()
    {
        $user = wp_get_current_user();
        $user_id = $user->ID;
        $agency_id = self::get_agency_id_by_user($user_id);

        // Usar clase de KPIs si está disponible
        if (class_exists('Inmopress_Dashboard_KPIs')) {
            $kpis = Inmopress_Dashboard_KPIs::get_kpis($user_id, $agency_id);
            return array(
                'inmuebles' => $kpis['properties'],
                'clientes' => $kpis['clients'],
                'visitas' => $kpis['visits'],
                'propietarios' => 0, // TODO: calcular propietarios
                'leads' => $kpis['leads'],
                'commission_total' => $kpis['commission_total'],
                'avg_price' => $kpis['avg_price'],
                'opportunities' => $kpis['opportunities'],
                'conversion_rate' => $kpis['conversion_rate'],
                'new_properties' => $kpis['new_properties'],
                'new_clients' => $kpis['new_clients'],
            );
        }

        // Fallback al método anterior
        $stats = array(
            'inmuebles' => 0,
            'clientes' => 0,
            'visitas' => 0,
            'propietarios' => 0,
        );

        // Si es administrador, cuenta todo
        if (current_user_can('manage_options')) {
            $stats['inmuebles'] = wp_count_posts('impress_property')->publish;
            $stats['clientes'] = wp_count_posts('impress_client')->publish;
            $stats['visitas'] = wp_count_posts('impress_visit')->publish;
            $stats['propietarios'] = wp_count_posts('impress_owner')->publish;
        } else {
            // Para otros roles, filtrar por asignaciones
            if (in_array('agente', $user->roles)) {
                $agent_id = self::get_agent_id_by_user($user->ID);
                if ($agent_id) {
                    $stats['inmuebles'] = count(get_posts(array(
                        'post_type' => 'impress_property',
                        'posts_per_page' => -1,
                        'meta_query' => array(
                            array(
                                'key' => 'agente',
                                'value' => $agent_id,
                                'compare' => '=',
                            ),
                        ),
                        'fields' => 'ids',
                    )));

                    $stats['clientes'] = count(get_posts(array(
                        'post_type' => 'impress_client',
                        'posts_per_page' => -1,
                        'meta_query' => array(
                            array(
                                'key' => 'agente_asignado',
                                'value' => $agent_id,
                                'compare' => '=',
                            ),
                        ),
                        'fields' => 'ids',
                    )));

                    $stats['visitas'] = count(get_posts(array(
                        'post_type' => 'impress_visit',
                        'posts_per_page' => -1,
                        'meta_query' => array(
                            array(
                                'key' => 'agente',
                                'value' => $agent_id,
                                'compare' => '=',
                            ),
                        ),
                        'fields' => 'ids',
                    )));
                }
            } else {
                // Para otros roles, mostrar todos
                $stats['inmuebles'] = wp_count_posts('impress_property')->publish;
                $stats['clientes'] = wp_count_posts('impress_client')->publish;
                $stats['visitas'] = wp_count_posts('impress_visit')->publish;
                $stats['propietarios'] = wp_count_posts('impress_owner')->publish;
            }
        }

        return $stats;
    }

    /**
     * Obtener actividad reciente
     */
    public static function get_recent_activity()
    {
        $activity = array();

        // Inmuebles recientes
        $recent_properties = get_posts(array(
            'post_type' => 'impress_property',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        foreach ($recent_properties as $post) {
            $activity[] = array(
                'type' => 'Inmueble',
                'title' => $post->post_title,
                'link' => get_edit_post_link($post->ID),
                'date' => human_time_diff(strtotime($post->post_date), current_time('timestamp')) . ' atrás',
            );
        }

        // Visitas recientes
        $recent_visits = get_posts(array(
            'post_type' => 'impress_visit',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        foreach ($recent_visits as $post) {
            $activity[] = array(
                'type' => 'Visita',
                'title' => $post->post_title,
                'link' => get_edit_post_link($post->ID),
                'date' => human_time_diff(strtotime($post->post_date), current_time('timestamp')) . ' atrás',
            );
        }

        // Ordenar por fecha
        usort($activity, function ($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return array_slice($activity, 0, 10);
    }

    public static function get_operation_terms()
    {
        $terms = get_the_terms(get_the_ID(), 'impress_operation');
        if ($terms && !is_wp_error($terms)) {
            return $terms[0]->name;
        }
        return '-';
    }

    public static function get_type_terms()
    {
        $terms = get_the_terms(get_the_ID(), 'impress_property_type');
        if ($terms && !is_wp_error($terms)) {
            return $terms[0]->name;
        }
        return '-';
    }

    public static function get_price()
    {
        $precio_venta = get_field('precio_venta');
        $precio_alquiler = get_field('precio_alquiler');
        if ($precio_venta) {
            return number_format($precio_venta, 0, ',', '.') . ' €';
        } elseif ($precio_alquiler) {
            return number_format($precio_alquiler, 0, ',', '.') . ' €/mes';
        }
        return '-';
    }

    /**
     * Añade filtros para buscar inmuebles por referencia o nombre.
     */
    private static function add_ref_search_filters()
    {
        add_filter('posts_join', array(__CLASS__, 'filter_ref_search_join'), 10, 2);
        add_filter('posts_search', array(__CLASS__, 'filter_ref_search_where'), 10, 2);
        add_filter('posts_distinct', array(__CLASS__, 'filter_ref_search_distinct'), 10, 2);
    }

    private static function remove_ref_search_filters()
    {
        remove_filter('posts_join', array(__CLASS__, 'filter_ref_search_join'), 10);
        remove_filter('posts_search', array(__CLASS__, 'filter_ref_search_where'), 10);
        remove_filter('posts_distinct', array(__CLASS__, 'filter_ref_search_distinct'), 10);
    }

    public static function filter_ref_search_join($join, $query)
    {
        if (!$query->get('inmopress_ref_search')) {
            return $join;
        }

        global $wpdb;
        if (strpos($join, 'inmopress_ref_pm') === false) {
            $join .= " LEFT JOIN {$wpdb->postmeta} AS inmopress_ref_pm ON ({$wpdb->posts}.ID = inmopress_ref_pm.post_id AND inmopress_ref_pm.meta_key = 'referencia')";
        }

        return $join;
    }

    public static function filter_ref_search_where($search, $query)
    {
        if (!$query->get('inmopress_ref_search')) {
            return $search;
        }

        global $wpdb;
        $term = $query->get('s');
        if ($term === '') {
            return $search;
        }

        $like = '%' . $wpdb->esc_like($term) . '%';

        return $wpdb->prepare(
            " AND ({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_excerpt LIKE %s OR {$wpdb->posts}.post_content LIKE %s OR inmopress_ref_pm.meta_value LIKE %s)",
            $like,
            $like,
            $like,
            $like
        );
    }

    public static function filter_ref_search_distinct($distinct, $query)
    {
        if (!$query->get('inmopress_ref_search')) {
            return $distinct;
        }

        return 'DISTINCT';
    }

    /**
     * Obtener posibles matches para una propiedad (ranking simple).
     *
     * @param int $property_id
     * @param int $limit
     * @return array
     */
    public static function get_opportunity_matches_for_property($property_id, $limit = 5)
    {
        $property_id = absint($property_id);
        $limit = max(1, absint($limit));
        if (!$property_id) {
            return array();
        }

        $candidates = get_posts(array(
            'post_type' => 'impress_client',
            'posts_per_page' => max(25, $limit * 5),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ));

        $results = array();
        foreach ($candidates as $client_id) {
            $score = self::score_property_client_match($property_id, $client_id);
            if ($score > 0) {
                $results[] = array(
                    'client_id' => $client_id,
                    'score' => $score,
                );
            }
        }

        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Obtener posibles matches para un cliente (ranking simple).
     *
     * @param int $client_id
     * @param int $limit
     * @return array
     */
    public static function get_opportunity_matches_for_client($client_id, $limit = 8)
    {
        $client_id = absint($client_id);
        $limit = max(1, absint($limit));
        if (!$client_id) {
            return array();
        }

        $candidates = get_posts(array(
            'post_type' => 'impress_property',
            'posts_per_page' => max(25, $limit * 5),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ));

        $results = array();
        foreach ($candidates as $property_id) {
            $score = self::score_property_client_match($property_id, $client_id);
            if ($score > 0) {
                $results[] = array(
                    'property_id' => $property_id,
                    'score' => $score,
                );
            }
        }

        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Score simple propiedad/cliente (0-100). Devuelve 0 si no encaja.
     *
     * @param int $property_id
     * @param int $client_id
     * @return int
     */
    private static function score_property_client_match($property_id, $client_id)
    {
        $property_id = absint($property_id);
        $client_id = absint($client_id);

        if (!$property_id || !$client_id) {
            return 0;
        }

        $score = 0;

        $purpose = get_field('proposito', $property_id);
        $interest = get_field('interes', $client_id);

        if (!empty($interest) && !empty($purpose)) {
            $valid_interest = ($purpose === 'venta' && in_array($interest, array('compra', 'inversion'), true))
                || ($purpose === 'alquiler' && $interest === 'alquiler');

            if (!$valid_interest) {
                return 0;
            }
        }

        $city_terms = get_the_terms($property_id, 'impress_city');
        $property_city_ids = array();
        if ($city_terms && !is_wp_error($city_terms)) {
            foreach ($city_terms as $term) {
                $property_city_ids[] = (int) $term->term_id;
            }
        }

        $client_zones = get_field('zona_interes', $client_id);
        if (!empty($client_zones)) {
            $client_zone_ids = is_array($client_zones) ? array_map('absint', $client_zones) : array(absint($client_zones));
            $zone_match = array_intersect($client_zone_ids, $property_city_ids);
            if (empty($zone_match)) {
                return 0;
            }
            $score += 30;
        }

        $price = get_field('precio_venta', $property_id);
        if (!$price) {
            $price = get_field('precio_alquiler', $property_id);
        }
        $price = $price ? (float) $price : 0;

        $budget_min = (float) get_field('presupuesto_min', $client_id);
        $budget_max = (float) get_field('presupuesto_max', $client_id);

        if ($price > 0 && $budget_max > 0) {
            if ($price <= $budget_max) {
                $score += ($price <= ($budget_max * 0.9)) ? 25 : 20;
            } elseif ($price <= ($budget_max * 1.1)) {
                $score += 10;
            } else {
                return 0;
            }
        }

        $bedrooms_min = (int) get_field('dormitorios_min', $client_id);
        $bedrooms = (int) get_field('dormitorios', $property_id);
        if ($bedrooms_min > 0 && $bedrooms > 0) {
            if ($bedrooms >= $bedrooms_min) {
                $score += 10;
            } else {
                return 0;
            }
        }

        return (int) min(100, $score);
    }

    public static function get_city_terms()
    {
        $terms = get_the_terms(get_the_ID(), 'impress_city');
        if ($terms && !is_wp_error($terms)) {
            return $terms[0]->name;
        }
        return '-';
    }

    public static function get_lead_status_terms()
    {
        $terms = get_the_terms(get_the_ID(), 'impress_lead_status');
        if ($terms && !is_wp_error($terms)) {
            return $terms[0]->name;
        }
        return '-';
    }

    public static function get_visit_status_terms()
    {
        $terms = get_the_terms(get_the_ID(), 'impress_visit_status');
        if ($terms && !is_wp_error($terms)) {
            return $terms[0]->name;
        }
        return '-';
    }

    public static function get_related_property()
    {
        $property = get_field('inmueble');
        if ($property) {
            return '<a href="' . esc_url(get_edit_post_link($property->ID)) . '">' . esc_html($property->post_title) . '</a>';
        }
        return '-';
    }

    public static function get_related_client()
    {
        $client = get_field('cliente');
        if ($client) {
            return '<a href="' . esc_url(get_edit_post_link($client->ID)) . '">' . esc_html($client->post_title) . '</a>';
        }
        return '-';
    }

    /**
     * Obtener ID del agente por ID de usuario WordPress (con caché)
     *
     * @param int $user_id
     * @return int|false
     */
    public static function get_agent_id_by_user($user_id)
    {
        $user_id = absint($user_id);

        // Retornar desde caché si existe
        if (isset(self::$agent_cache[$user_id])) {
            return self::$agent_cache[$user_id];
        }

        $agents = get_posts(array(
            'post_type'      => 'impress_agent',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'     => 'usuario_wordpress',
                    'value'   => $user_id,
                    'compare' => '=',
                ),
            ),
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ));

        $result = !empty($agents) ? $agents[0] : false;
        self::$agent_cache[$user_id] = $result;

        return $result;
    }

    /**
     * Obtener ID de la agencia por ID de usuario WordPress (con caché)
     *
     * @param int $user_id
     * @return int|false
     */
    public static function get_agency_id_by_user($user_id)
    {
        $user_id = absint($user_id);

        // Retornar desde caché si existe
        if (isset(self::$agency_cache[$user_id])) {
            return self::$agency_cache[$user_id];
        }

        $agencies = get_posts(array(
            'post_type'      => 'impress_agency',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'     => 'usuario_wordpress',
                    'value'   => $user_id,
                    'compare' => '=',
                ),
            ),
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ));

        $result = !empty($agencies) ? $agencies[0] : false;
        self::$agency_cache[$user_id] = $result;

        return $result;
    }

    /**
     * Listado de agentes
     */
    public static function agents_list($atts)
    {
        if ($error = self::require_login('ver los agentes')) {
            return $error;
        }

        $paged = max(1, get_query_var('paged'));
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $agency_filter = isset($_GET['agency']) ? absint($_GET['agency']) : 0;
        $active_filter = isset($_GET['active']) ? sanitize_key($_GET['active']) : '';

        $args = array(
            'post_type' => 'impress_agent',
            'posts_per_page' => 20,
            'paged' => $paged,
            'orderby' => 'title',
            'order' => 'ASC'
        );

        $meta_query = array('relation' => 'AND');

        // Filtro por búsqueda
        if (!empty($search)) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => 'nombre',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'apellidos',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'email',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'telefono',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
            );
            // También buscar en el título del post
            $args['s'] = $search;
        }

        // Filtro por agencia
        if ($agency_filter > 0) {
            $meta_query[] = array(
                'key' => 'agencia_relacionada',
                'value' => $agency_filter,
                'compare' => '=',
            );
        }

        // Filtro por estado activo
        if ($active_filter !== '') {
            $meta_query[] = array(
                'key' => 'activo',
                'value' => $active_filter === '1' ? '1' : '0',
                'compare' => '=',
            );
        }

        // Filtros por rol
        $user = wp_get_current_user();
        if (in_array('agente', $user->roles)) {
            // Agentes solo ven su propio perfil
            $agent_id = self::get_agent_id_by_user($user->ID);
            if ($agent_id) {
                $args['p'] = $agent_id;
            } else {
                $args['post__in'] = array(0);
            }
        } elseif (in_array('agencia', $user->roles)) {
            // Agencias ven solo sus agentes
            $agency_id = self::get_agency_id_by_user($user->ID);
            if ($agency_id) {
                $meta_query[] = array(
                    'key' => 'agencia_relacionada',
                    'value' => $agency_id,
                    'compare' => '=',
                );
            } else {
                $args['post__in'] = array(0);
            }
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        $query = new WP_Query($args);

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/crm-agents-list.php';
        return ob_get_clean();
    }

    /**
     * Formulario de agente
     */
    public static function agent_form($atts)
    {
        if ($error = self::require_login('crear agentes')) {
            return $error;
        }

        if ($error = self::require_acf()) {
            return $error;
        }

        $atts = shortcode_atts(array(
            'post_id' => 'new_post',
            'return'  => self::panel_url('agents'),
        ), $atts, 'inmopress_agent_form');

        // Si hay un ID en la URL, estamos editando
        $post_id = isset($_GET['edit']) ? absint($_GET['edit']) : (isset($_GET['agent_id']) ? absint($_GET['agent_id']) : $atts['post_id']);

        // Verificar permisos
        if ($post_id !== 'new_post') {
            $post = get_post($post_id);
            if (!$post || $post->post_type !== 'impress_agent') {
                return '<p class="inmopress-error">' . esc_html__('El agente no existe.', 'inmopress') . '</p>';
            }
            if (!current_user_can('edit_post', $post_id)) {
                return '<p class="inmopress-error">' . esc_html__('No tienes permisos para editar este agente.', 'inmopress') . '</p>';
            }
        } else {
            if (!current_user_can('edit_posts')) {
                return '<p class="inmopress-error">' . esc_html__('No tienes permisos para crear agentes.', 'inmopress') . '</p>';
            }
        }

        // ACF requiere que acf_form_head() se llame antes de cualquier output
        if (function_exists('acf_form_head') && !did_action('acf/form_head')) {
            acf_form_head();
        }

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/crm-agent-form.php';
        return ob_get_clean();
    }

    /**
     * Formulario de propietario
     */
    public static function propietario_form($atts)
    {
        if ($error = self::require_login('crear propietarios')) {
            return $error;
        }

        if ($error = self::require_acf()) {
            return $error;
        }

        $atts = shortcode_atts(array(
            'post_id' => 'new_post',
            'return'  => self::panel_url('owners'),
        ), $atts, 'inmopress_propietario_form');

        $post_id = isset($_GET['edit']) ? absint($_GET['edit']) : $atts['post_id'];

        if ($post_id !== 'new_post' && !current_user_can('edit_post', $post_id)) {
            return '<p class="inmopress-error">' . esc_html__('No tienes permisos para editar este propietario.', 'inmopress') . '</p>';
        }

        $return_url = esc_url($atts['return']);
        $field_groups = array('group_owner_info');

        if (function_exists('acf_form_head') && !did_action('acf/form_head')) {
            acf_form_head();
        }

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/propietario-form.php';
        return ob_get_clean();
    }

    /**
     * Listado de transacciones
     */
    public static function transactions_list($atts)
    {
        if ($error = self::require_login('ver las transacciones')) {
            return $error;
        }

        $paged = max(1, get_query_var('paged'));

        $args = array(
            'post_type' => 'impress_transaction',
            'posts_per_page' => 20,
            'paged' => $paged,
            'orderby' => 'meta_value',
            'meta_key' => 'closing_date',
            'order' => 'DESC'
        );

        // Agentes solo ven sus transacciones
        $user = wp_get_current_user();
        if (in_array('agente', $user->roles)) {
            $agent_id = self::get_agent_id_by_user($user->ID);
            if ($agent_id) {
                $args['meta_query'][] = array(
                    'key' => 'assigned_agent',
                    'value' => $agent_id,
                    'compare' => '=',
                );
            } else {
                $args['post__in'] = array(0);
            }
        }

        $query = new WP_Query($args);

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/transactions-list.php';
        return ob_get_clean();
    }

    /**
     * Formulario de transacción
     */
    public static function transaction_form($atts)
    {
        if ($error = self::require_login('crear transacciones')) {
            return $error;
        }

        if ($error = self::require_acf()) {
            return $error;
        }

        $atts = shortcode_atts(array(
            'post_id' => 'new_post',
            'return'  => self::panel_url('transactions'),
        ), $atts, 'inmopress_transaction_form');

        $post_id = isset($_GET['edit']) ? absint($_GET['edit']) : $atts['post_id'];

        if ($post_id !== 'new_post' && !current_user_can('edit_post', $post_id)) {
            return '<p class="inmopress-error">' . esc_html__('No tienes permisos para editar esta transacción.', 'inmopress') . '</p>';
        }

        $return_url = esc_url($atts['return']);

        if (function_exists('acf_form_head') && !did_action('acf/form_head')) {
            acf_form_head();
        }

        ob_start();
        include INMOPRESS_FRONTEND_PATH . 'templates/transaction-form.php';
        return ob_get_clean();
    }
}
