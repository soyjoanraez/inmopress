<?php
/**
 * Plugin Name: Inmopress Core
 * Description: CPTs, Taxonomías y Roles para Inmopress
 * Version: 1.0.0
 * Author: Inmopress
 */

if (!defined('ABSPATH'))
    exit;

define('INMOPRESS_CORE_PATH', plugin_dir_path(__FILE__));
define('INMOPRESS_CORE_URL', plugin_dir_url(__FILE__));
define('INMOPRESS_CORE_VERSION', '1.0.0');
define('INMOPRESS_CORE_FILE', __FILE__);

class Inmopress_Core
{

    private static $instance = null;
    private static $syncing_owner = false;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies()
    {
        require_once INMOPRESS_CORE_PATH . 'includes/class-cpts.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-taxonomies.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-roles.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-taxonomy-seeder.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-acf-fields.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-frontend-pages.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-rank-math-config.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-kyero-importer.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-inmopress-ai.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-events.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-notifications.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-automations.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-trigger-engine.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-condition-evaluator.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-action-executor.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-automation-manager.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-matching-engine.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-activity-logger.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-performance-optimizer.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-fake-content-seeder.php';
    }

    private function init_hooks()
    {
        add_action('init', array('Inmopress_CPTs', 'register'), 0);
        add_action('init', array('Inmopress_CPTs', 'maybe_migrate_email_templates'), 1);
        add_action('init', array('Inmopress_Taxonomies', 'register'), 0);
        add_action('init', array('Inmopress_Kyero_Importer', 'init'), 0);
        add_action('init', array('Inmopress_AI', 'init'), 0);
        add_action('init', array('Inmopress_Events', 'init'), 0);
        add_action('init', array('Inmopress_Notifications', 'init'), 0);
        add_action('init', array('Inmopress_Automations', 'init'), 0);
        add_action('init', array('Inmopress_Trigger_Engine', 'get_instance'), 0);
        add_action('init', array('Inmopress_Matching_Engine', 'get_instance'), 0);
        add_action('init', array('Inmopress_Activity_Logger', 'get_instance'), 0);
        add_action('init', array('Inmopress_Performance_Optimizer', 'get_instance'), 0);


        // Activación del plugin
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Flush rewrite rules en cada carga (temporal, para desarrollo)
        // Comentar en producción
        // add_action('init', 'flush_rewrite_rules');

        // ACF JSON Sync
        add_filter('acf/settings/save_json', array($this, 'acf_json_save_path'));
        add_filter('acf/settings/load_json', array($this, 'acf_json_load_paths'));

        // Relaciones bidireccionales
        add_filter('acf/update_value/name=agente', array($this, 'sync_agente_inmueble'), 10, 3);
        add_filter('acf/update_value/name=propietario', array($this, 'sync_propietario_inmueble'), 10, 3);
        add_action('acf/save_post', array($this, 'sync_owner_inmuebles'), 20);

        // Validaciones
        add_filter('acf/validate_value/name=referencia', array($this, 'validate_unique_ref'), 10, 4);

        // SEO - Rank Math integration
        add_filter('rank_math/vars/replacements', array($this, 'rank_math_add_vars'));
        add_filter('rank_math/replacements', array($this, 'rank_math_replace_vars'));
        add_filter('rank_math/json_ld', array($this, 'rank_math_add_schema'), 10, 2);

        // Configurar Rank Math automáticamente al activar
        add_action('rank_math/loaded', array($this, 'configure_rank_math'));

        // Añadir menú de configuración Rank Math
        add_action('admin_menu', array($this, 'add_rank_math_config_menu'));

        // Admin: Añadir páginas de utilidades
        add_action('admin_menu', array($this, 'add_seeder_menu'));
        add_action('admin_init', array($this, 'handle_seeder_action'));
        add_action('admin_menu', array($this, 'add_fake_content_menu'));
        add_action('admin_init', array($this, 'handle_fake_content_action'));
        add_action('admin_menu', array($this, 'add_acf_fields_menu'));
        add_action('admin_init', array($this, 'handle_acf_fields_action'));
        add_action('admin_menu', array($this, 'add_frontend_pages_menu'));
        add_action('admin_init', array($this, 'handle_frontend_pages_action'));
    }

    /**
     * Configurar ruta de guardado para ACF JSON
     */
    public function acf_json_save_path($path)
    {
        return INMOPRESS_CORE_PATH . 'acf-json';
    }

    /**
     * Configurar rutas de carga para ACF JSON
     */
    public function acf_json_load_paths($paths)
    {
        unset($paths[0]);
        $paths[] = INMOPRESS_CORE_PATH . 'acf-json';
        return $paths;
    }

    /**
     * Sincronizar relación bidireccional Inmueble <-> Agente
     * Cuando se asigna un agente a un inmueble, se vincula automáticamente el inmueble al agente
     */
    public function sync_agente_inmueble($value, $post_id, $field)
    {
        if ($value && get_post_type($post_id) === 'impress_property') {
            $inmuebles_agente = get_field('inmuebles', $value);
            if (!is_array($inmuebles_agente)) {
                $inmuebles_agente = array();
            }
            if (!in_array($post_id, $inmuebles_agente)) {
                $inmuebles_agente[] = $post_id;
                update_field('inmuebles', $inmuebles_agente, $value);
            }
        }
        return $value;
    }

