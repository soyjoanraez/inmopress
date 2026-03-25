<?php if (!defined('ABSPATH')) exit;
// Variables: $query, $search, $status, $paged, $list_title, $new_label, $new_url, $hide_new_button, $edit_tab, $property_id, $semaforo_labels, $total_count, $status_counts

if (!function_exists('inmopress_client_initials')) {
    function inmopress_client_initials($name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '';
        }
        $parts = preg_split('/\s+/', $name);
        $first = $parts[0] ?? '';
        $second = $parts[1] ?? '';

        $sub = function ($value, $start, $length) {
            if (function_exists('mb_substr')) {
                return mb_substr($value, $start, $length);
            }
            return substr($value, $start, $length);
        };
        $len = function ($value) {
            if (function_exists('mb_strlen')) {
                return mb_strlen($value);
            }
            return strlen($value);
        };

        $initials = '';
        if ($first !== '') {
            $initials .= $sub($first, 0, 1);
        }
        if ($second !== '') {
            $initials .= $sub($second, 0, 1);
        } elseif ($len($first) > 1) {
            $initials .= $sub($first, 1, 1);
        }

        return strtoupper($initials);
    }
}

$edit_tab = isset($edit_tab) ? sanitize_key($edit_tab) : (isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'clients');
$is_leads_tab = ($edit_tab === 'leads');
$section_label = isset($list_title) ? $list_title : ($is_leads_tab ? 'Prospectos' : 'Clientes');
$new_label = isset($new_label) ? $new_label : ($is_leads_tab ? 'Nuevo prospecto' : 'Nuevo cliente');
$new_url = isset($new_url) ? $new_url : Inmopress_Shortcodes::panel_url($edit_tab, array('new' => 1));
$hide_new_button = isset($hide_new_button) ? (bool) $hide_new_button : false;
$property_id = isset($property_id) ? absint($property_id) : 0;

if (!function_exists('inmopress_build_client_search_meta')) {
    function inmopress_build_client_search_meta($search_value)
    {
        return array(
            'relation' => 'OR',
            array(
                'key' => 'nombre',
                'value' => $search_value,
                'compare' => 'LIKE',
            ),
            array(
                'key' => 'apellidos',
                'value' => $search_value,
                'compare' => 'LIKE',
            ),
            array(
                'key' => 'correo',
                'value' => $search_value,
                'compare' => 'LIKE',
            ),
            array(
                'key' => 'email',
                'value' => $search_value,
                'compare' => 'LIKE',
            ),
            array(
                'key' => 'telefono',
                'value' => $search_value,
                'compare' => 'LIKE',
            ),
            array(
                'key' => 'movil',
                'value' => $search_value,
                'compare' => 'LIKE',
            ),
            array(
                'key' => 'direccion',
                'value' => $search_value,
                'compare' => 'LIKE',
            ),
            array(
                'key' => 'zona_interes',
                'value' => $search_value,
                'compare' => 'LIKE',
            ),
        );
    }
}

$semaforo_labels = isset($semaforo_labels) && is_array($semaforo_labels) ? $semaforo_labels : array(
    'hot' => 'HOT',
    'warm' => 'WARM',
    'cold' => 'COLD',
);

$search_value = trim((string) $search);
$search_args = array();
if ($search_value !== '') {
    $search_args['s'] = $search_value;
}

$total_count = isset($total_count) ? (int) $total_count : 0;
$status_counts = isset($status_counts) && is_array($status_counts) ? $status_counts : array();

