<?php
/**
 * Buscador Inmuebles Block Template.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'inmopress-search-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'inmopress-search-block';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

$display_type = get_field('tipo_display') ?: 'horizontal'; // horizontal, vertical
$className .= ' ' . $display_type;

// Obtener taxonomías para los selects
$terms_operation = get_terms(['taxonomy' => 'impress_operation', 'hide_empty' => false]);
$terms_type = get_terms(['taxonomy' => 'impress_property_type', 'hide_empty' => false]);
$terms_city = get_terms(['taxonomy' => 'impress_city', 'hide_empty' => true]); // Solo ciudades con inmuebles

// URL de la página de resultados (archivo de inmuebles)
$action_url = get_post_type_archive_link('impress_property');
?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <form action="<?php echo esc_url($action_url); ?>" method="get" class="inmopress-search-form">

        <!-- Operación -->
        <div class="inmopress-search-field">
            <label>
                <?php _e('Operación', 'inmopress'); ?>
            </label>
            <select name="operacion">
                <option value="">
                    <?php _e('Cualquier operación', 'inmopress'); ?>
                </option>
                <?php foreach ($terms_operation as $term): ?>
                    <option value="<?php echo esc_attr($term->slug); ?>">
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tipo -->
        <div class="inmopress-search-field">
            <label>
                <?php _e('Tipo de vivienda', 'inmopress'); ?>
            </label>
            <select name="tipo">
                <option value="">
                    <?php _e('Todos los tipos', 'inmopress'); ?>
                </option>
                <?php foreach ($terms_type as $term): ?>
                    <option value="<?php echo esc_attr($term->slug); ?>">
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Ciudad -->
        <div class="inmopress-search-field">
            <label>
                <?php _e('Ciudad', 'inmopress'); ?>
            </label>
            <select name="ciudad">
                <option value="">
                    <?php _e('Todas las ciudades', 'inmopress'); ?>
                </option>
                <?php foreach ($terms_city as $term): ?>
                    <option value="<?php echo esc_attr($term->slug); ?>">
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Submit -->
        <div class="inmopress-search-submit">
            <label>&nbsp;</label> <!-- Spacer -->
            <button type="submit" class="btn-inmo">
                <span class="dashicons dashicons-search"></span>
                <?php echo get_field('texto_boton') ?: __('Buscar', 'inmopress'); ?>
            </button>
        </div>

    </form>

</div>