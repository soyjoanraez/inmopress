<?php
/**
 * Stats/Números Destacados Block Template
 */

$stats = get_field('stats');
$layout = get_field('layout') ?: 'grid';
$columns = get_field('columns') ?: 4;
$background_color = get_field('background_color') ?: '#f8f9fa';
$text_color = get_field('text_color') ?: '#333333';

if (!$stats) {
    return;
}

$block_id = 'stats-' . $block['id'];
$class_name = 'wp-block-inmopress-stats-numeros';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}
?>

<div id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($class_name); ?>">
    <style>
        #<?php echo esc_attr($block_id); ?> {
            background-color: <?php echo esc_attr($background_color); ?>;
            color: <?php echo esc_attr($text_color); ?>;
        }
        #<?php echo esc_attr($block_id); ?> .stats-container {
            display: <?php echo $layout === 'horizontal' ? 'flex' : 'grid'; ?>;
            <?php if ($layout === 'grid'): ?>
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            <?php endif; ?>
            gap: 2rem;
            padding: 3rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        @media (max-width: 768px) {
            #<?php echo esc_attr($block_id); ?> .stats-container {
                grid-template-columns: repeat(<?php echo min($columns, 2); ?>, 1fr);
                gap: 1.5rem;
                padding: 2rem 1rem;
            }
        }
    </style>

    <div class="stats-container layout-<?php echo esc_attr($layout); ?> columns-<?php echo esc_attr($columns); ?>">
        <?php foreach ($stats as $stat): ?>
            <div class="stat-item">
                <?php if ($stat['icon']): ?>
                    <div class="stat-icon">
                        <i class="<?php echo esc_attr($stat['icon']); ?>"></i>
                    </div>
                <?php endif; ?>

                <div class="stat-number">
                    <?php echo esc_html($stat['numero']); ?>
                    <?php if ($stat['simbolo']): ?>
                        <span class="stat-symbol"><?php echo esc_html($stat['simbolo']); ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($stat['texto_descriptivo']): ?>
                    <div class="stat-description">
                        <?php echo esc_html($stat['texto_descriptivo']); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>