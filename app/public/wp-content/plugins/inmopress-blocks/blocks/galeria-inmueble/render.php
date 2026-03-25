<?php
/**
 * Galería Inmueble Block Template.
 */

$id = 'inmopress-gallery-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'inmopress-gallery';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

$type = get_field('tipo') ?: 'grid'; // grid, carousel
$className .= ' style-' . $type;

// Get ACF Gallery field
$images = get_field('fotos');

if (!$images) {
    // Placeholder for editor
    if (is_admin()) {
        echo '<div class="inmopress-placeholder">Galería de imágenes (Añadir imágenes en el campo "Fotos" del inmueble)</div>';
    }
    return;
}

$count = count($images);
?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <div class="inmopress-gallery-<?php echo esc_attr($type); ?>">
        <?php foreach ($images as $index => $image):
            // Limit grid items to 5
            if ($type === 'grid' && $index > 4)
                break;
            ?>
            <div class="inmopress-gallery-item">
                <a href="<?php echo esc_url($image['url']); ?>" data-lightbox="property-gallery">
                    <img src="<?php echo esc_url($image['sizes']['large']); ?>"
                        alt="<?php echo esc_attr($image['alt']); ?>" />

                    <?php if ($type === 'grid' && $index === 4 && $count > 5): ?>
                        <div class="inmopress-gallery-more">
                            +
                            <?php echo ($count - 5); ?>
                        </div>
                    <?php endif; ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (get_field('contador_fotos')): ?>
        <div class="inmopress-gallery-count">
            <span class="dashicons dashicons-camera"></span>
            <?php echo $count; ?> Fotos
        </div>
    <?php endif; ?>

</div>