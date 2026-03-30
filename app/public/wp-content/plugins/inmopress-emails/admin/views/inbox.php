<?php
if (!defined('ABSPATH')) {
    exit;
}

$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;

$args = array(
    'post_type' => 'impress_message',
    'posts_per_page' => $per_page,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
);

$messages = new WP_Query($args);
?>
<div class="wrap">
    <h1>Bandeja de Entrada</h1>
    
    <div style="margin: 20px 0;">
        <a href="<?php echo admin_url('edit.php?post_type=impress_property&page=inmopress-emails&tab=compose'); ?>" class="button button-primary">Nuevo Email</a>
    </div>

    <?php if ($messages->have_posts()): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>De</th>
                    <th>Para</th>
                    <th>Asunto</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($messages->have_posts()): $messages->the_post(); ?>
                    <?php
                    $from_email = get_post_meta(get_the_ID(), 'impress_message_from_email', true);
                    $to_email = get_post_meta(get_the_ID(), 'impress_message_to_email', true);
                    $direction = get_post_meta(get_the_ID(), 'impress_message_direction', true);
                    $status = get_post_meta(get_the_ID(), 'impress_message_status', true);
                    ?>
                    <tr>
                        <td><?php echo esc_html($from_email); ?></td>
                        <td><?php echo esc_html($to_email); ?></td>
                        <td>
                            <a href="<?php echo get_edit_post_link(get_the_ID()); ?>">
                                <?php echo esc_html(get_the_title()); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html(get_the_date()); ?></td>
                        <td>
                            <span class="status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                            <?php if ($direction === 'incoming'): ?>
                                <span style="color: green;">↓</span>
                            <?php else: ?>
                                <span style="color: blue;">↑</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo get_edit_post_link(get_the_ID()); ?>">Ver</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php
        echo paginate_links(array(
            'total' => $messages->max_num_pages,
            'current' => $paged,
            'format' => '?paged=%#%',
        ));
        ?>
    <?php else: ?>
        <p>No hay mensajes.</p>
    <?php endif; ?>
</div>