    /**
     * Sincronizar relación bidireccional Inmueble <-> Propietario
     * Cuando se asigna un propietario a un inmueble, se vincula automáticamente el inmueble al propietario
     */
    public function sync_propietario_inmueble($value, $post_id, $field)
    {
        if (self::$syncing_owner) {
            return $value;
        }

        if (get_post_type($post_id) !== 'impress_property') {
            return $value;
        }

        $new_owner_id = 0;
        if (is_object($value) && isset($value->ID)) {
            $new_owner_id = (int) $value->ID;
        } elseif (is_numeric($value)) {
            $new_owner_id = (int) $value;
        }

        $old_owner_id = (int) get_post_meta($post_id, 'propietario', true);

        if ($old_owner_id && $old_owner_id !== $new_owner_id) {
            $current = get_field('inmuebles', $old_owner_id);
            if (!is_array($current)) {
                $current = array();
            }
            $current = array_map('intval', $current);
            $current = array_values(array_diff($current, array((int) $post_id)));
            update_field('inmuebles', $current, $old_owner_id);
        }

        if ($new_owner_id) {
            $current = get_field('inmuebles', $new_owner_id);
            if (!is_array($current)) {
                $current = array();
            }
            $current = array_map('intval', $current);
            if (!in_array((int) $post_id, $current, true)) {
                $current[] = (int) $post_id;
                $current = array_values(array_unique($current));
                update_field('inmuebles', $current, $new_owner_id);
            }
        }

        return $value;
    }

