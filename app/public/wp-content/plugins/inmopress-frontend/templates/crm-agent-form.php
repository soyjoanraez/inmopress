<?php if (!defined('ABSPATH')) {
    exit;
}

$post_id = isset($_GET['edit']) ? intval($_GET['edit']) : (isset($_GET['agent_id']) ? intval($_GET['agent_id']) : 'new_post');

// Verificar permisos y validez del post
if ($post_id !== 'new_post') {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'impress_agent') {
        wp_die(esc_html__('El agente no existe.', 'inmopress'));
    }
    if (!current_user_can('edit_post', $post_id)) {
        wp_die(esc_html__('No tienes permisos para editar este agente.', 'inmopress'));
    }
} else {
    // Verificar permisos para crear nuevos posts
    if (!current_user_can('edit_posts')) {
        wp_die(esc_html__('No tienes permisos para crear agentes.', 'inmopress'));
    }
}

// Obtener grupos ACF una sola vez
$acf_groups = acf_get_field_groups(array('post_type' => 'impress_agent'));
$acf_groups_by_key = array();
foreach ($acf_groups as $group) {
    $acf_groups_by_key[$group['key']] = $group;
}

if (!function_exists('inmopress_render_acf_group')) {
    /**
     * Renderiza los campos de un grupo ACF
     *
     * @param string $group_key Clave del grupo ACF
     * @param int|string $post_id ID del post o 'new_post'
     * @param array $groups_by_key Array de grupos indexados por key
     * @return void
     */
    function inmopress_render_acf_group($group_key, $post_id, $groups_by_key)
    {
        $fields = acf_get_fields($group_key);
        if (!$fields) {
            return;
        }

        // Renderiza campos con valores cargados correctamente
        acf_render_fields($fields, $post_id);
    }
}

// Obtener estado real del post
$post_status = ($post_id !== 'new_post') ? get_post_status($post_id) : 'draft';
$status_labels = array(
    'publish' => array('label' => __('Activo', 'inmopress'), 'bg' => '#ECFDF5', 'color' => '#10B981'),
    'draft' => array('label' => __('Inactivo', 'inmopress'), 'bg' => '#E5E7EB', 'color' => '#6B7280'),
    'pending' => array('label' => __('Pendiente', 'inmopress'), 'bg' => '#FEF3C7', 'color' => '#D97706'),
);
$current_status = isset($status_labels[$post_status]) ? $status_labels[$post_status] : $status_labels['draft'];

// Obtener nombre completo para el título
$nombre = ($post_id !== 'new_post') ? get_field('nombre', $post_id) : '';
$apellidos = ($post_id !== 'new_post') ? get_field('apellidos', $post_id) : '';
$full_name = trim($nombre . ' ' . $apellidos);
$display_title = $full_name ?: ($post_id !== 'new_post' ? get_the_title($post_id) : '');

// Usamos envío ACF front-end, pero con layout personalizado.
// Es necesario acf_form_head() antes de renderizar el form para validar y guardar.

?>

