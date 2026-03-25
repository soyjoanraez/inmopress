<?php
/**
 * Visits Dashboard Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();

// Get visits
$args = array(
    'post_type' => 'impress_visit',
    'posts_per_page' => 20,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
);

$visits = new \WP_Query($args);
?>

<div class="dashboard-section-header">
    <div class="section-header-left">
        <h3>Visitas</h3>
        <p class="section-description">Gestiona todas las visitas</p>
    </div>
    <div class="section-header-right">
        <input type="text" class="form-control table-search" placeholder="Buscar visitas..." style="max-width: 300px; margin-right: 0.75rem;">
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=impress_visit')); ?>" class="btn btn-primary">
            <span class="dashicons dashicons-plus"></span>
            Nueva Visita
        </a>
    </div>
</div>

<div class="dashboard-table-container">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Inmueble</th>
                <th>Agente</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($visits->have_posts()) : ?>
                <?php while ($visits->have_posts()) : $visits->the_post(); ?>
                    <?php
                    $fecha_hora = get_field('fecha_hora');
                    $cliente = get_field('cliente');
                    $inmueble = get_field('inmueble');
                    $agente = get_field('agente');
                    $estado = get_field('estado');
                    ?>
                    <tr>
                        <td>
                            <?php
                            if ($fecha_hora) {
                                echo esc_html(date_i18n('d/m/Y H:i', strtotime($fecha_hora)));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($cliente) {
                                echo esc_html($cliente->post_title);
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($inmueble) {
                                echo esc_html($inmueble->post_title);
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($agente) {
                                echo esc_html($agente->post_title);
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($estado) : ?>
                                <span class="badge badge-info"><?php echo esc_html(ucfirst($estado)); ?></span>
                            <?php else : ?>
                                <span>-</span>
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
                        <p>No hay visitas disponibles.</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

