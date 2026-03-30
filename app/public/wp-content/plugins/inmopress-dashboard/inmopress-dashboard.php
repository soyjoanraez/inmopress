<?php
/**
 * Plugin Name: InmoPress Dashboard
 * Description: Panel de control personalizado y CRM para InmoPress (Web App / SPA).
 * Version: 1.0.0
 * Author: Jimmy
 * Text Domain: inmopress-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'INMOPRESS_DASHBOARD_VERSION', '1.0.0' );
define( 'INMOPRESS_DASHBOARD_DIR', plugin_dir_path( __FILE__ ) );
define( 'INMOPRESS_DASHBOARD_URL', plugin_dir_url( __FILE__ ) );

final class InmoPress_Dashboard {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->includes();
	}

	private function includes() {
		require_once INMOPRESS_DASHBOARD_DIR . 'includes/class-admin-menu.php';
	}
}

function InmoPress_Dashboard_Init() {
    return InmoPress_Dashboard::instance();
}

add_action( 'plugins_loaded', 'InmoPress_Dashboard_Init' );

// Expose property metadata to REST API
add_action( 'rest_api_init', 'inmopress_dashboard_register_rest_fields' );
function inmopress_dashboard_register_rest_fields() {
    register_rest_field( 'impress_property', 'ip_meta', array(
        'get_callback'    => 'inmopress_dashboard_get_property_meta',
        'update_callback' => null,
        'schema'          => null,
    ) );
}

function inmopress_dashboard_get_property_meta( $object, $field_name, $request ) {
    $meta = get_post_meta( $object['id'] );
    $data = array();
    
    foreach ( $meta as $key => $value ) {
        // Obtenemos solo los metadatos públicos de ACF reales (los que no empiezan por _)
        if ( strpos( $key, '_' ) !== 0 ) {
            $data[$key] = $value[0];
        }
    }
    
    // Si queremos asegurar algún campo específico que se guarde con guion bajo y no sobreescriba
    // lo podemos agregar de manera segura
    $useful_hidden_keys = array('_precio', '_habitaciones', '_banos', '_precio_venta', '_superficie_construida');
    foreach ( $useful_hidden_keys as $hidden_key ) {
        $public_key = ltrim($hidden_key, '_');
        if ( isset($meta[$hidden_key]) && !isset($data[$public_key]) ) {
            $data[$public_key] = $meta[$hidden_key][0];
        }
    }
    
    // Añadir todas las imágenes adjuntas (galería)
    $images = get_attached_media( 'image', $object['id'] );
    $gallery = array();
    if ( $images ) {
        foreach ( $images as $image ) {
            $gallery[] = array(
                'id'  => $image->ID,
                'url' => wp_get_attachment_url( $image->ID )
            );
        }
    }
    $data['gallery'] = $gallery;
    
    return $data;
}

// ------------------------------------------------------------------------
// Registrar endpoint personalizado para añadir Inmuebles
// ------------------------------------------------------------------------
function inmopress_dashboard_register_custom_endpoints() {
    register_rest_route('inmopress/v1', '/property/add', array(
        'methods' => 'POST',
        'callback' => 'inmopress_dashboard_add_property_handler',
        'permission_callback' => function () {
            // El usuario debe tener permisos para editar posts en WordPress
            return current_user_can('edit_posts');
        }
    ));
    register_rest_route('inmopress/v1', '/property/update/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'inmopress_dashboard_update_property_handler',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));

    register_rest_route('inmopress/v1', '/property/delete-attachment', array(
        'methods' => 'POST',
        'callback' => 'inmopress_dashboard_delete_attachment_handler',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));
}
add_action('rest_api_init', 'inmopress_dashboard_register_custom_endpoints');

function inmopress_dashboard_add_property_handler($request) {
    $params = $request->get_params();
    $files = $request->get_file_params();

    // 1. Crear el post (Inmueble)
    $post_data = array(
        'post_title'    => sanitize_text_field($params['title'] ?? 'Sin título'),
        'post_content'  => sanitize_textarea_field($params['description'] ?? ''),
        'post_status'   => 'publish',
        'post_type'     => 'impress_property',
    );
    
    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id)) {
        return new WP_Error('db_error', 'No se pudo crear el inmueble.', array('status' => 500));
    }

    // 2. Añadir Taxonomías (wp_set_object_terms soporta slugs/nombres si pasamos strings)
    if (!empty($params['type'])) {
        wp_set_object_terms($post_id, sanitize_text_field($params['type']), 'impress_property_type');
    }
    if (!empty($params['status'])) {
        wp_set_object_terms($post_id, sanitize_text_field($params['status']), 'impress_status');
    }
    if (!empty($params['city'])) {
        wp_set_object_terms($post_id, sanitize_text_field($params['city']), 'impress_city');
    }

    // 3. Añadir Metadatos (Campos personalizados de Inmopress / ACF)
    if (isset($params['price'])) {
        update_post_meta($post_id, 'precio_venta', sanitize_text_field($params['price']));
        update_post_meta($post_id, 'precio', sanitize_text_field($params['price'])); // Alias común
    }
    if (isset($params['rooms'])) {
        update_post_meta($post_id, 'habitaciones', sanitize_text_field($params['rooms']));
        update_post_meta($post_id, 'dormitorios', sanitize_text_field($params['rooms'])); // Alias
    }
    if (isset($params['baths'])) {
        update_post_meta($post_id, 'banos', sanitize_text_field($params['baths']));
    }
    if (isset($params['size'])) {
        update_post_meta($post_id, 'superficie_construida', sanitize_text_field($params['size']));
    }

    // 4. Procesar Múltiples Imágenes si se han subido
    if (!empty($files['images'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $files_array = $files['images'];
        // WordPress re-arrays multifile uploads into 'name', 'type', etc. arrays
        $count = is_array($files_array['name']) ? count($files_array['name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $file = array(
                'name'     => is_array($files_array['name']) ? $files_array['name'][$i] : $files_array['name'],
                'type'     => is_array($files_array['type']) ? $files_array['type'][$i] : $files_array['type'],
                'tmp_name' => is_array($files_array['tmp_name']) ? $files_array['tmp_name'][$i] : $files_array['tmp_name'],
                'error'    => is_array($files_array['error']) ? $files_array['error'][$i] : $files_array['error'],
                'size'     => is_array($files_array['size']) ? $files_array['size'][$i] : $files_array['size']
            );

            if ($file['error'] === 0) {
                $attachment_id = media_handle_sideload($file, $post_id);

                if (!is_wp_error($attachment_id)) {
                    // La primera imagen se establece como destacada
                    if ($i === 0) {
                        set_post_thumbnail($post_id, $attachment_id);
                    }
                }
            }
        }
    }

    return rest_ensure_response(array(
        'success' => true, 
        'post_id' => $post_id,
        'message' => 'Inmueble añadido correctamente con sus imágenes.'
    ));
}

function inmopress_dashboard_update_property_handler($request) {
    $post_id = $request['id'];
    $params = $request->get_params();
    $files = $request->get_file_params();

    // 1. Actualizar el post
    $post_data = array(
        'ID'           => $post_id,
        'post_title'   => sanitize_text_field($params['title'] ?? ''),
        'post_content' => sanitize_textarea_field($params['description'] ?? ''),
    );
    wp_update_post($post_data);

    // 2. Actualizar Taxonomías
    if (!empty($params['type'])) {
        wp_set_object_terms($post_id, sanitize_text_field($params['type']), 'impress_property_type');
    }
    if (!empty($params['status'])) {
        wp_set_object_terms($post_id, sanitize_text_field($params['status']), 'impress_status');
    }
    if (!empty($params['city'])) {
        wp_set_object_terms($post_id, sanitize_text_field($params['city']), 'impress_city');
    }

    // 3. Actualizar Metadatos
    if (isset($params['price'])) {
        update_post_meta($post_id, 'precio_venta', sanitize_text_field($params['price']));
        update_post_meta($post_id, 'precio', sanitize_text_field($params['price']));
    }
    if (isset($params['rooms'])) {
        update_post_meta($post_id, 'habitaciones', sanitize_text_field($params['rooms']));
        update_post_meta($post_id, 'dormitorios', sanitize_text_field($params['rooms']));
    }
    if (isset($params['baths'])) {
        update_post_meta($post_id, 'banos', sanitize_text_field($params['baths']));
    }
    if (isset($params['size'])) {
        update_post_meta($post_id, 'superficie_construida', sanitize_text_field($params['size']));
    }

    // 4. Procesar nuevas imágenes (se añaden a las existentes)
    if (!empty($files['images'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $files_array = $files['images'];
        $count = is_array($files_array['name']) ? count($files_array['name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $file = array(
                'name'     => is_array($files_array['name']) ? $files_array['name'][$i] : $files_array['name'],
                'type'     => is_array($files_array['type']) ? $files_array['type'][$i] : $files_array['type'],
                'tmp_name' => is_array($files_array['tmp_name']) ? $files_array['tmp_name'][$i] : $files_array['tmp_name'],
                'error'    => is_array($files_array['error']) ? $files_array['error'][$i] : $files_array['error'],
                'size'     => is_array($files_array['size']) ? $files_array['size'][$i] : $files_array['size']
            );

            if ($file['error'] === 0) {
                media_handle_sideload($file, $post_id);
            }
        }
    }

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Inmueble actualizado correctamente.'
    ));
}

function inmopress_dashboard_delete_attachment_handler($request) {
    $post_id = $request['post_id'];
    $attachment_id = $request['attachment_id'];

    if (empty($post_id) || empty($attachment_id)) {
        return new WP_Error('missing_data', 'ID de inmueble o imagen faltante.', array('status' => 400));
    }

    $parent_id = wp_get_post_parent_id($attachment_id);
    if (intval($parent_id) !== intval($post_id)) {
        return new WP_Error('wrong_parent', 'La imagen no pertenece a este inmueble.', array('status' => 403));
    }

    $deleted = wp_delete_attachment($attachment_id, true);

    if (!$deleted) {
        return new WP_Error('delete_failed', 'No se pudo eliminar la imagen.', array('status' => 500));
    }

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Imagen eliminada correctamente.'
    ));
}

