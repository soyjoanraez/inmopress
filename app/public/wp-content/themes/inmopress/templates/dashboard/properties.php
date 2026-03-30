<?php
/**
 * Properties Dashboard Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();
$action = get_query_var('inmopress_action') ?: 'list';
$user = wp_get_current_user();
$user_role = $user->roles[0] ?? 'cliente';

// Get properties
$args = array(
    'post_type' => 'impress_property',
    'posts_per_page' => 20,
    'post_status' => 'publish',
);

// Filter by user role
if ($user_role === 'agent') {
    // Get agent's properties
    $agent_posts = get_posts(array(
        'post_type' => 'impress_agent',
        'meta_query' => array(
            array(
                'key' => 'usuario_wp',
                'value' => $user->ID,
                'compare' => '=',
            ),
        ),
    ));
    if (!empty($agent_posts)) {
        $args['meta_query'] = array(
            array(
                'key' => 'agente',
                'value' => $agent_posts[0]->ID,
                'compare' => '=',
            ),
        );
    }
}

$properties = new \WP_Query($args);
?>

<div class="dashboard-section-header">
    <div class="section-header-left">
        <h3>Inmuebles</h3>
        <p class="section-description">Gestiona todos tus inmuebles</p>
    </div>
    <?php if (in_array($user_role, array('administrator', 'agency', 'agent', 'trabajador'), true)) : ?>
        <div class="section-header-right">
            <input type="text" class="form-control table-search" placeholder="Buscar inmuebles..." style="max-width: 300px; margin-right: 0.75rem;">
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=impress_property')); ?>" class="btn btn-primary">
                <span class="dashicons dashicons-plus"></span>
                Nuevo Inmueble
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-table-container">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>Referencia</th>
                <th>Título</th>
                <th>Ubicación</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($properties->have_posts()) : ?>
                <?php while ($properties->have_posts()) : $properties->the_post(); ?>
                    <?php
                    $referencia = get_field('referencia');
                    $direccion = get_field('direccion');
                    $precio_venta = get_field('precio_venta');
                    $precio_alquiler = get_field('precio_alquiler');
                    $publicada = get_field('publicada');
                    $vendida = get_field('vendida');
                    $reservada = get_field('reservada');
                    ?>
                    <tr>
                        <td><?php echo esc_html($referencia ?: '-'); ?></td>
                        <td>
                            <strong><?php the_title(); ?></strong>
                        </td>
                        <td><?php echo esc_html($direccion ?: '-'); ?></td>
                        <td>
                            <?php
                            if ($precio_venta) {
                                echo esc_html(number_format($precio_venta, 0, ',', '.') . ' €');
                            } elseif ($precio_alquiler) {
                                echo esc_html(number_format($precio_alquiler, 0, ',', '.') . ' €/mes');
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($vendida) : ?>
                                <span class="badge badge-danger">Vendida</span>
                            <?php elseif ($reservada) : ?>
                                <span class="badge badge-warning">Reservada</span>
                            <?php elseif ($publicada) : ?>
                                <span class="badge badge-success">Publicada</span>
                            <?php else : ?>
                                <span class="badge badge-secondary">Borrador</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="btn-icon" title="Ver">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            <?php if (current_user_can('edit_post', get_the_ID())) : ?>
                                <a href="<?php echo esc_url(get_edit_post_link()); ?>" class="btn-icon" title="Editar">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <tr>
                    <td colspan="6" class="empty-state">
                        <p>No hay inmuebles disponibles.</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

