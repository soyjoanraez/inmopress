<?php
/**
 * Property Shortcode
 *
 * Shortcode para mostrar listado de propiedades
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Property Shortcode class
 */
class Property_Shortcode
{

    /**
     * Instance of this class
     *
     * @var Property_Shortcode
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Property_Shortcode
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
        add_shortcode('inmopress_properties', array($this, 'render_shortcode'));
    }

    /**
     * Render shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_shortcode($atts)
    {
        // Parse attributes
        $atts = shortcode_atts(array(
            'layout' => 'grid',
            'columns' => '3',
            'posts_per_page' => '9',
            'proposito' => '',
            'provincia' => '',
            'poblacion' => '',
            'tipo_vivienda' => '',
            'precio_min' => '',
            'precio_max' => '',
            'dormitorios_min' => '',
            'banos_min' => '',
            'superficie_min' => '',
            'orderby' => 'precio',
            'order' => 'ASC',
            'mostrar_campos' => '',
            'ocultar_campos' => '',
            'show_filters' => 'false',
        ), $atts, 'inmopress_properties');

        // Build filters array
        $filters = array();
        if (!empty($atts['proposito'])) {
            $filters['proposito'] = $atts['proposito'];
        }
        if (!empty($atts['provincia'])) {
            $filters['provincia'] = $atts['provincia'];
        }
        if (!empty($atts['poblacion'])) {
            $filters['poblacion'] = $atts['poblacion'];
        }
        if (!empty($atts['tipo_vivienda'])) {
            $filters['tipo_vivienda'] = $atts['tipo_vivienda'];
        }
        if (!empty($atts['precio_min'])) {
            $filters['precio_min'] = $atts['precio_min'];
        }
        if (!empty($atts['precio_max'])) {
            $filters['precio_max'] = $atts['precio_max'];
        }
        if (!empty($atts['dormitorios_min'])) {
            $filters['dormitorios_min'] = $atts['dormitorios_min'];
        }
        if (!empty($atts['banos_min'])) {
            $filters['banos_min'] = $atts['banos_min'];
        }
        if (!empty($atts['superficie_min'])) {
            $filters['superficie_min'] = $atts['superficie_min'];
        }
        if (!empty($atts['orderby'])) {
            $filters['orderby'] = $atts['orderby'];
        }
        if (!empty($atts['order'])) {
            $filters['order'] = $atts['order'];
        }
        $filters['posts_per_page'] = absint($atts['posts_per_page']);

        // Get query args
        $query_args = Property_Query::build_query_args($filters);
        $query = new \WP_Query($query_args);

        // Start output buffering
        ob_start();

        // Determine layout
        $layout = in_array($atts['layout'], array('grid', 'list')) ? $atts['layout'] : 'grid';
        $columns = absint($atts['columns']);
        if ($columns < 1 || $columns > 4) {
            $columns = 3;
        }

        // Get settings
        $settings = Property_Settings::get_instance();

        // Show filters if requested
        if ($atts['show_filters'] === 'true') {
            $this->render_filters($filters);
        }

        // Render properties
        if ($query->have_posts()) {
            echo '<div class="inmopress-properties-container inmopress-properties-' . esc_attr($layout) . ' inmopress-properties-columns-' . esc_attr($columns) . '">';
            
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_property_card(get_the_ID(), $layout, $settings);
            }
            
            echo '</div>';

            // Pagination
            if ($query->max_num_pages > 1) {
                echo '<div class="inmopress-properties-pagination">';
                echo paginate_links(array(
                    'total' => $query->max_num_pages,
                    'current' => max(1, get_query_var('paged')),
                    'format' => '?paged=%#%',
                ));
                echo '</div>';
            }
        } else {
            echo '<p class="inmopress-no-properties">No se encontraron propiedades.</p>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Render property card
     *
     * @param int $post_id Post ID
     * @param string $layout Layout type (grid/list)
     * @param Property_Settings $settings Settings instance
     */
    private function render_property_card($post_id, $layout, $settings)
    {
        $template_file = INMOPRESS_THEME_DIR . '/templates/properties/property-card-' . $layout . '.php';
        
        if (file_exists($template_file)) {
            // Set variables for template
            $GLOBALS['inmopress_post_id'] = $post_id;
            $GLOBALS['inmopress_settings'] = $settings;
            include $template_file;
            unset($GLOBALS['inmopress_post_id'], $GLOBALS['inmopress_settings']);
        } else {
            // Fallback to basic card
            $this->render_basic_card($post_id, $settings);
        }
    }

    /**
     * Render basic card (fallback)
     *
     * @param int $post_id Post ID
     * @param Property_Settings $settings Settings instance
     */
    private function render_basic_card($post_id, $settings)
    {
        $titulo = get_field('titulo_seo', $post_id) ?: get_the_title($post_id);
        $referencia = get_field('referencia', $post_id);
        $precio_venta = get_field('precio_venta', $post_id);
        $precio_alquiler = get_field('precio_alquiler', $post_id);
        $proposito = get_field('proposito', $post_id);
        $precio = ($proposito === 'venta') ? $precio_venta : $precio_alquiler;
        ?>
        <div class="inmopress-property-card">
            <?php if (has_post_thumbnail($post_id)) : ?>
                <div class="property-image">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                        <?php echo get_the_post_thumbnail($post_id, 'medium'); ?>
                    </a>
                </div>
            <?php endif; ?>
            <div class="property-content">
                <h3 class="property-title">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                        <?php echo esc_html($titulo); ?>
                    </a>
                </h3>
                <?php if ($referencia) : ?>
                    <div class="property-reference">Ref: <?php echo esc_html($referencia); ?></div>
                <?php endif; ?>
                <?php if ($precio) : ?>
                    <div class="property-price"><?php echo number_format($precio, 0, ',', '.'); ?> €</div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render filters
     *
     * @param array $current_filters Current filter values
     */
    private function render_filters($current_filters = array())
    {
        $template_file = INMOPRESS_THEME_DIR . '/templates/properties/property-filters.php';
        
        if (file_exists($template_file)) {
            include $template_file;
        }
    }
}

