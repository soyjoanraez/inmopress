<?php if (!defined('ABSPATH')) exit;
// Variables: $query, $status, $date_from, $date_to, $paged, $agent, $client, $property

$status_terms = get_terms(array('taxonomy' => 'impress_visit_status', 'hide_empty' => false));
if (!is_array($status_terms)) {
    $status_terms = array();
}

$current_user = wp_get_current_user();
$is_agent = in_array('agente', $current_user->roles, true);
$agent_id = $is_agent ? Inmopress_Shortcodes::get_agent_id_by_user($current_user->ID) : 0;

$agents = array();
if ($is_agent && $agent_id) {
    $agent_post = get_post($agent_id);
    if ($agent_post) {
        $agents = array($agent_post);
        $agent = $agent_id;
    }
} else {
    $agents = get_posts(array(
        'post_type' => 'impress_agent',
        'posts_per_page' => 200,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
}

$clients = get_posts(array(
    'post_type' => 'impress_client',
    'posts_per_page' => 200,
    'orderby' => 'title',
    'order' => 'ASC',
));

$properties = get_posts(array(
    'post_type' => 'impress_property',
    'posts_per_page' => 200,
    'orderby' => 'title',
    'order' => 'ASC',
));

$filter_args = array();
if (!empty($status)) $filter_args['status'] = $status;
if (!empty($date_from)) $filter_args['date_from'] = $date_from;
if (!empty($date_to)) $filter_args['date_to'] = $date_to;
if (!empty($agent)) $filter_args['agent'] = $agent;
if (!empty($client)) $filter_args['client'] = $client;
if (!empty($property)) $filter_args['property'] = $property;
if (!empty($property_search)) $filter_args['property_search'] = $property_search;

$base_url = Inmopress_Shortcodes::panel_url('visits');
$total_found = isset($query->found_posts) ? (int) $query->found_posts : 0;
?>

<div class="crm-visits">
    <div class="crm-visits-header">
        <div>
            <h1 class="crm-visits-title">Visitas</h1>
            <p class="crm-visits-subtitle"><?php echo number_format_i18n($total_found); ?> visitas encontradas</p>
        </div>
        <div class="crm-visits-actions">
            <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('visits', array('new' => 1))); ?>" class="btn-crm primary">
                <span class="dashicons dashicons-plus"></span> Nueva visita
            </a>
        </div>
    </div>

    <form method="get" action="<?php echo esc_url($base_url); ?>" class="crm-visits-filters">
        <input type="hidden" name="paged" value="1">
        <div class="crm-filter-row">
            <div class="crm-filter-field">
                <label>Estado</label>
                <select name="status">
                    <option value="">Todos</option>
                    <?php foreach ($status_terms as $status_term): ?>
                        <option value="<?php echo esc_attr($status_term->slug); ?>" <?php echo selected($status, $status_term->slug, false); ?>>
                            <?php echo esc_html($status_term->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="crm-filter-field">
                <label>Fecha desde</label>
                <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>">
            </div>
            <div class="crm-filter-field">
                <label>Fecha hasta</label>
                <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>">
            </div>
            <div class="crm-filter-field">
                <label>Agente</label>
                <select name="agent">
                    <option value="">Todos</option>
                    <?php foreach ($agents as $agent_post): ?>
                        <option value="<?php echo esc_attr($agent_post->ID); ?>" <?php echo selected($agent, $agent_post->ID, false); ?>>
                            <?php echo esc_html($agent_post->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="crm-filter-field">
                <label>Inmueble (selección)</label>
                <select name="property">
                    <option value="">Todos</option>
                    <?php foreach ($properties as $property_post): ?>
                        <option value="<?php echo esc_attr($property_post->ID); ?>" <?php echo selected($property, $property_post->ID, false); ?>>
                            <?php echo esc_html($property_post->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="crm-filter-field">
                <label>Inmueble (buscar)</label>
                <input type="text" name="property_search" value="<?php echo esc_attr($property_search); ?>" placeholder="Referencia o título">
            </div>
            <div class="crm-filter-field">
                <label>Cliente</label>
                <select name="client">
                    <option value="">Todos</option>
                    <?php foreach ($clients as $client_post): ?>
                        <option value="<?php echo esc_attr($client_post->ID); ?>" <?php echo selected($client, $client_post->ID, false); ?>>
                            <?php echo esc_html($client_post->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="crm-filter-actions">
            <button type="submit" class="btn-crm">Filtrar</button>
            <a href="<?php echo esc_url($base_url); ?>" class="btn-crm ghost">Limpiar</a>
        </div>
    </form>

    <?php if ($query->have_posts()): ?>
        <div class="crm-visits-grid">
            <?php while ($query->have_posts()): $query->the_post();
                $visit_id = get_the_ID();
                $fecha = get_field('fecha_hora');
                $cliente = get_field('cliente');
                $cliente_id = is_object($cliente) ? $cliente->ID : absint($cliente);
                $cliente_nombre = $cliente_id ? get_the_title($cliente_id) : '-';
                $inmueble = get_field('inmueble');
                $inmueble_id = is_object($inmueble) ? $inmueble->ID : absint($inmueble);
                $inmueble_titulo = $inmueble_id ? get_the_title($inmueble_id) : '-';
                $agente = get_field('agente');
                $agente_id = is_object($agente) ? $agente->ID : absint($agente);
                $agente_nombre = $agente_id ? get_the_title($agente_id) : '-';
                $notas = trim((string) get_field('notas'));
                $notas_resumen = '';
                if ($notas !== '') {
                    $plain = wp_strip_all_tags($notas);
                    $max_len = 320;
                    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                        $notas_resumen = mb_substr($plain, 0, $max_len);
                        if (mb_strlen($plain) > $max_len) {
                            $notas_resumen .= '...';
                        }
                    } else {
                        $notas_resumen = substr($plain, 0, $max_len);
                        if (strlen($plain) > $max_len) {
                            $notas_resumen .= '...';
                        }
                    }
                }
                $terms = get_the_terms($visit_id, 'impress_visit_status');
                $status_term = ($terms && !is_wp_error($terms)) ? $terms[0] : null;
                $status_label = $status_term ? $status_term->name : 'Sin estado';
                $status_slug = $status_term ? $status_term->slug : 'default';

                $action_args = array_merge($filter_args, array(
                    'visit_action' => 'mark_status',
                    'visit_id' => $visit_id,
                ));
                $redirect_to = Inmopress_Shortcodes::panel_url('visits', array('edit' => $visit_id));
                $nonce = wp_create_nonce('visit_status_' . $visit_id);
                ?>
                <div class="crm-visit-card">
                    <div class="crm-visit-header">
                        <div class="crm-visit-date">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php echo $fecha ? esc_html($fecha) : '-'; ?>
                        </div>
                        <span class="crm-visit-status status-<?php echo esc_attr($status_slug); ?>">
                            <?php echo esc_html($status_label); ?>
                        </span>
                    </div>
                    <div class="crm-visit-body">
                        <div class="crm-visit-meta">
                            <div>
                                <span class="crm-visit-label">Cliente</span>
                                <?php if ($cliente_id): ?>
                                    <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('clients', array('edit' => $cliente_id))); ?>">
                                        <?php echo esc_html($cliente_nombre); ?>
                                    </a>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="crm-visit-label">Inmueble</span>
                                <?php if ($inmueble_id): ?>
                                    <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties', array('edit' => $inmueble_id, 'property_id' => $inmueble_id))); ?>">
                                        <?php echo esc_html($inmueble_titulo); ?>
                                    </a>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="crm-visit-label">Agente</span>
                                <span><?php echo esc_html($agente_nombre); ?></span>
                            </div>
                        </div>
                        <div class="crm-visit-notes">
                            <span class="crm-visit-label">Observaciones</span>
                            <?php if ($notas_resumen !== ''): ?>
                                <p><?php echo nl2br(esc_html($notas_resumen)); ?></p>
                            <?php else: ?>
                                <p>Sin observaciones.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="crm-visit-actions">
                        <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('visits', array('edit' => $visit_id))); ?>" class="crm-visit-action">Editar</a>
                        <a href="<?php echo esc_url(add_query_arg(array_merge($action_args, array('visit_status' => 'reagendada', 'redirect_to' => $redirect_to, '_wpnonce' => $nonce)), $base_url)); ?>" class="crm-visit-action reprogram">
                            Reprogramar
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array_merge($action_args, array('visit_status' => 'confirmada', '_wpnonce' => $nonce)), $base_url)); ?>" class="crm-visit-action info">
                            Confirmada
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array_merge($action_args, array('visit_status' => 'realizada', '_wpnonce' => $nonce)), $base_url)); ?>" class="crm-visit-action success">
                            Marcar realizada
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array_merge($action_args, array('visit_status' => 'no-asistio', '_wpnonce' => $nonce)), $base_url)); ?>" class="crm-visit-action neutral">
                            No asistió
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array_merge($action_args, array('visit_status' => 'cancelada', '_wpnonce' => $nonce)), $base_url)); ?>" class="crm-visit-action danger">
                            Cancelar
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="crm-pagination">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',
                'add_args' => $filter_args,
            ));
            ?>
        </div>
    <?php else: ?>
        <div class="crm-empty-state">
            <p>No hay visitas registradas<?php echo !empty($filter_args) ? ' con los filtros seleccionados' : ''; ?>.</p>
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
</div>
