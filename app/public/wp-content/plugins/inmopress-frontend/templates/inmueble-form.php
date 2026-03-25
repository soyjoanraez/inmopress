<?php if (!defined('ABSPATH'))
    exit;
// Variables: $post_id, $return_url, $field_groups
?>
<div class="inmopress-dashboard crm-editor-wrapper">
    <div class="crm-editor-header">
        <div class="header-left">
            <div class="crm-breadcrumbs">
                Inmuebles
                <span>›</span>
                <?php echo $post_id === 'new_post' ? 'Nuevo Inmueble' : 'Editar Inmueble'; ?>
            </div>
            <h1 style="margin: 0;">
                <?php echo $post_id === 'new_post' ? 'Nuevo Inmueble' : 'Editar Inmueble'; ?>
            </h1>
        </div>
        <div class="header-actions">
            <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties')); ?>" class="btn-crm secondary">Cancelar</a>
        </div>
    </div>

    <div class="crm-card acf-form">
        <?php
        $form_args = Inmopress_ACF_Forms::get_form_args(
            $post_id,
            'impress_property',
            $return_url,
            $field_groups
        );
        acf_form($form_args);
        ?>
    </div>
</div>
