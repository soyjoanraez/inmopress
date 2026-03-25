<?php if (!defined('ABSPATH')) exit;
// Variables: $post_id, $return_url
?>
<div class="inmopress-visita-form crm-editor-wrapper">
    <div class="crm-editor-header">
        <div class="header-left">
            <div class="crm-breadcrumbs">
                Visitas
                <span>›</span>
                <?php echo $post_id === 'new_post' ? 'Nueva Visita' : 'Editar Visita'; ?>
            </div>
            <h1 style="margin: 0;">
                <?php echo $post_id === 'new_post' ? 'Nueva Visita' : 'Editar Visita'; ?>
            </h1>
        </div>
        <div class="header-actions">
            <a href="<?php echo esc_url($return_url); ?>" class="btn-crm secondary">Cancelar</a>
        </div>
    </div>

    <div class="crm-card">
        <?php
        $form_args = Inmopress_ACF_Forms::get_form_args(
            $post_id,
            'impress_visit',
            $return_url,
            array('group_visit_info')
        );
        acf_form($form_args);
        ?>
    </div>
</div>
