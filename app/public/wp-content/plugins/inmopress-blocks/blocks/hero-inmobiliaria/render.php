<?php
/**
 * Hero Inmobiliaria Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'hero-inmobiliaria-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'hero-inmobiliaria';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$bg_image = get_field('imagen_fondo');
$title = get_field('titulo') ?: 'Encuentra tu hogar ideal';
$subtitle = get_field('subtitulo') ?: 'Miles de propiedades te están esperando';
$show_search = get_field('incluir_buscador');
$overlay_opacity = get_field('overlay_opacity') ?: 50;
$cta_1 = get_field('boton_1');
$cta_2 = get_field('boton_2');

// Background style
$style = '';
if ($bg_image) {
    $style = 'background-image: url(' . esc_url($bg_image['url']) . ');';
}
?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>"
    style="<?php echo esc_attr($style); ?>">

    <div class="hero-overlay" style="background: rgba(0,0,0,<?php echo $overlay_opacity / 100; ?>);"></div>

    <div class="hero-content inmopress-container">
        <h1 class="hero-title">
            <?php echo esc_html($title); ?>
        </h1>
        <p class="hero-subtitle">
            <?php echo esc_html($subtitle); ?>
        </p>

        <?php if ($cta_1 || $cta_2): ?>
            <div class="hero-actions">
                <?php if ($cta_1): ?>
                    <a href="<?php echo esc_url($cta_1['url']); ?>" class="btn-inmo"
                        target="<?php echo esc_attr($cta_1['target']); ?>">
                        <?php echo esc_html($cta_1['title']); ?>
                    </a>
                <?php endif; ?>

                <?php if ($cta_2): ?>
                    <a href="<?php echo esc_url($cta_2['url']); ?>" class="btn-inmo outline"
                        target="<?php echo esc_attr($cta_2['target']); ?>">
                        <?php echo esc_html($cta_2['title']); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($show_search): ?>
            <div class="hero-search-wrapper">
                <?php
                // Si existe el bloque de buscador, intentamos renderizarlo o ponemos un placeholder
                if (function_exists('acf_register_block_type') && file_exists(INMOPRESS_BLOCKS_PATH . 'blocks/buscador-inmuebles/render.php')) {
                    echo '<!-- El buscador se integrará aquí -->';
                    echo do_shortcode('[inmopress_buscador compact="true"]'); // Fallback o integración futura
                } else {
                    echo '<p style="color:#666; padding: 20px;">Buscador integrado próximamente...</p>';
                }
                ?>
            </div>
        <?php endif; ?>
    </div>

</div>