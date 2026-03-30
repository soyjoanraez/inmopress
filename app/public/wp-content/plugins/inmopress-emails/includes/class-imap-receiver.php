<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * IMAP Receiver - Recibe emails del buzón IMAP
 * 
 * NOTA: Requiere extensión PHP imap
 */
class Inmopress_IMAP_Receiver
{
    private static $instance = null;
    private $parser;
    private $auto_associator;
    private $thread_manager;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->parser = Inmopress_Email_Parser::get_instance();
        $this->auto_associator = Inmopress_Auto_Associator::get_instance();
        $this->thread_manager = Inmopress_Thread_Manager::get_instance();
    }

    /**
     * Revisar buzón IMAP
     */
    public function check_inbox()
    {
        if (!function_exists('imap_open')) {
            error_log('Inmopress Emails: Extensión IMAP no está disponible');
            return false;
        }

        $host = get_option('inmopress_imap_host', '');
        $port = intval(get_option('inmopress_imap_port', 993));
        $username = get_option('inmopress_imap_username', '');
        $password = get_option('inmopress_imap_password', '');

        if (empty($host) || empty($username) || empty($password)) {
            return false;
        }

        $mailbox_string = '{' . $host . ':' . $port . '/imap/ssl}INBOX';

        try {
            $mailbox = @imap_open($mailbox_string, $username, $password);

            if (!$mailbox) {
                error_log('Inmopress Emails: Error al conectar con IMAP: ' . imap_last_error());
                return false;
            }

            // Buscar emails no vistos
            $emails = imap_search($mailbox, 'UNSEEN');

            if (!$emails) {
                imap_close($mailbox);
                return true; // No hay emails nuevos
            }

            foreach ($emails as $email_number) {
                $this->process_email($mailbox, $email_number);
            }

            imap_close($mailbox);
            return true;

        } catch (Exception $e) {
            error_log('Inmopress Emails IMAP Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Procesar email individual
     */
    private function process_email($mailbox, $email_number)
    {
        // Obtener overview
        $overview = imap_fetch_overview($mailbox, $email_number, 0);
        if (empty($overview)) {
            return;
        }

        $overview = $overview[0];

        // Parsear email completo
        $email_data = $this->parser->parse_email($mailbox, $email_number);

        // Crear mensaje en WordPress
        $message_id = $this->create_incoming_message($email_data);

        if (!$message_id || is_wp_error($message_id)) {
            return;
        }

        // Auto-asociar
        $this->auto_associator->associate_message($message_id, $email_data);

        // Gestionar thread
        $this->thread_manager->add_to_thread($message_id, $email_data);

        // Marcar como visto
        imap_setflag_full($mailbox, $email_number, "\\Seen");

        // Disparar acción
        do_action('inmopress_email_received', array(
            'email_id' => $message_id,
            'from' => $email_data['from'],
            'subject' => $email_data['subject'],
        ));
    }

    /**
     * Crear mensaje entrante como CPT
     */
    private function create_incoming_message($email_data)
    {
        $post_data = array(
            'post_type' => 'impress_message',
            'post_status' => 'publish',
            'post_title' => $email_data['subject'],
            'post_content' => $email_data['body_html'],
            'post_date' => $email_data['date'],
        );

        $message_id = wp_insert_post($post_data);

        if (is_wp_error($message_id)) {
            return $message_id;
        }

        // Metadatos
        update_post_meta($message_id, 'impress_message_from_email', $email_data['from_email']);
        update_post_meta($message_id, 'impress_message_from_name', $email_data['from_name']);
        update_post_meta($message_id, 'impress_message_to_email', $email_data['to_email']);
        update_post_meta($message_id, 'impress_message_subject', $email_data['subject']);
        update_post_meta($message_id, 'impress_message_direction', 'incoming');
        update_post_meta($message_id, 'impress_message_status', 'received');
        update_post_meta($message_id, 'impress_message_message_id', $email_data['message_id']);

        // Headers importantes para threading
        if (isset($email_data['in_reply_to'])) {
            update_post_meta($message_id, 'impress_message_in_reply_to', $email_data['in_reply_to']);
        }
        if (isset($email_data['references'])) {
            update_post_meta($message_id, 'impress_message_references', $email_data['references']);
        }

        return $message_id;
    }
}
