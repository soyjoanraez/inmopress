/**
 * Inmopress Dashboard - Sidebar Mobile Toggle
 * Maneja el toggle del sidebar en mobile y animaciones
 * Versión: 1.0.0
 */

(function() {
    'use strict';

    var sidebar = {
        init: function() {
            this.createToggleButton();
            this.createOverlay();
            this.bindEvents();
            this.handleResize();
        },

        createToggleButton: function() {
            var topBar = document.querySelector('.crm-top-bar');
            if (!topBar) return;

            var toggleButton = document.createElement('button');
            toggleButton.className = 'crm-menu-toggle';
            toggleButton.setAttribute('aria-label', 'Toggle menu');
            toggleButton.innerHTML = '<span class="dashicons dashicons-menu"></span>';
            
            topBar.insertBefore(toggleButton, topBar.firstChild);
        },

        createOverlay: function() {
            var wrapper = document.querySelector('.inmopress-crm-wrapper');
            if (!wrapper) return;

            var overlay = document.createElement('div');
            overlay.className = 'crm-sidebar-overlay';
            wrapper.appendChild(overlay);
        },

        bindEvents: function() {
            var toggleButton = document.querySelector('.crm-menu-toggle');
            var sidebar = document.querySelector('.crm-sidebar');
            var overlay = document.querySelector('.crm-sidebar-overlay');

            if (toggleButton) {
                toggleButton.addEventListener('click', this.toggleSidebar.bind(this));
            }

            if (overlay) {
                overlay.addEventListener('click', this.closeSidebar.bind(this));
            }

            // Cerrar sidebar con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar && sidebar.classList.contains('is-open')) {
                    this.closeSidebar();
                }
            }.bind(this));

            // Cerrar sidebar al hacer click en un enlace del menú (mobile)
            if (sidebar) {
                var navLinks = sidebar.querySelectorAll('.crm-nav-item');
                navLinks.forEach(function(link) {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 1023) {
                            this.closeSidebar();
                        }
                    }.bind(this));
                }.bind(this));
            }
        },

        toggleSidebar: function() {
            var sidebar = document.querySelector('.crm-sidebar');
            var overlay = document.querySelector('.crm-sidebar-overlay');

            if (!sidebar) return;

            if (sidebar.classList.contains('is-open')) {
                this.closeSidebar();
            } else {
                this.openSidebar();
            }
        },

        openSidebar: function() {
            var sidebar = document.querySelector('.crm-sidebar');
            var overlay = document.querySelector('.crm-sidebar-overlay');
            var body = document.body;

            if (sidebar) {
                sidebar.classList.add('is-open');
            }

            if (overlay) {
                overlay.classList.add('is-active');
            }

            // Prevenir scroll del body cuando sidebar está abierto
            if (body) {
                body.style.overflow = 'hidden';
            }
        },

        closeSidebar: function() {
            var sidebar = document.querySelector('.crm-sidebar');
            var overlay = document.querySelector('.crm-sidebar-overlay');
            var body = document.body;

            if (sidebar) {
                sidebar.classList.remove('is-open');
            }

            if (overlay) {
                overlay.classList.remove('is-active');
            }

            // Restaurar scroll del body
            if (body) {
                body.style.overflow = '';
            }
        },

        handleResize: function() {
            var resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    // Cerrar sidebar automáticamente si se cambia a desktop
                    if (window.innerWidth > 1023) {
                        this.closeSidebar();
                    }
                }.bind(this), 250);
            }.bind(this));
        }
    };

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            sidebar.init();
        });
    } else {
        sidebar.init();
    }

})();
