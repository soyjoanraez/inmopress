<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Webhook Manager - Gestiona webhooks para eventos
 */
class Inmopress_Webhook_Manager
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
     * Registrar webhook
     */
    public function register_webhook($user_id, $url, $events)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_webhooks';

        $secret = wp_generate_password(32, false);

        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'url' => esc_url_raw($url),
                'events' => json_encode($events),
                'secret' => $secret,
                'is_active' => 1,
                'created_at' => current_time('mysql'),
            )
        );

        if ($result) {
            return array(
                'id' => $wpdb->insert_id,
                'secret' => $secret,
            );
        }

        return false;
    }

    /**
     * Disparar webhook para evento
     */
    public function trigger_webhook($event, $data)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_webhooks';

        $webhooks = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE is_active = 1"
        );

        foreach ($webhooks as $webhook) {
            $events = json_decode($webhook->events, true);

            if (in_array($event, $events)) {
                $this->send_webhook($webhook, $event, $data);
            }
        }
    }

    /**
     * Enviar webhook individual
     */
    private function send_webhook($webhook, $event, $data)
    {
        $payload = array(
            'event' => $event,
            'data' => $data,
            'timestamp' => time(),
        );

        $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);

        $response = wp_remote_post($webhook->url, array(
            'timeout' => 10,
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Inmopress-Signature' => $signature,
                'X-Inmopress-Event' => $event,
            ),
            'body' => json_encode($payload),
        ));

        // Actualizar last_triggered_at
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'inmopress_webhooks',
            array('last_triggered_at' => current_time('mysql')),
            array('id' => $webhook->id)
        );

        return !is_wp_error($response);
    }
}
