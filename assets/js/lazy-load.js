/**
 * Lazy Loading de imágenes
 */
(function() {
    'use strict';

    // Verificar si IntersectionObserver está disponible
    if (!('IntersectionObserver' in window)) {
        // Fallback: cargar todas las imágenes inmediatamente
        document.addEventListener('DOMContentLoaded', function() {
            var lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(function(img) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            });
        });
        return;
    }

    // Configurar IntersectionObserver
    var imageObserver = new IntersectionObserver(function(entries, observer) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var img = entry.target;
                
                // Cargar imagen
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    
                    // Añadir clase cuando se carga
                    img.addEventListener('load', function() {
                        img.classList.add('loaded');
                    });
                    
                    // Manejar errores
                    img.addEventListener('error', function() {
                        img.classList.add('error');
                    });
                }
                
                // Dejar de observar esta imagen
                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px', // Cargar 50px antes de que sea visible
        threshold: 0.01
    });

    // Observar todas las imágenes con data-src
    document.addEventListener('DOMContentLoaded', function() {
        var lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(function(img) {
            imageObserver.observe(img);
        });
    });

    // Observar imágenes añadidas dinámicamente
    var mutationObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    if (node.tagName === 'IMG' && node.dataset.src) {
                        imageObserver.observe(node);
                    } else {
                        var lazyImages = node.querySelectorAll && node.querySelectorAll('img[data-src]');
                        if (lazyImages) {
                            lazyImages.forEach(function(img) {
                                imageObserver.observe(img);
                            });
                        }
                    }
                }
            });
        });
    });

    mutationObserver.observe(document.body, {
        childList: true,
        subtree: true
    });
})();