$base_url = Inmopress_Shortcodes::panel_url($edit_tab);
$property_title = $property_id ? get_the_title($property_id) : '';
?>
<div class="crm-clients">
    <div class="crm-clients-header">
        <div>
            <?php $section_label_lower = function_exists('mb_strtolower') ? mb_strtolower($section_label, 'UTF-8') : strtolower($section_label); ?>
            <h1 class="crm-clients-title"><?php echo esc_html($section_label); ?></h1>
            <p class="crm-clients-subtitle"><?php echo number_format_i18n($total_count); ?> <?php echo esc_html($section_label_lower); ?> en total</p>
        </div>
        <div class="crm-clients-actions">
            <form method="get" action="<?php echo esc_url($base_url); ?>" class="crm-client-search">
                <?php if ($property_id): ?>
                    <input type="hidden" name="property" value="<?php echo esc_attr($property_id); ?>">
                <?php endif; ?>
                <?php if (!empty($status)): ?>
                    <input type="hidden" name="status" value="<?php echo esc_attr($status); ?>">
                <?php endif; ?>
                <input type="hidden" name="paged" value="1">
                <span class="dashicons dashicons-search"></span>
                <input type="text" name="s" value="<?php echo esc_attr($search_value); ?>" placeholder="Buscar <?php echo esc_attr($section_label_lower); ?>...">
            </form>
            <?php if (!$hide_new_button): ?>
                <a href="<?php echo esc_url($new_url); ?>" class="btn-crm primary">
                    <span class="dashicons dashicons-plus"></span> <?php echo esc_html($new_label); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($property_id): ?>
        <div class="crm-filter-chips crm-filter-chips-top">
            <span class="crm-filter-chip active">
                Filtrado por propiedad: <?php echo esc_html($property_title ? $property_title : ('#' . $property_id)); ?>
            </span>
            <a class="crm-filter-chip" href="<?php echo esc_url($base_url); ?>">
                Quitar filtro
            </a>
        </div>
    <?php endif; ?>

    <div class="crm-filter-chips">
        <?php
        $all_args = $search_args;
        if ($property_id) {
            $all_args['property'] = $property_id;
        }
        $all_url = add_query_arg($all_args, $base_url);
        $all_active = empty($status);
        ?>
        <a class="crm-filter-chip <?php echo $all_active ? 'active' : ''; ?>" href="<?php echo esc_url($all_url); ?>">
            Todos (<?php echo number_format_i18n($total_count); ?>)
        </a>
        <?php foreach ($semaforo_labels as $status_key => $status_label):
            $term_args = array_merge($search_args, array('status' => $status_key));
            if ($property_id) {
                $term_args['property'] = $property_id;
            }
            $term_url = add_query_arg($term_args, $base_url);
            $is_active = ($status === $status_key);
            $count = $status_counts[$status_key] ?? 0;
            ?>
            <a class="crm-filter-chip status-<?php echo esc_attr($status_key); ?> <?php echo $is_active ? 'active' : ''; ?>"
                href="<?php echo esc_url($term_url); ?>">
                <?php echo esc_html($status_label); ?> (<?php echo number_format_i18n($count); ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($query->have_posts()): ?>
        <div class="crm-clients-grid">
            <?php while ($query->have_posts()):
                $query->the_post();
                $client_id = get_the_ID();
                $nombre = get_field('nombre');
                $apellidos = get_field('apellidos');
                $client_name = trim(trim($nombre . ' ' . $apellidos));
                if ($client_name === '') {
                    $client_name = get_the_title();
                }
                $client_email = get_field('correo') ?: get_field('email');
                $client_phone = get_field('telefono') ?: get_field('movil');
                $lead_stage = Inmopress_Shortcodes::get_lead_status_terms();
                $semaforo = get_field('semaforo_estado');
                $puntuacion = get_field('puntuacion');
                $initials = inmopress_client_initials($client_name);
                $avatar_classes = array('crm-avatar-blue', 'crm-avatar-amber', 'crm-avatar-indigo', 'crm-avatar-emerald', 'crm-avatar-rose');
                $avatar_index = abs(crc32((string) $client_name)) % count($avatar_classes);
                $avatar_class = $avatar_classes[$avatar_index];
                $status_label = isset($semaforo_labels[$semaforo]) ? $semaforo_labels[$semaforo] : 'Sin estado';
                $status_slug = isset($semaforo_labels[$semaforo]) ? $semaforo : 'default';
                ?>
                <a class="crm-client-card" href="<?php echo esc_url(Inmopress_Shortcodes::panel_url($edit_tab, array('edit' => $client_id))); ?>">
                    <div class="crm-client-avatar <?php echo esc_attr($avatar_class); ?>">
                        <?php echo esc_html($initials); ?>
                    </div>
                    <div class="crm-client-info">
                        <div class="crm-client-name-row">
                            <h3><?php echo esc_html($client_name); ?></h3>
                            <span class="crm-client-status status-<?php echo esc_attr($status_slug); ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                        </div>
                        <div class="crm-client-meta">
                            <?php if (!empty($client_phone)): ?>
                                <span><span class="dashicons dashicons-phone"></span><?php echo esc_html($client_phone); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($client_email)): ?>
                                <span><span class="dashicons dashicons-email"></span><?php echo esc_html($client_email); ?></span>
                            <?php endif; ?>
                            <?php if ($is_leads_tab && !empty($lead_stage)): ?>
                                <span><span class="dashicons dashicons-flag"></span><?php echo esc_html($lead_stage); ?></span>
                            <?php endif; ?>
                            <?php if ($puntuacion !== null && $puntuacion !== ''): ?>
                                <span><span class="dashicons dashicons-chart-bar"></span><?php echo esc_html($puntuacion); ?>/10</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>

        <div class="crm-pagination">
            <?php
            $pagination_args = array(
                's' => $search,
                'status' => $status,
            );
            if ($property_id) {
                $pagination_args['property'] = $property_id;
            }
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',
                'add_args' => $pagination_args,
            ));
            ?>
        </div>
    <?php else: ?>
        <div class="crm-empty-state">
            <p>No hay <?php echo esc_html($section_label_lower); ?> registrados<?php echo !empty($search) || !empty($status) || $property_id ? ' con los filtros seleccionados' : ''; ?>.</p>
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
</div>
