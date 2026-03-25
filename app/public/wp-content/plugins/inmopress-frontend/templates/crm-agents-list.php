<?php if (!defined('ABSPATH'))
    exit;
// Available variables: $query (WP_Query object)
$search = isset($search) ? $search : (isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '');
$agency_filter = isset($_GET['agency']) ? absint($_GET['agency']) : 0;
$active_filter = isset($_GET['active']) ? sanitize_key($_GET['active']) : '';
?>

<div class="crm-agents-wrapper">

    <!-- Header con acciones -->
    <div class="crm-list-header">
        <div class="header-left">
            <h1><?php esc_html_e('Agentes', 'inmopress'); ?></h1>
            <p class="crm-list-subtitle"><?php esc_html_e('Gestiona tu equipo de agentes inmobiliarios', 'inmopress'); ?></p>
        </div>
        <div class="header-actions">
            <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('agents', array('new' => 1))); ?>" class="btn-crm primary">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php esc_html_e('Nuevo Agente', 'inmopress'); ?>
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <form class="crm-filters-bar" method="get" action="<?php echo esc_url(Inmopress_Shortcodes::panel_url('agents')); ?>">

        <div class="crm-filter-search">
            <span class="dashicons dashicons-search"></span>
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Buscar por nombre, email o teléfono...">
        </div>

        <div class="crm-filter-actions">
            <?php
            // Filtro por agencia
            $agencies = get_posts(array(
                'post_type' => 'impress_agency',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
            ));
            ?>
            <select class="crm-select" name="agency">
                <option value="">Todas las agencias</option>
                <?php if (!empty($agencies)): ?>
                    <?php foreach ($agencies as $agency): ?>
                        <option value="<?php echo esc_attr($agency->ID); ?>" <?php selected($agency_filter, $agency->ID); ?>>
                            <?php echo esc_html($agency->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <select class="crm-select" name="active">
                <option value="">Todos los estados</option>
                <option value="1" <?php selected($active_filter, '1'); ?>><?php esc_html_e('Activos', 'inmopress'); ?></option>
                <option value="0" <?php selected($active_filter, '0'); ?>><?php esc_html_e('Inactivos', 'inmopress'); ?></option>
            </select>

            <button type="submit" class="btn-crm ghost small"><?php esc_html_e('Filtrar', 'inmopress'); ?></button>
            <a class="btn-crm ghost small" href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('agents')); ?>">
                <?php esc_html_e('Limpiar filtros', 'inmopress'); ?>
            </a>
        </div>
    </form>

    <div class="crm-agents-layout">
        <!-- Tabla de agentes -->
        <div class="crm-table-container crm-card">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Avatar', 'inmopress'); ?></th>
                        <th><?php esc_html_e('Agente', 'inmopress'); ?></th>
                        <th class="table-hide-mobile"><?php esc_html_e('Contacto', 'inmopress'); ?></th>
                        <th class="table-hide-mobile"><?php esc_html_e('Agencia', 'inmopress'); ?></th>
                        <th><?php esc_html_e('Especialización', 'inmopress'); ?></th>
                        <th><?php esc_html_e('Estado', 'inmopress'); ?></th>
                        <th><?php esc_html_e('Acciones', 'inmopress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query->have_posts()): ?>
                        <?php while ($query->have_posts()):
                            $query->the_post();
                            $id = get_the_ID();
                            $nombre = get_field('nombre', $id) ?: '';
                            $apellidos = get_field('apellidos', $id) ?: '';
                            $full_name = trim($nombre . ' ' . $apellidos) ?: get_the_title();
                            $email = get_field('email', $id) ?: '';
                            $telefono = get_field('telefono', $id) ?: '';
                            $usuario_wp = get_field('usuario_wordpress', $id);
                            $agencia = get_field('agencia_relacionada', $id);
                            $activo = get_field('activo', $id);
                            $avatar = get_field('avatar', $id);
                            
                            // Obtener avatar
                            $avatar_url = '';
                            if ($avatar) {
                                if (is_array($avatar)) {
                                    $avatar_url = isset($avatar['sizes']['thumbnail']) ? $avatar['sizes']['thumbnail'] : (isset($avatar['url']) ? $avatar['url'] : '');
                                } else {
                                    $avatar_url = wp_get_attachment_image_url($avatar, 'thumbnail');
                                }
                            }
                            if (!$avatar_url) {
                                $avatar_url = 'https://via.placeholder.com/60?text=' . urlencode(substr($full_name, 0, 1));
                            }

                            // Obtener especialidades
                            $specialties = wp_get_post_terms($id, 'impress_agent_specialty', array('fields' => 'names'));
                            $specialties_text = !empty($specialties) ? implode(', ', $specialties) : '-';

                            // Obtener nombre de agencia
                            $agencia_name = '';
                            if ($agencia) {
                                if (is_object($agencia) && isset($agencia->post_title)) {
                                    $agencia_name = $agencia->post_title;
                                } elseif (is_numeric($agencia)) {
                                    $agencia_post = get_post($agencia);
                                    $agencia_name = $agencia_post ? $agencia_post->post_title : '';
                                }
                            }

                            // Estado del agente
                            $is_active = ($activo === true || $activo === '1' || $activo === 1);
                            $status_badge_class = $is_active ? 'badge-success' : 'badge-grey';
                            $status_text = $is_active ? __('Activo', 'inmopress') : __('Inactivo', 'inmopress');
                            ?>
                            <tr>
                                <!-- Avatar -->
                                <td class="crm-agent-avatar-cell">
                                    <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($full_name); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                </td>

                                <!-- Nombre -->
                                <td class="crm-agent-details">
                                    <?php $edit_link = Inmopress_Shortcodes::panel_url('agents', array('edit' => $id, 'agent_id' => $id)); ?>
                                    <a href="<?php echo esc_url($edit_link); ?>">
                                        <div class="crm-agent-name">
                                            <?php echo esc_html($full_name); ?>
                                        </div>
                                    </a>
                                    <?php if ($usuario_wp): ?>
                                        <?php
                                        $user_data = is_array($usuario_wp) ? $usuario_wp : get_userdata($usuario_wp);
                                        $username = is_array($usuario_wp) ? (isset($usuario_wp['user_login']) ? $usuario_wp['user_login'] : '') : ($user_data ? $user_data->user_login : '');
                                        ?>
                                        <div class="crm-agent-meta">
                                            <span class="badge badge-grey small"><?php echo esc_html($username); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Contacto -->
                                <td class="crm-agent-contact table-hide-mobile">
                                    <?php if ($email): ?>
                                        <div class="crm-agent-contact-item">
                                            <span class="dashicons dashicons-email"></span>
                                            <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($telefono): ?>
                                        <div class="crm-agent-contact-item">
                                            <span class="dashicons dashicons-phone"></span>
                                            <a href="tel:<?php echo esc_attr($telefono); ?>"><?php echo esc_html($telefono); ?></a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!$email && !$telefono): ?>
                                        <span class="crm-text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Agencia -->
                                <td class="crm-agent-agency table-hide-mobile">
                                    <?php if ($agencia_name): ?>
                                        <?php
                                        $agencia_id = is_object($agencia) ? $agencia->ID : (is_numeric($agencia) ? $agencia : 0);
                                        $agencia_link = $agencia_id ? Inmopress_Shortcodes::panel_url('agencies', array('edit' => $agencia_id)) : '';
                                        
                                        // Contar agentes de esta agencia
                                        $agents_count = 0;
                                        if ($agencia_id) {
                                            $agents_query = new WP_Query(array(
                                                'post_type' => 'impress_agent',
                                                'posts_per_page' => -1,
                                                'meta_query' => array(
                                                    array(
                                                        'key' => 'agencia_relacionada',
                                                        'value' => $agencia_id,
                                                        'compare' => '=',
                                                    ),
                                                ),
                                                'fields' => 'ids',
                                            ));
                                            $agents_count = $agents_query->found_posts;
                                        }
                                        ?>
                                        <?php if ($agencia_link): ?>
                                            <a href="<?php echo esc_url($agencia_link); ?>" title="<?php echo esc_attr(sprintf(__('%d agente(s) en esta agencia', 'inmopress'), $agents_count)); ?>">
                                                <?php echo esc_html($agencia_name); ?>
                                                <?php if ($agents_count > 0): ?>
                                                    <span class="badge badge-grey small" style="margin-left: 8px;"><?php echo esc_html($agents_count); ?></span>
                                                <?php endif; ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo esc_html($agencia_name); ?>
                                            <?php if ($agents_count > 0): ?>
                                                <span class="badge badge-grey small" style="margin-left: 8px;"><?php echo esc_html($agents_count); ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="crm-text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Especialización -->
                                <td class="crm-agent-specialty">
                                    <?php if ($specialties_text !== '-'): ?>
                                        <span class="badge badge-grey small"><?php echo esc_html($specialties_text); ?></span>
                                    <?php else: ?>
                                        <span class="crm-text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Estado -->
                                <td class="crm-agent-status">
                                    <span class="badge <?php echo esc_attr($status_badge_class); ?> small">
                                        <?php echo esc_html($status_text); ?>
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="crm-agent-actions">
                                    <div class="crm-agent-actions-row">
                                        <a href="<?php echo esc_url($edit_link); ?>" class="btn-icon" title="<?php esc_attr_e('Editar', 'inmopress'); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                        <?php
                                        // Link a propiedades del agente
                                        $properties_link = Inmopress_Shortcodes::panel_url('properties', array('agent' => $id));
                                        ?>
                                        <a href="<?php echo esc_url($properties_link); ?>" class="btn-icon" title="<?php esc_attr_e('Ver propiedades', 'inmopress'); ?>">
                                            <span class="dashicons dashicons-admin-home"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="table-empty">
                                <?php esc_html_e('No hay agentes registrados.', 'inmopress'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($query->max_num_pages > 1): ?>
        <div class="crm-pagination">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => max(1, get_query_var('paged')),
                'format' => '?paged=%#%',
                'prev_text' => '<span class="dashicons dashicons-arrow-left-alt2"></span>',
                'next_text' => '<span class="dashicons dashicons-arrow-right-alt2"></span>',
            ));
            ?>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>
