<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_Events_Ajax
{
    public static function init()
    {
        add_action('wp_ajax_inmopress_get_week_events', array(__CLASS__, 'get_week_events'));
        add_action('wp_ajax_inmopress_update_event_time', array(__CLASS__, 'update_event_time'));
    }

    public static function get_week_events()
    {
        check_ajax_referer('inmopress_events_nonce', 'nonce');

        if (!current_user_can('read')) {
            wp_send_json_error('forbidden', 403);
        }

        $start = isset($_POST['start']) ? sanitize_text_field(wp_unslash($_POST['start'])) : '';
        $end = isset($_POST['end']) ? sanitize_text_field(wp_unslash($_POST['end'])) : '';
        $type = isset($_POST['type']) ? sanitize_key(wp_unslash($_POST['type'])) : '';
        $status = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';
        $priority = isset($_POST['priority']) ? sanitize_key(wp_unslash($_POST['priority'])) : '';
        $agent_filter = isset($_POST['agent']) ? absint($_POST['agent']) : 0;

        if (empty($start) || empty($end)) {
            wp_send_json_error('invalid_range', 400);
        }

        $range_start = date('Y-m-d 00:00:00', strtotime($start));
        $range_end = date('Y-m-d 23:59:59', strtotime($end));

        $meta_query = array(
            'relation' => 'AND',
            array(
                'key' => 'impress_event_start',
                'value' => array($range_start, $range_end),
                'compare' => 'BETWEEN',
                'type' => 'DATETIME',
            ),
        );

        if (!empty($type)) {
            $meta_query[] = array(
                'key' => 'impress_event_type',
                'value' => $type,
                'compare' => '=',
            );
        }

        if (!empty($status)) {
            $meta_query[] = array(
                'key' => 'impress_event_status',
                'value' => $status,
                'compare' => '=',
            );
        }

        if (!empty($priority)) {
            $meta_query[] = array(
                'key' => 'impress_event_priority',
                'value' => $priority,
                'compare' => '=',
            );
        }

        $user = wp_get_current_user();
        if (in_array('agente', (array) $user->roles, true)) {
            $agent_id = Inmopress_Shortcodes::get_agent_id_by_user($user->ID);
            if ($agent_id) {
                $meta_query[] = array(
                    'key' => 'impress_event_agent_rel',
                    'value' => $agent_id,
                    'compare' => '=',
                );
            }
        } elseif ($agent_filter) {
            $meta_query[] = array(
                'key' => 'impress_event_agent_rel',
                'value' => $agent_filter,
                'compare' => '=',
            );
        }

        $events = get_posts(array(
            'post_type' => 'impress_event',
            'posts_per_page' => 300,
            'fields' => 'ids',
            'meta_query' => $meta_query,
        ));

        $data = array();
        foreach ($events as $event_id) {
            $title = get_field('impress_event_title', $event_id);
            if (empty($title)) {
                $title = get_the_title($event_id);
            }

            $agent_id = get_field('impress_event_agent_rel', $event_id);
            $color = '';
            if ($agent_id) {
                $color = get_field('color_calendario', $agent_id);
            }

            $data[] = array(
                'id' => $event_id,
                'title' => $title,
                'start' => get_field('impress_event_start', $event_id),
                'end' => get_field('impress_event_end', $event_id),
                'type' => get_field('impress_event_type', $event_id),
                'status' => get_field('impress_event_status', $event_id),
                'priority' => get_field('impress_event_priority', $event_id),
                'color' => $color,
            );
        }

        wp_send_json_success($data);
    }

    public static function update_event_time()
    {
        check_ajax_referer('inmopress_events_nonce', 'nonce');

        $event_id = isset($_POST['event_id']) ? absint($_POST['event_id']) : 0;
        $start = isset($_POST['start']) ? sanitize_text_field(wp_unslash($_POST['start'])) : '';
        $end = isset($_POST['end']) ? sanitize_text_field(wp_unslash($_POST['end'])) : '';

        if (!$event_id || empty($start)) {
            wp_send_json_error('invalid_request', 400);
        }

        if (!current_user_can('edit_post', $event_id)) {
            wp_send_json_error('forbidden', 403);
        }

        if (function_exists('update_field')) {
            update_field('impress_event_start', $start, $event_id);
            if (!empty($end)) {
                update_field('impress_event_end', $end, $event_id);
            }

            if (!empty($end)) {
                $duration = (strtotime($end) - strtotime($start)) / 60;
                if ($duration > 0) {
                    update_field('impress_event_duration_minutes', (int) $duration, $event_id);
                }
            }
        } else {
            update_post_meta($event_id, 'impress_event_start', $start);
            if (!empty($end)) {
                update_post_meta($event_id, 'impress_event_end', $end);
            }
        }

        wp_send_json_success(array(
            'event_id' => $event_id,
            'start' => $start,
            'end' => $end,
        ));
    }
}
