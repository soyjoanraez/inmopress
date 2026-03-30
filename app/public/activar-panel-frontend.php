<?php
/**
 * Script para reactivar el plugin Inmopress Frontend
 * y generar las 11 páginas del panel
 *
 * INSTRUCCIONES:
 * 1. Visita: http://inmopress.local/activar-panel-frontend.php (o tu URL local)
 * 2. El script creará automáticamente las 11 páginas
 * 3. ELIMINA este archivo después de ejecutarlo
 */

// Cargar WordPress
require_once __DIR__ . '/wp-load.php';

// Verificar permisos de admin
if (!current_user_can('manage_options')) {
    wp_die('Debes ser administrador para ejecutar este script.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Activar Panel Frontend - Inmopress</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f3f4f6;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1e3a8a;
            margin-top: 0;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .pages-list {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .pages-list ul {
            list-style: none;
            padding: 0;
        }
        .pages-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .pages-list li:last-child {
            border-bottom: none;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #1e3a8a;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 10px 10px 0;
        }
        .button:hover {
            background: #1e40af;
        }
        .button-secondary {
            background: #6b7280;
        }
        .button-secondary:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Activar Panel Frontend - Inmopress</h1>

        <?php
        // Cargar clase Page Generator
        require_once __DIR__ . '/wp-content/plugins/inmopress-frontend/includes/class-page-generator.php';

        // Ejecutar creación de páginas
        $created = Inmopress_Page_Generator::create_all_pages();

        if ($created > 0):
        ?>
            <div class="success">
                <h2>✅ ¡Páginas creadas exitosamente!</h2>
                <p>Se han creado <strong><?php echo $created; ?> página(s)</strong> del panel frontend.</p>
            </div>

            <div class="pages-list">
                <h3>Páginas creadas:</h3>
                <ul>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/'); ?>" target="_blank">Mi Panel</a> - Dashboard principal</li>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/inmuebles/'); ?>" target="_blank">Inmuebles</a> - Listado</li>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/nuevo-inmueble/'); ?>" target="_blank">Nuevo Inmueble</a></li>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/editar-inmueble/'); ?>" target="_blank">Editar Inmueble</a></li>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/clientes/'); ?>" target="_blank">Clientes</a> - Listado</li>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/nuevo-cliente/'); ?>" target="_blank">Nuevo Cliente</a></li>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/editar-cliente/'); ?>" target="_blank">Editar Cliente</a></li>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/visitas/'); ?>" target="_blank">Visitas</a> - Listado</li>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/nueva-visita/'); ?>" target="_blank">Nueva Visita</a></li>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/propietarios/'); ?>" target="_blank">Propietarios</a> - Listado</li>
                    <li>✓ <a href="<?php echo home_url('/mi-panel/nuevo-propietario/'); ?>" target="_blank">Nuevo Propietario</a></li>
                </ul>
            </div>

            <div class="info">
                <h3>📝 Próximos pasos:</h3>
                <ol>
                    <li>Visita <strong><a href="<?php echo home_url('/mi-panel/'); ?>" target="_blank">/mi-panel/</a></strong> para ver el dashboard</li>
                    <li>Verifica que los shortcodes funcionan correctamente</li>
                    <li>Crea algunos inmuebles, clientes y visitas de prueba</li>
                    <li><strong>ELIMINA este archivo (activar-panel-frontend.php)</strong> por seguridad</li>
                </ol>
            </div>

        <?php elseif ($created === 0): ?>
            <div class="info">
                <h2>ℹ️ Las páginas ya existen</h2>
                <p>Todas las páginas del panel ya fueron creadas anteriormente. No se creó ninguna página nueva.</p>
            </div>

            <div class="pages-list">
                <h3>Páginas existentes:</h3>
                <ul>
                    <li><a href="<?php echo home_url('/mi-panel/'); ?>" target="_blank">Mi Panel</a></li>
                    <li><a href="<?php echo home_url('/mi-panel/inmuebles/'); ?>" target="_blank">Inmuebles</a></li>
                    <li><a href="<?php echo home_url('/mi-panel/clientes/'); ?>" target="_blank">Clientes</a></li>
                    <li><a href="<?php echo home_url('/mi-panel/visitas/'); ?>" target="_blank">Visitas</a></li>
                    <li><a href="<?php echo home_url('/mi-panel/propietarios/'); ?>" target="_blank">Propietarios</a></li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="warning">
            <h3>⚠️ Importante - Seguridad</h3>
            <p><strong>Elimina este archivo inmediatamente</strong> después de ejecutarlo para evitar riesgos de seguridad.</p>
            <p>Ruta del archivo: <code>/activar-panel-frontend.php</code></p>
        </div>

        <div style="margin-top: 30px;">
            <a href="<?php echo home_url('/mi-panel/'); ?>" class="button">Ver Panel Frontend</a>
            <a href="<?php echo admin_url('edit.php?post_type=page'); ?>" class="button button-secondary">Ver Páginas en Admin</a>
        </div>
    </div>
</body>
</html>
