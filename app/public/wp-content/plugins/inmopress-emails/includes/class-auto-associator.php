<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Auto Associator - Asocia emails automáticamente a clientes/propiedades/leads
 */
class Inmopress_Auto_Associator
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
     * Asociar mensaje automáticamente
     */
    public function associate_message($message_id, $email_data)
    {
        $from_email = $email_data['from_email'];

        // 1. Buscar por email exacto
        $client_id = $this->find_by_email($from_email, 'impress_client');
        if ($client_id) {
            update_post_meta($message_id, 'impress_message_related_type', 'impress_client');
            update_post_meta($message_id, 'impress_message_related_id', $client_id);
            return;
        }

        $lead_id = $this->find_by_email($from_email, 'impress_lead');
        if ($lead_id) {
            update_post_meta($message_id, 'impress_message_related_type', 'impress_lead');
            update_post_meta($message_id, 'impress_message_related_id', $lead_id);
            return;
        }

        // 2. Buscar por referencia en subject/body
        $property_id = $this->find_by_reference($email_data);
        if ($property_id) {
            update_post_meta($message_id, 'impress_message_related_type', 'impress_property');
            update_post_meta($message_id, 'impress_message_related_id', $property_id);
        }
    }

    /**
     * Buscar por email
     */
    private function find_by_email($email, $post_type)
    {
        $posts = get_posts(array(
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'email',
                    'value' => $email,
                    'compare' => '=',
                ),
            ),
        ));

        return !empty($posts) ? $posts[0] : null;
    }

    /**
     * Buscar por referencia en subject/body
     */
    private function find_by_reference($email_data)
    {
        $text = $email_data['subject'] . ' ' . $email_data['body_text'];

        // Buscar patrones como REF123, referencia 123, etc.
        if (preg_match('/ref[\.\s]*(\d+)/i', $text, $matches)) {
            $ref = $matches[1];
            $property = get_posts(array(
                'post_type' => 'impress_property',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'referencia',
                        'value' => $ref,
                        'compare' => '=',
                    ),
                ),
            ));

            if (!empty($property)) {
                return $property[0];
            }
        }

        return null;
    }
}
