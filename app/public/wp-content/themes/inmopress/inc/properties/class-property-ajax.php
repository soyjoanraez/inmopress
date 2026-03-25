<?php
/**
 * Property AJAX Handlers
 *
 * Endpoints AJAX para filtros dinámicos
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Property AJAX class
 */
class Property_Ajax
{

    /**
     * Instance of this class
     *
     * @var Property_Ajax
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Property_Ajax
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
        add_action('wp_ajax_inmopress_filter_properties', array($this, 'filter_properties'));
        add_action('wp_ajax_nopriv_inmopress_filter_properties', array($this, 'filter_properties'));
        add_action('wp_ajax_inmopress_get_municipalities', array($this, 'get_municipalities'));
        add_action('wp_ajax_nopriv_inmopress_get_municipalities', array($this, 'get_municipalities'));
    }

    /**
     * Filter properties via AJAX
     */
    public function filter_properties()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'inmopress_filters_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        // Get filters from POST
        $filters = array();
        if (isset($_POST['filters'])) {
            $filters = array_map('sanitize_text_field', $_POST['filters']);
        }

        // Add pagination
        if (isset($_POST['paged'])) {
            $filters['paged'] = absint($_POST['paged']);
        }
        if (isset($_POST['posts_per_page'])) {
            $filters['posts_per_page'] = absint($_POST['posts_per_page']);
        }

        // Build query
        $query_args = Property_Query::build_query_args($filters);
        $query = new \WP_Query($query_args);

        // Get layout
        $layout = isset($_POST['layout']) ? sanitize_text_field($_POST['layout']) : 'grid';

        // Get settings
        $settings = Property_Settings::get_instance();

        // Build response
        ob_start();

        if ($query->have_posts()) {
            echo '<div class="inmopress-properties-container inmopress-properties-' . esc_attr($layout) . '">';
            
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
                    'current' => max(1, $filters['paged'] ?? 1),
                    'format' => '?paged=%#%',
                    'prev_text' => '« Anterior',
                    'next_text' => 'Siguiente »',
                ));
                echo '</div>';
            }
        } else {
            echo '<p class="inmopress-no-properties">No se encontraron propiedades con los filtros seleccionados.</p>';
        }

        wp_reset_postdata();

        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'found' => $query->found_posts,
            'max_pages' => $query->max_num_pages,
        ));
    }

    /**
     * Get municipalities for province
     */
    public function get_municipalities()
    {
        $province_id = isset($_POST['province_id']) ? absint($_POST['province_id']) : 0;

        if (!$province_id) {
            wp_send_json_error(array('message' => 'Province ID required'));
        }

        $municipalities = Property_Filters::get_municipalities($province_id);

        $options = array();
        foreach ($municipalities as $municipality) {
            $options[] = array(
                'value' => $municipality->term_id,
                'label' => $municipality->name,
            );
        }

        wp_send_json_success(array('municipalities' => $options));
    }

    /**
     * Render property card
     *
     * @param int $post_id Post ID
     * @param string $layout Layout type
     * @param Property_Settings $settings Settings instance
     */
    private function render_property_card($post_id, $layout, $settings)
    {
        $template_file = INMOPRESS_THEME_DIR . '/templates/properties/property-card-' . $layout . '.php';
        
        if (file_exists($template_file)) {
            $GLOBALS['inmopress_post_id'] = $post_id;
            $GLOBALS['inmopress_settings'] = $settings;
            include $template_file;
            unset($GLOBALS['inmopress_post_id'], $GLOBALS['inmopress_settings']);
        }
    }
}

