<?php
/**
 * Agents Dashboard Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();

// Get agents
$args = array(
    'post_type' => 'impress_agent',
    'posts_per_page' => 20,
    'post_status' => 'publish',
);

$agents = new \WP_Query($args);
?>

<div class="dashboard-section-header">
    <div class="section-header-left">
        <h3>Agentes</h3>
        <p class="section-description">Gestiona todos los agentes</p>
    </div>
    <div class="section-header-right">
        <input type="text" class="form-control table-search" placeholder="Buscar agentes..." style="max-width: 300px; margin-right: 0.75rem;">
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=impress_agent')); ?>" class="btn btn-primary">
            <span class="dashicons dashicons-plus"></span>
            Nuevo Agente
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
                <th>Agencia</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($agents->have_posts()) : ?>
                <?php while ($agents->have_posts()) : $agents->the_post(); ?>
                    <?php
                    $nombre = get_field('nombre');
                    $apellidos = get_field('apellidos');
                    $email = get_field('email');
                    $telefono = get_field('telefono');
                    $agencia = get_field('agencia');
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html(trim($nombre . ' ' . $apellidos)); ?></strong>
                        </td>
                        <td><?php echo esc_html($email ?: '-'); ?></td>
                        <td><?php echo esc_html($telefono ?: '-'); ?></td>
                        <td>
                            <?php
                            if ($agencia) {
                                echo esc_html($agencia->post_title);
                            } else {
                                echo '-';
                            }
                            ?>
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
                    <td colspan="5" class="empty-state">
                        <p>No hay agentes disponibles.</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

