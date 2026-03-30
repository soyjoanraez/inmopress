<?php
/**
 * Testimonios Block Template.
 */

$id = 'inmopress-testimonials-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'inmopress-testimonials';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Layout options
$layout = get_field('layout') ?: 'grid'; // grid, carousel, list
$columns = get_field('columns') ?: 3; // 2, 3, 4
$style = get_field('estilo') ?: 'cards'; // cards, minimal, quote

$className .= ' layout-' . $layout;
$className .= ' cols-' . $columns;
$className .= ' style-' . $style;

// Get testimonials from repeater field
$testimonials = get_field('testimonios');

// If no testimonials, try to get from clients
if (empty($testimonials)) {
    $testimonials = array();
    
    // Get clients with testimonials
    $clients = get_posts(array(
        'post_type' => 'impress_client',
        'posts_per_page' => 6,
        'meta_query' => array(
            array(
                'key' => 'impress_client_testimonial',
                'compare' => 'EXISTS',
            ),
        ),
    ));
    
    foreach ($clients as $client) {
        $testimonial_text = get_field('impress_client_testimonial', $client->ID);
        if ($testimonial_text) {
            $testimonials[] = array(
                'nombre' => get_the_title($client->ID),
                'testimonio' => $testimonial_text,
                'cargo' => get_field('impress_client_position', $client->ID) ?: '',
                'empresa' => get_field('impress_client_company', $client->ID) ?: '',
                'foto' => get_the_post_thumbnail_url($client->ID, 'thumbnail'),
                'valoracion' => get_field('impress_client_rating', $client->ID) ?: 5,
            );
        }
    }
}

// Default testimonials if none found
if (empty($testimonials)) {
    $testimonials = array(
        array(
            'nombre' => 'María González',
            'testimonio' => 'Excelente servicio. Encontraron exactamente lo que buscábamos en tiempo récord.',
            'cargo' => 'Compradora',
            'foto' => '',
            'valoracion' => 5,
        ),
        array(
            'nombre' => 'Juan Pérez',
            'testimonio' => 'Profesionales y eficientes. Recomendamos totalmente sus servicios.',
            'cargo' => 'Inversor',
            'foto' => '',
            'valoracion' => 5,
        ),
        array(
            'nombre' => 'Ana Martínez',
            'testimonio' => 'El proceso fue muy sencillo y transparente. Muy contentos con el resultado.',
            'cargo' => 'Vendedora',
            'foto' => '',
            'valoracion' => 5,
        ),
    );
}

$show_rating = get_field('mostrar_valoracion') !== false;
$autoplay = get_field('autoplay') !== false;
$autoplay_delay = get_field('autoplay_delay') ?: 5000;

?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <?php if (!empty($testimonials)): ?>
        <div class="inmopress-testimonials-container">
            <?php foreach ($testimonials as $testimonial): ?>
                <?php
                $name = $testimonial['nombre'] ?? $testimonial['name'] ?? '';
                $text = $testimonial['testimonio'] ?? $testimonial['text'] ?? '';
                $position = $testimonial['cargo'] ?? $testimonial['position'] ?? '';
                $company = $testimonial['empresa'] ?? $testimonial['company'] ?? '';
                $photo = $testimonial['foto'] ?? $testimonial['photo'] ?? '';
                $rating = intval($testimonial['valoracion'] ?? $testimonial['rating'] ?? 5);
                ?>
                <div class="inmopress-testimonial-item">
                    <?php if ($style === 'quote'): ?>
                        <div class="inmopress-testimonial-quote-icon">
                            <span class="dashicons dashicons-format-quote"></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="inmopress-testimonial-content">
                        <p class="inmopress-testimonial-text"><?php echo esc_html($text); ?></p>
                    </div>
                    
                    <?php if ($show_rating): ?>
                        <div class="inmopress-testimonial-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="dashicons dashicons-star-filled <?php echo $i <= $rating ? 'filled' : 'empty'; ?>"></span>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="inmopress-testimonial-author">
                        <?php if ($photo): ?>
                            <div class="inmopress-testimonial-photo">
                                <img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr($name); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="inmopress-testimonial-info">
                            <div class="inmopress-testimonial-name"><?php echo esc_html($name); ?></div>
                            <?php if ($position || $company): ?>
                                <div class="inmopress-testimonial-meta">
                                    <?php if ($position): ?>
                                        <span><?php echo esc_html($position); ?></span>
                                    <?php endif; ?>
                                    <?php if ($position && $company): ?>
                                        <span> - </span>
                                    <?php endif; ?>
                                    <?php if ($company): ?>
                                        <span><?php echo esc_html($company); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($layout === 'carousel'): ?>
            <div class="inmopress-testimonials-nav">
                <button class="inmopress-testimonial-prev">‹</button>
                <button class="inmopress-testimonial-next">›</button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php if ($layout === 'carousel'): ?>
<script>
(function() {
    var container = document.querySelector('#<?php echo esc_js($id); ?> .inmopress-testimonials-container');
    if (!container) return;
    
    var currentIndex = 0;
    var items = container.querySelectorAll('.inmopress-testimonial-item');
    var total = items.length;
    
    function showSlide(index) {
        items.forEach(function(item, i) {
            item.style.display = i === index ? 'block' : 'none';
        });
    }
    
    function nextSlide() {
        currentIndex = (currentIndex + 1) % total;
        showSlide(currentIndex);
    }
    
    function prevSlide() {
        currentIndex = (currentIndex - 1 + total) % total;
        showSlide(currentIndex);
    }
    
    var nextBtn = document.querySelector('#<?php echo esc_js($id); ?> .inmopress-testimonial-next');
    var prevBtn = document.querySelector('#<?php echo esc_js($id); ?> .inmopress-testimonial-prev');
    
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    
    showSlide(0);
    
    <?php if ($autoplay): ?>
    setInterval(nextSlide, <?php echo intval($autoplay_delay); ?>);
    <?php endif; ?>
})();
</script>
<?php endif; ?>
