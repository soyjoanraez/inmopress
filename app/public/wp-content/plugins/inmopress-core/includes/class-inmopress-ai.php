<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Sistema de IA para generación de contenido SEO
 * Integración con OpenAI ChatGPT y Rank Math
 * 
 * @package Inmopress
 * @subpackage AI
 */
class Inmopress_AI
{
    private static $option_key = 'inmopress_ai_settings';
    private static $usage_option_key = 'inmopress_ai_usage';
    
    // Límites por plan (temporal hasta que se implemente Feature Manager)
    private static $plan_limits = array(
        'starter' => 0,
        'pro' => 0,
        'pro_ai' => 500,
        'agency' => 2000,
    );
    
    // Prompts optimizados
    private static $prompts = array(
        'seo_title' => 'Genera un título SEO optimizado (máximo 60 caracteres) para esta propiedad inmobiliaria:

Tipo: {{property_type}}
Operación: {{operation}}
Ciudad: {{city}}
Zona: {{area}}
Precio: {{price}}
Características: {{bedrooms}} dormitorios, {{bathrooms}} baños, {{area_built}} m²

El título debe:
- Incluir el tipo de propiedad y operación
- Mencionar la ubicación
- Ser atractivo y único
- NO inventar datos
- NO superar 60 caracteres

Responde SOLO con el título, sin comillas ni explicaciones.',
        
        'meta_description' => 'Genera una meta description SEO (máximo 155 caracteres) para esta propiedad:

Tipo: {{property_type}}
Operación: {{operation}}
Ciudad: {{city}}
Zona: {{area}}
Precio: {{price}}
Características: {{bedrooms}} dormitorios, {{bathrooms}} baños, {{area_built}} m²
Extras: {{features}}

La descripción debe:
- Ser persuasiva y concisa
- Incluir las características principales
- Mencionar el precio
- NO superar 155 caracteres
- Terminar con call-to-action

Responde SOLO con la descripción, sin comillas.',
        
        'faqs' => 'Genera 5 preguntas frecuentes (FAQ) relevantes para esta propiedad:

Tipo: {{property_type}}
Operación: {{operation}}
Ciudad: {{city}}
Zona: {{area}}
Precio: {{price}}
Características: {{bedrooms}} dormitorios, {{bathrooms}} baños, {{area_built}} m²

Responde en formato JSON válido:
[
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."}
]

Las preguntas deben ser relevantes para el tipo de propiedad y operación. NO inventes datos que no se hayan proporcionado.',
    );

