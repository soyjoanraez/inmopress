(function ($) {
    'use strict';

    // Función para inicializar el bloque
    var initializeBlock = function ($block) {
        // En Fase 1 el buscador funciona por GET estándar a la página de archivo
        // pero preparamos la estructura para AJAX futuro
        $block.find('form').on('submit', function (e) {
            // Validaciones si fuesen necesarias
        });
    };

    // Inicializar en Guternberg y Frontend
    if (window.acf) {
        window.acf.addAction('render_block_preview/type=buscador-inmuebles', initializeBlock);
    }

    $(function () {
        $('.inmopress-search-block').each(function () {
            initializeBlock($(this));
        });
    });

})(jQuery);
