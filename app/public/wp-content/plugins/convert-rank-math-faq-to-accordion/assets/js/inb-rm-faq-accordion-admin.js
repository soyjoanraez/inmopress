jQuery(document).ready(function($) {
    // Initialize color pickers
    $('.inbrmfa-color-picker').wpColorPicker();


    $('#INBRMFA_reset_settings').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to reset all settings to default?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'INBRMFA_reset_settings'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Settings reset to default. Please refresh the page.');
                        location.reload();
                    } else {
                        alert('Error resetting settings. Please try again.');
                    }
                }
            });
        }
    });
});