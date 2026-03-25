<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Automation Manager - Gestiona y ejecuta automatizaciones
 */
class Inmopress_Automation_Manager
{
    private static $instance = null;
    private $condition_evaluator;
    private $action_executor;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->condition_evaluator = Inmopress_Condition_Evaluator::get_instance();
        $this->action_executor = Inmopress_Action_Executor::get_instance();
    }

    /**
     * Ejecutar automatización
     */
    public function execute_automation($automation_id, $trigger_data)
    {
        global $wpdb;

        $start_time = microtime(true);

        $automation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}inmopress_automations WHERE id = %d",
            $automation_id
        ));

        if (!$automation || !$automation->is_active) {
            return false;
        }

        // Evaluar condiciones
        $conditions = json_decode($automation->conditions, true);
        $conditions_met = $this->condition_evaluator->evaluate($conditions, $trigger_data);

        if (!$conditions_met) {
            $this->log_execution(
                $automation_id,
                $trigger_data,
                false,
                0,
                'conditions_not_met',
                null,
                microtime(true) - $start_time
            );
            return false;
        }

        // Ejecutar acciones
        $actions = json_decode($automation->actions, true);
        $actions_executed = 0;
        $errors = array();

        if (is_array($actions)) {
            foreach ($actions as $action) {
                try {
                    $result = $this->action_executor->execute($action, $trigger_data);
                    if ($result) {
                        $actions_executed++;
                    }
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        $execution_time = microtime(true) - $start_time;
        $status = empty($errors) ? 'success' : 'partial_success';

        // Log
        $this->log_execution(
            $automation_id,
            $trigger_data,
            true,
            $actions_executed,
            $status,
            empty($errors) ? null : implode('; ', $errors),
            $execution_time
        );

        // Actualizar contador
        $wpdb->update(
            $wpdb->prefix . 'inmopress_automations',
            array(
                'run_count' => $automation->run_count + 1,
                'last_run_at' => current_time('mysql'),
            ),
            array('id' => $automation_id)
        );

        return true;
    }

    /**
     * Registrar log de ejecución
     */
    private function log_execution($automation_id, $trigger_data, $conditions_met, $actions_executed, $status, $error_message = null, $execution_time = 0)
    {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'inmopress_automation_logs',
            array(
                'automation_id' => $automation_id,
                'trigger_data' => json_encode($trigger_data),
                'conditions_met' => $conditions_met ? 1 : 0,
                'actions_executed' => $actions_executed,
                'status' => $status,
                'error_message' => $error_message,
                'execution_time' => $execution_time,
                'created_at' => current_time('mysql'),
            )
        );
    }

    /**
     * Crear automatización
     */
    public function create_automation($data)
    {
        global $wpdb;

        $defaults = array(
            'name' => '',
            'description' => '',
            'trigger_type' => '',
            'trigger_config' => array(),
            'conditions' => array(),
            'actions' => array(),
            'is_active' => 1,
        );

        $data = wp_parse_args($data, $defaults);

        if (empty($data['name']) || empty($data['trigger_type'])) {
            return false;
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'inmopress_automations',
            array(
                'name' => sanitize_text_field($data['name']),
                'description' => sanitize_textarea_field($data['description']),
                'trigger_type' => sanitize_text_field($data['trigger_type']),
                'trigger_config' => json_encode($data['trigger_config']),
                'conditions' => json_encode($data['conditions']),
                'actions' => json_encode($data['actions']),
                'is_active' => $data['is_active'] ? 1 : 0,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            )
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Actualizar automatización
     */
    public function update_automation($automation_id, $data)
    {
        global $wpdb;

        $update_data = array();
        $allowed_fields = array('name', 'description', 'trigger_type', 'trigger_config', 'conditions', 'actions', 'is_active');

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, array('trigger_config', 'conditions', 'actions'))) {
                    $update_data[$field] = json_encode($data[$field]);
                } elseif ($field === 'is_active') {
                    $update_data[$field] = $data[$field] ? 1 : 0;
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                }
            }
        }

        $update_data['updated_at'] = current_time('mysql');

        if (empty($update_data)) {
            return false;
        }

        return $wpdb->update(
            $wpdb->prefix . 'inmopress_automations',
            $update_data,
            array('id' => $automation_id)
        ) !== false;
    }

    /**
     * Eliminar automatización
     */
    public function delete_automation($automation_id)
    {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'inmopress_automations',
            array('id' => $automation_id)
        ) !== false;
    }

    /**
     * Obtener automatización
     */
    public function get_automation($automation_id)
    {
        global $wpdb;

        $automation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}inmopress_automations WHERE id = %d",
            $automation_id
        ));

        if (!$automation) {
            return null;
        }

        $automation->trigger_config = json_decode($automation->trigger_config, true);
        $automation->conditions = json_decode($automation->conditions, true);
        $automation->actions = json_decode($automation->actions, true);

        return $automation;
    }

    /**
     * Obtener todas las automatizaciones
     */
    public function get_automations($args = array())
    {
        global $wpdb;

        $defaults = array(
            'is_active' => null,
            'trigger_type' => null,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => -1,
        );

        $args = wp_parse_args($args, $defaults);

        $where = array('1=1');
        $where_values = array();

        if ($args['is_active'] !== null) {
            $where[] = 'is_active = %d';
            $where_values[] = $args['is_active'] ? 1 : 0;
        }

        if ($args['trigger_type']) {
            $where[] = 'trigger_type = %s';
            $where_values[] = $args['trigger_type'];
        }

        $where_clause = implode(' AND ', $where);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);

        $query = "SELECT * FROM {$wpdb->prefix}inmopress_automations WHERE {$where_clause} ORDER BY {$orderby}";

        if ($args['limit'] > 0) {
            $query .= $wpdb->prepare(' LIMIT %d', $args['limit']);
        }

        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }

        $results = $wpdb->get_results($query);

        foreach ($results as $automation) {
            $automation->trigger_config = json_decode($automation->trigger_config, true);
            $automation->conditions = json_decode($automation->conditions, true);
            $automation->actions = json_decode($automation->actions, true);
        }

        return $results;
    }

    /**
     * Crear automatizaciones por defecto
     */
    public function create_default_automations()
    {
        // Auto-respuesta Lead Web
        $this->create_automation(array(
            'name' => 'Auto-respuesta Lead Web',
            'description' => 'Envía email automático cuando se crea un lead desde la web',
            'trigger_type' => 'lead_created',
            'trigger_config' => array('source' => 'web'),
            'conditions' => array(
                array(
                    'field' => 'lead_source',
                    'operator' => 'equals',
                    'value' => 'web',
                ),
            ),
            'actions' => array(
                array(
                    'type' => 'create_task',
                    'config' => array(
                        'title' => 'Llamar a {{nombre}} {{apellidos}}',
                        'type' => 'llamada',
                        'priority' => 'alta',
                        'due_date' => '+24 hours',
                    ),
                ),
            ),
            'is_active' => 1,
        ));

        // Matching automático cuando se publica propiedad
        $this->create_automation(array(
            'name' => 'Matching Automático Propiedad',
            'description' => 'Calcula matching automáticamente cuando se publica una propiedad',
            'trigger_type' => 'property_status_changed',
            'trigger_config' => array('new_status' => 'disponible'),
            'conditions' => array(
                array(
                    'field' => 'impress_publish_web',
                    'operator' => 'equals',
                    'value' => true,
                ),
            ),
            'actions' => array(
                array(
                    'type' => 'calculate_matching',
                    'config' => array('threshold' => 70),
                ),
            ),
            'is_active' => 1,
        ));
    }
}
