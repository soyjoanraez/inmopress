<?php
/**
 * Archive Template for Properties
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use Inmopress\CRM\Property_Query;
use Inmopress\CRM\Property_Filters;
use Inmopress\CRM\Property_Settings;

get_header();

// Get current filters from URL
$current_filters = Property_Filters::get_filter_values();

// Get order from URL or default
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'precio';
$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
$current_filters['orderby'] = $orderby;
$current_filters['order'] = $order;

// Get layout from URL or default
$layout = isset($_GET['layout']) ? sanitize_text_field($_GET['layout']) : 'grid';
$columns = isset($_GET['columns']) ? absint($_GET['columns']) : 3;

// Build query
$query_args = Property_Query::build_query_args($current_filters);
$query = new WP_Query($query_args);

// Get settings
$settings = Property_Settings::get_instance();
?>

<div class="inmopress-archive-properties">
    <?php include INMOPRESS_THEME_DIR . '/templates/properties/archive-header.php'; ?>

    <div class="archive-content-wrapper">
        <aside class="archive-sidebar">
            <?php include INMOPRESS_THEME_DIR . '/templates/properties/archive-filters.php'; ?>
        </aside>

        <main class="archive-main">
            <div class="archive-controls">
                <div class="archive-results">
                    <span class="results-count">
                        <?php
                        printf(
                            _n('%d propiedad encontrada', '%d propiedades encontradas', $query->found_posts, 'inmopress'),
                            $query->found_posts
                        );
                        ?>
                    </span>
                </div>

                <div class="archive-controls-right">
                    <div class="archive-sort">
                        <label for="archive-orderby">Ordenar por:</label>
                        <select id="archive-orderby" name="orderby" class="archive-select">
                            <option value="precio" <?php selected($orderby, 'precio'); ?>>Precio</option>
                            <option value="fecha" <?php selected($orderby, 'fecha'); ?>>Fecha</option>
                            <option value="superficie" <?php selected($orderby, 'superficie'); ?>>Superficie</option>
                            <option value="titulo" <?php selected($orderby, 'titulo'); ?>>Título</option>
                            <option value="referencia" <?php selected($orderby, 'referencia'); ?>>Referencia</option>
                        </select>
                        <select id="archive-order" name="order" class="archive-select">
                            <option value="ASC" <?php selected($order, 'ASC'); ?>>Ascendente</option>
                            <option value="DESC" <?php selected($order, 'DESC'); ?>>Descendente</option>
                        </select>
                    </div>

                    <div class="archive-view-toggle">
                        <button type="button" class="view-btn view-grid <?php echo $layout === 'grid' ? 'active' : ''; ?>" data-layout="grid" aria-label="Vista grid">
                            <span class="icon-grid">☰</span>
                        </button>
                        <button type="button" class="view-btn view-list <?php echo $layout === 'list' ? 'active' : ''; ?>" data-layout="list" aria-label="Vista lista">
                            <span class="icon-list">☷</span>
                        </button>
                    </div>
                </div>
            </div>

            <?php if ($query->have_posts()) : ?>
                <div class="inmopress-properties-container inmopress-properties-<?php echo esc_attr($layout); ?> inmopress-properties-columns-<?php echo esc_attr($columns); ?>" data-layout="<?php echo esc_attr($layout); ?>" data-posts-per-page="<?php echo esc_attr($query_args['posts_per_page']); ?>">
                    <?php
                    while ($query->have_posts()) {
                        $query->the_post();
                        $GLOBALS['inmopress_post_id'] = get_the_ID();
                        $GLOBALS['inmopress_settings'] = $settings;
                        include INMOPRESS_THEME_DIR . '/templates/properties/property-card-' . $layout . '.php';
                        unset($GLOBALS['inmopress_post_id'], $GLOBALS['inmopress_settings']);
                    }
                    ?>
                </div>

                <?php if ($query->max_num_pages > 1) : ?>
                    <div class="inmopress-properties-pagination">
                        <?php
                        echo paginate_links(array(
                            'total' => $query->max_num_pages,
                            'current' => max(1, get_query_var('paged')),
                            'format' => '?paged=%#%',
                            'prev_text' => '« Anterior',
                            'next_text' => 'Siguiente »',
                        ));
                        ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="inmopress-no-properties">
                    <p>No se encontraron propiedades con los filtros seleccionados.</p>
                    <a href="<?php echo get_post_type_archive_link('impress_property'); ?>" class="button">Ver todas las propiedades</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle orderby/order change
    $('#archive-orderby, #archive-order').on('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('orderby', $('#archive-orderby').val());
        url.searchParams.set('order', $('#archive-order').val());
        window.location.href = url.toString();
    });

    // Handle view toggle
    $('.view-btn').on('click', function() {
        const layout = $(this).data('layout');
        const url = new URL(window.location.href);
        url.searchParams.set('layout', layout);
        window.location.href = url.toString();
    });
});
</script>

<?php
wp_reset_postdata();
get_footer();

