<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Configuración de API</h1>

    <form method="post" action="">
        <?php wp_nonce_field('inmopress_api_settings'); ?>

        <table class="form-table">
            <tr>
                <th><label>Habilitar API</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="api_enabled" value="1" <?php checked($api_enabled, 1); ?>>
                        Activar API REST
                    </label>
                </td>
            </tr>
            <tr>
                <th><label>JWT Secret</label></th>
                <td>
                    <input type="text" name="jwt_secret" value="<?php echo esc_attr($jwt_secret); ?>" class="large-text" readonly>
                    <p class="description">Clave secreta para firmar tokens JWT. Se genera automáticamente.</p>
                </td>
            </tr>
            <tr>
                <th><label>Rate Limit</label></th>
                <td>
                    <input type="number" name="rate_limit" value="<?php echo esc_attr($rate_limit); ?>" class="small-text" min="1">
                    <p class="description">Número máximo de peticiones por hora por usuario.</p>
                </td>
            </tr>
        </table>

        <h2>Endpoints Disponibles</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Método</th>
                    <th>Endpoint</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>POST</code></td>
                    <td><code>/wp-json/inmopress/v1/auth/login</code></td>
                    <td>Autenticación y obtención de token</td>
                </tr>
                <tr>
                    <td><code>GET</code></td>
                    <td><code>/wp-json/inmopress/v1/auth/me</code></td>
                    <td>Información del usuario autenticado</td>
                </tr>
                <tr>
                    <td><code>GET</code></td>
                    <td><code>/wp-json/inmopress/v1/properties</code></td>
                    <td>Listar propiedades</td>
                </tr>
                <tr>
                    <td><code>GET</code></td>
                    <td><code>/wp-json/inmopress/v1/properties/{id}</code></td>
                    <td>Obtener propiedad</td>
                </tr>
                <tr>
                    <td><code>POST</code></td>
                    <td><code>/wp-json/inmopress/v1/properties</code></td>
                    <td>Crear propiedad</td>
                </tr>
                <tr>
                    <td><code>PUT</code></td>
                    <td><code>/wp-json/inmopress/v1/properties/{id}</code></td>
                    <td>Actualizar propiedad</td>
                </tr>
                <tr>
                    <td><code>DELETE</code></td>
                    <td><code>/wp-json/inmopress/v1/properties/{id}</code></td>
                    <td>Eliminar propiedad</td>
                </tr>
                <tr>
                    <td><code>GET</code></td>
                    <td><code>/wp-json/inmopress/v1/clients</code></td>
                    <td>Listar clientes</td>
                </tr>
                <tr>
                    <td><code>GET</code></td>
                    <td><code>/wp-json/inmopress/v1/leads</code></td>
                    <td>Listar leads</td>
                </tr>
                <tr>
                    <td><code>POST</code></td>
                    <td><code>/wp-json/inmopress/v1/leads</code></td>
                    <td>Crear lead (público)</td>
                </tr>
                <tr>
                    <td><code>GET</code></td>
                    <td><code>/wp-json/inmopress/v1/matching/property/{id}</code></td>
                    <td>Obtener matches de una propiedad</td>
                </tr>
                <tr>
                    <td><code>GET</code></td>
                    <td><code>/wp-json/inmopress/v1/matching/client/{id}</code></td>
                    <td>Obtener matches de un cliente</td>
                </tr>
            </tbody>
        </table>

        <h2>Documentación</h2>
        <p>Para más información sobre la API, consulta la documentación completa en <code>/wp-content/plugins/inmopress-api/README.md</code></p>

        <p class="submit">
            <button type="submit" name="save_api_settings" class="button button-primary">Guardar Configuración</button>
        </p>
    </form>
</div>
