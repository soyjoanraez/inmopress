<?php
/**
 * Script CLI para crear las páginas del panel frontend
 * Ejecutar desde: php crear-paginas-panel.php
 */

// Configurar WordPress para CLI
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

// Cargar clase
require_once __DIR__ . '/wp-content/plugins/inmopress-frontend/includes/class-page-generator.php';

echo "==============================================\n";
echo "  CREAR PÁGINAS DEL PANEL FRONTEND\n";
echo "==============================================\n\n";

// Ejecutar creación
$created = Inmopress_Page_Generator::create_all_pages();

if ($created > 0) {
    echo "✅ ÉXITO: Se crearon $created página(s) correctamente.\n\n";

    echo "Páginas creadas:\n";
    echo "  1. Mi Panel (/mi-panel/)\n";
    echo "  2. Inmuebles (/mi-panel/inmuebles/)\n";
    echo "  3. Nuevo Inmueble (/mi-panel/nuevo-inmueble/)\n";
    echo "  4. Editar Inmueble (/mi-panel/editar-inmueble/)\n";
    echo "  5. Clientes (/mi-panel/clientes/)\n";
    echo "  6. Nuevo Cliente (/mi-panel/nuevo-cliente/)\n";
    echo "  7. Editar Cliente (/mi-panel/editar-cliente/)\n";
    echo "  8. Visitas (/mi-panel/visitas/)\n";
    echo "  9. Nueva Visita (/mi-panel/nueva-visita/)\n";
    echo "  10. Propietarios (/mi-panel/propietarios/)\n";
    echo "  11. Nuevo Propietario (/mi-panel/nuevo-propietario/)\n\n";

    echo "Próximos pasos:\n";
    echo "  • Visita: http://inmopress.local/mi-panel/\n";
    echo "  • Verifica el dashboard con KPIs\n";
    echo "  • Prueba crear inmuebles, clientes y visitas\n\n";
} elseif ($created === 0) {
    echo "ℹ️  INFO: Todas las páginas ya existían. No se creó ninguna página nueva.\n\n";
    echo "Las páginas del panel ya están disponibles en:\n";
    echo "  • http://inmopress.local/mi-panel/\n\n";
} else {
    echo "❌ ERROR: Hubo un problema al crear las páginas.\n\n";
}

echo "==============================================\n";
echo "Puedes eliminar este script después de ejecutarlo.\n";
echo "==============================================\n";
