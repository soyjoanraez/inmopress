<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para generar contenido fake en CPTs y rellenar ACFs
 */
class Inmopress_Fake_Content_Seeder
{
    private static $reference_counter = 0;

    public static function get_post_types()
    {
        return array(
            'impress_property' => 'Inmueble',
            'impress_client' => 'Cliente',
            'impress_lead' => 'Lead',
            'impress_visit' => 'Visita',
            'impress_agency' => 'Agencia',
            'impress_agent' => 'Agente',
            'impress_owner' => 'Propietario',
            'impress_promotion' => 'Promoción',
            'impress_transaction' => 'Transacción',
            'impress_email_tpl' => 'Plantilla Email',
            'impress_event' => 'Evento',
        );
    }

    public static function get_default_counts()
    {
        return array(
            'impress_property' => 10,
            'impress_client' => 6,
            'impress_lead' => 8,
            'impress_visit' => 6,
            'impress_agency' => 3,
            'impress_agent' => 5,
            'impress_owner' => 4,
            'impress_promotion' => 3,
            'impress_transaction' => 6,
            'impress_email_tpl' => 3,
            'impress_event' => 6,
        );
    }

    public static function seed_all($counts = array())
    {
        if (class_exists('Inmopress_Taxonomy_Seeder')) {
            Inmopress_Taxonomy_Seeder::seed_all();
        }

        $defaults = self::get_default_counts();
        $post_types = self::get_post_types();
        $counts = array_merge($defaults, is_array($counts) ? $counts : array());

        $created_ids = array();
        $stats = array(
            'total_created' => 0,
            'by_post_type' => array(),
        );

        foreach ($post_types as $post_type => $label) {
            $count = isset($counts[$post_type]) ? max(0, intval($counts[$post_type])) : 0;
            $created = 0;
            for ($i = 0; $i < $count; $i++) {
                $post_id = self::create_post($post_type, $i + 1);
                if ($post_id && !is_wp_error($post_id)) {
                    $created++;
                    $created_ids[] = $post_id;
                }
            }
            $stats['by_post_type'][$post_type] = $created;
            $stats['total_created'] += $created;
        }

        foreach ($created_ids as $post_id) {
            self::populate_acf_fields($post_id);
            self::assign_taxonomies($post_id);
        }

        return $stats;
    }

