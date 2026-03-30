<?php if (!defined('ABSPATH')) {
    exit;
}
// Variables: $post_id, $return_url, $field_groups
?>
<div class="crm-owners-form crm-editor-wrapper">
    <div class="crm-editor-header">
        <div class="header-left">
            <div class="crm-breadcrumbs">
                Propietarios
                <span>›</span>
                <?php echo $post_id === 'new_post' ? 'Nuevo Propietario' : 'Editar Propietario'; ?>
            </div>
            <h1 style="margin: var(--spacing-xs) 0 0;">
                <?php echo $post_id === 'new_post' ? 'Nuevo Propietario' : 'Editar Propietario'; ?>
            </h1>
            <p class="crm-clients-subtitle" style="margin-top: var(--spacing-xs);">Gestiona los datos del propietario y vincula sus inmuebles.</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('owners')); ?>" class="btn-crm secondary">Cancelar</a>
        </div>
    </div>

    <div class="crm-card">
        <?php
        $form_args = Inmopress_ACF_Forms::get_form_args(
            $post_id,
            'impress_owner',
            $return_url,
            $field_groups
        );
        acf_form($form_args);
        ?>
    </div>
</div>
