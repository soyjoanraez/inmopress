<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_Notifications
{
    public static function init()
    {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }

    public static function register_routes()
    {
        register_rest_route('inmopress/v1', '/push-subscription', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'save_push_subscription'),
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ));
    }

    public static function save_push_subscription(WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('inmopress_not_logged', __('Usuario no autorizado.', 'inmopress'), array('status' => 401));
        }

        $payload = $request->get_json_params();
        if (empty($payload) || !is_array($payload)) {
            return new WP_Error('inmopress_invalid_payload', __('Payload inválido.', 'inmopress'), array('status' => 400));
        }

        update_user_meta($user_id, 'inmopress_push_subscription', $payload);

        return new WP_REST_Response(array('ok' => true), 200);
    }

    public static function notify_user($user_id, $subject, $message, $payload = array())
    {
        $email_sent = false;
        $user = get_user_by('id', $user_id);
        if ($user && !empty($user->user_email)) {
            $email_sent = wp_mail($user->user_email, $subject, $message);
        }

        self::queue_push($user_id, array(
            'title' => $subject,
            'message' => $message,
            'data' => $payload,
        ));

        return $email_sent;
    }

    public static function queue_push($user_id, $payload)
    {
        if (!$user_id) {
            return false;
        }

        $subscription = get_user_meta($user_id, 'inmopress_push_subscription', true);
        if (empty($subscription)) {
            return false;
        }

        $queue = get_user_meta($user_id, 'inmopress_push_queue', true);
        if (!is_array($queue)) {
            $queue = array();
        }

        $queue[] = array_merge(array(
            'time' => current_time('mysql'),
        ), $payload);

        if (count($queue) > 50) {
            $queue = array_slice($queue, -50);
        }

        update_user_meta($user_id, 'inmopress_push_queue', $queue);

        return true;
    }
}
