<?php if (!defined('ABSPATH'))
    exit;
// Available variables: $query (WP_Query object)
$selected_property_id = isset($_GET['property']) ? absint($_GET['property']) : 0;
$selected_property = $selected_property_id ? get_post($selected_property_id) : null;
if ($selected_property && $selected_property->post_type !== 'impress_property') {
    $selected_property = null;
    $selected_property_id = 0;
}

$search = isset($search) ? $search : (isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '');
$operation = isset($operation) ? $operation : (isset($_GET['operation']) ? sanitize_key($_GET['operation']) : '');
$city = isset($city) ? $city : (isset($_GET['city']) ? sanitize_key($_GET['city']) : '');
$property_type = isset($property_type) ? $property_type : (isset($_GET['type']) ? sanitize_key($_GET['type']) : '');
$price_min = isset($_GET['price_min']) ? sanitize_text_field(wp_unslash($_GET['price_min'])) : '';
$price_max = isset($_GET['price_max']) ? sanitize_text_field(wp_unslash($_GET['price_max'])) : '';

$operation_terms = get_terms(array('taxonomy' => 'impress_operation', 'hide_empty' => false));
$city_terms = get_terms(array('taxonomy' => 'impress_city', 'hide_empty' => false));
$type_terms = get_terms(array('taxonomy' => 'impress_property_type', 'hide_empty' => false));
if (is_wp_error($operation_terms)) {
    $operation_terms = array();
}
if (is_wp_error($city_terms)) {
    $city_terms = array();
}
if (is_wp_error($type_terms)) {
    $type_terms = array();
}
?>

