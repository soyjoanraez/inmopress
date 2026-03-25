(function($) {
    'use strict';

    var dashboard = {
        init: function() {
            if (typeof inmopressDashboard === 'undefined') {
                return;
            }

            this.renderCharts();
            this.initSearch();
            this.initFilters();
        },

        renderCharts: function() {
            if (typeof Chart === 'undefined' || !inmopressDashboard.chartData) {
                return;
            }

            // Gráfica de líneas: Propiedades, Clientes, Leads
            var ctx = document.getElementById('inmopress-chart-activity');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: inmopressDashboard.chartData.labels,
                        datasets: [
                            {
                                label: 'Propiedades',
                                data: inmopressDashboard.chartData.properties,
                                borderColor: '#6C5DD3', // Color primario púrpura
                                backgroundColor: 'rgba(108, 93, 211, 0.1)',
                                tension: 0.4,
                            },
                            {
                                label: 'Clientes',
                                data: inmopressDashboard.chartData.clients,
                                borderColor: '#52C41A', // Verde éxito
                                backgroundColor: 'rgba(82, 196, 26, 0.1)',
                                tension: 0.4,
                            },
                            {
                                label: 'Leads',
                                data: inmopressDashboard.chartData.leads,
                                borderColor: '#3D8EFF', // Azul información
                                backgroundColor: 'rgba(61, 142, 255, 0.1)',
                                tension: 0.4,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                            },
                        },
                    },
                });
            }

            // Gráfica de dona: Distribución de operaciones
            var ctxDoughnut = document.getElementById('inmopress-chart-operations');
            if (ctxDoughnut && inmopressDashboard.operationsData) {
                // Usar datos reales del servidor
                var operationsData = inmopressDashboard.operationsData;
                var venta = operationsData.venta || 0;
                var alquiler = operationsData.alquiler || 0;
                var other = operationsData.other || 0;

                // Preparar datos y labels
                var labels = [];
                var data = [];
                var colors = [];

                if (venta > 0) {
                    labels.push('Venta');
                    data.push(venta);
                    colors.push('#6C5DD3'); // Color primario púrpura
                }

                if (alquiler > 0) {
                    labels.push('Alquiler');
                    data.push(alquiler);
                    colors.push('#52C41A'); // Verde éxito
                }

                if (other > 0) {
                    labels.push('Otras');
                    data.push(other);
                    colors.push('#3D8EFF'); // Azul información
                }

                // Solo mostrar gráfica si hay datos
                if (data.length > 0 && data.reduce(function(a, b) { return a + b; }, 0) > 0) {
                    new Chart(ctxDoughnut, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: colors,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            var label = context.label || '';
                                            var value = context.parsed || 0;
                                            var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                            var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            },
                        },
                    });
                } else {
                    // Mostrar mensaje si no hay datos
                    ctxDoughnut.parentElement.innerHTML = '<p style="text-align: center; color: var(--color-text-secondary); padding: var(--spacing-lg);">No hay datos de operaciones disponibles</p>';
                }
            }
        },

        initSearch: function() {
            var $searchInput = $('#inmopress-global-search');
            if (!$searchInput.length) {
                return;
            }

            var searchTimeout;
            var $results = $('#inmopress-search-results');

            // Búsqueda con debounce
            $searchInput.on('input', function() {
                clearTimeout(searchTimeout);
                var query = $(this).val().trim();

                if (query.length < 2) {
                    $results.hide().empty();
                    return;
                }

                searchTimeout = setTimeout(function() {
                    dashboard.performSearch(query);
                }, 300);
            });

            // Ocultar resultados al hacer click fuera
            $(document).on('click', function(e) {
                if (!$searchInput.is(e.target) && !$results.is(e.target) && $results.has(e.target).length === 0) {
                    $results.hide();
                }
            });

            // Ocultar resultados al presionar ESC
            $searchInput.on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $results.hide();
                    $(this).blur();
                }
            });

            // Prevenir que el click en resultados cierre el dropdown
            $results.on('click', function(e) {
                e.stopPropagation();
            });
        },

        performSearch: function(query) {
            $.ajax({
                url: inmopressDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'inmopress_global_search',
                    query: query,
                    nonce: inmopressDashboard.nonce,
                },
                success: function(response) {
                    if (response.success) {
                        dashboard.displaySearchResults(response.data);
                    } else {
                        dashboard.displaySearchError(response.data && response.data.message ? response.data.message : 'Error en la búsqueda');
                    }
                },
                error: function(xhr, status, error) {
                    dashboard.displaySearchError('Error de conexión. Por favor, intenta de nuevo.');
                },
            });
        },

        displaySearchResults: function(results) {
            var $results = $('#inmopress-search-results');
            if (!$results.length) {
                $results = $('<div id="inmopress-search-results" class="inmopress-search-results"></div>');
                $('#inmopress-global-search').after($results);
            }

            if (results.length === 0) {
                $results.html('<p class="search-no-results">No se encontraron resultados</p>').show();
                return;
            }

            var html = '<ul class="search-results-list">';
            results.forEach(function(item) {
                html += '<li class="search-result-item">';
                html += '<a href="' + item.url + '" class="search-result-link">';
                html += '<span class="search-result-title">' + item.title + '</span>';
                html += '<span class="search-result-type badge badge-grey small">' + item.type + '</span>';
                html += '</a>';
                html += '</li>';
            });
            html += '</ul>';

            $results.html(html).show();
        },

        displaySearchError: function(message) {
            var $results = $('#inmopress-search-results');
            if (!$results.length) {
                $results = $('<div id="inmopress-search-results" class="inmopress-search-results"></div>');
                $('#inmopress-global-search').after($results);
            }
            $results.html('<p class="search-error">' + message + '</p>').show();
        },

        initFilters: function() {
            $('.inmopress-filter-toggle').on('click', function() {
                $(this).toggleClass('active');
                $('.inmopress-filters-panel').slideToggle();
            });
        },
    };

    $(document).ready(function() {
        dashboard.init();
    });

})(jQuery);
