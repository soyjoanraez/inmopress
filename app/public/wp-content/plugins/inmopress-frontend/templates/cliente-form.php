<?php if (!defined('ABSPATH')) exit;
// Variables: $post_id, $return_url
$current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'clients';
$is_leads_tab = ($current_tab === 'leads');
$section_label = $is_leads_tab ? 'Prospecto' : 'Cliente';
$post_type = $is_leads_tab ? 'impress_lead' : 'impress_client';
$field_groups = $is_leads_tab
    ? array(
        'group_lead_personal',
        'group_lead_status',
        'group_lead_preferences',
        'group_lead_management',
        'group_lead_config',
    )
    : array(
        'group_client_personal',
        'group_client_status',
        'group_client_preferences',
        'group_client_management',
        'group_client_config',
    );
?>
<div class="inmopress-cliente-form crm-editor-wrapper">
    <div class="crm-editor-header">
        <div class="header-left">
            <div class="crm-breadcrumbs">
                <?php echo esc_html($is_leads_tab ? 'Prospectos' : 'Clientes'); ?>
                <span>›</span>
                <?php echo $post_id === 'new_post' ? 'Nuevo ' . esc_html($section_label) : 'Editar ' . esc_html($section_label); ?>
            </div>
            <h1 style="margin: 0;">
                <?php echo $post_id === 'new_post' ? 'Nuevo ' . esc_html($section_label) : 'Editar ' . esc_html($section_label); ?>
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
            $post_type,
            $return_url,
            $field_groups
        );
        acf_form($form_args);
        ?>
    </div>
</div>
