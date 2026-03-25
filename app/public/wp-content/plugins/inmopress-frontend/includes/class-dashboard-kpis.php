<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard KPIs - Calcula KPIs reales para el dashboard
 */
class Inmopress_Dashboard_KPIs
{
    /**
     * Obtener KPIs reales
     */
    public static function get_kpis($user_id = null, $agency_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        // Obtener agency_id si no se proporciona
        if (!$agency_id && class_exists('Inmopress_Shortcodes')) {
            $agency_id = Inmopress_Shortcodes::get_agency_id_by_user($user_id);
        }

        // Filtros por agencia si aplica
        $meta_query = array();
        if ($agency_id) {
            $meta_query[] = array(
                'key' => 'impress_property_agency',
                'value' => $agency_id,
                'compare' => '=',
            );
        }

        // Propiedades activas
        $properties_args = array(
            'post_type' => 'impress_property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => $meta_query,
        );
        $properties = get_posts($properties_args);
        $total_properties = count($properties);

        // Clientes activos
        $clients_args = array(
            'post_type' => 'impress_client',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        if ($agency_id) {
            $clients_args['meta_query'] = array(
                array(
                    'key' => 'impress_client_agency',
                    'value' => $agency_id,
                    'compare' => '=',
                ),
            );
        }
        $clients = get_posts($clients_args);
        $total_clients = count($clients);

        // Leads
        $leads_args = array(
            'post_type' => 'impress_lead',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        if ($agency_id) {
            $leads_args['meta_query'] = array(
                array(
                    'key' => 'impress_lead_agency',
                    'value' => $agency_id,
                    'compare' => '=',
                ),
            );
        }
        $leads = get_posts($leads_args);
        $total_leads = count($leads);

        // Eventos/Visitas pendientes
        $events_args = array(
            'post_type' => 'impress_event',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'impress_event_status',
                    'value' => 'pendiente',
                    'compare' => '=',
                ),
            ),
        );
        if ($agency_id) {
            $events_args['meta_query'][] = array(
                'key' => 'impress_event_agency',
                'value' => $agency_id,
                'compare' => '=',
            );
        }
        $events = get_posts($events_args);
        $total_visits = count($events);

        // Calcular comisión total (suma de comisiones de propiedades vendidas/alquiladas)
        $commission_total = 0;
        $sold_properties = get_posts(array(
            'post_type' => 'impress_property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array_merge($meta_query, array(
                array(
                    'key' => 'impress_property_status',
                    'value' => array('vendido', 'alquilado'),
                    'compare' => 'IN',
                ),
            )),
        ));
        foreach ($sold_properties as $prop_id) {
            $commission = get_field('impress_property_commission', $prop_id);
            if ($commission) {
                $commission_total += floatval($commission);
            }
        }

        // Calcular precio promedio
        $prices = array();
        foreach ($properties as $prop_id) {
            $price = get_field('impress_property_price', $prop_id);
            if ($price && is_numeric($price)) {
                $prices[] = floatval($price);
            }
        }
        $avg_price = !empty($prices) ? round(array_sum($prices) / count($prices)) : 0;

        // Oportunidades (matches)
        $opportunities = 0;
        if (class_exists('Inmopress_Matching_Engine')) {
            global $wpdb;
            $table = $wpdb->prefix . 'inmopress_matching_scores';
            $opportunities = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(DISTINCT property_id) FROM {$table} WHERE score >= %d",
                    70
                )
            ) ?: 0;
        }

        // Conversión de leads a clientes (últimos 30 días)
        $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
        $converted_leads = get_posts(array(
            'post_type' => 'impress_lead',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'date_query' => array(
                array(
                    'after' => $thirty_days_ago,
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => 'impress_lead_status',
                    'value' => 'converted',
                    'compare' => '=',
                ),
            ),
        ));
        $conversion_rate = $total_leads > 0 ? round((count($converted_leads) / $total_leads) * 100, 1) : 0;

        // Propiedades nuevas (últimos 7 días)
        $seven_days_ago = date('Y-m-d', strtotime('-7 days'));
        $new_properties = get_posts(array(
            'post_type' => 'impress_property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'date_query' => array(
                array(
                    'after' => $seven_days_ago,
                ),
            ),
            'meta_query' => $meta_query,
        ));
        $new_properties_count = count($new_properties);

        // Clientes nuevos (últimos 7 días)
        $new_clients = get_posts(array(
            'post_type' => 'impress_client',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'date_query' => array(
                array(
                    'after' => $seven_days_ago,
                ),
            ),
        ));
        $new_clients_count = count($new_clients);

        return array(
            'properties' => $total_properties,
            'clients' => $total_clients,
            'leads' => $total_leads,
            'visits' => $total_visits,
            'commission_total' => $commission_total,
            'avg_price' => $avg_price,
            'opportunities' => $opportunities,
            'conversion_rate' => $conversion_rate,
            'new_properties' => $new_properties_count,
            'new_clients' => $new_clients_count,
        );
    }

    /**
     * Obtener datos para gráficas
     */
    public static function get_chart_data($period = '30days', $user_id = null, $agency_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$agency_id && class_exists('Inmopress_Shortcodes')) {
            $agency_id = Inmopress_Shortcodes::get_agency_id_by_user($user_id);
        }

        $days = $period === '7days' ? 7 : ($period === '30days' ? 30 : 90);
        $start_date = date('Y-m-d', strtotime("-{$days} days"));

        // Datos diarios para propiedades
        $properties_data = array();
        $clients_data = array();
        $leads_data = array();

        for ($i = $days; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $date_start = $date . ' 00:00:00';
            $date_end = $date . ' 23:59:59';

            // Propiedades creadas ese día
            $props_args = array(
                'post_type' => 'impress_property',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'date_query' => array(
                    array(
                        'after' => $date_start,
                        'before' => $date_end,
                        'inclusive' => true,
                    ),
                ),
            );
            if ($agency_id) {
                $props_args['meta_query'] = array(
                    array(
                        'key' => 'impress_property_agency',
                        'value' => $agency_id,
                        'compare' => '=',
                    ),
                );
            }
            $properties_data[] = count(get_posts($props_args));

            // Clientes creados ese día
            $clients_args = array(
                'post_type' => 'impress_client',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'date_query' => array(
                    array(
                        'after' => $date_start,
                        'before' => $date_end,
                        'inclusive' => true,
                    ),
                ),
            );
            if ($agency_id) {
                $clients_args['meta_query'] = array(
                    array(
                        'key' => 'impress_client_agency',
                        'value' => $agency_id,
                        'compare' => '=',
                    ),
                );
            }
            $clients_data[] = count(get_posts($clients_args));

            // Leads creados ese día
            $leads_args = array(
                'post_type' => 'impress_lead',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'date_query' => array(
                    array(
                        'after' => $date_start,
                        'before' => $date_end,
                        'inclusive' => true,
                    ),
                ),
            );
            if ($agency_id) {
                $leads_args['meta_query'] = array(
                    array(
                        'key' => 'impress_lead_agency',
                        'value' => $agency_id,
                        'compare' => '=',
                    ),
                );
            }
            $leads_data[] = count(get_posts($leads_args));
        }

        return array(
            'labels' => array_map(function($i) use ($days) {
                return date('d/m', strtotime("-" . ($days - $i) . " days"));
            }, range(0, $days)),
            'properties' => $properties_data,
            'clients' => $clients_data,
            'leads' => $leads_data,
        );
    }

    /**
     * Obtener datos de distribución de operaciones (venta/alquiler)
     * Para la gráfica doughnut
     */
    public static function get_operations_data($user_id = null, $agency_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$agency_id && class_exists('Inmopress_Shortcodes')) {
            $agency_id = Inmopress_Shortcodes::get_agency_id_by_user($user_id);
        }

        // Filtros por agencia si aplica
        $meta_query = array();
        if ($agency_id) {
            $meta_query[] = array(
                'key' => 'impress_property_agency',
                'value' => $agency_id,
                'compare' => '=',
            );
        }

        // Obtener todas las propiedades activas
        $properties_args = array(
            'post_type' => 'impress_property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => $meta_query,
        );
        $properties = get_posts($properties_args);

        // Contar por tipo de operación
        $venta_count = 0;
        $alquiler_count = 0;
        $other_count = 0;

        foreach ($properties as $property_id) {
            // Obtener términos de la taxonomía impress_operation
            $operation_terms = get_the_terms($property_id, 'impress_operation');
            
            if ($operation_terms && !is_wp_error($operation_terms) && !empty($operation_terms)) {
                $operation_slug = $operation_terms[0]->slug;
                $operation_name = strtolower($operation_terms[0]->name);
                
                // Contar venta (incluye variaciones)
                if (strpos($operation_slug, 'venta') !== false || 
                    strpos($operation_name, 'venta') !== false ||
                    $operation_slug === 'venta') {
                    $venta_count++;
                }
                // Contar alquiler (incluye variaciones como alquiler-opcion-compra, vacacional)
                elseif (strpos($operation_slug, 'alquiler') !== false || 
                        strpos($operation_name, 'alquiler') !== false ||
                        $operation_slug === 'alquiler') {
                    $alquiler_count++;
                }
                // Otros (traspaso, etc.)
                else {
                    $other_count++;
                }
            }
        }

        return array(
            'venta' => $venta_count,
            'alquiler' => $alquiler_count,
            'other' => $other_count,
        );
    }
}