    public static function clear_all()
    {
        $post_types = array_keys(self::get_post_types());
        $posts = get_posts(array(
            'post_type' => $post_types,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_key' => 'inmopress_fake_content',
            'meta_value' => '1',
        ));

        $deleted = 0;
        if ($posts) {
            foreach ($posts as $post_id) {
                wp_delete_post($post_id, true);
                $deleted++;
            }
        }

        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_key' => 'inmopress_fake_content',
            'meta_value' => '1',
        ));

        if ($attachments) {
            foreach ($attachments as $attachment_id) {
                wp_delete_attachment($attachment_id, true);
            }
        }

        delete_option('inmopress_fake_attachment_ids');

        return $deleted;
    }

    private static function create_post($post_type, $index)
    {
        $title = self::fake_title($post_type, $index);
        $content = self::fake_content($post_type);

        $post_id = wp_insert_post(array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'post_title' => $title,
            'post_content' => $content,
        ));

        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, 'inmopress_fake_content', '1');
            if (post_type_supports($post_type, 'thumbnail')) {
                $thumb_id = self::get_placeholder_attachment_id();
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
            }
        }

        return $post_id;
    }

    private static function populate_acf_fields($post_id)
    {
        if (!function_exists('acf_get_field_groups') || !function_exists('acf_get_fields')) {
            return;
        }

        $post_type = get_post_type($post_id);
        $groups = acf_get_field_groups(array('post_type' => $post_type));
        if (!$groups) {
            return;
        }

        foreach ($groups as $group) {
            $fields = acf_get_fields($group);
            if (!$fields) {
                continue;
            }

            foreach ($fields as $field) {
                $value = self::fake_value_for_field($field, $post_id);
                if ($value !== null) {
                    update_field($field['key'], $value, $post_id);
                }
            }
        }
    }

    private static function assign_taxonomies($post_id)
    {
        $post_type = get_post_type($post_id);
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        if (empty($taxonomies)) {
            return;
        }

        foreach ($taxonomies as $taxonomy => $taxonomy_obj) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));

            if (is_wp_error($terms) || empty($terms)) {
                continue;
            }

            $count = min(3, max(1, rand(1, 3)));
            $picked = array();
            shuffle($terms);
            foreach (array_slice($terms, 0, $count) as $term) {
                $picked[] = $term->term_id;
            }

            wp_set_object_terms($post_id, $picked, $taxonomy, false);
        }
    }

    private static function fake_value_for_field($field, $post_id)
    {
        $type = isset($field['type']) ? $field['type'] : '';

        switch ($type) {
            case 'text':
                return self::fake_text($field, $post_id);
            case 'textarea':
                return self::fake_paragraph();
            case 'wysiwyg':
                return '<p>' . esc_html(self::fake_paragraph()) . '</p>';
            case 'number':
            case 'range':
                return self::fake_number($field);
            case 'email':
                return self::fake_email();
            case 'url':
                return self::fake_url();
            case 'true_false':
                return rand(0, 1) ? 1 : 0;
            case 'select':
            case 'radio':
            case 'button_group':
                return self::fake_choice($field);
            case 'checkbox':
                return self::fake_checkbox($field);
            case 'date_picker':
                return date('Ymd', strtotime('+' . rand(-90, 90) . ' days'));
            case 'date_time_picker':
                return date('Y-m-d H:i:s', strtotime('+' . rand(-30, 30) . ' days ' . rand(8, 20) . ' hours'));
            case 'time_picker':
                return date('H:i', strtotime(rand(8, 20) . ':00'));
            case 'color_picker':
                return self::fake_color();
            case 'image':
                return self::format_attachment_value($field, self::get_placeholder_attachment_id());
            case 'gallery':
                return self::get_placeholder_gallery_ids();
            case 'file':
                return self::format_attachment_value($field, self::get_placeholder_attachment_id());
            case 'link':
                return array(
                    'title' => 'Ver más',
                    'url' => self::fake_url(),
                    'target' => '_self',
                );
            case 'post_object':
            case 'relationship':
                return self::fake_related_posts($field, $post_id);
            case 'taxonomy':
                return self::fake_taxonomy_terms($field);
            case 'user':
                return self::fake_user($field);
            case 'repeater':
                return self::fake_repeater($field, $post_id);
            case 'group':
                return self::fake_group($field, $post_id);
            case 'flexible_content':
                return self::fake_flexible($field, $post_id);
            case 'google_map':
                return self::fake_google_map();
            case 'message':
            case 'tab':
            case 'accordion':
                return null;
            default:
                return null;
        }
    }

    private static function fake_title($post_type, $index)
    {
        $cities = self::fake_cities();
        $city = $cities[array_rand($cities)];

        switch ($post_type) {
            case 'impress_property':
                $ref = self::fake_reference();
                return 'Piso en ' . $city . ' - ' . $ref;
            case 'impress_client':
                return 'Cliente ' . self::fake_person_name();
            case 'impress_lead':
                return 'Lead ' . self::fake_person_name();
            case 'impress_visit':
                return 'Visita ' . $index . ' - ' . $city;
            case 'impress_agency':
                return 'Agencia ' . self::fake_company_name();
            case 'impress_agent':
                return 'Agente ' . self::fake_person_name();
            case 'impress_owner':
                return 'Propietario ' . self::fake_person_name();
            case 'impress_promotion':
                return 'Promoción ' . $city . ' ' . $index;
            case 'impress_transaction':
                return 'Transacción ' . str_pad((string) $index, 3, '0', STR_PAD_LEFT);
            case 'impress_email_tpl':
                return 'Plantilla Email ' . $index;
            case 'impress_event':
                return 'Evento ' . $index . ' - ' . self::fake_event_title();
            default:
                return ucfirst($post_type) . ' ' . $index;
        }
    }

    private static function fake_content($post_type)
    {
        switch ($post_type) {
            case 'impress_property':
                return self::fake_property_description();
            case 'impress_promotion':
                return self::fake_promotion_description();
            default:
                return self::fake_paragraph();
        }
    }

    private static function fake_text($field, $post_id)
    {
        $name = isset($field['name']) ? strtolower($field['name']) : '';
        $label = isset($field['label']) ? strtolower($field['label']) : '';

        if ($name === 'referencia' || strpos($name, 'referencia') !== false) {
            return self::fake_reference();
        }

        if (strpos($name, 'email') !== false || strpos($label, 'email') !== false) {
            return self::fake_email();
        }

        if (strpos($name, 'telefono') !== false || strpos($label, 'tel') !== false) {
            return self::fake_phone();
        }

        if (strpos($name, 'direccion') !== false) {
            return self::fake_address();
        }

        if (strpos($name, 'nombre') !== false) {
            return self::fake_person_name();
        }

        if (strpos($name, 'apellido') !== false) {
            return self::fake_last_name();
        }

        if (strpos($name, 'ciudad') !== false || strpos($name, 'poblacion') !== false) {
            $cities = self::fake_cities();
            return $cities[array_rand($cities)];
        }

        if (strpos($name, 'provincia') !== false) {
            $provinces = self::fake_provinces();
            return $provinces[array_rand($provinces)];
        }

        if (strpos($name, 'web') !== false || strpos($name, 'url') !== false) {
            return self::fake_url();
        }

        if (strpos($name, 'precio') !== false) {
            return (string) self::fake_number(array('min' => 80000, 'max' => 650000));
        }

        if (strpos($name, 'codigo_postal') !== false || strpos($name, 'cp') !== false) {
            return (string) rand(10001, 46980);
        }

        return self::fake_short_text();
    }

    private static function fake_choice($field)
    {
        $choices = isset($field['choices']) ? $field['choices'] : array();
        if (empty($choices)) {
            return null;
        }

        $keys = array_keys($choices);
        return $keys[array_rand($keys)];
    }

    private static function fake_checkbox($field)
    {
        $choices = isset($field['choices']) ? $field['choices'] : array();
        if (empty($choices)) {
            return array();
        }

        $keys = array_keys($choices);
        shuffle($keys);
        $count = max(1, min(3, rand(1, count($keys))));
        return array_slice($keys, 0, $count);
    }

    private static function fake_number($field)
    {
        $min = isset($field['min']) && $field['min'] !== '' ? floatval($field['min']) : null;
        $max = isset($field['max']) && $field['max'] !== '' ? floatval($field['max']) : null;

        if ($min !== null && $max !== null) {
            return rand((int) $min, (int) $max);
        }

        if ($min !== null) {
            return rand((int) $min, (int) $min + 200);
        }

        if ($max !== null) {
            return rand(1, (int) $max);
        }

        return rand(1, 250);
    }

    private static function fake_related_posts($field, $post_id)
    {
        $post_types = isset($field['post_type']) && !empty($field['post_type']) ? $field['post_type'] : array('post');
        $multiple = !empty($field['multiple']);

        $posts = get_posts(array(
            'post_type' => $post_types,
            'posts_per_page' => $multiple ? 3 : 1,
            'orderby' => 'rand',
            'post__not_in' => array($post_id),
            'fields' => 'ids',
        ));

        if (empty($posts)) {
            return $multiple ? array() : null;
        }

        return $multiple ? $posts : $posts[0];
    }

    private static function fake_taxonomy_terms($field)
    {
        if (empty($field['taxonomy'])) {
            return null;
        }

        $taxonomy = $field['taxonomy'];
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));

        if (is_wp_error($terms) || empty($terms)) {
            return null;
        }

        $return_format = isset($field['return_format']) ? $field['return_format'] : 'id';
        $multiple = in_array($field['field_type'], array('multi_select', 'checkbox'), true);

        shuffle($terms);
        $count = $multiple ? min(3, max(1, rand(1, 3))) : 1;
        $selected = array_slice($terms, 0, $count);

        $values = array();
        foreach ($selected as $term) {
            if ($return_format === 'object') {
                $values[] = $term;
            } elseif ($return_format === 'name') {
                $values[] = $term->name;
            } else {
                $values[] = $term->term_id;
            }
        }

        return $multiple ? $values : $values[0];
    }

    private static function fake_user($field)
    {
        $users = get_users(array('fields' => array('ID')));
        if (empty($users)) {
            return null;
        }

        $ids = array_map(function ($user) {
            return $user->ID;
        }, $users);

        $multiple = !empty($field['multiple']);
        if ($multiple) {
            shuffle($ids);
            return array_slice($ids, 0, min(3, count($ids)));
        }

        return $ids[array_rand($ids)];
    }

    private static function fake_repeater($field, $post_id)
    {
        if (empty($field['sub_fields'])) {
            return array();
        }

        $rows = array();
        $count = isset($field['min']) && $field['min'] ? intval($field['min']) : rand(1, 2);
        $count = min(3, max(1, $count));

        for ($i = 0; $i < $count; $i++) {
            $row = array();
            foreach ($field['sub_fields'] as $sub_field) {
                $row[$sub_field['name']] = self::fake_value_for_field($sub_field, $post_id);
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private static function fake_group($field, $post_id)
    {
        if (empty($field['sub_fields'])) {
            return array();
        }

        $group = array();
        foreach ($field['sub_fields'] as $sub_field) {
            $group[$sub_field['name']] = self::fake_value_for_field($sub_field, $post_id);
        }

        return $group;
    }

    private static function fake_flexible($field, $post_id)
    {
        if (empty($field['layouts'])) {
            return array();
        }

        $layouts = $field['layouts'];
        $layout = $layouts[array_rand($layouts)];
        if (empty($layout['sub_fields'])) {
            return array();
        }

        $row = array('acf_fc_layout' => $layout['name']);
        foreach ($layout['sub_fields'] as $sub_field) {
            $row[$sub_field['name']] = self::fake_value_for_field($sub_field, $post_id);
        }

        return array($row);
    }

    private static function fake_google_map()
    {
        $cities = self::fake_cities();
        $city = $cities[array_rand($cities)];

        return array(
            'address' => 'Centro ' . $city,
            'lat' => 39.4699 + (rand(-50, 50) / 1000),
            'lng' => -0.3763 + (rand(-50, 50) / 1000),
        );
    }

    private static function fake_reference()
    {
        self::$reference_counter++;
        return 'REF' . date('ymd') . str_pad((string) self::$reference_counter, 3, '0', STR_PAD_LEFT) . str_pad((string) rand(0, 99), 2, '0', STR_PAD_LEFT);
    }

    private static function fake_person_name()
    {
        $names = array('Ana', 'Laura', 'Carlos', 'Marta', 'David', 'Lucía', 'Javier', 'Elena', 'Sergio', 'Nuria');
        $surnames = array('García', 'Martínez', 'López', 'Hernández', 'Torres', 'Sánchez', 'Pérez', 'Díaz', 'Ramírez', 'Vega');
        return $names[array_rand($names)] . ' ' . $surnames[array_rand($surnames)];
    }

    private static function fake_last_name()
    {
        $surnames = array('García', 'Martínez', 'López', 'Hernández', 'Torres', 'Sánchez', 'Pérez', 'Díaz', 'Ramírez', 'Vega');
        return $surnames[array_rand($surnames)];
    }

    private static function fake_company_name()
    {
        $prefixes = array('Inmo', 'Casa', 'Hogar', 'Urban', 'Nova', 'Prime', 'Vita');
        $suffixes = array('Valencia', 'Center', 'Realty', 'Homes', 'Plus', 'Group', 'Estate');
        return $prefixes[array_rand($prefixes)] . ' ' . $suffixes[array_rand($suffixes)];
    }

    private static function fake_address()
    {
        $streets = array('Calle Colón', 'Avenida del Puerto', 'Gran Vía', 'Calle de la Paz', 'Avenida Aragón', 'Calle Mayor');
        return $streets[array_rand($streets)] . ' ' . rand(1, 120);
    }

    private static function fake_short_text()
    {
        $texts = array(
            'Excelente ubicación',
            'Reformado recientemente',
            'Muy luminoso',
            'Con vistas despejadas',
            'Ideal para familias',
            'Cerca de servicios',
        );
        return $texts[array_rand($texts)];
    }

    private static function fake_paragraph()
    {
        $sentences = array(
            'Vivienda con excelente distribución y acabados modernos.',
            'Ubicación privilegiada con todos los servicios a pie de calle.',
            'Ideal para quienes buscan comodidad y estilo en una zona consolidada.',
            'Dispone de espacios amplios y muy buena iluminación natural.',
        );
        shuffle($sentences);
        return implode(' ', array_slice($sentences, 0, 3));
    }

    private static function fake_property_description()
    {
        return 'Propiedad destacada con buenas calidades, orientación ideal y cercanía a servicios. ' . self::fake_paragraph();
    }

    private static function fake_promotion_description()
    {
        return 'Promoción exclusiva con viviendas de diferentes tipologías y zonas comunes. ' . self::fake_paragraph();
    }

    private static function fake_event_title()
    {
        $titles = array('Reunión con cliente', 'Visita a inmueble', 'Llamada de seguimiento', 'Firma de contrato');
        return $titles[array_rand($titles)];
    }

    private static function fake_email()
    {
        $domains = array('example.com', 'inmopress.test', 'correo.com');
        $user = strtolower('cliente' . rand(100, 999));
        return $user . '@' . $domains[array_rand($domains)];
    }

    private static function fake_url()
    {
        $paths = array('contacto', 'inmuebles', 'promociones', 'agencia');
        return 'https://example.com/' . $paths[array_rand($paths)];
    }

    private static function fake_phone()
    {
        return '+34 6' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999);
    }

    private static function fake_color()
    {
        return sprintf('#%06X', rand(0, 0xFFFFFF));
    }

    private static function fake_cities()
    {
        return array('Valencia', 'Madrid', 'Barcelona', 'Sevilla', 'Málaga', 'Alicante', 'Bilbao', 'Zaragoza');
    }

    private static function fake_provinces()
    {
        return array('Valencia', 'Madrid', 'Barcelona', 'Sevilla', 'Málaga', 'Alicante');
    }

    private static function format_attachment_value($field, $attachment_id)
    {
        if (!$attachment_id) {
            return null;
        }

        $return_format = isset($field['return_format']) ? $field['return_format'] : 'id';
        if ($return_format === 'url') {
            return wp_get_attachment_url($attachment_id);
        }

        if ($return_format === 'array') {
            if (function_exists('acf_get_attachment')) {
                return acf_get_attachment($attachment_id);
            }

            return array(
                'ID' => $attachment_id,
                'url' => wp_get_attachment_url($attachment_id),
            );
        }

        return $attachment_id;
    }

    private static function get_placeholder_attachment_id()
    {
        $ids = self::get_placeholder_attachment_ids();
        return !empty($ids) ? $ids[0] : 0;
    }

    private static function get_placeholder_gallery_ids()
    {
        $ids = self::get_placeholder_attachment_ids();
        if (empty($ids)) {
            return array();
        }

        return array_slice($ids, 0, min(3, count($ids)));
    }

    private static function get_placeholder_attachment_ids()
    {
        $stored = get_option('inmopress_fake_attachment_ids');
        if (is_array($stored) && !empty($stored)) {
            return array_values(array_filter($stored));
        }

        $ids = array();
        for ($i = 1; $i <= 3; $i++) {
            $id = self::create_placeholder_attachment($i);
            if ($id) {
                $ids[] = $id;
            }
        }

        if (!empty($ids)) {
            update_option('inmopress_fake_attachment_ids', $ids, false);
        }

        return $ids;
    }

    private static function create_placeholder_attachment($index)
    {
        $upload_dir = wp_upload_dir();
        if (empty($upload_dir['path']) || empty($upload_dir['url'])) {
            return 0;
        }

        if (!wp_mkdir_p($upload_dir['path'])) {
            return 0;
        }

        $filename = 'inmopress-fake-' . $index . '.png';
        $filepath = trailingslashit($upload_dir['path']) . $filename;
        $fileurl = trailingslashit($upload_dir['url']) . $filename;

        if (!file_exists($filepath)) {
            self::write_placeholder_image($filepath, $index);
        }

        $attachment = array(
            'post_mime_type' => 'image/png',
            'post_title' => 'Inmopress Fake ' . $index,
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $fileurl,
        );

        $attachment_id = wp_insert_attachment($attachment, $filepath);
        if (!$attachment_id || is_wp_error($attachment_id)) {
            return 0;
        }

        update_post_meta($attachment_id, 'inmopress_fake_content', '1');

        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $metadata = wp_generate_attachment_metadata($attachment_id, $filepath);
        if (!is_wp_error($metadata)) {
            wp_update_attachment_metadata($attachment_id, $metadata);
        }

        return $attachment_id;
    }

    private static function write_placeholder_image($filepath, $index)
    {
        if (function_exists('imagecreatetruecolor')) {
            $img = imagecreatetruecolor(800, 520);
            if (!$img) {
                file_put_contents($filepath, self::fallback_png());
                return;
            }

            $colors = array(
                array(52, 120, 246),
                array(46, 204, 113),
                array(243, 156, 18),
            );
            $color = $colors[($index - 1) % count($colors)];
            $bg = imagecolorallocate($img, $color[0], $color[1], $color[2]);
            imagefill($img, 0, 0, $bg);

            $white = imagecolorallocate($img, 255, 255, 255);
            imagestring($img, 5, 20, 20, 'INMOPRESS', $white);
            imagestring($img, 3, 20, 50, 'Contenido Fake', $white);

            imagepng($img, $filepath);
            imagedestroy($img);
        } else {
            file_put_contents($filepath, self::fallback_png());
        }
    }

    private static function fallback_png()
    {
        $base64 = 'iVBORw0KGgoAAAANSUhEUgAAAyAAAAGACAYAAADN7v2aAAAACXBIWXMAAAsTAAALEwEAmpwYAAABZ0lEQVR4nO3BMQEAAADCoPVPbQhPoAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAL4B5gABg+1pYQAAAABJRU5ErkJggg==';
        return base64_decode($base64);
    }
}
