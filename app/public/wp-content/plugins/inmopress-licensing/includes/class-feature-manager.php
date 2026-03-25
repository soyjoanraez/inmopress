<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Feature Manager - Gestiona límites y features por plan
 */
class Inmopress_Feature_Manager
{
    private static $instance = null;

    // Límites por plan
    private $plan_limits = array(
        'starter' => array(
            'max_properties' => 50,
            'max_clients' => 100,
            'max_agents' => 1,
            'ai_generations_per_month' => 0,
            'features' => array('properties', 'clients', 'leads', 'events'),
        ),
        'pro' => array(
            'max_properties' => 500,
            'max_clients' => 1000,
            'max_agents' => 5,
            'ai_generations_per_month' => 0,
            'features' => array('properties', 'clients', 'leads', 'events', 'automations', 'matching'),
        ),
        'pro_ai' => array(
            'max_properties' => 500,
            'max_clients' => 1000,
            'max_agents' => 5,
            'ai_generations_per_month' => 500,
            'features' => array('properties', 'clients', 'leads', 'events', 'automations', 'matching', 'ai'),
        ),
        'agency' => array(
            'max_properties' => -1, // Ilimitado
            'max_clients' => -1,
            'max_agents' => 20,
            'ai_generations_per_month' => 2000,
            'features' => array('properties', 'clients', 'leads', 'events', 'automations', 'matching', 'ai', 'api', 'emails', 'pdfs'),
        ),
    );

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener límite de feature según plan
     */
    public function get_feature_limit($feature)
    {
        $license_manager = Inmopress_License_Manager::get_instance();
        $plan = $license_manager->get_current_plan();

        if (!isset($this->plan_limits[$plan])) {
            $plan = 'starter';
        }

        $limits = $this->plan_limits[$plan];

        return isset($limits[$feature]) ? $limits[$feature] : 0;
    }

    /**
     * Verificar si se puede crear propiedad
     */
    public function can_create_property()
    {
        if (!Inmopress_License_Manager::get_instance()->is_license_valid()) {
            return new WP_Error('license_required', 'Se requiere una licencia activa para crear propiedades.');
        }

        $limit = $this->get_feature_limit('max_properties');
        
        if ($limit === -1) {
            return true; // Ilimitado
        }

        $current_count = $this->get_property_count();

        if ($current_count >= $limit) {
            return new WP_Error('limit_reached', sprintf(
                'Has alcanzado el límite de %d propiedades de tu plan. Actualiza tu plan para crear más.',
                $limit
            ));
        }

        return true;
    }

    /**
     * Verificar si se puede crear cliente
     */
    public function can_create_client()
    {
        if (!Inmopress_License_Manager::get_instance()->is_license_valid()) {
            return new WP_Error('license_required', 'Se requiere una licencia activa.');
        }

        $limit = $this->get_feature_limit('max_clients');
        
        if ($limit === -1) {
            return true;
        }

        $current_count = $this->get_client_count();

        if ($current_count >= $limit) {
            return new WP_Error('limit_reached', sprintf(
                'Has alcanzado el límite de %d clientes de tu plan.',
                $limit
            ));
        }

        return true;
    }

    /**
     * Verificar si se puede usar IA
     */
    public function can_use_ai()
    {
        $license_manager = Inmopress_License_Manager::get_instance();
        $plan = $license_manager->get_current_plan();

        if (!in_array('ai', $this->plan_limits[$plan]['features'])) {
            return new WP_Error('feature_not_available', 'La generación con IA no está disponible en tu plan. Actualiza a Pro+AI o Agency.');
        }

        $limit = $this->get_feature_limit('ai_generations_per_month');
        
        if ($limit === 0) {
            return new WP_Error('feature_not_available', 'La generación con IA no está disponible en tu plan.');
        }

        // Verificar uso del mes actual
        $usage = $this->get_ai_usage_this_month();
        
        if ($limit > 0 && $usage >= $limit) {
            return new WP_Error('limit_reached', sprintf(
                'Has alcanzado el límite de %d generaciones IA este mes.',
                $limit
            ));
        }

        return true;
    }

    /**
     * Verificar si feature está disponible
     */
    public function is_feature_available($feature)
    {
        $license_manager = Inmopress_License_Manager::get_instance();
        
        if (!$license_manager->is_license_valid()) {
            return false;
        }

        $plan = $license_manager->get_current_plan();

        if (!isset($this->plan_limits[$plan])) {
            return false;
        }

        return in_array($feature, $this->plan_limits[$plan]['features']);
    }

    /**
     * Obtener conteo de propiedades
     */
    private function get_property_count()
    {
        $count = wp_count_posts('impress_property');
        return intval($count->publish) + intval($count->draft);
    }

    /**
     * Obtener conteo de clientes
     */
    private function get_client_count()
    {
        $count = wp_count_posts('impress_client');
        return intval($count->publish) + intval($count->draft);
    }

    /**
     * Obtener uso de IA del mes actual
     */
    private function get_ai_usage_this_month()
    {
        // Usar el sistema de Activity Log si está disponible
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_activity_log';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
            $start_of_month = date('Y-m-01 00:00:00');
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} 
                WHERE action = 'ai_generation' 
                AND created_at >= %s",
                $start_of_month
            ));
            return intval($count);
        }

        // Fallback: usar opción temporal
        $usage = get_option('inmopress_ai_usage', array());
        $current_month = date('Y-m');
        return isset($usage[$current_month]) ? intval($usage[$current_month]) : 0;
    }
}
