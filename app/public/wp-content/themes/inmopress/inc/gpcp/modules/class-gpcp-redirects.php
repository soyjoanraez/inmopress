<?php
/**
 * GPCP Redirects Module
 *
 * 301 redirects management
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Redirects class
 */
class GPCP_Redirects
{
    /**
     * Instance of this class
     *
     * @var GPCP_Redirects
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Redirects
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
        add_action('template_redirect', array($this, 'handle_redirects'), 1);
        add_action('wp_ajax_gpcp_save_redirect', array($this, 'save_redirect_ajax'));
        add_action('wp_ajax_gpcp_delete_redirect', array($this, 'delete_redirect_ajax'));
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_redirects', 'gpcp_redirects_list');
    }

    /**
     * Handle redirects
     */
    public function handle_redirects()
    {
        if (is_admin()) {
            return;
        }

        $redirects = get_option('gpcp_redirects_list', array());
        if (empty($redirects) || !is_array($redirects)) {
            return;
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $request_uri = strtok($request_uri, '?'); // Remove query string

        foreach ($redirects as $redirect) {
            if (isset($redirect['from']) && isset($redirect['to']) && isset($redirect['enabled'])) {
                if (!$redirect['enabled']) {
                    continue;
                }

                $from = trim($redirect['from'], '/');
                $current = trim($request_uri, '/');

                // Exact match
                if ($from === $current) {
                    $this->do_redirect($redirect['to'], 301);
                    return;
                }

                // Pattern match (simple wildcard)
                if (strpos($from, '*') !== false) {
                    $pattern = str_replace('*', '.*', preg_quote($from, '/'));
                    if (preg_match('/^' . $pattern . '$/', $current)) {
                        $to = str_replace('*', $current, $redirect['to']);
                        $this->do_redirect($to, 301);
                        return;
                    }
                }
            }
        }
    }

    /**
     * Do redirect
     */
    private function do_redirect($url, $status = 301)
    {
        // Increment click count
        $this->increment_click_count($url);

        // Make sure URL is absolute
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = home_url($url);
        }

        wp_redirect($url, $status);
        exit;
    }

    /**
     * Increment click count
     */
    private function increment_click_count($url)
    {
        $redirects = get_option('gpcp_redirects_list', array());
        foreach ($redirects as $key => $redirect) {
            if (isset($redirect['to']) && $redirect['to'] === $url) {
                if (!isset($redirects[$key]['clicks'])) {
                    $redirects[$key]['clicks'] = 0;
                }
                $redirects[$key]['clicks']++;
                update_option('gpcp_redirects_list', $redirects);
                break;
            }
        }
    }

    /**
     * Save redirect via AJAX
     */
    public function save_redirect_ajax()
    {
        check_ajax_referer('gpcp_redirects', 'nonce');

        $redirects = get_option('gpcp_redirects_list', array());
        $from = sanitize_text_field($_POST['from']);
        $to = sanitize_text_field($_POST['to']);
        $enabled = isset($_POST['enabled']) ? true : false;
        $id = isset($_POST['id']) ? intval($_POST['id']) : null;

        if (empty($from) || empty($to)) {
            wp_send_json_error(array('message' => __('Los campos "Desde" y "Hacia" son obligatorios.', 'gpcp')));
        }

        $redirect = array(
            'from' => $from,
            'to' => $to,
            'enabled' => $enabled,
            'clicks' => 0,
        );

        if ($id !== null && isset($redirects[$id])) {
            $redirect['clicks'] = isset($redirects[$id]['clicks']) ? $redirects[$id]['clicks'] : 0;
            $redirects[$id] = $redirect;
        } else {
            $redirects[] = $redirect;
        }

        update_option('gpcp_redirects_list', $redirects);
        wp_send_json_success(array('message' => __('Redirección guardada correctamente.', 'gpcp')));
    }

