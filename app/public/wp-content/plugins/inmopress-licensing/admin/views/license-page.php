<?php
if (!defined('ABSPATH')) {
    exit;
}

$license_manager = Inmopress_License_Manager::get_instance();
$license_data = $license_manager->get_license_data();
$feature_manager = Inmopress_Feature_Manager::get_instance();
$current_plan = $license_manager->get_current_plan();

// Obtener límites del plan (usando reflexión para acceder a propiedad privada)
$reflection = new ReflectionClass($feature_manager);
$property = $reflection->getProperty('plan_limits');
$property->setAccessible(true);
$all_limits = $property->getValue($feature_manager);
$limits = $all_limits[$current_plan] ?? $all_limits['starter'];
?>
<div class="wrap">
    <h1>Gestión de Licencia Inmopress</h1>

    <?php if ($license_data['status'] === Inmopress_License_Manager::STATUS_ACTIVE): ?>
        <div class="notice notice-success">
            <p><strong>✅ Licencia Activa</strong> - Plan: <?php echo esc_html(ucfirst($license_data['plan'])); ?></p>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>Información de la Licencia</h2>
            <table class="form-table">
                <tr>
                    <th>Estado</th>
                    <td><strong style="color: green;">Activa</strong></td>
                </tr>
                <tr>
                    <th>Plan</th>
                    <td><?php echo esc_html(ucfirst($license_data['plan'])); ?></td>
                </tr>
                <tr>
                    <th>Clave de Licencia</th>
                    <td><code><?php echo esc_html(substr($license_data['license_key'], 0, 20) . '...'); ?></code></td>
                </tr>
                <?php if (!empty($license_data['expires_at'])): ?>
                    <tr>
                        <th>Expira</th>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($license_data['expires_at']))); ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th>Última Validación</th>
                    <td><?php echo $license_data['last_validated'] ? esc_html($license_data['last_validated']) : 'Nunca'; ?></td>
                </tr>
            </table>

            <h3 style="margin-top: 30px;">Límites de tu Plan</h3>
            <ul>
                <li><strong>Propiedades:</strong> <?php echo $limits['max_properties'] === -1 ? 'Ilimitado' : $limits['max_properties']; ?></li>
                <li><strong>Clientes:</strong> <?php echo $limits['max_clients'] === -1 ? 'Ilimitado' : $limits['max_clients']; ?></li>
                <li><strong>Agentes:</strong> <?php echo $limits['max_agents']; ?></li>
                <li><strong>Generaciones IA/mes:</strong> <?php echo $limits['ai_generations_per_month'] === 0 ? 'No disponible' : $limits['ai_generations_per_month']; ?></li>
            </ul>

            <h3 style="margin-top: 30px;">Features Disponibles</h3>
            <ul>
                <?php foreach ($limits['features'] as $feature): ?>
                    <li>✅ <?php echo esc_html(ucfirst($feature)); ?></li>
                <?php endforeach; ?>
            </ul>

            <p style="margin-top: 30px;">
                <button type="button" class="button button-secondary" id="deactivate-license">Desactivar Licencia</button>
            </p>
        </div>

    <?php else: ?>
        <div class="notice notice-warning">
            <p><strong>⚠️ No hay licencia activa</strong></p>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>Activar Licencia</h2>
            <p>Introduce tu clave de licencia para activar Inmopress.</p>

            <form id="activate-license-form">
                <?php wp_nonce_field('inmopress_license_nonce', 'nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="license_key">Clave de Licencia</label></th>
                        <td>
                            <input type="text" id="license_key" name="license_key" class="regular-text" placeholder="XXXX-XXXX-XXXX-XXXX" required>
                            <p class="description">Puedes obtener tu clave de licencia desde tu cuenta en inmopress.com</p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">Activar Licencia</button>
                </p>
            </form>

            <div id="activation-result" style="display: none; margin-top: 20px;"></div>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>Planes Disponibles</h2>
            <table class="wp-list-table widefat">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Propiedades</th>
                        <th>Clientes</th>
                        <th>Agentes</th>
                        <th>IA/mes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Starter</strong></td>
                        <td>50</td>
                        <td>100</td>
                        <td>1</td>
                        <td>—</td>
                    </tr>
                    <tr>
                        <td><strong>Pro</strong></td>
                        <td>500</td>
                        <td>1,000</td>
                        <td>5</td>
                        <td>—</td>
                    </tr>
                    <tr>
                        <td><strong>Pro+AI</strong></td>
                        <td>500</td>
                        <td>1,000</td>
                        <td>5</td>
                        <td>500</td>
                    </tr>
                    <tr>
                        <td><strong>Agency</strong></td>
                        <td>∞</td>
                        <td>∞</td>
                        <td>20</td>
                        <td>2,000</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('#activate-license-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $result = $('#activation-result');
        var licenseKey = $('#license_key').val();

        $result.hide().html('<p>Activando licencia...</p>').show();

        $.post(ajaxurl, {
            action: 'inmopress_activate_license',
            license_key: licenseKey,
            nonce: '<?php echo wp_create_nonce('inmopress_license_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $result.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                $result.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
            }
        });
    });

    $('#deactivate-license').on('click', function() {
        if (!confirm('¿Desactivar la licencia? Esto deshabilitará las funcionalidades premium.')) {
            return;
        }

        $.post(ajaxurl, {
            action: 'inmopress_deactivate_license',
            nonce: '<?php echo wp_create_nonce('inmopress_license_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data.message);
            }
        });
    });
});
</script>
