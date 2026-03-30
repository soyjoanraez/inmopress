<?php
if (!defined('ABSPATH')) {
    exit;
}

class Inmopress_Automations
{
    public static function init()
    {
        // Legacy handlers (mantener compatibilidad)
        add_action('acf/save_post', array(__CLASS__, 'handle_lead_save'), 30);
        add_action('acf/save_post', array(__CLASS__, 'handle_event_save'), 35);
        add_action('acf/save_post', array(__CLASS__, 'handle_client_save'), 40);

        add_action('inmopress_daily_automations', array(__CLASS__, 'run_daily_automations'));
        self::schedule_daily();

        // Nuevo sistema de automatizaciones
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_admin_actions'));
        add_action('wp_ajax_inmopress_save_automation', array(__CLASS__, 'ajax_save_automation'));
        add_action('wp_ajax_inmopress_delete_automation', array(__CLASS__, 'ajax_delete_automation'));
        add_action('wp_ajax_inmopress_toggle_automation', array(__CLASS__, 'ajax_toggle_automation'));
        add_action('wp_ajax_inmopress_recalculate_all_matching', array(__CLASS__, 'ajax_recalculate_all_matching'));
        add_action('wp_ajax_inmopress_recalculate_all_matching', array(__CLASS__, 'ajax_recalculate_all_matching'));

        // Crear automatizaciones por defecto al activar
        register_activation_hook(INMOPRESS_CORE_PATH . 'inmopress-core.php', array(__CLASS__, 'create_default_automations'));
    }

