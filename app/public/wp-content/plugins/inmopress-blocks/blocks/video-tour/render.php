<?php
/**
 * Video Tour Block Template.
 */

$id = 'video-tour-' . $block['id'];
$className = 'inmopress-video';
if (!empty($block['className']))
    $className .= ' ' . $block['className'];

$video_url = get_field('video_url');
$tour_url = get_field('tour_virtual_url'); // Checking alternative name
if (!$tour_url)
    $tour_url = get_field('tour_url');

// Determine what to show. Tour takes precedence if both exist? Or show tabs? 
// For simplicity, showing both stacked if both exist, or checking content.
?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <?php if ($video_url): ?>
        <div class="video-container" style="margin-bottom: 30px;">
            <h3 class="video-title">Video del Inmueble</h3>
            <div class="video-responsive"
                style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%;">
                <?php
                // Use WP oEmbed to get the iframe
                echo wp_oembed_get($video_url);
                ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($tour_url): ?>
        <div class="tour-container">
            <h3 class="tour-title">Tour Virtual 360º</h3>
            <div class="tour-responsive"
                style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%;">
                <iframe src="<?php echo esc_url($tour_url); ?>" frameborder="0" allowfullscreen
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$video_url && !$tour_url): ?>
        <?php if (is_admin()): ?>
            <div class="video-placeholder" style="background: #f3f4f6; padding: 40px; text-align: center; border-radius: 8px;">
                <p>🎥 Sin video ni tour virtual asignado</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>