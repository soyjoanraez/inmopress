<?php
/**
 * GPCP Dashboard Widgets Module
 *
 * Custom dashboard widgets
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Dashboard Widgets class
 */
class GPCP_Dashboard_Widgets
{
    /**
     * Instance of this class
     *
     * @var GPCP_Dashboard_Widgets
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Dashboard_Widgets
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
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
        add_action('wp_ajax_gpcp_save_notes', array($this, 'save_notes'));
    }

    /**
     * Add dashboard widgets
     */
    public function add_dashboard_widgets()
    {
        // SEO Summary Widget
        wp_add_dashboard_widget(
            'gpcp_seo_summary',
            __('Resumen SEO', 'gpcp'),
            array($this, 'render_seo_summary_widget')
        );

        // Site Status Widget
        wp_add_dashboard_widget(
            'gpcp_site_status',
            __('Estado del Sitio', 'gpcp'),
            array($this, 'render_site_status_widget')
        );

        // Recent Activity Widget
        wp_add_dashboard_widget(
            'gpcp_recent_activity',
            __('Actividad Reciente', 'gpcp'),
            array($this, 'render_recent_activity_widget')
        );

        // Quick Notes Widget
        wp_add_dashboard_widget(
            'gpcp_quick_notes',
            __('Notas Rápidas', 'gpcp'),
            array($this, 'render_quick_notes_widget')
        );
    }

