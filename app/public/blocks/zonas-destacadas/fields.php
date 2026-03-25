<?php
/**
 * ACF Fields for Zonas Destacadas Block
 */

acf_add_local_field_group(array(
    'key' => 'group_zonas_destacadas',
    'title' => 'Zonas Destacadas',
    'fields' => array(
        array(
            'key' => 'field_zonas_titulo',
            'label' => 'Título de la Sección',
            'name' => 'titulo',
            'type' => 'text',
            'instructions' => 'Título principal del bloque (opcional)',
            'wrapper' => array(
                'width' => '60',
            ),
        ),
        array(
            'key' => 'field_zonas_descripcion',
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
            'key' => 'field_zonas_repeater',
            'label' => 'Zonas',
            'name' => 'zonas',
            'type' => 'repeater',
            'instructions' => 'Añade las zonas o ciudades que quieres destacar',
            'required' => 1,
            'layout' => 'row',
            'button_label' => 'Añadir Zona',
            'sub_fields' => array(
                array(
                    'key' => 'field_zona_ciudad',
                    'label' => 'Ciudad (Taxonomía)',
                    'name' => 'ciudad',
                    'type' => 'taxonomy',
                    'instructions' => 'Selecciona la ciudad de la taxonomía (para contador automático)',
                    'taxonomy' => 'ciudad', // Ajustar según taxonomía del proyecto
                    'field_type' => 'select',
                    'allow_null' => 1,
                    'wrapper' => array(
                        'width' => '40',
                    ),
                ),
                array(
                    'key' => 'field_zona_nombre',
                    'label' => 'Nombre Personalizado',
                    'name' => 'nombre_zona',
                    'type' => 'text',
                    'instructions' => 'Nombre personalizado (si no quieres usar el de la taxonomía)',
                    'wrapper' => array(
                        'width' => '60',
                    ),
                ),
                array(
                    'key' => 'field_zona_imagen',
                    'label' => 'Imagen de la Zona',
                    'name' => 'imagen_zona',
                    'type' => 'image',
                    'instructions' => 'Imagen representativa de la zona',
                    'required' => 1,
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ),
                array(
                    'key' => 'field_zona_texto',
                    'label' => 'Texto Personalizado',
                    'name' => 'texto_custom',
                    'type' => 'textarea',
                    'instructions' => 'Descripción breve de la zona (opcional)',
                    'rows' => 3,
                ),
                array(
                    'key' => 'field_zona_enlace',
                    'label' => 'Enlace',
                    'name' => 'enlace_zona',
                    'type' => 'url',
                    'instructions' => 'URL para ver inmuebles de esta zona (opcional)',
                ),
            ),
        ),
        array(
            'key' => 'field_zonas_layout',
            'label' => 'Layout',
            'name' => 'layout',
            'type' => 'select',
            'instructions' => 'Diseño de visualización',
            'choices' => array(
                'grid' => 'Grid (Cuadrícula)',
                'carrusel' => 'Carrusel',
                'lista' => 'Lista',
            ),
            'default_value' => 'grid',
            'wrapper' => array(
                'width' => '25',
            ),
        ),
        array(
            'key' => 'field_zonas_columnas',
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
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_zonas_layout',
                        'operator' => '!=',
                        'value' => 'lista',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '25',
            ),
        ),
        array(
            'key' => 'field_zonas_contador',
            'label' => 'Mostrar Contador',
            'name' => 'mostrar_contador',
            'type' => 'true_false',
            'instructions' => 'Mostrar número de inmuebles por zona',
            'default_value' => 1,
            'ui' => 1,
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
                'value' => 'acf/zonas-destacadas',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
));