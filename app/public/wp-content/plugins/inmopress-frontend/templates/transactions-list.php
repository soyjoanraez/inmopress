<?php if (!defined('ABSPATH'))
    exit; ?>
<div class="inmopress-transactions-list">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Transacciones</h2>
        <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('transactions', array('new' => 1))); ?>" class="button button-primary">Nueva
            Transacción</a>
    </div>

    <?php if ($query->have_posts()): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Propiedad</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Importe</th>
                    <th>Beneficio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($query->have_posts()):
                    $query->the_post();
                    $property = get_field('related_property');
                    $type = get_field('transaction_type');
                    $status = get_field('transaction_status');
                    $amount = get_field('amount');
                    $profit = get_field('profit_margin');

                    $type_label = $type === 'sale' ? 'Venta' : ($type === 'rental' ? 'Alquiler' : $type);
                    $status_colors = array(
                        'pending' => '#f59e0b',
                        'completed' => '#10b981',
                        'cancelled' => '#ef4444',
                    );
                    $status_label = $status === 'pending' ? 'Pendiente' : ($status === 'completed' ? 'Completada' : 'Cancelada');
                    $color = isset($status_colors[$status]) ? $status_colors[$status] : '#6b7280';
                    ?>
                    <tr>
                        <td>
                            <?php echo get_field('closing_date') ?: get_the_date('d/m/Y'); ?>
                        </td>
                        <td>
                            <?php if ($property): ?>
                                <a
                                    href="<?php echo user_can(get_current_user_id(), 'edit_post', $property->ID) ? esc_url(Inmopress_Shortcodes::panel_url('properties', array('edit' => $property->ID, 'property_id' => $property->ID))) : '#'; ?>">
                                    <?php echo esc_html($property->post_title); ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo esc_html($type_label); ?>
                        </td>
                        <td>
                            <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                <?php echo esc_html($status_label); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $amount ? number_format($amount, 0, ',', '.') . ' €' : '-'; ?>
                        </td>
                        <td>
                            <?php echo $profit ? number_format($profit, 0, ',', '.') . ' €' : '-'; ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('transactions', array('edit' => get_the_ID()))); ?>"
                                class="button button-small">Editar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => max(1, get_query_var('paged')),
            ));
            ?>
        </div>
        <?php wp_reset_postdata(); ?>
    <?php else: ?>
        <p>No hay transacciones registradas.</p>
    <?php endif; ?>
</div>
