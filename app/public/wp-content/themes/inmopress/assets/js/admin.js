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
                
                // Show preview if it's an image input
                if (targetInput.attr('type') === 'url' || targetInput.attr('name').indexOf('logo') !== -1) {
                    var preview = targetInput.siblings('p').find('img');
                    if (preview.length) {
                        preview.attr('src', attachment.url).parent().show();
                    } else {
                        targetInput.after('<p><img src="' + attachment.url + '" style="max-width: 300px; height: auto; margin-top: 10px;" /></p>');
                    }
                }
            });

            mediaUploader.open();
        });

        // Add tooltips
        $('[data-tooltip]').each(function() {
            var $this = $(this);
            var tooltipText = $this.data('tooltip');
            
            $this.addClass('gpcp-tooltip');
            $this.append('<span class="gpcp-tooltip-text">' + tooltipText + '</span>');
        });

        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 50
                }, 500);
            }
        });

        // Form validation feedback
        $('form').on('submit', function() {
            var $form = $(this);
            var $submit = $form.find('input[type="submit"], button[type="submit"]');
            
            $submit.prop('disabled', true);
            setTimeout(function() {
                $submit.prop('disabled', false);
            }, 3000);
        });

        // Auto-dismiss notices after 5 seconds
        setTimeout(function() {
            $('.notice.is-dismissible').fadeOut();
        }, 5000);
    });

})(jQuery);

