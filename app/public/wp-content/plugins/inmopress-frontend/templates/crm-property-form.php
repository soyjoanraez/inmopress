<?php if (!defined('ABSPATH')) {
    exit;
}

$post_id = isset($_GET['edit']) ? intval($_GET['edit']) : (isset($_GET['property_id']) ? intval($_GET['property_id']) : 'new_post');

// Verificar permisos y validez del post
if ($post_id !== 'new_post') {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'impress_property') {
        wp_die(esc_html__('La propiedad no existe.', 'inmopress'));
    }
    if (!current_user_can('edit_post', $post_id)) {
        wp_die(esc_html__('No tienes permisos para editar esta propiedad.', 'inmopress'));
    }
} else {
    // Verificar permisos para crear nuevos posts
    if (!current_user_can('edit_posts')) {
        wp_die(esc_html__('No tienes permisos para crear propiedades.', 'inmopress'));
    }
}

// Obtener grupos ACF una sola vez
$acf_groups = acf_get_field_groups(array('post_type' => 'impress_property'));
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
    'publish' => array('label' => __('Publicado', 'inmopress'), 'bg' => '#ECFDF5', 'color' => '#10B981'),
    'draft' => array('label' => __('Borrador', 'inmopress'), 'bg' => '#E5E7EB', 'color' => '#6B7280'),
    'pending' => array('label' => __('Pendiente', 'inmopress'), 'bg' => '#FEF3C7', 'color' => '#D97706'),
    'private' => array('label' => __('Privado', 'inmopress'), 'bg' => '#EDE9FE', 'color' => '#7C3AED'),
);
$current_status = isset($status_labels[$post_status]) ? $status_labels[$post_status] : $status_labels['draft'];

// Usamos envío ACF front-end, pero con layout personalizado.
// Es necesario acf_form_head() antes de renderizar el form para validar y guardar.

?>

