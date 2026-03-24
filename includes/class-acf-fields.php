<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Clase para crear automáticamente todos los Field Groups de ACF
 */
class Inmopress_ACF_Fields
{

    /**
     * Crear todos los Field Groups
     */
    public static function create_all_field_groups()
    {
        if (!function_exists('acf_update_field_group')) {
            return false;
        }

        self::create_property_field_groups();
        self::create_client_field_groups();
        self::create_lead_field_groups();
        self::create_visit_field_groups();
        self::create_agency_field_groups();
        self::create_agent_field_groups();
        self::create_owner_field_groups();
        self::create_promotion_field_groups();
        self::create_transaction_field_groups();
        self::create_email_template_field_groups();
        self::create_event_field_groups();

        return true;
    }

    /**
     * Helper para guardar un field group en la base de datos
     */
    private static function save_field_group($field_group)
    {
        // Preparar el field group con valores por defecto
        $field_group['active'] = 1;
        $field_group['style'] = 'default';
        $field_group['label_placement'] = 'top';
        $field_group['instruction_placement'] = 'label';
        $field_group['hide_on_screen'] = '';
        $field_group['menu_order'] = 0;
        $field_group['position'] = 'normal';

        // Primero lo añadimos como local para que ACF lo procese
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group($field_group);
        }