    /**
     * Render SEO Summary Widget
     */
    public function render_seo_summary_widget()
    {
        $posts = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));

        $scores = array();
        $excellent = 0;
        $good = 0;
        $needs_improvement = 0;

        foreach ($posts as $post) {
            $score = $this->calculate_seo_score($post->ID);
            $scores[] = $score;

            if ($score >= 80) {
                $excellent++;
            } elseif ($score >= 60) {
                $good++;
            } else {
                $needs_improvement++;
            }
        }

        $average_score = !empty($scores) ? round(array_sum($scores) / count($scores)) : 0;
        $total_posts = count($posts);

        ?>
        <div class="gpcp-seo-summary">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 48px; font-weight: bold; color: <?php echo $average_score >= 80 ? '#46b450' : ($average_score >= 60 ? '#ffb900' : '#dc3232'); ?>;">
                    <?php echo $average_score; ?>%
                </div>
                <div style="color: #666;"><?php _e('Puntuación SEO Media', 'gpcp'); ?></div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px;">
                <div style="text-align: center; padding: 10px; background: #f0f0f1; border-radius: 4px;">
                    <div style="font-size: 24px; font-weight: bold; color: #46b450;"><?php echo $excellent; ?></div>
                    <div style="font-size: 12px; color: #666;"><?php _e('Excelentes (80-100%)', 'gpcp'); ?></div>
                </div>
                <div style="text-align: center; padding: 10px; background: #f0f0f1; border-radius: 4px;">
                    <div style="font-size: 24px; font-weight: bold; color: #ffb900;"><?php echo $good; ?></div>
                    <div style="font-size: 12px; color: #666;"><?php _e('Buenos (60-79%)', 'gpcp'); ?></div>
                </div>
                <div style="text-align: center; padding: 10px; background: #f0f0f1; border-radius: 4px;">
                    <div style="font-size: 24px; font-weight: bold; color: #dc3232;"><?php echo $needs_improvement; ?></div>
                    <div style="font-size: 12px; color: #666;"><?php _e('Necesitan Mejora (<60%)', 'gpcp'); ?></div>
                </div>
            </div>

            <p style="text-align: center;">
                <a href="<?php echo admin_url('admin.php?page=gpcp-seo-manager'); ?>" class="button button-primary">
                    <?php _e('Abrir Gestor SEO', 'gpcp'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Calculate SEO score for a post
     */
    private function calculate_seo_score($post_id)
    {
        $score = 0;
        $factors = 0;

        // Title
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

        // Description
        $seo_description = get_post_meta($post_id, '_gpcp_seo_description', true);
        $desc_length = mb_strlen($seo_description);
        if ($desc_length >= 120 && $desc_length <= 160) {
            $score += 25;
        } elseif ($desc_length > 0) {
            $score += 15;
        }
        $factors += 25;

        // Keywords
        $seo_keywords = get_post_meta($post_id, '_gpcp_seo_keywords', true);
        if (!empty($seo_keywords)) {
            $score += 25;
        }
        $factors += 25;

        // Featured image
        if (has_post_thumbnail($post_id)) {
            $score += 25;
        }
        $factors += 25;

        return $factors > 0 ? round(($score / $factors) * 100) : 0;
    }

    /**
     * Render Site Status Widget
     */
    public function render_site_status_widget()
    {
        global $wpdb;

        // WordPress version
        $wp_version = get_bloginfo('version');

        // PHP version
        $php_version = PHP_VERSION;

        // Theme version
        $theme_version = GPCP_VERSION;

        // Uploads size
        $upload_dir = wp_upload_dir();
        $uploads_size = $this->get_directory_size($upload_dir['basedir']);
        $uploads_size_mb = round($uploads_size / 1024 / 1024, 2);

        // Database size
        $db_size = $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "'");
        $db_size_mb = round($db_size / 1024 / 1024, 2);

        // Posts count
        $posts_count = wp_count_posts('post');
        $published_posts = $posts_count->publish;

        // Pages count
        $pages_count = wp_count_posts('page');
        $published_pages = $pages_count->publish;

        ?>
        <div class="gpcp-site-status">
            <table class="widefat">
                <tr>
                    <td><strong><?php _e('WordPress', 'gpcp'); ?></strong></td>
                    <td><?php echo esc_html($wp_version); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('PHP', 'gpcp'); ?></strong></td>
                    <td><?php echo esc_html($php_version); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Tema', 'gpcp'); ?></strong></td>
                    <td>v<?php echo esc_html($theme_version); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Espacio en Uploads', 'gpcp'); ?></strong></td>
                    <td><?php echo esc_html($uploads_size_mb); ?> MB</td>
                </tr>
                <tr>
                    <td><strong><?php _e('Tamaño de Base de Datos', 'gpcp'); ?></strong></td>
                    <td><?php echo esc_html($db_size_mb); ?> MB</td>
                </tr>
                <tr>
                    <td><strong><?php _e('Posts Publicados', 'gpcp'); ?></strong></td>
                    <td><?php echo esc_html($published_posts); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Páginas Publicadas', 'gpcp'); ?></strong></td>
                    <td><?php echo esc_html($published_pages); ?></td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Get directory size
     */
    private function get_directory_size($directory)
    {
        $size = 0;
        if (is_dir($directory)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    /**
     * Render Recent Activity Widget
     */
    public function render_recent_activity_widget()
    {
        $recent_posts = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        $unoptimized = array();
        foreach ($recent_posts as $post) {
            $seo_title = get_post_meta($post->ID, '_gpcp_seo_title', true);
            $seo_description = get_post_meta($post->ID, '_gpcp_seo_description', true);
            if (empty($seo_title) || empty($seo_description)) {
                $unoptimized[] = $post;
            }
        }

        ?>
        <div class="gpcp-recent-activity">
            <h3><?php _e('Últimas 5 Publicaciones', 'gpcp'); ?></h3>
            <ul>
                <?php foreach ($recent_posts as $post): ?>
                    <li>
                        <a href="<?php echo get_edit_post_link($post->ID); ?>">
                            <?php echo esc_html($post->post_title); ?>
                        </a>
                        <span style="color: #666; font-size: 12px;">
                            (<?php echo human_time_diff(strtotime($post->post_date), current_time('timestamp')); ?>)
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if (!empty($unoptimized)): ?>
                <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffb900; border-radius: 4px;">
                    <strong><?php _e('⚠️ Posts sin optimizar SEO:', 'gpcp'); ?></strong>
                    <ul style="margin: 5px 0 0 0;">
                        <?php foreach ($unoptimized as $post): ?>
                            <li>
                                <a href="<?php echo get_edit_post_link($post->ID); ?>">
                                    <?php echo esc_html($post->post_title); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render Quick Notes Widget
     */
    public function render_quick_notes_widget()
    {
        $user_id = get_current_user_id();
        $notes = get_user_meta($user_id, 'gpcp_quick_notes', true);
        if (empty($notes)) {
            $notes = '';
        }

        ?>
        <div class="gpcp-quick-notes">
            <textarea id="gpcp-quick-notes-textarea" rows="8" style="width: 100%;"><?php echo esc_textarea($notes); ?></textarea>
            <p>
                <button type="button" id="gpcp-save-notes" class="button button-primary">
                    <?php _e('Guardar Notas', 'gpcp'); ?>
                </button>
                <span id="gpcp-notes-saved" style="display: none; color: #46b450; margin-left: 10px;">
                    <?php _e('✓ Guardado', 'gpcp'); ?>
                </span>
            </p>
            <p class="description">
                <?php _e('Tus notas se guardan automáticamente y solo tú puedes verlas.', 'gpcp'); ?>
            </p>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#gpcp-save-notes').on('click', function() {
                var notes = $('#gpcp-quick-notes-textarea').val();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gpcp_save_notes',
                        notes: notes,
                        nonce: '<?php echo wp_create_nonce('gpcp_save_notes'); ?>'
                    },
                    success: function(response) {
                        $('#gpcp-notes-saved').fadeIn().delay(2000).fadeOut();
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Save notes via AJAX
     */
    public function save_notes()
    {
        check_ajax_referer('gpcp_save_notes', 'nonce');

        $user_id = get_current_user_id();
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';

        update_user_meta($user_id, 'gpcp_quick_notes', $notes);

        wp_send_json_success();
    }
}

