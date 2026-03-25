<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo esc_html($vars['subject_fallback']); ?></title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2933; background: #f5f7fa; padding: 24px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="padding: 24px;">
                <h2 style="margin: 0 0 8px;">Hola <?php echo esc_html($vars['client_name']); ?>,</h2>
                <p style="margin: 0 0 16px;">Tenemos buenas noticias sobre una propiedad que te interesa.</p>
                <h3 style="margin: 0 0 8px;"><?php echo esc_html($vars['property_title']); ?></h3>
                <?php if (!empty($vars['property_city'])) : ?>
                    <p style="margin: 0 0 12px;">Ciudad: <?php echo esc_html($vars['property_city']); ?></p>
                <?php endif; ?>
                <p style="margin: 0 0 12px;"><strong>Antes:</strong> <?php echo esc_html($vars['old_price']); ?></p>
                <p style="margin: 0 0 12px;"><strong>Ahora:</strong> <?php echo esc_html($vars['new_price']); ?></p>
                <p style="margin: 0 0 12px;">Te ahorras <?php echo esc_html($vars['price_diff']); ?> (<?php echo esc_html($vars['drop_pct']); ?>%).</p>

                <?php if (!empty($vars['property_image'])) : ?>
                    <p style="margin: 16px 0;">
                        <img src="<?php echo esc_url($vars['property_image']); ?>" alt="<?php echo esc_attr($vars['property_title']); ?>" style="max-width: 100%; height: auto; border-radius: 6px;">
                    </p>
                <?php endif; ?>

                <?php if (!empty($vars['property_description'])) : ?>
                    <p style="margin: 0 0 16px;"><?php echo wp_kses_post($vars['property_description']); ?></p>
                <?php endif; ?>

                <p style="margin: 0 0 16px;">
                    <a href="<?php echo esc_url($vars['property_url']); ?>" style="background: #2563eb; color: #ffffff; padding: 12px 18px; border-radius: 4px; text-decoration: none; display: inline-block;">Ver detalles</a>
                </p>

                <?php if (!empty($vars['agent_name'])) : ?>
                    <p style="margin: 0;">¿Quieres visitarla? Contacta con <?php echo esc_html($vars['agent_name']); ?><?php echo !empty($vars['agent_phone']) ? ' (' . esc_html($vars['agent_phone']) . ')' : ''; ?>.</p>
                <?php endif; ?>

                <?php if (!empty($vars['unsubscribe_url'])) : ?>
                    <p style="margin-top: 24px; font-size: 12px; color: #6b7280;">
                        <a href="<?php echo esc_url($vars['unsubscribe_url']); ?>" style="color: #6b7280;">No quiero recibir alertas de bajada de precio</a>
                    </p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</body>
</html>
