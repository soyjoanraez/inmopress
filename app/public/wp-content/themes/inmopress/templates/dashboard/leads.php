<?php
/**
 * Leads Dashboard Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();

// Get leads
$args = array(
    'post_type' => 'impress_lead',
    'posts_per_page' => 20,
    'post_status' => 'publish',
);

$leads = new \WP_Query($args);
?>

<div class="dashboard-section-header">
    <div class="section-header-left">
        <h3>Leads</h3>
        <p class="section-description">Gestiona todos tus leads</p>
    </div>
    <div class="section-header-right">
        <input type="text" class="form-control table-search" placeholder="Buscar leads..." style="max-width: 300px;">
    </div>
</div>

<div class="dashboard-table-container">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Canal</th>
                <th>Interés</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($leads->have_posts()) : ?>
                <?php while ($leads->have_posts()) : $leads->the_post(); ?>
                    <?php
                    $nombre = get_field('nombre');
                    $apellidos = get_field('apellidos');
                    $correo = get_field('correo');
                    $telefono = get_field('telefono');
                    $canal = get_field('canal');
                    $interes = get_field('interes');
                    $convertido = get_field('convertido_a_cliente');
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html(trim($nombre . ' ' . $apellidos)); ?></strong>
                        </td>
                        <td><?php echo esc_html($correo ?: '-'); ?></td>
                        <td><?php echo esc_html($telefono ?: '-'); ?></td>
                        <td>
                            <?php if ($canal) : ?>
                                <span class="badge badge-info"><?php echo esc_html(ucfirst($canal)); ?></span>
                            <?php else : ?>
                                <span>-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($interes) : ?>
                                <span class="badge badge-info"><?php echo esc_html(ucfirst($interes)); ?></span>
                            <?php else : ?>
                                <span>-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($convertido) : ?>
                                <span class="badge badge-success">Convertido</span>
                            <?php else : ?>
                                <span class="badge badge-warning">Pendiente</span>
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
                    <td colspan="7" class="empty-state">
                        <p>No hay leads disponibles.</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

