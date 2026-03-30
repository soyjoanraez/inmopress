<?php
/**
 * WPTO Security Module
 * Implementa todas las funciones de seguridad
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPTO_Security {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('wpto_security_options', array());
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Cambio de URL de Login
        if (!empty($this->options['custom_login_url'])) {
            add_filter('site_url', array($this, 'filter_login_url'), 10, 3);
            add_filter('login_url', array($this, 'filter_login_url'), 10, 2);
            add_action('init', array($this, 'handle_custom_login'));
        }
        
        // Protección contra Fuerza Bruta
        if (!empty($this->options['brute_force_protection'])) {
            add_filter('authenticate', array($this, 'check_brute_force'), 30, 3);
            add_action('wp_login_failed', array($this, 'log_failed_login'));
        }
        
        // Hardening Automático
        if (!empty($this->options['auto_hardening'])) {
            add_action('init', array($this, 'apply_hardening'));
        }
        
        // Monitoreo de Archivos
        if (!empty($this->options['file_monitoring'])) {
            add_action('init', array($this, 'schedule_file_monitoring'));
            add_action('wpto_file_monitor_scan', array($this, 'run_file_monitoring'));
        }
        
        // Gestión de Sesiones
        if (!empty($this->options['session_management'])) {
            add_action('init', array($this, 'manage_sessions'));
            add_action('wp_login', array($this, 'register_session_login'), 10, 2);
        }
        
        // WAF Básico
        if (!empty($this->options['basic_waf'])) {
            add_action('init', array($this, 'basic_waf'));
        }

        // Autenticación de Dos Factores (2FA)
        if (!empty($this->options['two_factor_auth'])) {
            add_action('show_user_profile', array($this, 'render_2fa_profile'));
            add_action('edit_user_profile', array($this, 'render_2fa_profile'));
            add_action('personal_options_update', array($this, 'save_2fa_profile'));
            add_action('edit_user_profile_update', array($this, 'save_2fa_profile'));
            add_action('login_form', array($this, 'render_2fa_login_field'));
            add_filter('authenticate', array($this, 'verify_2fa_on_login'), 30, 3);
            add_action('admin_notices', array($this, 'maybe_show_2fa_admin_notice'));
            add_filter('login_message', array($this, 'render_login_grace_notice'));
        }
    }
    
    /**
     * Filtrar URL de login
     */
    public function filter_login_url($url, $path, $scheme = null) {
        // Solo procesar si está habilitado el login personalizado
        if (empty($this->options['custom_login_url'])) {
            return $url;
        }
        
        $slug = !empty($this->options['login_slug']) ? sanitize_key($this->options['login_slug']) : 'login';
        
        // Validar slug
        if (empty($slug) || preg_match('/[^a-z0-9_-]/', $slug)) {
            return $url;
        }
        
        if ($path === 'wp-login.php' || strpos($path, 'wp-login.php') === 0) {
            // Construir nueva URL con el slug personalizado
            $new_path = $slug . '/';
            if (strpos($path, '?') !== false) {
                $new_path .= '?' . substr($path, strpos($path, '?') + 1);
            }
            $url = home_url($new_path, $scheme);
        }
        
        return $url;
    }
    
    /**
     * Manejar login personalizado
     */
    public function handle_custom_login() {
        // No procesar en admin o AJAX
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            return;
        }

        // Verificar que REQUEST_URI esté disponible
        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }

        $slug = !empty($this->options['login_slug']) ? sanitize_key($this->options['login_slug']) : 'login';

        // Validar que el slug no esté vacío y sea válido
        if (empty($slug) || preg_match('/[^a-z0-9_-]/', $slug)) {
            return;
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $parsed_url = parse_url($request_uri);
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $path_trimmed = trim($path, '/');

        // PRIMERO: Bloquear acceso directo a wp-login.php y wp-admin (excepto admin-ajax.php)
        if (preg_match('/wp-login\.php/i', $path)) {
            // Permitir logout y otras acciones necesarias
            $allowed_actions = array('logout', 'postpass', 'lostpassword', 'retrievepassword', 'resetpass', 'rp');
            $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : '';

            if (!in_array($action, $allowed_actions)) {
                // Registrar intento de acceso bloqueado
                do_action('wpto_activity_logged', 'security_block', 'Acceso bloqueado a wp-login.php desde IP: ' . $this->get_client_ip(), 'warning');
                status_header(404);
                nocache_headers();
                die('Not Found');
            }
        }

        // Bloquear acceso directo a /wp-admin/ sin estar logueado (excepto admin-ajax.php y admin-post.php)
        if (preg_match('/wp-admin\/?$/i', $path) && !is_user_logged_in()) {
            status_header(404);
            nocache_headers();
            die('Not Found');
        }

        // SEGUNDO: Cargar wp-login.php si la ruta coincide con el slug personalizado
        if ($path_trimmed === $slug || strpos($path_trimmed, $slug . '/') === 0) {
            // Cargar wp-login.php
            if (file_exists(ABSPATH . 'wp-login.php')) {
                require_once(ABSPATH . 'wp-login.php');
                exit;
            }
        }
    }
    
    /**
     * Verificar protección contra fuerza bruta
     */
    public function check_brute_force($user, $username, $password) {
        if (empty($username)) {
            return $user;
        }
        
        $ip = $this->get_client_ip();
        $max_attempts = !empty($this->options['max_login_attempts']) ? intval($this->options['max_login_attempts']) : 5;
        $lockout_duration = !empty($this->options['lockout_duration']) ? intval($this->options['lockout_duration']) : 1800;
        
        $attempts = get_transient('wpto_brute_force_' . $ip);
        
        if ($attempts && $attempts >= $max_attempts) {
            $lockout_until = get_transient('wpto_brute_force_lockout_' . $ip);
            if ($lockout_until && $lockout_until > time()) {
                return new WP_Error('brute_force_locked', sprintf(
                    __('Demasiados intentos fallidos. Intenta de nuevo en %d minutos.', 'wpto'),
                    ceil(($lockout_until - time()) / 60)
                ));
            }
        }
        
        return $user;
    }
    
    /**
     * Registrar intento de login fallido
     */
    public function log_failed_login($username) {
        $ip = $this->get_client_ip();
        $max_attempts = !empty($this->options['max_login_attempts']) ? intval($this->options['max_login_attempts']) : 5;
        $lockout_duration = !empty($this->options['lockout_duration']) ? intval($this->options['lockout_duration']) : 1800;
        
        $attempts = get_transient('wpto_brute_force_' . $ip);
        $attempts = $attempts ? $attempts + 1 : 1;
        
        set_transient('wpto_brute_force_' . $ip, $attempts, $lockout_duration);
        
        if ($attempts >= $max_attempts) {
            set_transient('wpto_brute_force_lockout_' . $ip, time() + $lockout_duration, $lockout_duration);
        }
    }
    
    /**
     * Aplicar hardening automático
     */
    public function apply_hardening() {
        // Deshabilitar XML-RPC
        if (!empty($this->options['disable_xmlrpc'])) {
            add_filter('xmlrpc_enabled', '__return_false');
        }
        
        // Deshabilitar editor de archivos
        if (!empty($this->options['disable_file_edit'])) {
            if (!defined('DISALLOW_FILE_EDIT')) {
                define('DISALLOW_FILE_EDIT', true);
            }
        }
        
        // Cabeceras de seguridad
        if (!empty($this->options['security_headers'])) {
            add_action('send_headers', array($this, 'add_security_headers'));
        }
        
        // Ocultar versión de WordPress
        remove_action('wp_head', 'wp_generator');
        add_filter('the_generator', '__return_empty_string');
        
        // Deshabilitar información de versión en scripts
        add_filter('style_loader_src', array($this, 'remove_version_parameter'), 10, 2);
        add_filter('script_loader_src', array($this, 'remove_version_parameter'), 10, 2);
    }
    
    /**
     * Añadir cabeceras de seguridad HTTP
     */
    public function add_security_headers() {
        // Verificar que las cabeceras no se hayan enviado ya
        if (headers_sent()) {
            return;
        }
        
        // Añadir cabeceras de seguridad
        header('X-Content-Type-Options: nosniff', true);
        header('X-Frame-Options: SAMEORIGIN', true);
        header('X-XSS-Protection: 1; mode=block', true);
        header('Referrer-Policy: strict-origin-when-cross-origin', true);
        
        // Content Security Policy básico (opcional, puede causar problemas si hay inline scripts)
        // header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\'; style-src \'self\' \'unsafe-inline\';');
        
        // HSTS solo si es HTTPS
        if (is_ssl() || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains', true);
        }
    }
    
    /**
     * Remover parámetro de versión
     */
    public function remove_version_parameter($src, $handle) {
        if (strpos($src, 'ver=') !== false) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }
    
    /**
     * Programar monitoreo de archivos
     */
    public function schedule_file_monitoring() {
        if (empty($this->options['file_monitoring'])) {
            // Si se desactiva, limpiar cron
            wp_clear_scheduled_hook('wpto_file_monitor_scan');
            return;
        }

        if (!wp_next_scheduled('wpto_file_monitor_scan')) {
            wp_schedule_event(time(), 'daily', 'wpto_file_monitor_scan');
        }
    }

    /**
     * Ejecutar monitoreo de archivos (hashes)
     */
    public function run_file_monitoring() {
        // Solo cron o admins
        $is_cron = defined('DOING_CRON') && DOING_CRON;
        if (!$is_cron && !current_user_can('manage_options')) {
            return array('status' => 'error', 'message' => 'Permisos insuficientes');
        }

        $state = get_option('wpto_file_monitor_state', array());
        $previous_hashes = isset($state['hashes']) && is_array($state['hashes']) ? $state['hashes'] : array();

        $current_hashes = $this->build_file_hashes();
        if (empty($current_hashes)) {
            return array('status' => 'error', 'message' => 'No se pudo generar el hash de archivos');
        }

        if (empty($previous_hashes)) {
            update_option('wpto_file_monitor_state', array(
                'hashes' => $current_hashes,
                'last_scan' => current_time('mysql'),
                'count' => count($current_hashes),
                'last_status' => 'baseline',
                'last_summary' => 'Baseline de archivos creada'
            ));
            do_action('wpto_activity_logged', 'file_monitor', 'Baseline de archivos creada (' . count($current_hashes) . ' archivos)', 'success');
            return get_option('wpto_file_monitor_state', array());
        }

        $changes = $this->diff_file_hashes($previous_hashes, $current_hashes);
        $changes_limited = $this->limit_file_changes($changes, 50);

        $summary = sprintf(
            'Cambios detectados: %d modificados, %d añadidos, %d eliminados',
            count($changes['modified']),
            count($changes['added']),
            count($changes['deleted'])
        );

        // Guardar nuevo estado
        update_option('wpto_file_monitor_state', array(
            'hashes' => $current_hashes,
            'last_scan' => current_time('mysql'),
            'count' => count($current_hashes),
            'last_status' => (!empty($changes['modified']) || !empty($changes['added']) || !empty($changes['deleted'])) ? 'warning' : 'success',
            'last_summary' => $summary,
            'last_changes' => $changes_limited
        ));

        if (!empty($changes['modified']) || !empty($changes['added']) || !empty($changes['deleted'])) {
            do_action('wpto_activity_logged', 'file_monitor', $summary, 'warning');
            if (!empty($this->options['email_on_changes'])) {
                $this->email_file_changes($changes, $summary);
            }
        } else {
            do_action('wpto_activity_logged', 'file_monitor', 'Sin cambios detectados', 'success');
        }

        return get_option('wpto_file_monitor_state', array());
    }

    /**
     * Limitar lista de cambios para UI
     */
    private function limit_file_changes($changes, $limit = 50) {
        $limited = array('modified' => array(), 'added' => array(), 'deleted' => array());
        foreach (array('modified', 'added', 'deleted') as $key) {
            if (!empty($changes[$key]) && is_array($changes[$key])) {
                $limited[$key] = array_slice($changes[$key], 0, $limit);
            }
        }
        return $limited;
    }

    /**
     * Construir hashes de archivos críticos
     */
    private function build_file_hashes() {
        $targets = array(
            ABSPATH . 'wp-admin',
            ABSPATH . 'wp-includes',
            WP_CONTENT_DIR . '/plugins',
            WP_CONTENT_DIR . '/themes',
            ABSPATH . 'wp-config.php',
            ABSPATH . '.htaccess'
        );

        $hashes = array();
        foreach ($targets as $target) {
            if (is_file($target) && is_readable($target)) {
                $hashes[$target] = hash_file('sha256', $target);
                continue;
            }

            if (is_dir($target)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    if (!$file->isFile()) {
                        continue;
                    }
                    $path = $file->getPathname();

                    if ($this->should_skip_file($path)) {
                        continue;
                    }

                    // Límite de tamaño (5MB)
                    if ($file->getSize() > 5 * 1024 * 1024) {
                        continue;
                    }

                    if (!is_readable($path)) {
                        continue;
                    }

                    $hashes[$path] = hash_file('sha256', $path);
                }
            }
        }

        return $hashes;
    }

    /**
     * Determinar si un archivo debe excluirse del monitoreo
     */
    private function should_skip_file($path) {
        $normalized = str_replace('\\', '/', $path);
        $skip_paths = array(
            '/wp-content/uploads/',
            '/wp-content/cache/',
            '/wp-content/upgrade/',
            '/wp-content/backup',
            '/.git/',
            '/node_modules/',
            '/vendor/'
        );

        foreach ($skip_paths as $skip) {
            if (strpos($normalized, $skip) !== false) {
                return true;
            }
        }

        $allowed_ext = array('php', 'js', 'css', 'json', 'xml', 'txt', 'ini', 'htaccess', 'md');
        $ext = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));
        if ($ext !== '' && !in_array($ext, $allowed_ext, true)) {
            return true;
        }

        return false;
    }

    /**
     * Comparar hashes
     */
    private function diff_file_hashes($old, $new) {
        $modified = array();
        $added = array();
        $deleted = array();

        foreach ($new as $path => $hash) {
            if (!isset($old[$path])) {
                $added[] = $path;
            } elseif ($old[$path] !== $hash) {
                $modified[] = $path;
            }
        }

        foreach ($old as $path => $hash) {
            if (!isset($new[$path])) {
                $deleted[] = $path;
            }
        }

        return array(
            'modified' => $modified,
            'added' => $added,
            'deleted' => $deleted
        );
    }

    /**
     * Enviar email con cambios detectados
     */
    private function email_file_changes($changes, $summary) {
        $admin_email = get_option('admin_email');
        $subject = '[' . get_bloginfo('name') . '] Cambios detectados en archivos - WP Total Optimizer';

        $message = $summary . "\n\n";
        $message .= "MODIFICADOS:\n" . $this->format_change_list($changes['modified']) . "\n\n";
        $message .= "AÑADIDOS:\n" . $this->format_change_list($changes['added']) . "\n\n";
        $message .= "ELIMINADOS:\n" . $this->format_change_list($changes['deleted']) . "\n\n";
        $message .= "Fecha: " . current_time('mysql') . "\n";

        wp_mail($admin_email, $subject, $message);
    }

    private function format_change_list($items) {
        if (empty($items)) {
            return "- Ninguno";
        }

        $max = 25;
        $lines = array_slice($items, 0, $max);
        $output = '';
        foreach ($lines as $item) {
            $output .= '- ' . $item . "\n";
        }
        if (count($items) > $max) {
            $output .= '- ... y ' . (count($items) - $max) . " más\n";
        }

        return $output;
    }

    /**
     * Renderizar campos 2FA en perfil
     */
    public function render_2fa_profile($user) {
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }

        $enabled = get_user_meta($user->ID, 'wpto_2fa_enabled', true);
        $secret = get_user_meta($user->ID, 'wpto_2fa_secret', true);
        $backup_codes = get_user_meta($user->ID, 'wpto_2fa_backup_codes', true);
        $backup_plain = get_user_meta($user->ID, 'wpto_2fa_backup_plain', true);
        $backup_count = is_array($backup_codes) ? count($backup_codes) : 0;

        if (empty($secret)) {
            $secret = $this->generate_2fa_secret();
            update_user_meta($user->ID, 'wpto_2fa_secret', $secret);
        }

        $issuer = rawurlencode(get_bloginfo('name'));
        $label = rawurlencode($user->user_login . '@' . parse_url(home_url(), PHP_URL_HOST));
        $otpauth = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&digits=6&period=30";
        $qr_url = 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=' . rawurlencode($otpauth);
        ?>
        <h2>WP Total Optimizer - 2FA</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="wpto_2fa_enabled">Autenticación de Dos Factores</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="wpto_2fa_enabled" id="wpto_2fa_enabled" value="1" <?php checked($enabled, '1'); ?>>
                        Activar 2FA para este usuario
                    </label>
                    <p class="description">Recomendado para administradores.</p>
                </td>
            </tr>
            <tr>
                <th>Clave Secreta</th>
                <td>
                    <code style="font-size: 13px;"><?php echo esc_html($secret); ?></code>
                    <p class="description">Guarda esta clave en tu app (Google Authenticator, Authy, etc.).</p>
                </td>
            </tr>
            <tr>
                <th>Código QR</th>
                <td>
                    <img src="<?php echo esc_url($qr_url); ?>" alt="QR 2FA" style="border: 1px solid #ddd; padding: 6px; background: #fff;">
                    <p class="description">Escanea con tu app de autenticación.</p>
                </td>
            </tr>
            <tr>
                <th>Codes de Recuperación</th>
                <td>
                    <p><?php echo esc_html($backup_count); ?> codes disponibles.</p>
                    <?php if (!empty($backup_plain) && is_array($backup_plain)): ?>
                        <div style="background:#fff7e6;border:1px solid #f0c36d;padding:10px;border-radius:4px;margin-bottom:10px;">
                            <strong>Codes nuevos (guárdalos ahora):</strong>
                            <ul style="margin: 8px 0 0 18px;">
                                <?php foreach ($backup_plain as $code): ?>
                                    <li><code><?php echo esc_html($code); ?></code></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php delete_user_meta($user->ID, 'wpto_2fa_backup_plain'); ?>
                    <?php endif; ?>
                    <label>
                        <input type="checkbox" name="wpto_2fa_regen_codes" value="1">
                        Regenerar codes de recuperación
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Guardar 2FA en perfil
     */
    public function save_2fa_profile($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        $enabled = !empty($_POST['wpto_2fa_enabled']) ? '1' : '0';
        update_user_meta($user_id, 'wpto_2fa_enabled', $enabled);

        if ($enabled !== '1') {
            delete_user_meta($user_id, 'wpto_2fa_backup_codes');
            delete_user_meta($user_id, 'wpto_2fa_backup_plain');
            delete_user_meta($user_id, 'wpto_2fa_secret');
            return;
        }

        $existing_codes = get_user_meta($user_id, 'wpto_2fa_backup_codes', true);
        $regen = !empty($_POST['wpto_2fa_regen_codes']);

        if ($regen || empty($existing_codes) || !is_array($existing_codes)) {
            $codes = $this->generate_backup_codes(8);
            $hashed = array_map(array($this, 'hash_backup_code'), $codes);
            update_user_meta($user_id, 'wpto_2fa_backup_codes', $hashed);
            update_user_meta($user_id, 'wpto_2fa_backup_plain', $codes); // visible en el próximo perfil
        }
    }

    /**
     * Campo 2FA en login
     */
    public function render_2fa_login_field() {
        if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
            return;
        }
        ?>
        <p>
            <label for="wpto_2fa_code">Código 2FA<br>
                <input type="text" name="wpto_2fa_code" id="wpto_2fa_code" class="input" value="" size="20" autocomplete="one-time-code">
            </label>
        </p>
        <p>
            <label for="wpto_2fa_backup">Código de recuperación (opcional)<br>
                <input type="text" name="wpto_2fa_backup" id="wpto_2fa_backup" class="input" value="" size="20">
            </label>
        </p>
        <?php
    }

    /**
     * Verificar 2FA en login
     */
    public function verify_2fa_on_login($user, $username, $password) {
        if (is_wp_error($user) || !$user instanceof WP_User) {
            return $user;
        }

        if (empty($this->options['two_factor_auth'])) {
            return $user;
        }

        $enforced_for_admins = !empty($this->options['2fa_for_admins']);
        $is_admin = user_can($user, 'manage_options');

        $enabled = get_user_meta($user->ID, 'wpto_2fa_enabled', true);
        $secret = get_user_meta($user->ID, 'wpto_2fa_secret', true);

        if (empty($enabled)) {
            if ($enforced_for_admins && $is_admin) {
                $remaining = $this->get_2fa_grace_remaining();
                if ($remaining > 0) {
                    return $user;
                }
                return new WP_Error('wpto_2fa_required', __('Debes activar 2FA en tu perfil antes de iniciar sesión.', 'wpto'));
            }
            return $user;
        }

        if (empty($secret)) {
            return new WP_Error('wpto_2fa_missing_secret', __('2FA no está configurado correctamente para este usuario.', 'wpto'));
        }

        $backup_code = isset($_POST['wpto_2fa_backup']) ? sanitize_text_field($_POST['wpto_2fa_backup']) : '';
        if (!empty($backup_code)) {
            if ($this->consume_backup_code($user->ID, $backup_code)) {
                return $user;
            }
            return new WP_Error('wpto_2fa_backup_invalid', __('Código de recuperación inválido.', 'wpto'));
        }

        $code = isset($_POST['wpto_2fa_code']) ? sanitize_text_field($_POST['wpto_2fa_code']) : '';
        if (empty($code)) {
            return new WP_Error('wpto_2fa_missing', __('Introduce tu código 2FA.', 'wpto'));
        }

        if (!$this->verify_totp($secret, $code)) {
            return new WP_Error('wpto_2fa_invalid', __('Código 2FA inválido.', 'wpto'));
        }

        return $user;
    }

    /**
     * Aviso a admins sin 2FA cuando es obligatorio
     */
    public function maybe_show_2fa_admin_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (empty($this->options['two_factor_auth']) || empty($this->options['2fa_for_admins'])) {
            return;
        }

        $enabled = get_user_meta(get_current_user_id(), 'wpto_2fa_enabled', true);
        if (!empty($enabled)) {
            return;
        }

        $remaining = $this->get_2fa_grace_remaining();
        if ($remaining > 0) {
            $days = ceil($remaining / DAY_IN_SECONDS);
            ?>
            <div class="notice notice-warning">
                <p><strong>WP Total Optimizer:</strong> 2FA será obligatorio para administradores en <?php echo esc_html($days); ?> día(s). Actívalo en tu perfil.</p>
            </div>
            <?php
            return;
        }

        ?>
        <div class="notice notice-error">
            <p><strong>WP Total Optimizer:</strong> 2FA es obligatorio para administradores. Actívalo en tu perfil de usuario.</p>
        </div>
        <?php
    }

    /**
     * Obtener tiempo restante de gracia 2FA (segundos)
     */
    private function get_2fa_grace_remaining() {
        $grace_days = !empty($this->options['2fa_grace_days']) ? intval($this->options['2fa_grace_days']) : 0;
        if ($grace_days <= 0) {
            return 0;
        }

        $enforce_since = get_option('wpto_2fa_enforce_since');
        if (empty($enforce_since)) {
            $enforce_since = current_time('timestamp');
            update_option('wpto_2fa_enforce_since', $enforce_since);
        }

        $deadline = $enforce_since + ($grace_days * DAY_IN_SECONDS);
        return max(0, $deadline - current_time('timestamp'));
    }

    /**
     * Aviso en pantalla de login durante el grace period
     */
    public function render_login_grace_notice($message) {
        if (empty($this->options['two_factor_auth']) || empty($this->options['2fa_for_admins'])) {
            return $message;
        }

        $remaining = $this->get_2fa_grace_remaining();
        if ($remaining <= 0) {
            return $message;
        }

        $days = ceil($remaining / DAY_IN_SECONDS);
        $notice = '<p class="message">WP Total Optimizer: 2FA será obligatorio para administradores en ' . esc_html($days) . ' día(s). Actívalo en tu perfil.</p>';

        return $notice . $message;
    }

    /**
     * Generar secret base32
     */
    private function generate_2fa_secret($length = 16) {
        $bytes = random_bytes($length);
        return $this->base32_encode($bytes);
    }

    /**
     * Generar códigos de recuperación
     */
    private function generate_backup_codes($count = 8) {
        $codes = array();
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(wp_generate_password(10, false, false));
        }
        return $codes;
    }

    private function hash_backup_code($code) {
        return hash('sha256', $code);
    }

    private function consume_backup_code($user_id, $code) {
        $codes = get_user_meta($user_id, 'wpto_2fa_backup_codes', true);
        if (!is_array($codes)) {
            return false;
        }
        $hash = $this->hash_backup_code($code);
        $index = array_search($hash, $codes, true);
        if ($index === false) {
            return false;
        }
        unset($codes[$index]);
        update_user_meta($user_id, 'wpto_2fa_backup_codes', array_values($codes));
        return true;
    }

    /**
     * Verificar TOTP
     */
    private function verify_totp($secret, $code, $window = 1) {
        $code = preg_replace('/\s+/', '', $code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $timeSlice = floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            if ($this->generate_totp($secret, $timeSlice + $i) === $code) {
                return true;
            }
        }

        return false;
    }

    private function generate_totp($secret, $timeSlice) {
        $secretKey = $this->base32_decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncated = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;
        $code = $truncated % 1000000;
        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }

    private function base32_encode($data) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $chunks = str_split($binary, 5);
        $base32 = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $base32 .= $alphabet[bindec($chunk)];
        }
        return $base32;
    }

    private function base32_decode($secret) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
        $binary = '';
        foreach (str_split($secret) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                continue;
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = str_split($binary, 8);
        $output = '';
        foreach ($bytes as $byte) {
            if (strlen($byte) < 8) {
                continue;
            }
            $output .= chr(bindec($byte));
        }
        return $output;
    }
    
    /**
     * Gestionar sesiones
     */
    public function manage_sessions() {
        // Solo ejecutar si el usuario está logueado
        if (!is_user_logged_in()) {
            return;
        }
        
        // No ejecutar en AJAX para evitar problemas
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }

        $bootstrap = get_user_meta($user_id, 'wpto_session_bootstrap', true);
        if ($bootstrap) {
            update_user_meta($user_id, 'wpto_last_activity', time());
            delete_user_meta($user_id, 'wpto_session_bootstrap');
            return;
        }
        
        $timeout_minutes = !empty($this->options['session_timeout']) ? intval($this->options['session_timeout']) : 30;
        $timeout = $timeout_minutes * 60; // Convertir a segundos
        
        // Validar que el timeout sea razonable (mínimo 5 minutos, máximo 24 horas)
        $timeout = max(300, min(86400, $timeout));
        
        $last_activity = get_user_meta($user_id, 'wpto_last_activity', true);
        
        // Si hay última actividad registrada y ha expirado
        if ($last_activity && (time() - intval($last_activity)) > $timeout) {
            // Cerrar sesión
            wp_logout();
            
            // Redirigir a login con mensaje
            $redirect_url = add_query_arg('session_expired', '1', wp_login_url());
            wp_safe_redirect($redirect_url);
            exit;
        }
        
        // Actualizar última actividad
        update_user_meta($user_id, 'wpto_last_activity', time());
    }

    /**
     * Registrar sesión al hacer login para evitar logout inmediato
     */
    public function register_session_login($user_login, $user) {
        if (!$user || empty($user->ID)) {
            return;
        }
        update_user_meta($user->ID, 'wpto_last_activity', time());
        update_user_meta($user->ID, 'wpto_session_bootstrap', 1);
    }
    
    /**
     * WAF básico
     */
    public function basic_waf() {
        // No ejecutar en admin AJAX para evitar bloquear funcionalidades legítimas
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        // Verificar que REQUEST_URI esté disponible
        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $query_string = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

        // Decodificar URL para detectar ataques codificados (doble decodificación para detectar ataques avanzados)
        $decoded_uri = urldecode(urldecode($request_uri));
        $decoded_query = urldecode(urldecode($query_string));

        // También verificar datos POST si es método POST
        $post_data = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            // Solo verificar si no es una petición de admin legítima
            if (!is_admin()) {
                $post_data = http_build_query($_POST);
                $post_data = urldecode(urldecode($post_data));
            }
        }

        // Patrones de ataque comunes (más específicos para reducir falsos positivos)
        $patterns = array(
            // SQL Injection - patrones más específicos
            '/(\bunion\b\s+(all\s+)?select\b)/i',
            '/(\bselect\b.+\bfrom\b.+\bwhere\b.+[\'\"]\s*(or|and)\s*[\'\"]?\s*[\'\"0-9])/i',
            '/(\binsert\b\s+into\b.+\bvalues\b)/i',
            '/(\bdelete\b\s+from\b)/i',
            '/(\bdrop\b\s+(table|database)\b)/i',
            '/(\btruncate\b\s+table\b)/i',
            '/(\bexec\b\s*\(|\bexecute\b\s*\()/i',
            '/(sleep\s*\(\s*\d+\s*\)|benchmark\s*\()/i', // Time-based SQL injection
            // XSS
            '/<script[^>]*>/i',
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/data\s*:\s*text\/html/i',
            '/(onerror|onload|onclick|onmouseover|onfocus|onblur|onsubmit|onreset|onchange|oninput)\s*=/i',
            '/<\s*(iframe|object|embed|applet|form|input|img[^>]+onerror)/i',
            // Path Traversal
            '/(\.\.\/|\.\.\\\\){2,}/i', // Múltiples traversals
            '/\.\.[\/\\\\].*\.\.[\/\\\\]/i', // Traversal en diferentes partes
            // Command Injection (más específico)
            '/[;&|`]\s*(cat|ls|dir|pwd|whoami|id|uname|wget|curl|nc|netcat|bash|sh|cmd|powershell)\b/i',
            '/\$\(\s*(cat|ls|whoami|id)/i',
            // PHP Code Injection
            '/<\?php/i',
            '/base64_decode\s*\(/i',
            '/eval\s*\(/i',
            '/assert\s*\(/i',
            '/preg_replace\s*\([^)]*\/[a-z]*e[a-z]*\s*,/i', // preg_replace con modificador e
            // File Inclusion
            '/(include|require|include_once|require_once)\s*\(/i',
            '/php:\/\/input/i',
            '/php:\/\/filter/i',
            // Null byte injection
            '/\x00/i',
        );

        // Rutas que requieren protección especial (NO whitelist completa)
        // Solo aplicamos whitelist a archivos estáticos, no a rutas de admin
        $static_extensions = array('.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.eot', '.ico');
        $is_static_file = false;
        foreach ($static_extensions as $ext) {
            if (stripos($request_uri, $ext) !== false && strpos($request_uri, '?') === false) {
                $is_static_file = true;
                break;
            }
        }

        // Permitir archivos estáticos sin verificación
        if ($is_static_file) {
            return;
        }

        // Verificar patrones de ataque en URI y query string
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $decoded_uri) || preg_match($pattern, $decoded_query)) {
                $this->waf_block_request($pattern, $request_uri, 'URI/Query');
            }
        }

        // Verificar patrones en datos POST (excepto para ciertas rutas de admin)
        if (!empty($post_data) && !$this->is_safe_admin_action()) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $post_data)) {
                    $this->waf_block_request($pattern, $request_uri, 'POST data');
                }
            }
        }
    }

    /**
     * Verificar si es una acción de admin segura que no debe bloquearse
     */
    private function is_safe_admin_action() {
        // Permitir ciertas acciones de WordPress admin
        if (is_admin()) {
            $safe_actions = array(
                'edit', 'editpost', 'editedtag', 'add-tag',
                'upload-attachment', 'image-editor', 'save-attachment',
                'wpto_save_options', 'wpto_import_config', 'wpto_export_config'
            );

            $action = isset($_POST['action']) ? sanitize_key($_POST['action']) : '';
            if (in_array($action, $safe_actions)) {
                return true;
            }

            // Permitir edición de posts (contienen HTML legítimo)
            if (isset($_POST['post_content']) || isset($_POST['content'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bloquear petición del WAF
     */
    private function waf_block_request($pattern, $request_uri, $source) {
        $ip = $this->get_client_ip();
        do_action('wpto_activity_logged', 'waf_block', sprintf(
            'Ataque bloqueado - IP: %s, Fuente: %s, URI: %s',
            $ip,
            $source,
            substr($request_uri, 0, 200) // Limitar longitud para logs
        ), 'error');

        status_header(403);
        nocache_headers();
        die('Access Forbidden');
    }
    
    /**
     * Obtener IP del cliente
     * Maneja correctamente proxies, load balancers y CDNs
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',             // Algunos proxies
            'HTTP_X_REAL_IP',            // Nginx proxy
            'HTTP_X_FORWARDED_FOR',      // Proxies estándar
            'HTTP_X_FORWARDED',          // Otros proxies
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',         // Otros proxies
            'HTTP_FORWARDED',            // Otros proxies
            'REMOTE_ADDR'                // IP directa (último recurso)
        );
        
        foreach ($ip_keys as $key) {
            if (!isset($_SERVER[$key]) || empty($_SERVER[$key])) {
                continue;
            }
            
            // HTTP_X_FORWARDED_FOR puede contener múltiples IPs separadas por coma
            $ips = explode(',', $_SERVER[$key]);
            
            foreach ($ips as $ip) {
                $ip = trim($ip);
                
                // Validar que sea una IP válida
                if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                    continue;
                }
                
                // Priorizar IPs públicas sobre privadas
                // Pero si no hay IP pública, usar la privada (para desarrollo local)
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip; // IP pública encontrada
                } elseif (!isset($final_ip)) {
                    // Guardar primera IP privada como fallback
                    $final_ip = $ip;
                }
            }
        }
        
        // Si encontramos una IP privada, usarla (útil para desarrollo local)
        if (isset($final_ip)) {
            return $final_ip;
        }
        
        // Último recurso: REMOTE_ADDR
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        
        // Fallback final
        return '0.0.0.0';
    }
}
