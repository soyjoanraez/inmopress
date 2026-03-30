<?php
if (!defined('ABSPATH')) exit;

$current_user_id = get_current_user_id();
$dismissed = get_user_meta($current_user_id, 'inmopress_opportunities_dismissed', true);
if (!is_array($dismissed)) {
    $dismissed = array('properties' => array(), 'clients' => array());
}
$excluded_properties = isset($dismissed['properties']) ? array_map('absint', (array) $dismissed['properties']) : array();
$excluded_clients = isset($dismissed['clients']) ? array_map('absint', (array) $dismissed['clients']) : array();

$featured_property = get_posts(array(
    'post_type' => 'impress_property',
    'posts_per_page' => 1,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'post__not_in' => $excluded_properties,
));
$featured_property = !empty($featured_property) ? $featured_property[0] : null;

$featured_client = get_posts(array(
    'post_type' => 'impress_client',
    'posts_per_page' => 1,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'post__not_in' => $excluded_clients,
));
$featured_client = !empty($featured_client) ? $featured_client[0] : null;

$property_matches = array();
$client_matches = array();
if ($featured_property) {
    $property_matches = Inmopress_Shortcodes::get_opportunity_matches_for_property($featured_property->ID, 5);
}
if ($featured_client) {
    $client_matches = Inmopress_Shortcodes::get_opportunity_matches_for_client($featured_client->ID, 8);
}

$clients_url = Inmopress_Shortcodes::panel_url('clients');
$properties_url = Inmopress_Shortcodes::panel_url('properties');
$action_url = admin_url('admin-post.php');
$action_nonce = wp_create_nonce('inmopress_opportunity_action');

$notice_code = isset($_GET['op_notice']) ? sanitize_key(wp_unslash($_GET['op_notice'])) : '';
$notice_count = isset($_GET['op_count']) ? absint($_GET['op_count']) : 0;
$notice_messages = array(
    'notified' => $notice_count ? sprintf('Se han creado %d notificaciones de match.', $notice_count) : 'Notificaciones creadas.',
    'selection' => $notice_count ? sprintf('Se han creado %d envios de seleccion.', $notice_count) : 'Seleccion enviada.',
    'dismissed' => 'Oportunidad descartada.',
    'no_matches' => 'No se encontraron coincidencias para esta accion.',
    'error' => 'No se pudo completar la accion. Intentalo de nuevo.',
);

$op_tab = isset($_GET['op_tab']) ? sanitize_key(wp_unslash($_GET['op_tab'])) : '';
$search_property_id = isset($_GET['op_property']) ? absint($_GET['op_property']) : 0;
$search_client_id = isset($_GET['op_client']) ? absint($_GET['op_client']) : 0;

$manual_operation = isset($_GET['op_operation']) ? sanitize_key(wp_unslash($_GET['op_operation'])) : '';
$manual_zone = isset($_GET['op_zone']) ? absint($_GET['op_zone']) : 0;
$manual_type = isset($_GET['op_type']) ? sanitize_text_field(wp_unslash($_GET['op_type'])) : '';
$manual_price = isset($_GET['op_price']) ? floatval(str_replace(',', '.', wp_unslash($_GET['op_price']))) : 0;
$manual_bedrooms = isset($_GET['op_bedrooms']) ? absint($_GET['op_bedrooms']) : 0;
$manual_active = ($manual_operation || $manual_zone || $manual_type || $manual_price || $manual_bedrooms);

$search_properties = get_posts(array(
    'post_type' => 'impress_property',
    'posts_per_page' => 50,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
));
$search_clients = get_posts(array(
    'post_type' => 'impress_client',
    'posts_per_page' => 50,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
));
$zone_terms = get_terms(array('taxonomy' => 'impress_city', 'hide_empty' => false));
if (is_wp_error($zone_terms)) {
    $zone_terms = array();
}

$search_property_matches = array();
$search_client_matches = array();
$search_manual_matches = array();

if ($search_property_id) {
    $search_property_matches = Inmopress_Shortcodes::get_opportunity_matches_for_property($search_property_id, 10);
}

