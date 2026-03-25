<?php
/**
 * Ubicación y Mapa Block Template.
 */

$id = 'inmopress-location-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'inmopress-location';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Get location data
global $post;
$address = '';
$city = '';
$province = '';
$postcode = '';
$latitude = '';
$longitude = '';

if ($post && $post->post_type === 'impress_property') {
    $address = get_field('impress_property_address', $post->ID) ?: '';
    $city = get_field('impress_property_city', $post->ID) ?: '';
    $province = get_field('impress_property_province', $post->ID) ?: '';
    $postcode = get_field('impress_property_postcode', $post->ID) ?: '';
    $latitude = get_field('impress_property_latitude', $post->ID) ?: '';
    $longitude = get_field('impress_property_longitude', $post->ID) ?: '';
}

// Override with block fields if set
$block_address = get_field('direccion');
$block_city = get_field('ciudad');
$block_lat = get_field('latitud');
$block_lng = get_field('longitud');

if ($block_address) $address = $block_address;
if ($block_city) $city = $block_city;
if ($block_lat) $latitude = $block_lat;
if ($block_lng) $longitude = $block_lng;

// Build full address
$full_address = trim(implode(', ', array_filter(array($address, $city, $province, $postcode))));

// Map provider (Google Maps, OpenStreetMap, etc.)
$map_provider = get_field('map_provider') ?: 'google'; // google, osm
$map_height = get_field('map_height') ?: 400;

// Google Maps API Key (should be set in theme options or ACF options)
$google_maps_api_key = get_option('inmopress_google_maps_api_key') ?: '';

?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <?php if ($full_address): ?>
        <div class="inmopress-location-info">
            <h3 class="inmopress-location-title">Ubicación</h3>
            <div class="inmopress-location-address">
                <span class="dashicons dashicons-location"></span>
                <span><?php echo esc_html($full_address); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($latitude && $longitude): ?>
        <div class="inmopress-location-map" style="height: <?php echo esc_attr($map_height); ?>px;">
            <?php if ($map_provider === 'google' && $google_maps_api_key): ?>
                <iframe
                    width="100%"
                    height="100%"
                    style="border:0"
                    loading="lazy"
                    allowfullscreen
                    src="https://www.google.com/maps/embed/v1/place?key=<?php echo esc_attr($google_maps_api_key); ?>&q=<?php echo esc_attr(urlencode($latitude . ',' . $longitude)); ?>">
                </iframe>
            <?php else: ?>
                <!-- OpenStreetMap fallback -->
                <div id="inmopress-osm-map-<?php echo esc_attr($block['id']); ?>" 
                     class="inmopress-osm-map"
                     data-lat="<?php echo esc_attr($latitude); ?>"
                     data-lng="<?php echo esc_attr($longitude); ?>"
                     style="width: 100%; height: 100%;">
                </div>
                <script>
                (function() {
                    var mapDiv = document.getElementById('inmopress-osm-map-<?php echo esc_attr($block['id']); ?>');
                    if (mapDiv && typeof L !== 'undefined') {
                        var lat = parseFloat(mapDiv.dataset.lat);
                        var lng = parseFloat(mapDiv.dataset.lng);
                        var map = L.map(mapDiv).setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);
                        L.marker([lat, lng]).addTo(map);
                    }
                })();
                </script>
            <?php endif; ?>
        </div>
    <?php elseif ($full_address): ?>
        <div class="inmopress-location-map" style="height: <?php echo esc_attr($map_height); ?>px;">
            <?php if ($map_provider === 'google' && $google_maps_api_key): ?>
                <iframe
                    width="100%"
                    height="100%"
                    style="border:0"
                    loading="lazy"
                    allowfullscreen
                    src="https://www.google.com/maps/embed/v1/place?key=<?php echo esc_attr($google_maps_api_key); ?>&q=<?php echo esc_attr(urlencode($full_address)); ?>">
                </iframe>
            <?php else: ?>
                <p class="inmopress-map-placeholder">Mapa no disponible. Configure las coordenadas o la clave de API de Google Maps.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
