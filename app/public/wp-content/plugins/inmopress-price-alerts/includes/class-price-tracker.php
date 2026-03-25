<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_Price_Tracker
{
    private static $processing = array();

    public static function init()
    {
        add_action('acf/save_post', array(__CLASS__, 'handle_property_save'), 35);
    }

    public static function handle_property_save($post_id)
    {
        if (get_post_type($post_id) !== 'impress_property') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (isset(self::$processing[$post_id])) {
            return;
        }

        self::$processing[$post_id] = true;

        $new_price = self::get_property_price($post_id);
        if ($new_price <= 0) {
            self::update_last_price($post_id, $new_price);
            unset(self::$processing[$post_id]);
            return;
        }

        $previous_price = (float) get_post_meta($post_id, '_inmopress_last_price', true);

        if ($previous_price > 0 && $new_price !== $previous_price) {
            self::update_field_value($post_id, 'precio_anterior', $previous_price);
        }

        if ($previous_price > 0 && $new_price < $previous_price) {
            $drop_pct = round((($previous_price - $new_price) / $previous_price) * 100, 2);

            self::update_field_value($post_id, 'fecha_ultima_bajada', current_time('mysql'));
            self::update_field_value($post_id, 'porcentaje_ultima_bajada', $drop_pct);

            do_action('inmopress_price_drop', $post_id, $previous_price, $new_price, array(
                'drop_pct' => $drop_pct,
            ));
        }

        self::update_last_price($post_id, $new_price);
        unset(self::$processing[$post_id]);
    }

    private static function update_last_price($post_id, $price)
    {
        update_post_meta($post_id, '_inmopress_last_price', $price);
    }

    private static function update_field_value($post_id, $field_name, $value)
    {
        if (function_exists('update_field')) {
            update_field($field_name, $value, $post_id);
            return;
        }

        update_post_meta($post_id, $field_name, $value);
    }

    private static function get_property_price($post_id)
    {
        $purpose = get_field('proposito', $post_id);
        $price = 0;

        if ($purpose === 'alquiler') {
            $price = get_field('precio_alquiler', $post_id);
        } elseif ($purpose === 'venta') {
            $price = get_field('precio_venta', $post_id);
        }

        if (!$price) {
            $price = get_field('precio_venta', $post_id);
        }

        if (!$price) {
            $price = get_field('precio_alquiler', $post_id);
        }

        return $price ? (float) $price : 0.0;
    }
}
