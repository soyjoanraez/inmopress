<?php
/**
 * Mapa Inmuebles Block Template.
 */

$id = 'mapa-inmueble-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'inmopress-map';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Get Location Data from ACF
$lat = get_field('latitud');
$lng = get_field('longitud');

// Fallback if ACF field names are different (checking 'coordenades' or Google Maps field)
if (empty($lat) || empty($lng)) {
    $location = get_field('mapa'); // Standard Google Maps Field
    if ($location) {
        $lat = $location['lat'];
        $lng = $location['lng'];
    }
}

// Enqueue Leaflet Assets (CDN for speed/simplicity, can be local)
wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);
?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
    <?php if ($lat && $lng): ?>
        <div id="map-<?php echo esc_attr($block['id']); ?>" class="inmo-map-container"
            data-lat="<?php echo esc_attr($lat); ?>" data-lng="<?php echo esc_attr($lng); ?>"
            style="height: 400px; width: 100%; border-radius: 8px;"></div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var mapId = 'map-<?php echo esc_js($block['id']); ?>';
                var lat = <?php echo floatval($lat); ?>;
                var lng = <?php echo floatval($lng); ?>;

                if (typeof L !== 'undefined') {
                    var map = L.map(mapId).setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(map);

                    L.marker([lat, lng]).addTo(map)
                        .bindPopup('<?php echo esc_js(get_the_title()); ?>')
                        .openPopup();
                }
            });
        </script>
    <?php else: ?>
        <div class="map-placeholder" style="background: #f3f4f6; padding: 40px; text-align: center; border-radius: 8px;">
            <p>📍 Ubicación no disponible</p>
            <?php if (is_admin()): ?>
                <small>Asegúrate de rellenar los campos de Latitud/Longitud o Mapa en el inmueble.</small>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>