<div class="crm-properties-wrapper">

    <!-- Filters Bar -->
    <form class="crm-filters-bar" method="get" action="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties')); ?>">

        <div class="crm-filter-search">
            <span class="dashicons dashicons-search"></span>
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Buscar por referencia o título...">
        </div>

        <div class="crm-filter-actions">
            <select class="crm-select" name="type">
                <option value="">Todos los tipos</option>
                <?php if (!empty($type_terms) && !is_wp_error($type_terms)): ?>
                    <?php foreach ($type_terms as $term): ?>
                        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($property_type, $term->slug); ?>><?php echo esc_html($term->name); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <select class="crm-select" name="operation">
                <option value="">Venta y alquiler</option>
                <?php if (!empty($operation_terms) && !is_wp_error($operation_terms)): ?>
                    <?php foreach ($operation_terms as $term): ?>
                        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($operation, $term->slug); ?>><?php echo esc_html($term->name); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <select class="crm-select" name="city">
                <option value="">Ubicación</option>
                <?php if (!empty($city_terms) && !is_wp_error($city_terms)): ?>
                    <?php foreach ($city_terms as $term): ?>
                        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($city, $term->slug); ?>><?php echo esc_html($term->name); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <input class="crm-select" type="number" name="price_min" min="0" placeholder="Precio mín"
                value="<?php echo esc_attr($price_min); ?>">
            <input class="crm-select" type="number" name="price_max" min="0" placeholder="Precio máx"
                value="<?php echo esc_attr($price_max); ?>">

            <button type="submit" class="btn-crm ghost small">Filtrar</button>
            <a class="btn-crm ghost small" href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties')); ?>">Limpiar filtros</a>
        </div>
    </form>

    <div class="crm-properties-layout">
        <!-- Tabla de propiedades -->
        <div class="crm-table-container crm-card">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Detalles</th>
                        <th class="table-hide-mobile">Características</th>
                        <th>Precio</th>
                        <th class="table-hide-mobile">Ubicación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query->have_posts()): ?>
                        <?php while ($query->have_posts()):
                            $query->the_post();
                            $id = get_the_ID();
                            $ref = get_field('referencia') ?: '#REF-' . $id;
                            $price = Inmopress_Shortcodes::get_price(); // Assuming helper exists/modified
                            $city = Inmopress_Shortcodes::get_city_terms();
                            $type = Inmopress_Shortcodes::get_type_terms(); // Need to implement or use term getter
                            $operation = Inmopress_Shortcodes::get_operation_terms();
                            $beds = get_field('dormitorios') ?: '-';
                            $baths = get_field('banos') ?: '-';
                            $area = get_field('superficie_construida') ?: get_field('superficie_util') ?: '-';
                            $owner = get_field('propietario');
                            $owner_id = 0;
                            if (is_object($owner) && isset($owner->ID)) {
                                $owner_id = (int) $owner->ID;
                            } elseif (is_numeric($owner)) {
                                $owner_id = (int) $owner;
                            }
                            $owner_link = $owner_id ? Inmopress_Shortcodes::panel_url('owners', array('edit' => $owner_id)) : '';
                            $leads_link = Inmopress_Shortcodes::panel_url('leads', array('property' => $id));
                            $select_link = add_query_arg('property', $id, Inmopress_Shortcodes::panel_url('properties'));
                            $is_selected = ($selected_property_id === $id);

                            // Image
                            $thumb = get_the_post_thumbnail_url($id, 'thumbnail');
                            if (!$thumb)
                                $thumb = 'https://via.placeholder.com/60'; // Fallback
                            ?>
                            <tr class="<?php echo $is_selected ? 'selected' : ''; ?>">
                                <!-- Image -->
                                <td class="crm-property-image-cell">
                                    <img src="<?php echo esc_url($thumb); ?>" alt="">
                                </td>

                                <!-- Details -->
                                <td class="crm-property-details">
                                    <?php $edit_link = Inmopress_Shortcodes::panel_url('properties', array('edit' => $id, 'property_id' => $id)); ?>
                                    <a href="<?php echo esc_url($edit_link); ?>">
                                        <div class="crm-property-ref">
                                            <?php echo esc_html($ref); ?>
                                        </div>
                                        <div class="crm-property-title">
                                            <?php the_title(); ?>
                                        </div>
                                    </a>
                                    <div class="crm-property-badges">
                                        <span class="badge badge-grey small"><?php echo esc_html($type); ?></span>
                                        <span class="badge badge-grey small"><?php echo esc_html($operation); ?></span>
                                    </div>
                                </td>

                                <!-- Specs -->
                                <td class="crm-property-specs table-hide-mobile">
                                    <div class="crm-property-spec-item" title="Dormitorios">
                                        <span class="dashicons dashicons-admin-home"></span>
                                        <?php echo esc_html($beds); ?>
                                    </div>
                                    <div class="crm-property-spec-item" title="Baños">
                                        <span class="dashicons dashicons-money-alt"></span>
                                        <?php echo esc_html($baths); ?>
                                    </div>
                                    <div class="crm-property-spec-item" title="Superficie">
                                        <span class="dashicons dashicons-image-rotate-right"></span>
                                        <?php echo esc_html($area); ?> m²
                                    </div>
                                </td>

                                <!-- Price -->
                                <td class="crm-property-price">
                                    <?php echo esc_html($price); ?>
                                </td>

                                <!-- Location -->
                                <td class="crm-property-location table-hide-mobile">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php echo esc_html($city); ?>
                                </td>

                                <!-- Status -->
                                <td class="crm-property-status">
                                    <?php
                                    // Logic for status colors
                                    $status_badge_class = 'badge-warning'; // Default yellow
                                    $status_text = 'Disponible'; // Default
                                    // We should get actual status term here if exists
                                    ?>
                                    <span class="badge <?php echo esc_attr($status_badge_class); ?> small">
                                        <?php echo esc_html($status_text); ?>
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="crm-property-actions">
                                    <div class="crm-property-actions-row">
                                        <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties', array('edit' => $id, 'property_id' => $id))); ?>" class="btn-icon" title="Editar">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                        <a href="<?php echo esc_url($leads_link); ?>" class="btn-icon" title="Ver prospectos">
                                            <span class="dashicons dashicons-megaphone"></span>
                                        </a>
                                        <?php if ($owner_link): ?>
                                            <a href="<?php echo esc_url($owner_link); ?>" class="btn-icon" title="Ver propietario">
                                                <span class="dashicons dashicons-id"></span>
                                            </a>
                                        <?php else: ?>
                                            <span class="btn-icon disabled" title="Sin propietario">
                                                <span class="dashicons dashicons-id"></span>
                                            </span>
                                        <?php endif; ?>
                                        <a href="<?php echo esc_url($select_link); ?>" class="btn-icon <?php echo $is_selected ? 'active' : ''; ?>" title="Ver acciones">
                                            <span class="dashicons dashicons-ellipsis"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="table-empty">No hay inmuebles registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Panel de acciones -->
        <div class="crm-card crm-actions-panel">
            <div class="crm-actions-panel-header">
                <div>
                    <div class="crm-actions-panel-label">Acciones</div>
                    <h3 class="crm-actions-panel-title">Acciones rápidas</h3>
                </div>
                <span class="dashicons dashicons-admin-generic"></span>
            </div>

            <?php if (!$selected_property): ?>
                <div class="crm-actions-panel-empty">
                    <p>Selecciona un inmueble</p>
                    <p>Pulsa el botón de acciones en una fila para ver las opciones.</p>
                </div>
            <?php else:
                $selected_ref = get_field('referencia', $selected_property_id) ?: '#REF-' . $selected_property_id;
                $selected_thumb = get_the_post_thumbnail_url($selected_property_id, 'thumbnail');
                if (!$selected_thumb) {
                    $selected_thumb = 'https://via.placeholder.com/60';
                }
                $kyero_enabled = (int) get_post_meta($selected_property_id, '_inmopress_kyero_feed', true);
                $featured_enabled = (int) get_post_meta($selected_property_id, '_inmopress_destacada', true);
                $action_nonce = wp_create_nonce('inmopress_property_action_' . $selected_property_id);
                $action_base = admin_url('admin-post.php');
                $action_args = array(
                    'action' => 'inmopress_property_action',
                    'property_id' => $selected_property_id,
                    '_wpnonce' => $action_nonce,
                );
                $action_kyero = add_query_arg(array_merge($action_args, array('action_type' => 'kyero')), $action_base);
                $action_featured = add_query_arg(array_merge($action_args, array('action_type' => 'featured')), $action_base);
                $action_draft = add_query_arg(array_merge($action_args, array('action_type' => 'draft')), $action_base);
                $action_delete = add_query_arg(array_merge($action_args, array('action_type' => 'delete')), $action_base);
                ?>
                <div class="crm-selected-item">
                    <img src="<?php echo esc_url($selected_thumb); ?>" alt="">
                    <div>
                        <div class="crm-selected-item-title"><?php echo esc_html($selected_ref); ?></div>
                        <div class="crm-selected-item-subtitle"><?php echo esc_html(get_the_title($selected_property_id)); ?></div>
                    </div>
                </div>

                <div class="crm-actions-list">
                    <a href="<?php echo esc_url($action_kyero); ?>" class="btn-crm secondary crm-action-item">
                        <span>
                            <span class="dashicons dashicons-rss"></span>
                            <?php echo $kyero_enabled ? 'Quitar de Kyero' : 'Marcar en Kyero'; ?>
                        </span>
                        <span class="crm-action-status"><?php echo $kyero_enabled ? 'Activo' : 'Inactivo'; ?></span>
                    </a>
                    <a href="<?php echo esc_url($action_featured); ?>" class="btn-crm secondary crm-action-item">
                        <span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <?php echo $featured_enabled ? 'Quitar destacada' : 'Pasar a destacada'; ?>
                        </span>
                        <span class="crm-action-status"><?php echo $featured_enabled ? 'Activo' : 'Inactivo'; ?></span>
                    </a>
                    <a href="<?php echo esc_url($action_draft); ?>" class="btn-crm secondary">
                        <span class="dashicons dashicons-media-text"></span>
                        Poner en borrador
                    </a>
                    <a href="<?php echo esc_url($action_delete); ?>" class="btn-crm danger"
                        onclick="return confirm('¿Seguro que quieres eliminar este inmueble?');">
                        <span class="dashicons dashicons-trash"></span>
                        Eliminar inmueble
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <div class="crm-pagination">
        <div>
            Rows per page: <strong>10</strong>
        </div>
        <div>
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'mid_size' => 1,
                'prev_text' => '<',
                'next_text' => '>',
            ));
            ?>
        </div>
    </div>

</div>
