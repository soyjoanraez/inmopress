<?php if (!defined('ABSPATH')) exit;
// Variables from events list template: $status, $type, $priority, $agent_filter, $agents
$base_date = isset($_GET['week']) ? sanitize_text_field(wp_unslash($_GET['week'])) : date('Y-m-d', current_time('timestamp'));
$base_ts = strtotime($base_date);
if (!$base_ts) {
    $base_ts = current_time('timestamp');
}
$week_start = date('Y-m-d', strtotime('monday this week', $base_ts));
$week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));
$prev_week = date('Y-m-d', strtotime($week_start . ' -7 days'));
$next_week = date('Y-m-d', strtotime($week_start . ' +7 days'));
$week_label = sprintf('%s - %s', date_i18n('d M', strtotime($week_start)), date_i18n('d M Y', strtotime($week_end)));
$base_view_url = Inmopress_Shortcodes::panel_url('events');
$calendar_url = add_query_arg(array('view' => 'calendar', 'week' => $week_start), $base_view_url);
?>

<div class="crm-card" style="margin-bottom: 16px;">
    <div style="display:flex; justify-content: space-between; align-items:center; gap: 12px;">
        <div>
            <div style="font-weight:700; font-size:16px;">Semana <?php echo esc_html($week_label); ?></div>
            <div style="font-size:12px; color:#6B7280;">Arrastra eventos para reagendar</div>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <a class="btn-crm ghost" style="padding:6px 10px; font-size:12px;" href="<?php echo esc_url(add_query_arg(array('tab' => 'events', 'view' => 'calendar', 'week' => $prev_week))); ?>">← Anterior</a>
            <a class="btn-crm ghost" style="padding:6px 10px; font-size:12px;" href="<?php echo esc_url(add_query_arg(array('tab' => 'events', 'view' => 'calendar', 'week' => date('Y-m-d', current_time('timestamp'))))); ?>">Hoy</a>
            <a class="btn-crm ghost" style="padding:6px 10px; font-size:12px;" href="<?php echo esc_url(add_query_arg(array('tab' => 'events', 'view' => 'calendar', 'week' => $next_week))); ?>">Siguiente →</a>
        </div>
    </div>
</div>

<div class="crm-card" style="padding: 16px; margin-bottom: 16px;">
    <form method="get" action="">
        <input type="hidden" name="page" value="inmopress-panel">
        <input type="hidden" name="tab" value="events">
        <input type="hidden" name="view" value="calendar">
        <input type="hidden" name="week" value="<?php echo esc_attr($week_start); ?>">
        <div class="crm-form-grid">
            <div class="crm-form-field">
                <label>Estado:</label>
                <select name="status">
                    <option value="">Todos</option>
                    <?php foreach (Inmopress_Shortcodes::get_event_status_labels() as $status_key => $status_label): ?>
                        <option value="<?php echo esc_attr($status_key); ?>" <?php selected($status, $status_key); ?>><?php echo esc_html($status_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="crm-form-field">
                <label>Tipo:</label>
                <select name="type">
                    <option value="">Todos</option>
                    <?php foreach (Inmopress_Shortcodes::get_event_type_labels() as $type_key => $type_label): ?>
                        <option value="<?php echo esc_attr($type_key); ?>" <?php selected($type, $type_key); ?>><?php echo esc_html($type_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="crm-form-field">
                <label>Prioridad:</label>
                <select name="priority">
                    <option value="">Todas</option>
                    <?php foreach (Inmopress_Shortcodes::get_event_priority_labels() as $priority_key => $priority_label): ?>
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
        </div>
        <div class="crm-form-actions">
            <button type="submit" class="btn-crm primary" style="padding: 8px 14px; font-size: 12px;">Filtrar</button>
            <a href="<?php echo esc_url($calendar_url); ?>" class="btn-crm ghost" style="padding: 8px 14px; font-size: 12px;">Limpiar</a>
        </div>
    </form>
</div>

<div class="crm-calendar" data-week-start="<?php echo esc_attr($week_start); ?>" data-hour-start="8" data-hour-end="20">
    <div class="crm-calendar-grid">
        <div class="crm-calendar-times">
            <?php for ($h = 8; $h <= 20; $h++): ?>
                <div class="crm-calendar-time"><?php echo esc_html(sprintf('%02d:00', $h)); ?></div>
            <?php endfor; ?>
        </div>
        <div class="crm-calendar-days">
            <?php for ($i = 0; $i < 7; $i++):
                $day_date = date('Y-m-d', strtotime($week_start . " +{$i} days"));
                $day_label = date_i18n('D d', strtotime($day_date));
                ?>
                <div class="crm-calendar-day" data-date="<?php echo esc_attr($day_date); ?>">
                    <div class="crm-calendar-day-header"><?php echo esc_html($day_label); ?></div>
                    <div class="crm-calendar-day-body"></div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>
