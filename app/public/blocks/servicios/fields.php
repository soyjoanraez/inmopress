<?php
/**
 * ACF Fields for Servicios Inmobiliaria Block
 */

acf_add_local_field_group(array(
    'key' => 'group_servicios',
    'title' => 'Servicios Inmobiliaria',
    'fields' => array(
        array(
            'key' => 'field_servicios_titulo',
            'label' => 'Título de la Sección',
            'name' => 'titulo',
            'type' => 'text',
            'instructions' => 'Título principal del bloque (opcional)',
            'wrapper' => array(
                'width' => '60',
            ),
        ),
        array(
            'key' => 'field_servicios_descripcion',
            'label' => 'Descripción',
            'name' => 'descripcion',
            'type' => 'textarea',
            'instructions' => 'Descripción debajo del título (opcional)',
            'rows' => 3,
            'wrapper' => array(
                'width' => '40',
            ),
        ),
        array(
            'key' => 'field_servicios_repeater',
            'label' => 'Servicios',
            'name' => 'servicios',
            'type' => 'repeater',
            'instructions' => 'Añade los servicios de tu inmobiliaria',
            'required' => 1,
            'layout' => 'row',
            'button_label' => 'Añadir Servicio',
            'sub_fields' => array(
                array(
                    'key' => 'field_servicio_icono',
                    'label' => 'Icono',
                    'name' => 'icono',
                    'type' => 'text',
                    'instructions' => 'Clase CSS del icono (ej: fas fa-home, fas fa-handshake)',
                    'wrapper' => array(
                        'width' => '20',
                    ),
                ),
                array(
                    'key' => 'field_servicio_titulo',
                    'label' => 'Título',
                    'name' => 'titulo',
                    'type' => 'text',
                    'instructions' => 'Nombre del servicio',
                    'required' => 1,
                    'wrapper' => array(
                        'width' => '30',
                    ),
                ),
                array(
                    'key' => 'field_servicio_descripcion',
                    'label' => 'Descripción',
                    'name' => 'descripcion',
                    'type' => 'textarea',
                    'instructions' => 'Descripción del servicio',
                    'rows' => 3,
                    'wrapper' => array(
                        'width' => '50',
                    ),
                ),
                array(
                    'key' => 'field_servicio_enlace',
                    'label' => 'Enlace',
                    'name' => 'enlace',
                    'type' => 'url',
                    'instructions' => 'URL de la página del servicio (opcional)',
                    'wrapper' => array(
                        'width' => '60',
                    ),
                ),
                array(
                    'key' => 'field_servicio_texto_enlace',
                    'label' => 'Texto del Enlace',
                    'name' => 'texto_enlace',
                    'type' => 'text',
                    'instructions' => 'Texto para el botón/enlace (ej: "Más información")',
                    'default_value' => 'Más información',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_servicio_enlace',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '40',
                    ),
                ),
            ),
        ),
        array(
            'key' => 'field_servicios_columnas',
            'label' => 'Columnas',
            'name' => 'columnas',
            'type' => 'select',
            'instructions' => 'Número de columnas en desktop',
            'choices' => array(
                '2' => '2 columnas',
                '3' => '3 columnas',
                '4' => '4 columnas',
            ),
            'default_value' => '3',
            'wrapper' => array(
                'width' => '30',
            ),
        ),
        array(
            'key' => 'field_servicios_estilo',
            'label' => 'Estilo',
            'name' => 'estilo',
            'type' => 'select',
            'instructions' => 'Estilo visual de las tarjetas',
            'choices' => array(
                'sencilla' => 'Sencilla (sin fondo)',
                'sombra' => 'Con Sombra',
                'borde' => 'Con Borde',
            ),
            'default_value' => 'sombra',
            'wrapper' => array(
                'width' => '30',
            ),
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/servicios',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
));