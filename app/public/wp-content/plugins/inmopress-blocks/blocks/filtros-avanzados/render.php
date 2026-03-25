<?php
/**
 * Filtros Avanzados Block Template.
 */

$id = 'inmopress-filters-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'inmopress-filters';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Layout options
$layout = get_field('layout') ?: 'horizontal'; // horizontal, vertical, accordion
$show_search = get_field('mostrar_busqueda') !== false;
$show_operation = get_field('mostrar_operacion') !== false;
$show_property_type = get_field('mostrar_tipo') !== false;
$show_price = get_field('mostrar_precio') !== false;
$show_location = get_field('mostrar_ubicacion') !== false;
$show_bedrooms = get_field('mostrar_dormitorios') !== false;
$show_bathrooms = get_field('mostrar_banos') !== false;

$results_page = get_field('pagina_resultados') ?: '';
$ajax_enabled = get_field('ajax_enabled') !== false;

$className .= ' layout-' . $layout;

// Get taxonomies for dropdowns
$cities = get_terms(array(
    'taxonomy' => 'impress_property_city',
    'hide_empty' => false,
));

$property_types = get_terms(array(
    'taxonomy' => 'impress_property_type',
    'hide_empty' => false,
));

?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <form class="inmopress-filters-form" 
          action="<?php echo esc_url($results_page ?: get_post_type_archive_link('impress_property')); ?>" 
          method="GET"
          data-ajax="<?php echo $ajax_enabled ? 'true' : 'false'; ?>">

        <?php if ($show_search): ?>
            <div class="inmopress-filter-field">
                <label for="<?php echo esc_attr($id); ?>-search">Buscar</label>
                <input type="text" 
                       id="<?php echo esc_attr($id); ?>-search" 
                       name="s" 
                       placeholder="Buscar propiedades..."
                       value="<?php echo esc_attr(get_query_var('s')); ?>">
            </div>
        <?php endif; ?>

        <?php if ($show_operation): ?>
            <div class="inmopress-filter-field">
                <label for="<?php echo esc_attr($id); ?>-operation">Operación</label>
                <select id="<?php echo esc_attr($id); ?>-operation" name="operation">
                    <option value="">Todas</option>
                    <option value="venta" <?php selected(get_query_var('operation'), 'venta'); ?>>Venta</option>
                    <option value="alquiler" <?php selected(get_query_var('operation'), 'alquiler'); ?>>Alquiler</option>
                </select>
            </div>
        <?php endif; ?>

        <?php if ($show_property_type && !empty($property_types)): ?>
            <div class="inmopress-filter-field">
                <label for="<?php echo esc_attr($id); ?>-type">Tipo</label>
                <select id="<?php echo esc_attr($id); ?>-type" name="property_type">
                    <option value="">Todos</option>
                    <?php foreach ($property_types as $type): ?>
                        <option value="<?php echo esc_attr($type->slug); ?>" 
                                <?php selected(get_query_var('property_type'), $type->slug); ?>>
                            <?php echo esc_html($type->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if ($show_location && !empty($cities)): ?>
            <div class="inmopress-filter-field">
                <label for="<?php echo esc_attr($id); ?>-city">Ciudad</label>
                <select id="<?php echo esc_attr($id); ?>-city" name="city">
                    <option value="">Todas</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo esc_attr($city->slug); ?>" 
                                <?php selected(get_query_var('city'), $city->slug); ?>>
                            <?php echo esc_html($city->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if ($show_price): ?>
            <div class="inmopress-filter-field inmopress-filter-range">
                <label>Precio</label>
                <div class="inmopress-range-inputs">
                    <input type="number" 
                           name="min_price" 
                           placeholder="Mín."
                           value="<?php echo esc_attr(get_query_var('min_price')); ?>">
                    <span>-</span>
                    <input type="number" 
                           name="max_price" 
                           placeholder="Máx."
                           value="<?php echo esc_attr(get_query_var('max_price')); ?>">
                </div>
            </div>
        <?php endif; ?>

        <?php if ($show_bedrooms): ?>
            <div class="inmopress-filter-field">
                <label for="<?php echo esc_attr($id); ?>-bedrooms">Dormitorios</label>
                <select id="<?php echo esc_attr($id); ?>-bedrooms" name="bedrooms">
                    <option value="">Todos</option>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php selected(get_query_var('bedrooms'), $i); ?>>
                            <?php echo $i; ?>+
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if ($show_bathrooms): ?>
            <div class="inmopress-filter-field">
                <label for="<?php echo esc_attr($id); ?>-bathrooms">Baños</label>
                <select id="<?php echo esc_attr($id); ?>-bathrooms" name="bathrooms">
                    <option value="">Todos</option>
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php selected(get_query_var('bathrooms'), $i); ?>>
                            <?php echo $i; ?>+
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="inmopress-filter-actions">
            <button type="submit" class="inmopress-filter-submit">
                Buscar
            </button>
            <button type="reset" class="inmopress-filter-reset">
                Limpiar
            </button>
        </div>

    </form>

    <?php if ($ajax_enabled): ?>
        <div class="inmopress-filters-results" style="display: none;">
            <!-- AJAX results will be loaded here -->
        </div>
    <?php endif; ?>

</div>

<script>
(function() {
    var form = document.querySelector('#<?php echo esc_js($id); ?> .inmopress-filters-form');
    if (form && form.dataset.ajax === 'true') {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // AJAX search implementation would go here
            console.log('AJAX search not yet implemented');
        });
    }
})();
</script>
