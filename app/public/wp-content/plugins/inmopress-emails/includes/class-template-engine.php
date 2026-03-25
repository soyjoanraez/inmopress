<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Engine - Gestiona plantillas de email
 */
class Inmopress_Template_Engine
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
     * Renderizar plantilla
     */
    public function render_template($template_slug, $variables = array())
    {
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_email_templates';

        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE slug = %s AND is_active = 1",
            $template_slug
        ));

        if (!$template) {
            return false;
        }

        // Reemplazar variables
        $subject = $this->replace_variables($template->subject, $variables);
        $body_html = $this->replace_variables($template->body_html, $variables);

        // Añadir header y footer
        $body_html = $this->wrap_template($body_html);

        return array(
            'subject' => $subject,
            'body_html' => $body_html,
            'body_text' => wp_strip_all_tags($body_html),
        );
    }

    /**
     * Reemplazar variables en template
     */
    private function replace_variables($text, $variables)
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }

        // Variables globales
        $text = str_replace('{{site_name}}', get_bloginfo('name'), $text);
        $text = str_replace('{{site_url}}', home_url(), $text);
        $text = str_replace('{{current_date}}', date_i18n(get_option('date_format')), $text);
        $text = str_replace('{{current_time}}', date_i18n(get_option('time_format')), $text);

        return $text;
    }

    /**
     * Envolver template con header y footer
     */
    private function wrap_template($body)
    {
        $header = $this->get_template_header();
        $footer = $this->get_template_footer();

        return $header . $body . $footer;
    }

    /**
     * Obtener header de template
     */
    private function get_template_header()
    {
        $logo_url = get_option('inmopress_email_logo_url', '');
        $agency_name = get_option('inmopress_email_agency_name', get_bloginfo('name'));

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <?php if ($logo_url): ?>
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($agency_name); ?>" style="max-height: 60px;">
                </div>
            <?php endif; ?>
        <?php
        return ob_get_clean();
    }

    /**
     * Obtener footer de template
     */
    private function get_template_footer()
    {
        $agency_name = get_option('inmopress_email_agency_name', get_bloginfo('name'));
        $agency_phone = get_option('inmopress_email_agency_phone', '');
        $agency_email = get_option('inmopress_email_agency_email', get_bloginfo('admin_email'));
        $agency_address = get_option('inmopress_email_agency_address', '');

        ob_start();
        ?>
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
                <p><strong><?php echo esc_html($agency_name); ?></strong></p>
                <?php if ($agency_phone): ?>
                    <p>Teléfono: <?php echo esc_html($agency_phone); ?></p>
                <?php endif; ?>
                <?php if ($agency_email): ?>
                    <p>Email: <?php echo esc_html($agency_email); ?></p>
                <?php endif; ?>
                <?php if ($agency_address): ?>
                    <p><?php echo esc_html($agency_address); ?></p>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Guardar plantilla
     */
    public function save_template($data)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'inmopress_email_templates';

        if (empty($data['slug'])) {
            $data['slug'] = sanitize_title($data['name']);
        }

        $result = $wpdb->replace(
            $table,
            array(
                'name' => sanitize_text_field($data['name']),
                'slug' => sanitize_title($data['slug']),
                'subject' => sanitize_text_field($data['subject']),
                'body_html' => wp_kses_post($data['body_html']),
                'category' => isset($data['category']) ? sanitize_text_field($data['category']) : 'general',
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
                'created_at' => current_time('mysql'),
            )
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Crear plantillas por defecto
     */
    public function create_default_templates()
    {
        $templates = array(
            array(
                'name' => 'Contacto Recibido',
                'slug' => 'contact-received',
                'subject' => 'Hemos recibido tu consulta - {{site_name}}',
                'body_html' => '<p>Hola {{client_name}},</p><p>Gracias por contactarnos. Hemos recibido tu consulta y nos pondremos en contacto contigo pronto.</p><p>Saludos,<br>{{agent_name}}</p>',
                'category' => 'automation',
            ),
            array(
                'name' => 'Confirmación de Visita',
                'slug' => 'visit-confirmation',
                'subject' => 'Confirmación de visita - {{property_title}}',
                'body_html' => '<p>Hola {{client_name}},</p><p>Te confirmamos tu visita programada para el {{visit_date}} a las {{visit_time}}.</p><p>Dirección: {{property_address}}</p><p>Saludos,<br>{{agent_name}}</p>',
                'category' => 'automation',
            ),
            array(
                'name' => 'Nueva Propiedad Matching',
                'slug' => 'property-match',
                'subject' => 'Nueva propiedad que puede interesarte - {{site_name}}',
                'body_html' => '<p>Hola {{client_name}},</p><p>Hemos encontrado una propiedad que encaja con tus criterios:</p><p><strong>{{property_title}}</strong><br>{{property_address}}<br>Precio: {{property_price}}</p><p><a href="{{property_url}}">Ver detalles</a></p>',
                'category' => 'automation',
            ),
        );

        foreach ($templates as $template) {
            $this->save_template($template);
        }
    }
}
