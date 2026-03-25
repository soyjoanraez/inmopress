<?php
/**
 * GPCP SEO Manager
 *
 * Centralized SEO management
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP SEO Manager class
 */
class GPCP_SEO_Manager
{
    /**
     * Instance of this class
     *
     * @var GPCP_SEO_Manager
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_SEO_Manager
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
        add_action('wp_ajax_gpcp_save_seo', array($this, 'save_seo_ajax'));
    }

    /**
     * Save SEO via AJAX
     */
    public function save_seo_ajax()
    {
        check_ajax_referer('gpcp_seo_manager', 'nonce');

        $post_id = intval($_POST['post_id']);
        $seo_title = sanitize_text_field($_POST['seo_title']);
        $seo_description = sanitize_textarea_field($_POST['seo_description']);
        $seo_keywords = sanitize_text_field($_POST['seo_keywords']);

        update_post_meta($post_id, '_gpcp_seo_title', $seo_title);
        update_post_meta($post_id, '_gpcp_seo_description', $seo_description);
        update_post_meta($post_id, '_gpcp_seo_keywords', $seo_keywords);

        wp_send_json_success(array('message' => __('SEO guardado correctamente.', 'gpcp')));
    }

    /**
     * Calculate SEO score
     */
    private function calculate_seo_score($post_id)
    {
        $score = 0;
        $factors = 0;

        $seo_title = get_post_meta($post_id, '_gpcp_seo_title', true);
        if (empty($seo_title)) {
            $seo_title = get_the_title($post_id);
        }
        $title_length = mb_strlen($seo_title);
        if ($title_length >= 30 && $title_length <= 60) {
            $score += 25;
        } elseif ($title_length > 0) {
            $score += 15;
        }
        $factors += 25;

        $seo_description = get_post_meta($post_id, '_gpcp_seo_description', true);
        $desc_length = mb_strlen($seo_description);
        if ($desc_length >= 120 && $desc_length <= 160) {
            $score += 25;
        } elseif ($desc_length > 0) {
            $score += 15;
        }
        $factors += 25;

        $seo_keywords = get_post_meta($post_id, '_gpcp_seo_keywords', true);
        if (!empty($seo_keywords)) {
            $score += 25;
        }
        $factors += 25;

        if (has_post_thumbnail($post_id)) {
            $score += 25;
        }
        $factors += 25;

        return $factors > 0 ? round(($score / $factors) * 100) : 0;
    }

    /**
     * Render page
     */
    public function render_page()
    {
        $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'post';
        $posts_per_page = 20;
        $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'post_status' => 'publish',
        );

        $query = new WP_Query($args);
        ?>
        <div class="wrap">
            <h1><?php _e('Gestor SEO', 'gpcp'); ?></h1>
            <p><?php _e('Gestiona el SEO de todos tus posts desde una tabla centralizada.', 'gpcp'); ?></p>

