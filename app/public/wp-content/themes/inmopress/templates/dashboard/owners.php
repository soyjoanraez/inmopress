<?php
/**
 * Owners Dashboard Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$dashboard = \Inmopress\CRM\Dashboard::get_instance();

// Get owners
$args = array(
    'post_type' => 'impress_owner',
    'posts_per_page' => 20,
    'post_status' => 'publish',
);

$owners = new \WP_Query($args);
?>

<div class="dashboard-section-header">
    <div class="section-header-left">
        <h3>Propietarios</h3>
        <p class="section-description">Gestiona todos los propietarios</p>
    </div>
    <div class="section-header-right">
        <input type="text" class="form-control table-search" placeholder="Buscar propietarios..." style="max-width: 300px; margin-right: 0.75rem;">
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=impress_owner')); ?>" class="btn btn-primary">
            <span class="dashicons dashicons-plus"></span>
            Nuevo Propietario
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
                <th>DNI</th>
                <th>Ubicación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($owners->have_posts()) : ?>
                <?php while ($owners->have_posts()) : $owners->the_post(); ?>
                    <?php
                    $nombre = get_field('nombre');
                    $apellidos = get_field('apellidos');
                    $correo = get_field('correo');
                    $telefono = get_field('telefono');
                    $dni = get_field('dni');
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html(trim($nombre . ' ' . $apellidos)); ?></strong>
                        </td>
                        <td><?php echo esc_html($correo ?: '-'); ?></td>
                        <td><?php echo esc_html($telefono ?: '-'); ?></td>
                        <td><?php echo esc_html($dni ?: '-'); ?></td>
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
                        <p>No hay propietarios disponibles.</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

