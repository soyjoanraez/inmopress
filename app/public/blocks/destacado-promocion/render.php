<?php
/**
 * Destacado Promoción Block Template
 */

$promocion = get_field('promocion');
$mostrar = get_field('mostrar') ?: array();
$layout = get_field('layout') ?: 'completo';

if (!$promocion) {
    echo '<div class="wp-block-inmopress-destacado-promocion-empty">Selecciona una promoción para mostrar</div>';
    return;
}

$block_id = 'promocion-' . $block['id'];
$class_name = 'wp-block-inmopress-destacado-promocion';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}

// Obtener datos de la promoción
$promocion_id = $promocion->ID;
$titulo = get_the_title($promocion_id);
$descripcion = get_field('descripcion', $promocion_id);
$galeria = get_field('galeria', $promocion_id);
$plano = get_field('plano', $promocion_id);
$precio_desde = get_field('precio_desde', $promocion_id);
$ubicacion = get_field('ubicacion', $promocion_id);
$estado = get_field('estado', $promocion_id);
$fecha_entrega = get_field('fecha_entrega', $promocion_id);

// Obtener inmuebles de la promoción
$inmuebles_relacionados = array();
if (in_array('inmuebles', $mostrar)) {
    $args = array(
        'post_type' => 'inmueble',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'promocion',
                'value' => $promocion_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 6
    );
    $inmuebles_relacionados = get_posts($args);
}
?>

