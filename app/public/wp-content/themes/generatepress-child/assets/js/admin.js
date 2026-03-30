/**
 * GPCP Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Media uploader for branding and maintenance logos
        $('.gpcp-upload-button').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var targetInput = $('#' + button.data('target'));
            
            var mediaUploader = wp.media({
                title: 'Seleccionar Imagen',
                button: {
                    text: 'Usar esta imagen'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                targetInput.val(attachment.url);
            });

            mediaUploader.open();
        });
    });

})(jQuery);



