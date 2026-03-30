<?php if (!defined('ABSPATH'))
    exit;
// Variables: $post_id, $return_url
?>
<div class="inmopress-transaction-form crm-editor-wrapper">
    <div class="crm-editor-header">
        <div class="header-left">
            <div class="crm-breadcrumbs">
                Transacciones
                <span>›</span>
                <?php echo $post_id === 'new_post' ? 'Nueva Transacción' : 'Editar Transacción'; ?>
            </div>
            <h1 style="margin: 0;">
                <?php echo $post_id === 'new_post' ? 'Nueva Transacción' : 'Editar Transacción'; ?>
            </h1>
        </div>
        <div class="header-actions">
            <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('transactions')); ?>" class="btn-crm secondary">Cancelar</a>
        </div>
    </div>

    <div class="crm-card acf-form-wrapper">
        <?php
        $form_args = Inmopress_ACF_Forms::get_form_args(
            $post_id,
            'impress_transaction',
            $return_url,
            array('group_transaction_details') // Ensure this key matches class-acf-fields.php
        );
        acf_form($form_args);
        ?>
    </div>
</div>
