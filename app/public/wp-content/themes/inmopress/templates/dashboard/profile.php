<?php
/**
 * Profile Dashboard Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
?>

<div class="dashboard-section-header">
    <div class="section-header-left">
        <h3>Mi Perfil</h3>
        <p class="section-description">Gestiona tu información personal</p>
    </div>
</div>

<div class="dashboard-profile">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo get_avatar($user->ID, 120); ?>
            </div>
            <div class="profile-info">
                <h2><?php echo esc_html($user->display_name); ?></h2>
                <p class="profile-email"><?php echo esc_html($user->user_email); ?></p>
                <p class="profile-role">
                    <?php
                    $role_names = array(
                        'administrator' => 'Administrador',
                        'agency' => 'Agencia',
                        'agent' => 'Agente',
                        'trabajador' => 'Trabajador',
                        'cliente' => 'Cliente',
                    );
                    $user_role = $user->roles[0] ?? 'cliente';
                    echo esc_html($role_names[$user_role] ?? ucfirst($user_role));
                    ?>
                </p>
            </div>
        </div>

        <div class="profile-form">
            <form method="post" action="<?php echo esc_url(admin_url('profile.php')); ?>">
                <div class="form-group">
                    <label for="display_name">Nombre para mostrar</label>
                    <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr($user->display_name); ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="user_email">Email</label>
                    <input type="email" id="user_email" name="user_email" value="<?php echo esc_attr($user->user_email); ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="user_url">Sitio web</label>
                    <input type="url" id="user_url" name="user_url" value="<?php echo esc_attr($user->user_url); ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="description">Biografía</label>
                    <textarea id="description" name="description" rows="4" class="form-control"><?php echo esc_textarea($user->description); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="<?php echo esc_url(admin_url('profile.php')); ?>" class="btn btn-secondary">Editar en Admin</a>
                </div>
            </form>
        </div>
    </div>
</div>