        // Buscar si ya existe un field group con esta key
        $existing_post = get_posts(array(
            'post_type' => 'acf-field-group',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'key',
                    'value' => $field_group['key'],
                    'compare' => '=',
                ),
            ),
        ));

        // Preparar datos del post
        $post_data = array(
            'post_title' => $field_group['title'],
            'post_name' => sanitize_title($field_group['title']),
            'post_type' => 'acf-field-group',
            'post_status' => 'publish',
            'post_content' => '',
        );

        // Si existe, actualizar; si no, crear nuevo
        if (!empty($existing_post)) {
            $post_data['ID'] = $existing_post[0]->ID;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }

        if ($post_id && !is_wp_error($post_id)) {
            // Guardar todos los metadatos del field group
            update_post_meta($post_id, 'key', $field_group['key']);
            update_post_meta($post_id, 'title', $field_group['title']);
            update_post_meta($post_id, 'location', $field_group['location']);
            update_post_meta($post_id, 'menu_order', $field_group['menu_order']);
            update_post_meta($post_id, 'position', $field_group['position']);
            update_post_meta($post_id, 'style', $field_group['style']);
            update_post_meta($post_id, 'label_placement', $field_group['label_placement']);
            update_post_meta($post_id, 'instruction_placement', $field_group['instruction_placement']);
            update_post_meta($post_id, 'hide_on_screen', $field_group['hide_on_screen']);
            update_post_meta($post_id, 'active', $field_group['active']);

            // Guardar cada campo individualmente usando ACF
            if (!empty($field_group['fields']) && is_array($field_group['fields']) && function_exists('acf_update_field')) {
                foreach ($field_group['fields'] as $field) {
                    $field['parent'] = $post_id;
                    // Verificar si el campo ya existe
                    $existing_field = acf_get_field($field['key']);
                    if ($existing_field) {
                        $field['ID'] = $existing_field['ID'];
                    }
                    acf_update_field($field);
                }
            }
        }
    }

    /**
     * Field Groups para INMUEBLES
     */
    private static function create_property_field_groups()
    {
        // Field Group 1: Información General
        self::save_field_group(array(
            'key' => 'group_property_info',
            'title' => 'Inmuebles - Información General',
            'fields' => array(
                array(
                    'key' => 'field_publicada',
                    'label' => 'Publicada',
                    'name' => 'publicada',
                    'type' => 'true_false',
                    'default_value' => 1,
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_vendida',
                    'label' => 'Vendida',
                    'name' => 'vendida',
                    'type' => 'true_false',
                    'default_value' => 0,
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_reservada',
                    'label' => 'Reservada',
                    'name' => 'reservada',
                    'type' => 'true_false',
                    'default_value' => 0,
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_proposito',
                    'label' => 'Propósito',
                    'name' => 'proposito',
                    'type' => 'select',
                    'required' => 1,
                    'choices' => array(
                        'venta' => 'Venta',
                        'alquiler' => 'Alquiler',
                    ),
                    'default_value' => '',
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_referencia',
                    'label' => 'Referencia',
                    'name' => 'referencia',
                    'type' => 'text',
                    'required' => 1,
                    'placeholder' => 'N4569Z',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_zona',
                    'label' => 'Zona',
                    'name' => 'zona',
                    'type' => 'text',
                    'placeholder' => 'Mercado Colón',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_direccion',
                    'label' => 'Dirección',
                    'name' => 'direccion',
                    'type' => 'text',
                    'placeholder' => 'Avenida Tir de Colom 6',
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_descripcion',
                    'label' => 'Descripción',
                    'name' => 'descripcion',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_listing_status',
                    'label' => 'Estado del Listado',
                    'name' => 'listing_status',
                    'type' => 'select',
                    'choices' => array(
                        'active' => 'Activo',
                        'pending' => 'Reservado',
                        'sold' => 'Vendido',
                        'rented' => 'Alquilado',
                        'off_market' => 'Retirado',
                    ),
                    'default_value' => 'active',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_tax_operation',
                    'label' => 'Operación',
                    'name' => 'tax_operation',
                    'type' => 'taxonomy',
                    'taxonomy' => 'impress_operation',
                    'field_type' => 'select',
                    'allow_null' => 0,
                    'add_term' => 0,
                    'save_terms' => 1,
                    'load_terms' => 1,
                    'return_format' => 'id',
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_tax_type',
                    'label' => 'Tipo de Propiedad',
                    'name' => 'tax_property_type',
                    'type' => 'taxonomy',
                    'taxonomy' => 'impress_property_type',
                    'field_type' => 'select',
                    'allow_null' => 0,
                    'add_term' => 0,
                    'save_terms' => 1,
                    'load_terms' => 1,
                    'return_format' => 'id',
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_tax_city',
                    'label' => 'Ciudad',
                    'name' => 'tax_city',
                    'type' => 'taxonomy',
                    'taxonomy' => 'impress_city',
                    'field_type' => 'select',
                    'allow_null' => 0,
                    'add_term' => 1,
                    'save_terms' => 1,
                    'load_terms' => 1,
                    'return_format' => 'id',
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_tax_features',
                    'label' => 'Características',
                    'name' => 'tax_features',
                    'type' => 'taxonomy',
                    'taxonomy' => 'impress_features',
                    'field_type' => 'multi_select',
                    'allow_null' => 1,
                    'add_term' => 1,
                    'save_terms' => 1,
                    'load_terms' => 1,
                    'return_format' => 'id',
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_views_count',
                    'label' => 'Total Vistas',
                    'name' => 'views_count',
                    'type' => 'number',
                    'default_value' => 0,
                    'readonly' => 1,
                    'wrapper' => array('width' => '33'),
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

        // Field Group 2: Ubicación
        self::save_field_group(array(
            'key' => 'group_property_location',
            'title' => 'Inmuebles - Ubicación',
            'fields' => array(
                array(
                    'key' => 'field_coordenadas',
                    'label' => 'Coordenadas',
                    'name' => 'coordenadas',
                    'type' => 'google_map',
                    'center_lat' => '39.4699',
                    'center_lng' => '-0.3763',
                    'zoom' => 14,
                    'height' => 400,
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_gps_lat',
                    'label' => 'GPS Latitud',
                    'name' => 'gps_lat',
                    'type' => 'text',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_gps_lng',
                    'label' => 'GPS Longitud',
                    'name' => 'gps_lng',
                    'type' => 'text',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_ocultar_direccion',
                    'label' => 'Ocultar dirección',
                    'name' => 'ocultar_direccion',
                    'type' => 'true_false',
                    'default_value' => 0,
                    'ui' => 1,
                    'wrapper' => array('width' => '34'),
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

        // Field Group 3: Relaciones
        self::save_field_group(array(
            'key' => 'group_property_relations',
            'title' => 'Inmuebles - Relaciones',
            'fields' => array(
                array(
                    'key' => 'field_agencia_colaboradora',
                    'label' => 'Agencia colaboradora',
                    'name' => 'agencia_colaboradora',
                    'type' => 'post_object',
                    'post_type' => array('impress_agency'),
                    'allow_null' => 1,
                    'return_format' => 'object',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_agente',
                    'label' => 'Agente',
                    'name' => 'agente',
                    'type' => 'post_object',
                    'post_type' => array('impress_agent'),
                    'allow_null' => 1,
                    'return_format' => 'object',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_propietario',
                    'label' => 'Propietario',
                    'name' => 'propietario',
                    'type' => 'post_object',
                    'post_type' => array('impress_owner'),
                    'allow_null' => 1,
                    'return_format' => 'object',
                    'wrapper' => array('width' => '34'),
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

        // Field Group 4: Características Físicas
        self::save_field_group(array(
            'key' => 'group_property_physical',
            'title' => 'Inmuebles - Características Físicas',
            'fields' => array(
                array(
                    'key' => 'field_superficie_util',
                    'label' => 'Superficie útil',
                    'name' => 'superficie_util',
                    'type' => 'number',
                    'append' => 'm²',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_superficie_construida',
                    'label' => 'Superficie construida',
                    'name' => 'superficie_construida',
                    'type' => 'number',
                    'append' => 'm²',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_superficie_parcela',
                    'label' => 'Superficie parcela',
                    'name' => 'superficie_parcela',
                    'type' => 'number',
                    'append' => 'm²',
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_dormitorios',
                    'label' => 'Dormitorios',
                    'name' => 'dormitorios',
                    'type' => 'number',
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_banos',
                    'label' => 'Baños',
                    'name' => 'banos',
                    'type' => 'number',
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_banos_suite',
                    'label' => 'Baños suite',
                    'name' => 'banos_suite',
                    'type' => 'number',
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_cocinas',
                    'label' => 'Cocinas',
                    'name' => 'cocinas',
                    'type' => 'number',
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_salones',
                    'label' => 'Salones',
                    'name' => 'salones',
                    'type' => 'number',
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_balcones',
                    'label' => 'Balcones',
                    'name' => 'balcones',
                    'type' => 'number',
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_terrazas',
                    'label' => 'Terrazas',
                    'name' => 'terrazas',
                    'type' => 'number',
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_trasteros',
                    'label' => 'Trasteros',
                    'name' => 'trasteros',
                    'type' => 'number',
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_planta',
                    'label' => 'Planta',
                    'name' => 'planta',
                    'type' => 'select',
                    'choices' => array(
                        'Bajo' => 'Bajo',
                        '1º' => '1º',
                        '2º' => '2º',
                        '3º' => '3º',
                        '4º' => '4º',
                        '5º' => '5º',
                        'Ático' => 'Ático',
                    ),
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_plantas',
                    'label' => 'Plantas',
                    'name' => 'plantas',
                    'type' => 'number',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_ano',
                    'label' => 'Año construcción',
                    'name' => 'ano',
                    'type' => 'number',
                    'min' => 1800,
                    'max' => 2100,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_ano_construccion',
                    'label' => 'Año construcción (alternativo)',
                    'name' => 'ano_construccion',
                    'type' => 'number',
                    'min' => 1800,
                    'max' => 2100,
                    'wrapper' => array('width' => '34'),
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

        // Field Group 5: Detalles Técnicos
        self::save_field_group(array(
            'key' => 'group_property_technical',
            'title' => 'Inmuebles - Detalles Técnicos',
            'fields' => array(
                array(
                    'key' => 'field_orientacion',
                    'label' => 'Orientación',
                    'name' => 'orientacion',
                    'type' => 'select',
                    'choices' => array(
                        'norte' => 'Norte',
                        'sur' => 'Sur',
                        'este' => 'Este',
                        'oeste' => 'Oeste',
                        'noreste' => 'Noreste',
                        'noroeste' => 'Noroeste',
                        'sureste' => 'Sureste',
                        'suroeste' => 'Suroeste',
                        'este_oeste' => 'Este-Oeste',
                        'norte_sur' => 'Norte-Sur',
                    ),
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_tipo_suelo',
                    'label' => 'Tipo suelo',
                    'name' => 'tipo_suelo',
                    'type' => 'select',
                    'choices' => array(
                        'marmol' => 'Mármol',
                        'parquet' => 'Parquet',
                        'ceramicas' => 'Cerámicas',
                        'madera' => 'Madera',
                        'terrazo' => 'Terrazo',
                        'laminado' => 'Laminado',
                        'vinilo' => 'Vinilo',
                        'hormigon' => 'Hormigón pulido',
                        'moqueta' => 'Moqueta',
                    ),
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_tipo_ventanas',
                    'label' => 'Tipo ventanas',
                    'name' => 'tipo_ventanas',
                    'type' => 'select',
                    'choices' => array(
                        'climalit' => 'Climalit',
                        'doble' => 'Doble cristal',
                        'pvc' => 'PVC',
                        'aluminio' => 'Aluminio',
                        'madera' => 'Madera',
                        'corredizas' => 'Corredizas',
                        'abatibles' => 'Abatibles',
                        'oscilobatientes' => 'Oscilobatientes',
                    ),
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_certificacion_energetica',
                    'label' => 'Certificación energética',
                    'name' => 'certificacion_energetica',
                    'type' => 'select',
                    'choices' => array(
                        'tramite' => 'En trámite',
                        'sin' => 'Sin certificación',
                        'a' => 'A',
                        'b' => 'B',
                        'c' => 'C',
                        'd' => 'D',
                        'e' => 'E',
                        'f' => 'F',
                        'g' => 'G',
                        'pendiente' => 'Pendiente renovación',
                        'caducada' => 'Caducada',
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_ficha_energetica',
                    'label' => 'Ficha energética',
                    'name' => 'ficha_energetica',
                    'type' => 'file',
                    'return_format' => 'url',
                    'mime_types' => 'pdf',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_tipo_calefaccion',
                    'label' => 'Tipo calefacción',
                    'name' => 'tipo_calefaccion',
                    'type' => 'select',
                    'choices' => array(
                        'central' => 'Calefacción central',
                        'gas' => 'Individual (gas)',
                        'electrica' => 'Individual (eléctrica)',
                        'radiadores' => 'Radiadores eléctricos',
                        'suelo_electrico' => 'Suelo radiante eléctrico',
                        'suelo_agua' => 'Suelo radiante agua',
                        'gasoleo' => 'Gasóleo',
                        'biomasa_pellets' => 'Biomasa pellets',
                        'biomasa_lena' => 'Biomasa leña',
                        'solar' => 'Energía solar',
                        'bomba' => 'Bomba de calor',
                        'radiadores_aceite' => 'Radiadores aceite',
                        'estufas_gas' => 'Estufas gas',
                        'estufas_electric' => 'Estufas eléctricas',
                        'no' => 'Sin calefacción',
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_jardin_tipo',
                    'label' => 'Jardín tipo',
                    'name' => 'jardin_tipo',
                    'type' => 'select',
                    'choices' => array(
                        'privado' => 'Privado',
                        'comunitario' => 'Comunitario',
                        'patio' => 'Patio/terraza',
                        'zonas' => 'Zonas verdes',
                    ),
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_piscina_tipo',
                    'label' => 'Piscina tipo',
                    'name' => 'piscina_tipo',
                    'type' => 'select',
                    'choices' => array(
                        'privada' => 'Privada',
                        'comunitaria' => 'Comunitaria',
                        'aire' => 'Aire libre',
                        'cubierta' => 'Cubierta',
                        'climatizada' => 'Climatizada',
                        'infinita' => 'Infinita',
                    ),
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_amueblado',
                    'label' => 'Amueblado',
                    'name' => 'amueblado',
                    'type' => 'select',
                    'choices' => array(
                        'totalmente' => 'Totalmente',
                        'parcialmente' => 'Parcialmente',
                        'sin' => 'Sin amueblar',
                        'lujo' => 'De lujo',
                        'basico' => 'Básico',
                        'moderno' => 'Moderno',
                        'clasico' => 'Clásico',
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_estacionamiento',
                    'label' => 'Estacionamiento',
                    'name' => 'estacionamiento',
                    'type' => 'select',
                    'choices' => array(
                        'pago' => 'Zona de pago',
                        'publico' => 'Público',
                        'privado' => 'Privado',
                        'individual' => 'Individual',
                        'comunitario' => 'Comunitario',
                        'subterraneo' => 'Subterráneo',
                        'aire_libre' => 'Aire libre',
                        'cubierto' => 'Cubierto',
                        'calle' => 'En calle',
                        '24h' => '24 horas',
                        'vigilancia' => 'Con videovigilancia',
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_ascensor',
                    'label' => 'Ascensor',
                    'name' => 'ascensor',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_parking',
                    'label' => 'Parking',
                    'name' => 'parking',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_plazas_parking',
                    'label' => 'Plazas de parking',
                    'name' => 'plazas_parking',
                    'type' => 'number',
                    'min' => 0,
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_armarios_empotrados',
                    'label' => 'Armarios empotrados',
                    'name' => 'armarios_empotrados',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_aire_acondicionado',
                    'label' => 'Aire acondicionado',
                    'name' => 'aire_acondicionado',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_piscina',
                    'label' => 'Piscina',
                    'name' => 'piscina',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_jardin',
                    'label' => 'Jardín',
                    'name' => 'jardin',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '20'),
                ),
                array(
                    'key' => 'field_portero_automatico',
                    'label' => 'Portero automático',
                    'name' => 'portero_automatico',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '20'),
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

        // Field Group 6: Distancias
        $distance_choices = array(
            '1min' => '1 min',
            '2min' => '2 min',
            '3min' => '3 min',
            '4min' => '4 min',
            '5min' => '5 min',
            '10min' => '10 min',
            '15min' => '15 min',
            '20min' => '20 min',
            '25min' => '25 min',
            '30min' => '30 min',
            '35min' => '35 min',
            '40min' => '40 min',
            '45min' => '45 min',
            '50min' => '50 min',
            '55min' => '55 min',
            '1h' => '1h',
            '1-2h' => '1-2h',
            '2-3h' => '2-3h',
        );

        self::save_field_group(array(
            'key' => 'group_property_distances',
            'title' => 'Inmuebles - Distancias',
            'fields' => array(
                array(
                    'key' => 'field_dist_autobus',
                    'label' => 'Autobús',
                    'name' => 'dist_autobus',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_dist_metro',
                    'label' => 'Metro',
                    'name' => 'dist_metro',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_dist_colegios',
                    'label' => 'Colegios',
                    'name' => 'dist_colegios',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_dist_supermercados',
                    'label' => 'Supermercados',
                    'name' => 'dist_supermercados',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_dist_salud',
                    'label' => 'Centro salud',
                    'name' => 'dist_salud',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_dist_areas_verdes',
                    'label' => 'Áreas verdes',
                    'name' => 'dist_areas_verdes',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_dist_cc',
                    'label' => 'Centros comerciales',
                    'name' => 'dist_cc',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_dist_gimnasios',
                    'label' => 'Gimnasios',
                    'name' => 'dist_gimnasios',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_dist_farmacias',
                    'label' => 'Farmacias',
                    'name' => 'dist_farmacias',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_dist_ocio',
                    'label' => 'Teatros/Cines',
                    'name' => 'dist_ocio',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_dist_playa',
                    'label' => 'Playa',
                    'name' => 'dist_playa',
                    'type' => 'select',
                    'choices' => $distance_choices,
                    'wrapper' => array('width' => '50'),
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

        // Field Group 6b: Características Secundarias (Switches)
        self::save_field_group(array(
            'key' => 'group_property_features',
            'title' => 'Inmuebles - Características Secundarias',
            'fields' => array(
                array(
                    'key' => 'field_barbacoa',
                    'label' => 'Barbacoa',
                    'name' => 'barbacoa',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_chimenea',
                    'label' => 'Chimenea',
                    'name' => 'chimenea',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_jacuzzi',
                    'label' => 'Jacuzzi',
                    'name' => 'jacuzzi',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_spa',
                    'label' => 'Spa',
                    'name' => 'spa',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_gimnasio_privado',
                    'label' => 'Gimnasio privado',
                    'name' => 'gimnasio_privado',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_bodega',
                    'label' => 'Bodega',
                    'name' => 'bodega',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_lavanderia',
                    'label' => 'Lavandería',
                    'name' => 'lavanderia',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_despensa',
                    'label' => 'Despensa',
                    'name' => 'despensa',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_buhardilla',
                    'label' => 'Buhardilla',
                    'name' => 'buhardilla',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_sotano',
                    'label' => 'Sótano',
                    'name' => 'sotano',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_porche',
                    'label' => 'Porche',
                    'name' => 'porche',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_solarium',
                    'label' => 'Solarium',
                    'name' => 'solarium',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_zona_infantil',
                    'label' => 'Zona infantil',
                    'name' => 'zona_infantil',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_pista_tenis',
                    'label' => 'Pista tenis',
                    'name' => 'pista_tenis',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_pista_padel',
                    'label' => 'Pista padel',
                    'name' => 'pista_padel',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_lavabajillas',
                    'label' => 'Lavavajillas',
                    'name' => 'lavabajillas',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_gimnasio',
                    'label' => 'Gimnasio',
                    'name' => 'gimnasio',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_encimera_granito',
                    'label' => 'Encimera de granito',
                    'name' => 'encimera_granito',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_solar',
                    'label' => 'Solar',
                    'name' => 'solar',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_minusvalidos',
                    'label' => 'Adaptado minusválidos',
                    'name' => 'minusvalidos',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_luminoso',
                    'label' => 'Luminoso',
                    'name' => 'luminoso',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_horno',
                    'label' => 'Horno',
                    'name' => 'horno',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_puerta_blindada',
                    'label' => 'Puerta blindada',
                    'name' => 'puerta_blindada',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_patio',
                    'label' => 'Patio',
                    'name' => 'patio',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_conserje',
                    'label' => 'Conserje',
                    'name' => 'conserje',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_agua_potable',
                    'label' => 'Agua potable',
                    'name' => 'agua_potable',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_alarma',
                    'label' => 'Alarma',
                    'name' => 'alarma',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_vistas_mar',
                    'label' => 'Vistas al mar',
                    'name' => 'vistas_mar',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_vistas_montana',
                    'label' => 'Vistas a la montaña',
                    'name' => 'vistas_montana',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_suelo_radiante',
                    'label' => 'Suelo radiante',
                    'name' => 'suelo_radiante',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_aislamiento_termico',
                    'label' => 'Aislamiento Térmico',
                    'name' => 'aislamiento_termico',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_sistema_riego_automatico',
                    'label' => 'Sistema riego automático',
                    'name' => 'sistema_riego_automatico',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_internet',
                    'label' => 'Internet',
                    'name' => 'internet',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_sat',
                    'label' => 'SAT',
                    'name' => 'sat',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_vitroceramica',
                    'label' => 'Vitrocerámica',
                    'name' => 'vitroceramica',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_frigorifico',
                    'label' => 'Frigorífico',
                    'name' => 'frigorifico',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_microondas',
                    'label' => 'Microondas',
                    'name' => 'microondas',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_muebles_jardin',
                    'label' => 'Muebles jardín',
                    'name' => 'muebles_jardin',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '25'),
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

        // Field Group 6c: Costes y Gastos
        self::save_field_group(array(
            'key' => 'group_property_costs',
            'title' => 'Inmuebles - Costes y Gastos',
            'fields' => array(
                array(
                    'key' => 'field_ibi',
                    'label' => 'IBI (€/año)',
                    'name' => 'ibi',
                    'type' => 'number',
                    'append' => '€/año',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_impuesto_basura',
                    'label' => 'Impuesto basura',
                    'name' => 'impuesto_basura',
                    'type' => 'number',
                    'append' => '€/año',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_comunidad',
                    'label' => 'Comunidad (€/mes)',
                    'name' => 'comunidad',
                    'type' => 'number',
                    'append' => '€/mes',
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_gastos_comunidad',
                    'label' => 'Gastos de comunidad incluidos',
                    'name' => 'gastos_comunidad',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_co2_emisiones',
                    'label' => 'CO2 Emisiones',
                    'name' => 'co2_emisiones',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_otros_gastos',
                    'label' => 'Otros gastos',
                    'name' => 'otros_gastos',
                    'type' => 'textarea',
                    'wrapper' => array('width' => '100'),
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

        // Field Group 7: Datos Venta
        self::save_field_group(array(
            'key' => 'group_property_sale',
            'title' => 'Inmuebles - Datos Venta',
            'fields' => array(
                array(
                    'key' => 'field_precio_venta_propietario',
                    'label' => 'Precio venta deseado propietario',
                    'name' => 'precio_venta_propietario',
                    'type' => 'number',
                    'append' => '€',
                    'min' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_precio_minimo_venta',
                    'label' => 'Precio mínimo venta',
                    'name' => 'precio_minimo_venta',
                    'type' => 'number',
                    'append' => '€',
                    'min' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_precio_venta',
                    'label' => 'Precio venta',
                    'name' => 'precio_venta',
                    'type' => 'number',
                    'required' => 1,
                    'append' => '€',
                    'min' => 0,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_tipo_descuento_venta',
                    'label' => 'Tipo descuento venta',
                    'name' => 'tipo_descuento_venta',
                    'type' => 'select',
                    'choices' => array(
                        'cantidad' => 'Cantidad',
                        'porcentaje' => 'Porcentaje',
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_cantidad_descuento_venta',
                    'label' => 'Cantidad descuento venta',
                    'name' => 'cantidad_descuento_venta',
                    'type' => 'number',
                    'min' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_tipo_comision_venta',
                    'label' => 'Tipo comisión venta',
                    'name' => 'tipo_comision_venta',
                    'type' => 'select',
                    'choices' => array(
                        'cantidad' => 'Cantidad',
                        'porcentaje' => 'Porcentaje',
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_cantidad_comision_venta',
                    'label' => 'Cantidad comisión venta',
                    'name' => 'cantidad_comision_venta',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 100,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_gastos_notaria',
                    'label' => 'Gastos notaría',
                    'name' => 'gastos_notaria',
                    'type' => 'number',
                    'append' => '€',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_gastos_registro',
                    'label' => 'Gastos registro',
                    'name' => 'gastos_registro',
                    'type' => 'number',
                    'append' => '€',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_gastos_gestoria',
                    'label' => 'Gastos gestoría',
                    'name' => 'gastos_gestoria',
                    'type' => 'number',
                    'append' => '€',
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_price_sold',
                    'label' => 'Precio Final de Venta',
                    'name' => 'price_sold',
                    'type' => 'number',
                    'append' => '€',
                    'instructions' => 'Rellenar solo cuando se haya vendido',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_iva',
                    'label' => 'IVA incluido',
                    'name' => 'iva',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_permite_hipoteca',
                    'label' => 'Permite hipoteca',
                    'name' => 'permite_hipoteca',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_negociable',
                    'label' => 'Negociable',
                    'name' => 'negociable',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '34'),
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

        // Field Group 8: Datos Alquiler
        self::save_field_group(array(
            'key' => 'group_property_rent',
            'title' => 'Inmuebles - Datos Alquiler',
            'fields' => array(
                array(
                    'key' => 'field_precio_alquiler_propietario',
                    'label' => 'Precio alquiler deseado',
                    'name' => 'precio_alquiler_propietario',
                    'type' => 'number',
                    'append' => '€/mes',
                    'min' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_precio_alquiler',
                    'label' => 'Precio alquiler',
                    'name' => 'precio_alquiler',
                    'type' => 'number',
                    'required' => 1,
                    'append' => '€/mes',
                    'min' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_deposito',
                    'label' => 'Depósito/Fianza',
                    'name' => 'deposito',
                    'type' => 'number',
                    'append' => '€',
                    'min' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_fianza',
                    'label' => 'Fianza (alternativo)',
                    'name' => 'fianza',
                    'type' => 'number',
                    'append' => '€',
                    'min' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_tipo_descuento_alquiler',
                    'label' => 'Tipo descuento alquiler',
                    'name' => 'tipo_descuento_alquiler',
                    'type' => 'select',
                    'choices' => array(
                        'cantidad' => 'Cantidad',
                        'porcentaje' => 'Porcentaje',
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_cantidad_descuento_alquiler',
                    'label' => 'Cantidad descuento',
                    'name' => 'cantidad_descuento_alquiler',
                    'type' => 'number',
                    'min' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_tipo_comision_alquiler',
                    'label' => 'Tipo comisión alquiler',
                    'name' => 'tipo_comision_alquiler',
                    'type' => 'select',
                    'choices' => array(
                        'cantidad' => 'Cantidad',
                        'porcentaje' => 'Porcentaje',
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_cantidad_comision_alquiler',
                    'label' => 'Cantidad comisión',
                    'name' => 'cantidad_comision_alquiler',
                    'type' => 'number',
                    'min' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_mascotas_permitidas',
                    'label' => 'Mascotas permitidas',
                    'name' => 'mascotas_permitidas',
                    'type' => 'select',
                    'choices' => array(
                        'si' => 'Sí',
                        'no' => 'No',
                        'negociable' => 'Negociable',
                    ),
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_mascotas',
                    'label' => 'Permite mascotas (alternativo)',
                    'name' => 'mascotas',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_fumar_permitido',
                    'label' => 'Fumar permitido',
                    'name' => 'fumar_permitido',
                    'type' => 'select',
                    'choices' => array(
                        'si' => 'Sí',
                        'no' => 'No',
                    ),
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_fumadores',
                    'label' => 'Permite fumadores (alternativo)',
                    'name' => 'fumadores',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_periodo_pago',
                    'label' => 'Periodo pago',
                    'name' => 'periodo_pago',
                    'type' => 'select',
                    'choices' => array(
                        'semanal' => 'Semanal',
                        'mensual' => 'Mensual',
                        'trimestral' => 'Trimestral',
                    ),
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_plazo_minimo',
                    'label' => 'Plazo mínimo',
                    'name' => 'plazo_minimo',
                    'type' => 'select',
                    'choices' => array(
                        '1mes' => '1 mes',
                        '3meses' => '3 meses',
                        '6meses' => '6 meses',
                        '1ano' => '1 año',
                        'negociable' => 'Negociable',
                    ),
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_seguro',
                    'label' => 'Seguro',
                    'name' => 'seguro',
                    'type' => 'select',
                    'choices' => array(
                        'opcional' => 'Opcional',
                        'obligatorio' => 'Obligatorio',
                        'sin' => 'Sin seguro',
                        'incluido' => 'Incluido',
                        'arrendador' => 'A cargo arrendador',
                        'arrendatario' => 'A cargo arrendatario',
                    ),
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_fumar',
                    'label' => 'Fumar',
                    'name' => 'fumar',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_gastos_incluidos',
                    'label' => 'Gastos incluidos',
                    'name' => 'gastos_incluidos',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_duracion_minima',
                    'label' => 'Duración mínima',
                    'name' => 'duracion_minima',
                    'type' => 'number',
                    'append' => 'meses',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_equipado',
                    'label' => 'Equipado',
                    'name' => 'equipado',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_disponible_desde',
                    'label' => 'Disponible desde',
                    'name' => 'disponible_desde',
                    'type' => 'date_picker',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_requisitos',
                    'label' => 'Requisitos',
                    'name' => 'requisitos',
                    'type' => 'textarea',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_comision_agencia',
                    'label' => 'Comisión agencia',
                    'name' => 'comision_agencia',
                    'type' => 'number',
                    'append' => '€',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_renovacion_automatica',
                    'label' => 'Renovación automática',
                    'name' => 'renovacion_automatica',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => array('width' => '50'),
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

        // Field Group 9: Media
        self::save_field_group(array(
            'key' => 'group_property_media',
            'title' => 'Inmuebles - Media',
            'fields' => array(
                array(
                    'key' => 'field_galeria',
                    'label' => 'Galería de imágenes',
                    'name' => 'galeria',
                    'type' => 'gallery',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_plano',
                    'label' => 'Plano',
                    'name' => 'plano',
                    'type' => 'image',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_video',
                    'label' => 'Video (URL)',
                    'name' => 'video',
                    'type' => 'url',
                    'placeholder' => 'https://youtube.com/...',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_videos',
                    'label' => 'Vídeo (alternativo)',
                    'name' => 'videos',
                    'type' => 'url',
                    'placeholder' => 'https://youtube.com/...',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_virtual_tour',
                    'label' => 'Tour virtual (URL)',
                    'name' => 'virtual_tour',
                    'type' => 'url',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_tour_360',
                    'label' => 'Tour 360',
                    'name' => 'tour_360',
                    'type' => 'url',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_documentos',
                    'label' => 'Documentos',
                    'name' => 'documentos',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'wrapper' => array('width' => '100'),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_documento_archivo',
                            'label' => 'Archivo',
                            'name' => 'documento_archivo',
                            'type' => 'file',
                            'return_format' => 'url',
                        ),
                        array(
                            'key' => 'field_documento_nombre',
                            'label' => 'Nombre',
                            'name' => 'documento_nombre',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'field_documento_tipo',
                            'label' => 'Tipo',
                            'name' => 'documento_tipo',
                            'type' => 'select',
                            'choices' => array(
                                'contrato' => 'Contrato',
                                'nota_simple' => 'Nota simple',
                                'planos' => 'Planos',
                                'cedula' => 'Cédula',
                                'otro' => 'Otro',
                            ),
                        ),
                    ),
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
    }

    /**
     * Field Groups para CLIENTES
     */
    private static function create_client_field_groups()
    {
        // Grupo 1: Datos Personales
        self::save_field_group(array(
            'key' => 'group_client_personal',
            'title' => 'Clientes - Datos Personales',
            'fields' => array(
                array(
                    'key' => 'field_cliente_nombre',
                    'label' => 'Nombre',
                    'name' => 'nombre',
                    'type' => 'text',
                    'required' => 1,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_cliente_apellidos',
                    'label' => 'Apellidos',
                    'name' => 'apellidos',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_cliente_telefono',
                    'label' => 'Teléfono',
                    'name' => 'telefono',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_cliente_correo',
                    'label' => 'Correo',
                    'name' => 'correo',
                    'type' => 'email',
                    'required' => 1,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_cliente_direccion',
                    'label' => 'Dirección',
                    'name' => 'direccion',
                    'type' => 'textarea',
                    'wrapper' => array('width' => '100'),
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

        // Grupo 2: Clasificación
        self::save_field_group(array(
            'key' => 'group_client_status',
            'title' => 'Clientes - Clasificación',
            'fields' => array(
                array(
                    'key' => 'field_cliente_semaforo_estado',
                    'label' => 'Semáforo Estado',
                    'name' => 'semaforo_estado',
                    'type' => 'select',
                    'choices' => array(
                        'hot' => '🔴 HOT',
                        'warm' => '🟡 WARM',
                        'cold' => '🔵 COLD',
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_cliente_puntuacion',
                    'label' => 'Puntuación',
                    'name' => 'puntuacion',
                    'type' => 'range',
                    'min' => 0,
                    'max' => 10,
                    'step' => 1,
                    'wrapper' => array('width' => '50'),
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

        // Grupo 3: Preferencias de Búsqueda
        self::save_field_group(array(
            'key' => 'group_client_preferences',
            'title' => 'Clientes - Preferencias de Búsqueda',
            'fields' => array(
                array(
                    'key' => 'field_cliente_interes',
                    'label' => 'Interés',
                    'name' => 'interes',
                    'type' => 'select',
                    'choices' => array(
                        'compra' => 'Compra',
                        'alquiler' => 'Alquiler',
                        'inversion' => 'Inversión',
                    ),
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_cliente_presupuesto_min',
                    'label' => 'Presupuesto mínimo',
                    'name' => 'presupuesto_min',
                    'type' => 'number',
                    'append' => '€',
                    'min' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_cliente_presupuesto_max',
                    'label' => 'Presupuesto máximo',
                    'name' => 'presupuesto_max',
                    'type' => 'number',
                    'append' => '€',
                    'min' => 0,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_cliente_zona_interes',
                    'label' => 'Zona de interés',
                    'name' => 'zona_interes',
                    'type' => 'taxonomy',
                    'taxonomy' => 'impress_city',
                    'field_type' => 'multi_select',
                    'allow_null' => 1,
                    'add_term' => 0,
                    'save_terms' => 0,
                    'load_terms' => 0,
                    'return_format' => 'id',
                ),
                array(
                    'key' => 'field_cliente_dormitorios_min',
                    'label' => 'Dormitorios mínimos',
                    'name' => 'dormitorios_min',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 10,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_cliente_banos_min',
                    'label' => 'Baños mínimos',
                    'name' => 'banos_min',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 10,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_cliente_superficie_min',
                    'label' => 'Superficie mínima',
                    'name' => 'superficie_min',
                    'type' => 'number',
                    'append' => 'm²',
                    'min' => 0,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_cliente_notas_preferencias',
                    'label' => 'Notas de preferencias',
                    'name' => 'notas_preferencias',
                    'type' => 'textarea',
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

        // Grupo 4: Gestión
        self::save_field_group(array(
            'key' => 'group_client_management',
            'title' => 'Clientes - Gestión',
            'fields' => array(
                array(
                    'key' => 'field_cliente_agente_asignado',
                    'label' => 'Agente asignado',
                    'name' => 'agente_asignado',
                    'type' => 'post_object',
                    'post_type' => array('impress_agent'),
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_cliente_fecha_proximo_contacto',
                    'label' => 'Fecha próximo contacto',
                    'name' => 'fecha_proximo_contacto',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'd/m/Y',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_cliente_ultimo_contacto',
                    'label' => 'Último contacto',
                    'name' => 'impress_client_last_contact',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_cliente_notas_internas',
                    'label' => 'Notas internas',
                    'name' => 'notas_internas',
                    'type' => 'textarea',
                    'wrapper' => array('width' => '34'),
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

        // Grupo 5: Configuración
        self::save_field_group(array(
            'key' => 'group_client_config',
            'title' => 'Clientes - Configuración',
            'fields' => array(
                array(
                    'key' => 'field_cliente_newsletter_activo',
                    'label' => 'Newsletter activo',
                    'name' => 'newsletter_activo',
                    'type' => 'true_false',
                    'ui' => 1,
                    'default_value' => 0,
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_cliente_visitas_realizadas',
                    'label' => 'Visitas realizadas',
                    'name' => 'visitas_realizadas',
                    'type' => 'repeater',
                    'layout' => 'row',
                    'button_label' => 'Añadir visita',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_cliente_visita_inmueble',
                            'label' => 'Inmueble',
                            'name' => 'inmueble',
                            'type' => 'post_object',
                            'post_type' => array('impress_property'),
                            'return_format' => 'id',
                            'wrapper' => array('width' => '40'),
                        ),
                        array(
                            'key' => 'field_cliente_visita_fecha',
                            'label' => 'Fecha',
                            'name' => 'fecha',
                            'type' => 'date_picker',
                            'display_format' => 'd/m/Y',
                            'return_format' => 'd/m/Y',
                            'wrapper' => array('width' => '20'),
                        ),
                        array(
                            'key' => 'field_cliente_visita_valoracion',
                            'label' => 'Valoración',
                            'name' => 'valoracion',
                            'type' => 'range',
                            'min' => 1,
                            'max' => 5,
                            'step' => 1,
                            'wrapper' => array('width' => '20'),
                        ),
                        array(
                            'key' => 'field_cliente_visita_nota',
                            'label' => 'Nota',
                            'name' => 'nota',
                            'type' => 'text',
                            'wrapper' => array('width' => '20'),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_cliente_solicitudes',
                    'label' => 'Solicitudes',
                    'name' => 'solicitudes',
                    'type' => 'repeater',
                    'layout' => 'row',
                    'button_label' => 'Añadir solicitud',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_cliente_solicitud_inmueble',
                            'label' => 'Inmueble',
                            'name' => 'inmueble',
                            'type' => 'post_object',
                            'post_type' => array('impress_property'),
                            'return_format' => 'id',
                            'wrapper' => array('width' => '30'),
                        ),
                        array(
                            'key' => 'field_cliente_solicitud_fecha',
                            'label' => 'Fecha',
                            'name' => 'fecha',
                            'type' => 'date_picker',
                            'display_format' => 'd/m/Y',
                            'return_format' => 'd/m/Y',
                            'wrapper' => array('width' => '20'),
                        ),
                        array(
                            'key' => 'field_cliente_solicitud_tipo',
                            'label' => 'Tipo',
                            'name' => 'tipo',
                            'type' => 'select',
                            'choices' => array(
                                'info' => 'Info',
                                'visita' => 'Visita',
                                'tasacion' => 'Tasación',
                            ),
                            'wrapper' => array('width' => '25'),
                        ),
                        array(
                            'key' => 'field_cliente_solicitud_estado',
                            'label' => 'Estado',
                            'name' => 'estado',
                            'type' => 'select',
                            'choices' => array(
                                'pendiente' => 'Pendiente',
                                'atendida' => 'Atendida',
                                'cerrada' => 'Cerrada',
                            ),
                            'wrapper' => array('width' => '25'),
                        ),
                    ),
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

    /**
     * Field Groups para LEADS
     */
    private static function create_lead_field_groups()
    {
        // Grupo 1: Datos Personales
        self::save_field_group(array(
            'key' => 'group_lead_personal',
            'title' => 'Leads - Datos Personales',
            'fields' => array(
                array(
                    'key' => 'field_lead_nombre',
                    'label' => 'Nombre',
                    'name' => 'nombre',
                    'type' => 'text',
                    'required' => 1,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_lead_apellidos',
                    'label' => 'Apellidos',
                    'name' => 'apellidos',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_lead_telefono',
                    'label' => 'Teléfono',
                    'name' => 'telefono',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_lead_correo',
                    'label' => 'Correo',
                    'name' => 'correo',
                    'type' => 'email',
                    'required' => 1,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_lead_direccion',
                    'label' => 'Dirección',
                    'name' => 'direccion',
                    'type' => 'textarea',
                    'wrapper' => array('width' => '100'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_lead',
                    ),
                ),
            ),
        ));

        // Grupo 2: Clasificación
        self::save_field_group(array(
            'key' => 'group_lead_status',
            'title' => 'Leads - Clasificación',
            'fields' => array(
                array(
                    'key' => 'field_lead_semaforo_estado',
                    'label' => 'Semáforo Estado',
                    'name' => 'semaforo_estado',
                    'type' => 'select',
                    'choices' => array(
                        'hot' => '🔴 HOT',
                        'warm' => '🟡 WARM',
                        'cold' => '🔵 COLD',
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_lead_puntuacion',
                    'label' => 'Puntuación',
                    'name' => 'puntuacion',
                    'type' => 'range',
                    'min' => 0,
                    'max' => 10,
                    'step' => 1,
                    'wrapper' => array('width' => '50'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_lead',
                    ),
                ),
            ),
        ));

        // Grupo 3: Preferencias de Búsqueda
        self::save_field_group(array(
            'key' => 'group_lead_preferences',
            'title' => 'Leads - Preferencias de Búsqueda',
            'fields' => array(
                array(
                    'key' => 'field_lead_interes',
                    'label' => 'Interés',
                    'name' => 'interes',
                    'type' => 'select',
                    'choices' => array(
                        'compra' => 'Compra',
                        'alquiler' => 'Alquiler',
                        'inversion' => 'Inversión',
                    ),
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_lead_presupuesto_min',
                    'label' => 'Presupuesto mínimo',
                    'name' => 'presupuesto_min',
                    'type' => 'number',
                    'append' => '€',
                    'min' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_lead_presupuesto_max',
                    'label' => 'Presupuesto máximo',
                    'name' => 'presupuesto_max',
                    'type' => 'number',
                    'append' => '€',
                    'min' => 0,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_lead_zona_interes',
                    'label' => 'Zona de interés',
                    'name' => 'zona_interes',
                    'type' => 'taxonomy',
                    'taxonomy' => 'impress_city',
                    'field_type' => 'multi_select',
                    'allow_null' => 1,
                    'add_term' => 0,
                    'save_terms' => 0,
                    'load_terms' => 0,
                    'return_format' => 'id',
                ),
                array(
                    'key' => 'field_lead_dormitorios_min',
                    'label' => 'Dormitorios mínimos',
                    'name' => 'dormitorios_min',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 10,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_lead_banos_min',
                    'label' => 'Baños mínimos',
                    'name' => 'banos_min',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 10,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_lead_superficie_min',
                    'label' => 'Superficie mínima',
                    'name' => 'superficie_min',
                    'type' => 'number',
                    'append' => 'm²',
                    'min' => 0,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_lead_notas_preferencias',
                    'label' => 'Notas de preferencias',
                    'name' => 'notas_preferencias',
                    'type' => 'textarea',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_lead',
                    ),
                ),
            ),
        ));

        // Grupo 4: Gestión (+ conversión)
        self::save_field_group(array(
            'key' => 'group_lead_management',
            'title' => 'Leads - Gestión',
            'fields' => array(
                array(
                    'key' => 'field_lead_agente_asignado',
                    'label' => 'Agente asignado',
                    'name' => 'agente_asignado',
                    'type' => 'post_object',
                    'post_type' => array('impress_agent'),
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_lead_fecha_proximo_contacto',
                    'label' => 'Fecha próximo contacto',
                    'name' => 'fecha_proximo_contacto',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'd/m/Y',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_lead_notas_internas',
                    'label' => 'Notas internas',
                    'name' => 'notas_internas',
                    'type' => 'textarea',
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_lead_convertido_cliente',
                    'label' => 'Convertido a cliente',
                    'name' => 'convertido_cliente',
                    'type' => 'true_false',
                    'ui' => 1,
                    'default_value' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_lead_cliente_relacionado',
                    'label' => 'Cliente relacionado',
                    'name' => 'cliente_relacionado',
                    'type' => 'post_object',
                    'post_type' => array('impress_client'),
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_lead_fecha_conversion',
                    'label' => 'Fecha de conversión',
                    'name' => 'fecha_conversion',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'd/m/Y',
                    'wrapper' => array('width' => '34'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_lead',
                    ),
                ),
            ),
        ));

        // Grupo 5: Configuración
        self::save_field_group(array(
            'key' => 'group_lead_config',
            'title' => 'Leads - Configuración',
            'fields' => array(
                array(
                    'key' => 'field_lead_newsletter_activo',
                    'label' => 'Newsletter activo',
                    'name' => 'newsletter_activo',
                    'type' => 'true_false',
                    'ui' => 1,
                    'default_value' => 0,
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_lead_visitas_realizadas',
                    'label' => 'Visitas realizadas',
                    'name' => 'visitas_realizadas',
                    'type' => 'repeater',
                    'layout' => 'row',
                    'button_label' => 'Añadir visita',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_lead_visita_inmueble',
                            'label' => 'Inmueble',
                            'name' => 'inmueble',
                            'type' => 'post_object',
                            'post_type' => array('impress_property'),
                            'return_format' => 'id',
                            'wrapper' => array('width' => '40'),
                        ),
                        array(
                            'key' => 'field_lead_visita_fecha',
                            'label' => 'Fecha',
                            'name' => 'fecha',
                            'type' => 'date_picker',
                            'display_format' => 'd/m/Y',
                            'return_format' => 'd/m/Y',
                            'wrapper' => array('width' => '20'),
                        ),
                        array(
                            'key' => 'field_lead_visita_valoracion',
                            'label' => 'Valoración',
                            'name' => 'valoracion',
                            'type' => 'range',
                            'min' => 1,
                            'max' => 5,
                            'step' => 1,
                            'wrapper' => array('width' => '20'),
                        ),
                        array(
                            'key' => 'field_lead_visita_nota',
                            'label' => 'Nota',
                            'name' => 'nota',
                            'type' => 'text',
                            'wrapper' => array('width' => '20'),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_lead_solicitudes',
                    'label' => 'Solicitudes',
                    'name' => 'solicitudes',
                    'type' => 'repeater',
                    'layout' => 'row',
                    'button_label' => 'Añadir solicitud',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_lead_solicitud_inmueble',
                            'label' => 'Inmueble',
                            'name' => 'inmueble',
                            'type' => 'post_object',
                            'post_type' => array('impress_property'),
                            'return_format' => 'id',
                            'wrapper' => array('width' => '30'),
                        ),
                        array(
                            'key' => 'field_lead_solicitud_fecha',
                            'label' => 'Fecha',
                            'name' => 'fecha',
                            'type' => 'date_picker',
                            'display_format' => 'd/m/Y',
                            'return_format' => 'd/m/Y',
                            'wrapper' => array('width' => '20'),
                        ),
                        array(
                            'key' => 'field_lead_solicitud_tipo',
                            'label' => 'Tipo',
                            'name' => 'tipo',
                            'type' => 'select',
                            'choices' => array(
                                'info' => 'Info',
                                'visita' => 'Visita',
                                'tasacion' => 'Tasación',
                            ),
                            'wrapper' => array('width' => '25'),
                        ),
                        array(
                            'key' => 'field_lead_solicitud_estado',
                            'label' => 'Estado',
                            'name' => 'estado',
                            'type' => 'select',
                            'choices' => array(
                                'pendiente' => 'Pendiente',
                                'atendida' => 'Atendida',
                                'cerrada' => 'Cerrada',
                            ),
                            'wrapper' => array('width' => '25'),
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_lead',
                    ),
                ),
            ),
        ));
    }

    /**
     * Field Groups para VISITAS
     */
    private static function create_visit_field_groups()
    {
        self::save_field_group(array(
            'key' => 'group_visit_info',
            'title' => 'Visitas - Información',
            'fields' => array(
                array(
                    'key' => 'field_visit_fecha_hora',
                    'label' => 'Fecha y hora',
                    'name' => 'fecha_hora',
                    'type' => 'date_time_picker',
                    'required' => 1,
                    'display_format' => 'd/m/Y H:i',
                    'return_format' => 'd/m/Y H:i',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_visit_cliente',
                    'label' => 'Cliente',
                    'name' => 'cliente',
                    'type' => 'post_object',
                    'required' => 1,
                    'post_type' => array('impress_client'),
                    'return_format' => 'object',
                    'allow_null' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_visit_inmueble',
                    'label' => 'Inmueble',
                    'name' => 'inmueble',
                    'type' => 'post_object',
                    'required' => 1,
                    'post_type' => array('impress_property'),
                    'return_format' => 'object',
                    'allow_null' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_visit_agente',
                    'label' => 'Agente',
                    'name' => 'agente',
                    'type' => 'post_object',
                    'required' => 1,
                    'post_type' => array('impress_agent'),
                    'return_format' => 'object',
                    'allow_null' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_visit_duracion',
                    'label' => 'Duración',
                    'name' => 'duracion',
                    'type' => 'number',
                    'append' => 'minutos',
                    'min' => 0,
                    'default_value' => 30,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_visit_valoracion_cliente',
                    'label' => 'Valoración del cliente',
                    'name' => 'valoracion_cliente',
                    'type' => 'range',
                    'min' => 1,
                    'max' => 5,
                    'step' => 1,
                    'default_value' => 3,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_visit_interes_mostrado',
                    'label' => 'Interés mostrado',
                    'name' => 'interes_mostrado',
                    'type' => 'select',
                    'choices' => array(
                        'ninguno' => 'Ninguno',
                        'bajo' => 'Bajo',
                        'medio' => 'Medio',
                        'alto' => 'Alto',
                        'muy_alto' => 'Muy alto',
                    ),
                    'default_value' => 'medio',
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_visit_notas',
                    'label' => 'Notas de la visita',
                    'name' => 'notas',
                    'type' => 'textarea',
                    'rows' => 4,
                    'placeholder' => 'Observaciones durante la visita...',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_visit_firma_cliente',
                    'label' => 'Firma del cliente',
                    'name' => 'firma_cliente',
                    'type' => 'image',
                    'return_format' => 'url',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_visit_fotos_visita',
                    'label' => 'Fotos de la visita',
                    'name' => 'fotos_visita',
                    'type' => 'gallery',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'wrapper' => array('width' => '50'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_visit',
                    ),
                ),
            ),
        ));
    }

    /**
     * Field Groups para AGENCIAS
     */
    private static function create_agency_field_groups()
    {
        // Grupo 1: Contacto
        self::save_field_group(array(
            'key' => 'group_agency_info',
            'title' => 'Agencias - Contacto',
            'fields' => array(
                array(
                    'key' => 'field_agency_telefono',
                    'label' => 'Teléfono',
                    'name' => 'telefono',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_agency_email',
                    'label' => 'Email',
                    'name' => 'email',
                    'type' => 'email',
                ),
                array(
                    'key' => 'field_agency_web',
                    'label' => 'Web',
                    'name' => 'web',
                    'type' => 'url',
                ),
                array(
                    'key' => 'field_agency_direccion',
                    'label' => 'Dirección',
                    'name' => 'direccion',
                    'type' => 'textarea',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_agency',
                    ),
                ),
            ),
        ));

        // Grupo 2: Datos de la Agencia
        self::save_field_group(array(
            'key' => 'group_agency_details',
            'title' => 'Agencias - Datos de la Agencia',
            'fields' => array(
                array(
                    'key' => 'field_agency_nombre_comercial',
                    'label' => 'Nombre comercial',
                    'name' => 'nombre_comercial',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_agency_razon_social',
                    'label' => 'Razón social',
                    'name' => 'razon_social',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_agency_cif',
                    'label' => 'CIF',
                    'name' => 'cif',
                    'type' => 'text',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_agency_logo',
                    'label' => 'Logo',
                    'name' => 'logo',
                    'type' => 'image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_agency_ciudad',
                    'label' => 'Ciudad',
                    'name' => 'ciudad',
                    'type' => 'taxonomy',
                    'taxonomy' => 'impress_city',
                    'field_type' => 'select',
                    'allow_null' => 1,
                    'add_term' => 0,
                    'save_terms' => 0,
                    'load_terms' => 0,
                    'return_format' => 'id',
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_agency_codigo_postal',
                    'label' => 'Código postal',
                    'name' => 'codigo_postal',
                    'type' => 'text',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_agency_horario',
                    'label' => 'Horario',
                    'name' => 'horario',
                    'type' => 'textarea',
                    'wrapper' => array('width' => '67'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_agency',
                    ),
                ),
            ),
        ));

        // Grupo 3: Usuario
        self::save_field_group(array(
            'key' => 'group_agency_user',
            'title' => 'Agencias - Usuario',
            'fields' => array(
                array(
                    'key' => 'field_agency_usuario_wordpress',
                    'label' => 'Usuario WordPress',
                    'name' => 'usuario_wordpress',
                    'type' => 'user',
                    'role' => array('agencia', 'administrator'),
                    'allow_null' => 1,
                    'wrapper' => array('width' => '100'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_agency',
                    ),
                ),
            ),
        ));
    }

    /**
     * Field Groups para AGENTES
     */
    private static function create_agent_field_groups()
    {
        // Grupo 1: Vinculación y Datos
        self::save_field_group(array(
            'key' => 'group_agent_info',
            'title' => 'Agentes - Vinculación y Datos',
            'fields' => array(
                array(
                    'key' => 'field_agent_usuario_wordpress',
                    'label' => 'Usuario WordPress',
                    'name' => 'usuario_wordpress',
                    'type' => 'user',
                    'required' => 1,
                    'role' => array('agente', 'administrator'),
                    'allow_null' => 0,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_agent_agencia_relacionada',
                    'label' => 'Agencia relacionada',
                    'name' => 'agencia_relacionada',
                    'type' => 'post_object',
                    'post_type' => array('impress_agency'),
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_agent_nombre',
                    'label' => 'Nombre',
                    'name' => 'nombre',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_agent_apellidos',
                    'label' => 'Apellidos',
                    'name' => 'apellidos',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_agent_telefono',
                    'label' => 'Teléfono',
                    'name' => 'telefono',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_agent_email',
                    'label' => 'Email',
                    'name' => 'email',
                    'type' => 'email',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_agent_biografia',
                    'label' => 'Biografía',
                    'name' => 'biografia',
                    'type' => 'textarea',
                    'wrapper' => array('width' => '100'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_agent',
                    ),
                ),
            ),
        ));

        // Grupo 2: Perfil Público
        self::save_field_group(array(
            'key' => 'group_agent_profile',
            'title' => 'Agentes - Perfil Público',
            'fields' => array(
                array(
                    'key' => 'field_agent_avatar',
                    'label' => 'Avatar',
                    'name' => 'avatar',
                    'type' => 'image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_agent_activo',
                    'label' => 'Activo',
                    'name' => 'activo',
                    'type' => 'true_false',
                    'ui' => 1,
                    'default_value' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_agent_color_calendario',
                    'label' => 'Color calendario',
                    'name' => 'color_calendario',
                    'type' => 'color_picker',
                    'default_value' => '#3B82F6',
                    'wrapper' => array('width' => '34'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_agent',
                    ),
                ),
            ),
        ));
    }

    /**
     * Field Groups para PROPIETARIOS
     */
    private static function create_owner_field_groups()
    {
        self::save_field_group(array(
            'key' => 'group_owner_info',
            'title' => 'Propietarios - Información',
            'fields' => array(
                array(
                    'key' => 'field_owner_nombre',
                    'label' => 'Nombre',
                    'name' => 'nombre',
                    'type' => 'text',
                    'required' => 1,
                    'placeholder' => 'Juan',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_owner_apellidos',
                    'label' => 'Apellidos',
                    'name' => 'apellidos',
                    'type' => 'text',
                    'placeholder' => 'García López',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_owner_telefono',
                    'label' => 'Teléfono',
                    'name' => 'telefono',
                    'type' => 'text',
                    'required' => 1,
                    'placeholder' => '655123456',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_owner_email',
                    'label' => 'Email',
                    'name' => 'email',
                    'type' => 'email',
                    'placeholder' => 'propietario@ejemplo.com',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_owner_inmuebles',
                    'label' => 'Inmuebles',
                    'name' => 'inmuebles',
                    'type' => 'post_object',
                    'instructions' => 'Selecciona los inmuebles vinculados a este propietario.',
                    'post_type' => array('impress_property'),
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'multiple' => 1,
                    'ui' => 1,
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_owner_dni_cif',
                    'label' => 'DNI/CIF',
                    'name' => 'dni_cif',
                    'type' => 'text',
                    'placeholder' => '12345678A',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_owner_direccion',
                    'label' => 'Dirección',
                    'name' => 'direccion',
                    'type' => 'textarea',
                    'rows' => 3,
                    'placeholder' => 'Calle Principal 123, Valencia',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_owner_notas',
                    'label' => 'Notas internas',
                    'name' => 'notas',
                    'type' => 'textarea',
                    'rows' => 4,
                    'placeholder' => 'Observaciones sobre el propietario...',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_owner_puede_publicar_directo',
                    'label' => 'Puede publicar dirección',
                    'name' => 'puede_publicar_directo',
                    'type' => 'true_false',
                    'message' => '¿El propietario autoriza publicar la dirección exacta del inmueble?',
                    'default_value' => 0,
                    'ui' => 1,
                    'wrapper' => array('width' => '100'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_owner',
                    ),
                ),
            ),
        ));
    }

    /**
     * Field Groups para PROMOCIONES
     */
    private static function create_promotion_field_groups()
    {
        // Grupo 1: Datos
        self::save_field_group(array(
            'key' => 'group_promotion_info',
            'title' => 'Promociones - Datos',
            'fields' => array(
                array(
                    'key' => 'field_promotion_descripcion',
                    'label' => 'Descripción',
                    'name' => 'descripcion',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_promotion_inmuebles_relacionados',
                    'label' => 'Inmuebles relacionados',
                    'name' => 'inmuebles_relacionados',
                    'type' => 'relationship',
                    'post_type' => array('impress_property'),
                    'return_format' => 'id',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_promotion_fecha_inicio',
                    'label' => 'Fecha inicio',
                    'name' => 'fecha_inicio',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'd/m/Y',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_promotion_fecha_fin',
                    'label' => 'Fecha fin',
                    'name' => 'fecha_fin',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'd/m/Y',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_promotion_fecha_entrega_estimada',
                    'label' => 'Fecha entrega estimada',
                    'name' => 'fecha_entrega_estimada',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'd/m/Y',
                    'wrapper' => array('width' => '34'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_promotion',
                    ),
                ),
            ),
        ));

        // Grupo 2: Media
        self::save_field_group(array(
            'key' => 'group_promotion_media',
            'title' => 'Promociones - Media',
            'fields' => array(
                array(
                    'key' => 'field_promotion_promotora_nombre',
                    'label' => 'Promotora',
                    'name' => 'promotora_nombre',
                    'type' => 'text',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_promotion_galeria',
                    'label' => 'Galería',
                    'name' => 'galeria',
                    'type' => 'gallery',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_promotion_dossier_pdf',
                    'label' => 'Dossier PDF',
                    'name' => 'dossier_pdf',
                    'type' => 'file',
                    'return_format' => 'array',
                    'library' => 'all',
                    'wrapper' => array('width' => '100'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_promotion',
                    ),
                ),
            ),
        ));
    }
    /**
     * Field Groups para TRANSACCIONES
     */
    private static function create_transaction_field_groups()
    {
        self::save_field_group(array(
            'key' => 'group_transaction_details',
            'title' => 'Detalles de la Transacción',
            'fields' => array(
                array(
                    'key' => 'field_transaction_type',
                    'label' => 'Tipo de Transacción',
                    'name' => 'transaction_type',
                    'type' => 'select',
                    'choices' => array(
                        'sale' => 'Venta',
                        'rental' => 'Alquiler',
                    ),
                    'required' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_transaction_status',
                    'label' => 'Estado',
                    'name' => 'transaction_status',
                    'type' => 'select',
                    'choices' => array(
                        'pending' => 'Pendiente',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ),
                    'default_value' => 'pending',
                    'required' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_transaction_amount',
                    'label' => 'Importe Total',
                    'name' => 'amount',
                    'type' => 'number',
                    'append' => '€',
                    'required' => 1,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_transaction_profit',
                    'label' => 'Beneficio / Comisión',
                    'name' => 'profit_margin',
                    'type' => 'number',
                    'append' => '€',
                    'instructions' => 'Ganancia neta para la agencia',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_transaction_date',
                    'label' => 'Fecha de Cierre',
                    'name' => 'closing_date',
                    'type' => 'date_picker',
                    'required' => 1,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_related_property',
                    'label' => 'Propiedad',
                    'name' => 'related_property',
                    'type' => 'post_object',
                    'post_type' => array('impress_property'),
                    'return_format' => 'object',
                    'required' => 1,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_related_client',
                    'label' => 'Cliente (Comprador/Inquilino)',
                    'name' => 'related_client',
                    'type' => 'post_object',
                    'post_type' => array('impress_client'),
                    'return_format' => 'object',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_assigned_agent',
                    'label' => 'Agente Asignado',
                    'name' => 'assigned_agent',
                    'type' => 'post_object',
                    'post_type' => array('impress_agent'),
                    'return_format' => 'object',
                    'wrapper' => array('width' => '100'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_transaction',
                    ),
                ),
            ),
        ));
    }

    /**
     * Field Groups para EMAIL TEMPLATES
     */
    private static function create_email_template_field_groups()
    {
        self::save_field_group(array(
            'key' => 'group_email_template_info',
            'title' => 'Email Templates - Información',
            'fields' => array(
                array(
                    'key' => 'field_email_subject',
                    'label' => 'Asunto',
                    'name' => 'email_subject',
                    'type' => 'text',
                    'required' => 1,
                    'wrapper' => array('width' => '70'),
                ),
                array(
                    'key' => 'field_email_status',
                    'label' => 'Estado',
                    'name' => 'email_status',
                    'type' => 'select',
                    'choices' => array(
                        'active' => 'Activa',
                        'inactive' => 'Inactiva',
                    ),
                    'default_value' => 'active',
                    'required' => 1,
                    'wrapper' => array('width' => '30'),
                ),
                array(
                    'key' => 'field_email_trigger',
                    'label' => 'Disparador',
                    'name' => 'email_trigger',
                    'type' => 'select',
                    'choices' => array(
                        'lead_created' => 'Lead creado',
                        'visit_confirmed' => 'Visita confirmada',
                        'visit_reminder' => 'Recordatorio visita (24h)',
                        'visit_followup' => 'Seguimiento post-visita',
                        'property_matching' => 'Inmueble matching',
                        'owner_docs_request' => 'Solicitud documentación propietario',
                        'offer_received' => 'Notificación oferta recibida',
                    ),
                    'required' => 1,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_email_from_name',
                    'label' => 'Nombre Remitente',
                    'name' => 'email_from_name',
                    'type' => 'text',
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_email_from_address',
                    'label' => 'Email Remitente',
                    'name' => 'email_from_address',
                    'type' => 'email',
                    'wrapper' => array('width' => '25'),
                ),
                array(
                    'key' => 'field_email_body',
                    'label' => 'Cuerpo del Email',
                    'name' => 'email_body',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 0,
                    'required' => 1,
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_email_variables',
                    'label' => 'Variables Disponibles',
                    'name' => 'email_variables',
                    'type' => 'textarea',
                    'instructions' => 'Ejemplos: {{cliente_nombre}}, {{inmueble_titulo}}, {{inmueble_precio}}, {{inmueble_url}}, {{agente_nombre}}, {{agente_telefono}}, {{fecha_visita}}',
                    'rows' => 3,
                    'wrapper' => array('width' => '100'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_email_tpl',
                    ),
                ),
            ),
        ));
    }

    /**
     * Field Groups para EVENTOS
     */
    private static function create_event_field_groups()
    {
        self::save_field_group(array(
            'key' => 'group_event_info',
            'title' => 'Eventos - Información',
            'fields' => array(
                array(
                    'key' => 'field_event_type',
                    'label' => 'Tipo',
                    'name' => 'impress_event_type',
                    'type' => 'select',
                    'choices' => array(
                        'tarea' => 'Tarea',
                        'visita' => 'Visita',
                        'llamada' => 'Llamada',
                        'email' => 'Email',
                        'reunion' => 'Reunión',
                        'seguimiento' => 'Seguimiento',
                        'valoracion' => 'Valoración',
                        'firma' => 'Firma',
                        'tarea_general' => 'Tarea general',
                        'revisar_cliente' => 'Revisar ficha cliente',
                        'contactar_cliente' => 'Contactar cliente',
                        'actualizar_propiedad' => 'Actualizar datos propiedad',
                        'captacion' => 'Captación propiedad',
                    ),
                    'required' => 1,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_event_status',
                    'label' => 'Estado',
                    'name' => 'impress_event_status',
                    'type' => 'select',
                    'choices' => array(
                        'pendiente' => 'Pendiente',
                        'en_curso' => 'En curso',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                        'no_presentado' => 'No presentado',
                        'vencida' => 'Vencida',
                    ),
                    'default_value' => 'pendiente',
                    'required' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_priority',
                    'label' => 'Prioridad',
                    'name' => 'impress_event_priority',
                    'type' => 'select',
                    'choices' => array(
                        'baja' => 'Baja',
                        'media' => 'Media',
                        'alta' => 'Alta',
                        'urgente' => 'Urgente',
                    ),
                    'default_value' => 'media',
                    'required' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_title',
                    'label' => 'Título',
                    'name' => 'impress_event_title',
                    'type' => 'text',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_event_start',
                    'label' => 'Inicio',
                    'name' => 'impress_event_start',
                    'type' => 'date_time_picker',
                    'required' => 1,
                    'display_format' => 'd/m/Y H:i',
                    'return_format' => 'Y-m-d H:i:s',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_event_end',
                    'label' => 'Fin',
                    'name' => 'impress_event_end',
                    'type' => 'date_time_picker',
                    'display_format' => 'd/m/Y H:i',
                    'return_format' => 'Y-m-d H:i:s',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_event_duration',
                    'label' => 'Duración (min)',
                    'name' => 'impress_event_duration_minutes',
                    'type' => 'number',
                    'min' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_all_day',
                    'label' => 'Todo el día',
                    'name' => 'impress_event_all_day',
                    'type' => 'true_false',
                    'ui' => 1,
                    'default_value' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_reminder',
                    'label' => 'Recordatorio',
                    'name' => 'impress_event_reminder',
                    'type' => 'select',
                    'choices' => array(
                        'sin_recordatorio' => 'Sin recordatorio',
                        '15_min_antes' => '15 min antes',
                        '30_min_antes' => '30 min antes',
                        '1_hora_antes' => '1 hora antes',
                        '1_dia_antes' => '1 día antes',
                    ),
                    'default_value' => 'sin_recordatorio',
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_event_reminder_min',
                    'label' => 'Recordatorio (min)',
                    'name' => 'impress_event_reminder_min',
                    'type' => 'number',
                    'min' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_reminder_sent',
                    'label' => 'Recordatorio enviado',
                    'name' => 'impress_event_reminder_sent',
                    'type' => 'true_false',
                    'ui' => 0,
                    'default_value' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_agent_rel',
                    'label' => 'Agente asignado',
                    'name' => 'impress_event_agent_rel',
                    'type' => 'post_object',
                    'post_type' => array('impress_agent'),
                    'return_format' => 'id',
                    'required' => 1,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_event_created_by',
                    'label' => 'Creado por',
                    'name' => 'impress_event_created_by',
                    'type' => 'user',
                    'allow_null' => 1,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_event_client_rel',
                    'label' => 'Cliente relacionado',
                    'name' => 'impress_event_client_rel',
                    'type' => 'post_object',
                    'post_type' => array('impress_client'),
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_lead_rel',
                    'label' => 'Lead relacionado',
                    'name' => 'impress_event_lead_rel',
                    'type' => 'post_object',
                    'post_type' => array('impress_lead'),
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_agency_rel',
                    'label' => 'Agencia relacionada',
                    'name' => 'impress_event_agency_rel',
                    'type' => 'post_object',
                    'post_type' => array('impress_agency'),
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_property_rel',
                    'label' => 'Inmueble relacionado',
                    'name' => 'impress_event_property_rel',
                    'type' => 'post_object',
                    'post_type' => array('impress_property'),
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_event_owner_rel',
                    'label' => 'Propietario relacionado',
                    'name' => 'impress_event_owner_rel',
                    'type' => 'post_object',
                    'post_type' => array('impress_owner'),
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_notes',
                    'label' => 'Notas',
                    'name' => 'impress_event_notes',
                    'type' => 'textarea',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_event_private_notes',
                    'label' => 'Notas privadas',
                    'name' => 'impress_event_private_notes',
                    'type' => 'textarea',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_event_result',
                    'label' => 'Resultado',
                    'name' => 'impress_event_result',
                    'type' => 'select',
                    'choices' => array(
                        'exitosa' => 'Exitosa',
                        'pendiente_seguimiento' => 'Pendiente seguimiento',
                        'sin_respuesta' => 'Sin respuesta',
                        'cancelada_cliente' => 'Cancelada por cliente',
                        'reagendar' => 'Reagendar',
                    ),
                    'allow_null' => 1,
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_event_completion_time',
                    'label' => 'Fecha completada',
                    'name' => 'impress_event_completion_time',
                    'type' => 'date_time_picker',
                    'display_format' => 'd/m/Y H:i',
                    'return_format' => 'Y-m-d H:i:s',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_event_attachments',
                    'label' => 'Adjuntos',
                    'name' => 'impress_event_attachments',
                    'type' => 'gallery',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_event_signature',
                    'label' => 'Firma',
                    'name' => 'impress_event_signature',
                    'type' => 'image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_event_photos',
                    'label' => 'Fotos de visita',
                    'name' => 'impress_event_photos',
                    'type' => 'gallery',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_event_location_lat',
                    'label' => 'Latitud',
                    'name' => 'impress_event_location_lat',
                    'type' => 'number',
                    'step' => '0.000001',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_event_location_lng',
                    'label' => 'Longitud',
                    'name' => 'impress_event_location_lng',
                    'type' => 'number',
                    'step' => '0.000001',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_event_is_recurring',
                    'label' => 'Evento recurrente',
                    'name' => 'impress_event_is_recurring',
                    'type' => 'true_false',
                    'ui' => 1,
                    'default_value' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_recurrence_pattern',
                    'label' => 'Patrón de recurrencia',
                    'name' => 'impress_event_recurrence_pattern',
                    'type' => 'select',
                    'choices' => array(
                        'diaria' => 'Diaria',
                        'semanal' => 'Semanal',
                        'quincenal' => 'Quincenal',
                        'mensual' => 'Mensual',
                    ),
                    'allow_null' => 1,
                    'wrapper' => array('width' => '33'),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_event_is_recurring',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_event_recurrence_end_date',
                    'label' => 'Fin recurrencia',
                    'name' => 'impress_event_recurrence_end_date',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                    'wrapper' => array('width' => '34'),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_event_is_recurring',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_event_parent_recurring_id',
                    'label' => 'ID recurrencia',
                    'name' => 'impress_event_parent_recurring_id',
                    'type' => 'number',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_auto_created',
                    'label' => 'Creado automáticamente',
                    'name' => 'impress_event_auto_created',
                    'type' => 'true_false',
                    'ui' => 0,
                    'default_value' => 0,
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_event_automation_rule_id',
                    'label' => 'Regla automática',
                    'name' => 'impress_event_automation_rule_id',
                    'type' => 'number',
                    'wrapper' => array('width' => '34'),
                ),
                array(
                    'key' => 'field_event_follow_up_scheduled',
                    'label' => 'Seguimiento generado',
                    'name' => 'impress_event_follow_up_scheduled',
                    'type' => 'true_false',
                    'ui' => 0,
                    'default_value' => 0,
                    'wrapper' => array('width' => '33'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'impress_event',
                    ),
                ),
            ),
        ));
    }
}
