<?php if (!defined('ABSPATH'))
    exit; ?>
<div class="inmopress-dashboard">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Mis Inmuebles</h1>
        <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties', array('new' => 1))); ?>" class="button button-primary">＋ Nuevo Inmueble</a>
    </div>

    <!-- Filters -->
    <form method="GET" class="filters-bar">
        <input type="text" name="s" placeholder="Buscar por referencia o nombre..." value="<?php echo esc_attr($search); ?>">

        <select name="operation">
            <option value="">Todas las Operaciones</option>
            <option value="venta" <?php selected($operation, 'venta'); ?>>Venta</option>
            <option value="alquiler" <?php selected($operation, 'alquiler'); ?>>Alquiler</option>
        </select>

        <button type="submit">Filtrar</button>
        <?php if (!empty($search) || !empty($operation)): ?>
            <a href="<?php echo get_permalink(); ?>" class="button">Limpiar</a>
        <?php endif; ?>
    </form>

    <?php if ($query->have_posts()): ?>
        <table class="inmopress-table">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Título / Ref</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Visitas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($query->have_posts()):
                    $query->the_post();
                    $post_id = get_the_ID();
                    $ref = get_field('referencia');
                    $precio_venta = get_field('precio_venta');
                    $precio_alquiler = get_field('precio_alquiler');
                    $estado = get_field('listing_status') ?: 'active';
                    $estado_labels = array(
                        'active' => 'Activo',
                        'pending' => 'Reservado',
                        'sold' => 'Vendido',
                        'rented' => 'Alquilado',
                        'off_market' => 'Retirado',
                    );
                    $estado_label = isset($estado_labels[$estado]) ? $estado_labels[$estado] : ucfirst($estado);
                    ?>
                    <tr>
                        <td width="80">
                            <?php if (has_post_thumbnail()): ?>
                                <?php the_post_thumbnail('thumbnail', array('style' => 'width: 60px; height: 60px; object-fit: cover; border-radius: 6px;')); ?>
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; background: #eee; border-radius: 6px;"></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php the_title(); ?></strong><br>
                            <span style="color: #666; font-size: 0.85em;">Ref: <?php echo esc_html($ref); ?></span>
                        </td>
                        <td>
                            <?php
                            if ($precio_venta)
                                echo number_format($precio_venta, 0, ',', '.') . ' €';
                            elseif ($precio_alquiler)
                                echo number_format($precio_alquiler, 0, ',', '.') . ' €/mes';
                            else
                                echo '-';
                            ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo esc_attr($estado); ?>">
                                <?php echo esc_html($estado_label); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            // Quick count of visits
                            $visit_count = count(get_posts(array(
                                'post_type' => 'impress_visit',
                                'meta_key' => 'inmueble',
                                'meta_value' => $post_id,
                                'fields' => 'ids'
                            )));
                            echo $visit_count;
                            ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties', array('edit' => $post_id, 'property_id' => $post_id))); ?>"
                                    class="button" style="padding: 5px 10px; font-size: 0.8em;" title="Editar">✏️</a>
                                <a href="<?php the_permalink(); ?>" class="button" style="padding: 5px 10px; font-size: 0.8em;"
                                    target="_blank" title="Ver">👁️</a>
                                <a href="<?php echo esc_url(home_url('/print-property/?id=' . $post_id)); ?>" class="button"
                                    style="padding: 5px 10px; font-size: 0.8em;" target="_blank" title="Imprimir">🖨️</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile;
                wp_reset_postdata(); ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination" style="margin-top: 20px; text-align: center;">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'mid_size' => 1,
                'prev_text' => '«',
                'next_text' => '»',
            ));
            ?>
        </div>

    <?php else: ?>
        <p>No se encontraron inmuebles.</p>
    <?php endif; ?>
</div>
