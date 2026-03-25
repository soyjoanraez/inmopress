<?php
if (!defined('ABSPATH')) exit;

/**
 * Clase para crear automáticamente las páginas del panel frontend
 */
class Inmopress_Frontend_Pages {
    
    /**
     * Crear todas las páginas del panel
     */
    public static function create_all_pages() {
        $pages = array(
            array(
                'title' => 'Mi Panel',
                'slug' => 'mi-panel',
                'content' => '[inmopress_dashboard]',
                'parent' => 0,
            ),
            array(
                'title' => 'Inmuebles',
                'slug' => 'inmuebles',
                'content' => '[inmopress_inmuebles_list]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Nuevo Inmueble',
                'slug' => 'nuevo-inmueble',
                'content' => '[inmopress_inmueble_form]',
                'parent' => 'inmuebles',
            ),
            array(
                'title' => 'Editar Inmueble',
                'slug' => 'editar-inmueble',
                'content' => '[inmopress_inmueble_form]',
                'parent' => 'inmuebles',
            ),
            array(
                'title' => 'Clientes',
                'slug' => 'clientes',
                'content' => '[inmopress_clientes_list]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Nuevo Cliente',
                'slug' => 'nuevo-cliente',
                'content' => '[inmopress_cliente_form]',
                'parent' => 'clientes',
            ),
            array(
                'title' => 'Visitas',
                'slug' => 'visitas',
                'content' => '[inmopress_visitas_list]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Nueva Visita',
                'slug' => 'nueva-visita',
                'content' => '[inmopress_visita_form]',
                'parent' => 'visitas',
            ),
            array(
                'title' => 'Propietarios',
                'slug' => 'propietarios',
                'content' => '[inmopress_propietarios_list]',
                'parent' => 'mi-panel',
            ),
        );
        
        $created_pages = array();
        $parent_ids = array();
        
        foreach ($pages as $page_data) {
            // Verificar si la página ya existe
            $existing_page = get_page_by_path($page_data['slug']);
            
            if ($existing_page) {
                $created_pages[] = array(
                    'id' => $existing_page->ID,
                    'title' => $page_data['title'],
                    'slug' => $page_data['slug'],
                    'status' => 'existed'
                );
                $parent_ids[$page_data['slug']] = $existing_page->ID;
                continue;
            }
            
            // Determinar parent_id
            $parent_id = 0;
            if ($page_data['parent'] && isset($parent_ids[$page_data['parent']])) {
                $parent_id = $parent_ids[$page_data['parent']];
            } elseif ($page_data['parent']) {
                $parent_page = get_page_by_path($page_data['parent']);
                if ($parent_page) {
                    $parent_id = $parent_page->ID;
                    $parent_ids[$page_data['parent']] = $parent_id;
                }
            }
            
            // Crear la página
            $page_id = wp_insert_post(array(
                'post_title' => $page_data['title'],
                'post_name' => $page_data['slug'],
                'post_content' => $page_data['content'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_parent' => $parent_id,
            ));
            
            if ($page_id && !is_wp_error($page_id)) {
                // Configurar Astra settings
                update_post_meta($page_id, '_astra_content_layout_flag', true);
                update_post_meta($page_id, 'site-content-layout', 'page-builder');
                update_post_meta($page_id, 'site-sidebar-layout', 'no-sidebar');
                update_post_meta($page_id, 'site-post-title', 'disabled');
                update_post_meta($page_id, 'ast-title-bar-display', 'disabled');
                update_post_meta($page_id, 'ast-featured-img', 'disabled');
                
                // Configurar template full width
                update_post_meta($page_id, '_wp_page_template', 'page-builder.php');
                
                $created_pages[] = array(
                    'id' => $page_id,
                    'title' => $page_data['title'],
                    'slug' => $page_data['slug'],
                    'url' => get_permalink($page_id),
                    'status' => 'created'
                );
                
                $parent_ids[$page_data['slug']] = $page_id;
            }
        }
        
        // Crear página print-property
        $print_page = get_page_by_path('print-property');
        if (!$print_page) {
            $print_page_id = wp_insert_post(array(
                'post_title' => 'Print Property',
                'post_name' => 'print-property',
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'page',
            ));
            
            if ($print_page_id && !is_wp_error($print_page_id)) {
                // Configurar sin header/footer
                update_post_meta($print_page_id, '_astra_content_layout_flag', true);
                update_post_meta($print_page_id, 'site-content-layout', 'page-builder');
                update_post_meta($print_page_id, 'site-sidebar-layout', 'no-sidebar');
                update_post_meta($print_page_id, 'site-post-title', 'disabled');
                update_post_meta($print_page_id, 'ast-title-bar-display', 'disabled');
                update_post_meta($print_page_id, 'ast-featured-img', 'disabled');
                update_post_meta($print_page_id, 'ast-main-header-display', 'disabled');
                update_post_meta($print_page_id, 'footer-sml-layout', 'disabled');
                
                $created_pages[] = array(
                    'id' => $print_page_id,
                    'title' => 'Print Property',
                    'slug' => 'print-property',
                    'url' => get_permalink($print_page_id),
                    'status' => 'created'
                );
            }
        }
        
        return $created_pages;
    }
    
    /**
     * Eliminar todas las páginas del panel
     */
    public static function delete_all_pages() {
        $slugs = array(
            'mi-panel',
            'inmuebles',
            'nuevo-inmueble',
            'editar-inmueble',
            'clientes',
            'nuevo-cliente',
            'visitas',
            'nueva-visita',
            'propietarios',
            'print-property'
        );
        
        $deleted = array();
        
        foreach ($slugs as $slug) {
            $page = get_page_by_path($slug);
            if ($page) {
                wp_delete_post($page->ID, true);
                $deleted[] = $slug;
            }
        }
        
        return $deleted;
    }
}


