<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_ACF_Forms
{
    /**
     * Obtiene los argumentos del formulario ACF
     *
     * @param int|string $post_id ID del post o 'new_post'
     * @param string $post_type Tipo de post
     * @param string $return_url URL de retorno (opcional)
     * @param array $field_groups Grupos de campos ACF (opcional)
     * @return array
     */
    public static function get_form_args($post_id, $post_type = 'impress_property', $return_url = '', $field_groups = array())
    {
        $is_new = ($post_id === 'new_post');
        $label = self::get_label($post_type);

        // URL de retorno por defecto
        if (empty($return_url)) {
            if (class_exists('Inmopress_Shortcodes')) {
                $return_url = Inmopress_Shortcodes::panel_url('properties');
            } else {
                $return_url = home_url('/mi-panel/?tab=properties');
            }
        }

        $args = array(
            'post_id'       => $post_id,
            'post_title'    => $is_new && $post_type === 'impress_property',
            'post_content'  => true,
            'uploader'      => 'wp',
            'form'          => true,
            'form_attributes' => array(
                'class' => 'inmopress-acf-form',
            ),
            'new_post' => array(
                'post_type'   => $post_type,
                'post_status' => 'draft',
            ),
            'return'          => $return_url,
            'submit_value'    => $is_new
                ? sprintf(__('Crear %s', 'inmopress'), $label)
                : sprintf(__('Actualizar %s', 'inmopress'), $label),
            'updated_message' => $is_new
                ? sprintf(__('%s creado correctamente.', 'inmopress'), $label)
                : sprintf(__('%s actualizado correctamente.', 'inmopress'), $label),
            'field_groups'    => $field_groups,
            'html_updated_message' => '<div class="acf-notice -success"><p>%s</p></div>',
        );

        return $args;
    }

    /**
     * Obtiene la etiqueta legible del tipo de post
     *
     * @param string $post_type
     * @return string
     */
    private static function get_label($post_type)
    {
        $labels = array(
            'impress_property'    => __('Inmueble', 'inmopress'),
            'impress_client'      => __('Cliente', 'inmopress'),
            'impress_visit'       => __('Visita', 'inmopress'),
            'impress_transaction' => __('Transacción', 'inmopress'),
            'impress_owner'       => __('Propietario', 'inmopress'),
            'impress_agent'       => __('Agente', 'inmopress'),
            'impress_event'       => __('Evento', 'inmopress'),
        );

        return isset($labels[$post_type]) ? $labels[$post_type] : __('Elemento', 'inmopress');
    }
}
