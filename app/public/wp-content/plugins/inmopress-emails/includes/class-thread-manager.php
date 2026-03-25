<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Thread Manager - Gestiona threads de conversación
 */
class Inmopress_Thread_Manager
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
     * Añadir mensaje a thread
     */
    public function add_to_thread($message_id, $email_data)
    {
        $thread_id = $this->get_thread_id($email_data);

        if (!$thread_id) {
            $thread_id = $this->create_thread($message_id, $email_data);
        } else {
            $this->update_thread($thread_id, $message_id);
        }

        update_post_meta($message_id, 'impress_message_thread_id', $thread_id);
        return $thread_id;
    }

    /**
     * Obtener thread ID desde email data
     */
    private function get_thread_id($email_data)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_email_threads';

        // Buscar por In-Reply-To o References
        if (!empty($email_data['in_reply_to'])) {
            $thread = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table} WHERE thread_id = %s",
                $email_data['in_reply_to']
            ));

            if ($thread) {
                return $thread->id;
            }
        }

        // Buscar por subject similar (para emails sin threading headers)
        if (!empty($email_data['subject'])) {
            $clean_subject = $this->clean_subject($email_data['subject']);
            $thread = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table} WHERE subject LIKE %s ORDER BY created_at DESC LIMIT 1",
                '%' . $wpdb->esc_like($clean_subject) . '%'
            ));

            if ($thread) {
                return $thread->id;
            }
        }

        return null;
    }

    /**
     * Crear nuevo thread
     */
    private function create_thread($message_id, $email_data)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_email_threads';

        $thread_id = !empty($email_data['message_id']) ? $email_data['message_id'] : 'thread_' . $message_id . '_' . time();

        $wpdb->insert(
            $table,
            array(
                'thread_id' => $thread_id,
                'subject' => $email_data['subject'],
                'participants' => json_encode(array($email_data['from_email'], $email_data['to_email'])),
                'message_count' => 1,
                'last_message_id' => $message_id,
                'last_message_at' => current_time('mysql'),
                'created_at' => current_time('mysql'),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Actualizar thread existente
     */
    private function update_thread($thread_db_id, $message_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_email_threads';

        $wpdb->query($wpdb->prepare(
            "UPDATE {$table} 
            SET message_count = message_count + 1,
                last_message_id = %d,
                last_message_at = %s
            WHERE id = %d",
            $message_id,
            current_time('mysql'),
            $thread_db_id
        ));
    }

    /**
     * Limpiar subject para matching (eliminar Re:, Fwd:, etc.)
     */
    private function clean_subject($subject)
    {
        return preg_replace('/^(Re:|Fwd?:|RE:|FWD?:)\s*/i', '', $subject);
    }

    /**
     * Obtener mensajes de un thread
     */
    public function get_thread_messages($thread_id)
    {
        $args = array(
            'post_type' => 'impress_message',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'impress_message_thread_id',
                    'value' => $thread_id,
                ),
            ),
            'orderby' => 'date',
            'order' => 'ASC',
        );

        return get_posts($args);
    }
}
