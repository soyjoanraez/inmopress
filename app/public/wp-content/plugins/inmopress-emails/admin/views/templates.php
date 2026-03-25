<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'inmopress_email_templates';
$templates = $wpdb->get_results("SELECT * FROM {$table} ORDER BY category, name");
?>
<div class="wrap">
    <h1>Plantillas de Email</h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Slug</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($templates)): ?>
                <tr>
                    <td colspan="5">No hay plantillas. Se crearán automáticamente al activar el plugin.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($templates as $template): ?>
                    <tr>
                        <td><strong><?php echo esc_html($template->name); ?></strong></td>
                        <td><code><?php echo esc_html($template->slug); ?></code></td>
                        <td><?php echo esc_html(ucfirst($template->category)); ?></td>
                        <td><?php echo $template->is_active ? '✅ Activa' : '❌ Inactiva'; ?></td>
                        <td>
                            <a href="<?php echo admin_url('post.php?post=' . $template->id . '&action=edit'); ?>">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
