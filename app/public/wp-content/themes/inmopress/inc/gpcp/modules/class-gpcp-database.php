<?php
/**
 * GPCP Database Module
 *
 * Database optimization and cleanup
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Database class
 */
class GPCP_Database
{
    /**
     * Instance of this class
     *
     * @var GPCP_Database
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Database
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
        add_action('wp_ajax_gpcp_cleanup_database', array($this, 'cleanup_database_ajax'));
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_database', 'gpcp_database_auto_cleanup');
    }

    /**
     * Get database stats
     */
    private function get_database_stats()
    {
        global $wpdb;

        $stats = array(
            'total_size' => 0,
            'revisions' => 0,
            'trashed_posts' => 0,
            'spam_comments' => 0,
            'expired_transients' => 0,
            'orphaned_meta' => 0,
        );

        // Total size
        $stats['total_size'] = $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "'");

        // Revisions
        $stats['revisions'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'");

        // Trashed posts
        $stats['trashed_posts'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'");

        // Spam comments
        $stats['spam_comments'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'");

        // Expired transients
        $stats['expired_transients'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE (option_name LIKE '_transient_timeout_%' OR option_name LIKE '_site_transient_timeout_%') AND option_value < UNIX_TIMESTAMP()");

        // Orphaned meta
        $stats['orphaned_meta'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE p.ID IS NULL");

        return $stats;
    }

    /**
     * Cleanup database
     */
    public function cleanup_database($options = array())
    {
        global $wpdb;
        $cleaned = array();

        // Clean revisions
        if (isset($options['revisions']) && $options['revisions']) {
            $deleted = $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'");
            $cleaned['revisions'] = $deleted;
        }

        // Clean trashed posts
        if (isset($options['trashed_posts']) && $options['trashed_posts']) {
            $deleted = $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_status = 'trash'");
            $cleaned['trashed_posts'] = $deleted;
        }

        // Clean spam comments
        if (isset($options['spam_comments']) && $options['spam_comments']) {
            $deleted = $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
            $cleaned['spam_comments'] = $deleted;
        }

        // Clean expired transients
        if (isset($options['expired_transients']) && $options['expired_transients']) {
            $deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE (option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%') AND (option_name LIKE '%_timeout_%' AND option_value < UNIX_TIMESTAMP() OR option_name NOT LIKE '%_timeout_%')");
            $cleaned['expired_transients'] = $deleted;
        }

        // Clean orphaned meta
        if (isset($options['orphaned_meta']) && $options['orphaned_meta']) {
            $deleted = $wpdb->query("DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE p.ID IS NULL");
            $cleaned['orphaned_meta'] = $deleted;
        }

        // Optimize tables
        if (isset($options['optimize_tables']) && $options['optimize_tables']) {
            $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
            foreach ($tables as $table) {
                $wpdb->query("OPTIMIZE TABLE `{$table[0]}`");
            }
            $cleaned['optimize_tables'] = true;
        }

        return $cleaned;
    }

    /**
     * Cleanup database via AJAX
     */
    public function cleanup_database_ajax()
    {
        check_ajax_referer('gpcp_cleanup_database', 'nonce');

        $options = array(
            'revisions' => isset($_POST['clean_revisions']),
            'trashed_posts' => isset($_POST['clean_trashed_posts']),
            'spam_comments' => isset($_POST['clean_spam_comments']),
            'expired_transients' => isset($_POST['clean_expired_transients']),
            'orphaned_meta' => isset($_POST['clean_orphaned_meta']),
            'optimize_tables' => isset($_POST['optimize_tables']),
        );

        $cleaned = $this->cleanup_database($options);

        wp_send_json_success(array(
            'message' => __('Limpieza completada correctamente.', 'gpcp'),
            'cleaned' => $cleaned,
        ));
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_database_save'])) {
            check_admin_referer('gpcp_database_save');
            
            update_option('gpcp_database_auto_cleanup', isset($_POST['gpcp_database_auto_cleanup']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $auto_cleanup = get_option('gpcp_database_auto_cleanup', false);
        $stats = $this->get_database_stats();
        ?>
        <div class="wrap">
            <h1><?php _e('Optimización de Base de Datos', 'gpcp'); ?></h1>
            <p><?php _e('Limpia y optimiza tu base de datos para mejorar el rendimiento.', 'gpcp'); ?></p>
            
            <form method="post" action="" style="margin-bottom: 20px;">
                <?php wp_nonce_field('gpcp_database_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Limpieza Automática', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_database_auto_cleanup" value="1" <?php checked($auto_cleanup); ?> />
                                <?php _e('Ejecutar limpieza automática semanalmente', 'gpcp'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_database_save'); ?>
            </form>

            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle"><?php _e('Análisis de Base de Datos', 'gpcp'); ?></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Tamaño Total', 'gpcp'); ?></th>
                            <td><strong><?php echo size_format($stats['total_size']); ?></strong></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Revisiones de Posts', 'gpcp'); ?></th>
                            <td><?php echo number_format($stats['revisions']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Posts en Papelera', 'gpcp'); ?></th>
                            <td><?php echo number_format($stats['trashed_posts']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Comentarios Spam', 'gpcp'); ?></th>
                            <td><?php echo number_format($stats['spam_comments']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Transients Expirados', 'gpcp'); ?></th>
                            <td><?php echo number_format($stats['expired_transients']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Meta Huérfana', 'gpcp'); ?></th>
                            <td><?php echo number_format($stats['orphaned_meta']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle"><?php _e('Limpieza de Base de Datos', 'gpcp'); ?></h2>
                <div class="inside">
                    <p><?php _e('⚠️ Advertencia: Estas acciones no se pueden deshacer. Se recomienda hacer un backup antes.', 'gpcp'); ?></p>
                    <form id="gpcp-database-cleanup-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Elementos a Limpiar', 'gpcp'); ?></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox" name="clean_revisions" value="1" />
                                            <?php printf(__('Eliminar revisiones (%s entradas)', 'gpcp'), number_format($stats['revisions'])); ?>
                                        </label>
                                        <br>
                                        <label>
                                            <input type="checkbox" name="clean_trashed_posts" value="1" />
                                            <?php printf(__('Eliminar posts en papelera (%s entradas)', 'gpcp'), number_format($stats['trashed_posts'])); ?>
                                        </label>
                                        <br>
                                        <label>
                                            <input type="checkbox" name="clean_spam_comments" value="1" />
                                            <?php printf(__('Eliminar comentarios spam (%s entradas)', 'gpcp'), number_format($stats['spam_comments'])); ?>
                                        </label>
                                        <br>
                                        <label>
                                            <input type="checkbox" name="clean_expired_transients" value="1" />
                                            <?php printf(__('Eliminar transients expirados (%s entradas)', 'gpcp'), number_format($stats['expired_transients'])); ?>
                                        </label>
                                        <br>
                                        <label>
                                            <input type="checkbox" name="clean_orphaned_meta" value="1" />
                                            <?php printf(__('Eliminar meta huérfana (%s entradas)', 'gpcp'), number_format($stats['orphaned_meta'])); ?>
                                        </label>
                                        <br>
                                        <label>
                                            <input type="checkbox" name="optimize_tables" value="1" />
                                            <?php _e('Optimizar tablas de base de datos', 'gpcp'); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                        <p>
                            <button type="submit" class="button button-primary" onclick="return confirm('<?php _e('¿Estás seguro de realizar esta limpieza? Esta acción no se puede deshacer.', 'gpcp'); ?>');">
                                <?php _e('Ejecutar Limpieza', 'gpcp'); ?>
                            </button>
                        </p>
                        <div id="gpcp-database-cleanup-result" style="margin-top: 10px;"></div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#gpcp-database-cleanup-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $result = $('#gpcp-database-cleanup-result');
                var formData = $form.serialize();
                
                $result.html('<div class="notice notice-info"><p><?php _e('Ejecutando limpieza...', 'gpcp'); ?></p></div>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData + '&action=gpcp_cleanup_database&nonce=<?php echo wp_create_nonce('gpcp_cleanup_database'); ?>',
                    success: function(response) {
                        if (response.success) {
                            $result.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $result.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
}



