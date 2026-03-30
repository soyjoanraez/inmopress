<?php if (!defined('ABSPATH'))
    exit; ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>
        <?php echo esc_html($title); ?>
    </title>
    <link rel="stylesheet" href="<?php echo INMOPRESS_PRINTABLES_URL . 'assets/css/print-styles.css'; ?>">
</head>

<body class="format-a4-vertical">
    <div class="print-container format-a4-vertical">
        <!-- Header -->
        <div class="print-header">
            <div class="print-logo">
                <!-- Placeholder for logo, replace with dynamic option later -->
                <h2>INMOPRESS</h2>
            </div>
            <div class="print-ref">REF:
                <?php echo esc_html($referencia); ?>
            </div>
        </div>

        <!-- Main Image -->
        <?php if ($thumbnail_url): ?>
            <img src="<?php echo esc_url($thumbnail_url); ?>" class="print-main-image" alt="Main Image">
        <?php endif; ?>

        <!-- Title Bar -->
        <div class="print-title-bar">
            <h1 class="print-title">
                <?php echo esc_html($title); ?>
            </h1>
            <div class="print-price">
                <?php echo esc_html($price); ?>
            </div>
        </div>

        <!-- Features Icons -->
        <div class="print-features">
            <?php if ($dormitorios): ?>
                <div class="feature-item">
                    <span>
                        <?php echo esc_html($dormitorios); ?>
                    </span> Dormitorios
                </div>
            <?php endif; ?>
            <?php if ($banos): ?>
                <div class="feature-item">
                    <span>
                        <?php echo esc_html($banos); ?>
                    </span> Baños
                </div>
            <?php endif; ?>
            <?php if ($superficie): ?>
                <div class="feature-item">
                    <span>
                        <?php echo esc_html($superficie); ?> m²
                    </span> Construidos
                </div>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <div class="print-description">
            <p><strong>Ubicación:</strong>
                <?php echo esc_html($ciudad); ?>
            </p>
            <?php echo wp_trim_words($descripcion, 60); ?>
        </div>

        <!-- Secondary Images (Small Gallery) -->
        <div class="grid-gallery">
            <?php
            $gallery = get_field('galeria', $post_id);
            if ($gallery && is_array($gallery)):
                $count = 0;
                foreach ($gallery as $image_id):
                    if ($count >= 2)
                        break;
                    $img_src = wp_get_attachment_image_url($image_id, 'medium');
                    ?>
                    <img src="<?php echo esc_url($img_src); ?>" alt="Gallery">
                    <?php
                    $count++;
                endforeach;
            endif;
            ?>
        </div>

        <!-- Footer with QR -->
        <div class="print-footer">
            <div class="footer-contact">
                <h3>Más información:</h3>
                <p>📞 +34 600 000 000</p>
                <p>📧 info@inmopress.com</p>
                <p>📍 Calle Principal 123, Ciudad</p>
            </div>
            <div class="footer-qr">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode(get_permalink($post_id)); ?>"
                    alt="QR Code">
                <p>Escanéame</p>
            </div>
        </div>
    </div>

    <div class="no-print"
        style="position: fixed; top: 20px; right: 20px; background: white; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.5); border-radius: 8px; z-index: 9999;">
        <button onclick="window.print()"
            style="font-size: 1.2rem; padding: 10px 20px; background: #0073aa; color: white; border: none; cursor: pointer; border-radius: 4px;">🖨️
            Imprimir</button>
        <button onclick="window.close()"
            style="font-size: 1.2rem; padding: 10px 20px; background: #dc3232; color: white; border: none; cursor: pointer; border-radius: 4px;">❌
            Cerrar</button>
    </div>
</body>

</html>