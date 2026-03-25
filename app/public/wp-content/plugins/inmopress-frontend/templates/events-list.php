<?php if (!defined('ABSPATH')) exit;
// Variables: $query, $status, $type, $priority, $agent_filter, $date_from, $date_to, $search, $paged, $view, $agents
$type_labels = Inmopress_Shortcodes::get_event_type_labels();
$status_labels = Inmopress_Shortcodes::get_event_status_labels();
$priority_labels = Inmopress_Shortcodes::get_event_priority_labels();
$priority_colors = Inmopress_Shortcodes::get_event_priority_colors();
$notice = Inmopress_Shortcodes::get_action_notice();

$base_view_url = Inmopress_Shortcodes::panel_url('events');
$list_url = add_query_arg(array('view' => 'list'), $base_view_url);
$calendar_url = add_query_arg(array('view' => 'calendar'), $base_view_url);
?>

<div class="inmopress-events-list">
    <div class="crm-card" style="margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div>
                <h2 style="margin: 0 0 4px;">Eventos</h2>
                <div style="font-size: 13px; color: #6B7280;">Gestiona tus tareas, visitas y recordatorios</div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <a href="<?php echo esc_url($list_url); ?>" class="btn-crm ghost" style="padding: 6px 12px; font-size: 12px; <?php echo $view === 'list' ? 'border-color:#111827;color:#111827;' : ''; ?>">Lista</a>
                <a href="<?php echo esc_url($calendar_url); ?>" class="btn-crm ghost" style="padding: 6px 12px; font-size: 12px; <?php echo $view === 'calendar' ? 'border-color:#111827;color:#111827;' : ''; ?>">Semana</a>
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('events', array('new' => 1))); ?>" class="btn-crm primary" style="padding: 8px 14px; font-size: 12px;">Nuevo Evento</a>
            </div>
        </div>
    </div>

    <?php if (!empty($notice)): ?>
        <div class="crm-card" style="padding: 12px 16px; margin-bottom: 16px; border-left: 4px solid #10B981;">
            <?php echo esc_html($notice); ?>
        </div>
    <?php endif; ?>

    <?php if ($view === 'calendar'): ?>
        <?php include INMOPRESS_FRONTEND_PATH . 'templates/events-calendar-week.php'; ?>
        <?php return; ?>
    <?php endif; ?>

    <div class="crm-card" style="margin-bottom: 16px;">
        <form method="get" action="">
            <input type="hidden" name="page" value="inmopress-panel">
            <input type="hidden" name="tab" value="events">
            <input type="hidden" name="paged" value="1">
            <input type="hidden" name="view" value="<?php echo esc_attr($view); ?>">
            <div class="crm-form-grid">
                <div class="crm-form-field">
                    <label>Estado:</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <?php foreach ($status_labels as $status_key => $status_label): ?>
                            <option value="<?php echo esc_attr($status_key); ?>" <?php selected($status, $status_key); ?>><?php echo esc_html($status_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="crm-form-field">
                    <label>Tipo:</label>
                    <select name="type">
                        <option value="">Todos</option>
                        <?php foreach ($type_labels as $type_key => $type_label): ?>
                            <option value="<?php echo esc_attr($type_key); ?>" <?php selected($type, $type_key); ?>><?php echo esc_html($type_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="crm-form-field">
                    <label>Prioridad:</label>
                    <select name="priority">
                        <option value="">Todas</option>
                        <?php foreach ($priority_labels as $priority_key => $priority_label): ?>
                            <option value="<?php echo esc_attr($priority_key); ?>" <?php selected($priority, $priority_key); ?>><?php echo esc_html($priority_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($agents)): ?>
                    <div class="crm-form-field">
                        <label>Agente:</label>
                        <select name="agent">
                            <option value="">Todos</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?php echo esc_attr($agent->ID); ?>" <?php selected($agent_filter, $agent->ID); ?>><?php echo esc_html(get_the_title($agent->ID)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="crm-form-field">
                    <label>Desde:</label>
                    <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>">
                </div>
                <div class="crm-form-field">
                    <label>Hasta:</label>
                    <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>">
                </div>
                <div class="crm-form-field">
                    <label>Buscar:</label>
                    <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Título o referencia">
                </div>
            </div>
            <div class="crm-form-actions">
                <button type="submit" class="btn-crm primary" style="padding: 8px 14px; font-size: 12px;">Filtrar</button>
                <a href="<?php echo esc_url($base_view_url); ?>" class="btn-crm ghost" style="padding: 8px 14px; font-size: 12px;">Limpiar</a>
            </div>
        </form>
    </div>

    <?php if ($query->have_posts()): ?>
        <div class="crm-card" style="padding: 0;">
            <table class="crm-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($query->have_posts()):
                    $query->the_post();
                    $event_id = get_the_ID();
                    $start = get_field('impress_event_start');
                    $title = get_field('impress_event_title') ?: get_the_title();
                    $type_value = get_field('impress_event_type');
                    $status_value = get_field('impress_event_status');
                    $priority_value = get_field('impress_event_priority');
                    $priority_color = isset($priority_colors[$priority_value]) ? $priority_colors[$priority_value] : '#9ca3af';
                    $status_color = ($status_value === 'completada') ? '#10B981' : (($status_value === 'cancelada') ? '#EF4444' : '#F59E0B');

                    $complete_url = wp_nonce_url(add_query_arg(array(
                        'event_action' => 'complete',
                        'event_id' => $event_id,
                    )), 'inmopress_event_action_' . $event_id);

                    $start_url = wp_nonce_url(add_query_arg(array(
                        'event_action' => 'start',
                        'event_id' => $event_id,
                    )), 'inmopress_event_action_' . $event_id);

                    $cancel_url = wp_nonce_url(add_query_arg(array(
                        'event_action' => 'cancel',
                        'event_id' => $event_id,
                    )), 'inmopress_event_action_' . $event_id);

                    $is_closed = in_array($status_value, array('completada', 'cancelada'), true);
                    ?>
                    <tr>
                        <td><?php echo $start ? esc_html(date_i18n('d/m/Y H:i', strtotime($start))) : '-'; ?></td>
                        <td><?php echo esc_html($title); ?></td>
                        <td><?php echo esc_html($type_labels[$type_value] ?? $type_value); ?></td>
                        <td><span style="color: <?php echo esc_attr($priority_color); ?>; font-weight: 600;"><?php echo esc_html($priority_labels[$priority_value] ?? $priority_value); ?></span></td>
                        <td><span style="color: <?php echo esc_attr($status_color); ?>; font-weight: 600;"><?php echo esc_html($status_labels[$status_value] ?? $status_value); ?></span></td>
                        <td>
                            <div class="crm-table-actions">
                            <a class="btn-crm ghost" style="padding: 6px 10px; font-size: 12px;" href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('events', array('edit' => $event_id))); ?>">Editar</a>
                            <?php if (!$is_closed): ?>
                                <a class="btn-crm ghost" style="padding: 6px 10px; font-size: 12px;" href="<?php echo esc_url($start_url); ?>">Iniciar</a>
                                <a class="btn-crm ghost" style="padding: 6px 10px; font-size: 12px;" href="<?php echo esc_url($complete_url); ?>">Completar</a>
                                <a class="btn-crm ghost" style="padding: 6px 10px; font-size: 12px; color:#EF4444; border-color:#FECACA;" href="<?php echo esc_url($cancel_url); ?>">Cancelar</a>
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            </table>
        </div>

        <div class="pagination" style="margin-top: 20px;">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',
                'add_args' => array(
                    'page' => 'inmopress-panel',
                    'tab' => 'events',
                    'status' => $status,
                    'type' => $type,
                    'priority' => $priority,
                    'agent' => $agent_filter,
                    'date_from' => $date_from,
                    'date_to' => $date_to,
                    's' => $search,
                    'view' => $view,
                ),
            ));
            ?>
        </div>
    <?php else: ?>
        <div class="crm-card">
            <p style="margin: 0;">No hay eventos registrados.</p>
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
</div>
