<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_Price_Alert_Logger
{
    public static function init()
    {
        // Reserved for future hooks.
    }

    public static function create_table()
    {
        global $wpdb;

        $table = self::get_table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            property_id BIGINT UNSIGNED NOT NULL,
            client_id BIGINT UNSIGNED NOT NULL,
            old_price DECIMAL(14,2) NOT NULL,
            new_price DECIMAL(14,2) NOT NULL,
            drop_pct DECIMAL(6,2) NOT NULL DEFAULT 0,
            sent_at DATETIME NOT NULL,
            channel VARCHAR(20) NOT NULL DEFAULT 'email',
            PRIMARY KEY  (id),
            KEY property_client (property_id, client_id),
            KEY client_sent (client_id, sent_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function was_notified_recently($client_id, $property_id, $cooldown_days)
    {
        global $wpdb;

        $client_id = absint($client_id);
        $property_id = absint($property_id);
        $cooldown_days = max(0, (int) $cooldown_days);

        if (!$client_id || !$property_id || $cooldown_days <= 0) {
            return false;
        }

        $table = self::get_table_name();
        $cutoff = gmdate('Y-m-d H:i:s', time() - ($cooldown_days * DAY_IN_SECONDS));

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$table} WHERE client_id = %d AND property_id = %d AND sent_at >= %s",
            $client_id,
            $property_id,
            $cutoff
        ));

        return $count > 0;
    }

    public static function count_daily_notifications($client_id)
    {
        global $wpdb;

        $client_id = absint($client_id);
        if (!$client_id) {
            return 0;
        }

        $table = self::get_table_name();
        $today = current_time('Y-m-d');
        $start = $today . ' 00:00:00';
        $end = $today . ' 23:59:59';

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$table} WHERE client_id = %d AND sent_at BETWEEN %s AND %s",
            $client_id,
            $start,
            $end
        ));

        return (int) $count;
    }

    public static function log_sent($client_id, $property_id, $old_price, $new_price, $drop_pct)
    {
        global $wpdb;

        $client_id = absint($client_id);
        $property_id = absint($property_id);

        if (!$client_id || !$property_id) {
            return false;
        }

        $table = self::get_table_name();
        $sent_at = current_time('mysql');

        $result = $wpdb->insert(
            $table,
            array(
                'property_id' => $property_id,
                'client_id' => $client_id,
                'old_price' => $old_price,
                'new_price' => $new_price,
                'drop_pct' => $drop_pct,
                'sent_at' => $sent_at,
                'channel' => 'email',
            ),
            array('%d', '%d', '%f', '%f', '%f', '%s', '%s')
        );

        if ($result) {
            self::touch_client_last_alert($client_id, $sent_at);
        }

        return (bool) $result;
    }

    private static function touch_client_last_alert($client_id, $datetime)
    {
        if (function_exists('update_field')) {
            update_field('alertas_ultima_fecha', $datetime, $client_id);
            return;
        }

        update_post_meta($client_id, 'alertas_ultima_fecha', $datetime);
    }

    private static function get_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'inmopress_price_alerts';
    }
}
