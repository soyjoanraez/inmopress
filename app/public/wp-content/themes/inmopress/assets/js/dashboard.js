/**
 * Dashboard JavaScript
 * Minimal CRM Dashboard
 */

(function($) {
    'use strict';

    var Dashboard = {
        init: function() {
            this.mobileMenu();
            this.confirmActions();
            this.ajaxForms();
            this.tooltips();
            this.tableFilters();
        },

        // Mobile menu toggle
        mobileMenu: function() {
            $('.dashboard-menu-toggle').on('click', function(e) {
                e.preventDefault();
                $('.dashboard-sidebar').toggleClass('open');
            });

            // Close sidebar when clicking outside on mobile
            $(document).on('click', function(e) {
                if ($(window).width() <= 768) {
                    if (!$(e.target).closest('.dashboard-sidebar, .dashboard-menu-toggle').length) {
                        $('.dashboard-sidebar').removeClass('open');
                    }
                }
            });
        },

        // Confirm delete actions
        confirmActions: function() {
            $(document).on('click', '.btn-delete, [data-action="delete"]', function(e) {
                if (!confirm('¿Estás seguro de que quieres eliminar este elemento?')) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        // Handle AJAX forms
        ajaxForms: function() {
            $(document).on('submit', '.ajax-form', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $submitBtn = $form.find('button[type="submit"]');
                var originalText = $submitBtn.text();

                $submitBtn.prop('disabled', true).text('Guardando...');

                $.ajax({
                    url: inmopressDashboard.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: $form.data('action'),
                        nonce: inmopressDashboard.nonce,
                        form_data: $form.serialize()
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            } else {
                                location.reload();
                            }
                        } else {
                            alert(response.data.message || 'Error al guardar');
                            $submitBtn.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function() {
                        alert('Error de conexión. Por favor, intenta de nuevo.');
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });
        },

        // Initialize tooltips
        tooltips: function() {
            $('[data-tooltip]').each(function() {
                $(this).attr('title', $(this).data('tooltip'));
            });
        },

        // Table filters and search
        tableFilters: function() {
            var $searchInput = $('.table-search');
            if ($searchInput.length) {
                $searchInput.on('keyup', function() {
                    var value = $(this).val().toLowerCase();
                    $('.dashboard-table tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        Dashboard.init();

        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 300);
            }
        });

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    });

})(jQuery);

