<?php
/**
 * ACF Fields for Stats/Números Destacados Block
 */

acf_add_local_field_group(array(
    'key' => 'group_stats_numeros',
    'title' => 'Stats/Números Destacados',
    'fields' => array(
        array(
            'key' => 'field_stats_repeater',
            'label' => 'Estadísticas',
            'name' => 'stats',
            'type' => 'repeater',
            'instructions' => 'Añade las estadísticas que quieres mostrar',
            'required' => 1,
            'layout' => 'table',
            'button_label' => 'Añadir Estadística',
            'sub_fields' => array(
                array(
                    'key' => 'field_stat_numero',
                    'label' => 'Número',
                    'name' => 'numero',
                    'type' => 'text',
                    'instructions' => 'Ej: 500, 1000, 25',
                    'required' => 1,
                    'wrapper' => array(
                        'width' => '25',
                    ),
                ),
                array(
                    'key' => 'field_stat_simbolo',
                    'label' => 'Símbolo',
                    'name' => 'simbolo',
                    'type' => 'text',
                    'instructions' => 'Ej: +, %, €, años',
                    'wrapper' => array(
                        'width' => '15',
                    ),
                ),
                array(
                    'key' => 'field_stat_texto',
                    'label' => 'Texto Descriptivo',
                    'name' => 'texto_descriptivo',
                    'type' => 'text',
                    'instructions' => 'Ej: Inmuebles vendidos, Clientes satisfechos',
                    'required' => 1,
                    'wrapper' => array(
                        'width' => '40',
                    ),
                ),
                array(
                    'key' => 'field_stat_icon',
                    'label' => 'Icono',
                    'name' => 'icon',
                    'type' => 'text',
                    'instructions' => 'Clase CSS del icono (ej: fas fa-home)',
                    'wrapper' => array(
                        'width' => '20',
                    ),
                ),
            ),
        ),
        array(
            'key' => 'field_stats_layout',
            'label' => 'Diseño',
            'name' => 'layout',
            'type' => 'radio',
            'choices' => array(
                'grid' => 'Grid (Cuadrícula)',
                'horizontal' => 'Horizontal (Línea)',
            ),
            'default_value' => 'grid',
            'layout' => 'horizontal',
            'wrapper' => array(
                'width' => '25',
            ),
        ),
        array(
            'key' => 'field_stats_columns',
            'label' => 'Columnas',
            'name' => 'columns',
            'type' => 'select',
            'instructions' => 'Número de columnas en desktop',
            'choices' => array(
                '2' => '2 columnas',
                '3' => '3 columnas',
                '4' => '4 columnas',
            ),
            'default_value' => '4',
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_stats_layout',
                        'operator' => '==',
                        'value' => 'grid',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '25',
            ),
        ),
        array(
            'key' => 'field_stats_bg_color',
            'label' => 'Color de Fondo',
            'name' => 'background_color',
            'type' => 'color_picker',
            'default_value' => '#f8f9fa',
            'wrapper' => array(
                'width' => '25',
            ),
        ),
        array(
            'key' => 'field_stats_text_color',
            'label' => 'Color de Texto',
            'name' => 'text_color',
            'type' => 'color_picker',
            'default_value' => '#333333',
            'wrapper' => array(
                'width' => '25',
            ),
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/stats-numeros',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
));