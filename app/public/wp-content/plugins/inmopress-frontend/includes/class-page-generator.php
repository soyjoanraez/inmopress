<?php
if (!defined('ABSPATH'))
    exit;

class Inmopress_Page_Generator
{

    /**
     * Generar todas las páginas del panel frontend
     */
    public static function create_all_pages()
    {
        $pages = array(
            array(
                'title' => 'Mi Panel',
                'slug' => 'mi-panel',
                'shortcode' => '[inmopress_dashboard]',
                'parent' => null,
            ),
            array(
                'title' => 'Inmuebles',
                'slug' => 'inmuebles',
                'shortcode' => '[inmopress_inmuebles_list]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Nuevo Inmueble',
                'slug' => 'nuevo-inmueble',
                'shortcode' => '[inmopress_inmueble_form]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Editar Inmueble',
                'slug' => 'editar-inmueble',
                'shortcode' => '[inmopress_inmueble_form]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Clientes',
                'slug' => 'clientes',
                'shortcode' => '[inmopress_clientes_list]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Nuevo Cliente',
                'slug' => 'nuevo-cliente',
                'shortcode' => '[inmopress_cliente_form]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Editar Cliente',
                'slug' => 'editar-cliente',
                'shortcode' => '[inmopress_cliente_form]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Visitas',
                'slug' => 'visitas',
                'shortcode' => '[inmopress_visitas_list]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Nueva Visita',
                'slug' => 'nueva-visita',
                'shortcode' => '[inmopress_visita_form]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Propietarios',
                'slug' => 'propietarios',
                'shortcode' => '[inmopress_propietarios_list]',
                'parent' => 'mi-panel',
            ),
            array(
                'title' => 'Nuevo Propietario',
                'slug' => 'nuevo-propietario',
                'shortcode' => '[inmopress_propietario_form]',
                'parent' => 'mi-panel',
            ),
        );

        $created = 0;
        $parent_ids = array();

        foreach ($pages as $page_data) {
            // Buscar si ya existe la página
            $existing_page = get_page_by_path($page_data['slug']);

            if ($existing_page) {
                // La página ya existe, guardar su ID por si es padre de otras
                $parent_ids[$page_data['slug']] = $existing_page->ID;
                continue;
            }

            // Obtener parent ID si tiene padre
            $parent_id = 0;
            if (!empty($page_data['parent'])) {
                if (isset($parent_ids[$page_data['parent']])) {
                    $parent_id = $parent_ids[$page_data['parent']];
                } else {
                    $parent_page = get_page_by_path($page_data['parent']);
                    if ($parent_page) {
                        $parent_id = $parent_page->ID;
                        $parent_ids[$page_data['parent']] = $parent_id;
                    }
                }
            }

            // Crear página
            $page_id = wp_insert_post(array(
                'post_title' => $page_data['title'],
                'post_name' => $page_data['slug'],
                'post_content' => $page_data['shortcode'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_parent' => $parent_id,
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ));

            if ($page_id && !is_wp_error($page_id)) {
                $parent_ids[$page_data['slug']] = $page_id;
                $created++;
            }
        }

        return $created;
    }

    /**
     * Eliminar todas las páginas del panel (útil para testing)
     */
    public static function delete_all_pages()
    {
        $slugs = array(
            'mi-panel',
            'inmuebles',
            'nuevo-inmueble',
            'editar-inmueble',
            'clientes',
            'nuevo-cliente',
            'editar-cliente',
            'visitas',
            'nueva-visita',
            'propietarios',
            'nuevo-propietario',
        );

        $deleted = 0;

        foreach ($slugs as $slug) {
            $page = get_page_by_path($slug);
            if ($page) {
                wp_delete_post($page->ID, true); // true = force delete, bypass trash
                $deleted++;
            }
        }

        return $deleted;
    }
}
