<?php
if (!defined('ABSPATH')) exit;

/**
 * Clase para configurar Rank Math automáticamente
 */
class Inmopress_Rank_Math_Config {
    
    /**
     * Configurar Rank Math para Inmopress
     */
    public static function configure() {
        if (!function_exists('rank_math')) {
            return false;
        }
        
        $configured = false;
        
        // Configurar títulos y meta descriptions para Inmuebles
        if (self::configure_property_titles()) {
            $configured = true;
        }
        
        // Configurar sitemap
        if (self::configure_sitemap()) {
            $configured = true;
        }
        
        // Limpiar caché de Rank Math
        if (function_exists('rank_math')) {
            delete_transient('rank_math_sitemap');
            rank_math()->sitemap->ping_search_engines();
        }
        
        return $configured;
    }
    
    /**
     * Configurar títulos y meta descriptions para Inmuebles
     */
    private static function configure_property_titles() {
        // Rank Math guarda las opciones en un formato específico
        $all_options = get_option('rank-math-options', array());
        
        // Title Template (optimizado para SEO)
        $title_template = '%impress_property_type% en %impress_city% %sep% %sitename%';
        
        // Description Template (optimizado con variables personalizadas)
        $description_template = '%excerpt% 📍 %impress_city%, %impress_province% - Ref: %impress_ref% - %impress_rooms% hab. - %impress_surface% m² - Precio: %impress_price%';
        
        // Configurar para impress_property
        if (!isset($all_options['titles'])) {
            $all_options['titles'] = array();
        }
        
        $all_options['titles']['pt_impress_property_title'] = $title_template;
        $all_options['titles']['pt_impress_property_description'] = $description_template;
        $all_options['titles']['pt_impress_property_add_meta_box'] = 'on';
        $all_options['titles']['pt_impress_property_bulk_editing'] = 'on';
        
        // Habilitar Schema.org para propiedades
        if (!isset($all_options['titles']['pt_impress_property_schema_type'])) {
            $all_options['titles']['pt_impress_property_schema_type'] = 'Product';
        }
        
        // Guardar opciones
        update_option('rank-math-options', $all_options);
        
        return true;
    }
    
    /**
     * Configurar sitemap
     */
    private static function configure_sitemap() {
        $all_options = get_option('rank-math-options', array());
        
        if (!isset($all_options['sitemap'])) {
            $all_options['sitemap'] = array();
        }
        
        // Incluir post types públicos
        $all_options['sitemap']['pt_impress_property_sitemap'] = 'on';
        $all_options['sitemap']['pt_impress_promotion_sitemap'] = 'on';
        
        // Excluir post types privados
        $all_options['sitemap']['pt_impress_client_sitemap'] = 'off';
        $all_options['sitemap']['pt_impress_lead_sitemap'] = 'off';
        $all_options['sitemap']['pt_impress_visit_sitemap'] = 'off';
        $all_options['sitemap']['pt_impress_agency_sitemap'] = 'off';
        $all_options['sitemap']['pt_impress_agent_sitemap'] = 'off';
        $all_options['sitemap']['pt_impress_owner_sitemap'] = 'off';
        
        // Incluir taxonomías públicas
        $all_options['sitemap']['taxonomy_impress_operation_sitemap'] = 'on';
        $all_options['sitemap']['taxonomy_impress_property_type_sitemap'] = 'on';
        $all_options['sitemap']['taxonomy_impress_province_sitemap'] = 'on';
        $all_options['sitemap']['taxonomy_impress_city_sitemap'] = 'on';
        $all_options['sitemap']['taxonomy_impress_category_sitemap'] = 'on';
        
        // Excluir taxonomías privadas
        $all_options['sitemap']['taxonomy_impress_status_sitemap'] = 'off';
        $all_options['sitemap']['taxonomy_impress_lead_status_sitemap'] = 'off';
        $all_options['sitemap']['taxonomy_impress_lead_source_sitemap'] = 'off';
        $all_options['sitemap']['taxonomy_impress_visit_status_sitemap'] = 'off';
        $all_options['sitemap']['taxonomy_impress_agent_specialty_sitemap'] = 'off';
        $all_options['sitemap']['taxonomy_impress_promotion_status_sitemap'] = 'off';
        
        // Configuración general del sitemap
        $all_options['sitemap']['items_per_page'] = 200;
        $all_options['sitemap']['include_images'] = 'on';
        $all_options['sitemap']['exclude_empty_terms'] = 'on';
        
        // Guardar opciones
        update_option('rank-math-options', $all_options);
        
        return true;
    }
    
    /**
     * Obtener configuración recomendada para mostrar en admin
     */
    public static function get_recommended_config() {
        return array(
            'setup_wizard' => array(
                'site_type' => 'Local Business → Real Estate',
                'logo' => 'Subir logo de la inmobiliaria',
                'social_profiles' => 'Facebook, Instagram, LinkedIn, etc.',
                'sitemap' => 'Enable',
                '404_monitor' => 'Enable',
                'redirections' => 'Enable',
            ),
            'titles_meta' => array(
                'post_type' => 'impress_property',
                'title_template' => '%impress_property_type% en %impress_city% %sep% %sitename%',
                'description_template' => '%excerpt% 📍 %impress_city%, %impress_province% - Ref: %impress_ref% - %impress_rooms% hab. - %impress_surface% - Precio: %impress_price%',
            ),
            'sitemap' => array(
                'include_post_types' => array(
                    'impress_property' => true,
                    'impress_promotion' => true,
                ),
                'exclude_post_types' => array(
                    'impress_client' => true,
                    'impress_lead' => true,
                    'impress_visit' => true,
                    'impress_agency' => true,
                    'impress_agent' => true,
                    'impress_owner' => true,
                ),
                'include_taxonomies' => array(
                    'impress_operation' => true,
                    'impress_property_type' => true,
                    'impress_province' => true,
                    'impress_city' => true,
                    'impress_category' => true,
                ),
            ),
        );
    }
}

