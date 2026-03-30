<?php
/**
 * Inmuebles Similares Block Template.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'inmuebles-similares-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'inmopress-similares';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Logic to find similar properties
$current_id = get_the_ID();
$terms_city = get_the_terms($current_id, 'impress_city');
$terms_type = get_the_terms($current_id, 'impress_property_type');

$tax_query = array('relation' => 'AND');

// Match City
if ($terms_city && !is_wp_error($terms_city)) {
    $city_ids = wp_list_pluck($terms_city, 'term_id');
    $tax_query[] = array(
        'taxonomy' => 'impress_city',
        'field' => 'term_id',
        'terms' => $city_ids,
        'operator' => 'IN'
    );
}

// Match Type
if ($terms_type && !is_wp_error($terms_type)) {
    $type_ids = wp_list_pluck($terms_type, 'term_id');
    $tax_query[] = array(
        'taxonomy' => 'impress_property_type',
        'field' => 'term_id',
        'terms' => $type_ids,
        'operator' => 'IN'
    );
}

$args = array(
    'post_type' => 'impress_property',
    'posts_per_page' => 3,
    'post__not_in' => array($current_id),
    'tax_query' => $tax_query,
    'orderby' => 'rand', // Randomize for variety
);

$query = new WP_Query($args);

if ($query->have_posts()): ?>
    <div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
        <h3 class="similares-title">También te puede interesar</h3>
        <div class="inmopress-grid-wrapper">
            <?php while ($query->have_posts()):
                $query->the_post();
                $post_id = get_the_ID();
                $precio_venta = get_field('precio_venta', $post_id);
                $precio_alquiler = get_field('precio_alquiler', $post_id);
                $city_terms = get_the_terms($post_id, 'impress_city');
                $city = ($city_terms && !is_wp_error($city_terms)) ? $city_terms[0]->name : '';
                $dormitorios = get_field('dormitorios', $post_id);
                $banos = get_field('banos', $post_id);
                $superficie = get_field('superficie_construida', $post_id);
                ?>
                <div class="inmo-card">
                    <div class="inmo-card-image">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()): ?>
                                <?php the_post_thumbnail('medium_large'); ?>
                            <?php else: ?>
                                <img src="<?php echo plugins_url('assets/img/placeholder.jpg', dirname(dirname(__FILE__))); ?>"
                                    alt="No image">
                            <?php endif; ?>
                            <span
                                class="badget-status"><?php echo (get_field('estado_inmueble') == 'reservado') ? 'Reservado' : 'Disponible'; ?></span>
                        </a>
                    </div>
                    <div class="inmo-card-content">
                        <div class="inmo-card-price">
                            <?php
                            if ($precio_venta) {
                                echo number_format($precio_venta, 0, ',', '.') . ' €';
                            } elseif ($precio_alquiler) {
                                echo number_format($precio_alquiler, 0, ',', '.') . ' €/mes';
                            }
                            ?>
                        </div>
                        <h3 class="inmo-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="inmo-card-location">📍 <?php echo esc_html($city); ?></p>
                        <div class="inmo-card-features">
                            <?php if ($dormitorios): ?><span>🛏 <?php echo esc_html($dormitorios); ?></span><?php endif; ?>
                            <?php if ($banos): ?><span>🚿 <?php echo esc_html($banos); ?></span><?php endif; ?>
                            <?php if ($superficie): ?><span>📏 <?php echo esc_html($superficie); ?> m²</span><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>
    </div>
<?php else: ?>
    <?php if (is_admin()): ?>
        <p style="color: #666; font-style: italic;">No hay inmuebles similares para mostrar en la vista previa (basado en el
            post actual).</p>
    <?php endif; ?>
<?php endif; ?>