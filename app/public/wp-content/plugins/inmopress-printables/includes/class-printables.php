<?php
if (!defined('ABSPATH'))
    exit;

class Inmopress_Printables_Handler
{


    public static function init()
    {
        // Añadir submenú en Inmuebles
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));

        // Añadir meta box
        add_action('add_meta_boxes', array(__CLASS__, 'add_print_meta_box'));

        // Interceptar página print-property
        add_action('template_redirect', array(__CLASS__, 'handle_print_page'));

        // AJAX para generar PDFs
        add_action('wp_ajax_inmopress_generate_pdf', array(__CLASS__, 'ajax_generate_pdf'));

        // Cargar generador de PDFs
        require_once INMOPRESS_PRINTABLES_PATH . 'includes/class-pdf-generator.php';
    }

    public static function add_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=impress_property',
            'Plantillas Imprimibles',
            'Plantillas Imprimibles',
            'edit_posts',
            'inmopress-printables',
            array(__CLASS__, 'admin_page')
        );
    }

    public static function admin_page()
    {
        // Guardar configuración
        if (isset($_POST['save_pdf_settings']) && check_admin_referer('inmopress_pdf_settings')) {
            update_option('inmopress_pdf_logo_url', esc_url_raw($_POST['pdf_logo_url']));
            update_option('inmopress_pdf_agency_name', sanitize_text_field($_POST['pdf_agency_name']));
            update_option('inmopress_pdf_agency_phone', sanitize_text_field($_POST['pdf_agency_phone']));
            update_option('inmopress_pdf_agency_email', sanitize_email($_POST['pdf_agency_email']));
            update_option('inmopress_pdf_agency_address', sanitize_textarea_field($_POST['pdf_agency_address']));
            echo '<div class="notice notice-success"><p>Configuración guardada.</p></div>';
        }

        $logo_url = get_option('inmopress_pdf_logo_url', '');
        $agency_name = get_option('inmopress_pdf_agency_name', get_bloginfo('name'));
        $agency_phone = get_option('inmopress_pdf_agency_phone', '');
        $agency_email = get_option('inmopress_pdf_agency_email', get_bloginfo('admin_email'));
        $agency_address = get_option('inmopress_pdf_agency_address', '');

        $mpdf_available = class_exists('\Mpdf\Mpdf');
        ?>
        <div class="wrap">
            <h1>Configuración de PDFs</h1>

            <?php if (!$mpdf_available): ?>
                <div class="notice notice-warning">
                    <p><strong>mPDF no está instalado.</strong> Para habilitar la generación de PDFs, ejecuta:</p>
                    <code>composer require mpdf/mpdf:^8.2</code>
                    <p>O instálalo manualmente en la raíz del proyecto WordPress.</p>
                </div>
            <?php else: ?>
                <div class="notice notice-success">
                    <p>✅ mPDF está instalado y funcionando.</p>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <?php wp_nonce_field('inmopress_pdf_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th><label>Logo de la Agencia</label></th>
                        <td>
                            <input type="url" name="pdf_logo_url" value="<?php echo esc_attr($logo_url); ?>" class="regular-text">
                            <p class="description">URL completa del logo (debe ser accesible públicamente)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Nombre de la Agencia</label></th>
                        <td>
                            <input type="text" name="pdf_agency_name" value="<?php echo esc_attr($agency_name); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label>Teléfono</label></th>
                        <td>
                            <input type="text" name="pdf_agency_phone" value="<?php echo esc_attr($agency_phone); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label>Email</label></th>
                        <td>
                            <input type="email" name="pdf_agency_email" value="<?php echo esc_attr($agency_email); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label>Dirección</label></th>
                        <td>
                            <textarea name="pdf_agency_address" rows="3" class="large-text"><?php echo esc_textarea($agency_address); ?></textarea>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="save_pdf_settings" class="button button-primary">Guardar Configuración</button>
                </p>
            </form>
        </div>
        <?php
    }

    public static function add_print_meta_box()
    {
        add_meta_box(
            'inmopress_print_actions',
            '🖨️ Imprimir Carteles',
            array(__CLASS__, 'render_print_meta_box'),
            'impress_property',
            'side',
            'high'
        );
    }

    public static function render_print_meta_box($post)
    {
        $base_url = home_url('/print-property/?id=' . $post->ID);
        $mpdf_available = class_exists('\Mpdf\Mpdf');
        ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php if ($mpdf_available): ?>
                <h4>Generar PDFs</h4>
                <button type="button" class="button button-primary generate-pdf-btn" 
                        data-property-id="<?php echo $post->ID; ?>" 
                        data-type="property-sheet"
                        style="width: 100%;">
                    📄 Ficha de Propiedad (PDF)
                </button>
                <p class="description" style="font-size: 11px; margin-top: 5px;">
                    Genera un PDF completo con toda la información de la propiedad.
                </p>
            <?php endif; ?>

            <h4>Imprimir HTML</h4>
            <a href="<?php echo esc_url($base_url . '&template=a4-vertical'); ?>" target="_blank" class="button"
                style="text-align: center;">Cartel A4 Vertical</a>
            <a href="<?php echo esc_url($base_url . '&template=a4-horizontal'); ?>" target="_blank" class="button"
                style="text-align: center;">Cartel A4 Horizontal</a>
            <a href="<?php echo esc_url($base_url . '&template=a3-vertical'); ?>" target="_blank" class="button"
                style="text-align: center;">Cartel A3 Premium</a>
        </div>

        <?php if ($mpdf_available): ?>
        <script>
        jQuery(document).ready(function($) {
            $('.generate-pdf-btn').on('click', function() {
                var $btn = $(this);
                var propertyId = $btn.data('property-id');
                var type = $btn.data('type');
                
                $btn.prop('disabled', true).text('Generando PDF...');
                
                // Crear formulario oculto para descargar PDF
                var form = $('<form>', {
                    method: 'POST',
                    action: ajaxurl,
                    target: '_blank'
                });
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'action',
                    value: 'inmopress_generate_pdf'
                }));
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'property_id',
                    value: propertyId
                }));
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'pdf_type',
                    value: type
                }));
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'nonce',
                    value: '<?php echo wp_create_nonce('inmopress_pdf_nonce'); ?>'
                }));
                
                $('body').append(form);
                form.submit();
                form.remove();
                
                setTimeout(function() {
                    $btn.prop('disabled', false).text('📄 Ficha de Propiedad (PDF)');
                }, 2000);
            });
        });
        </script>
        <?php endif; ?>
        <?php
    }

    /**
     * AJAX: Generar PDF
     */
    public static function ajax_generate_pdf()
    {
        check_ajax_referer('inmopress_pdf_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_die('Sin permisos');
        }

        $property_id = intval($_POST['property_id']);
        $pdf_type = sanitize_text_field($_POST['pdf_type']);

        $generator = Inmopress_PDF_Generator::get_instance();

        switch ($pdf_type) {
            case 'property-sheet':
                $result = $generator->generate_property_sheet($property_id, array('output' => 'D'));
                break;
            default:
                wp_die('Tipo de PDF no válido');
        }

        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        }
    }

    public static function handle_print_page()
    {
        if (is_page('print-property') && isset($_GET['id'])) {
            $post_id = intval($_GET['id']);
            $post = get_post($post_id);
            $template = isset($_GET['template']) ? sanitize_text_field($_GET['template']) : 'a4-vertical';

            if ($post && $post->post_type === 'impress_property') {
                self::render_template($post, $template);
                exit;
            }
        }
    }

    private static function render_template($post, $template_name)
    {
        // Preparar variables para el template
        $post_id = $post->ID;
        $title = $post->post_title;
        $referencia = get_field('referencia', $post_id);
        $descripcion = get_field('descripcion', $post_id);
        $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');

        // Precio
        $precio_venta = get_field('precio_venta', $post_id);
        $precio_alquiler = get_field('precio_alquiler', $post_id);
        $price = '';
        if ($precio_venta) {
            $price = number_format($precio_venta, 0, ',', '.') . ' €';
        } elseif ($precio_alquiler) {
            $price = number_format($precio_alquiler, 0, ',', '.') . ' €/mes';
        }

        // Características
        $dormitorios = get_field('dormitorios', $post_id);
        $banos = get_field('banos', $post_id);
        $superficie = get_field('superficie_construida', $post_id);

        // Ubicación
        $ciudad_terms = get_the_terms($post_id, 'impress_city');
        $ciudad = $ciudad_terms && !is_wp_error($ciudad_terms) ? $ciudad_terms[0]->name : '';

        // Definir archivo
        $template_file = INMOPRESS_PRINTABLES_PATH . 'templates/poster-' . $template_name . '.php';

        if (file_exists($template_file)) {
            define('INMOPRESS_PRINTABLES_URL', plugin_dir_url(dirname(__FILE__)));
            include $template_file;
        } else {
            echo "Error: Plantilla no encontrada ($template_file)";
        }
    }
}


