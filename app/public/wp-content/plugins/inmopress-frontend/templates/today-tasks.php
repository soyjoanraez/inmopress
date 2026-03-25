<?php if (!defined('ABSPATH')) exit;
// Variables: $query, $type_labels, $priority_labels, $priority_colors

$now = current_time('timestamp');
$urgent = array();
$pending = array();
$completed = array();

if (!empty($query->posts)) {
    foreach ($query->posts as $task_post) {
        $status_value = get_field('impress_event_status', $task_post->ID);
        $priority_value = get_field('impress_event_priority', $task_post->ID);
        if ($status_value === 'completada') {
            $completed[] = $task_post;
        } elseif ($priority_value === 'urgente') {
            $urgent[] = $task_post;
        } else {
            $pending[] = $task_post;
        }
    }
}

$render_task = function ($task_post) use ($type_labels, $priority_labels, $priority_colors, $now) {
    $start = get_field('impress_event_start', $task_post->ID);
    $title = get_field('impress_event_title', $task_post->ID) ?: get_the_title($task_post->ID);
    $type_value = get_field('impress_event_type', $task_post->ID);
    $status_value = get_field('impress_event_status', $task_post->ID);
    $priority_value = get_field('impress_event_priority', $task_post->ID);
    $priority_color = isset($priority_colors[$priority_value]) ? $priority_colors[$priority_value] : '#9ca3af';
    $start_ts = $start ? strtotime($start) : null;
    $is_overdue = $start_ts && $start_ts < $now && $status_value !== 'completada';
    $complete_url = wp_nonce_url(add_query_arg(array(
        'event_action' => 'complete',
        'event_id' => $task_post->ID,
    )), 'inmopress_event_action_' . $task_post->ID);
    ?>
    <div class="crm-task-item" style="background: #fff; border-radius: 12px; padding: 12px 14px; border: 1px solid #E5E7EB;">
        <div style="display: flex; justify-content: space-between; align-items: start; gap: 12px;">
            <div>
                <div style="font-size: 12px; color: #6B7280; margin-bottom: 4px;">
                    <?php echo $start ? esc_html(date_i18n('H:i', strtotime($start))) : '-'; ?>
                    · <?php echo esc_html($type_labels[$type_value] ?? $type_value); ?>
                    <?php if ($is_overdue): ?>
                        <span style="margin-left: 6px; color: #ef4444; font-weight: 700;">Vencida</span>
                    <?php endif; ?>
                </div>
                <div style="font-weight: 600; color: #111827;"><?php echo esc_html($title); ?></div>
            </div>
            <span style="font-size: 11px; font-weight: 700; color: <?php echo esc_attr($priority_color); ?>; border: 1px solid <?php echo esc_attr($priority_color); ?>; padding: 2px 8px; border-radius: 999px;">
                <?php echo esc_html($priority_labels[$priority_value] ?? $priority_value); ?>
            </span>
        </div>
        <?php if ($status_value !== 'completada'): ?>
            <div style="display: flex; gap: 8px; margin-top: 10px;">
                <a href="<?php echo esc_url($complete_url); ?>" class="btn-crm ghost" style="padding: 6px 10px; font-size: 12px;">Completar</a>
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('events', array('edit' => $task_post->ID))); ?>" class="btn-crm ghost" style="padding: 6px 10px; font-size: 12px;">Nota</a>
            </div>
        <?php endif; ?>
    </div>
    <?php
};
?>

<div class="crm-card" style="background: transparent; box-shadow: none; border: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Tareas de hoy</h3>
        <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('events')); ?>" style="font-size: 12px; color: #6B7280; text-decoration: none;">Ver todo</a>
    </div>

    <?php if (!empty($urgent) || !empty($pending) || !empty($completed)): ?>
        <div class="crm-tasks-list" style="display: flex; flex-direction: column; gap: 16px;">
            <?php if (!empty($urgent)): ?>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #ef4444; margin-bottom: 8px;">Urgente</div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($urgent as $task_post) { $render_task($task_post); } ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($pending)): ?>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #f59e0b; margin-bottom: 8px;">Pendiente</div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($pending as $task_post) { $render_task($task_post); } ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($completed)): ?>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #10b981; margin-bottom: 8px;">Completadas (<?php echo count($completed); ?>)</div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($completed as $task_post) { $render_task($task_post); } ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="background: #fff; border-radius: 12px; padding: 16px; border: 1px dashed #E5E7EB; color: #9CA3AF;">
            No hay tareas programadas para hoy.
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
</div>