    public static function init()
    {
        // Settings Menu
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));

        // Meta Box en editor de propiedades
        add_action('add_meta_boxes', array(__CLASS__, 'add_ai_metabox'));

        // AJAX handlers
        add_action('wp_ajax_inmopress_generate_seo', array(__CLASS__, 'handle_ajax_generate_seo'));
        add_action('wp_ajax_inmopress_generate_desc', array(__CLASS__, 'handle_ajax_generate_desc'));

        // Enqueue scripts solo en editor de propiedades
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));
    }

    // --- SETTINGS PAGE ---

    public static function add_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=impress_property',
            'Inmopress AI',
            'Inmopress AI',
            'manage_options',
            'inmopress-ai-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }

    public static function render_settings_page()
    {
        // Save logic
        if (isset($_POST['inmopress_ai_key']) && check_admin_referer('save_inmopress_ai')) {
            $key = sanitize_text_field($_POST['inmopress_ai_key']);
            $model = isset($_POST['inmopress_ai_model']) ? sanitize_text_field($_POST['inmopress_ai_model']) : 'gpt-4o-mini';
            
            update_option(self::$option_key, array(
                'api_key' => $key,
                'model' => $model,
            ));
            echo '<div class="notice notice-success is-dismissible"><p>✅ Configuración guardada.</p></div>';
        }

        $opts = get_option(self::$option_key, array('api_key' => '', 'model' => 'gpt-4o-mini'));
        $usage = self::get_usage_this_month();
        $limit = self::get_current_limit();
        
        ?>
        <div class="wrap">
            <h1>✨ Inmopress AI - Generación SEO</h1>
            <p>Configura tu clave de API de OpenAI para habilitar la generación automática de contenido SEO.</p>

            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Uso Actual</h2>
                <p><strong>Generaciones este mes:</strong> <?php echo esc_html($usage); ?> / <?php echo esc_html($limit > 0 ? $limit : '∞'); ?></p>
                <?php if ($limit > 0 && $usage >= $limit * 0.8): ?>
                    <p style="color: #d63638;">⚠️ Estás cerca del límite de tu plan.</p>
                <?php endif; ?>
            </div>

            <form method="post" action="" style="margin-top: 20px;">
                <?php wp_nonce_field('save_inmopress_ai'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ai_key">OpenAI API Key</label></th>
                        <td>
                            <input type="password" name="inmopress_ai_key" id="ai_key"
                                value="<?php echo esc_attr($opts['api_key']); ?>" class="regular-text" placeholder="sk-...">
                            <p class="description">Consigue tu clave en <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ai_model">Modelo</label></th>
                        <td>
                            <select name="inmopress_ai_model" id="ai_model">
                                <option value="gpt-4o-mini" <?php selected($opts['model'], 'gpt-4o-mini'); ?>>gpt-4o-mini (Recomendado - Económico)</option>
                                <option value="gpt-4o" <?php selected($opts['model'], 'gpt-4o'); ?>>gpt-4o (Más potente)</option>
                                <option value="gpt-3.5-turbo" <?php selected($opts['model'], 'gpt-3.5-turbo'); ?>>gpt-3.5-turbo (Legacy)</option>
                            </select>
                            <p class="description">Modelo de OpenAI a utilizar para las generaciones.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Guardar Configuración'); ?>
            </form>

            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Funcionalidades</h2>
                <ul>
                    <li>✅ Generación automática de títulos SEO (máx. 60 caracteres)</li>
                    <li>✅ Generación de meta descriptions (máx. 155 caracteres)</li>
                    <li>✅ Generación de 5 FAQs optimizadas</li>
                    <li>✅ Integración directa con Rank Math</li>
                    <li>✅ Tracking de uso por mes</li>
                    <li>✅ Validación de longitudes automática</li>
                </ul>
            </div>
        </div>
        <?php
    }

    // --- META BOX ---

    public static function add_ai_metabox()
    {
        add_meta_box(
            'inmopress_ai_generator',
            __('✨ Generador SEO con IA', 'inmopress'),
            array(__CLASS__, 'render_ai_metabox'),
            'impress_property',
            'side',
            'high'
        );
    }

    public static function render_ai_metabox($post)
    {
        $opts = get_option(self::$option_key, array());
        $has_key = !empty($opts['api_key']);
        $usage = self::get_usage_this_month();
        $limit = self::get_current_limit();
        $can_generate = $limit === 0 || $usage < $limit;
        
        wp_nonce_field('inmopress_ai_nonce', 'inmopress_ai_nonce');
        ?>
        <div id="inmopress-ai-wrapper">
            <?php if (!$has_key): ?>
                <p style="color: #d63638;">⚠️ Falta API Key de OpenAI</p>
                <a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-ai-settings'); ?>"
                    class="button">Configurar</a>
            <?php elseif (!$can_generate): ?>
                <p style="color: #d63638;">⚠️ Has alcanzado el límite de generaciones este mes (<?php echo esc_html($usage); ?> / <?php echo esc_html($limit); ?>)</p>
                <p class="description">Actualiza tu plan para continuar usando la generación con IA.</p>
            <?php else: ?>
                <p class="usage-info" style="font-size: 11px; color: #666; margin-bottom: 10px;">
                    Uso: <?php echo esc_html($usage); ?> / <?php echo esc_html($limit > 0 ? $limit : '∞'); ?> este mes
                </p>

                <button type="button" id="generate-seo-content" 
                        class="button button-primary button-large" 
                        data-property-id="<?php echo esc_attr($post->ID); ?>"
                        style="width: 100%; margin-bottom: 10px;">
                    ✨ Generar SEO Completo
                </button>

                <button type="button" id="generate-desc-only" 
                        class="button button-secondary" 
                        data-property-id="<?php echo esc_attr($post->ID); ?>"
                        style="width: 100%; margin-bottom: 10px;">
                    📝 Solo Descripción
                </button>

                <div id="ai-status" style="display: none; margin-top: 10px;">
                    <span class="spinner is-active" style="float: none;"></span> <span id="ai-status-text">Generando...</span>
                </div>

                <div id="ai-generation-result" style="display:none; margin-top:15px; padding:10px; background:#f0f0f1; border-radius:4px;">
                    <h4 style="margin-top:0;">✅ Resultado:</h4>
                    <div id="ai-result-content"></div>
                    <p class="description" style="margin-top:10px;">
                        Los datos se han guardado automáticamente en Rank Math. Puedes revisarlos en la sección de SEO de Rank Math.
                    </p>
                </div>

                <p class="description" style="margin-top: 10px;">
                    Genera automáticamente título SEO, meta description y FAQs optimizadas para esta propiedad.
                </p>
            <?php endif; ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#generate-seo-content').on('click', function() {
                var $btn = $(this);
                var $status = $('#ai-status');
                var $statusText = $('#ai-status-text');
                var propertyId = $btn.data('property-id');
                
                $btn.prop('disabled', true);
                $status.show();
                $statusText.text('Generando título SEO...');
                
                $.post(ajaxurl, {
                    action: 'inmopress_generate_seo',
                    property_id: propertyId,
                    nonce: '<?php echo wp_create_nonce('inmopress_ai_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        $statusText.text('✅ Completado');
                        $('#ai-result-content').html(
                            '<p><strong>Título SEO:</strong> ' + escapeHtml(response.data.title) + '</p>' +
                            '<p><strong>Meta Description:</strong> ' + escapeHtml(response.data.description) + '</p>' +
                            '<p><strong>FAQs:</strong> ' + (response.data.faqs ? response.data.faqs.length : 0) + ' generadas</p>'
                        );
                        $('#ai-generation-result').show();
                        
                        // Actualizar campos de Rank Math si están visibles
                        if ($('#rank-math-editor-title').length) {
                            $('#rank-math-editor-title').val(response.data.title);
                        }
                        if ($('#rank-math-editor-description').length) {
                            $('#rank-math-editor-description').val(response.data.description);
                        }
                        
                        // Mostrar notificación de éxito
                        if (typeof rankMath !== 'undefined' && rankMath.notificationCenter) {
                            rankMath.notificationCenter.add('success', 'Contenido SEO generado con IA y guardado en Rank Math.');
                        }
                    } else {
                        $statusText.html('<span style="color: red;">❌ ' + escapeHtml(response.data.message) + '</span>');
                    }
                    $btn.prop('disabled', false);
                    setTimeout(function() {
                        $status.hide();
                    }, 3000);
                }).fail(function() {
                    $statusText.html('<span style="color: red;">❌ Error de conexión</span>');
                    $btn.prop('disabled', false);
                    setTimeout(function() {
                        $status.hide();
                    }, 3000);
                });
            });

            $('#generate-desc-only').on('click', function() {
                var $btn = $(this);
                var $status = $('#ai-status');
                var $statusText = $('#ai-status-text');
                var propertyId = $btn.data('property-id');
                
                $btn.prop('disabled', true);
                $status.show();
                $statusText.text('Generando descripción...');
                
                $.post(ajaxurl, {
                    action: 'inmopress_generate_desc',
                    property_id: propertyId,
                    nonce: '<?php echo wp_create_nonce('inmopress_ai_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        $statusText.text('✅ Completado');
                        var content = response.data.content;
                        
                        // Insertar en editor
                        if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                            var current = tinymce.get('content').getContent();
                            tinymce.get('content').setContent(current + (current ? '\n\n' : '') + content);
                        } else {
                            var current = $('#content').val();
                            $('#content').val(current + (current ? '\n\n' : '') + content);
                        }
                        
                        if (typeof rankMath !== 'undefined' && rankMath.notificationCenter) {
                            rankMath.notificationCenter.add('success', 'Descripción generada con IA.');
                        }
                    } else {
                        $statusText.html('<span style="color: red;">❌ ' + escapeHtml(response.data.message) + '</span>');
                    }
                    $btn.prop('disabled', false);
                    setTimeout(function() {
                        $status.hide();
                    }, 3000);
                }).fail(function() {
                    $statusText.html('<span style="color: red;">❌ Error de conexión</span>');
                    $btn.prop('disabled', false);
                    setTimeout(function() {
                        $status.hide();
                    }, 3000);
                });
            });

            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
            }
        });
        </script>
        <?php
    }

    public static function enqueue_admin_scripts($hook)
    {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        global $post_type;
        if ($post_type !== 'impress_property') {
            return;
        }

        // Scripts ya están inline en el metabox, pero podríamos moverlos aquí si fuera necesario
    }

    // --- AJAX HANDLERS ---

    public static function handle_ajax_generate_seo()
    {
        check_ajax_referer('inmopress_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $post_id = intval($_POST['property_id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'impress_property') {
            wp_send_json_error(array('message' => 'Propiedad no encontrada'));
        }

        // Verificar límite
        $usage = self::get_usage_this_month();
        $limit = self::get_current_limit();
        if ($limit > 0 && $usage >= $limit) {
            wp_send_json_error(array('message' => 'Has alcanzado el límite de generaciones este mes. Actualiza tu plan.'));
        }

        try {
            $result = self::generate_seo_content($post_id);
            
            if (is_wp_error($result)) {
                wp_send_json_error(array('message' => $result->get_error_message()));
            }

            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

    public static function handle_ajax_generate_desc()
    {
        check_ajax_referer('inmopress_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $post_id = intval($_POST['property_id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'impress_property') {
            wp_send_json_error(array('message' => 'Propiedad no encontrada'));
        }

        // Verificar límite
        $usage = self::get_usage_this_month();
        $limit = self::get_current_limit();
        if ($limit > 0 && $usage >= $limit) {
            wp_send_json_error(array('message' => 'Has alcanzado el límite de generaciones este mes.'));
        }

        try {
            $data = self::get_property_data($post_id);
            $prompt = self::build_description_prompt($data);
            $content = self::call_openai($prompt, array(
                'max_tokens' => 800,
                'temperature' => 0.7,
            ));

            // Registrar uso
            self::track_usage($post_id, 'description_generation');

            wp_send_json_success(array('content' => $content));
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

    // --- CORE LOGIC ---

    /**
     * Generar contenido SEO completo (title, description, FAQs)
     */
    public static function generate_seo_content($property_id)
    {
        // Obtener datos de la propiedad
        $data = self::get_property_data($property_id);

        // Generar título SEO
        $title_prompt = self::replace_variables(self::$prompts['seo_title'], $data);
        $seo_title = self::call_openai($title_prompt, array(
            'max_tokens' => 100,
            'temperature' => 0.7,
        ));
        
        // Validar y truncar título si es necesario
        $seo_title = self::validate_title($seo_title);

        // Generar meta description
        $desc_prompt = self::replace_variables(self::$prompts['meta_description'], $data);
        $seo_description = self::call_openai($desc_prompt, array(
            'max_tokens' => 200,
            'temperature' => 0.7,
        ));
        
        // Validar y truncar description si es necesario
        $seo_description = self::validate_description($seo_description);

        // Generar FAQs
        $faq_prompt = self::replace_variables(self::$prompts['faqs'], $data);
        $faqs_json = self::call_openai($faq_prompt, array(
            'max_tokens' => 800,
            'temperature' => 0.8,
        ));
        
        $faqs = self::parse_faqs($faqs_json);

        // Escribir en Rank Math
        $rankmath_data = array(
            'title' => $seo_title,
            'description' => $seo_description,
            'faqs' => $faqs,
        );
        self::write_to_rankmath($property_id, $rankmath_data);

        // Registrar uso (3 generaciones: title, description, FAQs)
        self::track_usage($property_id, 'seo_generation', 3);

        return array(
            'title' => $seo_title,
            'description' => $seo_description,
            'faqs' => $faqs,
        );
    }

    /**
     * Obtener datos de la propiedad para los prompts
     */
    private static function get_property_data($property_id)
    {
        $data = array();

        // Tipo de propiedad
        $type_terms = get_the_terms($property_id, 'impress_property_type');
        $data['property_type'] = $type_terms && !is_wp_error($type_terms) && !empty($type_terms) 
            ? $type_terms[0]->name 
            : 'Propiedad';

        // Operación
        $op_terms = get_the_terms($property_id, 'impress_operation');
        $data['operation'] = $op_terms && !is_wp_error($op_terms) && !empty($op_terms) 
            ? $op_terms[0]->name 
            : 'Venta';

        // Ciudad
        $city_terms = get_the_terms($property_id, 'impress_city');
        $data['city'] = $city_terms && !is_wp_error($city_terms) && !empty($city_terms) 
            ? $city_terms[0]->name 
            : '';

        // Provincia
        $province_terms = get_the_terms($property_id, 'impress_province');
        $data['province'] = $province_terms && !is_wp_error($province_terms) && !empty($province_terms) 
            ? $province_terms[0]->name 
            : '';

        // Zona/Área
        $area_terms = get_the_terms($property_id, 'impress_area');
        $data['area'] = $area_terms && !is_wp_error($area_terms) && !empty($area_terms) 
            ? $area_terms[0]->name 
            : '';

        // Precio
        $precio_venta = get_field('precio_venta', $property_id);
        $precio_alquiler = get_field('precio_alquiler', $property_id);
        $precio = $precio_venta ?: $precio_alquiler;
        $data['price'] = $precio ? number_format_i18n((float) $precio, 0, ',', '.') . ' €' : 'Consultar precio';

        // Características
        $data['bedrooms'] = get_field('dormitorios', $property_id) ?: '?';
        $data['bathrooms'] = get_field('banos', $property_id) ?: '?';
        $data['area_built'] = get_field('superficie_construida', $property_id) ?: '?';
        $data['area_useful'] = get_field('superficie_util', $property_id) ?: '?';

        // Features destacadas
        $features = array();
        $feature_keys = array('piscina', 'jardin', 'terraza', 'garaje', 'ascensor', 'aire_acondicionado', 'calefaccion', 'vistas_mar', 'balcon');
        foreach ($feature_keys as $key) {
            if (get_field($key, $property_id)) {
                $features[] = ucfirst(str_replace('_', ' ', $key));
            }
        }
        $data['features'] = !empty($features) ? implode(', ', $features) : '';

        // Referencia
        $data['reference'] = get_field('referencia', $property_id) ?: '';

        return $data;
    }

    /**
     * Reemplazar variables en prompts
     */
    private static function replace_variables($template, $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    /**
     * Construir prompt para descripción completa
     */
    private static function build_description_prompt($data)
    {
        $prompt = "Actúa como un agente inmobiliario experto copywriter. Escribe una descripción atractiva, emocional y optimizada para SEO para el siguiente inmueble:\n\n";
        $prompt .= "- Tipo: {$data['property_type']}\n";
        if ($data['city']) {
            $prompt .= "- Ubicación: {$data['city']}";
            if ($data['area']) {
                $prompt .= " ({$data['area']})";
            }
            $prompt .= "\n";
        }
        $prompt .= "- Operación: {$data['operation']} por {$data['price']}\n";
        $prompt .= "- Dormitorios: {$data['bedrooms']}\n";
        $prompt .= "- Baños: {$data['bathrooms']}\n";
        $prompt .= "- Superficie construida: {$data['area_built']} m²\n";
        if ($data['features']) {
            $prompt .= "- Características destacadas: {$data['features']}\n";
        }
        $prompt .= "\nInstrucciones:\n";
        $prompt .= "- Usa un tono profesional pero invitador.\n";
        $prompt .= "- Destaca los beneficios de vivir allí.\n";
        $prompt .= "- Estructura el texto en párrafos legibles.\n";
        $prompt .= "- Incluye una llamada a la acción al final.\n";
        $prompt .= "- Idioma: Español de España.\n";
        $prompt .= "- NO incluyas títulos markdown como '## Descripción', empieza directamente con el texto.";

        return $prompt;
    }

    /**
     * Llamar a OpenAI API
     */
    private static function call_openai($prompt, $options = array())
    {
        $opts = get_option(self::$option_key, array());
        $api_key = isset($opts['api_key']) ? $opts['api_key'] : '';
        $model = isset($opts['model']) ? $opts['model'] : 'gpt-4o-mini';

        if (empty($api_key)) {
            throw new Exception('Falta API Key de OpenAI. Configúrala en Inmopress AI Settings.');
        }

        $defaults = array(
            'max_tokens' => 500,
            'temperature' => 0.7,
        );

        $options = wp_parse_args($options, $defaults);

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'Eres un experto en SEO inmobiliario. Generas contenido optimizado, único y persuasivo. NUNCA inventas datos que no se hayan proporcionado.',
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt,
                    ),
                ),
                'max_tokens' => $options['max_tokens'],
                'temperature' => $options['temperature'],
            )),
        ));

        if (is_wp_error($response)) {
            throw new Exception('Error API: ' . $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            throw new Exception('Error OpenAI: ' . $body['error']['message']);
        }

        if (!isset($body['choices'][0]['message']['content'])) {
            throw new Exception('Respuesta vacía de OpenAI');
        }

        return trim($body['choices'][0]['message']['content']);
    }

    /**
     * Validar y truncar título SEO (máx 60 caracteres)
     */
    private static function validate_title($title)
    {
        $title = trim($title);
        // Eliminar comillas si las hay
        $title = trim($title, '"\'');
        
        if (mb_strlen($title) > 60) {
            $title = mb_substr($title, 0, 57) . '...';
        }
        
        return $title;
    }

    /**
     * Validar y truncar meta description (máx 155 caracteres)
     */
    private static function validate_description($description)
    {
        $description = trim($description);
        // Eliminar comillas si las hay
        $description = trim($description, '"\'');
        
        if (mb_strlen($description) > 155) {
            $description = mb_substr($description, 0, 152) . '...';
        }
        
        return $description;
    }

    /**
     * Parsear FAQs desde JSON
     */
    private static function parse_faqs($faqs_json)
    {
        // Limpiar posibles markdown code blocks
        $faqs_json = preg_replace('/```json\s*/', '', $faqs_json);
        $faqs_json = preg_replace('/```\s*/', '', $faqs_json);
        $faqs_json = trim($faqs_json);

        $faqs = json_decode($faqs_json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($faqs)) {
            // Intentar extraer JSON del texto si está embebido
            if (preg_match('/\[.*\]/s', $faqs_json, $matches)) {
                $faqs = json_decode($matches[0], true);
            }
        }

        if (!is_array($faqs) || empty($faqs)) {
            return array();
        }

        // Validar estructura y limitar a 5 FAQs
        $valid_faqs = array();
        foreach (array_slice($faqs, 0, 5) as $faq) {
            if (isset($faq['question']) && isset($faq['answer'])) {
                $valid_faqs[] = array(
                    'question' => sanitize_text_field($faq['question']),
                    'answer' => sanitize_textarea_field($faq['answer']),
                );
            }
        }

        return $valid_faqs;
    }

    /**
     * Escribir datos en Rank Math
     */
    private static function write_to_rankmath($post_id, $data)
    {
        // Verificar Rank Math instalado
        if (!function_exists('rank_math')) {
            return false;
        }

        // SEO Title
        if (!empty($data['title'])) {
            update_post_meta($post_id, 'rank_math_title', $data['title']);
        }

        // Meta Description
        if (!empty($data['description'])) {
            update_post_meta($post_id, 'rank_math_description', $data['description']);
        }

        // Focus Keyword (extraer palabras clave del título)
        if (!empty($data['title'])) {
            $keyword = self::extract_focus_keyword($data['title']);
            if ($keyword) {
                update_post_meta($post_id, 'rank_math_focus_keyword', $keyword);
            }
        }

        // FAQs (Rank Math Schema)
        if (!empty($data['faqs']) && is_array($data['faqs'])) {
            $faq_schema = self::format_faqs_for_rankmath($data['faqs']);
            update_post_meta($post_id, 'rank_math_schema_FAQPage', $faq_schema);
        }

        return true;
    }

    /**
     * Extraer focus keyword del título
     */
    private static function extract_focus_keyword($title)
    {
        // Extraer palabras clave principales (tipo + ciudad o tipo + operación)
        $words = explode(' ', $title);
        // Tomar las 2-3 primeras palabras significativas
        $keywords = array_slice($words, 0, 3);
        return implode(' ', $keywords);
    }

    /**
     * Formatear FAQs para Rank Math Schema
     */
    private static function format_faqs_for_rankmath($faqs)
    {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array(),
        );

        foreach ($faqs as $faq) {
            if (isset($faq['question']) && isset($faq['answer'])) {
                $schema['mainEntity'][] = array(
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => $faq['answer'],
                    ),
                );
            }
        }

        return $schema;
    }

    /**
     * Registrar uso de IA
     */
    public static function track_usage($post_id, $type, $count = 1)
    {
        // Por ahora usar opción, después se migrará a activity_log cuando esté implementado
        $usage = get_option(self::$usage_option_key, array());
        $current_month = date('Y-m');
        
        if (!isset($usage[$current_month])) {
            $usage[$current_month] = 0;
        }
        
        $usage[$current_month] += $count;
        
        // Mantener solo últimos 12 meses
        $months = array_keys($usage);
        if (count($months) > 12) {
            $oldest = min($months);
            unset($usage[$oldest]);
        }
        
        update_option(self::$usage_option_key, $usage);
        
        // TODO: Cuando Activity Log esté implementado, registrar ahí también
        // do_action('inmopress_activity_log', 'ai_generation', 'impress_property', $post_id, array('type' => $type));
        
        // Verificar límite y notificar si es necesario
        $usage_this_month = self::get_usage_this_month();
        $limit = self::get_current_limit();
        
        if ($limit > 0 && $usage_this_month >= $limit - 50) {
            do_action('inmopress_ai_limit_warning', $usage_this_month, $limit);
        }
    }

    /**
     * Obtener uso del mes actual
     */
    public static function get_usage_this_month()
    {
        $usage = get_option(self::$usage_option_key, array());
        $current_month = date('Y-m');
        
        return isset($usage[$current_month]) ? intval($usage[$current_month]) : 0;
    }

    /**
     * Obtener límite actual según plan
     * TODO: Integrar con Feature Manager cuando esté implementado
     */
    private static function get_current_limit()
    {
        // Por ahora retornar límite de plan "agency" (ilimitado prácticamente)
        // Cuando Feature Manager esté implementado, consultar ahí
        $plan = get_option('inmopress_license_plan', 'agency');
        
        if (isset(self::$plan_limits[$plan])) {
            return self::$plan_limits[$plan];
        }
        
        // Default: ilimitado para desarrollo
        return 0;
    }
}
