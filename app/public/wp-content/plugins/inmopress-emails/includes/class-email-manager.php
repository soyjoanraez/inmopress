<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email Manager - Gestiona creación y envío de mensajes
 */
class Inmopress_Email_Manager
{
    private static $instance = null;
    private $smtp_sender;
    private $queue;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->smtp_sender = Inmopress_SMTP_Sender::get_instance();
        $this->queue = Inmopress_Email_Queue::get_instance();
    }

    /**
     * Enviar email (añade a cola o envía inmediatamente)
     */
    public function send_email($data, $options = array())
    {
        $defaults = array(
            'use_queue' => true,
            'priority' => 5,
            'scheduled_at' => null,
        );

        $options = wp_parse_args($options, $defaults);

        // Validar datos
        $validation = $this->validate_email_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Crear mensaje como CPT
        $message_id = $this->create_message($data);

        if (is_wp_error($message_id)) {
            return $message_id;
        }

        // Añadir a cola o enviar inmediatamente
        if ($options['use_queue']) {
            $queue_id = $this->queue->add_to_queue($message_id, $data, $options);
            return $queue_id;
        } else {
            return $this->smtp_sender->send($data);
        }
    }

    /**
     * Crear mensaje como CPT
     */
    private function create_message($data)
    {
        $post_data = array(
            'post_type' => 'impress_message',
            'post_status' => 'publish',
            'post_title' => $data['subject'],
            'post_content' => $data['body_html'],
        );

        $message_id = wp_insert_post($post_data);

        if (is_wp_error($message_id)) {
            return $message_id;
        }

        // Guardar metadatos
        update_post_meta($message_id, 'impress_message_to_email', $data['to_email']);
        update_post_meta($message_id, 'impress_message_to_name', $data['to_name']);
        update_post_meta($message_id, 'impress_message_from_email', isset($data['from_email']) ? $data['from_email'] : get_option('inmopress_smtp_from_email'));
        update_post_meta($message_id, 'impress_message_from_name', isset($data['from_name']) ? $data['from_name'] : get_option('inmopress_smtp_from_name'));
        update_post_meta($message_id, 'impress_message_subject', $data['subject']);
        update_post_meta($message_id, 'impress_message_direction', 'outgoing');
        update_post_meta($message_id, 'impress_message_status', 'pending');

        // Asociaciones
        if (isset($data['related_type']) && isset($data['related_id'])) {
            update_post_meta($message_id, 'impress_message_related_type', $data['related_type']);
            update_post_meta($message_id, 'impress_message_related_id', $data['related_id']);
        }

        // Thread
        if (isset($data['thread_id'])) {
            update_post_meta($message_id, 'impress_message_thread_id', $data['thread_id']);
        }

        return $message_id;
    }

    /**
     * Validar datos de email
     */
    private function validate_email_data($data)
    {
        if (empty($data['to_email']) || !is_email($data['to_email'])) {
            return new WP_Error('invalid_email', 'Email destinatario no válido');
        }

        if (empty($data['subject'])) {
            return new WP_Error('empty_subject', 'El asunto no puede estar vacío');
        }

        if (empty($data['body_html']) && empty($data['body_text'])) {
            return new WP_Error('empty_body', 'El cuerpo del mensaje no puede estar vacío');
        }

        return true;
    }

    /**
     * Obtener mensajes relacionados con una entidad
     */
    public function get_related_messages($object_type, $object_id)
    {
        $args = array(
            'post_type' => 'impress_message',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'impress_message_related_type',
                    'value' => $object_type,
                ),
                array(
                    'key' => 'impress_message_related_id',
                    'value' => $object_id,
                ),
            ),
            'orderby' => 'date',
            'order' => 'DESC',
        );

        return get_posts($args);
    }
}
