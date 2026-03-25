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

<body class="format-a4-horizontal">
    <div class="print-container format-a4-horizontal">

        <!-- Header -->
        <div class="print-header">
            <div class="print-logo">
                <h2>INMOPRESS</h2>
            </div>
            <div class="print-ref">REF:
                <?php echo esc_html($referencia); ?>
            </div>
        </div>

        <!-- Left Column: Image -->
        <?php if ($thumbnail_url): ?>
            <img src="<?php echo esc_url($thumbnail_url); ?>" class="print-main-image" alt="Main Image"
                style="width: 100%; height: 100%; object-fit: cover;">
        <?php endif; ?>

        <!-- Right Column: Sidebar -->
        <div class="print-sidebar">
            <h1 class="print-title" style="font-size: 1.5rem; margin-bottom: 10px;">
                <?php echo esc_html($title); ?>
            </h1>
            <div class="print-price" style="color: var(--primary-color); margin-bottom: 20px;">
                <?php echo esc_html($price); ?>
            </div>

            <div class="print-features" style="flex-direction: column; gap: 15px; border: none; margin: 0; padding: 0;">
                <?php if ($dormitorios): ?>
                    <div class="feature-item" style="text-align: left; display: flex; align-items: center; gap: 10px;">
                        <span>
                            <?php echo esc_html($dormitorios); ?>
                        </span> Dormitorios
                    </div>
                <?php endif; ?>
                <?php if ($banos): ?>
                    <div class="feature-item" style="text-align: left; display: flex; align-items: center; gap: 10px;">
                        <span>
                            <?php echo esc_html($banos); ?>
                        </span> Baños
                    </div>
                <?php endif; ?>
                <?php if ($superficie): ?>
                    <div class="feature-item" style="text-align: left; display: flex; align-items: center; gap: 10px;">
                        <span>
                            <?php echo esc_html($superficie); ?> m²
                        </span> Construidos
                    </div>
                <?php endif; ?>
            </div>

            <div class="print-description" style="padding: 20px 0;">
                <p><strong>Ubicación:</strong>
                    <?php echo esc_html($ciudad); ?>
                </p>
                <?php echo wp_trim_words($descripcion, 40); ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="print-footer">
            <div class="footer-contact">
                <h3>Contacto</h3>
                <p>📞 +34 600 000 000 | 📧 info@inmopress.com</p>
            </div>
            <div class="footer-qr" style="display: flex; align-items: center; gap: 10px;">
                <p style="margin: 0;">Ver online</p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode(get_permalink($post_id)); ?>"
                    alt="QR Code" style="width: 80px; height: 80px;">
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