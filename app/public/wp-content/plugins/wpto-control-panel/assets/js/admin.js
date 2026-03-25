/**
 * WP Total Optimizer - Admin JavaScript
 * Maneja la interactividad del panel de control
 */

(function($) {
    'use strict';
    
    // Variables globales
    let currentTab = 'security';
    
    /**
     * Inicialización cuando el DOM está listo
     */
    $(document).ready(function() {
        initTabs();
        initToggleSwitches();
        initForms();
        initRangeInputs();
        loadActiveStats();
        checkCacheAvailability();
    });
    
    /**
     * Inicializar sistema de tabs
     */
    function initTabs() {
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            const targetTab = $(this).attr('href').substring(1);
            
            // Actualizar tabs
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Actualizar contenido
            $('.tab-content').removeClass('active');
            $('#' + targetTab).addClass('active');
            
            currentTab = targetTab;
            
            // Guardar tab activo en localStorage
            localStorage.setItem('wpto_active_tab', targetTab);
        });
        
        // Restaurar última tab activa
        const savedTab = localStorage.getItem('wpto_active_tab');
        if (savedTab) {
            $('.nav-tab[href="#' + savedTab + '"]').click();
        }
        
        // Activar tab desde hash de URL
        if (window.location.hash) {
            const hashTab = window.location.hash.substring(1);
            $('.nav-tab[href="#' + hashTab + '"]').click();
        }
    }
    
    /**
     * Inicializar toggle switches
     */
    function initToggleSwitches() {
        // Mostrar/ocultar configuración al activar toggle
        $('.toggle-switch input[type="checkbox"]').on('change', function() {
            const $card = $(this).closest('.function-card');
            const $config = $card.find('.function-config');
            
            if ($(this).is(':checked')) {
                $config.slideDown(300);
                $card.addClass('active');
            } else {
                $config.slideUp(300);
                $card.removeClass('active');
            }
        });
        
        // Inicializar estado de las tarjetas
        $('.toggle-switch input[type="checkbox"]:checked').each(function() {
            const $card = $(this).closest('.function-card');
            const $config = $card.find('.function-config');
            $config.show();
            $card.addClass('active');
        });
    }
    
    /**
     * Inicializar formularios
     */
    function initForms() {
        $('.wpto-options-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $button = $form.find('button[type="submit"]');
            const $status = $form.find('.save-status');
            const module = $form.data('module');
            
            // Deshabilitar botón
            $button.prop('disabled', true);
            
            // Mostrar estado de guardado
            $status.html('<span class="wpto-loading"></span> Guardando...').addClass('saving');
            
            // Serializar datos del formulario
            const formData = $form.serializeArray();
            const options = {};
            
            // Primero, añadir todos los checkboxes explícitamente (marcados y desmarcados)
            $form.find('input[type="checkbox"][name*="[' + module + ']"]').each(function() {
                const $checkbox = $(this);
                const name = $checkbox.attr('name');
                const isChecked = $checkbox.is(':checked');
                
                // Si no está en formData (porque serializeArray solo incluye checkboxes marcados)
                const exists = formData.some(function(item) {
                    return item.name === name;
                });
                
                if (!exists) {
                    // Añadir checkbox desmarcado explícitamente
                    formData.push({
                        name: name,
                        value: isChecked ? '1' : '0'
                    });
                } else {
                    // Asegurar que el valor sea '1' si está marcado
                    formData.forEach(function(item) {
                        if (item.name === name) {
                            item.value = isChecked ? '1' : '0';
                        }
                    });
                }
            });
            
            // Convertir array a objeto anidado
            formData.forEach(function(item) {
                const keys = item.name.replace(/\]/g, '').split('[');
                let current = options;
                
                keys.forEach(function(key, index) {
                    if (index === keys.length - 1) {
                        // Solo incluir si el valor no es '0' (checkboxes desmarcados)
                        // Los valores '0' se enviarán pero el servidor los eliminará
                        current[key] = item.value;
                    } else {
                        current[key] = current[key] || {};
                        current = current[key];
                    }
                });
            });
            
            // Enviar por AJAX
            $.ajax({
                url: wptoAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpto_save_options',
                    nonce: wptoAdmin.nonce,
                    module: module,
                    options: options[module] || {}
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('✓ ' + wptoAdmin.strings.saved)
                               .removeClass('saving')
                               .addClass('success');
                        
                        // Actualizar estadísticas con los conteos del servidor
                        if (response.data && response.data.counts) {
                            const counts = response.data.counts;
                            animateCounter($('#active-security'), counts.security);
                            animateCounter($('#active-optimization'), counts.optimization);
                            animateCounter($('#active-images'), counts.images);
                            animateCounter($('#active-seo'), counts.seo);
                        } else {
                            // Fallback: cargar desde servidor
                            loadActiveStats();
                        }
                        
                        // Limpiar mensaje después de 3 segundos
                        setTimeout(function() {
                            $status.fadeOut(function() {
                                $(this).html('').removeClass('success').show();
                            });
                        }, 3000);
                    } else {
                        $status.html('✗ ' + (response.data || wptoAdmin.strings.error))
                               .removeClass('saving')
                               .addClass('error');
                    }
                },
                error: function() {
                    $status.html('✗ ' + wptoAdmin.strings.error)
                           .removeClass('saving')
                           .addClass('error');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });
    }
    
    /**
     * Inicializar range inputs
     */
    function initRangeInputs() {
        $('input[type="range"]').each(function() {
            const $range = $(this);
            const $valueDisplay = $range.siblings('.range-value');
            
            // Actualizar valor mostrado
            $range.on('input', function() {
                $valueDisplay.text($(this).val() + '%');
            });
            
            // Inicializar valor
            $valueDisplay.text($range.val() + '%');
        });
    }
    
    /**
     * Cargar estadísticas de funciones activas
     */
    function loadActiveStats() {
        $.ajax({
            url: wptoAdmin.ajaxurl,
            method: 'POST',
            data: {
                action: 'wpto_get_status',
                nonce: wptoAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    const counts = response.data;
                    
                    // Animar contadores
                    animateCounter($('#active-security'), counts.security);
                    animateCounter($('#active-optimization'), counts.optimization);
                    animateCounter($('#active-images'), counts.images);
                    animateCounter($('#active-seo'), counts.seo);
                }
            }
        });
    }
    
    /**
     * Animar contador numérico
     */
    function animateCounter($element, targetValue) {
        const currentValue = parseInt($element.text()) || 0;
        
        $({ value: currentValue }).animate(
            { value: targetValue },
            {
                duration: 800,
                easing: 'swing',
                step: function() {
                    $element.text(Math.ceil(this.value));
                },
                complete: function() {
                    $element.text(targetValue);
                }
            }
        );
    }
    
    /**
     * Verificar disponibilidad de sistemas de caché
     */
    function checkCacheAvailability() {
        const $cacheStatus = $('#cache-detection');
        
        if ($cacheStatus.length) {
            $cacheStatus.html('<span class="wpto-loading"></span> Detectando...');
            
            // Detección real desde el servidor
            $.ajax({
                url: wptoAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpto_detect_cache',
                    nonce: wptoAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $cacheStatus.html(response.data.message);
                    } else {
                        $cacheStatus.html('<span style="color: #d63638;">✗ Error al detectar</span>');
                    }
                },
                error: function() {
                    $cacheStatus.html('<span style="color: #d63638;">✗ Error al detectar</span>');
                }
            });
        }
    }
    
    /**
     * Helpers para validación
     */
    function validateLoginSlug(slug) {
        // Solo letras, números y guiones
        const pattern = /^[a-z0-9-]+$/;
        return pattern.test(slug);
    }
    
    function validateDomain(domain) {
        // Validación básica de dominio
        const pattern = /^([a-z0-9-]+\.)+[a-z]{2,}$/i;
        return pattern.test(domain);
    }
    
    /**
     * Confirmaciones antes de desactivar funciones críticas
     */
    $(document).on('change', '.priority-critical input[type="checkbox"]', function() {
        if (!$(this).is(':checked')) {
            const functionName = $(this).closest('.function-card').find('h3').text();
            
            if (!confirm('¿Estás seguro de desactivar "' + functionName + '"? Esta es una función crítica.')) {
                $(this).prop('checked', true);
                return false;
            }
        }
    });
    
    /**
     * Validación en tiempo real de campos
     */
    $('input[name="security[login_slug]"]').on('blur', function() {
        const slug = $(this).val();
        
        if (slug && !validateLoginSlug(slug)) {
            alert('La URL de login solo puede contener letras minúsculas, números y guiones.');
            $(this).focus();
        }
    });
    
    /**
     * Búsqueda/filtro de funciones (característica futura)
     */
    function initSearch() {
        const $searchInput = $('<input>')
            .attr({
                type: 'search',
                placeholder: 'Buscar función...',
                class: 'wpto-search'
            })
            .css({
                width: '300px',
                margin: '0 0 20px 0'
            });
        
        $('.tab-content').prepend($searchInput);
        
        $searchInput.on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            $('.function-card').each(function() {
                const title = $(this).find('h3').text().toLowerCase();
                const description = $(this).find('.function-description').text().toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    $(this).fadeIn(200);
                } else {
                    $(this).fadeOut(200);
                }
            });
        });
    }
    
    /**
     * Atajos de teclado
     */
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + S para guardar
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            $('.tab-content.active form').submit();
        }
        
        // Ctrl/Cmd + 1-4 para cambiar de tab
        if ((e.ctrlKey || e.metaKey) && e.key >= '1' && e.key <= '4') {
            e.preventDefault();
            const tabs = ['security', 'optimization', 'images', 'seo'];
            const tabIndex = parseInt(e.key) - 1;
            $('.nav-tab[href="#' + tabs[tabIndex] + '"]').click();
        }
    });
    
    /**
     * Exportar configuración
     */
    window.wptoExportConfig = function() {
        const config = {
            security: getFormData('security'),
            optimization: getFormData('optimization'),
            images: getFormData('images'),
            seo: getFormData('seo'),
            exported_at: new Date().toISOString(),
            version: '1.0.0'
        };
        
        const dataStr = JSON.stringify(config, null, 2);
        const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
        
        const exportFileDefaultName = 'wpto-config-' + Date.now() + '.json';
        
        const linkElement = document.createElement('a');
        linkElement.setAttribute('href', dataUri);
        linkElement.setAttribute('download', exportFileDefaultName);
        linkElement.click();
    };
    
    /**
     * Importar configuración
     */
    window.wptoImportConfig = function(fileInput) {
        const file = fileInput.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                try {
                    const config = JSON.parse(e.target.result);
                    
                    if (confirm('¿Importar esta configuración? Se sobrescribirán los ajustes actuales.')) {
                        // Cargar configuración en formularios
                        loadConfigToForms(config);
                        alert('Configuración importada correctamente. Haz clic en Guardar en cada pestaña.');
                    }
                } catch (error) {
                    alert('Error al leer el archivo de configuración');
                }
            };
            
            reader.readAsText(file);
        }
    };
    
    /**
     * Obtener datos de formulario
     */
    function getFormData(module) {
        const $form = $('.wpto-options-form[data-module="' + module + '"]');
        const formData = $form.serializeArray();
        const data = {};
        
        formData.forEach(function(item) {
            const keys = item.name.replace(/\]/g, '').split('[');
            let current = data;
            
            keys.forEach(function(key, index) {
                if (index === keys.length - 1) {
                    current[key] = item.value;
                } else {
                    current[key] = current[key] || {};
                    current = current[key];
                }
            });
        });
        
        return data[module] || {};
    }
    
    /**
     * Cargar configuración en formularios
     */
    function loadConfigToForms(config) {
        Object.keys(config).forEach(function(module) {
            if (['security', 'optimization', 'images', 'seo'].includes(module)) {
                const moduleConfig = config[module];
                const $form = $('.wpto-options-form[data-module="' + module + '"]');
                
                Object.keys(moduleConfig).forEach(function(key) {
                    const $input = $form.find('[name="' + module + '[' + key + ']"]');
                    
                    if ($input.is(':checkbox')) {
                        $input.prop('checked', moduleConfig[key] === '1');
                    } else {
                        $input.val(moduleConfig[key]);
                    }
                });
                
                // Actualizar toggles
                $form.find('.toggle-switch input:checked').trigger('change');
            }
        });
    }
    
    /**
     * Mostrar/ocultar ayuda contextual
     */
    $('.help-icon').on('click', function() {
        const $helpText = $(this).siblings('.help-text');
        $helpText.slideToggle();
    });
    
    /**
     * Notificaciones toast
     */
    function showToast(message, type = 'success') {
        const $toast = $('<div>')
            .addClass('wpto-toast wpto-toast-' + type)
            .text(message)
            .css({
                position: 'fixed',
                top: '32px',
                right: '20px',
                padding: '15px 20px',
                borderRadius: '4px',
                boxShadow: '0 4px 12px rgba(0,0,0,0.2)',
                zIndex: 100000,
                display: 'none'
            });
        
        if (type === 'success') {
            $toast.css({ background: '#00a32a', color: 'white' });
        } else if (type === 'error') {
            $toast.css({ background: '#d63638', color: 'white' });
        }
        
        $('body').append($toast);
        $toast.fadeIn(300);
        
        setTimeout(function() {
            $toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Exponer función globalmente
    window.wptoShowToast = showToast;
    
    /**
     * Inicializar Editor SEO
     */
    window.wptoInitSEOEditor = function() {
        initSEOCharacterCounters();
        initSERPPreview();
        initSEOAnalysis();
    };
    
    /**
     * Inicializar contadores de caracteres SEO
     */
    function initSEOCharacterCounters() {
        const $title = $('#wpto_seo_title');
        const $desc = $('#wpto_seo_description');
        const $titleLength = $('#wpto_title_length');
        const $descLength = $('#wpto_desc_length');
        const $titleStatus = $('#wpto_title_status');
        const $descStatus = $('#wpto_desc_status');
        
        function updateTitleCounter() {
            const length = $title.val().length;
            $titleLength.text(length + ' caracteres');
            
            let status = '';
            let color = '';
            
            if (length === 0) {
                status = '';
            } else if (length >= 50 && length <= 60) {
                status = '✓ Óptimo';
                color = '#00a32a';
            } else if (length >= 30 && length < 50) {
                status = '⚠ Corto (recomendado: 50-60)';
                color = '#dba617';
            } else if (length > 60 && length <= 70) {
                status = '⚠ Largo (puede cortarse)';
                color = '#dba617';
            } else if (length < 30) {
                status = '✗ Muy corto';
                color = '#d63638';
            } else {
                status = '✗ Muy largo (se cortará)';
                color = '#d63638';
            }
            
            $titleStatus.html(status).css('color', color);
        }
        
        function updateDescCounter() {
            const length = $desc.val().length;
            $descLength.text(length + ' caracteres');
            
            let status = '';
            let color = '';
            
            if (length === 0) {
                status = '';
            } else if (length >= 150 && length <= 160) {
                status = '✓ Óptimo';
                color = '#00a32a';
            } else if (length >= 120 && length < 150) {
                status = '⚠ Corto (recomendado: 150-160)';
                color = '#dba617';
            } else if (length > 160) {
                status = '✗ Muy largo (se cortará)';
                color = '#d63638';
            } else if (length < 120) {
                status = '✗ Muy corto';
                color = '#d63638';
            }
            
            $descStatus.html(status).css('color', color);
        }
        
        $title.on('input', updateTitleCounter);
        $desc.on('input', updateDescCounter);
        
        // Inicializar
        updateTitleCounter();
        updateDescCounter();
    }
    
    /**
     * Inicializar Vista Previa SERP
     */
    function initSERPPreview() {
        const $desktopBtn = $('#wpto-serp-desktop');
        const $mobileBtn = $('#wpto-serp-mobile');
        const $serpContent = $('#wpto-serp-content');
        const $title = $('#wpto_seo_title');
        const $desc = $('#wpto_seo_description');
        const $serpTitle = $('#serp-title');
        const $serpDesc = $('#serp-description');
        const $serpUrl = $('#serp-url');
        
        let currentView = 'desktop';
        
        // Toggle Desktop/Mobile
        $desktopBtn.on('click', function() {
            currentView = 'desktop';
            $desktopBtn.addClass('button-primary').removeClass('button');
            $mobileBtn.removeClass('button-primary').addClass('button');
            updateSERPPreview();
        });
        
        $mobileBtn.on('click', function() {
            currentView = 'mobile';
            $mobileBtn.addClass('button-primary').removeClass('button');
            $desktopBtn.removeClass('button-primary').addClass('button');
            updateSERPPreview();
        });
        
        function updateSERPPreview() {
            const title = $title.val() || $('#title').val() || 'Título del Post';
            const desc = $desc.val() || '';
            const url = window.location.href.replace(/\/wp-admin.*/, '') + '/';
            
            // Truncar según vista
            let displayTitle = title;
            let displayDesc = desc;
            
            if (currentView === 'desktop') {
                if (title.length > 60) {
                    displayTitle = title.substring(0, 57) + '...';
                }
                if (desc.length > 160) {
                    displayDesc = desc.substring(0, 157) + '...';
                }
            } else {
                // Mobile: más corto
                if (title.length > 50) {
                    displayTitle = title.substring(0, 47) + '...';
                }
                if (desc.length > 120) {
                    displayDesc = desc.substring(0, 117) + '...';
                }
            }
            
            $serpTitle.text(displayTitle);
            $serpDesc.text(displayDesc || 'Sin descripción');
            $serpUrl.text(url);
        }
        
        // Actualizar en tiempo real
        $title.on('input', updateSERPPreview);
        $desc.on('input', updateSERPPreview);
        
        // Inicializar
        updateSERPPreview();
    }
    
    /**
     * Inicializar Análisis SEO
     */
    function initSEOAnalysis() {
        const $title = $('#wpto_seo_title');
        const $desc = $('#wpto_seo_description');
        const $focusKeyword = $('#wpto_focus_keyword');
        
        function updateAnalysis() {
            analyzeTitleLength();
            analyzeDescLength();
            analyzeFocusKeyword();
            analyzeKeywordInTitle();
        }
        
        function analyzeTitleLength() {
            const length = $title.val().length;
            const $check = $('#check-title-length');
            let icon = '';
            let text = '';
            let status = '';
            
            if (length === 0) {
                icon = '⏳';
                text = 'Título SEO no definido';
                status = 'warning';
            } else if (length >= 50 && length <= 60) {
                icon = '✓';
                text = 'Longitud del título: Óptimo (' + length + ' caracteres)';
                status = 'success';
            } else if (length >= 30 && length < 50) {
                icon = '⚠';
                text = 'Longitud del título: Corto (' + length + ' caracteres, recomendado: 50-60)';
                status = 'warning';
            } else if (length > 60 && length <= 70) {
                icon = '⚠';
                text = 'Longitud del título: Largo (' + length + ' caracteres, puede cortarse)';
                status = 'warning';
            } else {
                icon = '✗';
                text = 'Longitud del título: Fuera de rango (' + length + ' caracteres)';
                status = 'error';
            }
            
            $check.find('.wpto-check-icon').text(icon);
            $check.find('.wpto-check-text').text(text);
            $check.removeClass('wpto-check-success wpto-check-warning wpto-check-error')
                  .addClass('wpto-check-' + status);
        }
        
        function analyzeDescLength() {
            const length = $desc.val().length;
            const $check = $('#check-desc-length');
            let icon = '';
            let text = '';
            let status = '';
            
            if (length === 0) {
                icon = '⏳';
                text = 'Meta descripción no definida';
                status = 'warning';
            } else if (length >= 150 && length <= 160) {
                icon = '✓';
                text = 'Longitud de la descripción: Óptimo (' + length + ' caracteres)';
                status = 'success';
            } else if (length >= 120 && length < 150) {
                icon = '⚠';
                text = 'Longitud de la descripción: Corta (' + length + ' caracteres, recomendado: 150-160)';
                status = 'warning';
            } else if (length > 160) {
                icon = '✗';
                text = 'Longitud de la descripción: Muy larga (' + length + ' caracteres, se cortará)';
                status = 'error';
            } else {
                icon = '✗';
                text = 'Longitud de la descripción: Muy corta (' + length + ' caracteres)';
                status = 'error';
            }
            
            $check.find('.wpto-check-icon').text(icon);
            $check.find('.wpto-check-text').text(text);
            $check.removeClass('wpto-check-success wpto-check-warning wpto-check-error')
                  .addClass('wpto-check-' + status);
        }
        
        function analyzeFocusKeyword() {
            const keyword = $focusKeyword.val().trim();
            const $check = $('#check-focus-keyword');
            let icon = '';
            let text = '';
            let status = '';
            
            if (keyword === '') {
                icon = '⚠';
                text = 'Palabra clave focus no definida';
                status = 'warning';
            } else {
                icon = '✓';
                text = 'Palabra clave focus: "' + keyword + '"';
                status = 'success';
            }
            
            $check.find('.wpto-check-icon').text(icon);
            $check.find('.wpto-check-text').text(text);
            $check.removeClass('wpto-check-success wpto-check-warning wpto-check-error')
                  .addClass('wpto-check-' + status);
        }
        
        function analyzeKeywordInTitle() {
            const keyword = $focusKeyword.val().trim().toLowerCase();
            const title = $title.val().toLowerCase();
            const $check = $('#check-keyword-in-title');
            let icon = '';
            let text = '';
            let status = '';
            
            if (keyword === '') {
                icon = '⏳';
                text = 'No se puede verificar (falta palabra clave focus)';
                status = 'warning';
            } else if (title.includes(keyword)) {
                icon = '✓';
                text = 'Palabra clave encontrada en el título';
                status = 'success';
            } else {
                icon = '✗';
                text = 'Palabra clave NO está en el título';
                status = 'error';
            }
            
            $check.find('.wpto-check-icon').text(icon);
            $check.find('.wpto-check-text').text(text);
            $check.removeClass('wpto-check-success wpto-check-warning wpto-check-error')
                  .addClass('wpto-check-' + status);
        }
        
        // Event listeners
        $title.on('input', updateAnalysis);
        $desc.on('input', updateAnalysis);
        $focusKeyword.on('input', updateAnalysis);
        
        // Inicializar
        updateAnalysis();
    }

    /**
     * Inicializar Panel de Encabezados
     */
    function initHeadingPanel() {
        const $panel = $('#wpto-heading-panel');
        if (!$panel.length) return;

        const $summary = $panel.find('.wpto-heading-summary');
        const $list = $panel.find('.wpto-heading-list');

        function getEditorContent() {
            if (typeof wp !== 'undefined' && wp.data && wp.data.select) {
                return wp.data.select('core/editor').getEditedPostContent() || '';
            }
            const $content = $('#content');
            return $content.length ? $content.val() : '';
        }

        function parseHeadings(html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const nodes = doc.querySelectorAll('h1,h2,h3,h4,h5,h6');
            const headings = [];
            nodes.forEach(function(node) {
                const level = parseInt(node.tagName.replace('H', ''), 10);
                const text = node.textContent.trim();
                headings.push({ level: level, text: text || '(sin texto)' });
            });
            return headings;
        }

        function render() {
            const html = getEditorContent();
            const headings = parseHeadings(html);

            const counts = {1:0,2:0,3:0,4:0,5:0,6:0};
            headings.forEach(function(h) { counts[h.level]++; });

            const warnings = [];
            if (counts[1] === 0) warnings.push('No hay H1');
            if (counts[1] > 1) warnings.push('Hay más de un H1');

            // Detectar saltos de nivel
            for (let i = 1; i < headings.length; i++) {
                const prev = headings[i - 1].level;
                const curr = headings[i].level;
                if (curr - prev > 1) {
                    warnings.push('Salto de H' + prev + ' a H' + curr);
                    break;
                }
            }

            let summaryHtml = 'H1: ' + counts[1] + ' | H2: ' + counts[2] + ' | H3: ' + counts[3] + ' | H4: ' + counts[4] + ' | H5: ' + counts[5] + ' | H6: ' + counts[6];
            if (warnings.length) {
                summaryHtml += '<div class="wpto-heading-warnings">⚠ ' + warnings.join(' · ') + '</div>';
            } else {
                summaryHtml += '<div class="wpto-heading-ok">✓ Estructura correcta</div>';
            }

            $summary.html(summaryHtml);
            $list.empty();

            if (headings.length === 0) {
                $list.append('<li class="wpto-heading-empty">Sin encabezados detectados</li>');
                return;
            }

            headings.forEach(function(h) {
                $list.append('<li class="wpto-heading-item level-' + h.level + '"><span class="wpto-heading-tag">H' + h.level + '</span> ' + $('<div>').text(h.text).html() + '</li>');
            });
        }

        render();

        if (typeof wp !== 'undefined' && wp.data && wp.data.subscribe) {
            let prev = '';
            wp.data.subscribe(function() {
                const current = getEditorContent();
                if (current !== prev) {
                    prev = current;
                    render();
                }
            });
        } else {
            $('#content').on('input', render);
        }
    }

    /**
     * Inicializar Edición Masiva SEO
     */
    function initBulkSEO() {
        const $container = $('#wpto-bulk-seo');
        if (!$container.length) return;

        const $tableBody = $container.find('tbody');
        const $status = $container.find('.wpto-bulk-status');
        const $globalStats = $container.find('.wpto-bulk-stats-global');
        let currentCursor = null;
        let currentDirection = 'next';
        let pageIndex = 1;
        let hasNext = false;
        let hasPrev = false;
        let nextCursor = null;
        let prevCursor = null;

        function loadPosts(reset = false, direction = 'next', cursorOverride = null, targetPageIndex = null) {
            if (reset) {
                currentCursor = null;
                currentDirection = 'next';
                pageIndex = 1;
                loadGlobalStats();
            }
            const effectiveDirection = direction || 'next';
            const effectiveCursor = cursorOverride !== null ? cursorOverride : currentCursor;
            const nextPageIndex = reset ? 1 : (targetPageIndex !== null ? targetPageIndex : pageIndex);
            $status.text('Cargando...');
            $.ajax({
                url: wptoAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpto_seo_bulk_load',
                    nonce: wptoAdmin.nonce,
                    post_type: $('#wpto-filter-post-type').val(),
                    post_status: $('#wpto-filter-status').val(),
                    seo_filter: $('#wpto-filter-seo').val(),
                    focus_filter: $('#wpto-filter-focus').val(),
                    keywords_filter: $('#wpto-filter-keywords').val(),
                    title_length_filter: $('#wpto-filter-title-length').val(),
                    desc_length_filter: $('#wpto-filter-desc-length').val(),
                    search: $('#wpto-filter-search').val(),
                    cursor: effectiveCursor,
                    direction: effectiveDirection,
                    per_page: $('#wpto-filter-per-page').val()
                },
                success: function(response) {
                    if (!response.success) {
                        $status.text('Error al cargar');
                        return;
                    }

                    const data = response.data;
                    currentCursor = effectiveCursor;
                    currentDirection = effectiveDirection;
                    pageIndex = nextPageIndex;
                    $container.find('.wpto-stat-total').text(data.stats.total);
                    $container.find('.wpto-stat-missing-title').text(data.stats.missing_title);
                    $container.find('.wpto-stat-missing-desc').text(data.stats.missing_desc);
                    $container.find('.wpto-stat-complete').text(data.stats.complete);

                    $tableBody.empty();
                    data.items.forEach(function(item) {
                        const row = [
                            '<tr class="wpto-bulk-row" data-id="' + item.id + '" data-title="' + item.title.replace(/\"/g, '&quot;') + '" data-excerpt="' + item.excerpt.replace(/\"/g, '&quot;') + '">',
                            '<td>' + item.id + '</td>',
                            '<td>' + item.title + '<div class="wpto-bulk-meta">' + item.post_type + ' · ' + item.status + '</div></td>',
                            '<td><input type="text" class="wpto-bulk-input" data-field="title" value="' + item.seo_title.replace(/\"/g, '&quot;') + '"></td>',
                            '<td><textarea class="wpto-bulk-input" data-field="description" rows="2">' + item.seo_description + '</textarea></td>',
                            '<td><input type="text" class="wpto-bulk-input" data-field="keywords" value="' + item.seo_keywords.replace(/\"/g, '&quot;') + '"></td>',
                            '<td><input type="text" class="wpto-bulk-input" data-field="focus" value="' + item.focus_keyword.replace(/\"/g, '&quot;') + '"></td>',
                            '<td><button class="button wpto-bulk-generate">⚙️</button></td>',
                            '</tr>'
                        ].join('');
                        $tableBody.append(row);
                    });

                    hasNext = !!data.has_next;
                    hasPrev = !!data.has_prev;
                    nextCursor = data.next_cursor || null;
                    prevCursor = data.prev_cursor || null;
                    $('#wpto-bulk-page-info').text('Página ' + pageIndex);
                    $('#wpto-bulk-prev').prop('disabled', !hasPrev);
                    $('#wpto-bulk-next').prop('disabled', !hasNext);

                    $status.text('Cargados: ' + data.items.length);
                },
                error: function() {
                    $status.text('Error al cargar');
                }
            });
        }

        function loadGlobalStats() {
            if (!$globalStats.length) return;
            $globalStats.addClass('is-loading');
            const $badge = $('#wpto-stat-last-ms-badge');
            $.ajax({
                url: wptoAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpto_seo_bulk_stats',
                    nonce: wptoAdmin.nonce,
                    post_type: $('#wpto-filter-post-type').val(),
                    post_status: $('#wpto-filter-status').val(),
                    seo_filter: $('#wpto-filter-seo').val(),
                    focus_filter: $('#wpto-filter-focus').val(),
                    keywords_filter: $('#wpto-filter-keywords').val(),
                    title_length_filter: $('#wpto-filter-title-length').val(),
                    desc_length_filter: $('#wpto-filter-desc-length').val(),
                    search: $('#wpto-filter-search').val()
                },
                success: function(response) {
                    if (!response.success) {
                        $container.find('.wpto-stat-total-global').text('—');
                        $container.find('.wpto-stat-missing-title-global').text('—');
                        $container.find('.wpto-stat-missing-desc-global').text('—');
                        $container.find('.wpto-stat-complete-global').text('—');
                        $container.find('.wpto-stat-last-ms').text('—');
                        if ($badge.length) {
                            $badge.attr('title', '').addClass('is-hidden');
                        }
                        return;
                    }

                    const payload = response.data || {};
                    const stats = payload.stats || payload;
                    const meta = payload.meta || {};
                    $container.find('.wpto-stat-total-global').text(stats.total || 0);
                    $container.find('.wpto-stat-missing-title-global').text(stats.missing_title || 0);
                    $container.find('.wpto-stat-missing-desc-global').text(stats.missing_desc || 0);
                    $container.find('.wpto-stat-complete-global').text(stats.complete || 0);
                    if (typeof meta.last_ms !== 'undefined' && meta.last_ms !== null) {
                        const suffix = meta.last_at ? (' · ' + meta.last_at) : '';
                        $container.find('.wpto-stat-last-ms').text(meta.last_ms + ' ms' + suffix);
                        if ($badge.length) {
                            const cachedText = payload.cached ? 'Sí' : 'No';
                            const lastAt = meta.last_at ? meta.last_at : '—';
                            $badge.attr('title', 'Generado: ' + lastAt + ' | Caché: ' + cachedText).removeClass('is-hidden');
                        }
                    } else {
                        $container.find('.wpto-stat-last-ms').text('—');
                        if ($badge.length) {
                            $badge.attr('title', '').addClass('is-hidden');
                        }
                    }
                },
                error: function() {
                    $container.find('.wpto-stat-total-global').text('—');
                    $container.find('.wpto-stat-missing-title-global').text('—');
                    $container.find('.wpto-stat-missing-desc-global').text('—');
                    $container.find('.wpto-stat-complete-global').text('—');
                    $container.find('.wpto-stat-last-ms').text('—');
                    if ($badge.length) {
                        $badge.attr('title', '').addClass('is-hidden');
                    }
                },
                complete: function() {
                    $globalStats.removeClass('is-loading');
                }
            });
        }

        function collectChanges() {
            const updates = [];
            $tableBody.find('tr').each(function() {
                const $row = $(this);
                const changed = $row.find('.wpto-bulk-input.dirty').length > 0;
                if (!changed) return;

                updates.push({
                    id: $row.data('id'),
                    title: $row.find('[data-field="title"]').val(),
                    description: $row.find('[data-field="description"]').val(),
                    keywords: $row.find('[data-field="keywords"]').val(),
                    focus: $row.find('[data-field="focus"]').val()
                });
            });
            return updates;
        }

        function saveChanges() {
            const updates = collectChanges();
            if (updates.length === 0) {
                $status.text('No hay cambios para guardar');
                return;
            }
            $status.text('Guardando...');
            $.ajax({
                url: wptoAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpto_seo_bulk_save',
                    nonce: wptoAdmin.nonce,
                    items: updates
                },
                success: function(response) {
                    if (response.success) {
                        $status.text('Guardado: ' + response.data.updated + ' posts');
                        $tableBody.find('.wpto-bulk-input').removeClass('dirty');
                    } else {
                        $status.text('Error al guardar');
                    }
                },
                error: function() {
                    $status.text('Error al guardar');
                }
            });
        }

        $container.on('input', '.wpto-bulk-input', function() {
            $(this).addClass('dirty');
        });

        $container.on('click', '.wpto-bulk-generate', function(e) {
            e.preventDefault();
            const $row = $(this).closest('tr');
            const title = $row.data('title') || '';
            const excerpt = $row.data('excerpt') || '';
            const $title = $row.find('[data-field="title"]');
            const $desc = $row.find('[data-field="description"]');
            if (!$title.val()) $title.val(title).addClass('dirty');
            if (!$desc.val()) $desc.val(excerpt).addClass('dirty');
        });

        $('#wpto-bulk-load').on('click', function(e) {
            e.preventDefault();
            loadPosts(true);
        });

        $('#wpto-filter-per-page').on('change', function() {
            loadPosts(true);
        });

        $('#wpto-bulk-save').on('click', function(e) {
            e.preventDefault();
            saveChanges();
        });

        $('#wpto-bulk-generate-all').on('click', function(e) {
            e.preventDefault();
            $tableBody.find('tr').each(function() {
                const $row = $(this);
                const title = $row.data('title') || '';
                const excerpt = $row.data('excerpt') || '';
                const $title = $row.find('[data-field="title"]');
                const $desc = $row.find('[data-field="description"]');
                if (!$title.val()) $title.val(title).addClass('dirty');
                if (!$desc.val()) $desc.val(excerpt).addClass('dirty');
            });
        });

        $('#wpto-bulk-prev').on('click', function(e) {
            e.preventDefault();
            if (!hasPrev || !prevCursor) return;
            const target = Math.max(1, pageIndex - 1);
            loadPosts(false, 'prev', prevCursor, target);
        });

        $('#wpto-bulk-next').on('click', function(e) {
            e.preventDefault();
            if (!hasNext || !nextCursor) return;
            const target = pageIndex + 1;
            loadPosts(false, 'next', nextCursor, target);
        });

        loadPosts(true);
    }

    /**
     * Inicializar modal de cambios de monitoreo de archivos
     */
    function initFileMonitorModal() {
        const $modal = $('#wpto-file-monitor-modal');
        if (!$modal.length) return null;

        const $showBtn = $('#wpto-file-monitor-show');
        const $statusBox = $('.wpto-file-monitor-status');
        const $lists = $modal.find('.wpto-modal-list');
        let lastChanges = null;
        let isLoading = false;
        let showBtnLabel = $showBtn.length ? $showBtn.text() : '';

        function hasChanges(changes) {
            if (!changes || typeof changes !== 'object') return false;
            const modified = changes.modified || [];
            const added = changes.added || [];
            const deleted = changes.deleted || [];
            return modified.length > 0 || added.length > 0 || deleted.length > 0;
        }

        function renderList(type, items) {
            const $list = $lists.filter('[data-type="' + type + '"]');
            $list.empty();
            if (isLoading) {
                $list.append('<li class="wpto-modal-empty">Cargando...</li>');
                return;
            }
            if (!items || items.length === 0) {
                $list.append('<li class="wpto-modal-empty">Sin cambios</li>');
                return;
            }
            items.forEach(function(item) {
                const safe = $('<div>').text(item).html();
                $list.append('<li>' + safe + '</li>');
            });
        }

        function renderModal() {
            const changes = lastChanges || {};
            renderList('modified', changes.modified || []);
            renderList('added', changes.added || []);
            renderList('deleted', changes.deleted || []);
        }

        function openModal() {
            renderModal();
            $modal.show();
        }

        function closeModal() {
            $modal.hide();
        }

        function setChanges(changes) {
            lastChanges = changes || null;
        }

        function updateShowButton() {
            if ($showBtn.length) {
                const hasKnown = lastChanges !== null;
                const allowClick = !isLoading && (!hasKnown || hasChanges(lastChanges));
                $showBtn.prop('disabled', !allowClick);
            }
        }

        function setLoading(loading) {
            isLoading = loading;
            renderModal();
            if ($showBtn.length) {
                $showBtn.toggleClass('wpto-loading', loading);
                $showBtn.prop('disabled', loading);
                $showBtn.text(loading ? 'Cargando...' : showBtnLabel);
            }
        }

        function fetchChanges() {
            $.ajax({
                url: wptoAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpto_get_file_changes',
                    nonce: wptoAdmin.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        setChanges(data.last_changes || null);
                        updateShowButton();
                        if ($statusBox.length) {
                            const lastScan = data.last_scan ? data.last_scan : 'Nunca';
                            const count = data.count ? data.count : 0;
                            const status = data.last_status ? data.last_status : 'n/a';
                            const summary = data.last_summary ? data.last_summary : '';
                            $statusBox.html('<strong>Último escaneo:</strong> ' + lastScan + '<br>' +
                                            '<strong>Archivos monitorizados:</strong> ' + count + '<br>' +
                                            '<strong>Estado:</strong> ' + status + '<br>' +
                                            '<strong>Resumen:</strong> ' + summary);
                            $statusBox.attr('data-last-changes', JSON.stringify(data.last_changes || {}));
                        }
                    } else {
                        setChanges(null);
                        updateShowButton();
                    }
                },
                error: function() {
                    setChanges(null);
                    updateShowButton();
                },
                complete: function() {
                    setLoading(false);
                    updateShowButton();
                }
            });
        }

        $modal.on('click', '.wpto-modal-close', function(e) {
            e.preventDefault();
            closeModal();
        });

        $modal.on('click', '.wpto-modal-overlay', function(e) {
            e.preventDefault();
            closeModal();
        });

        if ($showBtn.length) {
            $showBtn.on('click', function(e) {
                e.preventDefault();
                setLoading(true);
                openModal();
                fetchChanges();
            });
        }

        if ($statusBox.length) {
            const raw = $statusBox.attr('data-last-changes');
            if (raw) {
                try {
                    setChanges(JSON.parse(raw));
                } catch (err) {
                    setChanges(null);
                }
            } else {
                setChanges(null);
            }
            updateShowButton();
        }

        return {
            setChanges: setChanges,
            open: openModal,
            hasChanges: function() {
                return hasChanges(lastChanges);
            }
        };
    }
    
    // Auto-inicializar si estamos en el editor
    $(document).ready(function() {
        if ($('#wpto_seo_title').length) {
            wptoInitSEOEditor();
        }
        initHeadingPanel();
        initBulkSEO();
        const fileMonitorUI = initFileMonitorModal();
        
        // Exportar configuración
        $('#wpto-export-config').on('click', function() {
            $.ajax({
                url: wptoAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpto_export_config',
                    nonce: wptoAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const dataStr = JSON.stringify(response.data, null, 2);
                        const dataBlob = new Blob([dataStr], {type: 'application/json'});
                        const url = URL.createObjectURL(dataBlob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'wpto-config-' + new Date().toISOString().split('T')[0] + '.json';
                        link.click();
                        URL.revokeObjectURL(url);
                        
                        $('#wpto-config-status').html('<span style="color: #00a32a;">✓ Configuración exportada correctamente</span>');
                        setTimeout(function() {
                            $('#wpto-config-status').html('');
                        }, 3000);
                    }
                }
            });
        });

        // Ejecutar escaneo de archivos
        $('#wpto-file-monitor-run').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $status = $('#wpto-file-monitor-run-status');
            $btn.prop('disabled', true);
            $status.text('Escaneando...');

            $.ajax({
                url: wptoAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpto_run_file_scan',
                    nonce: wptoAdmin.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        $status.text('Listo');
                        const $box = $('.wpto-file-monitor-status');
                        if ($box.length) {
                            const lastScan = data.last_scan ? data.last_scan : 'Nunca';
                            const count = data.count ? data.count : 0;
                            const status = data.last_status ? data.last_status : 'n/a';
                            const summary = data.last_summary ? data.last_summary : '';
                            $box.html('<strong>Último escaneo:</strong> ' + lastScan + '<br>' +
                                      '<strong>Archivos monitorizados:</strong> ' + count + '<br>' +
                                      '<strong>Estado:</strong> ' + status + '<br>' +
                                      '<strong>Resumen:</strong> ' + summary);
                            $box.attr('data-last-changes', JSON.stringify(data.last_changes || {}));
                        }
                        if (fileMonitorUI) {
                            fileMonitorUI.setChanges(data.last_changes || null);
                            if (fileMonitorUI.hasChanges()) {
                                fileMonitorUI.open();
                            }
                        }
                    } else {
                        $status.text('Error');
                    }
                },
                error: function() {
                    $status.text('Error');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
        
        // Importar configuración
        $('#wpto-import-config').on('click', function() {
            $('#wpto-import-file').click();
        });
        
        $('#wpto-import-file').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            if (!confirm('¿Estás seguro de importar esta configuración? Se sobrescribirán las configuraciones actuales.')) {
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const config = JSON.parse(e.target.result);
                    
                    $.ajax({
                        url: wptoAdmin.ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'wpto_import_config',
                            nonce: wptoAdmin.nonce,
                            config_json: JSON.stringify(config)
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#wpto-config-status').html('<span style="color: #00a32a;">✓ Configuración importada correctamente. Recargando...</span>');
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                $('#wpto-config-status').html('<span style="color: #d63638;">✗ Error: ' + (response.data || 'Error desconocido') + '</span>');
                            }
                        },
                        error: function() {
                            $('#wpto-config-status').html('<span style="color: #d63638;">✗ Error al importar la configuración</span>');
                        }
                    });
                } catch (error) {
                    $('#wpto-config-status').html('<span style="color: #d63638;">✗ Error: Archivo JSON inválido</span>');
                }
            };
            reader.readAsText(file);
        });
        
        // Resetear configuración
        $('#wpto-reset-config').on('click', function() {
            if (!confirm('¿Estás SEGURO de resetear toda la configuración? Esta acción no se puede deshacer.')) {
                return;
            }
            
            if (!confirm('Última confirmación: ¿Resetear TODA la configuración?')) {
                return;
            }
            
            $.ajax({
                url: wptoAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpto_reset_config',
                    nonce: wptoAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#wpto-config-status').html('<span style="color: #00a32a;">✓ Configuración reseteada. Recargando...</span>');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#wpto-config-status').html('<span style="color: #d63638;">✗ Error: ' + (response.data || 'Error desconocido') + '</span>');
                    }
                },
                error: function() {
                    $('#wpto-config-status').html('<span style="color: #d63638;">✗ Error al resetear la configuración</span>');
                }
            });
        });
    });
    
})(jQuery);
