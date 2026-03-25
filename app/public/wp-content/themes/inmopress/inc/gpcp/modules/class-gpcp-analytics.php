<?php
/**
 * GPCP Analytics Module
 *
 * Analytics and statistics
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Analytics class
 */
class GPCP_Analytics
{
    /**
     * Instance of this class
     *
     * @var GPCP_Analytics
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Analytics
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
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp', array($this, 'track_page_view'));
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_analytics', 'gpcp_analytics_enabled');
    }

    /**
     * Track page view
     */
    public function track_page_view()
    {
        if (!get_option('gpcp_analytics_enabled', true) || is_admin()) {
            return;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return;
        }

        $date = date('Y-m-d');
        $transient_key = 'gpcp_analytics_' . $date;
        $stats = get_transient($transient_key);

        if ($stats === false) {
            $stats = array();
        }

        if (!isset($stats[$post_id])) {
            $stats[$post_id] = 0;
        }

        $stats[$post_id]++;
        set_transient($transient_key, $stats, DAY_IN_SECONDS);

        // Update total views for post
        $total_views = get_post_meta($post_id, '_gpcp_total_views', true);
        $total_views = $total_views ? intval($total_views) + 1 : 1;
        update_post_meta($post_id, '_gpcp_total_views', $total_views);
    }

    /**
     * Get analytics data
     */
    private function get_analytics_data($days = 30)
    {
        $data = array(
            'dates' => array(),
            'views' => array(),
            'top_posts' => array(),
        );

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $transient_key = 'gpcp_analytics_' . $date;
            $stats = get_transient($transient_key);

            $total_views = 0;
            if ($stats && is_array($stats)) {
                $total_views = array_sum($stats);
            }

            $data['dates'][] = $date;
            $data['views'][] = $total_views;
        }

        // Get top posts
        $posts = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => 10,
            'meta_key' => '_gpcp_total_views',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
        ));

        foreach ($posts as $post) {
            $views = get_post_meta($post->ID, '_gpcp_total_views', true);
            if ($views) {
                $data['top_posts'][] = array(
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'url' => get_permalink($post->ID),
                    'views' => intval($views),
                );
            }
        }

        return $data;
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_analytics_save'])) {
            check_admin_referer('gpcp_analytics_save');
            
            update_option('gpcp_analytics_enabled', isset($_POST['gpcp_analytics_enabled']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $enabled = get_option('gpcp_analytics_enabled', true);
        $analytics_data = $this->get_analytics_data(30);
        ?>
        <div class="wrap">
            <h1><?php _e('Analytics', 'gpcp'); ?></h1>
            <p><?php _e('Estadísticas de visitas y análisis de tráfico de tu sitio.', 'gpcp'); ?></p>
            
            <form method="post" action="" style="margin-bottom: 20px;">
                <?php wp_nonce_field('gpcp_analytics_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Activar Analytics', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_analytics_enabled" value="1" <?php checked($enabled); ?> />
                                <?php _e('Rastrear visitas y estadísticas', 'gpcp'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_analytics_save'); ?>
            </form>

            <?php if ($enabled): ?>
                <div class="postbox" style="margin-top: 20px;">
                    <h2 class="hndle"><?php _e('Visitas (Últimos 30 días)', 'gpcp'); ?></h2>
                    <div class="inside">
                        <canvas id="gpcp-analytics-chart" style="max-height: 400px;"></canvas>
                    </div>
                </div>

                <div class="postbox" style="margin-top: 20px;">
                    <h2 class="hndle"><?php _e('Posts Más Visitados', 'gpcp'); ?></h2>
                    <div class="inside">
                        <?php if (empty($analytics_data['top_posts'])): ?>
                            <p><?php _e('Aún no hay datos de visitas.', 'gpcp'); ?></p>
                        <?php else: ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th><?php _e('Título', 'gpcp'); ?></th>
                                        <th style="width: 15%;"><?php _e('Visitas', 'gpcp'); ?></th>
                                        <th style="width: 15%;"><?php _e('Acción', 'gpcp'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics_data['top_posts'] as $post): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo esc_html($post['title']); ?></strong>
                                            </td>
                                            <td>
                                                <strong><?php echo number_format($post['views']); ?></strong>
                                            </td>
                                            <td>
                                                <a href="<?php echo esc_url($post['url']); ?>" target="_blank" class="button button-small">
                                                    <?php _e('Ver', 'gpcp'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($enabled): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
        jQuery(document).ready(function($) {
            var ctx = document.getElementById('gpcp-analytics-chart');
            if (ctx) {
                var chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($analytics_data['dates']); ?>,
                        datasets: [{
                            label: '<?php _e('Visitas', 'gpcp'); ?>',
                            data: <?php echo json_encode($analytics_data['views']); ?>,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
        </script>
        <?php endif; ?>
        <?php
    }
}



