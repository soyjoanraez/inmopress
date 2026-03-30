<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email Queue - Gestiona cola de envío de emails
 */
class Inmopress_Email_Queue
{
    private static $instance = null;
    private $smtp_sender;

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
    }

    /**
     * Añadir email a la cola
     */
    public function add_to_queue($message_id, $data, $options = array())
    {
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_email_queue';

        $scheduled_at = isset($options['scheduled_at']) ? $options['scheduled_at'] : current_time('mysql');

        $result = $wpdb->insert(
            $table,
            array(
                'to_email' => $data['to_email'],
                'to_name' => isset($data['to_name']) ? $data['to_name'] : '',
                'from_email' => isset($data['from_email']) ? $data['from_email'] : get_option('inmopress_smtp_from_email'),
                'subject' => $data['subject'],
                'body_html' => $data['body_html'],
                'body_text' => isset($data['body_text']) ? $data['body_text'] : wp_strip_all_tags($data['body_html']),
                'attachments' => isset($data['attachments']) ? json_encode($data['attachments']) : null,
                'priority' => isset($options['priority']) ? intval($options['priority']) : 5,
                'status' => 'pending',
                'scheduled_at' => $scheduled_at,
                'created_at' => current_time('mysql'),
            )
        );

        if ($result) {
            update_post_meta($message_id, 'impress_message_queue_id', $wpdb->insert_id);
        }

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Procesar cola de emails
     */
    public function process_queue($limit = 10)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_email_queue';

        // Obtener emails pendientes ordenados por prioridad
        $emails = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} 
            WHERE status = 'pending' 
            AND (scheduled_at IS NULL OR scheduled_at <= %s)
            ORDER BY priority DESC, created_at ASC
            LIMIT %d",
            current_time('mysql'),
            $limit
        ));

        if (empty($emails)) {
            return 0;
        }

        $processed = 0;

        foreach ($emails as $email) {
            $result = $this->process_email($email);

            if ($result) {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Procesar email individual
     */
    private function process_email($email)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_email_queue';

        // Marcar como procesando
        $wpdb->update(
            $table,
            array('status' => 'processing'),
            array('id' => $email->id)
        );

        // Preparar datos
        $data = array(
            'to_email' => $email->to_email,
            'to_name' => $email->to_name,
            'from_email' => $email->from_email,
            'subject' => $email->subject,
            'body_html' => $email->body_html,
            'body_text' => $email->body_text,
            'attachments' => $email->attachments ? json_decode($email->attachments, true) : array(),
        );

        // Intentar enviar
        $result = $this->smtp_sender->send($data);

        if (is_wp_error($result)) {
            // Error - incrementar intentos
            $attempts = $email->attempts + 1;
            $status = $attempts >= 3 ? 'failed' : 'pending';

            $wpdb->update(
                $table,
                array(
                    'status' => $status,
                    'attempts' => $attempts,
                    'last_error' => $result->get_error_message(),
                ),
                array('id' => $email->id)
            );

            return false;
        } else {
            // Éxito
            $wpdb->update(
                $table,
                array(
                    'status' => 'sent',
                    'sent_at' => current_time('mysql'),
                ),
                array('id' => $email->id)
            );

            return true;
        }
    }
}
