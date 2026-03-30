<?php
if (!defined('ABSPATH')) {
    exit;
}

$smtp_host = get_option('inmopress_smtp_host', '');
$smtp_port = get_option('inmopress_smtp_port', 587);
$smtp_username = get_option('inmopress_smtp_username', '');
$smtp_password = get_option('inmopress_smtp_password', '');
$smtp_encryption = get_option('inmopress_smtp_encryption', 'tls');
$smtp_from_email = get_option('inmopress_smtp_from_email', get_bloginfo('admin_email'));
$smtp_from_name = get_option('inmopress_smtp_from_name', get_bloginfo('name'));

$imap_host = get_option('inmopress_imap_host', '');
$imap_port = get_option('inmopress_imap_port', 993);
$imap_username = get_option('inmopress_imap_username', '');
$imap_password = get_option('inmopress_imap_password', '');
$imap_enabled = get_option('inmopress_imap_enabled', 0);

$phpmailer_available = class_exists('PHPMailer\PHPMailer\PHPMailer');
$imap_available = function_exists('imap_open');
?>
<div class="wrap">
    <h1>Configuración de Emails</h1>

    <?php if (!$phpmailer_available): ?>
        <div class="notice notice-warning">
            <p><strong>PHPMailer no está instalado.</strong> Ejecuta: <code>composer require phpmailer/phpmailer:^6.9</code></p>
        </div>
    <?php endif; ?>

    <?php if (!$imap_available): ?>
        <div class="notice notice-warning">
            <p><strong>Extensión IMAP no está disponible.</strong> Instala la extensión PHP imap para habilitar la recepción de emails.</p>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field('inmopress_email_settings'); ?>

        <h2>Configuración SMTP (Envío)</h2>
        <table class="form-table">
            <tr>
                <th><label>Servidor SMTP</label></th>
                <td><input type="text" name="smtp_host" value="<?php echo esc_attr($smtp_host); ?>" class="regular-text" placeholder="smtp.gmail.com"></td>
            </tr>
            <tr>
                <th><label>Puerto</label></th>
                <td><input type="number" name="smtp_port" value="<?php echo esc_attr($smtp_port); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label>Usuario</label></th>
                <td><input type="text" name="smtp_username" value="<?php echo esc_attr($smtp_username); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Contraseña</label></th>
                <td><input type="password" name="smtp_password" value="<?php echo esc_attr($smtp_password); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Cifrado</label></th>
                <td>
                    <select name="smtp_encryption">
                        <option value="tls" <?php selected($smtp_encryption, 'tls'); ?>>TLS</option>
                        <option value="ssl" <?php selected($smtp_encryption, 'ssl'); ?>>SSL</option>
                        <option value="" <?php selected($smtp_encryption, ''); ?>>Ninguno</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Email Remitente</label></th>
                <td><input type="email" name="smtp_from_email" value="<?php echo esc_attr($smtp_from_email); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Nombre Remitente</label></th>
                <td><input type="text" name="smtp_from_name" value="<?php echo esc_attr($smtp_from_name); ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2>Configuración IMAP (Recepción)</h2>
        <table class="form-table">
            <tr>
                <th><label>Habilitar IMAP</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="imap_enabled" value="1" <?php checked($imap_enabled, 1); ?>>
                        Activar recepción automática de emails
                    </label>
                </td>
            </tr>
            <tr>
                <th><label>Servidor IMAP</label></th>
                <td><input type="text" name="imap_host" value="<?php echo esc_attr($imap_host); ?>" class="regular-text" placeholder="imap.gmail.com"></td>
            </tr>
            <tr>
                <th><label>Puerto</label></th>
                <td><input type="number" name="imap_port" value="<?php echo esc_attr($imap_port); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label>Usuario</label></th>
                <td><input type="text" name="imap_username" value="<?php echo esc_attr($imap_username); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Contraseña</label></th>
                <td><input type="password" name="imap_password" value="<?php echo esc_attr($imap_password); ?>" class="regular-text"></td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" name="save_email_settings" class="button button-primary">Guardar Configuración</button>
        </p>
    </form>
</div>