if ($search_client_id) {
    $search_client_matches = Inmopress_Shortcodes::get_opportunity_matches_for_client($search_client_id, 10);
}

if ($manual_active) {
    $candidate_clients = get_posts(array(
        'post_type' => 'impress_client',
        'posts_per_page' => 80,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'fields' => 'ids',
        'no_found_rows' => true,
    ));

    foreach ($candidate_clients as $client_id) {
        $score = 0;

        if ($manual_operation) {
            $interest = get_field('interes', $client_id);
            $valid_interest = ($manual_operation === 'venta' && in_array($interest, array('compra', 'inversion'), true))
                || ($manual_operation === 'alquiler' && $interest === 'alquiler');
            if (!$valid_interest) {
                continue;
            }
            $score += 20;
        }

        if ($manual_zone) {
            $zones = get_field('zona_interes', $client_id);
            $zone_ids = is_array($zones) ? array_map('absint', $zones) : array(absint($zones));
            if (!in_array($manual_zone, $zone_ids, true)) {
                continue;
            }
            $score += 30;
        }

        if ($manual_price) {
            $budget_max = (float) get_field('presupuesto_max', $client_id);
            if ($budget_max && $manual_price > $budget_max) {
                continue;
            }
            $score += 25;
        }

        if ($manual_bedrooms) {
            $min_bedrooms = (int) get_field('dormitorios_min', $client_id);
            if ($min_bedrooms && $manual_bedrooms < $min_bedrooms) {
                continue;
            }
            $score += 10;
        }

        if ($manual_type) {
            $notes = (string) get_field('notas_preferencias', $client_id);
            if ($notes === '' || stripos($notes, $manual_type) === false) {
                continue;
            }
            $score += 15;
        }

        if ($score > 0) {
            $search_manual_matches[] = array(
                'client_id' => $client_id,
                'score' => $score,
            );
        }
    }

    usort($search_manual_matches, function ($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    $search_manual_matches = array_slice($search_manual_matches, 0, 10);
}
?>

<div class="crm-opportunities">
    <div class="crm-section-header" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px;">
        <div>
            <h2 style="margin: 0 0 6px; font-size: 20px; font-weight: 700;">Centro de Oportunidades</h2>
            <p style="margin: 0; color: var(--crm-text-secondary); font-size: 13px;">Matches inteligentes entre clientes y propiedades, ordenados por prioridad.</p>
        </div>
        <div class="crm-tabs" data-opportunities-tabs style="display: inline-flex; gap: 8px; background: #FEFCE8; padding: 6px; border-radius: 999px; border: 1px solid var(--crm-border);">
            <button class="crm-tab-button active" type="button" data-tab="new">Nuevas oportunidades</button>
            <button class="crm-tab-button" type="button" data-tab="search">Buscador inverso</button>
            <button class="crm-tab-button" type="button" data-tab="history">Historial</button>
        </div>
    </div>

    <?php if ($notice_code && isset($notice_messages[$notice_code])): ?>
        <div class="crm-card" style="padding: 12px 16px; margin-bottom: 16px; border-left: 4px solid #10B981;">
            <?php echo esc_html($notice_messages[$notice_code]); ?>
        </div>
    <?php endif; ?>

    <div class="crm-opportunities-section" data-tab="new" style="display: block;">
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php if ($featured_property): ?>
                <?php
                $property_id = $featured_property->ID;
                $ref = get_field('referencia', $property_id) ?: '#REF-' . $property_id;
                $price = get_field('precio_venta', $property_id);
                if (!$price) {
                    $price = get_field('precio_alquiler', $property_id);
                }
                $price_label = $price ? number_format($price, 0, ',', '.') . ' EUR' : '-';
                $beds = get_field('dormitorios', $property_id) ?: '-';
                $area = get_field('superficie_construida', $property_id) ?: get_field('superficie_util', $property_id) ?: '-';

                $hot_count = 0;
                $warm_count = 0;
                foreach ($property_matches as $match) {
                    if ($match['score'] >= 80) {
                        $hot_count++;
                    } elseif ($match['score'] >= 60) {
                        $warm_count++;
                    }
                }
                $client_ids = array_map(function ($match) {
                    return (int) $match['client_id'];
                }, $property_matches);
                ?>
                <div class="crm-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <div style="font-weight: 700;">Nueva propiedad detectada</div>
                        <span style="font-size: 12px; color: var(--crm-text-secondary);"><?php echo esc_html(human_time_diff(get_the_time('U', $property_id), current_time('timestamp'))); ?> atras</span>
                    </div>
                    <div class="crm-opportunities-row" style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 16px; align-items: center;">
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <div style="font-weight: 600;"><?php echo esc_html(get_the_title($property_id)); ?></div>
                            <div style="font-size: 12px; color: var(--crm-text-secondary);"><?php echo esc_html($ref); ?> · <?php echo esc_html($price_label); ?> · <?php echo esc_html($beds); ?> hab · <?php echo esc_html($area); ?> m2</div>
                            <div style="font-size: 12px; color: var(--crm-text-secondary);">
                                <?php if (!empty($property_matches)): ?>
                                    <?php echo esc_html(count($property_matches)); ?> clientes coinciden (<?php echo esc_html($hot_count); ?> HOT, <?php echo esc_html($warm_count); ?> WARM)
                                <?php else: ?>
                                    Sin coincidencias detectadas
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end;">
                            <a class="btn-crm ghost" href="<?php echo esc_url($clients_url); ?>">Ver clientes</a>
                            <form method="post" action="<?php echo esc_url($action_url); ?>" style="margin:0;">
                                <input type="hidden" name="action" value="inmopress_opportunity_action">
                                <input type="hidden" name="op_action" value="notify_all">
                                <input type="hidden" name="property_id" value="<?php echo esc_attr($property_id); ?>">
                                <input type="hidden" name="client_ids" value="<?php echo esc_attr(implode(',', $client_ids)); ?>">
                                <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($action_nonce); ?>">
                                <button class="btn-crm primary" type="submit" <?php echo empty($client_ids) ? 'disabled' : ''; ?>>Notificar todos</button>
                            </form>
                            <form method="post" action="<?php echo esc_url($action_url); ?>" style="margin:0;">
                                <input type="hidden" name="action" value="inmopress_opportunity_action">
                                <input type="hidden" name="op_action" value="dismiss">
                                <input type="hidden" name="entity_type" value="property">
                                <input type="hidden" name="entity_id" value="<?php echo esc_attr($property_id); ?>">
                                <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($action_nonce); ?>">
                                <button class="btn-crm ghost" type="submit">Descartar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="crm-card">
                    <div style="font-weight: 700; margin-bottom: 6px;">No hay propiedades recientes</div>
                    <div style="font-size: 12px; color: var(--crm-text-secondary);">Publica una propiedad para generar nuevas oportunidades.</div>
                </div>
            <?php endif; ?>

            <?php if ($featured_client): ?>
                <?php
                $client_id = $featured_client->ID;
                $interest = get_field('interes', $client_id);
                $budget_max = get_field('presupuesto_max', $client_id);
                $budget_label = $budget_max ? number_format($budget_max, 0, ',', '.') . ' EUR' : '-';

                $high_count = 0;
                $medium_count = 0;
                foreach ($client_matches as $match) {
                    if ($match['score'] >= 80) {
                        $high_count++;
                    } elseif ($match['score'] >= 60) {
                        $medium_count++;
                    }
                }
                $property_ids = array_map(function ($match) {
                    return (int) $match['property_id'];
                }, $client_matches);
                ?>
                <div class="crm-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <div style="font-weight: 700;">Nuevo cliente detectado</div>
                        <span style="font-size: 12px; color: var(--crm-text-secondary);"><?php echo esc_html(human_time_diff(get_the_time('U', $client_id), current_time('timestamp'))); ?> atras</span>
                    </div>
                    <div class="crm-opportunities-row" style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 16px; align-items: center;">
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <div style="font-weight: 600;"><?php echo esc_html(get_the_title($client_id)); ?></div>
                            <div style="font-size: 12px; color: var(--crm-text-secondary);">Busca: <?php echo esc_html($interest ?: 'Sin interes'); ?> · hasta <?php echo esc_html($budget_label); ?></div>
                            <div style="font-size: 12px; color: var(--crm-text-secondary);">
                                <?php if (!empty($client_matches)): ?>
                                    <?php echo esc_html(count($client_matches)); ?> propiedades disponibles (<?php echo esc_html($high_count); ?> alto, <?php echo esc_html($medium_count); ?> medio)
                                <?php else: ?>
                                    Sin propiedades compatibles
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end;">
                            <a class="btn-crm ghost" href="<?php echo esc_url($properties_url); ?>">Ver propiedades</a>
                            <form method="post" action="<?php echo esc_url($action_url); ?>" style="margin:0;">
                                <input type="hidden" name="action" value="inmopress_opportunity_action">
                                <input type="hidden" name="op_action" value="send_selection">
                                <input type="hidden" name="client_id" value="<?php echo esc_attr($client_id); ?>">
                                <input type="hidden" name="property_ids" value="<?php echo esc_attr(implode(',', $property_ids)); ?>">
                                <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($action_nonce); ?>">
                                <button class="btn-crm primary" type="submit" <?php echo empty($property_ids) ? 'disabled' : ''; ?>>Enviar seleccion</button>
                            </form>
                            <form method="post" action="<?php echo esc_url($action_url); ?>" style="margin:0;">
                                <input type="hidden" name="action" value="inmopress_opportunity_action">
                                <input type="hidden" name="op_action" value="dismiss">
                                <input type="hidden" name="entity_type" value="client">
                                <input type="hidden" name="entity_id" value="<?php echo esc_attr($client_id); ?>">
                                <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($action_nonce); ?>">
                                <button class="btn-crm ghost" type="submit">Descartar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="crm-card">
                    <div style="font-weight: 700; margin-bottom: 6px;">No hay clientes recientes</div>
                    <div style="font-size: 12px; color: var(--crm-text-secondary);">Crea un cliente para generar nuevas oportunidades.</div>
                </div>
            <?php endif; ?>

            <div class="crm-card" style="border: 1px dashed #FCD34D; background: #FFFBEB;">
                <div style="font-weight: 700; margin-bottom: 6px;">Auto-notificaciones activas</div>
                <div style="font-size: 12px; color: var(--crm-text-secondary);">Se enviaran alertas automaticas cuando el match sea mayor o igual a 70.</div>
            </div>
        </div>
    </div>

    <div class="crm-opportunities-section" data-tab="search" style="display: none;">
        <div class="crm-opportunities-grid" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px;">
            <div class="crm-card">
                <form method="get" action="<?php echo esc_url(Inmopress_Shortcodes::panel_url('opportunities')); ?>" style="display:flex; flex-direction: column; gap: 10px;">
                    <input type="hidden" name="op_tab" value="search">
                    <div style="font-weight: 700;">Tengo una propiedad, quien la quiere</div>
                    <label style="font-size: 12px; color: var(--crm-text-secondary);">Selecciona una propiedad</label>
                    <select name="op_property" style="padding: 10px 12px; border: 1px solid var(--crm-border); border-radius: 10px;">
                        <option value="">Buscar por referencia o titulo</option>
                        <?php foreach ($search_properties as $property): ?>
                            <?php
                            $ref = function_exists('get_field') ? get_field('referencia', $property->ID) : '';
                            $label = $ref ? ($ref . ' · ' . $property->post_title) : $property->post_title;
                            ?>
                            <option value="<?php echo esc_attr($property->ID); ?>" <?php selected($search_property_id, $property->ID); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div style="font-size: 12px; color: var(--crm-text-secondary); margin-top: 6px;">O introduce criterios manualmente</div>
                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px;">
                        <select name="op_operation" style="padding: 10px 12px; border: 1px solid var(--crm-border); border-radius: 10px;">
                            <option value="">Operacion</option>
                            <option value="venta" <?php selected($manual_operation, 'venta'); ?>>Venta</option>
                            <option value="alquiler" <?php selected($manual_operation, 'alquiler'); ?>>Alquiler</option>
                        </select>
                        <select name="op_zone" style="padding: 10px 12px; border: 1px solid var(--crm-border); border-radius: 10px;">
                            <option value="">Zona</option>
                            <?php foreach ($zone_terms as $term): ?>
                                <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($manual_zone, $term->term_id); ?>><?php echo esc_html($term->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="op_type" placeholder="Tipo (texto libre)" value="<?php echo esc_attr($manual_type); ?>" style="padding: 10px 12px; border: 1px solid var(--crm-border); border-radius: 10px;">
                        <input type="number" name="op_price" placeholder="Precio" value="<?php echo esc_attr($manual_price ?: ''); ?>" style="padding: 10px 12px; border: 1px solid var(--crm-border); border-radius: 10px;">
                        <input type="number" name="op_bedrooms" placeholder="Habitaciones" value="<?php echo esc_attr($manual_bedrooms ?: ''); ?>" style="padding: 10px 12px; border: 1px solid var(--crm-border); border-radius: 10px;">
                    </div>

                    <button class="btn-crm primary" type="submit" style="align-self: flex-start;">Buscar clientes compatibles</button>
                </form>
            </div>

            <div class="crm-card">
                <form method="get" action="<?php echo esc_url(Inmopress_Shortcodes::panel_url('opportunities')); ?>" style="display:flex; flex-direction: column; gap: 10px;">
                    <input type="hidden" name="op_tab" value="search">
                    <div style="font-weight: 700;">Tengo un cliente, que le ofrezco</div>
                    <label style="font-size: 12px; color: var(--crm-text-secondary);">Selecciona un cliente</label>
                    <select name="op_client" style="padding: 10px 12px; border: 1px solid var(--crm-border); border-radius: 10px;">
                        <option value="">Buscar cliente</option>
                        <?php foreach ($search_clients as $client): ?>
                            <option value="<?php echo esc_attr($client->ID); ?>" <?php selected($search_client_id, $client->ID); ?>><?php echo esc_html($client->post_title); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button class="btn-crm primary" type="submit" style="align-self: flex-start;">Buscar propiedades compatibles</button>

                    <div style="margin-top: 8px; font-size: 12px; color: var(--crm-text-secondary);">Resultados ordenados por</div>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <label style="font-size: 12px;"><input type="radio" name="crm-order" checked> Mejor match</label>
                        <label style="font-size: 12px;"><input type="radio" name="crm-order"> Recien publicadas</label>
                        <label style="font-size: 12px;"><input type="radio" name="crm-order"> Precio menor</label>
                        <label style="font-size: 12px;"><input type="radio" name="crm-order"> Precio mayor</label>
                    </div>
                </form>
            </div>
        </div>

        <?php
        $client_results = $search_property_id ? $search_property_matches : $search_manual_matches;
        if (!empty($client_results) || $manual_active || $search_property_id): ?>
            <div class="crm-card" style="margin-top: 20px;">
                <div style="font-weight: 700; margin-bottom: 12px;">Resultados de clientes</div>
                <?php if (!empty($client_results)): ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($client_results as $match): ?>
                            <?php
                            $client_id = $match['client_id'];
                            $score = $match['score'];
                            ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border: 1px solid var(--crm-border); border-radius: 10px;">
                                <div>
                                    <div style="font-weight: 600;"><?php echo esc_html(get_the_title($client_id)); ?></div>
                                    <div style="font-size: 12px; color: var(--crm-text-secondary);">Score <?php echo esc_html($score); ?></div>
                                </div>
                                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('clients', array('edit' => $client_id))); ?>" class="btn-crm ghost" style="padding: 6px 12px; font-size: 12px;">Ver cliente</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($search_property_id): ?>
                        <?php
                        $client_ids = array_map(function ($match) {
                            return (int) $match['client_id'];
                        }, $client_results);
                        ?>
                        <form method="post" action="<?php echo esc_url($action_url); ?>" style="margin-top: 12px;">
                            <input type="hidden" name="action" value="inmopress_opportunity_action">
                            <input type="hidden" name="op_action" value="notify_all">
                            <input type="hidden" name="property_id" value="<?php echo esc_attr($search_property_id); ?>">
                            <input type="hidden" name="client_ids" value="<?php echo esc_attr(implode(',', $client_ids)); ?>">
                            <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($action_nonce); ?>">
                            <button class="btn-crm primary" type="submit">Notificar todos</button>
                        </form>
                    <?php else: ?>
                        <div style="margin-top: 12px; font-size: 12px; color: var(--crm-text-secondary);">Selecciona una propiedad para enviar notificaciones.</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="font-size: 12px; color: var(--crm-text-secondary);">No se encontraron clientes con esos criterios.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($search_client_matches) || $search_client_id): ?>
            <div class="crm-card" style="margin-top: 20px;">
                <div style="font-weight: 700; margin-bottom: 12px;">Resultados de propiedades</div>
                <?php if (!empty($search_client_matches)): ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($search_client_matches as $match): ?>
                            <?php
                            $property_id = $match['property_id'];
                            $score = $match['score'];
                            $ref = function_exists('get_field') ? get_field('referencia', $property_id) : '';
                            ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border: 1px solid var(--crm-border); border-radius: 10px;">
                                <div>
                                    <div style="font-weight: 600;"><?php echo esc_html(get_the_title($property_id)); ?></div>
                                    <div style="font-size: 12px; color: var(--crm-text-secondary);"><?php echo esc_html($ref ?: 'Sin referencia'); ?> · Score <?php echo esc_html($score); ?></div>
                                </div>
                                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties', array('edit' => $property_id))); ?>" class="btn-crm ghost" style="padding: 6px 12px; font-size: 12px;">Ver propiedad</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    $property_ids = array_map(function ($match) {
                        return (int) $match['property_id'];
                    }, $search_client_matches);
                    ?>
                    <form method="post" action="<?php echo esc_url($action_url); ?>" style="margin-top: 12px;">
                        <input type="hidden" name="action" value="inmopress_opportunity_action">
                        <input type="hidden" name="op_action" value="send_selection">
                        <input type="hidden" name="client_id" value="<?php echo esc_attr($search_client_id); ?>">
                        <input type="hidden" name="property_ids" value="<?php echo esc_attr(implode(',', $property_ids)); ?>">
                        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($action_nonce); ?>">
                        <button class="btn-crm primary" type="submit">Enviar selección</button>
                    </form>
                <?php else: ?>
                    <div style="font-size: 12px; color: var(--crm-text-secondary);">No se encontraron propiedades compatibles.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="crm-opportunities-section" data-tab="history" style="display: none;">
        <div class="crm-card" style="padding: 0;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--crm-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="font-weight: 700;">Historial de matches</div>
                <a class="btn-crm ghost" href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('events')); ?>">Ver eventos</a>
            </div>
            <div style="padding: 18px 24px; font-size: 13px; color: var(--crm-text-secondary);">
                Las acciones de matching generan eventos en el CRM. Puedes revisarlos y filtrarlos desde la seccion de eventos.
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var root = document.querySelector('[data-opportunities-tabs]');
    if (!root) return;

    var buttons = root.querySelectorAll('[data-tab]');
    var sections = document.querySelectorAll('.crm-opportunities-section');

    function setActive(tab) {
        buttons.forEach(function(btn) {
            btn.classList.toggle('active', btn.getAttribute('data-tab') === tab);
        });
        sections.forEach(function(section) {
            section.style.display = section.getAttribute('data-tab') === tab ? 'block' : 'none';
        });
    }

    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            setActive(btn.getAttribute('data-tab'));
        });
    });

    var params = new URLSearchParams(window.location.search);
    var initialTab = params.get('op_tab');
    if (!initialTab && (params.get('op_property') || params.get('op_client') || params.get('op_operation') || params.get('op_zone') || params.get('op_type') || params.get('op_price') || params.get('op_bedrooms'))) {
        initialTab = 'search';
    }
    if (initialTab) {
        setActive(initialTab);
    }
})();
</script>
