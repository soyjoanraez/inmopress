<?php
/**
 * ACF Fields for Destacado Promoción Block
 */

acf_add_local_field_group(array(
    'key' => 'group_destacado_promocion',
    'title' => 'Destacado Promoción',
    'fields' => array(
        array(
            'key' => 'field_promocion_select',
            'label' => 'Seleccionar Promoción',
            'name' => 'promocion',
            'type' => 'post_object',
            'instructions' => 'Selecciona la promoción que quieres destacar',
            'required' => 1,
            'post_type' => array('promocion'), // Ajustar según el CPT de promociones
            'taxonomy' => array(),
            'allow_null' => 0,
            'multiple' => 0,
            'return_format' => 'object',
            'ui' => 1,
        ),
        array(
            'key' => 'field_promocion_mostrar',
            'label' => 'Elementos a Mostrar',
            'name' => 'mostrar',
            'type' => 'checkbox',
            'instructions' => 'Selecciona qué elementos mostrar de la promoción',
            'choices' => array(
                'galeria' => 'Galería de Imágenes',
                'descripcion' => 'Descripción Completa',
                'inmuebles' => 'Inmuebles Relacionados',
                'plano' => 'Plano de la Promoción',
            ),
            'default_value' => array('galeria', 'descripcion'),
            'layout' => 'vertical',
            'toggle' => 1,
        ),
        array(
            'key' => 'field_promocion_layout',
            'label' => 'Layout',
            'name' => 'layout',
            'type' => 'radio',
            'instructions' => 'Diseño de visualización del bloque',
            'choices' => array(
                'completo' => 'Completo (2 columnas)',
                'compacto' => 'Compacto (imagen pequeña)',
                'lateral' => 'Lateral (horizontal)',
            ),
            'default_value' => 'completo',
            'layout' => 'horizontal',
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/destacado-promocion',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
));

// Campos para el CPT Promoción (opcional - para referencia)
if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
        'key' => 'group_promocion_fields',
        'title' => 'Datos de la Promoción',
        'fields' => array(
            array(
                'key' => 'field_promocion_descripcion',
                'label' => 'Descripción',
                'name' => 'descripcion',
                'type' => 'wysiwyg',
                'instructions' => 'Descripción completa de la promoción',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ),
            array(
                'key' => 'field_promocion_galeria',
                'label' => 'Galería de Imágenes',
                'name' => 'galeria',
                'type' => 'gallery',
                'instructions' => 'Imágenes de la promoción',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'insert' => 'append',
                'library' => 'all',
            ),
            array(
                'key' => 'field_promocion_plano',
                'label' => 'Plano',
                'name' => 'plano',
                'type' => 'image',
                'instructions' => 'Plano general de la promoción',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
            ),
            array(
                'key' => 'field_promocion_precio_desde',
                'label' => 'Precio Desde',
                'name' => 'precio_desde',
                'type' => 'number',
                'instructions' => 'Precio mínimo de los inmuebles (solo número)',
                'min' => 0,
                'step' => 1000,
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            array(
                'key' => 'field_promocion_ubicacion',
                'label' => 'Ubicación',
                'name' => 'ubicacion',
                'type' => 'text',
                'instructions' => 'Dirección o zona de la promoción',
                'wrapper' => array(
                    'width' => '40',
                ),
            ),
            array(
                'key' => 'field_promocion_estado',
                'label' => 'Estado',
                'name' => 'estado',
                'type' => 'select',
                'instructions' => 'Estado actual de la promoción',
                'choices' => array(
                    'disponible' => 'Disponible',
                    'en-construccion' => 'En Construcción',
                    'agotado' => 'Agotado',
                ),
                'default_value' => 'disponible',
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 1,
                'ajax' => 0,
                'wrapper' => array(
                    'width' => '35',
                ),
            ),
            array(
                'key' => 'field_promocion_fecha_entrega',
                'label' => 'Fecha de Entrega',
                'name' => 'fecha_entrega',
                'type' => 'text',
                'instructions' => 'Fecha estimada de entrega (ej: "2º trimestre 2024")',
                'wrapper' => array(
                    'width' => '40',
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'promocion',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
}