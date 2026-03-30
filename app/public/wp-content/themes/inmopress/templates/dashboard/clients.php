<?php
/**
 * Clients Dashboard Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();
$user = wp_get_current_user();
$user_role = $user->roles[0] ?? 'cliente';

// Get clients
$args = array(
    'post_type' => 'impress_client',
    'posts_per_page' => 20,
    'post_status' => 'publish',
);

$clients = new \WP_Query($args);
?>

<div class="dashboard-section-header">
    <div class="section-header-left">
        <h3>Clientes</h3>
        <p class="section-description">Gestiona todos tus clientes</p>
    </div>
    <div class="section-header-right">
        <input type="text" class="form-control table-search" placeholder="Buscar clientes..." style="max-width: 300px; margin-right: 0.75rem;">
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=impress_client')); ?>" class="btn btn-primary">
            <span class="dashicons dashicons-plus"></span>
            Nuevo Cliente
        </a>
    </div>
</div>

<div class="dashboard-table-container">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Interés</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($clients->have_posts()) : ?>
                <?php while ($clients->have_posts()) : $clients->the_post(); ?>
                    <?php
                    $nombre = get_field('nombre');
                    $apellidos = get_field('apellidos');
                    $correo = get_field('correo');
                    $telefono = get_field('telefono');
                    $estado_lead = get_field('estado_lead');
                    $semaforo = get_field('semaforo_estado');
                    $interes = get_field('interes');
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html(trim($nombre . ' ' . $apellidos)); ?></strong>
                        </td>
                        <td><?php echo esc_html($correo ?: '-'); ?></td>
                        <td><?php echo esc_html($telefono ?: '-'); ?></td>
                        <td>
                            <?php if ($semaforo) : ?>
                                <span class="badge badge-<?php echo esc_attr($semaforo); ?>">
                                    <?php echo esc_html(strtoupper($semaforo)); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($estado_lead) : ?>
                                <span class="badge badge-secondary"><?php echo esc_html(ucfirst($estado_lead)); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($interes) : ?>
                                <span class="badge badge-info"><?php echo esc_html(ucfirst($interes)); ?></span>
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
                        <p>No hay clientes disponibles.</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

