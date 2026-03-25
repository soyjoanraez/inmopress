<?php
/**
 * Property Card List Template
 *
 * @package Inmopress\CRM
 * @var int $post_id Current post ID
 * @var Property_Settings $settings Settings instance
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use Inmopress\CRM\Property_Settings;

// Get variables from shortcode
$post_id = isset($GLOBALS['inmopress_post_id']) ? $GLOBALS['inmopress_post_id'] : get_the_ID();
$settings = isset($GLOBALS['inmopress_settings']) ? $GLOBALS['inmopress_settings'] : Property_Settings::get_instance();

// Get property data
$titulo = get_field('titulo_seo', $post_id) ?: get_the_title($post_id);
$referencia = get_field('referencia', $post_id);
$proposito = get_field('proposito', $post_id);
$precio_venta = get_field('precio_venta', $post_id);
$precio_alquiler = get_field('precio_alquiler', $post_id);
$precio = ($proposito === 'venta') ? $precio_venta : $precio_alquiler;

// Location
$provincia_terms = get_the_terms($post_id, 'impress_province');
$poblacion_terms = get_the_terms($post_id, 'impress_municipality');
$zona = get_field('zona', $post_id);

// Characteristics
$dormitorios = get_field('dormitorios', $post_id);
$banos = get_field('banos', $post_id);
$superficie_util = get_field('superficie_util', $post_id);
$superficie_construida = get_field('superficie_construida', $post_id);
$superficie = $superficie_util ?: $superficie_construida;

// Badges
$solo_vip = get_field('solo_vip', $post_id);
$exclusiva = get_field('exclusiva', $post_id);
$vendida = get_field('vendida', $post_id);
$reservada = get_field('reservada', $post_id);

// Get visible fields from settings
$visible_fields = $settings->get_visible_fields();
$visible_taxonomies = $settings->get_visible_taxonomies();
?>

<div class="inmopress-property-card inmopress-property-card-list">
    <div class="property-image-wrapper">
        <?php if (has_post_thumbnail($post_id)) : ?>
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="property-image-link">
                <?php echo get_the_post_thumbnail($post_id, 'medium', array('class' => 'property-image', 'loading' => 'lazy')); ?>
            </a>
        <?php else : ?>
            <div class="property-image-placeholder">
                <span class="placeholder-text">Sin imagen</span>
            </div>
        <?php endif; ?>
        
        <div class="property-badges">
            <?php if ($solo_vip) : ?>
                <span class="badge badge-vip">VIP</span>
            <?php endif; ?>
            <?php if ($exclusiva) : ?>
                <span class="badge badge-exclusiva">Exclusiva</span>
            <?php endif; ?>
            <?php if ($vendida) : ?>
                <span class="badge badge-vendida">Vendida</span>
            <?php endif; ?>
            <?php if ($reservada) : ?>
                <span class="badge badge-reservada">Reservada</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="property-content">
        <div class="property-header">
            <?php if (in_array('titulo_seo', $visible_fields) && $titulo) : ?>
                <h3 class="property-title">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                        <?php echo esc_html($titulo); ?>
                    </a>
                </h3>
            <?php endif; ?>
            
            <?php if (in_array('referencia', $visible_fields) && $referencia) : ?>
                <div class="property-reference">Ref: <?php echo esc_html($referencia); ?></div>
            <?php endif; ?>
        </div>

        <?php if (in_array('impress_province', $visible_taxonomies) || in_array('impress_municipality', $visible_taxonomies) || in_array('zona', $visible_fields)) : ?>
            <div class="property-location">
                <?php
                $location_parts = array();
                if (in_array('impress_province', $visible_taxonomies) && $provincia_terms && !is_wp_error($provincia_terms)) {
                    $location_parts[] = esc_html($provincia_terms[0]->name);
                }
                if (in_array('impress_municipality', $visible_taxonomies) && $poblacion_terms && !is_wp_error($poblacion_terms)) {
                    $location_parts[] = esc_html($poblacion_terms[0]->name);
                }
                if (in_array('zona', $visible_fields) && $zona) {
                    $location_parts[] = esc_html($zona);
                }
                if (!empty($location_parts)) {
                    echo '<span class="property-location-text">' . implode(', ', $location_parts) . '</span>';
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="property-features">
            <?php if (in_array('dormitorios', $visible_fields) && $dormitorios) : ?>
                <div class="property-feature">
                    <span class="feature-icon">🛏️</span>
                    <span class="feature-label">Dormitorios:</span>
                    <span class="feature-value"><?php echo esc_html($dormitorios); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (in_array('banos', $visible_fields) && $banos) : ?>
                <div class="property-feature">
                    <span class="feature-icon">🚿</span>
                    <span class="feature-label">Baños:</span>
                    <span class="feature-value"><?php echo esc_html($banos); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (in_array('superficie_util', $visible_fields) && $superficie) : ?>
                <div class="property-feature">
                    <span class="feature-icon">📐</span>
                    <span class="feature-label">Superficie:</span>
                    <span class="feature-value"><?php echo esc_html($superficie); ?> m²</span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($precio && (in_array('precio_venta', $visible_fields) || in_array('precio_alquiler', $visible_fields))) : ?>
            <div class="property-price">
                <span class="price-value"><?php echo number_format($precio, 0, ',', '.'); ?> €</span>
                <?php if ($proposito === 'alquiler') : ?>
                    <span class="price-period">/mes</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (in_array('proposito', $visible_fields) && $proposito) : ?>
            <div class="property-purpose">
                <span class="purpose-badge purpose-<?php echo esc_attr($proposito); ?>">
                    <?php echo $proposito === 'venta' ? 'Venta' : 'Alquiler'; ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

