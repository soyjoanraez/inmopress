<?php if (!defined('ABSPATH')) exit;
// Variables: $post_id, $return_url
?>
<div class="inmopress-event-form crm-editor-wrapper">
    <div class="crm-editor-header">
        <div class="header-left">
            <div class="crm-breadcrumbs">
                Eventos
                <span>›</span>
                <?php echo $post_id === 'new_post' ? 'Nuevo Evento' : 'Editar Evento'; ?>
            </div>
            <h1 style="margin: 0;">
                <?php echo $post_id === 'new_post' ? 'Nuevo Evento' : 'Editar Evento'; ?>
            </h1>
        </div>
        <div class="header-actions">
            <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('events')); ?>" class="btn-crm secondary">Cancelar</a>
        </div>
    </div>

    <div class="crm-card">
        <?php
        $form_args = Inmopress_ACF_Forms::get_form_args(
            $post_id,
            'impress_event',
            $return_url,
            array('group_event_info')
        );
        acf_form($form_args);
        ?>
    </div>
</div>
