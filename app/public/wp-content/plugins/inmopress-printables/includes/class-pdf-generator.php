<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PDF Generator usando mPDF
 * 
 * NOTA: Requiere composer install para instalar mpdf/mpdf
 * Ejecutar: composer require mpdf/mpdf:^8.2
 */
class Inmopress_PDF_Generator
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Verificar si mPDF está disponible
        $this->check_mpdf_available();
    }

    /**
     * Verificar si mPDF está disponible
     */
    private function check_mpdf_available()
    {
        // Buscar mPDF en diferentes ubicaciones posibles
        $mpdf_paths = array(
            INMOPRESS_PRINTABLES_PATH . '../vendor/autoload.php',
            WP_CONTENT_DIR . '/vendor/autoload.php',
            ABSPATH . 'vendor/autoload.php',
        );

        foreach ($mpdf_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return true;
            }
        }

        // Si no se encuentra, mostrar aviso en admin
        add_action('admin_notices', array($this, 'mpdf_missing_notice'));
        return false;
    }

    /**
     * Aviso si mPDF no está instalado
     */
    public function mpdf_missing_notice()
    {
        if (current_user_can('manage_options')) {
            ?>
            <div class="notice notice-warning">
                <p><strong>Inmopress Printables:</strong> mPDF no está instalado. Ejecuta <code>composer require mpdf/mpdf:^8.2</code> en la raíz del proyecto para habilitar la generación de PDFs.</p>
            </div>
            <?php
        }
    }

    /**
     * Generar PDF de ficha de propiedad
     */
    public function generate_property_sheet($property_id, $options = array())
    {
        if (!class_exists('\Mpdf\Mpdf')) {
            return new WP_Error('mpdf_not_available', 'mPDF no está disponible. Instala con: composer require mpdf/mpdf:^8.2');
        }

        $defaults = array(
            'format' => 'A4',
            'orientation' => 'portrait',
            'include_gallery' => true,
            'include_qr' => true,
        );

        $options = wp_parse_args($options, $defaults);

        // Obtener datos de la propiedad
        $data = $this->get_property_data($property_id);
        if (is_wp_error($data)) {
            return $data;
        }

        // Generar HTML
        $html = $this->get_property_html($data, $options);

        // Configurar mPDF
        $mpdf_config = array(
            'format' => $options['format'],
            'orientation' => $options['orientation'],
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_header' => 10,
            'margin_footer' => 10,
        );

        try {
            $mpdf = new \Mpdf\Mpdf($mpdf_config);

            // Header con logo
            $mpdf->SetHTMLHeader($this->get_header_html($data));

            // Footer con paginación
            $mpdf->SetHTMLFooter($this->get_footer_html());

            // Escribir HTML
            $mpdf->WriteHTML($html);

            // Generar QR code si está habilitado
            if ($options['include_qr']) {
                $qr_data = $this->generate_qr_code($data['permalink']);
                if ($qr_data) {
                    // Añadir QR al final del documento
                    $mpdf->AddPage();
                    $mpdf->WriteHTML('<div style="text-align:center;"><h3>Escanea para ver online</h3><img src="' . $qr_data . '" style="width:150px;"></div>');
                }
            }

            // Nombre del archivo
            $filename = 'propiedad-' . sanitize_file_name($data['reference']) . '.pdf';

            // Salida
            $output_mode = isset($options['output']) ? $options['output'] : 'D'; // D = Download, I = Inline, S = String
            return $mpdf->Output($filename, $output_mode);

        } catch (\Exception $e) {
            return new WP_Error('pdf_generation_error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generar dosier comercial (múltiples propiedades)
     */
    public function generate_commercial_dossier($property_ids, $options = array())
    {
        if (!class_exists('\Mpdf\Mpdf')) {
            return new WP_Error('mpdf_not_available', 'mPDF no está disponible.');
        }

        $defaults = array(
            'format' => 'A4',
            'include_index' => true,
        );

        $options = wp_parse_args($options, $defaults);

        $properties_data = array();
        foreach ($property_ids as $property_id) {
            $data = $this->get_property_data($property_id);
            if (!is_wp_error($data)) {
                $properties_data[] = $data;
            }
        }

        if (empty($properties_data)) {
            return new WP_Error('no_properties', 'No hay propiedades válidas para el dosier.');
        }

        // Generar HTML
        $html = $this->get_dossier_html($properties_data, $options);

        // Configurar mPDF
        $mpdf = new \Mpdf\Mpdf(array(
            'format' => $options['format'],
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 20,
        ));

        $mpdf->SetHTMLHeader($this->get_header_html());
        $mpdf->SetHTMLFooter($this->get_footer_html());
        $mpdf->WriteHTML($html);

        $filename = 'dosier-comercial-' . date('Y-m-d') . '.pdf';
        return $mpdf->Output($filename, 'D');
    }

    /**
     * Generar hoja de visita
     */
    public function generate_visit_sheet($visit_id, $options = array())
    {
        if (!class_exists('\Mpdf\Mpdf')) {
            return new WP_Error('mpdf_not_available', 'mPDF no está disponible.');
        }

        // Obtener datos de la visita
        $visit = get_post($visit_id);
        if (!$visit || $visit->post_type !== 'impress_visit') {
            return new WP_Error('invalid_visit', 'Visita no válida.');
        }

        $property_id = get_field('impress_visit_property_rel', $visit_id);
        $client_id = get_field('impress_visit_client_rel', $visit_id);

        $data = array(
            'visit' => $visit,
            'property' => $property_id ? $this->get_property_data($property_id) : null,
            'client' => $client_id ? $this->get_client_data($client_id) : null,
            'visit_date' => get_field('impress_visit_date', $visit_id),
            'visit_notes' => get_field('impress_visit_notes', $visit_id),
        );

        $html = $this->get_visit_sheet_html($data);

        $mpdf = new \Mpdf\Mpdf(array(
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 20,
        ));

        $mpdf->WriteHTML($html);

        $filename = 'hoja-visita-' . date('Y-m-d', strtotime($data['visit_date'])) . '.pdf';
        return $mpdf->Output($filename, 'D');
    }

    /**
     * Obtener datos de la propiedad
     */
    private function get_property_data($property_id)
    {
        $post = get_post($property_id);
        if (!$post || $post->post_type !== 'impress_property') {
            return new WP_Error('invalid_property', 'Propiedad no válida.');
        }

        // Operación y precio
        $operation_terms = get_the_terms($property_id, 'impress_operation');
        $operation = $operation_terms && !is_wp_error($operation_terms) && !empty($operation_terms)
            ? $operation_terms[0]->name
            : 'Venta';

        $precio_venta = get_field('precio_venta', $property_id);
        $precio_alquiler = get_field('precio_alquiler', $property_id);
        $price = $operation === 'Alquiler' ? $precio_alquiler : $precio_venta;

        // Ubicación
        $city_terms = get_the_terms($property_id, 'impress_city');
        $city = $city_terms && !is_wp_error($city_terms) && !empty($city_terms)
            ? $city_terms[0]->name
            : '';

        $province_terms = get_the_terms($property_id, 'impress_property_type');
        $property_type = $province_terms && !is_wp_error($province_terms) && !empty($province_terms)
            ? $province_terms[0]->name
            : '';

        // Galería
        $gallery = array();
        $galeria_field = get_field('galeria', $property_id);
        if (is_array($galeria_field)) {
            foreach ($galeria_field as $image) {
                if (isset($image['url'])) {
                    $gallery[] = $image['url'];
                } elseif (is_numeric($image)) {
                    $gallery[] = wp_get_attachment_image_url($image, 'full');
                }
            }
        }

        // Agente
        $agent_id = get_field('agente', $property_id);
        $agent_name = '';
        $agent_phone = '';
        $agent_email = '';
        if ($agent_id) {
            $agent_name = get_the_title($agent_id);
            $agent_phone = get_field('telefono', $agent_id);
            $agent_email = get_field('email', $agent_id);
        }

        return array(
            'id' => $property_id,
            'title' => $post->post_title,
            'reference' => get_field('referencia', $property_id) ?: 'REF' . $property_id,
            'description' => get_field('descripcion', $property_id) ?: $post->post_content,
            'operation' => $operation,
            'price' => floatval($price),
            'price_formatted' => $price ? number_format_i18n($price, 0, ',', '.') . ' €' . ($operation === 'Alquiler' ? '/mes' : '') : 'Consultar',
            'city' => $city,
            'property_type' => $property_type,
            'bedrooms' => get_field('dormitorios', $property_id) ?: '—',
            'bathrooms' => get_field('banos', $property_id) ?: '—',
            'area_built' => get_field('superficie_construida', $property_id) ?: '—',
            'area_useful' => get_field('superficie_util', $property_id) ?: '—',
            'main_image' => get_the_post_thumbnail_url($property_id, 'large') ?: (isset($gallery[0]) ? $gallery[0] : ''),
            'gallery' => $gallery,
            'agent_name' => $agent_name,
            'agent_phone' => $agent_phone,
            'agent_email' => $agent_email,
            'permalink' => get_permalink($property_id),
        );
    }

    /**
     * Obtener datos del cliente
     */
    private function get_client_data($client_id)
    {
        $post = get_post($client_id);
        if (!$post || $post->post_type !== 'impress_client') {
            return null;
        }

        return array(
            'id' => $client_id,
            'name' => trim((get_field('nombre', $client_id) ?: '') . ' ' . (get_field('apellidos', $client_id) ?: '')),
            'phone' => get_field('telefono', $client_id),
            'email' => get_field('email', $client_id),
        );
    }

    /**
     * Generar HTML para ficha de propiedad
     */
    private function get_property_html($data, $options)
    {
        ob_start();
        include INMOPRESS_PRINTABLES_PATH . 'templates/pdf-property-sheet.php';
        return ob_get_clean();
    }

    /**
     * Generar HTML para dosier
     */
    private function get_dossier_html($properties_data, $options)
    {
        ob_start();
        include INMOPRESS_PRINTABLES_PATH . 'templates/pdf-dossier.php';
        return ob_get_clean();
    }

    /**
     * Generar HTML para hoja de visita
     */
    private function get_visit_sheet_html($data)
    {
        ob_start();
        include INMOPRESS_PRINTABLES_PATH . 'templates/pdf-visit-sheet.php';
        return ob_get_clean();
    }

    /**
     * Obtener HTML del header
     */
    private function get_header_html($data = null)
    {
        $logo_url = get_option('inmopress_pdf_logo_url', '');
        $agency_name = get_option('inmopress_pdf_agency_name', get_bloginfo('name'));

        ob_start();
        ?>
        <div style="text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
            <?php if ($logo_url): ?>
                <img src="<?php echo esc_url($logo_url); ?>" style="max-height: 50px; margin-bottom: 5px;">
            <?php endif; ?>
            <div style="font-size: 14px; color: #666;"><?php echo esc_html($agency_name); ?></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Obtener HTML del footer
     */
    private function get_footer_html()
    {
        ob_start();
        ?>
        <div style="text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 5px;">
            Página {PAGENO} de {nbpg} | <?php echo esc_html(get_bloginfo('name')); ?> - <?php echo date('Y'); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Generar QR code (requiere librería QR)
     */
    private function generate_qr_code($url)
    {
        // Por ahora retornar null, se puede integrar con una librería QR después
        // Opciones: phpqrcode, endroid/qr-code (composer)
        return null;
    }
}
