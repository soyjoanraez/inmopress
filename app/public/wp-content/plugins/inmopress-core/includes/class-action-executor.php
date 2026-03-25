<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Action Executor - Ejecuta acciones de automatizaciones
 */
class Inmopress_Action_Executor
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
     * Ejecutar acción
     */
    public function execute($action, $trigger_data)
    {
        if (!isset($action['type'])) {
            return false;
        }

        $action_type = $action['type'];
        $config = isset($action['config']) ? $action['config'] : array();

        switch ($action_type) {
            case 'send_email':
                return $this->send_email($config, $trigger_data);

            case 'create_task':
                return $this->create_task($config, $trigger_data);

            case 'assign_agent':
                return $this->assign_agent($config, $trigger_data);

            case 'update_field':
                return $this->update_field($config, $trigger_data);

            case 'add_tag':
                return $this->add_tag($config, $trigger_data);

            case 'create_notification':
                return $this->create_notification($config, $trigger_data);

            case 'webhook':
                return $this->send_webhook($config, $trigger_data);

            case 'wait':
                return $this->wait($config, $trigger_data);

            case 'calculate_matching':
                return $this->calculate_matching($config, $trigger_data);

            default:
                return false;
        }
    }

    /**
     * Enviar email
     */
    private function send_email($config, $trigger_data)
    {
        // Por ahora solo registrar, cuando se implemente inmopress-emails se ejecutará
        $to = $this->replace_variables($config['to'] ?? '', $trigger_data);
        $template = $config['template'] ?? '';
        $subject = $this->replace_variables($config['subject'] ?? '', $trigger_data);

        // Disparar acción para que el módulo de emails lo procese
        do_action('inmopress_automation_send_email', array(
            'to' => $to,
            'template' => $template,
            'subject' => $subject,
            'trigger_data' => $trigger_data,
        ));

        return true;
    }

    /**
     * Crear tarea/evento
     */
    private function create_task($config, $trigger_data)
    {
        $post_id = isset($trigger_data['post_id']) ? $trigger_data['post_id'] : 0;
        if (!$post_id) {
            return false;
        }

        $title = $this->replace_variables($config['title'] ?? 'Nueva tarea', $trigger_data);
        $type = $config['type'] ?? 'tarea';
        $priority = $config['priority'] ?? 'media';
        $assign_to = $this->replace_variables($config['assign_to'] ?? '', $trigger_data);

        // Calcular fecha de inicio
        $due_date = isset($config['due_date']) ? $config['due_date'] : '+1 day';
        $start = $this->parse_date($due_date);
        $end = date('Y-m-d H:i:s', strtotime($start . ' +30 minutes'));

        $event_id = wp_insert_post(array(
            'post_type' => 'impress_event',
            'post_status' => 'publish',
            'post_title' => $title,
        ));

        if (!$event_id || is_wp_error($event_id)) {
            return false;
        }

        update_field('impress_event_title', $title, $event_id);
        update_field('impress_event_type', $type, $event_id);
        update_field('impress_event_status', 'pendiente', $event_id);
        update_field('impress_event_priority', $priority, $event_id);
        update_field('impress_event_start', $start, $event_id);
        update_field('impress_event_end', $end, $event_id);
        update_field('impress_event_auto_created', 1, $event_id);

        // Asignar agente
        if ($assign_to) {
            $agent_id = $this->get_agent_id($assign_to, $post_id);
            if ($agent_id) {
                update_field('impress_event_agent_rel', $agent_id, $event_id);
            }
        }

        // Relacionar con entidad origen
        $post_type = get_post_type($post_id);
        if ($post_type === 'impress_client') {
            update_field('impress_event_client_rel', $post_id, $event_id);
        } elseif ($post_type === 'impress_lead') {
            update_field('impress_event_lead_rel', $post_id, $event_id);
        } elseif ($post_type === 'impress_property') {
            update_field('impress_event_property_rel', $post_id, $event_id);
        }

        return $event_id;
    }

    /**
     * Asignar agente
     */
    private function assign_agent($config, $trigger_data)
    {
        $post_id = isset($trigger_data['post_id']) ? $trigger_data['post_id'] : 0;
        if (!$post_id) {
            return false;
        }

        $agent_identifier = $this->replace_variables($config['agent'] ?? '', $trigger_data);
        $agent_id = $this->get_agent_id($agent_identifier, $post_id);

        if (!$agent_id) {
            return false;
        }

        $post_type = get_post_type($post_id);
        if ($post_type === 'impress_property') {
            update_field('agente', $agent_id, $post_id);
        } elseif ($post_type === 'impress_client' || $post_type === 'impress_lead') {
            update_field('agente_asignado', $agent_id, $post_id);
        }

        return true;
    }

    /**
     * Actualizar campo ACF
     */
    private function update_field($config, $trigger_data)
    {
        $post_id = isset($trigger_data['post_id']) ? $trigger_data['post_id'] : 0;
        if (!$post_id) {
            return false;
        }

        $field_name = $config['field'] ?? '';
        $value = $this->replace_variables($config['value'] ?? '', $trigger_data);

        if (empty($field_name)) {
            return false;
        }

        update_field($field_name, $value, $post_id);
        return true;
    }

    /**
     * Añadir tag/taxonomía
     */
    private function add_tag($config, $trigger_data)
    {
        $post_id = isset($trigger_data['post_id']) ? $trigger_data['post_id'] : 0;
        if (!$post_id) {
            return false;
        }

        $taxonomy = $config['taxonomy'] ?? '';
        $term = $this->replace_variables($config['term'] ?? '', $trigger_data);

        if (empty($taxonomy) || empty($term)) {
            return false;
        }

        wp_set_post_terms($post_id, array($term), $taxonomy, true);
        return true;
    }

    /**
     * Crear notificación
     */
    private function create_notification($config, $trigger_data)
    {
        if (!class_exists('Inmopress_Notifications')) {
            return false;
        }

        $message = $this->replace_variables($config['message'] ?? '', $trigger_data);
        $user_id = isset($config['user_id']) ? intval($config['user_id']) : 0;

        if (empty($user_id)) {
            $post_id = isset($trigger_data['post_id']) ? $trigger_data['post_id'] : 0;
            $user_id = $this->get_user_from_post($post_id);
        }

        if (!$user_id) {
            return false;
        }

        // Usar el sistema de notificaciones si está disponible
        do_action('inmopress_create_notification', array(
            'user_id' => $user_id,
            'message' => $message,
            'type' => $config['type'] ?? 'info',
        ));

        return true;
    }

    /**
     * Enviar webhook
     */
    private function send_webhook($config, $trigger_data)
    {
        $url = $config['url'] ?? '';
        if (empty($url)) {
            return false;
        }

        $payload = isset($config['payload']) ? $config['payload'] : $trigger_data;
        $method = isset($config['method']) ? strtoupper($config['method']) : 'POST';

        $response = wp_remote_request($url, array(
            'method' => $method,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($payload),
            'timeout' => 10,
        ));

        return !is_wp_error($response);
    }

    /**
     * Wait/Delay (para workflows complejos)
     */
    private function wait($config, $trigger_data)
    {
        $delay = isset($config['delay']) ? $config['delay'] : '+1 hour';
        $delay_seconds = $this->parse_delay($delay);

        // Programar ejecución futura
        wp_schedule_single_event(time() + $delay_seconds, 'inmopress_automation_delayed', array(
            $config['next_action'] ?? array(),
            $trigger_data,
        ));

        return true;
    }

    /**
     * Calcular matching
     */
    private function calculate_matching($config, $trigger_data)
    {
        $property_id = isset($trigger_data['post_id']) ? $trigger_data['post_id'] : 0;
        if (!$property_id || get_post_type($property_id) !== 'impress_property') {
            return false;
        }

        // Disparar cálculo de matching (se implementará en Matching Engine)
        do_action('inmopress_calculate_matching', $property_id, $config);

        return true;
    }

    /**
     * Reemplazar variables en strings
     */
    private function replace_variables($text, $trigger_data)
    {
        if (empty($text)) {
            return '';
        }

        $post_id = isset($trigger_data['post_id']) ? $trigger_data['post_id'] : 0;

        // Variables comunes
        $replacements = array(
            '{{post_id}}' => $post_id,
            '{{timestamp}}' => current_time('mysql'),
            '{{date}}' => date('Y-m-d'),
            '{{time}}' => date('H:i:s'),
        );

        // Variables del post
        if ($post_id) {
            $post = get_post($post_id);
            if ($post) {
                $replacements['{{post_title}}'] = $post->post_title;
                $replacements['{{post_type}}'] = $post->post_type;
            }

            // Campos ACF comunes
            if (function_exists('get_field')) {
                $fields = array('nombre', 'apellidos', 'email', 'telefono', 'referencia');
                foreach ($fields as $field) {
                    $value = get_field($field, $post_id);
                    if ($value !== false && $value !== null) {
                        $replacements['{{' . $field . '}}'] = $value;
                    }
                }
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Obtener ID de agente
     */
    private function get_agent_id($identifier, $post_id = 0)
    {
        // Si es numérico, asumir que es ID
        if (is_numeric($identifier)) {
            return intval($identifier);
        }

        // Buscar por email o nombre
        $agents = get_posts(array(
            'post_type' => 'impress_agent',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'email',
                    'value' => $identifier,
                    'compare' => '=',
                ),
            ),
        ));

        return !empty($agents) ? $agents[0] : 0;
    }

    /**
     * Obtener usuario desde post
     */
    private function get_user_from_post($post_id)
    {
        if (!$post_id) {
            return 0;
        }

        // Buscar agente asignado
        $agent_id = get_field('agente_asignado', $post_id) ?: get_field('agente', $post_id);
        if ($agent_id) {
            $user_id = get_field('usuario_wordpress', $agent_id);
            if ($user_id) {
                return intval($user_id);
            }
        }

        return 0;
    }

    /**
     * Parsear fecha relativa
     */
    private function parse_date($date_string)
    {
        if (preg_match('/^\+(\d+)\s*(hour|hours|day|days|week|weeks)$/i', $date_string, $matches)) {
            $amount = intval($matches[1]);
            $unit = strtolower($matches[2]);
            $seconds = $amount * ($unit === 'hour' || $unit === 'hours' ? HOUR_IN_SECONDS : DAY_IN_SECONDS);
            return date('Y-m-d H:i:s', current_time('timestamp') + $seconds);
        }
        return date('Y-m-d H:i:s', strtotime($date_string));
    }

    /**
     * Parsear delay en segundos
     */
    private function parse_delay($delay_string)
    {
        if (preg_match('/^\+(\d+)\s*(second|seconds|minute|minutes|hour|hours|day|days)$/i', $delay_string, $matches)) {
            $amount = intval($matches[1]);
            $unit = strtolower($matches[2]);
            $multipliers = array(
                'second' => 1,
                'seconds' => 1,
                'minute' => 60,
                'minutes' => 60,
                'hour' => 3600,
                'hours' => 3600,
                'day' => 86400,
                'days' => 86400,
            );
            return $amount * ($multipliers[$unit] ?? 3600);
        }
        return 3600; // Default 1 hour
    }
}
