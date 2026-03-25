<?php
/**
 * Mapa Interactivo Block Template.
 */

$id = 'inmopress-interactive-map-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'inmopress-interactive-map';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Map options
$map_height = get_field('map_height') ?: 600;
$default_zoom = get_field('zoom') ?: 12;
$default_lat = get_field('latitud_centro') ?: 40.4168;
$default_lng = get_field('longitud_centro') ?: -3.7038;
$show_cluster = get_field('agrupar_marcadores') !== false;
$show_popup = get_field('mostrar_popup') !== false;

// Get properties to show
$properties_query = get_field('propiedades');
$properties = array();

if ($properties_query === 'all' || empty($properties_query)) {
    // Get all published properties
    $args = array(
        'post_type' => 'impress_property',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);
    $properties = $query->posts;
} elseif ($properties_query === 'current') {
    // Current property if on single property page
    global $post;
    if ($post && $post->post_type === 'impress_property') {
        $properties = array($post);
    }
} elseif (is_array($properties_query)) {
    // Specific properties selected
    $properties = $properties_query;
}

// Google Maps API Key
$google_maps_api_key = get_option('inmopress_google_maps_api_key') ?: '';

// Prepare markers data
$markers = array();
foreach ($properties as $property) {
    $lat = get_field('impress_property_latitude', $property->ID);
    $lng = get_field('impress_property_longitude', $property->ID);
    
    if ($lat && $lng) {
        $markers[] = array(
            'id' => $property->ID,
            'title' => get_the_title($property->ID),
            'lat' => floatval($lat),
            'lng' => floatval($lng),
            'price' => get_field('impress_property_price', $property->ID),
            'operation' => get_field('impress_property_operation', $property->ID),
            'url' => get_permalink($property->ID),
            'thumbnail' => get_the_post_thumbnail_url($property->ID, 'thumbnail'),
        );
    }
}

?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
    
    <div class="inmopress-map-container" 
         id="inmopress-map-<?php echo esc_attr($block['id']); ?>"
         style="height: <?php echo esc_attr($map_height); ?>px;"
         data-lat="<?php echo esc_attr($default_lat); ?>"
         data-lng="<?php echo esc_attr($default_lng); ?>"
         data-zoom="<?php echo esc_attr($default_zoom); ?>"
         data-cluster="<?php echo $show_cluster ? 'true' : 'false'; ?>"
         data-markers='<?php echo json_encode($markers); ?>'>
    </div>

    <?php if ($google_maps_api_key): ?>
        <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($google_maps_api_key); ?>&libraries=markerclusterer"></script>
    <?php endif; ?>

    <script>
    (function() {
        var mapContainer = document.getElementById('inmopress-map-<?php echo esc_js($block['id']); ?>');
        if (!mapContainer) return;

        var lat = parseFloat(mapContainer.dataset.lat);
        var lng = parseFloat(mapContainer.dataset.lng);
        var zoom = parseInt(mapContainer.dataset.zoom);
        var markers = JSON.parse(mapContainer.dataset.markers || '[]');
        var useCluster = mapContainer.dataset.cluster === 'true';

        // Use Google Maps if available
        if (typeof google !== 'undefined' && google.maps) {
            var map = new google.maps.Map(mapContainer, {
                center: { lat: lat, lng: lng },
                zoom: zoom,
            });

            var mapMarkers = [];
            markers.forEach(function(markerData) {
                var marker = new google.maps.Marker({
                    position: { lat: markerData.lat, lng: markerData.lng },
                    map: map,
                    title: markerData.title,
                });

                <?php if ($show_popup): ?>
                var infoWindow = new google.maps.InfoWindow({
                    content: '<div class="inmopress-map-popup">' +
                             '<h4><a href="' + markerData.url + '">' + markerData.title + '</a></h4>' +
                             (markerData.price ? '<p class="price">' + markerData.price + ' €</p>' : '') +
                             '</div>'
                });

                marker.addListener('click', function() {
                    infoWindow.open(map, marker);
                });
                <?php endif; ?>

                mapMarkers.push(marker);
            });

            if (useCluster && typeof MarkerClusterer !== 'undefined') {
                new MarkerClusterer(map, mapMarkers, {
                    imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
                });
            }
        } else if (typeof L !== 'undefined') {
            // Fallback to Leaflet/OpenStreetMap
            var map = L.map(mapContainer).setView([lat, lng], zoom);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            markers.forEach(function(markerData) {
                var marker = L.marker([markerData.lat, markerData.lng]).addTo(map);
                
                <?php if ($show_popup): ?>
                marker.bindPopup('<div class="inmopress-map-popup">' +
                                '<h4><a href="' + markerData.url + '">' + markerData.title + '</a></h4>' +
                                (markerData.price ? '<p class="price">' + markerData.price + ' €</p>' : '') +
                                '</div>');
                <?php endif; ?>
            });
        } else {
            mapContainer.innerHTML = '<p style="padding: 2rem; text-align: center; color: #666;">Mapa no disponible. Configure la clave de API de Google Maps o instale Leaflet.</p>';
        }
    })();
    </script>

</div>
