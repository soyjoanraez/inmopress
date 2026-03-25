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

<body class="format-a3-vertical">
    <div class="print-container format-a3-vertical">
        <!-- Header -->
        <div class="print-header" style="padding: 30px;">
            <div class="print-logo">
                <h1 style="font-size: 2.5rem; margin: 0;">INMOPRESS PREMIER</h1>
            </div>
            <div class="print-ref" style="font-size: 1.5rem;">REF:
                <?php echo esc_html($referencia); ?>
            </div>
        </div>

        <!-- Main Image -->
        <?php if ($thumbnail_url): ?>
            <img src="<?php echo esc_url($thumbnail_url); ?>" class="print-main-image" alt="Main Image"
                style="height: 50vh;">
        <?php endif; ?>

        <!-- Title Bar -->
        <div class="print-title-bar" style="padding: 20px 40px;">
            <h1 class="print-title" style="font-size: 2.8rem;">
                <?php echo esc_html($title); ?>
            </h1>
        </div>

        <div
            style="background: var(--primary-color); color: white; text-align: center; padding: 15px; font-size: 3rem; font-weight: 900;">
            <?php echo esc_html($price); ?>
        </div>

        <!-- Features Icons -->
        <div class="print-features" style="padding: 30px 40px;">
            <?php if ($dormitorios): ?>
                <div class="feature-item">
                    <span style="font-size: 2.5rem;">
                        <?php echo esc_html($dormitorios); ?>
                    </span>
                    <div style="font-size: 1.5rem;">Dormitorios</div>
                </div>
            <?php endif; ?>
            <?php if ($banos): ?>
                <div class="feature-item">
                    <span style="font-size: 2.5rem;">
                        <?php echo esc_html($banos); ?>
                    </span>
                    <div style="font-size: 1.5rem;">Baños</div>
                </div>
            <?php endif; ?>
            <?php if ($superficie): ?>
                <div class="feature-item">
                    <span style="font-size: 2.5rem;">
                        <?php echo esc_html($superficie); ?> m²
                    </span>
                    <div style="font-size: 1.5rem;">Construidos</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <div class="print-description" style="font-size: 1.4rem; padding: 20px 40px;">
            <p><strong>Ubicación:</strong>
                <?php echo esc_html($ciudad); ?>
            </p>
            <?php echo wp_trim_words($descripcion, 80); ?>
        </div>

        <!-- Secondary Images (Gallery) -->
        <div class="grid-gallery" style="grid-template-columns: repeat(3, 1fr); padding: 0 40px;">
            <?php
            $gallery = get_field('galeria', $post_id);
            if ($gallery && is_array($gallery)):
                $count = 0;
                foreach ($gallery as $image_id):
                    if ($count >= 3)
                        break;
                    $img_src = wp_get_attachment_image_url($image_id, 'large');
                    ?>
                    <img src="<?php echo esc_url($img_src); ?>" alt="Gallery" style="height: 180px;">
                    <?php
                    $count++;
                endforeach;
            endif;
            ?>
        </div>

        <!-- Footer with QR -->
        <div class="print-footer" style="padding: 30px 40px;">
            <div class="footer-contact">
                <h3 style="font-size: 1.8rem;">Más información:</h3>
                <p style="font-size: 1.4rem;">📞 +34 600 000 000</p>
                <p style="font-size: 1.4rem;">📧 info@inmopress.com</p>
            </div>
            <div class="footer-qr">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode(get_permalink($post_id)); ?>"
                    alt="QR Code" style="width: 150px; height: 150px;">
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