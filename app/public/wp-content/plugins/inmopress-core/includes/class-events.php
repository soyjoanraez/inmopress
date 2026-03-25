<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_Events
{
    public static function init()
    {
        add_filter('cron_schedules', array(__CLASS__, 'add_cron_intervals'));
        add_action('inmopress_check_reminders', array(__CLASS__, 'check_reminders'));
        add_action('acf/save_post', array(__CLASS__, 'handle_event_save'), 20);
        self::schedule_reminders();
    }

    public static function add_cron_intervals($schedules)
    {
        if (!isset($schedules['every_15_minutes'])) {
            $schedules['every_15_minutes'] = array(
                'interval' => 15 * 60,
                'display' => __('Cada 15 minutos', 'inmopress'),
            );
        }

        return $schedules;
    }

    public static function schedule_reminders()
    {
        if (!wp_next_scheduled('inmopress_check_reminders')) {
            wp_schedule_event(time(), 'every_15_minutes', 'inmopress_check_reminders');
        }
    }

    public static function handle_event_save($post_id)
    {
        if (get_post_type($post_id) !== 'impress_event') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $reminder_choice = get_field('impress_event_reminder', $post_id);
        $reminder_min = (int) get_field('impress_event_reminder_min', $post_id);
        $mapped_min = self::reminder_choice_to_minutes($reminder_choice);

        if ($mapped_min !== null && $mapped_min !== $reminder_min) {
            update_field('impress_event_reminder_min', $mapped_min, $post_id);
            $reminder_min = $mapped_min;
        }

        if ($reminder_choice === 'sin_recordatorio' && $reminder_min !== 0) {
            update_field('impress_event_reminder_min', 0, $post_id);
            $reminder_min = 0;
        }

        if ($reminder_min > 0) {
            update_field('impress_event_reminder_sent', 0, $post_id);
        }

        $status = get_field('impress_event_status', $post_id);
        if ($status === 'completada') {
            $completion = get_field('impress_event_completion_time', $post_id);
            if (empty($completion)) {
                update_field('impress_event_completion_time', current_time('Y-m-d H:i:s'), $post_id);
            }
        }
    }

    private static function reminder_choice_to_minutes($choice)
    {
        switch ($choice) {
            case '15_min_antes':
                return 15;
            case '30_min_antes':
                return 30;
            case '1_hora_antes':
                return 60;
            case '1_dia_antes':
                return 1440;
            case 'sin_recordatorio':
                return 0;
            default:
                return null;
        }
    }

    public static function check_reminders()
    {
        $now = current_time('timestamp');
        $lower = date('Y-m-d H:i:s', $now - DAY_IN_SECONDS);
        $upper = date('Y-m-d H:i:s', $now + DAY_IN_SECONDS);

        $events = get_posts(array(
            'post_type' => 'impress_event',
            'posts_per_page' => 200,
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'impress_event_start',
                    'value' => array($lower, $upper),
                    'compare' => 'BETWEEN',
                    'type' => 'DATETIME',
                ),
                array(
                    'key' => 'impress_event_status',
                    'value' => array('completada', 'cancelada'),
                    'compare' => 'NOT IN',
                ),
            ),
        ));

        foreach ($events as $event_id) {
            $start = get_field('impress_event_start', $event_id);
            if (empty($start)) {
                continue;
            }

            $reminder_min = (int) get_field('impress_event_reminder_min', $event_id);
            if ($reminder_min <= 0) {
                continue;
            }

            $reminder_sent = get_field('impress_event_reminder_sent', $event_id);
            if ($reminder_sent) {
                continue;
            }

            $start_ts = strtotime($start);
            if (!$start_ts) {
                continue;
            }

            if ($start_ts - ($reminder_min * 60) <= $now) {
                if (self::send_event_reminder($event_id)) {
                    update_field('impress_event_reminder_sent', 1, $event_id);
                }
            }
        }
    }

    private static function send_event_reminder($event_id)
    {
        $title = get_field('impress_event_title', $event_id);
        if (empty($title)) {
            $title = get_the_title($event_id);
        }

        $start = get_field('impress_event_start', $event_id);
        $date_str = $start ? date_i18n('d/m/Y H:i', strtotime($start)) : '';

        $agent_id = get_field('impress_event_agent_rel', $event_id);
        $email = '';
        $agent_name = '';
        $user_id = null;

        if ($agent_id) {
            $agent_email = get_field('email', $agent_id);
            if (!empty($agent_email)) {
                $email = $agent_email;
            }

            $agent_name = trim((string) get_field('nombre', $agent_id) . ' ' . (string) get_field('apellidos', $agent_id));

            $user = get_field('usuario_wordpress', $agent_id);
            $user_id = self::extract_user_id($user);
            if ($user_id && empty($email)) {
                $user_obj = get_user_by('id', $user_id);
                if ($user_obj) {
                    $email = $user_obj->user_email;
                }
            }
        }

        if (empty($user_id)) {
            $created_by = get_field('impress_event_created_by', $event_id);
            $user_id = self::extract_user_id($created_by);
        }

        if (empty($email) && $user_id) {
            $user_obj = get_user_by('id', $user_id);
            if ($user_obj) {
                $email = $user_obj->user_email;
            }
        }

        if (empty($email)) {
            return false;
        }

        $lines = array(
            sprintf('Evento: %s', $title),
        );

        if (!empty($date_str)) {
            $lines[] = sprintf('Cuándo: %s', $date_str);
        }

        if (!empty($agent_name)) {
            $lines[] = sprintf('Agente: %s', $agent_name);
        }

        $subject = sprintf('Recordatorio: %s', $title);
        $body = implode("\n", $lines);

        if ($user_id && class_exists('Inmopress_Notifications')) {
            return Inmopress_Notifications::notify_user($user_id, $subject, $body, array('event_id' => $event_id));
        }

        return wp_mail($email, $subject, $body);
    }

    private static function extract_user_id($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_object($value) && !empty($value->ID)) {
            return (int) $value->ID;
        }

        if (is_array($value)) {
            if (!empty($value['ID'])) {
                return (int) $value['ID'];
            }
            if (!empty($value['id'])) {
                return (int) $value['id'];
            }
        }

        return null;
    }
}
