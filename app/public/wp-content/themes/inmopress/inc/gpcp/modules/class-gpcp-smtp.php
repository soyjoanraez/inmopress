<?php
/**
 * GPCP SMTP Module
 *
 * SMTP email configuration
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP SMTP class
 */
class GPCP_SMTP
{
    /**
     * Instance of this class
     *
     * @var GPCP_SMTP
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_SMTP
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('phpmailer_init', array($this, 'configure_smtp'));
        add_action('wp_ajax_gpcp_test_email', array($this, 'test_email_ajax'));
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_smtp', 'gpcp_smtp_enabled');
        register_setting('gpcp_smtp', 'gpcp_smtp_host');
        register_setting('gpcp_smtp', 'gpcp_smtp_port');
        register_setting('gpcp_smtp', 'gpcp_smtp_encryption');
        register_setting('gpcp_smtp', 'gpcp_smtp_auth');
        register_setting('gpcp_smtp', 'gpcp_smtp_username');
        register_setting('gpcp_smtp', 'gpcp_smtp_password');
        register_setting('gpcp_smtp', 'gpcp_smtp_from_name');
        register_setting('gpcp_smtp', 'gpcp_smtp_from_email');
    }

    /**
     * Configure SMTP
     */
    public function configure_smtp($phpmailer)
    {
        if (!get_option('gpcp_smtp_enabled', false)) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = get_option('gpcp_smtp_host', '');
        $phpmailer->Port = get_option('gpcp_smtp_port', 587);
        $phpmailer->SMTPSecure = get_option('gpcp_smtp_encryption', 'tls');
        
        if (get_option('gpcp_smtp_auth', true)) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = get_option('gpcp_smtp_username', '');
            $phpmailer->Password = get_option('gpcp_smtp_password', '');
        }

        $from_name = get_option('gpcp_smtp_from_name', get_bloginfo('name'));
        $from_email = get_option('gpcp_smtp_from_email', get_option('admin_email'));
        
