<?php
/**
 * Agencies Dashboard Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();

// Get agencies
$args = array(
    'post_type' => 'impress_agency',
    'posts_per_page' => 20,
    'post_status' => 'publish',
);

$agencies = new \WP_Query($args);
?>

<div class="dashboard-section-header">
    <div class="section-header-left">
        <h3>Agencias</h3>
        <p class="section-description">Gestiona todas las agencias</p>
    </div>
    <div class="section-header-right">
        <input type="text" class="form-control table-search" placeholder="Buscar agencias..." style="max-width: 300px; margin-right: 0.75rem;">
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=impress_agency')); ?>" class="btn btn-primary">
            <span class="dashicons dashicons-plus"></span>
            Nueva Agencia
        </a>
    </div>
</div>

<div class="dashboard-table-container">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>Nombre Agencia</th>
                <th>Contacto</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Ubicación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($agencies->have_posts()) : ?>
                <?php while ($agencies->have_posts()) : $agencies->the_post(); ?>
                    <?php
                    $nombre_agencia = get_field('nombre_agencia');
                    $nombre = get_field('nombre');
                    $apellidos = get_field('apellidos');
                    $correo_agencia = get_field('correo_agencia');
                    $telefono_agencia = get_field('telefono_agencia');
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($nombre_agencia ?: get_the_title()); ?></strong>
                        </td>
                        <td><?php echo esc_html(trim($nombre . ' ' . $apellidos)); ?></td>
                        <td><?php echo esc_html($correo_agencia ?: '-'); ?></td>
                        <td><?php echo esc_html($telefono_agencia ?: '-'); ?></td>
                        <td>
                            <?php
                            $ciudad = get_field('ciudad');
                            $municipio = get_field('municipio');
                            echo esc_html(($municipio ? $municipio->name . ', ' : '') . ($ciudad ? $ciudad->name : ''));
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
                    <td colspan="6" class="empty-state">
                        <p>No hay agencias disponibles.</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