    /**
     * Delete redirect via AJAX
     */
    public function delete_redirect_ajax()
    {
        check_ajax_referer('gpcp_redirects', 'nonce');

        $id = intval($_POST['id']);
        $redirects = get_option('gpcp_redirects_list', array());

        if (isset($redirects[$id])) {
            unset($redirects[$id]);
            $redirects = array_values($redirects); // Reindex
            update_option('gpcp_redirects_list', $redirects);
            wp_send_json_success(array('message' => __('Redirección eliminada correctamente.', 'gpcp')));
        }

        wp_send_json_error(array('message' => __('Redirección no encontrada.', 'gpcp')));
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        $redirects = get_option('gpcp_redirects_list', array());
        ?>
        <div class="wrap">
            <h1><?php _e('Gestor de Redirecciones 301', 'gpcp'); ?></h1>
            <p><?php _e('Gestiona las redirecciones 301 de tu sitio. Las redirecciones se aplican automáticamente.', 'gpcp'); ?></p>

            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle"><?php _e('Añadir Nueva Redirección', 'gpcp'); ?></h2>
                <div class="inside">
                    <form id="gpcp-redirect-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="gpcp-redirect-from"><?php _e('Desde (URL origen)', 'gpcp'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="gpcp-redirect-from" class="regular-text" placeholder="/pagina-antigua" />
                                    <p class="description"><?php _e('URL relativa (ej: /pagina-antigua) o con wildcard (ej: /categoria/*)', 'gpcp'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="gpcp-redirect-to"><?php _e('Hacia (URL destino)', 'gpcp'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="gpcp-redirect-to" class="regular-text" placeholder="/pagina-nueva" />
                                    <p class="description"><?php _e('URL relativa o absoluta (ej: /pagina-nueva o https://example.com)', 'gpcp'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Estado', 'gpcp'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="gpcp-redirect-enabled" checked />
                                        <?php _e('Activar redirección', 'gpcp'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" id="gpcp-redirect-id" value="" />
                        <p>
                            <button type="submit" class="button button-primary"><?php _e('Guardar Redirección', 'gpcp'); ?></button>
                            <button type="button" id="gpcp-redirect-cancel" class="button" style="display: none;"><?php _e('Cancelar', 'gpcp'); ?></button>
                        </p>
                    </form>
                </div>
            </div>

            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle"><?php _e('Redirecciones Existentes', 'gpcp'); ?></h2>
                <div class="inside">
                    <?php if (empty($redirects)): ?>
                        <p><?php _e('No hay redirecciones configuradas.', 'gpcp'); ?></p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 5%;"><?php _e('ID', 'gpcp'); ?></th>
                                    <th style="width: 30%;"><?php _e('Desde', 'gpcp'); ?></th>
                                    <th style="width: 30%;"><?php _e('Hacia', 'gpcp'); ?></th>
                                    <th style="width: 10%;"><?php _e('Clics', 'gpcp'); ?></th>
                                    <th style="width: 10%;"><?php _e('Estado', 'gpcp'); ?></th>
                                    <th style="width: 15%;"><?php _e('Acciones', 'gpcp'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($redirects as $id => $redirect): ?>
                                    <tr data-redirect-id="<?php echo $id; ?>">
                                        <td><?php echo $id; ?></td>
                                        <td><code><?php echo esc_html($redirect['from']); ?></code></td>
                                        <td><code><?php echo esc_html($redirect['to']); ?></code></td>
                                        <td><?php echo isset($redirect['clicks']) ? intval($redirect['clicks']) : 0; ?></td>
                                        <td>
                                            <?php if (isset($redirect['enabled']) && $redirect['enabled']): ?>
                                                <span style="color: #46b450;"><?php _e('Activa', 'gpcp'); ?></span>
                                            <?php else: ?>
                                                <span style="color: #dc3232;"><?php _e('Inactiva', 'gpcp'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="button button-small gpcp-edit-redirect" data-id="<?php echo $id; ?>">
                                                <?php _e('Editar', 'gpcp'); ?>
                                            </button>
                                            <button type="button" class="button button-small gpcp-delete-redirect" data-id="<?php echo $id; ?>">
                                                <?php _e('Eliminar', 'gpcp'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var editingId = null;

            // Save redirect
            $('#gpcp-redirect-form').on('submit', function(e) {
                e.preventDefault();
                
                var data = {
                    action: 'gpcp_save_redirect',
                    nonce: '<?php echo wp_create_nonce('gpcp_redirects'); ?>',
                    from: $('#gpcp-redirect-from').val(),
                    to: $('#gpcp-redirect-to').val(),
                    enabled: $('#gpcp-redirect-enabled').is(':checked') ? 1 : 0,
                    id: editingId
                };

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message);
                        }
                    }
                });
            });

            // Edit redirect
            $('.gpcp-edit-redirect').on('click', function() {
                var id = $(this).data('id');
                var $row = $('tr[data-redirect-id="' + id + '"]');
                
                $('#gpcp-redirect-from').val($row.find('td:eq(1) code').text());
                $('#gpcp-redirect-to').val($row.find('td:eq(2) code').text());
                $('#gpcp-redirect-enabled').prop('checked', $row.find('td:eq(4)').text().trim() === '<?php _e('Activa', 'gpcp'); ?>');
                $('#gpcp-redirect-id').val(id);
                editingId = id;
                $('#gpcp-redirect-cancel').show();
                
                $('html, body').animate({ scrollTop: 0 }, 500);
            });

            // Cancel edit
            $('#gpcp-redirect-cancel').on('click', function() {
                $('#gpcp-redirect-form')[0].reset();
                $('#gpcp-redirect-id').val('');
                editingId = null;
                $(this).hide();
            });

            // Delete redirect
            $('.gpcp-delete-redirect').on('click', function() {
                if (!confirm('<?php _e('¿Estás seguro de eliminar esta redirección?', 'gpcp'); ?>')) {
                    return;
                }

                var id = $(this).data('id');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gpcp_delete_redirect',
                        nonce: '<?php echo wp_create_nonce('gpcp_redirects'); ?>',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
}



