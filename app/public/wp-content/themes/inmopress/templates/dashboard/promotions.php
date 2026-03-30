<?php
/**
 * Promotions Dashboard Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();

// Get promotions
$args = array(
    'post_type' => 'impress_promotion',
    'posts_per_page' => 20,
    'post_status' => 'publish',
);

$promotions = new \WP_Query($args);
?>

<div class="dashboard-section-header">
    <div class="section-header-left">
        <h3>Promociones</h3>
        <p class="section-description">Gestiona todas las promociones</p>
    </div>
    <div class="section-header-right">
        <input type="text" class="form-control table-search" placeholder="Buscar promociones..." style="max-width: 300px; margin-right: 0.75rem;">
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=impress_promotion')); ?>" class="btn btn-primary">
            <span class="dashicons dashicons-plus"></span>
            Nueva Promoción
        </a>
    </div>
</div>

<div class="dashboard-table-container">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Inmuebles</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($promotions->have_posts()) : ?>
                <?php while ($promotions->have_posts()) : $promotions->the_post(); ?>
                    <?php
                    $fecha_inicio = get_field('fecha_inicio');
                    $fecha_fin = get_field('fecha_fin');
                    $inmuebles = get_field('inmuebles');
                    $now = current_time('Y-m-d');
                    $status = 'activa';
                    if ($fecha_fin && $fecha_fin < $now) {
                        $status = 'finalizada';
                    } elseif ($fecha_inicio && $fecha_inicio > $now) {
                        $status = 'programada';
                    }
                    ?>
                    <tr>
                        <td>
                            <strong><?php the_title(); ?></strong>
                        </td>
                        <td>
                            <?php
                            if ($fecha_inicio) {
                                echo esc_html(date_i18n('d/m/Y', strtotime($fecha_inicio)));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($fecha_fin) {
                                echo esc_html(date_i18n('d/m/Y', strtotime($fecha_fin)));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($inmuebles && is_array($inmuebles)) {
                                echo esc_html(count($inmuebles));
                            } else {
                                echo '0';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $badge_class = 'badge-info';
                            if ($status === 'finalizada') {
                                $badge_class = 'badge-secondary';
                            } elseif ($status === 'programada') {
                                $badge_class = 'badge-warning';
                            }
                            ?>
                            <span class="badge <?php echo esc_attr($badge_class); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
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
                        <p>No hay promociones disponibles.</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

