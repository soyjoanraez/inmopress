<?php
/**
 * Servicios Inmobiliaria Block Template
 */

$titulo = get_field('titulo');
$descripcion = get_field('descripcion');
$servicios = get_field('servicios');
$columnas = get_field('columnas') ?: 3;
$estilo = get_field('estilo') ?: 'sombra';

if (!$servicios) {
    return;
}

$block_id = 'servicios-' . $block['id'];
$class_name = 'wp-block-inmopress-servicios';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}
?>

<div id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($class_name); ?>">
    <div class="servicios-container">
        <?php if ($titulo || $descripcion): ?>
            <div class="servicios-header">
                <?php if ($titulo): ?>
                    <h2 class="servicios-titulo"><?php echo esc_html($titulo); ?></h2>
                <?php endif; ?>

                <?php if ($descripcion): ?>
                    <div class="servicios-descripcion">
                        <?php echo wp_kses_post($descripcion); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="servicios-grid columns-<?php echo esc_attr($columnas); ?> estilo-<?php echo esc_attr($estilo); ?>">
            <?php foreach ($servicios as $servicio): ?>
                <div class="servicio-item">
                    <?php if ($servicio['icono']): ?>
                        <div class="servicio-icon">
                            <i class="<?php echo esc_attr($servicio['icono']); ?>"></i>
                        </div>
                    <?php endif; ?>

                    <div class="servicio-content">
                        <?php if ($servicio['titulo']): ?>
                            <h3 class="servicio-titulo">
                                <?php if ($servicio['enlace']): ?>
                                    <a href="<?php echo esc_url($servicio['enlace']); ?>"><?php echo esc_html($servicio['titulo']); ?></a>
                                <?php else: ?>
                                    <?php echo esc_html($servicio['titulo']); ?>
                                <?php endif; ?>
                            </h3>
                        <?php endif; ?>

                        <?php if ($servicio['descripcion']): ?>
                            <div class="servicio-descripcion">
                                <?php echo wp_kses_post($servicio['descripcion']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($servicio['enlace'] && $servicio['texto_enlace']): ?>
                            <div class="servicio-enlace">
                                <a href="<?php echo esc_url($servicio['enlace']); ?>" class="servicio-link">
                                    <?php echo esc_html($servicio['texto_enlace']); ?>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>