<?php
if (!defined('ABSPATH')) exit;

class Inmopress_Kyero_Importer {
    
    private static $log_file = 'inmopress_kyero_import.log';
    private static $option_name = 'inmopress_kyero_settings';
    
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('wp_ajax_inmopress_import_kyero', array(__CLASS__, 'handle_ajax_import'));
        add_action('wp_ajax_inmopress_delete_kyero_import', array(__CLASS__, 'handle_ajax_delete'));
        add_action('wp_ajax_inmopress_get_kyero_log', array(__CLASS__, 'handle_ajax_get_log'));
    }
    
    public static function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=impress_property',
            'Importar Kyero',
            'Importar Kyero',
            'manage_options',
            'inmopress-kyero-import',
            array(__CLASS__, 'render_admin_page')
        );
    }
    
    public static function render_admin_page() {
        $settings = get_option(self::$option_name, array('feed_url' => ''));
        ?>
        <div class="wrap">
            <h1>🏠 Importador Kyero XML</h1>
            
            <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
                <h2>Configuración de Importación</h2>
                <p>Introduce la URL del feed XML de Kyero (v3) para comenzar la importación.</p>
                
                <form id="kyero-import-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="feed_url">URL del Feed XML</label></th>
                            <td>
                                <input type="url" name="feed_url" id="feed_url" class="regular-text" 
                                       placeholder="https://ejemplo.com/kyero-feed.xml" 
                                       value="<?php echo esc_attr($settings['feed_url']); ?>" 
                                       required style="width: 100%;">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Opciones</th>
                            <td>
                                <label><input type="checkbox" name="update_existing" value="1" checked> Actualizar existentes (coincidencia por referencia)</label><br>
                                <label style="margin-left: 20px; color: #666;"><input type="checkbox" name="smart_update" value="1"> <strong>Smart Update:</strong> Solo rellenar campos vacíos (mantiene cambios manuales)</label><br>
                                <label><input type="checkbox" name="import_images" value="1" checked> Importar Imágenes (Primera destacada, resto galería)</label>
                            </td>
                        </tr>
                    </table>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" class="button button-primary button-hero" id="start-import-btn">
                            🚀 Iniciar Importación
                        </button>
                        <span class="spinner" style="float: none; margin-left: 10px;"></span>
                    </div>
                </form>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
                <h3>📜 Registro de Importación (Log)</h3>
                <div id="import-log" style="background: #f0f0f1; border: 1px solid #ccd0d4; padding: 15px; height: 300px; overflow-y: auto;">
                    <div id="log-content" style="font-family: monospace; white-space: pre-wrap; font-size: 12px;">Cargando log anterior...</div>
                </div>
                <p class="description">Este registro se guarda automáticamente y muestra la actividad de la última importación.</p>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px; border-left: 4px solid #dc3232;">
                <h3>⚠️ Zona de Peligro</h3>
                <p>Esta acción eliminará <strong>todos</strong> los inmuebles que hayan sido marcados como importados desde Kyero.</p>
                <button type="button" class="button button-link-delete" id="delete-import-btn">
                    🗑️ Borrar toda la importación
                </button>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                // Load existing log
                loadLog();
                
                function loadLog() {
                    $.get(ajaxurl, { action: 'inmopress_get_kyero_log', nonce: '<?php echo wp_create_nonce("inmopress_kyero_log"); ?>' }, function(response) {
                        if(response.success) {
                            $('#log-content').text(response.data.log || 'Sin actividad reciente.');
                            var logDiv = document.getElementById("import-log");
                            logDiv.scrollTop = logDiv.scrollHeight;
                        }
                    });
                }
                
                // IMPORT HANDLER
                $('#kyero-import-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    var feedUrl = $('#feed_url').val();
                    var updateExisting = $('input[name="update_existing"]').is(':checked');
                    var smartUpdate = $('input[name="smart_update"]').is(':checked');
                    var importImages = $('input[name="import_images"]').is(':checked');
                    
                    if (!feedUrl) return alert('URL requerida');
                    
                    $('#start-import-btn').prop('disabled', true);
                    $('.spinner').addClass('is-active');
                    $('#log-content').text('Iniciando nueva importación...\n');
                    
                    processImport(feedUrl, updateExisting, smartUpdate, importImages, 0);
                    
                    function processImport(url, update, smart, images, offset) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'inmopress_import_kyero',
                                feed_url: url,
                                update_existing: update ? 1 : 0,
                                smart_update: smart ? 1 : 0,
                                import_images: images ? 1 : 0,
                                offset: offset,
                                nonce: '<?php echo wp_create_nonce("inmopress_kyero_import"); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#log-content').append(response.data.message + '\n');
                                    var logDiv = document.getElementById("import-log");
                                    logDiv.scrollTop = logDiv.scrollHeight;
                                    
                                    if (!response.data.completed) {
                                        processImport(url, update, smart, images, response.data.next_offset);
                                    } else {
                                        $('#log-content').append('✅ FIN.\n');
                                        resetUI();
                                    }
                                } else {
                                    $('#log-content').append('❌ ERROR: ' + (response.data ? response.data.message : '?') + '\n');
                                    resetUI();
                                }
                            },
                            error: function() {
                                $('#log-content').append('❌ Error de conexión.\n');
                                resetUI();
                            }
                        });
                    }
                    
                    function resetUI() {
                        $('#start-import-btn').prop('disabled', false);
                        $('.spinner').removeClass('is-active');
                    }
                });
                
                // DELETE HANDLER
                $('#delete-import-btn').on('click', function() {
                    if (!confirm('¿BORRAR TODO? Acción irreversible.')) return;
                    
                    var $btn = $(this);
                    $btn.prop('disabled', true);
                    $('#log-content').text('🗑️ Iniciando borrado...\n');
                    
                    processDelete();
                    
                    function processDelete() {
                        $.post(ajaxurl, { 
                            action: 'inmopress_delete_kyero_import',
                            nonce: '<?php echo wp_create_nonce("inmopress_kyero_import"); ?>'
                        }, function(response) {
                            if (response.success) {
                                $('#log-content').append(response.data.message + '\n');
                                if (!response.data.completed) {
                                    processDelete();
                                } else {
                                    $('#log-content').append('✅ Borrado completado.\n');
                                    $btn.prop('disabled', false);
                                }
                            } else {
                                $('#log-content').append('❌ Error al borrar.\n');
                                $btn.prop('disabled', false);
                            }
                        });
                    }
                });
            });
            </script>
        </div>
        <?php
    }
    
    // --- AJAX HANDLERS ---
    
    public static function handle_ajax_get_log() {
        check_ajax_referer('inmopress_kyero_log', 'nonce');
        $log = self::get_log_content();
        wp_send_json_success(array('log' => $log));
    }
    
    public static function handle_ajax_import() {
        if (!current_user_can('manage_options')) wp_send_json_error();
        
        $url = esc_url_raw($_POST['feed_url']);
        $offset = intval($_POST['offset']);
        $update = filter_var($_POST['update_existing'], FILTER_VALIDATE_BOOLEAN);
        $smart = filter_var($_POST['smart_update'], FILTER_VALIDATE_BOOLEAN);
        $images = filter_var($_POST['import_images'], FILTER_VALIDATE_BOOLEAN);
        
        // Save Settings (only on first batch)
        if ($offset === 0) {
            update_option(self::$option_name, array('feed_url' => $url));
            self::clear_log();
            self::log("🚀 Iniciando importación desde $url");
            if ($smart) self::log("ℹ️ Smart Update ACTIVO");
        }
        
        // Fetch XML
        $response = wp_remote_get($url, array('timeout' => 120));
        if (is_wp_error($response)) {
            self::log("❌ Error descarga: " . $response->get_error_message());
            wp_send_json_error(array('message' => 'Error descarga.'));
        }
        
        $xml_content = wp_remote_retrieve_body($response);
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xml_content);
        if (!$xml) {
            self::log("❌ Error XML inválido.");
            wp_send_json_error(array('message' => 'XML inválido.'));
        }
        
        $properties = [];
        foreach ($xml->property as $p) $properties[] = $p;
        $total = count($properties);
        $batch = array_slice($properties, $offset, 5);
        
        if (empty($batch)) {
            wp_send_json_success(array('message' => 'Fin.', 'completed' => true));
        }
        
        $msg_buffer = "";
        
        foreach ($batch as $prop) {
            $res = self::process_property($prop, $update, $smart, $images);
            $msg_buffer .= $res . "\n";
            self::log($res);
        }
        
        $next = $offset + 5;
        $completed = $next >= $total;
        
        wp_send_json_success(array(
            'message' => $msg_buffer,
            'completed' => $completed,
            'next_offset' => $next
        ));
    }
    
    public static function handle_ajax_delete() {
        if (!current_user_can('manage_options')) wp_send_json_error();
        
        // Use global wpdb to be faster potentially, or get_posts
        // get_posts safer for cleaning up attachments? No, manual delete is safer.
        $posts = get_posts(array(
            'post_type' => 'impress_property',
            'meta_key' => '_imported_from_kyero',
            'posts_per_page' => 20,
            'fields' => 'ids'
        ));
        
        if (empty($posts)) {
            self::log("✅ Borrado finalizado.");
            wp_send_json_success(array('message' => 'Fin borrado.', 'completed' => true));
        }
        
        foreach ($posts as $pid) wp_delete_post($pid, true);
        
        $count = count($posts);
        $msg = "🗑️ Borrados $count inmuebles...";
        self::log($msg);
        
        wp_send_json_success(array('message' => $msg, 'completed' => false));
    }
    
    // --- LOGIC ---
    
    private static function process_property($xml, $update, $smart, $images) {
        $ref = (string)$xml->ref;
        $title = (string)($xml->title->es ?? $xml->title);
        $desc = (string)($xml->desc->es ?? $xml->desc);
        
        $existing_id = self::get_property_by_ref($ref);
        
        $post_data = array(
            'post_type' => 'impress_property',
            'post_status' => 'publish',
            'post_title' => $title ?: "Ref $ref",
            'post_content' => $desc
        );
        
        if ($existing_id) {
            if (!$update) return "⏭️ Existe: $ref (Saltado)";
            
            $post_data['ID'] = $existing_id;
            // Smart: Don't overwrite Title/Content if they are not empty? 
            // WP doesn't support that easily in wp_update_post without fetching first.
            // For MVP: We always update core fields if update is checked, Smart applies to ACF.
            $pid = wp_update_post($post_data);
            $log_prefix = "🔄 Editado";
        } else {
            $pid = wp_insert_post($post_data);
            $log_prefix = "✅ Creado";
        }
        
        if (is_wp_error($pid)) return "❌ Error $ref";
        
        update_post_meta($pid, '_imported_from_kyero', 1);
        
        // Smart Helper
        $set_field = function($key, $val) use ($pid, $smart) {
            if ($smart && !empty(get_field($key, $pid))) return;
            update_field($key, $val, $pid);
        };
        
        // Fields
        $set_field('field_referencia', $ref);
        
        $price = (float)$xml->price;
        $freq = (string)$xml->price_freq;
        
        if ($freq === 'sale') {
            $set_field('field_precio_venta', $price);
            wp_set_object_terms($pid, 'Venta', 'impress_operation');
            $set_field('field_proposito', 'venta');
        } else {
            $set_field('field_precio_alquiler', $price);
            wp_set_object_terms($pid, 'Alquiler', 'impress_operation');
            $set_field('field_proposito', 'alquiler');
        }
        
        $set_field('field_dormitorios', (int)$xml->bedrooms);
        $set_field('field_banos', (int)$xml->bathrooms);
        if (isset($xml->surface_area->built)) $set_field('field_superficie_construida', (int)$xml->surface_area->built);
        if (isset($xml->surface_area->plot)) $set_field('field_superficie_parcela', (int)$xml->surface_area->plot);
        
        // Location
        $town = (string)$xml->town ?: (string)$xml->location;
        // Fallback: Use location_id as town if specific town/location invalid
        if (empty($town) && isset($xml->location_id)) {
            $town = (string)$xml->location_id;
        }
        
        $prov = (string)$xml->province;
        
        if ($town) {
            // Capitalize for better consistency if it's uppercase (e.g. DENIA -> Denia)
            $town = ucwords(strtolower($town));
            wp_set_object_terms($pid, $town, 'impress_city');
            $set_field('field_direccion', $town);
        }
        if ($prov) wp_set_object_terms($pid, $prov, 'impress_province');
        
        // Type
        if (!empty($xml->type)) {
            wp_set_object_terms($pid, self::map_type((string)$xml->type), 'impress_property_type');
        }
        
        // Images
        if ($images && isset($xml->images)) {
            // Smart check: If gallery exists, skip
            if (!($smart && get_field('galeria', $pid))) {
                $gallery_ids = [];
                $count = 0;
                foreach ($xml->images->image as $img) {
                    $url = (string)$img->url;
                    if (!$url) continue;
                    
                    $att_id = self::sideload_image($url, $pid);
                    if ($att_id) {
                        if ($count === 0) set_post_thumbnail($pid, $att_id);
                        else $gallery_ids[] = $att_id;
                        $count++;
                    }
                }
                if ($gallery_ids) update_field('field_galeria', $gallery_ids, $pid);
            }
        }
        
        return "$log_prefix: $ref";
    }
    
    // --- HELPERS ---
    
    private static function get_property_by_ref($ref) {
        $q = get_posts(array('post_type'=>'impress_property', 'meta_key'=>'referencia', 'meta_value'=>$ref, 'posts_per_page'=>1, 'fields'=>'ids'));
        return $q ? $q[0] : false;
    }
    
    private static function map_type($t) {
        $t = strtolower($t);
        if (strpos($t, 'apartment')!==false) return 'Apartamento';
        if (strpos($t, 'flat')!==false) return 'Piso';
        if (strpos($t, 'villa')!==false) return 'Chalet';
        return ucfirst($t);
    }
    
    private static function sideload_image($url, $pid) {
        // Basic dedup by checking GUID or name? Too slow.
        // For now, simple download.
        $tmp = download_url($url);
        if (is_wp_error($tmp)) return false;
        
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $id = media_handle_sideload(array('name'=>basename($url), 'tmp_name'=>$tmp), $pid);
        if (is_wp_error($id)) { @unlink($tmp); return false; }
        return $id;
    }
    
    // --- LOG FILE ---
    
    private static function get_log_file() {
        $dir = wp_upload_dir();
        return $dir['basedir'] . '/' . self::$log_file;
    }
    
    private static function log($msg) {
        $file = self::get_log_file();
        $entry = "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
        file_put_contents($file, $entry, FILE_APPEND);
    }
    
    private static function clear_log() {
        file_put_contents(self::get_log_file(), "");
    }
    
    private static function get_log_content() {
        $file = self::get_log_file();
        if (file_exists($file)) return file_get_contents($file);
        return "";
    }
}
