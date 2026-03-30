<?php if (!defined('ABSPATH')) {
    exit;
}
// Variables: $query, $search, $paged

$base_url = Inmopress_Shortcodes::panel_url('owners');
$new_url = Inmopress_Shortcodes::panel_url('owners', array('new' => 1));
$total_count = isset($query) && $query instanceof WP_Query ? (int) $query->found_posts : 0;
$search_value = trim((string) $search);

$get_meta = function ($key, $post_id) {
    if (function_exists('get_field')) {
        return get_field($key, $post_id);
    }
    return get_post_meta($post_id, $key, true);
};
?>

<div class="crm-clients crm-owners">
    <div class="crm-clients-header">
        <div>
            <h1 class="crm-clients-title">Propietarios</h1>
            <p class="crm-clients-subtitle"><?php echo number_format_i18n($total_count); ?> propietarios en total</p>
        </div>
        <div class="crm-clients-actions">
            <form method="get" action="<?php echo esc_url($base_url); ?>" class="crm-client-search">
                <input type="hidden" name="paged" value="1">
                <span class="dashicons dashicons-search"></span>
                <input type="text" name="s" value="<?php echo esc_attr($search_value); ?>" placeholder="Buscar propietarios...">
            </form>
            <a href="<?php echo esc_url($new_url); ?>" class="btn-crm primary">
                <span class="dashicons dashicons-plus"></span> Nuevo propietario
            </a>
        </div>
    </div>

    <?php if ($query->have_posts()): ?>
        <div class="crm-card" style="padding: 0;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Propietario</th>
                        <th>Contacto</th>
                        <th>Inmuebles</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($query->have_posts()):
                        $query->the_post();
                        $owner_id = get_the_ID();
                        $nombre = $get_meta('nombre', $owner_id);
                        $apellidos = $get_meta('apellidos', $owner_id);
                        $owner_name = trim(trim($nombre . ' ' . $apellidos));
                        if ($owner_name === '') {
                            $owner_name = get_the_title($owner_id);
                        }

                        $email = $get_meta('correo', $owner_id);
                        if (!$email) {
                            $email = $get_meta('email', $owner_id);
                        }
                        $telefono = $get_meta('telefono', $owner_id);

                        $properties = function_exists('get_field') ? get_field('inmuebles', $owner_id) : array();
                        $property_ids = array();
                        if (is_array($properties)) {
                            foreach ($properties as $property) {
                                if (is_object($property) && isset($property->ID)) {
                                    $property_ids[] = (int) $property->ID;
                                } elseif (is_numeric($property)) {
                                    $property_ids[] = (int) $property;
                                }
                            }
                        }

                        if (empty($property_ids)) {
                            $property_ids = get_posts(array(
                                'post_type' => 'impress_property',
                                'posts_per_page' => -1,
                                'fields' => 'ids',
                                'meta_query' => array(
                                    array(
                                        'key' => 'propietario',
                                        'value' => $owner_id,
                                        'compare' => '=',
                                    ),
                                ),
                            ));
                        }

                        $property_ids = is_array($property_ids) ? array_values(array_filter(array_map('intval', $property_ids))) : array();
                        $property_count = count($property_ids);
                        $property_preview = array_slice($property_ids, 0, 2);
                        $property_titles = array();
                        foreach ($property_preview as $property_id) {
                            $property_titles[] = get_the_title($property_id);
                        }
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <strong><?php echo esc_html($owner_name ?: 'Propietario'); ?></strong>
                                    <span style="font-size: 12px; color: #9CA3AF;">ID #<?php echo esc_html($owner_id); ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span><?php echo esc_html($email ?: '—'); ?></span>
                                    <span style="color: #6B7280; font-size: 12px;"><?php echo esc_html($telefono ?: '—'); ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if ($property_count > 0): ?>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <strong><?php echo esc_html($property_count); ?> inmuebles</strong>
                                        <span style="font-size: 12px; color: #6B7280;">
                                            <?php echo esc_html(implode(' · ', $property_titles)); ?>
                                            <?php if ($property_count > count($property_titles)): ?>
                                                <?php echo esc_html(' +' . ($property_count - count($property_titles))); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #9CA3AF;">Sin inmuebles</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="crm-table-actions">
                                    <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('owners', array('edit' => $owner_id))); ?>"
                                        class="btn-crm ghost" style="padding: 6px 12px; font-size: 12px;">Editar</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="crm-pagination" style="margin-top: 16px;">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',
                'add_args' => array(
                    's' => $search,
                ),
            ));
            ?>
        </div>
    <?php else: ?>
        <div class="crm-empty-state">
            <p>No hay propietarios registrados<?php echo $search_value !== '' ? ' con los filtros seleccionados' : ''; ?>.</p>
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
</div>
