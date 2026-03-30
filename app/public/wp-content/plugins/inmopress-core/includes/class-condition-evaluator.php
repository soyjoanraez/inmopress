<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Condition Evaluator - Evalúa condiciones para ejecutar automatizaciones
 */
class Inmopress_Condition_Evaluator
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
     * Evaluar conjunto de condiciones
     */
    public function evaluate($conditions, $trigger_data)
    {
        if (empty($conditions) || !is_array($conditions)) {
            return true; // Sin condiciones = siempre ejecutar
        }

        // Si hay grupo lógico, evaluarlo
        if (isset($conditions['group'])) {
            return $this->evaluate_group($conditions, $trigger_data);
        }

        // Evaluar condiciones individuales
        foreach ($conditions as $condition) {
            if (!$this->evaluate_condition($condition, $trigger_data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluar grupo de condiciones (AND/OR)
     */
    private function evaluate_group($group, $trigger_data)
    {
        $logic = isset($group['logic']) ? strtolower($group['logic']) : 'and';
        $conditions = isset($group['conditions']) ? $group['conditions'] : array();

        if (empty($conditions)) {
            return true;
        }

        if ($logic === 'or') {
            // OR: al menos una condición debe cumplirse
            foreach ($conditions as $condition) {
                if ($this->evaluate_condition($condition, $trigger_data)) {
                    return true;
                }
            }
            return false;
        } else {
            // AND: todas las condiciones deben cumplirse
            foreach ($conditions as $condition) {
                if (!$this->evaluate_condition($condition, $trigger_data)) {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * Evaluar condición individual
     */
    private function evaluate_condition($condition, $trigger_data)
    {
        if (!isset($condition['field']) || !isset($condition['operator'])) {
            return false;
        }

        $field = $condition['field'];
        $operator = strtolower($condition['operator']);
        $value = isset($condition['value']) ? $condition['value'] : '';

        // Obtener valor del campo
        $field_value = $this->get_field_value($field, $trigger_data);

        // Evaluar según operador
        switch ($operator) {
            case 'equals':
                return $this->compare_equals($field_value, $value);

            case 'not_equals':
                return !$this->compare_equals($field_value, $value);

            case 'contains':
                return $this->compare_contains($field_value, $value);

            case 'not_contains':
                return !$this->compare_contains($field_value, $value);

            case 'greater_than':
                return $this->compare_greater_than($field_value, $value);

            case 'less_than':
                return $this->compare_less_than($field_value, $value);

            case 'in':
                return $this->compare_in($field_value, $value);

            case 'not_in':
                return !$this->compare_in($field_value, $value);

            case 'is_empty':
                return empty($field_value);

            case 'is_not_empty':
                return !empty($field_value);

            default:
                return false;
        }
    }

    /**
     * Obtener valor del campo desde trigger_data o ACF
     */
    private function get_field_value($field, $trigger_data)
    {
        // Primero buscar en trigger_data
        if (isset($trigger_data[$field])) {
            return $trigger_data[$field];
        }

        // Si hay post_id, buscar en ACF
        $post_id = isset($trigger_data['post_id']) ? $trigger_data['post_id'] : 0;
        if ($post_id && function_exists('get_field')) {
            $acf_value = get_field($field, $post_id);
            if ($acf_value !== false && $acf_value !== null) {
                return $acf_value;
            }
        }

        // Buscar en post meta
        if ($post_id) {
            $meta_value = get_post_meta($post_id, $field, true);
            if ($meta_value !== false && $meta_value !== '') {
                return $meta_value;
            }
        }

        // Buscar en taxonomías
        if ($post_id && taxonomy_exists($field)) {
            $terms = get_the_terms($post_id, $field);
            if ($terms && !is_wp_error($terms)) {
                return array_map(function($term) {
                    return $term->name;
                }, $terms);
            }
        }

        return null;
    }

    /**
     * Comparación equals
     */
    private function compare_equals($field_value, $value)
    {
        if (is_array($field_value)) {
            return in_array($value, $field_value);
        }
        return (string) $field_value === (string) $value;
    }

    /**
     * Comparación contains
     */
    private function compare_contains($field_value, $value)
    {
        if (is_array($field_value)) {
            foreach ($field_value as $item) {
                if (stripos((string) $item, (string) $value) !== false) {
                    return true;
                }
            }
            return false;
        }
        return stripos((string) $field_value, (string) $value) !== false;
    }

    /**
     * Comparación greater_than
     */
    private function compare_greater_than($field_value, $value)
    {
        $field_num = is_numeric($field_value) ? floatval($field_value) : 0;
        $value_num = is_numeric($value) ? floatval($value) : 0;
        return $field_num > $value_num;
    }

    /**
     * Comparación less_than
     */
    private function compare_less_than($field_value, $value)
    {
        $field_num = is_numeric($field_value) ? floatval($field_value) : 0;
        $value_num = is_numeric($value) ? floatval($value) : 0;
        return $field_num < $value_num;
    }

    /**
     * Comparación in
     */
    private function compare_in($field_value, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        if (is_array($field_value)) {
            return !empty(array_intersect($field_value, $value));
        }
        return in_array($field_value, $value);
    }
}
