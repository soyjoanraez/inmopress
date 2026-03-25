<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SMTP Sender - Envía emails mediante PHPMailer
 * 
 * NOTA: Requiere composer install para instalar phpmailer/phpmailer
 * Ejecutar: composer require phpmailer/phpmailer:^6.9
 */
class Inmopress_SMTP_Sender
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Enviar email
     */
    public function send($data)
    {
        // Cargar PHPMailer
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $autoload_paths = array(
                INMOPRESS_EMAILS_PATH . '../../vendor/autoload.php',
                WP_CONTENT_DIR . '/vendor/autoload.php',
                ABSPATH . 'vendor/autoload.php',
            );

            foreach ($autoload_paths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    break;
                }
            }
        }

        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return new WP_Error('phpmailer_not_available', 'PHPMailer no está disponible. Instala con: composer require phpmailer/phpmailer:^6.9');
        }

        try {
            $mailer = new PHPMailer\PHPMailer\PHPMailer(true);

            // Configuración SMTP
            $mailer->isSMTP();
            $mailer->Host = get_option('inmopress_smtp_host', '');
            $mailer->SMTPAuth = true;
            $mailer->Username = get_option('inmopress_smtp_username', '');
            $mailer->Password = get_option('inmopress_smtp_password', '');
            $mailer->SMTPSecure = get_option('inmopress_smtp_encryption', 'tls');
            $mailer->Port = intval(get_option('inmopress_smtp_port', 587));
            $mailer->CharSet = 'UTF-8';

            // Remitente
            $from_email = isset($data['from_email']) ? $data['from_email'] : get_option('inmopress_smtp_from_email', get_bloginfo('admin_email'));
            $from_name = isset($data['from_name']) ? $data['from_name'] : get_option('inmopress_smtp_from_name', get_bloginfo('name'));
            $mailer->setFrom($from_email, $from_name);

            // Destinatario
            $mailer->addAddress($data['to_email'], isset($data['to_name']) ? $data['to_name'] : '');

            // Asunto y cuerpo
            $mailer->Subject = $data['subject'];
            $mailer->isHTML(true);
            $mailer->Body = isset($data['body_html']) ? $data['body_html'] : wpautop($data['body_text']);
            $mailer->AltBody = isset($data['body_text']) ? $data['body_text'] : wp_strip_all_tags($data['body_html']);

            // Adjuntos
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    if (file_exists($attachment)) {
                        $mailer->addAttachment($attachment);
                    }
                }
            }

            // Enviar
            $mailer->send();

            // Registrar en Activity Log
            do_action('inmopress_email_sent', $data);

            return true;

        } catch (Exception $e) {
            error_log('SMTP Error: ' . $e->getMessage());
            return new WP_Error('smtp_error', 'Error al enviar email: ' . $e->getMessage());
        }
    }
}