    /**
     * Sincronizar propiedades al guardar propietario (lista -> campo propietario en inmueble)
     */
    public function sync_owner_inmuebles($post_id)
    {
        if (self::$syncing_owner) {
            return;
        }

        if (!is_numeric($post_id)) {
            return;
        }

        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        if (get_post_type($post_id) !== 'impress_owner') {
            return;
        }

        if (!function_exists('get_field')) {
            return;
        }

        self::$syncing_owner = true;

        $owner_id = (int) $post_id;
        $selected = get_field('inmuebles', $owner_id);
        if (!is_array($selected)) {
            $selected = array();
        }

        $selected_ids = array_values(array_filter(array_map(function ($item) {
            if (is_object($item) && isset($item->ID)) {
                return (int) $item->ID;
            }
            if (is_numeric($item)) {
                return (int) $item;
            }
            return 0;
        }, $selected)));

        $current_ids = get_posts(array(
            'post_type' => 'impress_property',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'propietario',
                    'value' => $owner_id,
                    'compare' => '=',
                ),
            ),
        ));

        if (!is_array($current_ids)) {
            $current_ids = array();
        }

        $current_ids = array_values(array_filter(array_map('intval', $current_ids)));

        $to_add = array_diff($selected_ids, $current_ids);
        $to_remove = array_diff($current_ids, $selected_ids);

        foreach ($to_add as $property_id) {
            update_field('propietario', $owner_id, $property_id);
        }

        foreach ($to_remove as $property_id) {
            update_field('propietario', null, $property_id);
        }

        self::$syncing_owner = false;
    }

    /**
     * Validar que la referencia del inmueble sea única
     */
    public function validate_unique_ref($valid, $value, $field, $input)
    {
        if (!$valid || empty($value)) {
            return $valid;
        }

        global $post;
        if (!$post || $post->post_type !== 'impress_property') {
            return $valid;
        }

        $posts = get_posts(array(
            'post_type' => 'impress_property',
            'meta_key' => 'referencia',
            'meta_value' => $value,
            'post__not_in' => array($post->ID),
            'posts_per_page' => 1,
            'fields' => 'ids'
        ));

        if (!empty($posts)) {
            $valid = 'Esta referencia ya existe. Por favor, utiliza una referencia única.';
        }

        return $valid;
    }

    /**
     * Añadir variables personalizadas a Rank Math
     */
    public function rank_math_add_vars($replacements)
    {
        $replacements['impress_ref'] = array(
            'name' => 'Referencia Inmueble',
            'description' => 'Referencia del inmueble',
            'variable' => 'impress_ref',
            'example' => 'REF12345',
        );

        $replacements['impress_price'] = array(
            'name' => 'Precio Inmueble',
            'description' => 'Precio del inmueble',
            'variable' => 'impress_price',
            'example' => '250000',
        );

        $replacements['impress_city'] = array(
            'name' => 'Ciudad',
            'description' => 'Ciudad del inmueble',
            'variable' => 'impress_city',
            'example' => 'Valencia',
        );

        $replacements['impress_province'] = array(
            'name' => 'Provincia',
            'description' => 'Provincia del inmueble',
            'variable' => 'impress_province',
            'example' => 'Valencia',
        );

        $replacements['impress_property_type'] = array(
            'name' => 'Tipo de Vivienda',
            'description' => 'Tipo de vivienda del inmueble',
            'variable' => 'impress_property_type',
            'example' => 'Piso',
        );

        $replacements['impress_operation'] = array(
            'name' => 'Operación',
            'description' => 'Operación del inmueble (Venta/Alquiler)',
            'variable' => 'impress_operation',
            'example' => 'Venta',
        );

        $replacements['impress_rooms'] = array(
            'name' => 'Dormitorios',
            'description' => 'Número de dormitorios',
            'variable' => 'impress_rooms',
            'example' => '3',
        );

        $replacements['impress_bathrooms'] = array(
            'name' => 'Baños',
            'description' => 'Número de baños',
            'variable' => 'impress_bathrooms',
            'example' => '2',
        );

        $replacements['impress_surface'] = array(
            'name' => 'Superficie',
            'description' => 'Superficie construida en m²',
            'variable' => 'impress_surface',
            'example' => '120',
        );

        return $replacements;
    }

    /**
     * Rellenar las variables personalizadas de Rank Math
     */
    public function rank_math_replace_vars($replacements)
    {
        global $post;

        if ($post && $post->post_type === 'impress_property') {
            $replacements['impress_ref'] = get_field('referencia', $post->ID) ?: '';

            $precio_venta = get_field('precio_venta', $post->ID);
            $precio_alquiler = get_field('precio_alquiler', $post->ID);
            $precio = $precio_venta ?: $precio_alquiler;
            $replacements['impress_price'] = $precio ? number_format($precio, 0, ',', '.') . ' €' : '';

            $ciudad_terms = get_the_terms($post->ID, 'impress_city');
            $replacements['impress_city'] = $ciudad_terms && !is_wp_error($ciudad_terms) && !empty($ciudad_terms) ? $ciudad_terms[0]->name : '';

            $province_terms = get_the_terms($post->ID, 'impress_province');
            $replacements['impress_province'] = $province_terms && !is_wp_error($province_terms) && !empty($province_terms) ? $province_terms[0]->name : '';

            $property_type_terms = get_the_terms($post->ID, 'impress_property_type');
            $replacements['impress_property_type'] = $property_type_terms && !is_wp_error($property_type_terms) && !empty($property_type_terms) ? $property_type_terms[0]->name : '';

            $operation_terms = get_the_terms($post->ID, 'impress_operation');
            $replacements['impress_operation'] = $operation_terms && !is_wp_error($operation_terms) && !empty($operation_terms) ? $operation_terms[0]->name : '';

            $dormitorios = get_field('dormitorios', $post->ID);
            $replacements['impress_rooms'] = $dormitorios ? $dormitorios : '';

            $banos = get_field('banos', $post->ID);
            $replacements['impress_bathrooms'] = $banos ? $banos : '';

            $superficie = get_field('superficie_construida', $post->ID);
            $replacements['impress_surface'] = $superficie ? $superficie . ' m²' : '';
        }

        return $replacements;
    }

    /**
     * Añadir Schema.org RealEstateListing para inmuebles
     */
    public function rank_math_add_schema($data, $jsonld)
    {
        global $post;

        if ($post && $post->post_type === 'impress_property') {
            $precio_venta = get_field('precio_venta', $post->ID);
            $precio_alquiler = get_field('precio_alquiler', $post->ID);
            $precio = $precio_venta ?: $precio_alquiler;

            // Obtener operación
            $operation_terms = get_the_terms($post->ID, 'impress_operation');
            $operation = $operation_terms && !is_wp_error($operation_terms) && !empty($operation_terms)
                ? $operation_terms[0]->name
                : '';

            // Obtener ubicación
            $ciudad_terms = get_the_terms($post->ID, 'impress_city');
            $ciudad = $ciudad_terms && !is_wp_error($ciudad_terms) && !empty($ciudad_terms)
                ? $ciudad_terms[0]->name
                : '';

            $province_terms = get_the_terms($post->ID, 'impress_province');
            $provincia = $province_terms && !is_wp_error($province_terms) && !empty($province_terms)
                ? $province_terms[0]->name
                : '';

            // Construir dirección
            $direccion = get_field('direccion', $post->ID);
            $address_parts = array();
            if ($direccion)
                $address_parts[] = $direccion;
            if ($ciudad)
                $address_parts[] = $ciudad;
            if ($provincia)
                $address_parts[] = $provincia;
            $full_address = implode(', ', $address_parts);

            // Obtener coordenadas
            $coordenadas = get_field('coordenadas', $post->ID);
            $lat = '';
            $lng = '';
            if ($coordenadas && is_array($coordenadas)) {
                $lat = isset($coordenadas['lat']) ? $coordenadas['lat'] : '';
                $lng = isset($coordenadas['lng']) ? $coordenadas['lng'] : '';
            }

            // Obtener tipo de vivienda
            $property_type_terms = get_the_terms($post->ID, 'impress_property_type');
            $property_type = $property_type_terms && !is_wp_error($property_type_terms) && !empty($property_type_terms)
                ? $property_type_terms[0]->name
                : '';

            // Construir Schema
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'RealEstateListing',
                'name' => get_the_title($post->ID),
                'description' => wp_strip_all_tags(get_the_excerpt($post->ID) ?: get_field('descripcion', $post->ID)),
                'url' => get_permalink($post->ID),
                'priceCurrency' => 'EUR',
            );

            // Precio
            if ($precio) {
                $schema['price'] = floatval($precio);
            }

            // Imagen
            $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full');
            if ($thumbnail_url) {
                $schema['image'] = $thumbnail_url;
            }

            // Galería de imágenes
            $galeria = get_field('galeria', $post->ID);
            if ($galeria && is_array($galeria) && count($galeria) > 0) {
                $images = array();
                foreach ($galeria as $image) {
                    if (isset($image['url'])) {
                        $images[] = $image['url'];
                    }
                }
                if (!empty($images)) {
                    $schema['image'] = count($images) === 1 ? $images[0] : $images;
                }
            }

            // Dirección y ubicación
            if ($full_address) {
                $schema['address'] = array(
                    '@type' => 'PostalAddress',
                    'streetAddress' => $direccion ?: '',
                    'addressLocality' => $ciudad ?: '',
                    'addressRegion' => $provincia ?: '',
                    'addressCountry' => 'ES'
                );
            }

            // Coordenadas
            if ($lat && $lng) {
                $schema['geo'] = array(
                    '@type' => 'GeoCoordinates',
                    'latitude' => floatval($lat),
                    'longitude' => floatval($lng)
                );
            }

            // Características del inmueble
            $dormitorios = get_field('dormitorios', $post->ID);
            if ($dormitorios) {
                $schema['numberOfRooms'] = intval($dormitorios);
            }

            $banos = get_field('banos', $post->ID);
            if ($banos) {
                $schema['numberOfBathroomsTotal'] = intval($banos);
            }

            $superficie_util = get_field('superficie_util', $post->ID);
            $superficie_construida = get_field('superficie_construida', $post->ID);
            $superficie = $superficie_construida ?: $superficie_util;

            if ($superficie) {
                $schema['floorSize'] = array(
                    '@type' => 'QuantitativeValue',
                    'value' => floatval($superficie),
                    'unitCode' => 'MTK',
                    'unitText' => 'm²'
                );
            }

            // Tipo de vivienda
            if ($property_type) {
                $schema['category'] = $property_type;
            }

            // Operación (Venta/Alquiler)
            if ($operation) {
                $schema['businessFunction'] = $operation === 'Venta'
                    ? 'https://schema.org/Sell'
                    : 'https://schema.org/LeaseOut';
            }

            // Fecha de publicación
            $schema['datePosted'] = get_the_date('c', $post->ID);

            // Añadir al array de datos
            $data['RealEstateListing'] = $schema;
        }

        return $data;
    }

    /**
     * Configurar Rank Math automáticamente
     */
    public function configure_rank_math()
    {
        if (function_exists('Inmopress_Rank_Math_Config::configure')) {
            Inmopress_Rank_Math_Config::configure();
        }
    }

    /**
     * Añadir menú de configuración Rank Math
     */
    public function add_rank_math_config_menu()
    {
        add_submenu_page(
            'tools.php',
            'Configurar Rank Math',
            'Configurar Rank Math',
            'manage_options',
            'inmopress-rank-math-config',
            array($this, 'rank_math_config_page')
        );
    }

    /**
     * Página de configuración Rank Math
     */
    public function rank_math_config_page()
    {
        if (isset($_POST['configure_rank_math']) && check_admin_referer('inmopress_rank_math_config')) {
            $this->configure_rank_math();
            echo '<div class="notice notice-success"><p>Rank Math configurado correctamente.</p></div>';
        }

        $config = Inmopress_Rank_Math_Config::get_recommended_config();
        ?>
        <div class="wrap">
            <h1>Configuración Rank Math - Inmopress</h1>

            <div class="card">
                <h2>Estado de Rank Math</h2>
                <?php if (function_exists('rank_math')): ?>
                    <p style="color: green;">✅ Rank Math está activo</p>
                <?php else: ?>
                    <p style="color: red;">❌ Rank Math no está activo. Por favor, instala y activa Rank Math primero.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>Configuración Automática</h2>
                <p>Haz clic en el botón para configurar Rank Math automáticamente con los valores recomendados para Inmopress.
                </p>
                <form method="post">
                    <?php wp_nonce_field('inmopress_rank_math_config'); ?>
                    <input type="submit" name="configure_rank_math" class="button button-primary"
                        value="Configurar Rank Math Automáticamente">
                </form>
            </div>

            <div class="card">
                <h2>Configuración Recomendada</h2>

                <h3>1. Setup Wizard (Manual)</h3>
                <p>Ve a <strong>Rank Math → Dashboard → Setup Wizard</strong> y configura:</p>
                <ul>
                    <li><strong>Site Type:</strong> <?php echo esc_html($config['setup_wizard']['site_type']); ?></li>
                    <li><strong>Logo:</strong> <?php echo esc_html($config['setup_wizard']['logo']); ?></li>
                    <li><strong>Social Profiles:</strong> <?php echo esc_html($config['setup_wizard']['social_profiles']); ?>
                    </li>
                    <li><strong>Sitemap:</strong> <?php echo esc_html($config['setup_wizard']['sitemap']); ?></li>
                    <li><strong>404 Monitor:</strong> <?php echo esc_html($config['setup_wizard']['404_monitor']); ?></li>
                    <li><strong>Redirections:</strong> <?php echo esc_html($config['setup_wizard']['redirections']); ?></li>
                </ul>

                <h3>2. Títulos y Meta Descriptions</h3>
                <p>Ve a <strong>Rank Math → Titles & Meta → Post Types → Inmuebles</strong></p>
                <p><strong>Title Template:</strong></p>
                <code><?php echo esc_html($config['titles_meta']['title_template']); ?></code>
                <p><strong>Description Template:</strong></p>
                <code><?php echo esc_html($config['titles_meta']['description_template']); ?></code>

                <h3>3. Sitemap</h3>
                <p>Ve a <strong>Rank Math → Sitemap Settings</strong></p>
                <p><strong>Incluir Post Types:</strong></p>
                <ul>
                    <?php foreach ($config['sitemap']['include_post_types'] as $pt => $include): ?>
                        <li><?php echo $include ? '✅' : '❌'; ?>             <?php echo esc_html($pt); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Excluir Post Types:</strong></p>
                <ul>
                    <?php foreach ($config['sitemap']['exclude_post_types'] as $pt => $exclude): ?>
                        <li><?php echo $exclude ? '❌' : '✅'; ?>             <?php echo esc_html($pt); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Incluir Taxonomías:</strong></p>
                <ul>
                    <?php foreach ($config['sitemap']['include_taxonomies'] as $tax => $include): ?>
                        <li><?php echo $include ? '✅' : '❌'; ?>             <?php echo esc_html($tax); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <h2>Variables Personalizadas Disponibles</h2>
                <p>Estas variables están disponibles para usar en títulos y meta descriptions:</p>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Descripción</th>
                            <th>Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>%impress_ref%</code></td>
                            <td>Referencia del inmueble</td>
                            <td>REF12345</td>
                        </tr>
                        <tr>
                            <td><code>%impress_price%</code></td>
                            <td>Precio formateado</td>
                            <td>250.000 €</td>
                        </tr>
                        <tr>
                            <td><code>%impress_city%</code></td>
                            <td>Ciudad</td>
                            <td>Valencia</td>
                        </tr>
                        <tr>
                            <td><code>%impress_province%</code></td>
                            <td>Provincia</td>
                            <td>Valencia</td>
                        </tr>
                        <tr>
                            <td><code>%impress_property_type%</code></td>
                            <td>Tipo de vivienda</td>
                            <td>Piso</td>
                        </tr>
                        <tr>
                            <td><code>%impress_operation%</code></td>
                            <td>Operación (Venta/Alquiler)</td>
                            <td>Venta</td>
                        </tr>
                        <tr>
                            <td><code>%impress_rooms%</code></td>
                            <td>Número de dormitorios</td>
                            <td>3</td>
                        </tr>
                        <tr>
                            <td><code>%impress_bathrooms%</code></td>
                            <td>Número de baños</td>
                            <td>2</td>
                        </tr>
                        <tr>
                            <td><code>%impress_surface%</code></td>
                            <td>Superficie construida</td>
                            <td>120 m²</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>Schema.org RealEstateListing</h2>
                <p>El Schema.org RealEstateListing se genera automáticamente para todos los inmuebles e incluye:</p>
                <ul>
                    <li>Información básica (nombre, descripción, URL)</li>
                    <li>Precio y moneda</li>
                    <li>Imágenes (destacada y galería)</li>
                    <li>Dirección completa (PostalAddress)</li>
                    <li>Coordenadas GPS (GeoCoordinates)</li>
                    <li>Características (dormitorios, baños, superficie)</li>
                    <li>Tipo de vivienda</li>
                    <li>Operación (Venta/Alquiler)</li>
                    <li>Fecha de publicación</li>
                </ul>
                <p><strong>Validación:</strong> Usa <a href="https://search.google.com/test/rich-results" target="_blank">Google
                        Rich Results Test</a> para validar el Schema.</p>
            </div>
        </div>
        <?php
    }

    /**
     * Añadir menú para poblar taxonomías
     */
    public function add_seeder_menu()
    {
        add_submenu_page(
            'tools.php',
            'Poblar Taxonomías Inmopress',
            'Poblar Taxonomías',
            'manage_options',
            'inmopress-seed-taxonomies',
            array($this, 'seeder_page')
        );
    }

    /**
     * Manejar acciones del seeder
     */
    public function handle_seeder_action()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'inmopress-seed-taxonomies') {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['seed_taxonomies']) && check_admin_referer('inmopress_seed_taxonomies')) {
            Inmopress_Taxonomy_Seeder::seed_all();
            add_action('admin_notices', function () {
                echo '<div class="notice notice-success is-dismissible"><p>Taxonomías pobladas correctamente.</p></div>';
            });
        }

        if (isset($_POST['clear_taxonomies']) && check_admin_referer('inmopress_clear_taxonomies')) {
            Inmopress_Taxonomy_Seeder::clear_all();
            add_action('admin_notices', function () {
                echo '<div class="notice notice-success is-dismissible"><p>Todos los términos han sido eliminados.</p></div>';
            });
        }
    }

    /**
     * Añadir menú para generar contenido fake
     */
    public function add_fake_content_menu()
    {
        add_submenu_page(
            'tools.php',
            'Contenido Fake Inmopress',
            'Contenido Fake',
            'manage_options',
            'inmopress-fake-content',
            array($this, 'fake_content_page')
        );
    }

    /**
     * Manejar acciones del contenido fake
     */
    public function handle_fake_content_action()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'inmopress-fake-content') {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['seed_fake_content']) && check_admin_referer('inmopress_seed_fake_content')) {
            $counts = array();
            if (isset($_POST['fake_counts']) && is_array($_POST['fake_counts'])) {
                foreach ($_POST['fake_counts'] as $post_type => $count) {
                    $counts[$post_type] = intval($count);
                }
            }
            $stats = Inmopress_Fake_Content_Seeder::seed_all($counts);
            add_action('admin_notices', function () use ($stats) {
                $total = isset($stats['total_created']) ? intval($stats['total_created']) : 0;
                echo '<div class="notice notice-success is-dismissible"><p>Contenido fake creado. Total: ' . esc_html($total) . '.</p></div>';
            });
        }

        if (isset($_POST['clear_fake_content']) && check_admin_referer('inmopress_clear_fake_content')) {
            $deleted = Inmopress_Fake_Content_Seeder::clear_all();
            add_action('admin_notices', function () use ($deleted) {
                echo '<div class="notice notice-success is-dismissible"><p>Contenido fake eliminado. Total borrados: ' . esc_html($deleted) . '.</p></div>';
            });
        }
    }

    /**
     * Añadir menú para crear campos ACF
     */
    public function add_acf_fields_menu()
    {
        add_submenu_page(
            'tools.php',
            'Crear Campos ACF Inmopress',
            'Crear Campos ACF',
            'manage_options',
            'inmopress-create-acf-fields',
            array($this, 'acf_fields_page')
        );
    }

    /**
     * Manejar acciones de creación de campos ACF
     */
    public function handle_acf_fields_action()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'inmopress-create-acf-fields') {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['create_acf_fields']) && check_admin_referer('inmopress_create_acf_fields')) {
            if (!function_exists('acf_add_local_field_group')) {
                add_action('admin_notices', function () {
                    echo '<div class="notice notice-error is-dismissible"><p>ACF Pro no está activo. Por favor, activa ACF Pro primero.</p></div>';
                });
            } else {
                $result = Inmopress_ACF_Fields::create_all_field_groups();
                if ($result) {
                    add_action('admin_notices', function () {
                        echo '<div class="notice notice-success is-dismissible"><p>Field Groups de ACF creados correctamente. Los campos ahora están disponibles en los editores de posts.</p></div>';
                    });
                } else {
                    add_action('admin_notices', function () {
                        echo '<div class="notice notice-error is-dismissible"><p>Error al crear los Field Groups. Verifica que ACF Pro esté activo.</p></div>';
                    });
                }
            }
        }
    }

    /**
     * Añadir menú para crear páginas frontend
     */
    public function add_frontend_pages_menu()
    {
        add_submenu_page(
            'tools.php',
            'Crear Páginas Panel Frontend',
            'Crear Páginas Panel',
            'manage_options',
            'inmopress-create-frontend-pages',
            array($this, 'frontend_pages_page')
        );
    }

    /**
     * Manejar acciones de creación de páginas frontend
     */
    public function handle_frontend_pages_action()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'inmopress-create-frontend-pages') {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['create_pages']) && check_admin_referer('inmopress_create_frontend_pages')) {
            $pages = Inmopress_Frontend_Pages::create_all_pages();
            add_action('admin_notices', function () use ($pages) {
                $created = count(array_filter($pages, function ($p) {
                    return $p['status'] === 'created';
                }));
                $existed = count(array_filter($pages, function ($p) {
                    return $p['status'] === 'existed';
                }));
                $panel_url = class_exists('Inmopress_Shortcodes')
                    ? Inmopress_Shortcodes::panel_url()
                    : home_url('/mi-panel/');
                $message = sprintf(
                    'Páginas procesadas: %d creadas, %d ya existían. <a href="%s" target="_blank">Ver Panel</a>',
                    $created,
                    $existed,
                    $panel_url
                );
                echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
            });
        }

        if (isset($_POST['delete_pages']) && check_admin_referer('inmopress_delete_frontend_pages')) {
            $deleted = Inmopress_Frontend_Pages::delete_all_pages();
            add_action('admin_notices', function () use ($deleted) {
                echo '<div class="notice notice-success is-dismissible"><p>' . count($deleted) . ' páginas eliminadas.</p></div>';
            });
        }
    }

    /**
     * Página para crear páginas frontend
     */
    public function frontend_pages_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $frontend_active = is_plugin_active('inmopress-frontend/inmopress-frontend.php');
        ?>
        <div class="wrap">
            <h1>Crear Páginas del Panel Frontend</h1>

            <?php if (!$frontend_active): ?>
                <div class="notice notice-warning">
                    <p><strong>Plugin Inmopress Frontend no está activo.</strong> Por favor, activa el plugin "Inmopress Frontend"
                        antes de crear las páginas.</p>
                </div>
            <?php endif; ?>

            <div class="card">
                <h2>Crear Páginas del Panel</h2>
                <p>Este proceso creará automáticamente todas las páginas necesarias para el panel frontend:</p>

                <h3>Páginas que se crearán:</h3>
                <ul>
                    <li><strong>Mi Panel</strong> (<code>/mi-panel/</code>) - Dashboard principal (redirige a admin)</li>
                    <li><strong>Inmuebles</strong> (<code>/inmuebles/</code>) - Listado de inmuebles</li>
                    <li><strong>Nuevo Inmueble</strong> (<code>/nuevo-inmueble/</code>) - Formulario nuevo inmueble</li>
                    <li><strong>Editar Inmueble</strong> (<code>/editar-inmueble/</code>) - Formulario editar inmueble</li>
                    <li><strong>Clientes</strong> (<code>/clientes/</code>) - Listado de clientes</li>
                    <li><strong>Nuevo Cliente</strong> (<code>/nuevo-cliente/</code>) - Formulario nuevo cliente</li>
                    <li><strong>Visitas</strong> (<code>/visitas/</code>) - Listado de visitas</li>
                    <li><strong>Nueva Visita</strong> (<code>/nueva-visita/</code>) - Formulario nueva visita</li>
                    <li><strong>Propietarios</strong> (<code>/propietarios/</code>) - Listado de propietarios</li>
                    <li><strong>Print Property</strong> (<code>/print-property/</code>) - Página de impresión</li>
                </ul>

                <p><strong>Configuración automática:</strong></p>
                <ul>
                    <li>Las páginas se configurarán con template Full Width</li>
                    <li>Header y Footer deshabilitados (solo para print-property)</li>
                    <li>Títulos deshabilitados</li>
                    <li>Jerarquía de páginas configurada (páginas hijas)</li>
                </ul>

                <form method="post" action="">
                    <?php wp_nonce_field('inmopress_create_frontend_pages'); ?>
                    <p>
                        <button type="submit" name="create_pages" class="button button-primary" <?php echo !$frontend_active ? 'disabled' : ''; ?>
                            onclick="return confirm('¿Estás seguro de crear todas las páginas del panel? Si ya existen, se actualizarán.');">
                            Crear Páginas del Panel
                        </button>
                    </p>
                </form>

                <?php if ($frontend_active): ?>
                    <?php
                    $panel_url = class_exists('Inmopress_Shortcodes')
                        ? Inmopress_Shortcodes::panel_url()
                        : home_url('/mi-panel/');
                    ?>
                    <p><strong>Una vez creadas, podrás acceder al panel en:</strong> <a
                            href="<?php echo esc_url($panel_url); ?>"
                            target="_blank"><?php echo esc_url($panel_url); ?></a></p>
                <?php endif; ?>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2>Eliminar Páginas del Panel</h2>
                <p><strong>Advertencia:</strong> Esto eliminará todas las páginas del panel frontend.</p>
                <form method="post" action="">
                    <?php wp_nonce_field('inmopress_delete_frontend_pages'); ?>
                    <p>
                        <button type="submit" name="delete_pages" class="button button-secondary"
                            onclick="return confirm('¿Estás SEGURO? Esto eliminará TODAS las páginas del panel. Esta acción no se puede deshacer.');">
                            Eliminar Todas las Páginas
                        </button>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Página para crear campos ACF
     */
    public function acf_fields_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $acf_active = function_exists('acf_add_local_field_group');
        ?>
        <div class="wrap">
            <h1>Crear Campos ACF Inmopress</h1>

            <?php if (!$acf_active): ?>
                <div class="notice notice-error">
                    <p><strong>ACF Pro no está activo.</strong> Por favor, activa ACF Pro antes de crear los campos.</p>
                </div>
            <?php endif; ?>

            <div class="card">
                <h2>Crear Field Groups de ACF</h2>
                <p>Este proceso creará automáticamente todos los Field Groups de ACF para Inmopress:</p>

                <h3>Inmuebles (9 Field Groups):</h3>
                <ul>
                    <li>Información General (7 campos)</li>
                    <li>Ubicación (3 campos)</li>
                    <li>Relaciones (3 campos)</li>
                    <li>Características Físicas (14 campos)</li>
                    <li>Detalles Técnicos (8 campos)</li>
                    <li>Costes y Gastos (4 campos)</li>
                    <li>Datos Venta (7 campos)</li>
                    <li>Datos Alquiler (12 campos)</li>
                    <li>Media (4 campos)</li>
                </ul>

                <h3>Otros CPTs:</h3>
                <ul>
                    <li><strong>Clientes:</strong> 1 Field Group (8 campos)</li>
                    <li><strong>Leads:</strong> 1 Field Group (4 campos)</li>
                    <li><strong>Visitas:</strong> 1 Field Group (5 campos)</li>
                    <li><strong>Agencias:</strong> 1 Field Group (4 campos)</li>
                    <li><strong>Agentes:</strong> 1 Field Group (5 campos) - Incluye campo "inmuebles" para relación
                        bidireccional</li>
                    <li><strong>Propietarios:</strong> 1 Field Group (4 campos)</li>
                    <li><strong>Promociones:</strong> 1 Field Group (4 campos)</li>
                </ul>

                <p><strong>Total:</strong> 15 Field Groups con aproximadamente 100+ campos.</p>

                <form method="post" action="">
                    <?php wp_nonce_field('inmopress_create_acf_fields'); ?>
                    <p>
                        <button type="submit" name="create_acf_fields" class="button button-primary" <?php echo !$acf_active ? 'disabled' : ''; ?>
                            onclick="return confirm('¿Estás seguro de crear todos los Field Groups de ACF? Esto añadirá todos los campos a los editores correspondientes.');">
                            Crear Field Groups de ACF
                        </button>
                    </p>
                </form>

                <p><em>Nota: Los campos se crearán como "Local JSON" y se guardarán automáticamente en la carpeta acf-json del
                        plugin.</em></p>
            </div>
        </div>
        <?php
    }

    /**
     * Página del seeder
     */
    public function seeder_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Poblar Taxonomías Inmopress</h1>

            <div class="card">
                <h2>Poblar Taxonomías</h2>
                <p>Este proceso creará automáticamente todos los términos iniciales para las taxonomías de Inmopress:</p>
                <ul>
                    <li><strong>Operación:</strong> Venta, Alquiler, Alquiler vacacional, Traspaso</li>
                    <li><strong>Tipo de Vivienda:</strong> Piso, Ático, Dúplex, Chalet, Adosado, Estudio, Oficina, Local,
                        Terreno, Garaje, etc.</li>
                    <li><strong>Estado Conservación:</strong> A estrenar, Muy bueno, Bueno, A reformar, etc.</li>
                    <li><strong>Certificación Energética:</strong> A, B, C, D, E, F, G, En trámite, Exento</li>
                    <li><strong>Calefacción:</strong> Gas natural, Gasoil, Eléctrica, Aerotermia, etc.</li>
                    <li><strong>Orientación:</strong> Norte, Sur, Este, Oeste, Múltiple, etc.</li>
                    <li><strong>Estado Lead:</strong> Nuevo, Contactado, Calificado, Interesado, etc.</li>
                    <li><strong>Canal de Entrada:</strong> Web, Teléfono, Email, Redes sociales, etc.</li>
                    <li><strong>Idioma:</strong> Español, Inglés, Francés, Alemán, etc.</li>
                    <li><strong>Estado Visita:</strong> Programada, Confirmada, Realizada, Cancelada, etc.</li>
                    <li><strong>Especialización Agente:</strong> Venta, Alquiler, Comercial, Residencial, etc.</li>
                    <li><strong>Estado Promoción:</strong> Planificación, En construcción, En venta, etc.</li>
                    <li><strong>Provincias y Ciudades:</strong> Principales provincias y ciudades de España</li>
                </ul>

                <form method="post" action="">
                    <?php wp_nonce_field('inmopress_seed_taxonomies'); ?>
                    <p>
                        <button type="submit" name="seed_taxonomies" class="button button-primary"
                            onclick="return confirm('¿Estás seguro de poblar todas las taxonomías? Esto añadirá los términos iniciales.');">
                            Poblar Taxonomías
                        </button>
                    </p>
                </form>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2>Limpiar Taxonomías</h2>
                <p><strong>Advertencia:</strong> Esto eliminará TODOS los términos de todas las taxonomías de Inmopress.</p>
                <form method="post" action="">
                    <?php wp_nonce_field('inmopress_clear_taxonomies'); ?>
                    <p>
                        <button type="submit" name="clear_taxonomies" class="button button-secondary"
                            onclick="return confirm('¿Estás SEGURO? Esto eliminará TODOS los términos de todas las taxonomías. Esta acción no se puede deshacer.');">
                            Limpiar Todas las Taxonomías
                        </button>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Página para generar contenido fake
     */
    public function fake_content_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $post_types = Inmopress_Fake_Content_Seeder::get_post_types();
        $defaults = Inmopress_Fake_Content_Seeder::get_default_counts();
        ?>
        <div class="wrap">
            <h1>Contenido Fake Inmopress</h1>

            <div class="card">
                <h2>Generar contenido</h2>
                <p>Genera contenido de prueba para los CPTs y rellena automáticamente los campos ACF.</p>

                <form method="post" action="">
                    <?php wp_nonce_field('inmopress_seed_fake_content'); ?>
                    <table class="widefat striped" style="max-width: 900px;">
                        <thead>
                            <tr>
                                <th>Post Type</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($post_types as $post_type => $label) : ?>
                                <tr>
                                    <td><?php echo esc_html($label . ' (' . $post_type . ')'); ?></td>
                                    <td>
                                        <input type="number" name="fake_counts[<?php echo esc_attr($post_type); ?>]" min="0"
                                            value="<?php echo esc_attr(isset($defaults[$post_type]) ? $defaults[$post_type] : 0); ?>" />
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p>
                        <button type="submit" name="seed_fake_content" class="button button-primary"
                            onclick="return confirm('¿Crear contenido fake y rellenar ACFs?');">
                            Generar Contenido Fake
                        </button>
                    </p>
                </form>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2>Limpiar contenido fake</h2>
                <p><strong>Advertencia:</strong> Se eliminará TODO el contenido generado por esta herramienta.</p>
                <form method="post" action="">
                    <?php wp_nonce_field('inmopress_clear_fake_content'); ?>
                    <p>
                        <button type="submit" name="clear_fake_content" class="button button-secondary"
                            onclick="return confirm('¿Eliminar todo el contenido fake? Esta acción no se puede deshacer.');">
                            Limpiar Contenido Fake
                        </button>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }

    public function activate()
    {
        Inmopress_CPTs::register();
        Inmopress_Taxonomies::register();
        Inmopress_Roles::create_roles();
        
        // Crear tablas de automatizaciones
        $this->create_automation_tables();
        
        // Crear índices de base de datos para optimización
        if (class_exists('Inmopress_Performance_Optimizer')) {
            Inmopress_Performance_Optimizer::get_instance()->create_database_indexes();
        }
        
        flush_rewrite_rules();
    }

    /**
     * Crear tablas necesarias para automatizaciones
     */
    private function create_automation_tables()
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabla de automatizaciones
        $table_automations = $wpdb->prefix . 'inmopress_automations';
        $sql_automations = "CREATE TABLE IF NOT EXISTS {$table_automations} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            trigger_type varchar(100) NOT NULL,
            trigger_config longtext,
            conditions longtext,
            actions longtext,
            is_active tinyint(1) DEFAULT 1,
            run_count int(11) DEFAULT 0,
            last_run_at datetime,
            created_at datetime NOT NULL,
            updated_at datetime,
            PRIMARY KEY (id),
            KEY trigger_type (trigger_type),
            KEY is_active (is_active)
        ) $charset_collate;";
        dbDelta($sql_automations);
        
        // Tabla de logs de automatizaciones
        $table_logs = $wpdb->prefix . 'inmopress_automation_logs';
        $sql_logs = "CREATE TABLE IF NOT EXISTS {$table_logs} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            automation_id bigint(20) NOT NULL,
            trigger_data longtext,
            conditions_met tinyint(1),
            actions_executed int(11),
            status varchar(20),
            error_message text,
            execution_time float,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY automation_id (automation_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_logs);
        
        // Tabla de matching scores
        $table_matching = $wpdb->prefix . 'inmopress_matching_scores';
        $sql_matching = "CREATE TABLE IF NOT EXISTS {$table_matching} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            property_id bigint(20) NOT NULL,
            client_id bigint(20) NOT NULL,
            score int(11) NOT NULL,
            score_breakdown longtext,
            notified tinyint(1) DEFAULT 0,
            notified_at datetime,
            calculated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY property_client (property_id, client_id),
            KEY score (score),
            KEY notified (notified)
        ) $charset_collate;";
        dbDelta($sql_matching);
        
        // Tabla de activity log
        $table_activity = $wpdb->prefix . 'inmopress_activity_log';
        $sql_activity = "CREATE TABLE IF NOT EXISTS {$table_activity} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            action varchar(100) NOT NULL,
            object_type varchar(50) NOT NULL,
            object_id bigint(20) NOT NULL,
            data longtext,
            ip_address varchar(45),
            user_agent varchar(255),
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY object_type_id (object_type, object_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_activity);
    }
}

function inmopress_core()
{
    return Inmopress_Core::get_instance();
}
add_action('plugins_loaded', 'inmopress_core');
