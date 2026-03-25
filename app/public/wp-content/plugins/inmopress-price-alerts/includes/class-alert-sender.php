<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_Price_Alert_Sender
{
    public static function init()
    {
        add_action('inmopress_price_drop', array(__CLASS__, 'handle_price_drop'), 10, 4);
        add_action('inmopress_price_alerts_send', array(__CLASS__, 'send_scheduled_alert'), 10, 4);
    }

    public static function handle_price_drop($property_id, $old_price, $new_price, $context = array())
    {
        $property_id = absint($property_id);
        if (!$property_id) {
            return;
        }

        if (!self::should_alert_property($property_id)) {
            return;
        }

        $drop_pct = isset($context['drop_pct']) ? (float) $context['drop_pct'] : 0.0;
        if ($drop_pct <= 0 && $old_price > 0) {
            $drop_pct = round((($old_price - $new_price) / $old_price) * 100, 2);
        }

        $min_pct = (float) apply_filters('inmopress_price_alerts_min_drop_pct', 5, $property_id);
        $min_amount = (float) apply_filters('inmopress_price_alerts_min_drop_amount', 0, $property_id);
        $drop_amount = $old_price - $new_price;

        if ($min_pct > 0 && $drop_pct < $min_pct && $min_amount <= 0) {
            return;
        }

        if ($min_amount > 0 && $drop_amount < $min_amount && $min_pct <= 0) {
            return;
        }

        if ($min_pct > 0 && $min_amount > 0 && $drop_pct < $min_pct && $drop_amount < $min_amount) {
            return;
        }

        $candidates = Inmopress_Price_Alert_Matcher::get_interested_clients($property_id);
        if (empty($candidates)) {
            return;
        }

        $score_threshold = (int) apply_filters('inmopress_price_alerts_score_threshold', 50, $property_id);
        $cooldown_days = (int) apply_filters('inmopress_price_alerts_cooldown_days', 30, $property_id);
        $daily_limit = (int) apply_filters('inmopress_price_alerts_daily_limit', 3, $property_id);

        foreach ($candidates as $candidate) {
            $client_id = (int) $candidate['client_id'];
            $score = (int) $candidate['score'];

            if ($score < $score_threshold) {
                continue;
            }

            if (!Inmopress_Price_Alert_Matcher::client_allows_alerts($client_id)) {
                continue;
            }

            if ($cooldown_days > 0 && Inmopress_Price_Alert_Logger::was_notified_recently($client_id, $property_id, $cooldown_days)) {
                continue;
            }

            if ($daily_limit > 0 && Inmopress_Price_Alert_Logger::count_daily_notifications($client_id) >= $daily_limit) {
                continue;
            }

            self::dispatch_alert($client_id, $property_id, $old_price, $new_price, $score);
        }
    }

    public static function send_scheduled_alert($client_id, $property_id, $old_price, $new_price)
    {
        self::send_alert_now($client_id, $property_id, $old_price, $new_price);
    }

    private static function dispatch_alert($client_id, $property_id, $old_price, $new_price, $score)
    {
        $frequency = Inmopress_Price_Alert_Matcher::get_client_frequency($client_id);

        if ($frequency === 'inmediata') {
            self::send_alert_now($client_id, $property_id, $old_price, $new_price, $score);
            return;
        }

        $send_at = self::get_next_schedule($frequency);
        if (!$send_at) {
            self::send_alert_now($client_id, $property_id, $old_price, $new_price, $score);
            return;
        }

        $args = array($client_id, $property_id, $old_price, $new_price);
        if (!wp_next_scheduled('inmopress_price_alerts_send', $args)) {
            wp_schedule_single_event($send_at, 'inmopress_price_alerts_send', $args);
        }
    }

    private static function send_alert_now($client_id, $property_id, $old_price, $new_price, $score = 0)
    {
        $client_id = absint($client_id);
        $property_id = absint($property_id);

        if (!$client_id || !$property_id) {
            return false;
        }

        $email = get_field('correo', $client_id);
        if (empty($email)) {
            return false;
        }

        $vars = self::build_variables($client_id, $property_id, $old_price, $new_price, $score);
        $template = self::get_email_template('price_drop');

        $subject = !empty($template['subject']) ? self::apply_variables($template['subject'], $vars) : $vars['subject_fallback'];
        $body = !empty($template['body']) ? self::apply_variables($template['body'], $vars) : self::render_fallback_template($vars);

        $headers = array('Content-Type: text/html; charset=UTF-8');
        if (!empty($template['from_name']) || !empty($template['from_email'])) {
            $from_name = !empty($template['from_name']) ? $template['from_name'] : get_bloginfo('name');
            $from_email = !empty($template['from_email']) ? $template['from_email'] : get_bloginfo('admin_email');
            $headers[] = sprintf('From: %s <%s>', $from_name, $from_email);
        }

        $sent = wp_mail($email, $subject, $body, $headers);
        if ($sent) {
            $drop_pct = $vars['drop_pct'];
            Inmopress_Price_Alert_Logger::log_sent($client_id, $property_id, $old_price, $new_price, $drop_pct);
        }

        return $sent;
    }

    private static function get_email_template($trigger)
    {
        $templates = get_posts(array(
            'post_type' => 'impress_email_tpl',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'email_trigger',
                    'value' => $trigger,
                    'compare' => '=',
                ),
                array(
                    'key' => 'email_status',
                    'value' => 'active',
                    'compare' => '=',
                ),
            ),
        ));

        if (empty($templates)) {
            return array();
        }

        $template_id = $templates[0]->ID;

        return array(
            'subject' => get_field('email_subject', $template_id),
            'body' => get_field('email_body', $template_id),
            'from_name' => get_field('email_from_name', $template_id),
            'from_email' => get_field('email_from_address', $template_id),
        );
    }

    private static function render_fallback_template($vars)
    {
        $path = INMOPRESS_PRICE_ALERTS_PATH . 'templates/email-price-drop.php';
        if (!file_exists($path)) {
            return nl2br($vars['plain_fallback']);
        }

        ob_start();
        include $path;
        return ob_get_clean();
    }

    private static function apply_variables($text, $vars)
    {
        $replacements = array();
        foreach ($vars as $key => $value) {
            $replacements['{{' . $key . '}}'] = $value;
        }

        return strtr($text, $replacements);
    }

    private static function build_variables($client_id, $property_id, $old_price, $new_price, $score)
    {
        $client_name = trim((string) get_field('nombre', $client_id) . ' ' . (string) get_field('apellidos', $client_id));
        if (empty($client_name)) {
            $client_name = get_the_title($client_id);
        }

        $property_title = get_the_title($property_id);
        $city_terms = get_the_terms($property_id, 'impress_city');
        $city = $city_terms && !is_wp_error($city_terms) && !empty($city_terms) ? $city_terms[0]->name : '';

        $price_diff = $old_price - $new_price;
        $drop_pct = $old_price > 0 ? round(($price_diff / $old_price) * 100, 2) : 0;

        $agent_name = '';
        $agent_phone = '';
        $agent_email = '';
        $agent_obj = get_field('agente', $property_id);
        if ($agent_obj) {
            $agent_id = is_object($agent_obj) ? $agent_obj->ID : (int) $agent_obj;
            if ($agent_id) {
                $agent_name = trim((string) get_field('nombre', $agent_id) . ' ' . (string) get_field('apellidos', $agent_id));
                $agent_phone = get_field('telefono', $agent_id);
                $agent_email = get_field('email', $agent_id);
            }
        }

        $property_desc = get_field('descripcion', $property_id);
        if (empty($property_desc)) {
            $property_desc = get_the_excerpt($property_id);
        }

        $image_url = get_the_post_thumbnail_url($property_id, 'large');

        $vars = array(
            'client_name' => $client_name,
            'property_title' => $property_title,
            'property_city' => $city,
            'property_url' => get_permalink($property_id),
            'property_image' => $image_url ? $image_url : '',
            'property_description' => $property_desc ? $property_desc : '',
            'old_price' => self::format_price($old_price),
            'new_price' => self::format_price($new_price),
            'price_diff' => self::format_price($price_diff),
            'drop_pct' => $drop_pct,
            'agent_name' => $agent_name,
            'agent_phone' => $agent_phone,
            'agent_email' => $agent_email,
            'agency_name' => get_bloginfo('name'),
            'score' => $score,
            'unsubscribe_url' => apply_filters('inmopress_price_alerts_unsubscribe_url', '', $client_id),
        );

        $vars['subject_fallback'] = sprintf('Bajada de precio: %s', $property_title);
        $vars['plain_fallback'] = sprintf("Hola %s,\n\nLa propiedad %s ha bajado de precio de %s a %s.\n\nVer detalles: %s\n", $client_name, $property_title, $vars['old_price'], $vars['new_price'], $vars['property_url']);

        return $vars;
    }

    private static function format_price($value)
    {
        if ($value === '' || $value === null) {
            return '';
        }

        return number_format((float) $value, 0, ',', '.') . ' EUR';
    }

    private static function should_alert_property($property_id)
    {
        $status = get_field('listing_status', $property_id);
        if (!empty($status) && $status !== 'active') {
            return false;
        }

        $reserved = get_field('reservada', $property_id);
        if ($reserved) {
            return false;
        }

        return get_post_status($property_id) === 'publish';
    }

    private static function get_next_schedule($frequency)
    {
        $frequency = (string) $frequency;
        $now = current_time('timestamp');

        if ($frequency === 'diaria') {
            $hour = (int) apply_filters('inmopress_price_alerts_daily_hour', 9);
            $minute = (int) apply_filters('inmopress_price_alerts_daily_minute', 0);
            $scheduled = mktime($hour, $minute, 0, (int) date('m', $now), (int) date('d', $now), (int) date('Y', $now));
            if ($scheduled <= $now) {
                $scheduled = strtotime('+1 day', $scheduled);
            }
            return $scheduled;
        }

        if ($frequency === 'semanal') {
            $hour = (int) apply_filters('inmopress_price_alerts_weekly_hour', 9);
            $minute = (int) apply_filters('inmopress_price_alerts_weekly_minute', 0);
            $weekday = (int) apply_filters('inmopress_price_alerts_weekly_weekday', 1); // 1 = Monday
            $scheduled = strtotime('next monday', $now);
            if ($weekday !== 1) {
                $scheduled = strtotime('next ' . self::weekday_name($weekday), $now);
            }
            $scheduled = mktime($hour, $minute, 0, (int) date('m', $scheduled), (int) date('d', $scheduled), (int) date('Y', $scheduled));
            return $scheduled;
        }

        return 0;
    }

    private static function weekday_name($weekday)
    {
        $map = array(
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        );

        return isset($map[$weekday]) ? $map[$weekday] : 'monday';
    }
}
