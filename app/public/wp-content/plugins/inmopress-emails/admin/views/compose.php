<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Redactar Email</h1>

    <form id="compose-email-form">
        <?php wp_nonce_field('inmopress_email_nonce', 'nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th><label>Para</label></th>
                <td>
                    <input type="email" name="to_email" class="regular-text" required>
                    <input type="text" name="to_name" class="regular-text" placeholder="Nombre (opcional)" style="margin-top: 5px;">
                </td>
            </tr>
            <tr>
                <th><label>Asunto</label></th>
                <td><input type="text" name="subject" class="large-text" required></td>
            </tr>
            <tr>
                <th><label>Mensaje</label></th>
                <td>
                    <?php
                    wp_editor('', 'body_html', array(
                        'textarea_name' => 'body_html',
                        'textarea_rows' => 15,
                        'media_buttons' => true,
                    ));
                    ?>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary">Enviar</button>
        </p>
    </form>

    <div id="send-result" style="display: none; margin-top: 20px;"></div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#compose-email-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $result = $('#send-result');

        $result.hide().html('<p>Enviando...</p>').show();

        $.post(ajaxurl, {
            action: 'inmopress_send_email',
            to_email: $('input[name="to_email"]').val(),
            to_name: $('input[name="to_name"]').val(),
            subject: $('input[name="subject"]').val(),
            body_html: tinyMCE.get('body_html').getContent(),
            nonce: '<?php echo wp_create_nonce('inmopress_email_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $result.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                $form[0].reset();
                tinyMCE.get('body_html').setContent('');
            } else {
                $result.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
            }
        });
    });
});
</script>
