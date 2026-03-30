<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_Price_Alerts_ACF
{
    public static function init()
    {
        add_action('acf/init', array(__CLASS__, 'register_fields'));
        add_filter('acf/load_field/name=email_trigger', array(__CLASS__, 'extend_email_triggers'));
    }

    public static function register_fields()
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_inmopress_price_alerts_property',
            'title' => 'Inmuebles - Alertas de precio',
            'fields' => array(
                array(
                    'key' => 'field_precio_anterior',
                    'label' => 'Precio anterior',
                    'name' => 'precio_anterior',
                    'type' => 'number',
                    'required' => 0,
                    'wrapper' => array('width' => '33'),
                    'append' => 'EUR',
                ),
                array(
                    'key' => 'field_fecha_ultima_bajada',
                    'label' => 'Fecha ultima bajada',
                    'name' => 'fecha_ultima_bajada',
                    'type' => 'date_time_picker',
                    'required' => 0,
                    'wrapper' => array('width' => '33'),
                    'display_format' => 'd/m/Y H:i',
                    'return_format' => 'Y-m-d H:i:s',
                ),
                array(
                    'key' => 'field_porcentaje_ultima_bajada',
                    'label' => 'Porcentaje ultima bajada',
                    'name' => 'porcentaje_ultima_bajada',
                    'type' => 'number',
                    'required' => 0,
                    'wrapper' => array('width' => '34'),
                    'append' => '%',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_property',
                    ),
                ),
            ),
        ));

        acf_add_local_field_group(array(
            'key' => 'group_inmopress_price_alerts_client',
            'title' => 'Clientes - Alertas de precio',
            'fields' => array(
                array(
                    'key' => 'field_alertas_bajada_precio',
                    'label' => 'Recibir alertas de bajada de precio',
                    'name' => 'alertas_bajada_precio',
                    'type' => 'true_false',
                    'default_value' => 1,
                    'ui' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_alertas_frecuencia',
                    'label' => 'Frecuencia alertas',
                    'name' => 'alertas_frecuencia',
                    'type' => 'select',
                    'choices' => array(
                        'inmediata' => 'Inmediata',
                        'diaria' => 'Diaria',
                        'semanal' => 'Semanal',
                    ),
                    'default_value' => 'inmediata',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_alertas_ultima_fecha',
                    'label' => 'Ultima alerta recibida',
                    'name' => 'alertas_ultima_fecha',
                    'type' => 'date_time_picker',
                    'required' => 0,
                    'wrapper' => array('width' => '34'),
                    'display_format' => 'd/m/Y H:i',
                    'return_format' => 'Y-m-d H:i:s',
                ),
                array(
                    'key' => 'field_cliente_favoritos',
                    'label' => 'Favoritos',
                    'name' => 'favoritos',
                    'type' => 'relationship',
                    'post_type' => array('impress_property'),
                    'filters' => array('search'),
                    'return_format' => 'id',
                    'instructions' => 'Propiedades marcadas como favoritas por el cliente.',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_client',
                    ),
                ),
            ),
        ));
    }

    public static function extend_email_triggers($field)
    {
        if (!isset($field['choices']) || !is_array($field['choices'])) {
            $field['choices'] = array();
        }

        if (!isset($field['choices']['price_drop'])) {
            $field['choices']['price_drop'] = 'Bajada de precio';
        }

        return $field;
    }
}
