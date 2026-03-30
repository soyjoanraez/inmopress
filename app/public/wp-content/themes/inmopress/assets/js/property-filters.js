/**
 * Property Filters JavaScript
 *
 * @package Inmopress\CRM
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const $filtersForm = $('#inmopress-filters-form');
        const $filtersContent = $('.filters-content');
        const $filtersToggle = $('.filters-toggle');
        const $propertiesContainer = $('.inmopress-properties-container');
        const $provinciaSelect = $('#filter-provincia');
        const $poblacionSelect = $('#filter-poblacion');

        // Toggle filters visibility
        $filtersToggle.on('click', function() {
            $filtersContent.slideToggle();
            $(this).find('.toggle-icon').toggleClass('rotated');
        });

        // Update municipalities when province changes
        $provinciaSelect.on('change', function() {
            const provinceId = $(this).val();
            
            if (!provinceId) {
                $poblacionSelect.html('<option value="">Todas</option>');
                return;
            }

            $.ajax({
                url: inmopressFilters.ajaxurl,
                type: 'POST',
                data: {
                    action: 'inmopress_get_municipalities',
                    province_id: provinceId
                },
                success: function(response) {
                    if (response.success && response.data.municipalities) {
                        let options = '<option value="">Todas</option>';
                        response.data.municipalities.forEach(function(municipality) {
                            options += '<option value="' + municipality.value + '">' + municipality.label + '</option>';
                        });
                        $poblacionSelect.html(options);
                    }
                }
            });
        });

        // Handle form submission
        $filtersForm.on('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });

        // Handle reset
        $filtersForm.on('reset', function() {
            setTimeout(function() {
                applyFilters();
            }, 100);
        });

        // Auto-submit on select/checkbox change (optional - can be disabled)
        $filtersForm.find('select, input[type="radio"]').on('change', function() {
            // Uncomment to auto-submit on change
            // applyFilters();
        });

        /**
         * Apply filters via AJAX
         */
        function applyFilters() {
            const formData = $filtersForm.serializeArray();
            const filters = {};
            const layout = $propertiesContainer.data('layout') || 'grid';

            // Convert form data to filters object
            formData.forEach(function(item) {
                if (item.name.endsWith('[]')) {
                    const key = item.name.replace('[]', '');
                    if (!filters[key]) {
                        filters[key] = [];
                    }
                    filters[key].push(item.value);
                } else {
                    filters[item.name] = item.value;
                }
            });

            // Show loading
            $propertiesContainer.parent().addClass('loading');

            $.ajax({
                url: inmopressFilters.ajaxurl,
                type: 'POST',
                data: {
                    action: 'inmopress_filter_properties',
                    nonce: inmopressFilters.nonce,
                    filters: filters,
                    layout: layout,
                    paged: 1,
                    posts_per_page: $propertiesContainer.data('posts-per-page') || 9
                },
                success: function(response) {
                    if (response.success) {
                        // Replace content
                        const $parent = $propertiesContainer.parent();
                        $propertiesContainer.replaceWith(response.data.html);
                        
                        // Update URL without reload
                        updateURL(filters);
                    } else {
                        console.error('Error:', response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    $propertiesContainer.parent().removeClass('loading');
                }
            });
        }

        /**
         * Update URL with filters
         */
        function updateURL(filters) {
            const url = new URL(window.location.href);
            
            // Clear existing filter params
            ['proposito', 'provincia', 'poblacion', 'tipo_vivienda', 'precio_min', 'precio_max', 
             'dormitorios_min', 'banos_min', 'superficie_min', 'superficie_max', 'orderby', 'order'].forEach(function(key) {
                url.searchParams.delete(key);
            });

            // Add new filter params
            Object.keys(filters).forEach(function(key) {
                if (filters[key] && filters[key] !== '') {
                    if (Array.isArray(filters[key])) {
                        filters[key].forEach(function(value) {
                            url.searchParams.append(key + '[]', value);
                        });
                    } else {
                        url.searchParams.set(key, filters[key]);
                    }
                }
            });

            // Update URL without reload
            window.history.pushState({}, '', url);
        }

        // Handle browser back/forward
        window.addEventListener('popstate', function() {
            // Reload page to apply URL filters
            window.location.reload();
        });
    });

})(jQuery);


