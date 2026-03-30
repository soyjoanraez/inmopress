<?php
if (!defined('ABSPATH')) exit;

/**
 * Clase para poblar taxonomías con términos iniciales
 */
class Inmopress_Taxonomy_Seeder {
    
    /**
     * Poblar todas las taxonomías con términos iniciales
     * @return array Estadísticas de términos creados
     */
    public static function seed_all() {
        $stats = array(
            'total_created' => 0,
            'total_existing' => 0,
            'by_taxonomy' => array()
        );
        
        $results = array();
        $results[] = self::seed_operation();
        $results[] = self::seed_property_type();
        $results[] = self::seed_condition();
        $results[] = self::seed_energy_rating();
        $results[] = self::seed_heating();
        $results[] = self::seed_orientation();
        $results[] = self::seed_amenities();
        $results[] = self::seed_features();
        $results[] = self::seed_category();
        $results[] = self::seed_status();
        $results[] = self::seed_property_group();
        $results[] = self::seed_lead_status();
        $results[] = self::seed_lead_source();
        $results[] = self::seed_language();
        $results[] = self::seed_visit_status();
        $results[] = self::seed_agent_specialty();
        $results[] = self::seed_promotion_status();
        $results[] = self::seed_province_city();
        
        // Agregar estadísticas de cada resultado
        foreach ($results as $result) {
            if (is_array($result)) {
                $stats['total_created'] += $result['created'];
                $stats['total_existing'] += $result['existing'];
                if (isset($result['taxonomy'])) {
                    $stats['by_taxonomy'][$result['taxonomy']] = $result;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Operación (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_operation() {
        $terms = array(
            'Venta',
            'Alquiler',
            'Alquiler vacacional',
            'Traspaso'
        );
        return self::insert_terms('impress_operation', $terms);
    }
    
    /**
     * Tipo de Vivienda (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_property_type() {
        $terms = array(
            'Apartamento',
            'Casa',
            'Piso',
            'Ático',
            'Dúplex',
            'Chalet',
            'Adosado',
            'Estudio',
            'Planta Baja',
            'Bungalow',
            'Finca',
            'Casa de Campo',
            'Mansión',
            'Oficina',
            'Local',
            'Terreno',
            'Garaje',
            'Trastero',
            'Nave industrial',
            'Edificio',
            'Suelo',
            'Finca rústica'
        );
        return self::insert_terms('impress_property_type', $terms);
    }
    
    /**
     * Estado Conservación (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_condition() {
        $terms = array(
            'Obra Nueva',
            'A estrenar',
            'Reformado',
            'Muy bueno',
            'Buen estado',
            'Bueno',
            'Para reformar',
            'A reformar',
            'En construcción',
            'En proyecto',
            'A rehabilitar',
            'En ruina'
        );
        return self::insert_terms('impress_condition', $terms);
    }
    
    /**
     * Certificación Energética (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_energy_rating() {
        $terms = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'En trámite',
            'Exento',
            'Pendiente renovación',
            'Caducada'
        );
        return self::insert_terms('impress_energy_rating', $terms);
    }
    
    /**
     * Tipo de Calefacción (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_heating() {
        $terms = array(
            'Gas natural',
            'Gasoil',
            'Eléctrica',
            'Aerotermia',
            'Geotermia',
            'Bomba de calor',
            'Radiadores',
            'Suelo radiante',
            'Sin calefacción',
            'Otros'
        );
        return self::insert_terms('impress_heating', $terms);
    }
    
    /**
     * Orientación (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_orientation() {
        $terms = array(
            'Norte',
            'Sur',
            'Este',
            'Oeste',
            'Noreste',
            'Noroeste',
            'Sureste',
            'Suroeste',
            'Múltiple'
        );
        return self::insert_terms('impress_orientation', $terms);
    }
    
    /**
     * Estado Lead (Clientes/Leads)
     * @return array Estadísticas
     */
    private static function seed_lead_status() {
        $terms = array(
            'Nuevo',
            'Contactado',
            'Calificado',
            'Interesado',
            'Oferta enviada',
            'Negociación',
            'Cerrado',
            'Perdido',
            'No interesado'
        );
        return self::insert_terms('impress_lead_status', $terms);
    }
    
    /**
     * Canal de Entrada (Clientes/Leads)
     * @return array Estadísticas
     */
    private static function seed_lead_source() {
        $terms = array(
            'Web',
            'Teléfono',
            'Email',
            'Redes sociales',
            'Referido',
            'Visita presencial',
            'Anuncio',
            'Otros portales',
            'Evento',
            'Otros'
        );
        return self::insert_terms('impress_lead_source', $terms);
    }
    
    /**
     * Idioma (Clientes/Leads)
     * @return array Estadísticas
     */
    private static function seed_language() {
        $terms = array(
            'Español',
            'Inglés',
            'Francés',
            'Alemán',
            'Italiano',
            'Portugués',
            'Ruso',
            'Chino',
            'Árabe',
            'Otros'
        );
        return self::insert_terms('impress_language', $terms);
    }
    
    /**
     * Estado Visita (Visitas)
     * @return array Estadísticas
     */
    private static function seed_visit_status() {
        $terms = array(
            'Programada',
            'Confirmada',
            'Realizada',
            'Cancelada',
            'Reagendada',
            'No asistió'
        );
        return self::insert_terms('impress_visit_status', $terms);
    }
    
    /**
     * Especialización Agente (Agentes)
     * @return array Estadísticas
     */
    private static function seed_agent_specialty() {
        $terms = array(
            'Venta',
            'Alquiler',
            'Comercial',
            'Residencial',
            'Lujo',
            'Nuevo desarrollo',
            'Gestión',
            'Valoraciones',
            'General'
        );
        return self::insert_terms('impress_agent_specialty', $terms);
    }
    
    /**
     * Estado Promoción (Promociones)
     * @return array Estadísticas
     */
    private static function seed_promotion_status() {
        $terms = array(
            'Planificación',
            'En construcción',
            'En venta',
            'Agotada',
            'Finalizada',
            'Cancelada'
        );
        return self::insert_terms('impress_promotion_status', $terms);
    }
    
    /**
     * Equipamiento / Comodidades (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_amenities() {
        $terms = array(
            'Ascensor',
            'Piscina',
            'Garaje',
            'Terraza',
            'Aire acondicionado',
            'Calefacción',
            'Trastero',
            'Jardín',
            'Balcón',
            'Armarios empotrados',
            'Parking',
            'Portero',
            'Alarma',
            'Acceso minusválidos'
        );
        return self::insert_terms('impress_amenities', $terms);
    }
    
    /**
     * Etiquetas Especiales / Features (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_features() {
        $terms = array(
            'Lujo',
            'Exclusiva',
            'Oportunidad',
            'Bajada de precio',
            'Frente al mar',
            'Vistas al mar',
            'Vistas a la montaña',
            'Primera línea',
            'Centro ciudad',
            'Golf',
            'Montaña',
            'Pueblo',
            'Urbanización cerrada',
            'Solo VIP'
        );
        return self::insert_terms('impress_features', $terms);
    }
    
    /**
     * Categoría / Agrupación (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_category() {
        $terms = array(
            'Villas',
            'Apartamentos',
            'Casas',
            'Chalets',
            'Terrenos',
            'Solares',
            'Residencial',
            'Comercial',
            'Industrial'
        );
        return self::insert_terms('impress_category', $terms);
    }
    
    /**
     * Estado Comercial (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_status() {
        $terms = array(
            'Disponible',
            'Reservado',
            'Vendido',
            'En captación',
            'Baja',
            'Pendiente aprobación'
        );
        return self::insert_terms('impress_status', $terms);
    }
    
    /**
     * Agrupación de Propiedades (Inmuebles)
     * @return array Estadísticas
     */
    private static function seed_property_group() {
        $terms = array(
            'Villas',
            'Apartamentos',
            'Casas',
            'Chalets',
            'Terrenos',
            'Solares'
        );
        return self::insert_terms('impress_property_group', $terms);
    }
    
    /**
     * Provincias y Ciudades principales de España
     * @return array Estadísticas
     */
    private static function seed_province_city() {
        $provinces_cities = array(
            'Alicante' => array('Alicante', 'Elche', 'Torrevieja', 'Orihuela', 'Benidorm', 'Alcoy', 'Denia', 'Villena'),
            'Valencia' => array('Valencia', 'Torrent', 'Gandia', 'Paterna', 'Sagunto', 'Alzira', 'Mislata', 'Burjassot'),
            'Madrid' => array('Madrid', 'Móstoles', 'Alcalá de Henares', 'Getafe', 'Leganés', 'Fuenlabrada', 'Alcorcón', 'Torrejón de Ardoz'),
            'Barcelona' => array('Barcelona', 'Badalona', 'Sabadell', 'Terrassa', 'Santa Coloma de Gramenet', 'L\'Hospitalet de Llobregat', 'Mataró', 'Sant Cugat del Vallès'),
            'Sevilla' => array('Sevilla', 'Dos Hermanas', 'Alcalá de Guadaíra', 'Utrera', 'Écija', 'Mairena del Aljarafe', 'Coria del Río', 'Carmona'),
            'Málaga' => array('Málaga', 'Marbella', 'Mijas', 'Vélez-Málaga', 'Fuengirola', 'Torremolinos', 'Estepona', 'Ronda'),
            'Murcia' => array('Murcia', 'Cartagena', 'Lorca', 'Molina de Segura', 'Alcantarilla', 'Cieza', 'Yecla', 'Caravaca de la Cruz'),
            'Cádiz' => array('Cádiz', 'Jerez de la Frontera', 'Algeciras', 'San Fernando', 'El Puerto de Santa María', 'Chiclana de la Frontera', 'Rota', 'Puerto Real'),
            'Baleares' => array('Palma', 'Calvià', 'Ibiza', 'Manacor', 'Llucmajor', 'Marratxí', 'Inca', 'Ciutadella'),
            'Las Palmas' => array('Las Palmas de Gran Canaria', 'Telde', 'Santa Lucía de Tirajana', 'Arucas', 'Gáldar', 'Ingenio', 'Agüimes', 'Moya'),
            'Santa Cruz de Tenerife' => array('Santa Cruz de Tenerife', 'San Cristóbal de La Laguna', 'Arona', 'Adeje', 'La Orotava', 'Puerto de la Cruz', 'Los Realejos', 'Candelaria'),
            'Zaragoza' => array('Zaragoza', 'Calatayud', 'Utebo', 'Ejea de los Caballeros', 'Tarazona', 'Alagón', 'Caspe', 'Barbastro'),
            'Bilbao' => array('Bilbao', 'Getxo', 'Barakaldo', 'Portugalete', 'Santurtzi', 'Leioa', 'Basauri', 'Galdakao'),
            'Granada' => array('Granada', 'Motril', 'Almuñécar', 'Baza', 'Loja', 'Guadix', 'Armilla', 'Maracena'),
            'Córdoba' => array('Córdoba', 'Lucena', 'Puente Genil', 'Montilla', 'Priego de Córdoba', 'Cabra', 'Baena', 'Rute')
        );
        
        $stats = array('created' => 0, 'existing' => 0, 'taxonomy' => 'impress_province_city');
        
        foreach ($provinces_cities as $province => $cities) {
            // Verificar si la provincia ya existe
            $province_exists = term_exists($province, 'impress_province');
            
            if (!$province_exists) {
                $province_term = wp_insert_term($province, 'impress_province');
                if (!is_wp_error($province_term)) {
                    $stats['created']++;
                    $province_id = $province_term['term_id'];
                } else {
                    // Si falla, intentar obtener el término existente
                    $province_term = get_term_by('name', $province, 'impress_province');
                    if ($province_term) {
                        $province_id = $province_term->term_id;
                        $stats['existing']++;
                    } else {
                        continue;
                    }
                }
            } else {
                $stats['existing']++;
                $province_id = $province_exists['term_id'];
            }
            
            // Insertar ciudades como hijos de la provincia
            foreach ($cities as $city) {
                $city_exists = term_exists($city, 'impress_city');
                if (!$city_exists) {
                    $result = wp_insert_term($city, 'impress_city', array(
                        'parent' => $province_id
                    ));
                    if (!is_wp_error($result)) {
                        $stats['created']++;
                    }
                } else {
                    $stats['existing']++;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Insertar términos en una taxonomía
     * @param string $taxonomy Slug de la taxonomía
     * @param array $terms Array de nombres de términos
     * @return array Estadísticas de inserción
     */
    private static function insert_terms($taxonomy, $terms) {
        if (!taxonomy_exists($taxonomy)) {
            return array('created' => 0, 'existing' => 0, 'taxonomy' => $taxonomy);
        }
        
        $created = 0;
        $existing = 0;
        
        foreach ($terms as $term_name) {
            // Verificar si el término ya existe
            $term_exists = term_exists($term_name, $taxonomy);
            
            if (!$term_exists) {
                $result = wp_insert_term($term_name, $taxonomy);
                if (!is_wp_error($result)) {
                    $created++;
                }
            } else {
                $existing++;
            }
        }
        
        return array(
            'created' => $created,
            'existing' => $existing,
            'total' => count($terms),
            'taxonomy' => $taxonomy
        );
    }
    
    /**
     * Obtener datos estructurados de todas las taxonomías y sus términos
     * @return array Array con todas las taxonomías, términos y estadísticas
     */
    public static function get_all_taxonomies_data() {
        $data = array(
            'total_taxonomies' => 0,
            'total_terms' => 0,
            'taxonomies' => array()
        );
        
        // Obtener todos los términos definidos en el seeder
        $taxonomies_data = array(
            'impress_operation' => array(
                'name' => 'Operaciones',
                'singular' => 'Operación',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('Venta', 'Alquiler', 'Alquiler vacacional', 'Traspaso')
            ),
            'impress_property_type' => array(
                'name' => 'Tipos de Vivienda',
                'singular' => 'Tipo',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('Apartamento', 'Casa', 'Piso', 'Ático', 'Dúplex', 'Chalet', 'Adosado', 'Estudio', 'Planta Baja', 'Bungalow', 'Finca', 'Casa de Campo', 'Mansión', 'Oficina', 'Local', 'Terreno', 'Garaje', 'Trastero', 'Nave industrial', 'Edificio', 'Suelo', 'Finca rústica')
            ),
            'impress_condition' => array(
                'name' => 'Estados',
                'singular' => 'Estado',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('Obra Nueva', 'A estrenar', 'Reformado', 'Muy bueno', 'Buen estado', 'Bueno', 'Para reformar', 'A reformar', 'En construcción', 'En proyecto', 'A rehabilitar', 'En ruina')
            ),
            'impress_energy_rating' => array(
                'name' => 'Certificaciones Energéticas',
                'singular' => 'Certificación',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'En trámite', 'Exento', 'Pendiente renovación', 'Caducada')
            ),
            'impress_heating' => array(
                'name' => 'Tipos de Calefacción',
                'singular' => 'Calefacción',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('Gas natural', 'Gasoil', 'Eléctrica', 'Aerotermia', 'Geotermia', 'Bomba de calor', 'Radiadores', 'Suelo radiante', 'Sin calefacción', 'Otros')
            ),
            'impress_orientation' => array(
                'name' => 'Orientaciones',
                'singular' => 'Orientación',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('Norte', 'Sur', 'Este', 'Oeste', 'Noreste', 'Noroeste', 'Sureste', 'Suroeste', 'Múltiple')
            ),
            'impress_amenities' => array(
                'name' => 'Equipamiento',
                'singular' => 'Equipamiento',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('Ascensor', 'Piscina', 'Garaje', 'Terraza', 'Aire acondicionado', 'Calefacción', 'Trastero', 'Jardín', 'Balcón', 'Armarios empotrados', 'Parking', 'Portero', 'Alarma', 'Acceso minusválidos')
            ),
            'impress_features' => array(
                'name' => 'Características Premium',
                'singular' => 'Característica',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('Lujo', 'Exclusiva', 'Oportunidad', 'Bajada de precio', 'Frente al mar', 'Vistas al mar', 'Vistas a la montaña', 'Primera línea', 'Centro ciudad', 'Golf', 'Montaña', 'Pueblo', 'Urbanización cerrada', 'Solo VIP')
            ),
            'impress_category' => array(
                'name' => 'Categorías',
                'singular' => 'Categoría',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('Villas', 'Apartamentos', 'Casas', 'Chalets', 'Terrenos', 'Solares', 'Residencial', 'Comercial', 'Industrial')
            ),
            'impress_status' => array(
                'name' => 'Estados Comerciales',
                'singular' => 'Estado',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('Disponible', 'Reservado', 'Vendido', 'En captación', 'Baja', 'Pendiente aprobación')
            ),
            'impress_property_group' => array(
                'name' => 'Agrupaciones',
                'singular' => 'Agrupación',
                'post_types' => array('impress_property'),
                'hierarchical' => false,
                'terms' => array('Villas', 'Apartamentos', 'Casas', 'Chalets', 'Terrenos', 'Solares')
            ),
            'impress_lead_status' => array(
                'name' => 'Estados Lead',
                'singular' => 'Estado',
                'post_types' => array('impress_client', 'impress_lead'),
                'hierarchical' => false,
                'terms' => array('Nuevo', 'Contactado', 'Calificado', 'Interesado', 'Oferta enviada', 'Negociación', 'Cerrado', 'Perdido', 'No interesado')
            ),
            'impress_lead_source' => array(
                'name' => 'Canales',
                'singular' => 'Canal',
                'post_types' => array('impress_client', 'impress_lead'),
                'hierarchical' => false,
                'terms' => array('Web', 'Teléfono', 'Email', 'Redes sociales', 'Referido', 'Visita presencial', 'Anuncio', 'Otros portales', 'Evento', 'Otros')
            ),
            'impress_language' => array(
                'name' => 'Idiomas',
                'singular' => 'Idioma',
                'post_types' => array('impress_client', 'impress_lead'),
                'hierarchical' => false,
                'terms' => array('Español', 'Inglés', 'Francés', 'Alemán', 'Italiano', 'Portugués', 'Ruso', 'Chino', 'Árabe', 'Otros')
            ),
            'impress_visit_status' => array(
                'name' => 'Estados Visita',
                'singular' => 'Estado',
                'post_types' => array('impress_visit'),
                'hierarchical' => false,
                'terms' => array('Programada', 'Confirmada', 'Realizada', 'Cancelada', 'Reagendada', 'No asistió')
            ),
            'impress_agent_specialty' => array(
                'name' => 'Especializaciones',
                'singular' => 'Especialización',
                'post_types' => array('impress_agent'),
                'hierarchical' => false,
                'terms' => array('Venta', 'Alquiler', 'Comercial', 'Residencial', 'Lujo', 'Nuevo desarrollo', 'Gestión', 'Valoraciones', 'General')
            ),
            'impress_promotion_status' => array(
                'name' => 'Estados Promoción',
                'singular' => 'Estado',
                'post_types' => array('impress_promotion'),
                'hierarchical' => false,
                'terms' => array('Planificación', 'En construcción', 'En venta', 'Agotada', 'Finalizada', 'Cancelada')
            ),
            'impress_province' => array(
                'name' => 'Provincias',
                'singular' => 'Provincia',
                'post_types' => array('impress_property', 'impress_client', 'impress_agency', 'impress_owner'),
                'hierarchical' => true,
                'terms' => array('Alicante', 'Valencia', 'Madrid', 'Barcelona', 'Sevilla', 'Málaga', 'Murcia', 'Cádiz', 'Baleares', 'Las Palmas', 'Santa Cruz de Tenerife', 'Zaragoza', 'Bilbao', 'Granada', 'Córdoba')
            ),
            'impress_city' => array(
                'name' => 'Ciudades',
                'singular' => 'Ciudad',
                'post_types' => array('impress_property', 'impress_client', 'impress_agency', 'impress_owner'),
                'hierarchical' => true,
                'parent_taxonomy' => 'impress_province',
                'terms' => array() // Se poblará dinámicamente desde seed_province_city
            )
        );
        
        // Obtener términos reales de la base de datos si existen
        foreach ($taxonomies_data as $taxonomy_slug => $taxonomy_info) {
            $terms_in_db = array();
            if (taxonomy_exists($taxonomy_slug)) {
                $terms = get_terms(array(
                    'taxonomy' => $taxonomy_slug,
                    'hide_empty' => false
                ));
                
                if (!is_wp_error($terms) && !empty($terms)) {
                    foreach ($terms as $term) {
                        $terms_in_db[] = array(
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug,
                            'count' => $term->count
                        );
                    }
                }
            }
            
            $data['taxonomies'][$taxonomy_slug] = array(
                'slug' => $taxonomy_slug,
                'name' => $taxonomy_info['name'],
                'singular' => $taxonomy_info['singular'],
                'post_types' => $taxonomy_info['post_types'],
                'hierarchical' => $taxonomy_info['hierarchical'],
                'defined_terms' => $taxonomy_info['terms'],
                'total_defined' => count($taxonomy_info['terms']),
                'terms_in_db' => $terms_in_db,
                'total_in_db' => count($terms_in_db)
            );
            
            $data['total_terms'] += count($taxonomy_info['terms']);
        }
        
        $data['total_taxonomies'] = count($taxonomies_data);
        
        return $data;
    }
    
    /**
     * Limpiar todos los términos (útil para resetear)
     */
    public static function clear_all() {
        $taxonomies = array(
            'impress_operation',
            'impress_property_type',
            'impress_condition',
            'impress_energy_rating',
            'impress_heating',
            'impress_orientation',
            'impress_amenities',
            'impress_features',
            'impress_category',
            'impress_status',
            'impress_property_group',
            'impress_lead_status',
            'impress_lead_source',
            'impress_language',
            'impress_visit_status',
            'impress_agent_specialty',
            'impress_promotion_status',
            'impress_province',
            'impress_city'
        );
        
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false
            ));
            
            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    wp_delete_term($term->term_id, $taxonomy);
                }
            }
        }
    }
}

