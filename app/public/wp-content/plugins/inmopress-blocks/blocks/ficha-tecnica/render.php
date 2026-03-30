<?php
/**
 * Ficha Técnica Block Template.
 */

$id = 'inmopress-specs-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'inmopress-specs';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Layout options
$layout = get_field('layout') ?: '3col'; // 3col, 2col
$style = get_field('estilo') ?: 'cards'; // cards, list

$className .= ' cols-' . ($layout === '2col' ? '2' : '3');
$className .= ' style-' . $style;

// Data Values
$ref = get_field('referencia');
$surface = get_field('superficie_construida');
$rooms = get_field('dormitorios');
$baths = get_field('banos');
$year = get_field('ano');
// $status = get_field('estado'); // Es un select value, necesitaríamos el label
$energy = get_field('certificacion_energetica');

// Helper to get label for select fields (simplified for Phase 1)
function get_status_label($value)
{
    $labels = [
        'nuevo' => 'Obra Nueva',
        'reformado' => 'Reformado',
        'buen_estado' => 'Buen Estado',
        'reformar' => 'A reformar'
    ];
    return isset($labels[$value]) ? $labels[$value] : ucfirst($value);
}
$status_label = get_status_label(get_field('estado'));

?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <div class="inmopress-specs-grid">

        <?php if ($ref): ?>
            <div class="inmopress-spec-item">
                <div class="inmopress-spec-icon">
                    <span class="dashicons dashicons-tag"></span>
                </div>
                <div class="inmopress-spec-content">
                    <span class="inmopress-spec-label">
                        <?php _e('Referencia', 'inmopress'); ?>
                    </span>
                    <span class="inmopress-spec-value">
                        <?php echo esc_html($ref); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($surface): ?>
            <div class="inmopress-spec-item">
                <div class="inmopress-spec-icon">
                    <span class="dashicons dashicons-fullscreen"></span>
                </div>
                <div class="inmopress-spec-content">
                    <span class="inmopress-spec-label">
                        <?php _e('Superficie', 'inmopress'); ?>
                    </span>
                    <span class="inmopress-spec-value">
                        <?php echo esc_html($surface); ?> m²
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($rooms): ?>
            <div class="inmopress-spec-item">
                <div class="inmopress-spec-icon">
                    <span class="dashicons dashicons-admin-home"></span>
                </div>
                <div class="inmopress-spec-content">
                    <span class="inmopress-spec-label">
                        <?php _e('Habitaciones', 'inmopress'); ?>
                    </span>
                    <span class="inmopress-spec-value">
                        <?php echo esc_html($rooms); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($baths): ?>
            <div class="inmopress-spec-item">
                <div class="inmopress-spec-icon">
                    <span class="dashicons dashicons-drop"></span>
                </div>
                <div class="inmopress-spec-content">
                    <span class="inmopress-spec-label">
                        <?php _e('Baños', 'inmopress'); ?>
                    </span>
                    <span class="inmopress-spec-value">
                        <?php echo esc_html($baths); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($year): ?>
            <div class="inmopress-spec-item">
                <div class="inmopress-spec-icon">
                    <span class="dashicons dashicons-calendar-alt"></span>
                </div>
                <div class="inmopress-spec-content">
                    <span class="inmopress-spec-label">
                        <?php _e('Año', 'inmopress'); ?>
                    </span>
                    <span class="inmopress-spec-value">
                        <?php echo esc_html($year); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($status_label)): ?>
            <div class="inmopress-spec-item">
                <div class="inmopress-spec-icon">
                    <span class="dashicons dashicons-hammer"></span>
                </div>
                <div class="inmopress-spec-content">
                    <span class="inmopress-spec-label">
                        <?php _e('Estado', 'inmopress'); ?>
                    </span>
                    <span class="inmopress-spec-value">
                        <?php echo esc_html($status_label); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($energy): ?>
            <div class="inmopress-spec-item">
                <div class="inmopress-spec-icon">
                    <span class="dashicons dashicons-lightbulb"></span>
                </div>
                <div class="inmopress-spec-content">
                    <span class="inmopress-spec-label">
                        <?php _e('Certificado', 'inmopress'); ?>
                    </span>
                    <span class="inmopress-spec-value">
                        <?php echo strtoupper(esc_html($energy)); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>