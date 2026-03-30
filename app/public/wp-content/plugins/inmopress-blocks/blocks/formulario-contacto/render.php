<?php
/**
 * Formulario Contacto Block Template.
 */

$id = 'inmopress-contact-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'inmopress-contact-form';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Config
$fields = get_field('campos_formulario') ?: ['nombre', 'telefono', 'email', 'mensaje'];
$type = get_field('tipo_contacto') ?: 'info'; // visita, info, tasacion
$auto_lead = get_field('crear_lead_auto');

// Agent Info (if on single property)
$agent_info = false;
$agent_name = '';
$agent_avatar = '';

if (is_singular('impress_property')) {
    $agent_id = get_field('agente'); // Post Object
    if ($agent_id) {
        $agent_info = true;
        // Since get_field returns ID or Object depending on settings, handle both
        $agent_post = is_object($agent_id) ? $agent_id : get_post($agent_id);

        $agent_name = get_field('nombre', $agent_post->ID) . ' ' . get_field('apellidos', $agent_post->ID);
        $avatar_img = get_field('avatar', $agent_post->ID);
        $agent_avatar = $avatar_img ? $avatar_img['sizes']['thumbnail'] : '';
    }
}
?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <?php if ($agent_info): ?>
        <div class="inmopress-agent-info">
            <div class="inmopress-agent-avatar">
                <?php if ($agent_avatar): ?>
                    <img src="<?php echo esc_url($agent_avatar); ?>" alt="<?php echo esc_attr($agent_name); ?>">
                <?php else: ?>
                    <span class="dashicons dashicons-businessman"
                        style="font-size: 40px; width: 40px; height: 40px; color: #ccc; margin: 10px;"></span>
                <?php endif; ?>
            </div>
            <div class="inmopress-agent-details">
                <span class="inmopress-agent-name">
                    <?php echo esc_html($agent_name); ?>
                </span>
                <span class="inmopress-agent-role">
                    <?php _e('Agente Asignado', 'inmopress'); ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <div class="inmopress-form-title">
        <?php
        if ($type === 'visita')
            _e('Solicitar Visita', 'inmopress');
        elseif ($type === 'tasacion')
            _e('Solicitar Tasación', 'inmopress');
        else
            _e('Contactar', 'inmopress');
        ?>
    </div>

    <form class="inmopress-lead-form" method="post">

        <?php if (in_array('nombre', $fields)): ?>
            <div class="inmopress-form-group">
                <label>
                    <?php _e('Nombre Completo', 'inmopress'); ?>
                </label>
                <input type="text" name="nombre" required placeholder="<?php _e('Tu nombre', 'inmopress'); ?>">
            </div>
        <?php endif; ?>

        <?php if (in_array('email', $fields)): ?>
            <div class="inmopress-form-group">
                <label>
                    <?php _e('Email', 'inmopress'); ?>
                </label>
                <input type="email" name="email" required placeholder="<?php _e('tu@email.com', 'inmopress'); ?>">
            </div>
        <?php endif; ?>

        <?php if (in_array('telefono', $fields)): ?>
            <div class="inmopress-form-group">
                <label>
                    <?php _e('Teléfono', 'inmopress'); ?>
                </label>
                <input type="tel" name="telefono" required placeholder="<?php _e('Tu teléfono', 'inmopress'); ?>">
            </div>
        <?php endif; ?>

        <?php if (in_array('mensaje', $fields)): ?>
            <div class="inmopress-form-group">
                <label>
                    <?php _e('Mensaje', 'inmopress'); ?>
                </label>
                <textarea name="mensaje"
                    placeholder="<?php _e('Estoy interesado en este inmueble...', 'inmopress'); ?>"></textarea>
            </div>
        <?php endif; ?>

        <input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>">
        <input type="hidden" name="type" value="<?php echo esc_attr($type); ?>">

        <div class="inmopress-form-submit">
            <button type="submit" class="btn-inmo">
                <?php _e('Enviar Solicitud', 'inmopress'); ?>
            </button>
        </div>

    </form>

</div>