            <div style="margin: 20px 0;">
                <label>
                    <?php _e('Filtrar por tipo:', 'gpcp'); ?>
                    <select id="gpcp-seo-filter-type" onchange="window.location.href='?page=gpcp-seo-manager&post_type='+this.value">
                        <option value="post" <?php selected($post_type, 'post'); ?>><?php _e('Posts', 'gpcp'); ?></option>
                        <option value="page" <?php selected($post_type, 'page'); ?>><?php _e('Páginas', 'gpcp'); ?></option>
                        <option value="any" <?php selected($post_type, 'any'); ?>><?php _e('Todos', 'gpcp'); ?></option>
                    </select>
                </label>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 5%;"><?php _e('ID', 'gpcp'); ?></th>
                        <th style="width: 20%;"><?php _e('Título', 'gpcp'); ?></th>
                        <th style="width: 20%;"><?php _e('Título SEO', 'gpcp'); ?></th>
                        <th style="width: 25%;"><?php _e('Descripción SEO', 'gpcp'); ?></th>
                        <th style="width: 15%;"><?php _e('Keywords', 'gpcp'); ?></th>
                        <th style="width: 10%;"><?php _e('Puntuación', 'gpcp'); ?></th>
                        <th style="width: 5%;"><?php _e('Acción', 'gpcp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query->have_posts()): ?>
                        <?php while ($query->have_posts()): $query->the_post(); ?>
                            <?php
                            $post_id = get_the_ID();
                            $seo_title = get_post_meta($post_id, '_gpcp_seo_title', true);
                            $seo_description = get_post_meta($post_id, '_gpcp_seo_description', true);
                            $seo_keywords = get_post_meta($post_id, '_gpcp_seo_keywords', true);
                            $score = $this->calculate_seo_score($post_id);
                            $score_color = $score >= 80 ? '#46b450' : ($score >= 60 ? '#ffb900' : '#dc3232');
                            ?>
                            <tr data-post-id="<?php echo $post_id; ?>">
                                <td><?php echo $post_id; ?></td>
                                <td>
                                    <strong><?php echo esc_html(get_the_title()); ?></strong>
                                    <br>
                                    <small><?php echo get_post_type_object(get_post_type())->labels->singular_name; ?></small>
                                </td>
                                <td>
                                    <input type="text" class="gpcp-seo-title regular-text" value="<?php echo esc_attr($seo_title); ?>" 
                                           data-post-id="<?php echo $post_id; ?>" 
                                           placeholder="<?php echo esc_attr(get_the_title()); ?>" />
                                    <small class="gpcp-char-count" data-target="title" style="display: block; color: #666;">
                                        <?php echo mb_strlen($seo_title ?: get_the_title()); ?>/60
                                    </small>
                                </td>
                                <td>
                                    <textarea class="gpcp-seo-description large-text" rows="2" 
                                              data-post-id="<?php echo $post_id; ?>"
                                              placeholder="<?php _e('Descripción SEO...', 'gpcp'); ?>"><?php echo esc_textarea($seo_description); ?></textarea>
                                    <small class="gpcp-char-count" data-target="description" style="display: block; color: #666;">
                                        <?php echo mb_strlen($seo_description); ?>/160
                                    </small>
                                </td>
                                <td>
                                    <input type="text" class="gpcp-seo-keywords regular-text" value="<?php echo esc_attr($seo_keywords); ?>" 
                                           data-post-id="<?php echo $post_id; ?>" 
                                           placeholder="<?php _e('keyword1, keyword2', 'gpcp'); ?>" />
                                </td>
                                <td>
                                    <strong style="color: <?php echo $score_color; ?>; font-size: 18px;">
                                        <?php echo $score; ?>%
                                    </strong>
                                </td>
                                <td>
                                    <button type="button" class="button button-small gpcp-save-seo" data-post-id="<?php echo $post_id; ?>">
                                        <?php _e('Guardar', 'gpcp'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7"><?php _e('No se encontraron posts.', 'gpcp'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($query->max_num_pages > 1): ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $query->max_num_pages,
                            'current' => $paged
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Update character counts
            $('.gpcp-seo-title, .gpcp-seo-description').on('input', function() {
                var $this = $(this);
                var length = $this.val().length;
                var $counter = $this.siblings('.gpcp-char-count[data-target="' + ($this.hasClass('gpcp-seo-title') ? 'title' : 'description') + '"]');
                var max = $this.hasClass('gpcp-seo-title') ? 60 : 160;
                
                $counter.text(length + '/' + max);
                if (length > max) {
                    $counter.css('color', '#dc3232');
                } else if (length >= max * 0.8) {
                    $counter.css('color', '#ffb900');
                } else {
                    $counter.css('color', '#666');
                }
            });

            // Save SEO
            $('.gpcp-save-seo').on('click', function() {
                var $button = $(this);
                var postId = $button.data('post-id');
                var $row = $button.closest('tr');
                
                var data = {
                    action: 'gpcp_save_seo',
                    nonce: '<?php echo wp_create_nonce('gpcp_seo_manager'); ?>',
                    post_id: postId,
                    seo_title: $row.find('.gpcp-seo-title').val(),
                    seo_description: $row.find('.gpcp-seo-description').val(),
                    seo_keywords: $row.find('.gpcp-seo-keywords').val()
                };

                $button.prop('disabled', true).text('<?php _e('Guardando...', 'gpcp'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            $button.text('<?php _e('✓ Guardado', 'gpcp'); ?>').css('color', '#46b450');
                            setTimeout(function() {
                                $button.text('<?php _e('Guardar', 'gpcp'); ?>').css('color', '').prop('disabled', false);
                            }, 2000);
                        }
                    },
                    error: function() {
                        $button.text('<?php _e('Error', 'gpcp'); ?>').css('color', '#dc3232');
                        setTimeout(function() {
                            $button.text('<?php _e('Guardar', 'gpcp'); ?>').css('color', '').prop('disabled', false);
                        }, 2000);
                    }
                });
            });
        });
        </script>
        <?php
    }
}

