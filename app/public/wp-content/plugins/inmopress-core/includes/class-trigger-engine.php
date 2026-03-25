<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trigger Engine - Detecta eventos y dispara automatizaciones
 */
class Inmopress_Trigger_Engine
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init_hooks();
    }

    private function init_hooks()
    {
        // Property triggers
        add_action('wp_insert_post', array($this, 'handle_property_created'), 10, 3);
        add_action('acf/save_post', array($this, 'handle_property_status_changed'), 20);
        add_action('transition_post_status', array($this, 'handle_property_status_transition'), 10, 3);

        // Client triggers
        add_action('wp_insert_post', array($this, 'handle_client_created'), 10, 3);

        // Lead triggers
        add_action('wp_insert_post', array($this, 'handle_lead_created'), 10, 3);

        // Event triggers
        add_action('acf/save_post', array($this, 'handle_event_completed'), 25);

        // Scheduled triggers
        add_action('inmopress_automation_scheduled', array($this, 'handle_scheduled_trigger'));

        // Email triggers (cuando se implemente el módulo de emails)
        add_action('inmopress_email_received', array($this, 'handle_email_received'), 10, 1);
    }

    /**
     * Disparar automatizaciones por tipo de trigger
     */
    public function fire_trigger($trigger_type, $trigger_data = array())
    {
        global $wpdb;

        $automations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}inmopress_automations 
            WHERE trigger_type = %s AND is_active = 1",
            $trigger_type
        ));

        if (empty($automations)) {
            return;
        }

        foreach ($automations as $automation) {
            $automation_manager = Inmopress_Automation_Manager::get_instance();
            $automation_manager->execute_automation($automation->id, $trigger_data);
        }
    }

    /**
     * Property Created Trigger
     */
    public function handle_property_created($post_id, $post, $update)
    {
        if ($update || $post->post_type !== 'impress_property') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $this->fire_trigger('property_created', array(
            'post_id' => $post_id,
            'post' => $post,
            'timestamp' => current_time('mysql'),
        ));
    }

    /**
     * Property Status Changed Trigger
     */
    public function handle_property_status_changed($post_id)
    {
        if (get_post_type($post_id) !== 'impress_property') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $old_status = get_post_meta($post_id, '_inmopress_old_status', true);
        $new_status = get_post_meta($post_id, 'impress_status', true);

        if ($old_status === $new_status) {
            return;
        }

        update_post_meta($post_id, '_inmopress_old_status', $new_status);

        $this->fire_trigger('property_status_changed', array(
            'post_id' => $post_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'timestamp' => current_time('mysql'),
        ));
    }

    /**
     * Property Status Transition Trigger
     */
    public function handle_property_status_transition($new_status, $old_status, $post)
    {
        if ($post->post_type !== 'impress_property') {
            return;
        }

        if ($new_status === $old_status) {
            return;
        }

        $this->fire_trigger('property_status_changed', array(
            'post_id' => $post->ID,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'post_status' => $new_status,
            'timestamp' => current_time('mysql'),
        ));
    }

    /**
     * Client Created Trigger
     */
    public function handle_client_created($post_id, $post, $update)
    {
        if ($update || $post->post_type !== 'impress_client') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $this->fire_trigger('client_created', array(
            'post_id' => $post_id,
            'post' => $post,
            'timestamp' => current_time('mysql'),
        ));
    }

    /**
     * Lead Created Trigger
     */
    public function handle_lead_created($post_id, $post, $update)
    {
        if ($update || $post->post_type !== 'impress_lead') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $this->fire_trigger('lead_created', array(
            'post_id' => $post_id,
            'post' => $post,
            'timestamp' => current_time('mysql'),
        ));
    }

    /**
     * Event Completed Trigger
     */
    public function handle_event_completed($post_id)
    {
        if (get_post_type($post_id) !== 'impress_event') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $status = get_field('impress_event_status', $post_id);
        $old_status = get_post_meta($post_id, '_inmopress_event_old_status', true);

        if ($status === 'completada' && $old_status !== 'completada') {
            update_post_meta($post_id, '_inmopress_event_old_status', $status);

            $this->fire_trigger('event_completed', array(
                'post_id' => $post_id,
                'event_type' => get_field('impress_event_type', $post_id),
                'timestamp' => current_time('mysql'),
            ));
        }
    }

    /**
     * Scheduled Trigger
     */
    public function handle_scheduled_trigger($automation_id)
    {
        $this->fire_trigger('scheduled', array(
            'automation_id' => $automation_id,
            'timestamp' => current_time('mysql'),
        ));
    }

    /**
     * Email Received Trigger
     */
    public function handle_email_received($email_data)
    {
        $this->fire_trigger('email_received', array(
            'email_id' => isset($email_data['email_id']) ? $email_data['email_id'] : 0,
            'from' => isset($email_data['from']) ? $email_data['from'] : '',
            'subject' => isset($email_data['subject']) ? $email_data['subject'] : '',
            'timestamp' => current_time('mysql'),
        ));
    }

    /**
     * Manual Trigger (para testing)
     */
    public function trigger_manually($automation_id, $trigger_data = array())
    {
        $this->fire_trigger('manual', array_merge($trigger_data, array(
            'automation_id' => $automation_id,
            'timestamp' => current_time('mysql'),
        )));
    }
}
