<?php
/**
 * Zonas Destacadas Block Template
 */

$titulo = get_field('titulo');
$descripcion = get_field('descripcion');
$zonas = get_field('zonas');
$layout = get_field('layout') ?: 'grid';
$mostrar_contador = get_field('mostrar_contador');
$columnas = get_field('columnas') ?: 3;

if (!$zonas) {
    return;
}

$block_id = 'zonas-' . $block['id'];
$class_name = 'wp-block-inmopress-zonas-destacadas';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}

// Función para obtener contador de inmuebles por zona
function get_properties_count_by_zone($zona_id) {
    if (!$zona_id) return 0;

    $args = array(
        'post_type' => 'inmueble', // Asumiendo que el CPT se llama 'inmueble'
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'ciudad', // Campo ACF de ciudad en inmuebles
                'value' => $zona_id,
                'compare' => '='
            )
        ),
        'fields' => 'ids'
    );

    $query = new WP_Query($args);
    return $query->found_posts;
}
?>

<div id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($class_name); ?>">
    <div class="zonas-container">
        <?php if ($titulo || $descripcion): ?>
            <div class="zonas-header">
                <?php if ($titulo): ?>
                    <h2 class="zonas-titulo"><?php echo esc_html($titulo); ?></h2>
                <?php endif; ?>

                <?php if ($descripcion): ?>
                    <div class="zonas-descripcion">
                        <?php echo wp_kses_post($descripcion); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="zonas-content layout-<?php echo esc_attr($layout); ?> columns-<?php echo esc_attr($columnas); ?>">
            <?php if ($layout === 'carrusel'): ?>
                <div class="zonas-carrusel" data-columnas="<?php echo esc_attr($columnas); ?>">
            <?php endif; ?>

            <?php foreach ($zonas as $zona): ?>
                <div class="zona-item">
                    <div class="zona-image">
                        <?php if ($zona['imagen_zona']): ?>
                            <img src="<?php echo esc_url($zona['imagen_zona']['url']); ?>"
                                 alt="<?php echo esc_attr($zona['imagen_zona']['alt'] ?: $zona['nombre_zona']); ?>" />
                        <?php endif; ?>

                        <div class="zona-overlay">
                            <div class="zona-content">
                                <h3 class="zona-nombre">
                                    <?php if ($zona['enlace_zona']): ?>
                                        <a href="<?php echo esc_url($zona['enlace_zona']); ?>">
                                            <?php echo esc_html($zona['nombre_zona'] ?: $zona['ciudad']->name); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo esc_html($zona['nombre_zona'] ?: ($zona['ciudad'] ? $zona['ciudad']->name : '')); ?>
                                    <?php endif; ?>
                                </h3>

                                <?php if ($zona['texto_custom']): ?>
                                    <div class="zona-texto">
                                        <?php echo wp_kses_post($zona['texto_custom']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($mostrar_contador && $zona['ciudad']): ?>
                                    <?php
                                    $count = get_properties_count_by_zone($zona['ciudad']->term_id);
                                    if ($count > 0):
                                    ?>
                                        <div class="zona-contador">
                                            <span class="contador-numero"><?php echo esc_html($count); ?></span>
                                            <span class="contador-texto">
                                                <?php echo $count === 1 ? 'inmueble' : 'inmuebles'; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($zona['enlace_zona']): ?>
                                    <div class="zona-enlace">
                                        <a href="<?php echo esc_url($zona['enlace_zona']); ?>" class="zona-link">
                                            Ver inmuebles
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($layout === 'carrusel'): ?>
                </div>
                <div class="carrusel-navigation">
                    <button class="carrusel-prev"><i class="fas fa-chevron-left"></i></button>
                    <button class="carrusel-next"><i class="fas fa-chevron-right"></i></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($layout === 'carrusel'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const carrusel = document.querySelector('#<?php echo esc_js($block_id); ?> .zonas-carrusel');
    const prevBtn = document.querySelector('#<?php echo esc_js($block_id); ?> .carrusel-prev');
    const nextBtn = document.querySelector('#<?php echo esc_js($block_id); ?> .carrusel-next');

    if (carrusel && prevBtn && nextBtn) {
        let currentIndex = 0;
        const items = carrusel.children;
        const totalItems = items.length;
        const columnas = parseInt(carrusel.dataset.columnas) || 3;

        function updateCarrusel() {
            const translateX = -currentIndex * (100 / columnas);
            carrusel.style.transform = `translateX(${translateX}%)`;
        }

        prevBtn.addEventListener('click', function() {
            currentIndex = Math.max(0, currentIndex - 1);
            updateCarrusel();
        });

        nextBtn.addEventListener('click', function() {
            const maxIndex = Math.max(0, totalItems - columnas);
            currentIndex = Math.min(maxIndex, currentIndex + 1);
            updateCarrusel();
        });
    }
});
</script>
<?php endif; ?>