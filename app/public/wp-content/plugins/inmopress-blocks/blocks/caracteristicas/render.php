<?php
/**
 * Características Block Template.
 */

$id = 'inmopress-features-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'inmopress-features';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Layout options
$layout = get_field('layout') ?: 'grid'; // grid, list
$columns = get_field('columns') ?: 3; // 2, 3, 4

$className .= ' layout-' . $layout;
$className .= ' cols-' . $columns;

// Get features from ACF field (assuming it's a repeater or select field)
$features = get_field('caracteristicas'); // This should be configured in ACF

// If features is a repeater
if (is_array($features) && isset($features[0]['nombre'])) {
    $features_list = $features;
} elseif (is_array($features)) {
    // If it's a simple array of values
    $features_list = array();
    foreach ($features as $feature) {
        $features_list[] = array('nombre' => $feature, 'icono' => '');
    }
} else {
    // Fallback: get from property if we're on a property page
    global $post;
    if ($post && $post->post_type === 'impress_property') {
        $features_field = get_field('impress_property_features', $post->ID);
        if ($features_field) {
            $features_list = is_array($features_field) ? $features_field : array($features_field);
        } else {
            $features_list = array();
        }
    } else {
        $features_list = array();
    }
}

// Default features if none found
if (empty($features_list)) {
    $features_list = array(
        array('nombre' => 'Aire acondicionado', 'icono' => 'snowflake'),
        array('nombre' => 'Calefacción', 'icono' => 'fire'),
        array('nombre' => 'Parking', 'icono' => 'car'),
        array('nombre' => 'Ascensor', 'icono' => 'arrow-up'),
        array('nombre' => 'Terraza', 'icono' => 'home'),
        array('nombre' => 'Balcón', 'icono' => 'building'),
    );
}

?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <?php if (!empty($features_list)): ?>
        <div class="inmopress-features-container">
            <?php foreach ($features_list as $feature): ?>
                <?php
                $name = is_array($feature) ? ($feature['nombre'] ?? $feature['name'] ?? '') : $feature;
                $icon = is_array($feature) ? ($feature['icono'] ?? $feature['icon'] ?? '') : '';
                ?>
                <div class="inmopress-feature-item">
                    <?php if ($icon): ?>
                        <span class="inmopress-feature-icon dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
                    <?php endif; ?>
                    <span class="inmopress-feature-name"><?php echo esc_html($name); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="inmopress-no-features">No hay características disponibles.</p>
    <?php endif; ?>

</div>