    /**
     * Añadir menú admin
     */
    public static function add_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=impress_property',
            'Automatizaciones',
            'Automatizaciones',
            'manage_options',
            'inmopress-automations',
            array(__CLASS__, 'render_automations_page')
        );
    }

    /**
     * Renderizar página de automatizaciones
     */
    public static function render_automations_page()
    {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $automation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($action === 'edit' && $automation_id) {
            self::render_edit_page($automation_id);
        } else {
            self::render_list_page();
        }
    }

    /**
     * Renderizar lista de automatizaciones
     */
    private static function render_list_page()
    {
        $manager = Inmopress_Automation_Manager::get_instance();
        $automations = $manager->get_automations();

        ?>
        <div class="wrap">
            <h1>Automatizaciones</h1>
            <a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-automations&action=edit'); ?>" class="page-title-action">Añadir Nueva</a>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Trigger</th>
                        <th>Estado</th>
                        <th>Ejecuciones</th>
                        <th>Última ejecución</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($automations)): ?>
                        <tr>
                            <td colspan="6">No hay automatizaciones. <a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-automations&action=edit'); ?>">Crear una</a></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($automations as $automation): ?>
                            <tr>
                                <td><strong><?php echo esc_html($automation->name); ?></strong></td>
                                <td><?php echo esc_html($automation->trigger_type); ?></td>
                                <td>
                                    <span class="status-<?php echo $automation->is_active ? 'active' : 'inactive'; ?>">
                                        <?php echo $automation->is_active ? 'Activa' : 'Inactiva'; ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($automation->run_count); ?></td>
                                <td><?php echo $automation->last_run_at ? esc_html($automation->last_run_at) : 'Nunca'; ?></td>
                                <td>
                                    <a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-automations&action=edit&id=' . $automation->id); ?>">Editar</a> |
                                    <a href="#" class="toggle-automation" data-id="<?php echo $automation->id; ?>" data-active="<?php echo $automation->is_active; ?>">
                                        <?php echo $automation->is_active ? 'Desactivar' : 'Activar'; ?>
                                    </a> |
                                    <a href="#" class="delete-automation" data-id="<?php echo $automation->id; ?>">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.toggle-automation').on('click', function(e) {
                e.preventDefault();
                var $link = $(this);
                var id = $link.data('id');
                var active = $link.data('active');

                $.post(ajaxurl, {
                    action: 'inmopress_toggle_automation',
                    automation_id: id,
                    is_active: active ? 0 : 1,
                    nonce: '<?php echo wp_create_nonce('inmopress_automation_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    }
                });
            });

            $('.delete-automation').on('click', function(e) {
                e.preventDefault();
                if (!confirm('¿Eliminar esta automatización?')) {
                    return;
                }

                var $link = $(this);
                var id = $link.data('id');

                $.post(ajaxurl, {
                    action: 'inmopress_delete_automation',
                    automation_id: id,
                    nonce: '<?php echo wp_create_nonce('inmopress_automation_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Renderizar página de edición (simplificada por ahora)
     */
    private static function render_edit_page($automation_id = 0)
    {
        $manager = Inmopress_Automation_Manager::get_instance();
        $automation = $automation_id ? $manager->get_automation($automation_id) : null;

        ?>
        <div class="wrap">
            <h1><?php echo $automation ? 'Editar' : 'Nueva'; ?> Automatización</h1>
            <p><a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-automations'); ?>">← Volver a la lista</a></p>

            <form method="post" action="">
                <?php wp_nonce_field('inmopress_save_automation'); ?>
                <input type="hidden" name="automation_id" value="<?php echo $automation_id; ?>">

                <table class="form-table">
                    <tr>
                        <th><label>Nombre</label></th>
                        <td><input type="text" name="name" value="<?php echo $automation ? esc_attr($automation->name) : ''; ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label>Descripción</label></th>
                        <td><textarea name="description" rows="3" class="large-text"><?php echo $automation ? esc_textarea($automation->description) : ''; ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label>Tipo de Trigger</label></th>
                        <td>
                            <select name="trigger_type" required>
                                <option value="">Seleccionar...</option>
                                <option value="property_created" <?php selected($automation && $automation->trigger_type === 'property_created'); ?>>Propiedad Creada</option>
                                <option value="property_status_changed" <?php selected($automation && $automation->trigger_type === 'property_status_changed'); ?>>Estado Propiedad Cambiado</option>
                                <option value="client_created" <?php selected($automation && $automation->trigger_type === 'client_created'); ?>>Cliente Creado</option>
                                <option value="lead_created" <?php selected($automation && $automation->trigger_type === 'lead_created'); ?>>Lead Creado</option>
                                <option value="event_completed" <?php selected($automation && $automation->trigger_type === 'event_completed'); ?>>Evento Completado</option>
                                <option value="scheduled" <?php selected($automation && $automation->trigger_type === 'scheduled'); ?>>Programado</option>
                                <option value="email_received" <?php selected($automation && $automation->trigger_type === 'email_received'); ?>>Email Recibido</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Estado</label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="is_active" value="1" <?php checked(!$automation || $automation->is_active); ?>>
                                Activa
                            </label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">Guardar</button>
                </p>
            </form>

            <p class="description">
                <strong>Nota:</strong> La configuración completa de condiciones y acciones se implementará en la UI del workflow builder en una versión futura.
                Por ahora, puedes crear automatizaciones básicas que se ejecutarán cuando se dispare el trigger seleccionado.
            </p>
        </div>
        <?php
    }

    /**
     * Manejar acciones admin
     */
    public static function handle_admin_actions()
    {
        if (!isset($_POST['automation_id']) || !check_admin_referer('inmopress_save_automation')) {
            return;
        }

        $manager = Inmopress_Automation_Manager::get_instance();
        $automation_id = intval($_POST['automation_id']);

        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'description' => sanitize_textarea_field($_POST['description']),
            'trigger_type' => sanitize_text_field($_POST['trigger_type']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'conditions' => array(), // Por ahora vacío
            'actions' => array(), // Por ahora vacío
        );

        if ($automation_id) {
            $manager->update_automation($automation_id, $data);
        } else {
            $manager->create_automation($data);
        }

        wp_redirect(admin_url('edit.php?post_type=impress_property&page=inmopress-automations'));
        exit;
    }

    /**
     * AJAX: Guardar automatización
     */
    public static function ajax_save_automation()
    {
        check_ajax_referer('inmopress_automation_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        // Implementación futura
        wp_send_json_success();
    }

    /**
     * AJAX: Eliminar automatización
     */
    public static function ajax_delete_automation()
    {
        check_ajax_referer('inmopress_automation_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $automation_id = intval($_POST['automation_id']);
        $manager = Inmopress_Automation_Manager::get_instance();

        if ($manager->delete_automation($automation_id)) {
            wp_send_json_success();
        } else {
            wp_send_json_error(array('message' => 'Error al eliminar'));
        }
    }

    /**
     * AJAX: Toggle automatización
     */
    public static function ajax_toggle_automation()
    {
        check_ajax_referer('inmopress_automation_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $automation_id = intval($_POST['automation_id']);
        $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 0;

        $manager = Inmopress_Automation_Manager::get_instance();
        if ($manager->update_automation($automation_id, array('is_active' => $is_active))) {
            wp_send_json_success();
        } else {
            wp_send_json_error(array('message' => 'Error al actualizar'));
        }
    }

    /**
     * Añadir menú de Matching
     */
    public static function add_matching_menu()
    {
        add_submenu_page(
            'edit.php?post_type=impress_property',
            'Centro de Oportunidades',
            'Oportunidades',
            'edit_posts',
            'inmopress-matching',
            array(__CLASS__, 'render_matching_page')
        );
    }

    /**
     * Renderizar página de Matching/Oportunidades
     */
    public static function render_matching_page()
    {
        $engine = Inmopress_Matching_Engine::get_instance();
        $property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : 0;
        $client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

        if ($property_id) {
            $matches = $engine->get_property_matches($property_id, array('min_score' => 70, 'limit' => 50));
            $property = get_post($property_id);
            ?>
            <div class="wrap">
                <h1>Oportunidades para: <?php echo esc_html($property->post_title); ?></h1>
                <p><a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-matching'); ?>">← Volver al Centro de Oportunidades</a></p>

                <?php if (empty($matches)): ?>
                    <p>No se encontraron matches para esta propiedad.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Score</th>
                                <th>Desglose</th>
                                <th>Notificado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matches as $match): ?>
                                <?php
                                $client = get_post($match->client_id);
                                $breakdown = json_decode($match->score_breakdown, true);
                                ?>
                                <tr>
                                    <td>
                                        <strong><a href="<?php echo get_edit_post_link($match->client_id); ?>"><?php echo esc_html($client->post_title); ?></a></strong>
                                    </td>
                                    <td>
                                        <span style="font-size: 18px; font-weight: bold; color: <?php echo $match->score >= 80 ? '#46b450' : ($match->score >= 70 ? '#ffb900' : '#dc3232'); ?>;">
                                            <?php echo esc_html($match->score); ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (is_array($breakdown)): ?>
                                            <small>
                                                <?php
                                                $parts = array();
                                                foreach ($breakdown as $key => $value) {
                                                    if ($key !== 'reason' && $key !== 'error') {
                                                        $parts[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
                                                    }
                                                }
                                                echo esc_html(implode(', ', $parts));
                                                ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $match->notified ? '✅ ' . esc_html($match->notified_at) : 'No'; ?></td>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($match->client_id); ?>">Ver Cliente</a> |
                                        <a href="<?php echo get_edit_post_link($match->property_id); ?>">Ver Propiedad</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <?php
        } elseif ($client_id) {
            $matches = $engine->get_client_matches($client_id, array('min_score' => 70, 'limit' => 50));
            $client = get_post($client_id);
            ?>
            <div class="wrap">
                <h1>Propiedades para: <?php echo esc_html($client->post_title); ?></h1>
                <p><a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-matching'); ?>">← Volver al Centro de Oportunidades</a></p>

                <?php if (empty($matches)): ?>
                    <p>No se encontraron propiedades que coincidan con este cliente.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Score</th>
                                <th>Desglose</th>
                                <th>Notificado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matches as $match): ?>
                                <?php
                                $property = get_post($match->property_id);
                                $breakdown = json_decode($match->score_breakdown, true);
                                ?>
                                <tr>
                                    <td>
                                        <strong><a href="<?php echo get_edit_post_link($match->property_id); ?>"><?php echo esc_html($property->post_title); ?></a></strong>
                                    </td>
                                    <td>
                                        <span style="font-size: 18px; font-weight: bold; color: <?php echo $match->score >= 80 ? '#46b450' : ($match->score >= 70 ? '#ffb900' : '#dc3232'); ?>;">
                                            <?php echo esc_html($match->score); ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (is_array($breakdown)): ?>
                                            <small>
                                                <?php
                                                $parts = array();
                                                foreach ($breakdown as $key => $value) {
                                                    if ($key !== 'reason' && $key !== 'error') {
                                                        $parts[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
                                                    }
                                                }
                                                echo esc_html(implode(', ', $parts));
                                                ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $match->notified ? '✅ ' . esc_html($match->notified_at) : 'No'; ?></td>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($match->property_id); ?>">Ver Propiedad</a> |
                                        <a href="<?php echo get_edit_post_link($match->client_id); ?>">Ver Cliente</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <?php
        } else {
            // Vista general del Centro de Oportunidades
            global $wpdb;
            $table = $wpdb->prefix . 'inmopress_matching_scores';
            $top_matches = $wpdb->get_results(
                "SELECT * FROM {$table} 
                WHERE score >= 70 
                ORDER BY score DESC, calculated_at DESC 
                LIMIT 50"
            );
            ?>
            <div class="wrap">
                <h1>Centro de Oportunidades</h1>
                <p>Matches entre propiedades y clientes con score >= 70%</p>

                <div style="margin: 20px 0;">
                    <button type="button" class="button" id="recalculate-all-matching">Recalcular Todos los Matches</button>
                    <span id="recalculate-status" style="margin-left: 10px;"></span>
                </div>

                <?php if (empty($top_matches)): ?>
                    <p>No hay matches disponibles. <a href="#" id="recalculate-all-matching-link">Recalcular matches</a> para generar oportunidades.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Cliente</th>
                                <th>Score</th>
                                <th>Calculado</th>
                                <th>Notificado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_matches as $match): ?>
                                <?php
                                $property = get_post($match->property_id);
                                $client = get_post($match->client_id);
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-matching&property_id=' . $match->property_id); ?>">
                                            <?php echo esc_html($property ? $property->post_title : 'ID: ' . $match->property_id); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-matching&client_id=' . $match->client_id); ?>">
                                            <?php echo esc_html($client ? $client->post_title : 'ID: ' . $match->client_id); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span style="font-size: 16px; font-weight: bold; color: <?php echo $match->score >= 80 ? '#46b450' : '#ffb900'; ?>;">
                                            <?php echo esc_html($match->score); ?>%
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($match->calculated_at); ?></td>
                                    <td><?php echo $match->notified ? '✅ ' . esc_html($match->notified_at) : 'No'; ?></td>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($match->property_id); ?>">Ver Propiedad</a> |
                                        <a href="<?php echo get_edit_post_link($match->client_id); ?>">Ver Cliente</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <script>
            jQuery(document).ready(function($) {
                $('#recalculate-all-matching, #recalculate-all-matching-link').on('click', function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var $status = $('#recalculate-status');
                    
                    $btn.prop('disabled', true);
                    $status.html('Recalculando... <span class="spinner is-active" style="float:none;"></span>');
                    
                    $.post(ajaxurl, {
                        action: 'inmopress_recalculate_all_matching',
                        nonce: '<?php echo wp_create_nonce('inmopress_matching_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            $status.html('✅ Recalculado: ' + response.data.count + ' propiedades procesadas');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $status.html('❌ Error: ' + response.data.message);
                        }
                        $btn.prop('disabled', false);
                    });
                });
            });
            </script>
            <?php
        }
    }

    /**
     * AJAX: Recalcular todos los matches
     */
    public static function ajax_recalculate_all_matching()
    {
        check_ajax_referer('inmopress_matching_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $engine = Inmopress_Matching_Engine::get_instance();
        $count = $engine->recalculate_all_matches();

        wp_send_json_success(array('count' => $count));
    }

    /**
     * Crear automatizaciones por defecto
     */
    public static function create_default_automations()
    {
        $manager = Inmopress_Automation_Manager::get_instance();
        $manager->create_default_automations();
    }

    public static function schedule_daily()
    {
        if (!wp_next_scheduled('inmopress_daily_automations')) {
            wp_schedule_event(time(), 'daily', 'inmopress_daily_automations');
        }
    }

    public static function handle_lead_save($post_id)
    {
        if (get_post_type($post_id) !== 'impress_lead') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (get_post_meta($post_id, '_inmopress_lead_task_created', true)) {
            return;
        }

        $agent_id = get_field('agente_asignado', $post_id);
        if (empty($agent_id)) {
            $current_user = wp_get_current_user();
            if ($current_user && in_array('agente', (array) $current_user->roles, true)) {
                $agent_id = self::get_agent_id_by_user($current_user->ID);
            }
        }

        if (empty($agent_id)) {
            return;
        }

        $lead_name = trim((string) get_field('nombre', $post_id) . ' ' . (string) get_field('apellidos', $post_id));
        if (empty($lead_name)) {
            $lead_name = get_the_title($post_id);
        }

        $title = sprintf('Primera llamada - %s', $lead_name);
        $start = current_time('Y-m-d H:i:s');
        $end = date('Y-m-d H:i:s', strtotime($start . ' +30 minutes'));

        $event_id = wp_insert_post(array(
            'post_type' => 'impress_event',
            'post_status' => 'publish',
            'post_title' => $title,
        ));

        if (!$event_id || is_wp_error($event_id)) {
            return;
        }

        update_field('impress_event_title', $title, $event_id);
        update_field('impress_event_type', 'llamada', $event_id);
        update_field('impress_event_status', 'pendiente', $event_id);
        update_field('impress_event_priority', 'alta', $event_id);
        update_field('impress_event_start', $start, $event_id);
        update_field('impress_event_end', $end, $event_id);
        update_field('impress_event_agent_rel', $agent_id, $event_id);
        update_field('impress_event_lead_rel', $post_id, $event_id);
        update_field('impress_event_auto_created', 1, $event_id);
        update_field('impress_event_automation_rule_id', 1, $event_id);

        update_post_meta($post_id, '_inmopress_lead_task_created', 1);
    }

    public static function handle_event_save($post_id)
    {
        if (get_post_type($post_id) !== 'impress_event') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $status = get_field('impress_event_status', $post_id);
        $type = get_field('impress_event_type', $post_id);

        if ($status === 'completada') {
            $client_id = get_field('impress_event_client_rel', $post_id);
            if ($client_id) {
                self::update_client_last_contact($client_id);
            }
        }

        if ($type !== 'visita' || $status !== 'completada') {
            return;
        }

        if (get_field('impress_event_follow_up_scheduled', $post_id)) {
            return;
        }

        $start = get_field('impress_event_start', $post_id);
        $start_ts = $start ? strtotime($start) : current_time('timestamp');
        $follow_start = date('Y-m-d H:i:s', $start_ts + (2 * DAY_IN_SECONDS));
        $follow_end = date('Y-m-d H:i:s', strtotime($follow_start . ' +30 minutes'));

        $title = get_field('impress_event_title', $post_id);
        if (empty($title)) {
            $title = get_the_title($post_id);
        }
        $follow_title = sprintf('Seguimiento visita - %s', $title);

        $event_id = wp_insert_post(array(
            'post_type' => 'impress_event',
            'post_status' => 'publish',
            'post_title' => $follow_title,
        ));

        if (!$event_id || is_wp_error($event_id)) {
            return;
        }

        update_field('impress_event_title', $follow_title, $event_id);
        update_field('impress_event_type', 'seguimiento', $event_id);
        update_field('impress_event_status', 'pendiente', $event_id);
        update_field('impress_event_priority', 'media', $event_id);
        update_field('impress_event_start', $follow_start, $event_id);
        update_field('impress_event_end', $follow_end, $event_id);
        update_field('impress_event_agent_rel', get_field('impress_event_agent_rel', $post_id), $event_id);
        update_field('impress_event_client_rel', get_field('impress_event_client_rel', $post_id), $event_id);
        update_field('impress_event_lead_rel', get_field('impress_event_lead_rel', $post_id), $event_id);
        update_field('impress_event_property_rel', get_field('impress_event_property_rel', $post_id), $event_id);
        update_field('impress_event_owner_rel', get_field('impress_event_owner_rel', $post_id), $event_id);
        update_field('impress_event_agency_rel', get_field('impress_event_agency_rel', $post_id), $event_id);
        update_field('impress_event_auto_created', 1, $event_id);
        update_field('impress_event_automation_rule_id', 2, $event_id);

        update_field('impress_event_follow_up_scheduled', 1, $post_id);
    }

    public static function handle_client_save($post_id)
    {
        if (get_post_type($post_id) !== 'impress_client') {
            return;
        }

        $last_contact_ts = get_post_meta($post_id, 'impress_client_last_contact_ts', true);
        if (!empty($last_contact_ts)) {
            return;
        }

        $post = get_post($post_id);
        if (!$post) {
            return;
        }

        $created_ts = strtotime($post->post_date_gmt . ' GMT');
        if (!$created_ts) {
            $created_ts = current_time('timestamp');
        }

        update_post_meta($post_id, 'impress_client_last_contact_ts', $created_ts);
        update_field('impress_client_last_contact', date('Y-m-d', $created_ts), $post_id);
    }

    public static function run_daily_automations()
    {
        $cutoff = current_time('timestamp') - (7 * DAY_IN_SECONDS);

        $clients = get_posts(array(
            'post_type' => 'impress_client',
            'posts_per_page' => 200,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'impress_client_last_contact_ts',
                    'value' => $cutoff,
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ),
            ),
        ));

        foreach ($clients as $client_id) {
            $last_contact_ts = (int) get_post_meta($client_id, 'impress_client_last_contact_ts', true);
            if ($last_contact_ts > $cutoff) {
                continue;
            }

            $last_reminder = (int) get_post_meta($client_id, 'impress_client_contact_reminder_ts', true);
            if ($last_reminder && $last_reminder > $cutoff) {
                continue;
            }

            $agent_id = get_field('agente_asignado', $client_id);
            if (empty($agent_id)) {
                continue;
            }

            if (self::client_has_recent_pending_event($client_id, $cutoff)) {
                continue;
            }

            $client_name = trim((string) get_field('nombre', $client_id) . ' ' . (string) get_field('apellidos', $client_id));
            if (empty($client_name)) {
                $client_name = get_the_title($client_id);
            }

            $start_ts = max(current_time('timestamp') + 1800, $cutoff + 3600);
            $start = date('Y-m-d H:i:s', $start_ts);
            $end = date('Y-m-d H:i:s', $start_ts + 1800);

            $title = sprintf('Recordatorio contacto - %s', $client_name);

            $event_id = wp_insert_post(array(
                'post_type' => 'impress_event',
                'post_status' => 'publish',
                'post_title' => $title,
            ));

            if (!$event_id || is_wp_error($event_id)) {
                continue;
            }

            update_field('impress_event_title', $title, $event_id);
            update_field('impress_event_type', 'seguimiento', $event_id);
            update_field('impress_event_status', 'pendiente', $event_id);
            update_field('impress_event_priority', 'media', $event_id);
            update_field('impress_event_start', $start, $event_id);
            update_field('impress_event_end', $end, $event_id);
            update_field('impress_event_agent_rel', $agent_id, $event_id);
            update_field('impress_event_client_rel', $client_id, $event_id);
            update_field('impress_event_auto_created', 1, $event_id);
            update_field('impress_event_automation_rule_id', 3, $event_id);

            update_post_meta($client_id, 'impress_client_contact_reminder_ts', current_time('timestamp'));
        }
    }

    private static function client_has_recent_pending_event($client_id, $cutoff)
    {
        $events = get_posts(array(
            'post_type' => 'impress_event',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'impress_event_client_rel',
                    'value' => $client_id,
                    'compare' => '=',
                ),
                array(
                    'key' => 'impress_event_start',
                    'value' => date('Y-m-d H:i:s', $cutoff),
                    'compare' => '>=',
                    'type' => 'DATETIME',
                ),
                array(
                    'key' => 'impress_event_status',
                    'value' => array('completada', 'cancelada'),
                    'compare' => 'NOT IN',
                ),
            ),
        ));

        return !empty($events);
    }

    private static function update_client_last_contact($client_id)
    {
        $ts = current_time('timestamp');
        update_post_meta($client_id, 'impress_client_last_contact_ts', $ts);
        update_field('impress_client_last_contact', date('Y-m-d', $ts), $client_id);
    }

    private static function get_agent_id_by_user($user_id)
    {
        $agent_ids = get_posts(array(
            'post_type' => 'impress_agent',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'usuario_wordpress',
                    'value' => $user_id,
                    'compare' => '=',
                ),
            ),
        ));

        return !empty($agent_ids) ? $agent_ids[0] : null;
    }
}
