<?php
/**
 * Estadísticas Block Template.
 */

$id = 'inmopress-stats-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'inmopress-stats';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Layout options
$layout = get_field('layout') ?: 'grid'; // grid, list
$columns = get_field('columns') ?: 4; // 2, 3, 4
$style = get_field('estilo') ?: 'cards'; // cards, minimal, icons

$className .= ' layout-' . $layout;
$className .= ' cols-' . $columns;
$className .= ' style-' . $style;

// Get stats from repeater field
$stats = get_field('estadisticas');

// If no stats provided, use default or calculate from properties
if (empty($stats)) {
    $stats = array();
    
    // Calculate real stats from properties
    $properties_count = wp_count_posts('impress_property');
    $total_properties = $properties_count->publish ?? 0;
    
    $clients_count = wp_count_posts('impress_client');
    $total_clients = $clients_count->publish ?? 0;
    
    // Get average price
    $avg_price_query = new WP_Query(array(
        'post_type' => 'impress_property',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'impress_property_price',
                'value' => 0,
                'compare' => '>',
            ),
        ),
    ));
    
    $total_price = 0;
    $price_count = 0;
    foreach ($avg_price_query->posts as $post) {
        $price = get_field('impress_property_price', $post->ID);
        if ($price) {
            $total_price += floatval($price);
            $price_count++;
        }
    }
    $avg_price = $price_count > 0 ? round($total_price / $price_count) : 0;
    
    // Default stats
    $stats = array(
        array(
            'numero' => $total_properties,
            'etiqueta' => 'Propiedades',
            'icono' => 'admin-home',
            'sufijo' => '',
        ),
        array(
            'numero' => $total_clients,
            'etiqueta' => 'Clientes',
            'icono' => 'groups',
            'sufijo' => '',
        ),
        array(
            'numero' => $avg_price,
            'etiqueta' => 'Precio Promedio',
            'icono' => 'money-alt',
            'sufijo' => ' €',
        ),
        array(
            'numero' => '15+',
            'etiqueta' => 'Años de Experiencia',
            'icono' => 'calendar-alt',
            'sufijo' => '',
        ),
    );
}

// Animation
$animate = get_field('animar_numeros') !== false;

?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <?php if (!empty($stats)): ?>
        <div class="inmopress-stats-container">
            <?php foreach ($stats as $stat): ?>
                <?php
                $number = $stat['numero'] ?? $stat['number'] ?? '';
                $label = $stat['etiqueta'] ?? $stat['label'] ?? '';
                $icon = $stat['icono'] ?? $stat['icon'] ?? '';
                $suffix = $stat['sufijo'] ?? $stat['suffix'] ?? '';
                $prefix = $stat['prefijo'] ?? $stat['prefix'] ?? '';
                ?>
                <div class="inmopress-stat-item" 
                     <?php if ($animate && is_numeric($number)): ?>
                     data-number="<?php echo esc_attr($number); ?>"
                     <?php endif; ?>>
                    <?php if ($icon && $style !== 'minimal'): ?>
                        <div class="inmopress-stat-icon">
                            <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
                        </div>
                    <?php endif; ?>
                    <div class="inmopress-stat-content">
                        <div class="inmopress-stat-number">
                            <?php if ($prefix): ?>
                                <span class="prefix"><?php echo esc_html($prefix); ?></span>
                            <?php endif; ?>
                            <span class="value"><?php echo esc_html($number); ?></span>
                            <?php if ($suffix): ?>
                                <span class="suffix"><?php echo esc_html($suffix); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($label): ?>
                            <div class="inmopress-stat-label"><?php echo esc_html($label); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php if ($animate): ?>
<script>
(function() {
    var statItems = document.querySelectorAll('#<?php echo esc_js($id); ?> .inmopress-stat-item[data-number]');
    
    function animateValue(element, start, end, duration) {
        var startTime = null;
        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var current = Math.floor(progress * (end - start) + start);
            element.textContent = current.toLocaleString();
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }
        requestAnimationFrame(step);
    }
    
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var target = entry.target;
                var number = parseFloat(target.dataset.number);
                var valueSpan = target.querySelector('.value');
                if (valueSpan && number > 0) {
                    animateValue(valueSpan, 0, number, 2000);
                    observer.unobserve(target);
                }
            }
        });
    }, { threshold: 0.5 });
    
    statItems.forEach(function(item) {
        observer.observe(item);
    });
})();
</script>
<?php endif; ?>
