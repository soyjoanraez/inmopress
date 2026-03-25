<?php
/**
 * Grid Inmuebles Block Template.
 */

// Block configuration
$id = 'inmopress-grid-' . $block['id'];
if( !empty($block['anchor']) ) {
    $id = $block['anchor'];
}

$className = 'inmopress-grid-wrapper';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className'];
}

// ACF Fields
$source = get_field('fuente') ?: 'recientes'; // recientes, destacados, tax
$posts_per_page = get_field('numero_inmuebles') ?: 6;
$columns = get_field('columnas') ?: 3;
$orderby = get_field('orden') ?: 'date';
$pagination = get_field('paginacion');

// Build Query
$args = array(
    'post_type' => 'impress_property',
    'posts_per_page' => $posts_per_page,
    'post_status' => 'publish',
    'orderby' => $orderby,
    'order' => 'DESC',
);

// Source filters
if( $source === 'destacados' ) {
    $args['meta_query'] = array(
        array(
            'key' => 'exclusiva',
            'value' => '1',
            'compare' => '=='
        )
    );
}

// Custom Tax Query if selected (not implemented fully in phase 1 but prepared)
// ...

$query = new WP_Query($args);

// Grid classes
$grid_class = 'inmopress-grid cols-' . $columns;
?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
    
    <?php if( $query->have_posts() ): ?>
        
        <div class="<?php echo esc_attr($grid_class); ?>">
            <?php while( $query->have_posts() ): $query->the_post(); 
                $prop_id = get_the_ID();
                $price = get_field('precio_venta') ?: get_field('precio_alquiler');
                $price_suffix = get_field('proposito') === 'alquiler' ? '/mes' : '';
                $rooms = get_field('dormitorios');
                $baths = get_field('banos');
                $area = get_field('superficie_construida');
                $city_terms = get_the_terms($prop_id, 'impress_city');
                $city = $city_terms && !is_wp_error($city_terms) ? $city_terms[0]->name : '';
                $operation_terms = get_the_terms($prop_id, 'impress_operation');
                $operation = $operation_terms && !is_wp_error($operation_terms) ? $operation_terms[0]->name : '';
            ?>
                
                <article class="inmopress-card">
                    <div class="inmopress-card-image">
                        <a href="<?php the_permalink(); ?>">
                            <?php if( has_post_thumbnail() ): ?>
                                <?php the_post_thumbnail('medium_large'); ?>
                            <?php else: ?>
                                <img src="<?php echo INMOPRESS_BLOCKS_URL . 'assets/img/placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
                            <?php endif; ?>
                        </a>
                        <?php if( $operation ): ?>
                            <span class="inmopress-card-tag"><?php echo esc_html($operation); ?></span>
                        <?php endif; ?>
                        
                        <?php if( $price ): ?>
                            <div class="inmopress-card-price">
                                <?php echo number_format($price, 0, ',', '.') . ' €' . $price_suffix; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="inmopress-card-body">
                        <h3 class="inmopress-card-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        
                        <div class="inmopress-card-meta">
                            <?php if( $rooms ): ?>
                                <div class="inmopress-card-meta-item">
                                    <span class="dashicons dashicons-admin-home"></span> <?php echo $rooms; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if( $baths ): ?>
                                <div class="inmopress-card-meta-item">
                                    <span class="dashicons dashicons-drop"></span> <?php echo $baths; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if( $area ): ?>
                                <div class="inmopress-card-meta-item">
                                    <span class="dashicons dashicons-fullscreen"></span> <?php echo $area; ?> m²
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="inmopress-card-footer">
                            <div class="inmopress-card-location">
                                <span class="dashicons dashicons-location"></span> <?php echo esc_html($city); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="btn-inmo-link"><?php _e('Ver más', 'inmopress'); ?> →</a>
                        </div>
                    </div>
                </article>
                
            <?php endwhile; ?>
        </div>
        
        <?php if( $pagination ): ?>
            <div class="inmopress-pagination">
                <?php 
                echo paginate_links(array(
                    'total' => $query->max_num_pages
                )); 
                ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <p><?php _e('No se encontraron inmuebles.', 'inmopress'); ?></p>
    <?php endif; wp_reset_postdata(); ?>
    
</div>