<div class="crm-editor-wrapper">

    <form action="" method="post" class="crm-editor-form" enctype="multipart/form-data">
        <?php
        // Nonce de seguridad propio
        wp_nonce_field('inmopress_property_form', 'inmopress_property_nonce');

        // Essential ACF hidden fields (nonces, form id, etc)
        $acf_form_args = array(
            'post_id'      => $post_id,
            'post_title'   => false,
            'post_content' => false,
            'uploader'     => 'wp',
            'return'       => Inmopress_Shortcodes::panel_url('properties'),
            'new_post'     => array(
                'post_type'   => 'impress_property',
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
                    <?php esc_html_e('Inmuebles', 'inmopress'); ?>
                    <span>›</span>
                    <?php echo ($post_id !== 'new_post') ? esc_html__('Editar Inmueble', 'inmopress') : esc_html__('Nuevo Inmueble', 'inmopress'); ?>
                </div>
                <div class="crm-title-input-wrapper">
                    <input type="text" name="acf[_post_title]" id="post_title"
                        value="<?php echo esc_attr($post_id !== 'new_post' ? get_the_title($post_id) : ''); ?>"
                        placeholder="<?php esc_attr_e('Nombre del inmueble (p. ej. Villa en Mallorca)', 'inmopress'); ?>" required>
                    <?php
                    $status_badge_class = 'badge-grey';
                    if ($post_status === 'publish') $status_badge_class = 'badge-success';
                    elseif ($post_status === 'pending') $status_badge_class = 'badge-warning';
                    elseif ($post_status === 'private') $status_badge_class = 'badge-primary';
                    ?>
                    <span class="badge <?php echo esc_attr($status_badge_class); ?> small">
                        <?php echo esc_html($current_status['label']); ?>
                    </span>
                </div>
            </div>

            <div class="header-actions">
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties')); ?>" class="btn-crm secondary">
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

                <!-- Media Gallery -->
                <div class="crm-card">
                    <h3><?php esc_html_e('Galería multimedia', 'inmopress'); ?></h3>
                    <div class="crm-field-group">
                        <?php inmopress_render_acf_group('group_property_media', $post_id, $acf_groups_by_key); ?>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="crm-card">
                    <div class="crm-card-header">
                        <h3><?php esc_html_e('Información básica', 'inmopress'); ?></h3>
                    </div>
                    <div class="crm-basic-info-grid">
                        <?php inmopress_render_acf_group('group_property_info', $post_id, $acf_groups_by_key); ?>
                    </div>
                </div>

                <!-- Location -->
                <div class="crm-card">
                    <h3><?php esc_html_e('Ubicación', 'inmopress'); ?></h3>
                    <?php inmopress_render_acf_group('group_property_location', $post_id, $acf_groups_by_key); ?>
                </div>

                <!-- Physical Characteristics -->
                <div class="crm-card">
                    <div class="crm-card-header">
                        <h3><?php esc_html_e('Características físicas', 'inmopress'); ?></h3>
                    </div>
                    <?php inmopress_render_acf_group('group_property_physical', $post_id, $acf_groups_by_key); ?>
                </div>

                <!-- Technical Specs -->
                <div class="crm-card">
                    <h3><?php esc_html_e('Características técnicas y extras', 'inmopress'); ?></h3>
                    <?php inmopress_render_acf_group('group_property_technical', $post_id, $acf_groups_by_key); ?>
                </div>

                <!-- Costs & Fees -->
                <div class="crm-card">
                    <h3><?php esc_html_e('Costes y tasas', 'inmopress'); ?></h3>
                    <?php inmopress_render_acf_group('group_property_costs', $post_id, $acf_groups_by_key); ?>
                </div>
            </div>

            <!-- Right Column: Sidebar -->
            <div class="crm-col-sidebar">

                <?php
                $featured_id = ($post_id !== 'new_post') ? get_post_thumbnail_id($post_id) : 0;
                $featured_url = $featured_id ? wp_get_attachment_image_url($featured_id, 'medium') : '';
                ?>
                <div class="crm-card">
                    <h3><?php esc_html_e('Imagen destacada', 'inmopress'); ?></h3>
                    <div class="crm-featured-image-wrapper">
                        <div class="crm-featured-image-preview">
                            <?php if ($featured_url): ?>
                                <img id="inmopress-featured-preview" src="<?php echo esc_url($featured_url); ?>" alt="">
                                <div id="inmopress-featured-placeholder" class="crm-featured-image-placeholder" style="display: none;"><?php esc_html_e('Sin imagen', 'inmopress'); ?></div>
                            <?php else: ?>
                                <img id="inmopress-featured-preview" src="" alt="" style="display: none;">
                                <div id="inmopress-featured-placeholder" class="crm-featured-image-placeholder"><?php esc_html_e('Sin imagen', 'inmopress'); ?></div>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" id="inmopress_featured_image_id" name="inmopress_featured_image_id" value="<?php echo esc_attr($featured_id); ?>">
                        <div class="crm-featured-image-actions">
                            <button type="button" class="btn-crm secondary" id="inmopress-featured-upload">
                                <?php esc_html_e('Seleccionar imagen', 'inmopress'); ?>
                            </button>
                            <button type="button" class="btn-crm secondary" id="inmopress-featured-remove">
                                <?php esc_html_e('Quitar', 'inmopress'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- AI SEO Assistant -->
                <div class="crm-card ai-assistant-card">
                    <div class="ai-assistant-card-header">
                        <h3><?php esc_html_e('Asistente SEO con IA', 'inmopress'); ?></h3>
                        <span class="badge badge-success small"><?php esc_html_e('Vinculado', 'inmopress'); ?></span>
                    </div>

                    <!-- Score -->
                    <div class="ai-score-wrapper">
                        <div class="ai-score">45</div>
                        <div class="ai-score-info">
                            <div class="ai-score-title"><?php esc_html_e('Necesita mejorar', 'inmopress'); ?></div>
                            <a href="#" class="ai-score-link js-toggle-seo-recos"><?php esc_html_e('Ver 4 recomendaciones', 'inmopress'); ?></a>
                        </div>
                    </div>
                    <div class="crm-seo-recos">
                        <ul>
                            <li>Incluye la zona en el titulo.</li>
                            <li>Menciona 2 puntos fuertes (piscina, vistas, etc.).</li>
                            <li>Usa una palabra clave consistente.</li>
                            <li>Describe el tipo de inmueble claramente.</li>
                        </ul>
                    </div>

                    <!-- SEO Fields -->
                    <?php
                    $seo_keyword = ($post_id !== 'new_post') ? get_post_meta($post_id, '_inmopress_seo_keyword', true) : '';
                    $seo_title = ($post_id !== 'new_post') ? get_post_meta($post_id, '_inmopress_seo_title', true) : '';
                    ?>
                    <div class="crm-field-group">
                        <label for="inmopress_seo_keyword"><?php esc_html_e('Palabra clave', 'inmopress'); ?></label>
                        <input type="text" id="inmopress_seo_keyword" name="inmopress_seo_keyword"
                            value="<?php echo esc_attr($seo_keyword); ?>"
                            placeholder="<?php esc_attr_e('p. ej. Villa de lujo Mallorca', 'inmopress'); ?>">
                    </div>
                    <div class="crm-field-group">
                        <div class="crm-seo-field-header">
                            <label for="inmopress_seo_title"><?php esc_html_e('Título SEO', 'inmopress'); ?></label>
                            <button type="button" class="crm-seo-generate-btn js-generate-seo-title">
                                <?php esc_html_e('Generar', 'inmopress'); ?>
                            </button>
                        </div>
                        <input type="text" id="inmopress_seo_title" name="inmopress_seo_title"
                            value="<?php echo esc_attr($seo_title); ?>"
                            placeholder="<?php esc_attr_e('p. ej. Villa en Mallorca - Propiedad de lujo en venta', 'inmopress'); ?>">
                    </div>

                </div>

                <?php
                $tax_fields = apply_filters('inmopress_property_tax_fields', array(
                    'impress_province' => array('label' => __('Provincia', 'inmopress'), 'type' => 'single'),
                    'impress_city' => array('label' => __('Ciudad', 'inmopress'), 'type' => 'single'),
                    'impress_operation' => array('label' => __('Operación', 'inmopress'), 'type' => 'single'),
                    'impress_property_type' => array('label' => __('Tipo de propiedad', 'inmopress'), 'type' => 'single'),
                    'impress_property_group' => array('label' => __('Agrupación', 'inmopress'), 'type' => 'single'),
                    'impress_category' => array('label' => __('Categoría', 'inmopress'), 'type' => 'single'),
                    'impress_status' => array('label' => __('Estado comercial', 'inmopress'), 'type' => 'single'),
                    'impress_condition' => array('label' => __('Estado de conservación', 'inmopress'), 'type' => 'single'),
                    'impress_energy_rating' => array('label' => __('Certificación energética', 'inmopress'), 'type' => 'single'),
                    'impress_orientation' => array('label' => __('Orientación', 'inmopress'), 'type' => 'single'),
                    'impress_heating' => array('label' => __('Calefacción', 'inmopress'), 'type' => 'single'),
                    'impress_features' => array('label' => __('Características premium', 'inmopress'), 'type' => 'multi'),
                    'impress_amenities' => array('label' => __('Equipamiento', 'inmopress'), 'type' => 'multi'),
                ));
                ?>
                <div class="crm-card">
                    <h3><?php esc_html_e('Taxonomías', 'inmopress'); ?></h3>
                    <div class="crm-taxonomy-group">
                        <?php foreach ($tax_fields as $taxonomy => $config): ?>
                            <?php if (!taxonomy_exists($taxonomy)) continue; ?>
                            <?php
                            $selected = ($post_id !== 'new_post') ? wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids')) : array();
                            $type = isset($config['type']) ? $config['type'] : 'single';
                            $label = isset($config['label']) ? $config['label'] : $taxonomy;
                            ?>
                            <div class="crm-field-group crm-taxonomy-field">
                                <label><?php echo esc_html($label); ?></label>
                                <?php if ($type === 'multi'): ?>
                                    <div class="crm-taxonomy-checkboxes">
                                        <?php
                                        $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));
                                        foreach ($terms as $term):
                                            $is_checked = in_array($term->term_id, $selected, true);
                                            ?>
                                            <label class="crm-taxonomy-checkbox-label">
                                                <input type="checkbox" name="inmopress_tax[<?php echo esc_attr($taxonomy); ?>][]" value="<?php echo esc_attr($term->term_id); ?>" <?php checked($is_checked); ?>>
                                                <?php echo esc_html($term->name); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <select name="inmopress_tax[<?php echo esc_attr($taxonomy); ?>]" class="crm-taxonomy-select">
                                        <option value=""><?php esc_html_e('Selecciona una opción', 'inmopress'); ?></option>
                                        <?php
                                        $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));
                                        foreach ($terms as $term):
                                            $is_selected = in_array($term->term_id, $selected, true);
                                            ?>
                                            <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($is_selected); ?>>
                                                <?php echo esc_html($term->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price & Operation -->
                <div class="crm-card">
                    <h3><?php esc_html_e('Precio y operación', 'inmopress'); ?></h3>
                    <?php
                    inmopress_render_acf_group('group_property_sale', $post_id, $acf_groups_by_key);

                    if (isset($acf_groups_by_key['group_property_rent'])):
                        ?>
                        <h4 style="margin: var(--spacing-md) 0 var(--spacing-sm); font-size: var(--font-size-small); color: var(--color-text-secondary);">
                            <?php esc_html_e('Detalles de alquiler', 'inmopress'); ?></h4>
                        <?php inmopress_render_acf_group('group_property_rent', $post_id, $acf_groups_by_key); ?>
                    <?php endif; ?>
                </div>

            </div>

        </div>

        <!-- Hidden submit button triggered by the header Save button -->
        <input type="submit" value="Guardar cambios" class="hidden">

    </form>
</div>

<script>
    jQuery(function ($) {
        var frame;
        $('#inmopress-featured-upload').on('click', function (e) {
            e.preventDefault();
            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({
                title: '<?php echo esc_js(__('Seleccionar imagen destacada', 'inmopress')); ?>',
                button: { text: '<?php echo esc_js(__('Usar esta imagen', 'inmopress')); ?>' },
                multiple: false
            });
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#inmopress_featured_image_id').val(attachment.id);
                var url = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;
                $('#inmopress-featured-preview').attr('src', url).show();
                $('#inmopress-featured-placeholder').hide();
            });
            frame.open();
        });

        $('#inmopress-featured-remove').on('click', function (e) {
            e.preventDefault();
            $('#inmopress_featured_image_id').val('');
            $('#inmopress-featured-preview').hide();
            $('#inmopress-featured-placeholder').show();
        });

        $('.js-generate-seo-title').on('click', function () {
            var title = $('#post_title').val();
            if (!title) {
                title = 'Propiedad';
            }
            var seoTitle = title + ' | Inmueble en venta';
            $('#inmopress_seo_title').val(seoTitle).trigger('change');
        });

        $('.js-toggle-seo-recos').on('click', function (e) {
            e.preventDefault();
            $(this).closest('.ai-assistant-card').find('.crm-seo-recos').slideToggle(150);
        });
    });
</script>
