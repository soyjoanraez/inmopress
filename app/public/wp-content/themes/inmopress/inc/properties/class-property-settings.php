<?php
/**
 * Property Card Settings
 *
 * Panel de control para configurar qué campos mostrar en las cards
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Property Settings class
 */
class Property_Settings
{

    /**
     * Instance of this class
     *
     * @var Property_Settings
     */
    private static $instance = null;

    /**
     * Option name for card settings
     *
     * @var string
     */
    private $option_name = 'inmopress_property_card_settings';

    /**
     * Get instance
     *
     * @return Property_Settings
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
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add settings page to admin menu
     */
    public function add_settings_page()
    {
        add_submenu_page(
            'options-general.php',
            'Configuración de Cards de Propiedades',
            'Cards Propiedades',
            'manage_options',
            'inmopress-property-cards',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('inmopress_property_cards', $this->option_name, array(
            'sanitize_callback' => array($this, 'sanitize_settings'),
        ));
    }

    /**
     * Sanitize settings
     *
     * @param array $input Raw input
     * @return array Sanitized settings
     */
    public function sanitize_settings($input)
    {
        $sanitized = array();

        // Campos ACF visibles
        if (isset($input['acf_fields'])) {
            $sanitized['acf_fields'] = array_map('sanitize_text_field', $input['acf_fields']);
        }

        // Taxonomías visibles
        if (isset($input['taxonomies'])) {
            $sanitized['taxonomies'] = array_map('sanitize_text_field', $input['taxonomies']);
        }

        // Orden de campos
        if (isset($input['field_order'])) {
            // Si ya es un array, usarlo directamente; si es string, hacer explode
            if (is_array($input['field_order'])) {
                $sanitized['field_order'] = array_map('sanitize_text_field', $input['field_order']);
            } else {
                $sanitized['field_order'] = array_map('sanitize_text_field', explode(',', $input['field_order']));
            }
        }

        // Layout por defecto
        if (isset($input['default_layout'])) {
            $sanitized['default_layout'] = sanitize_text_field($input['default_layout']);
        }

        // Columnas por defecto
        if (isset($input['default_columns'])) {
            $sanitized['default_columns'] = absint($input['default_columns']);
        }

        return $sanitized;
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = $this->get_settings();
        $available_acf_fields = $this->get_available_acf_fields();
        $available_taxonomies = $this->get_available_taxonomies();
        $option_name = $this->option_name;

        include INMOPRESS_THEME_DIR . '/inc/properties/admin/property-card-settings.php';
    }

    /**
     * Get available ACF fields
     *
     * @return array
     */
    public function get_available_acf_fields()
    {
        return array(
            'referencia' => 'Referencia',
            'titulo_seo' => 'Título SEO',
            'proposito' => 'Propósito',
            'direccion' => 'Dirección',
            'zona' => 'Zona',
            'orientacion' => 'Orientación',
            'agrupacion' => 'Agrupación',
            'agrupacion_especial' => 'Agrupación Especial',
            'estado' => 'Estado',
            'superficie_util' => 'Superficie Útil',
            'superficie_construida' => 'Superficie Construida',
            'superficie_parcela' => 'Superficie Parcela',
            'plantas' => 'Plantas',
            'ano' => 'Año Construcción',
            'dormitorios' => 'Dormitorios',
            'banos' => 'Baños',
            'banos_suite' => 'Baños en Suite',
            'cocinas' => 'Cocinas',
            'salones' => 'Salones',
            'balcones' => 'Balcones',
            'terrazas' => 'Terrazas',
            'trasteros' => 'Trasteros',
            'certificacion_energetica' => 'Certificación Energética',
            'calefaccion' => 'Calefacción',
            'jardin' => 'Jardín',
            'piscina' => 'Piscina',
            'garajes' => 'Garajes',
            'amueblado' => 'Amueblado',
            'precio_venta' => 'Precio Venta',
            'precio_alquiler' => 'Precio Alquiler',
            'solo_vip' => 'Solo VIP',
            'exclusiva' => 'Exclusiva',
            'vendida' => 'Vendida',
            'reservada' => 'Reservada',
        );
    }

    /**
     * Get available taxonomies
     *
     * @return array
     */
    public function get_available_taxonomies()
    {
        return array(
            'impress_province' => 'Provincia',
            'impress_municipality' => 'Población',
            'impress_property_type' => 'Tipo de Vivienda',
            'impress_operation' => 'Operación',
        );
    }

    /**
     * Get settings
     *
     * @return array
     */
    public function get_settings()
    {
        $defaults = array(
            'acf_fields' => array('referencia', 'titulo_seo', 'proposito', 'zona', 'dormitorios', 'banos', 'superficie_util', 'precio_venta', 'precio_alquiler'),
            'taxonomies' => array('impress_province', 'impress_municipality', 'impress_property_type'),
            'field_order' => array('foto', 'titulo', 'referencia', 'localizacion', 'precio', 'dormitorios', 'banos', 'superficie'),
            'default_layout' => 'grid',
            'default_columns' => 3,
        );

        $settings = get_option($this->option_name, array());
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get visible fields for cards
     *
     * @return array
     */
    public function get_visible_fields()
    {
        $settings = $this->get_settings();
        return isset($settings['acf_fields']) ? $settings['acf_fields'] : array();
    }

    /**
     * Get visible taxonomies for cards
     *
     * @return array
     */
    public function get_visible_taxonomies()
    {
        $settings = $this->get_settings();
        return isset($settings['taxonomies']) ? $settings['taxonomies'] : array();
    }
}