<div id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($class_name); ?>">
    <div class="promocion-container layout-<?php echo esc_attr($layout); ?>">

        <?php if ($layout === 'completo' || $layout === 'compacto'): ?>
            <!-- Sección Principal -->
            <div class="promocion-main">
                <?php if (in_array('galeria', $mostrar) && $galeria): ?>
                    <div class="promocion-galeria">
                        <?php if (count($galeria) === 1): ?>
                            <div class="galeria-single">
                                <img src="<?php echo esc_url($galeria[0]['sizes']['large']); ?>"
                                     alt="<?php echo esc_attr($galeria[0]['alt'] ?: $titulo); ?>" />
                            </div>
                        <?php else: ?>
                            <div class="galeria-multiple">
                                <div class="galeria-principal">
                                    <img src="<?php echo esc_url($galeria[0]['sizes']['large']); ?>"
                                         alt="<?php echo esc_attr($galeria[0]['alt'] ?: $titulo); ?>"
                                         data-index="0" />
                                </div>
                                <div class="galeria-miniaturas">
                                    <?php foreach (array_slice($galeria, 0, 4) as $index => $imagen): ?>
                                        <div class="miniatura <?php echo $index === 0 ? 'active' : ''; ?>"
                                             data-index="<?php echo $index; ?>">
                                            <img src="<?php echo esc_url($imagen['sizes']['medium']); ?>"
                                                 alt="<?php echo esc_attr($imagen['alt']); ?>" />
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($galeria) > 4): ?>
                                        <div class="galeria-mas">
                                            +<?php echo count($galeria) - 4; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="promocion-info">
                    <div class="promocion-header">
                        <h2 class="promocion-titulo"><?php echo esc_html($titulo); ?></h2>

                        <?php if ($estado): ?>
                            <span class="promocion-estado estado-<?php echo esc_attr(strtolower($estado)); ?>">
                                <?php echo esc_html($estado); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($ubicacion): ?>
                        <div class="promocion-ubicacion">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo esc_html($ubicacion); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($precio_desde): ?>
                        <div class="promocion-precio">
                            <span class="precio-label">Desde</span>
                            <span class="precio-valor"><?php echo esc_html($precio_desde); ?>€</span>
                        </div>
                    <?php endif; ?>

                    <?php if (in_array('descripcion', $mostrar) && $descripcion): ?>
                        <div class="promocion-descripcion">
                            <?php echo wp_kses_post($descripcion); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($fecha_entrega): ?>
                        <div class="promocion-entrega">
                            <strong>Fecha de entrega:</strong> <?php echo esc_html($fecha_entrega); ?>
                        </div>
                    <?php endif; ?>

                    <div class="promocion-acciones">
                        <a href="<?php echo get_permalink($promocion_id); ?>" class="btn btn-primary">
                            Ver Promoción Completa
                        </a>
                        <a href="#contacto" class="btn btn-secondary">
                            Solicitar Información
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($layout === 'lateral'): ?>
            <!-- Layout Lateral -->
            <div class="promocion-lateral">
                <div class="promocion-imagen">
                    <?php if (in_array('galeria', $mostrar) && $galeria): ?>
                        <img src="<?php echo esc_url($galeria[0]['sizes']['large']); ?>"
                             alt="<?php echo esc_attr($galeria[0]['alt'] ?: $titulo); ?>" />
                    <?php endif; ?>
                </div>
                <div class="promocion-contenido">
                    <h3 class="promocion-titulo"><?php echo esc_html($titulo); ?></h3>

                    <?php if ($ubicacion): ?>
                        <div class="promocion-ubicacion">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo esc_html($ubicacion); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (in_array('descripcion', $mostrar) && $descripcion): ?>
                        <div class="promocion-descripcion-corta">
                            <?php echo wp_trim_words(wp_strip_all_tags($descripcion), 20); ?>
                        </div>
                    <?php endif; ?>

                    <a href="<?php echo get_permalink($promocion_id); ?>" class="btn-lateral">
                        Ver más <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array('plano', $mostrar) && $plano): ?>
            <!-- Sección Plano -->
            <div class="promocion-plano">
                <h3>Plano de la Promoción</h3>
                <div class="plano-imagen">
                    <img src="<?php echo esc_url($plano['sizes']['large']); ?>"
                         alt="Plano de <?php echo esc_attr($titulo); ?>" />
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array('inmuebles', $mostrar) && !empty($inmuebles_relacionados)): ?>
            <!-- Inmuebles de la Promoción -->
            <div class="promocion-inmuebles">
                <h3>Inmuebles Disponibles</h3>
                <div class="inmuebles-grid">
                    <?php foreach ($inmuebles_relacionados as $inmueble): ?>
                        <div class="inmueble-card">
                            <?php
                            $imagen_principal = get_field('imagen_principal', $inmueble->ID);
                            $precio = get_field('precio', $inmueble->ID);
                            $habitaciones = get_field('habitaciones', $inmueble->ID);
                            $superficie = get_field('superficie', $inmueble->ID);
                            ?>

                            <?php if ($imagen_principal): ?>
                                <div class="inmueble-imagen">
                                    <img src="<?php echo esc_url($imagen_principal['sizes']['medium']); ?>"
                                         alt="<?php echo esc_attr(get_the_title($inmueble->ID)); ?>" />
                                </div>
                            <?php endif; ?>

                            <div class="inmueble-info">
                                <h4 class="inmueble-titulo">
                                    <a href="<?php echo get_permalink($inmueble->ID); ?>">
                                        <?php echo esc_html(get_the_title($inmueble->ID)); ?>
                                    </a>
                                </h4>

                                <div class="inmueble-detalles">
                                    <?php if ($superficie): ?>
                                        <span class="detalle">
                                            <i class="fas fa-ruler-combined"></i>
                                            <?php echo esc_html($superficie); ?>m²
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($habitaciones): ?>
                                        <span class="detalle">
                                            <i class="fas fa-bed"></i>
                                            <?php echo esc_html($habitaciones); ?> hab.
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($precio): ?>
                                    <div class="inmueble-precio">
                                        <?php echo esc_html(number_format($precio, 0, ',', '.')); ?>€
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Galería de imágenes
    const miniaturas = document.querySelectorAll('#<?php echo esc_js($block_id); ?> .miniatura');
    const imagenPrincipal = document.querySelector('#<?php echo esc_js($block_id); ?> .galeria-principal img');

    if (miniaturas.length && imagenPrincipal) {
        const imagenes = <?php echo json_encode(array_column($galeria ?: array(), 'sizes')); ?>;

        miniaturas.forEach(function(miniatura) {
            miniatura.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);

                // Actualizar imagen principal
                if (imagenes[index]) {
                    imagenPrincipal.src = imagenes[index].large;
                }

                // Actualizar clases activas
                miniaturas.forEach(m => m.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
});
</script>