        $phpmailer->setFrom($from_email, $from_name);
    }

    /**
     * Test email via AJAX
     */
    public function test_email_ajax()
    {
        check_ajax_referer('gpcp_test_email', 'nonce');

        $to = isset($_POST['email']) ? sanitize_email($_POST['email']) : get_option('admin_email');
        
        $subject = __('Test Email - InmoPress Pro', 'gpcp');
        $message = __('Este es un email de prueba desde InmoPress Pro. Si recibes este mensaje, la configuración SMTP está funcionando correctamente.', 'gpcp');
        
        $sent = wp_mail($to, $subject, $message);
        
        if ($sent) {
            wp_send_json_success(array('message' => __('Email enviado correctamente. Revisa tu bandeja de entrada.', 'gpcp')));
        } else {
            wp_send_json_error(array('message' => __('Error al enviar el email. Revisa la configuración SMTP.', 'gpcp')));
        }
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_smtp_save'])) {
            check_admin_referer('gpcp_smtp_save');
            
            update_option('gpcp_smtp_enabled', isset($_POST['gpcp_smtp_enabled']));
            update_option('gpcp_smtp_host', sanitize_text_field($_POST['gpcp_smtp_host']));
            update_option('gpcp_smtp_port', intval($_POST['gpcp_smtp_port']));
            update_option('gpcp_smtp_encryption', sanitize_text_field($_POST['gpcp_smtp_encryption']));
            update_option('gpcp_smtp_auth', isset($_POST['gpcp_smtp_auth']));
            update_option('gpcp_smtp_username', sanitize_text_field($_POST['gpcp_smtp_username']));
            update_option('gpcp_smtp_password', sanitize_text_field($_POST['gpcp_smtp_password']));
            update_option('gpcp_smtp_from_name', sanitize_text_field($_POST['gpcp_smtp_from_name']));
            update_option('gpcp_smtp_from_email', sanitize_email($_POST['gpcp_smtp_from_email']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $enabled = get_option('gpcp_smtp_enabled', false);
        $host = get_option('gpcp_smtp_host', '');
        $port = get_option('gpcp_smtp_port', 587);
        $encryption = get_option('gpcp_smtp_encryption', 'tls');
        $auth = get_option('gpcp_smtp_auth', true);
        $username = get_option('gpcp_smtp_username', '');
        $password = get_option('gpcp_smtp_password', '');
        $from_name = get_option('gpcp_smtp_from_name', get_bloginfo('name'));
        $from_email = get_option('gpcp_smtp_from_email', get_option('admin_email'));
        ?>
        <div class="wrap">
            <h1><?php _e('Configuración SMTP', 'gpcp'); ?></h1>
            <p><?php _e('Configura el envío de emails a través de un servidor SMTP personalizado.', 'gpcp'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('gpcp_smtp_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Activar SMTP', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_smtp_enabled" value="1" <?php checked($enabled); ?> />
                                <?php _e('Usar servidor SMTP para enviar emails', 'gpcp'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_smtp_host"><?php _e('Servidor SMTP', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gpcp_smtp_host" name="gpcp_smtp_host" value="<?php echo esc_attr($host); ?>" class="regular-text" placeholder="smtp.example.com" />
                            <p class="description"><?php _e('Dirección del servidor SMTP (ej: smtp.gmail.com, smtp.mailtrap.io)', 'gpcp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_smtp_port"><?php _e('Puerto', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="gpcp_smtp_port" name="gpcp_smtp_port" value="<?php echo esc_attr($port); ?>" class="small-text" />
                            <p class="description"><?php _e('Puerto SMTP (587 para TLS, 465 para SSL, 25 para sin encriptación)', 'gpcp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_smtp_encryption"><?php _e('Encriptación', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <select id="gpcp_smtp_encryption" name="gpcp_smtp_encryption">
                                <option value="none" <?php selected($encryption, 'none'); ?>><?php _e('Ninguna', 'gpcp'); ?></option>
                                <option value="ssl" <?php selected($encryption, 'ssl'); ?>><?php _e('SSL', 'gpcp'); ?></option>
                                <option value="tls" <?php selected($encryption, 'tls'); ?>><?php _e('TLS', 'gpcp'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Autenticación', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_smtp_auth" value="1" <?php checked($auth); ?> />
                                <?php _e('El servidor requiere autenticación', 'gpcp'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_smtp_username"><?php _e('Usuario', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gpcp_smtp_username" name="gpcp_smtp_username" value="<?php echo esc_attr($username); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_smtp_password"><?php _e('Contraseña', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="gpcp_smtp_password" name="gpcp_smtp_password" value="<?php echo esc_attr($password); ?>" class="regular-text" />
                            <p class="description"><?php _e('La contraseña se guarda en texto plano. Usa contraseñas de aplicación si es posible.', 'gpcp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_smtp_from_name"><?php _e('Nombre del Remitente', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gpcp_smtp_from_name" name="gpcp_smtp_from_name" value="<?php echo esc_attr($from_name); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_smtp_from_email"><?php _e('Email del Remitente', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="gpcp_smtp_from_email" name="gpcp_smtp_from_email" value="<?php echo esc_attr($from_email); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_smtp_save'); ?>
            </form>

            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle"><?php _e('Probar Configuración', 'gpcp'); ?></h2>
                <div class="inside">
                    <p><?php _e('Envía un email de prueba para verificar que la configuración SMTP funciona correctamente.', 'gpcp'); ?></p>
                    <p>
                        <input type="email" id="gpcp-test-email" value="<?php echo esc_attr(get_option('admin_email')); ?>" class="regular-text" placeholder="<?php _e('Email de destino', 'gpcp'); ?>" />
                        <button type="button" id="gpcp-send-test-email" class="button button-secondary">
                            <?php _e('Enviar Email de Prueba', 'gpcp'); ?>
                        </button>
                    </p>
                    <div id="gpcp-test-email-result" style="margin-top: 10px;"></div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#gpcp-send-test-email').on('click', function() {
                var email = $('#gpcp-test-email').val();
                var $button = $(this);
                var $result = $('#gpcp-test-email-result');
                
                if (!email) {
                    $result.html('<div class="notice notice-error"><p><?php _e('Por favor, introduce un email válido.', 'gpcp'); ?></p></div>');
                    return;
                }
                
                $button.prop('disabled', true).text('<?php _e('Enviando...', 'gpcp'); ?>');
                $result.html('');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gpcp_test_email',
                        email: email,
                        nonce: '<?php echo wp_create_nonce('gpcp_test_email'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                        } else {
                            $result.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        $result.html('<div class="notice notice-error"><p><?php _e('Error al procesar la solicitud.', 'gpcp'); ?></p></div>');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('<?php _e('Enviar Email de Prueba', 'gpcp'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
}