<div class="crm-editor-wrapper">

    <form action="" method="post" class="crm-editor-form" enctype="multipart/form-data">
        <?php
        // Nonce de seguridad propio
        wp_nonce_field('inmopress_agent_form', 'inmopress_agent_nonce');

        // Essential ACF hidden fields (nonces, form id, etc)
        $acf_form_args = array(
            'post_id'      => $post_id,
            'post_title'   => false,
            'post_content' => false,
            'uploader'     => 'wp',
            'return'       => Inmopress_Shortcodes::panel_url('agents'),
            'new_post'     => array(
                'post_type'   => 'impress_agent',
                'post_status' => 'draft',
            ),
        );
        acf_form_data(array(
            'screen'  => 'acf_form',
            'post_id' => $post_id,
            'form'    => acf_encrypt(json_encode($acf_form_args)),
        ));
        ?>

        <!-- Header -->
        <div class="crm-editor-header">
            <div class="header-left">
                <div class="crm-breadcrumbs">
                    <?php esc_html_e('Agentes', 'inmopress'); ?>
                    <span>›</span>
                    <?php echo ($post_id !== 'new_post') ? esc_html__('Editar Agente', 'inmopress') : esc_html__('Nuevo Agente', 'inmopress'); ?>
                </div>
                <div class="crm-title-input-wrapper">
                    <input type="text" name="acf[_post_title]" id="post_title"
                        value="<?php echo esc_attr($post_id !== 'new_post' ? get_the_title($post_id) : ''); ?>"
                        placeholder="<?php esc_attr_e('Nombre del agente (se genera automáticamente)', 'inmopress'); ?>" readonly>
                    <?php
                    $status_badge_class = 'badge-grey';
                    if ($post_status === 'publish') $status_badge_class = 'badge-success';
                    elseif ($post_status === 'pending') $status_badge_class = 'badge-warning';
                    ?>
                    <span class="badge <?php echo esc_attr($status_badge_class); ?> small">
                        <?php echo esc_html($current_status['label']); ?>
                    </span>
                </div>
            </div>

            <div class="header-actions">
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('agents')); ?>" class="btn-crm secondary">
                    <?php esc_html_e('Cancelar', 'inmopress'); ?>
                </a>
                <button type="submit" class="btn-crm primary">
                    <span class="dashicons dashicons-saved"></span>
                    <?php esc_html_e('Guardar cambios', 'inmopress'); ?>
                </button>
            </div>
        </div>

        <!-- Grid Layout -->
        <div class="crm-editor-grid">

            <!-- Left Column: Main Content -->
            <div class="crm-col-main">

                <!-- Vinculación y Datos -->
                <div class="crm-card">
                    <div class="crm-card-header">
                        <h3><?php esc_html_e('Vinculación y Datos', 'inmopress'); ?></h3>
                    </div>
                    <div class="crm-basic-info-grid">
                        <?php inmopress_render_acf_group('group_agent_info', $post_id, $acf_groups_by_key); ?>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sidebar -->
            <div class="crm-col-sidebar">

                <!-- Avatar -->
                <?php
                $avatar_id = ($post_id !== 'new_post') ? get_field('avatar', $post_id) : null;
                $avatar_url = '';
                if ($avatar_id) {
                    if (is_array($avatar_id)) {
                        $avatar_url = isset($avatar_id['url']) ? $avatar_id['url'] : '';
                    } else {
                        $avatar_url = wp_get_attachment_image_url($avatar_id, 'medium');
                    }
                }
                ?>
                <div class="crm-card">
                    <h3><?php esc_html_e('Avatar', 'inmopress'); ?></h3>
                    <div class="crm-featured-image-wrapper">
                        <div class="crm-featured-image-preview">
                            <?php if ($avatar_url): ?>
                                <img id="inmopress-avatar-preview" src="<?php echo esc_url($avatar_url); ?>" alt="" style="border-radius: 50%; width: 150px; height: 150px; object-fit: cover;">
                                <div id="inmopress-avatar-placeholder" class="crm-featured-image-placeholder" style="display: none;"><?php esc_html_e('Sin imagen', 'inmopress'); ?></div>
                            <?php else: ?>
                                <img id="inmopress-avatar-preview" src="" alt="" style="display: none; border-radius: 50%; width: 150px; height: 150px; object-fit: cover;">
                                <div id="inmopress-avatar-placeholder" class="crm-featured-image-placeholder"><?php esc_html_e('Sin imagen', 'inmopress'); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php inmopress_render_acf_group('group_agent_profile', $post_id, $acf_groups_by_key); ?>
                    </div>
                </div>

                <!-- Perfil Público -->
                <div class="crm-card">
                    <h3><?php esc_html_e('Perfil Público', 'inmopress'); ?></h3>
                    <?php 
                    // Renderizar solo los campos de activo y color_calendario (avatar ya está arriba)
                    if (isset($acf_groups_by_key['group_agent_profile'])) {
                        $profile_fields = acf_get_fields('group_agent_profile');
                        if ($profile_fields) {
                            foreach ($profile_fields as $field) {
                                // Saltar el campo avatar ya que lo renderizamos arriba
                                if ($field['name'] === 'avatar') {
                                    continue;
                                }
                                acf_render_field($field, $post_id);
                            }
                        }
                    }
                    ?>
                </div>

                <!-- Especialización -->
                <?php
                $specialty_terms = get_terms(array('taxonomy' => 'impress_agent_specialty', 'hide_empty' => false));
                $selected_specialties = ($post_id !== 'new_post') ? wp_get_post_terms($post_id, 'impress_agent_specialty', array('fields' => 'ids')) : array();
                ?>
                <div class="crm-card">
                    <h3><?php esc_html_e('Especialización', 'inmopress'); ?></h3>
                    <div class="crm-field-group crm-taxonomy-field">
                        <label><?php esc_html_e('Especialidades', 'inmopress'); ?></label>
                        <div class="crm-taxonomy-checkboxes">
                            <?php if (!empty($specialty_terms) && !is_wp_error($specialty_terms)): ?>
                                <?php foreach ($specialty_terms as $term): ?>
                                    <?php $is_checked = in_array($term->term_id, $selected_specialties, true); ?>
                                    <label class="crm-taxonomy-checkbox-label">
                                        <input type="checkbox" name="inmopress_tax[impress_agent_specialty][]" value="<?php echo esc_attr($term->term_id); ?>" <?php checked($is_checked); ?>>
                                        <?php echo esc_html($term->name); ?>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Hidden submit button triggered by the header Save button -->
        <input type="submit" value="Guardar cambios" class="hidden">

    </form>
</div>

<script>
    jQuery(function ($) {
        // Auto-generar título cuando se cambian nombre o apellidos
        function updateTitle() {
            var nombre = $('input[name="acf[field_agent_nombre]"]').val() || '';
            var apellidos = $('input[name="acf[field_agent_apellidos]"]').val() || '';
            var fullName = (nombre + ' ' + apellidos).trim();
            if (fullName) {
                $('#post_title').val(fullName);
            }
        }

        $('input[name="acf[field_agent_nombre]"], input[name="acf[field_agent_apellidos]"]').on('input', updateTitle);

        // Actualizar preview de avatar cuando cambia el campo ACF
        $(document).on('acf/sync/field', function(e, field) {
            if (field.attr('data-name') === 'avatar') {
                var attachment = field.val();
                if (attachment && attachment.url) {
                    $('#inmopress-avatar-preview').attr('src', attachment.url).show();
                    $('#inmopress-avatar-placeholder').hide();
                } else {
                    $('#inmopress-avatar-preview').hide();
                    $('#inmopress-avatar-placeholder').show();
                }
            }
        });
    });
</script>
