<?php
/**
 * Plugin Name: WP Total Optimizer - Control Panel
 * Plugin URI: https://fixypet.com
 * Description: Panel de control para gestionar todas las optimizaciones de WordPress (Seguridad, Rendimiento, Imágenes, SEO)
 * Version: 1.0.0
 * Author: JoanRaez
 * Author URI: https://fixypet.com
 * License: GPL v2 or later
 * Text Domain: wpto
 * Domain Path: /languages
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('WPTO_VERSION', '1.0.0');
define('WPTO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPTO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Cargar clases de módulos
require_once WPTO_PLUGIN_DIR . 'includes/class-wpto-security.php';
require_once WPTO_PLUGIN_DIR . 'includes/class-wpto-optimization.php';
require_once WPTO_PLUGIN_DIR . 'includes/class-wpto-images.php';
require_once WPTO_PLUGIN_DIR . 'includes/class-wpto-seo.php';
require_once WPTO_PLUGIN_DIR . 'includes/class-wpto-redirects.php';
require_once WPTO_PLUGIN_DIR . 'includes/class-wpto-monitoring.php';
require_once WPTO_PLUGIN_DIR . 'includes/class-wpto-notifications.php';

/**
 * Clase principal del plugin
 */
class WP_Total_Optimizer {
    
    private static $instance = null;
    private $options;
    private $security;
    private $optimization;
    private $images;
    private $seo;
    private $redirects;
    private $monitoring;
    private $notifications;
    
    /**
     * Singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_options();
        $this->init_hooks();
        $this->init_modules();
    }
    
    /**
     * Inicializar módulos
     */
    private function init_modules() {
        $this->security = new WPTO_Security();
        $this->optimization = new WPTO_Optimization();
        $this->images = new WPTO_Images();
        $this->seo = new WPTO_SEO();
        $this->redirects = new WPTO_Redirects();
        $this->monitoring = new WPTO_Monitoring();
        $this->notifications = new WPTO_Notifications();
        
        // Registrar inicialización en log
        $this->log_activity('plugin_init', 'Plugin WPTO inicializado correctamente', 'success');
    }
    
    /**
     * Cargar opciones guardadas
     */
    private function load_options() {
        $this->options = array(
            'security' => get_option('wpto_security_options', array()),
            'optimization' => get_option('wpto_optimization_options', array()),
            'images' => get_option('wpto_images_options', array()),
            'seo' => get_option('wpto_seo_options', array()),
        );
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        add_action('wp_ajax_wpto_save_options', array($this, 'ajax_save_options'));
        add_action('wp_ajax_wpto_get_status', array($this, 'ajax_get_status'));
        add_action('wp_ajax_wpto_detect_cache', array($this, 'ajax_detect_cache'));
        add_action('wp_ajax_wpto_batch_convert', array($this, 'ajax_batch_convert'));
        add_action('wp_ajax_wpto_export_config', array($this, 'ajax_export_config'));
        add_action('wp_ajax_wpto_import_config', array($this, 'ajax_import_config'));
        add_action('wp_ajax_wpto_reset_config', array($this, 'ajax_reset_config'));
        add_action('wp_ajax_wpto_export_logs', array($this, 'ajax_export_logs'));
        add_action('wp_ajax_wpto_clear_old_logs', array($this, 'ajax_clear_old_logs'));
        add_action('wp_ajax_wpto_clear_debug_log', array($this, 'ajax_clear_debug_log'));
        add_action('wp_ajax_wpto_download_debug_log', array($this, 'ajax_download_debug_log'));
        add_action('wp_ajax_wpto_seo_bulk_load', array($this, 'ajax_seo_bulk_load'));
        add_action('wp_ajax_wpto_seo_bulk_save', array($this, 'ajax_seo_bulk_save'));
        add_action('wp_ajax_wpto_seo_bulk_stats', array($this, 'ajax_seo_bulk_stats'));
        add_action('wp_ajax_wpto_run_file_scan', array($this, 'ajax_run_file_scan'));
        add_action('wp_ajax_wpto_get_file_changes', array($this, 'ajax_get_file_changes'));
        add_action('save_post', array($this, 'invalidate_bulk_stats_cache'), 10, 3);
        add_action('delete_post', array($this, 'invalidate_bulk_stats_cache_simple'), 10, 1);
        add_action('transition_post_status', array($this, 'invalidate_bulk_stats_cache_transition'), 10, 3);
        add_action('admin_notices', array($this, 'admin_notices'));
    }
    
    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            'WP Total Optimizer',
            'Total Optimizer',
            'manage_options',
            'wpto-control-panel',
            array($this, 'render_control_panel'),
            'dashicons-performance',
            80
        );
        
        // Submenús
        add_submenu_page(
            'wpto-control-panel',
            'Panel de Control',
            'Panel de Control',
            'manage_options',
            'wpto-control-panel'
        );
        
        add_submenu_page(
            'wpto-control-panel',
            'Estadísticas',
            'Estadísticas',
            'manage_options',
            'wpto-stats',
            array($this, 'render_stats_page')
        );
        
        add_submenu_page(
            'wpto-control-panel',
            'Conversión Batch',
            'Conversión Batch',
            'manage_options',
            'wpto-batch-converter',
            array($this, 'render_batch_converter_page')
        );
        
        add_submenu_page(
            'wpto-control-panel',
            'Logs del Sistema',
            'Logs del Sistema',
            'manage_options',
            'wpto-logs',
            array($this, 'render_logs_page')
        );

        add_submenu_page(
            'wpto-control-panel',
            'Edición Masiva SEO',
            'Edición Masiva SEO',
            'manage_options',
            'wpto-bulk-seo',
            array($this, 'render_bulk_seo_page')
        );
    }
    
    /**
     * Cargar assets de administración
     */
    public function enqueue_admin_assets($hook) {
        // Cargar en páginas del plugin
        if (strpos($hook, 'wpto-') !== false) {
        wp_enqueue_style(
            'wpto-admin-css',
            WPTO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WPTO_VERSION
        );
        
        wp_enqueue_script(
            'wpto-admin-js',
            WPTO_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WPTO_VERSION,
            true
        );
        
        wp_localize_script('wpto-admin-js', 'wptoAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpto_nonce'),
            'strings' => array(
                'saved' => __('Configuración guardada correctamente', 'wpto'),
                'error' => __('Error al guardar la configuración', 'wpto'),
                'confirm_disable' => __('¿Estás seguro de desactivar esta función?', 'wpto'),
            ),
        ));
        }
        
        // Cargar también en editor de posts/páginas para SEO
        if (in_array($hook, array('post.php', 'post-new.php', 'page.php', 'page-new.php'))) {
            wp_enqueue_style(
                'wpto-admin-css',
                WPTO_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WPTO_VERSION
            );
            
            wp_enqueue_script(
                'wpto-admin-js',
                WPTO_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                WPTO_VERSION,
                true
            );
        }
    }
    
    /**
     * Obtener valor de opción guardada
     */
    private function get_option_value($module, $key, $default = '') {
        $options = $this->options[$module];
        if (isset($options[$key])) {
            return $options[$key];
        }
        return $default;
    }
    
    /**
     * Verificar si una opción está activa
     */
    private function is_option_active($module, $key) {
        $value = $this->get_option_value($module, $key);
        return ($value === '1' || $value === true || $value === 1);
    }
    
    /**
     * Renderizar panel de control
     */
    public function render_control_panel() {
        ?>
        <div class="wrap wpto-wrap">
            <h1 class="wpto-title">
                <span class="dashicons dashicons-performance"></span>
                WP Total Optimizer - Panel de Control
            </h1>

            <div id="wpto-file-monitor-modal" class="wpto-modal" style="display:none;">
                <div class="wpto-modal-overlay"></div>
                <div class="wpto-modal-content">
                    <div class="wpto-modal-header">
                        <strong>Cambios detectados</strong>
                        <button type="button" class="button-link wpto-modal-close">Cerrar</button>
                    </div>
                    <div class="wpto-modal-body">
                        <div class="wpto-modal-section">
                            <h4>Modificados</h4>
                            <ul class="wpto-modal-list" data-type="modified"></ul>
                        </div>
                        <div class="wpto-modal-section">
                            <h4>Añadidos</h4>
                            <ul class="wpto-modal-list" data-type="added"></ul>
                        </div>
                        <div class="wpto-modal-section">
                            <h4>Eliminados</h4>
                            <ul class="wpto-modal-list" data-type="deleted"></ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wpto-header-stats">
                <div class="stat-card">
                    <span class="stat-icon dashicons dashicons-shield"></span>
                    <div class="stat-content">
                        <div class="stat-value" id="active-security">0</div>
                        <div class="stat-label">Funciones de Seguridad Activas</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <span class="stat-icon dashicons dashicons-performance"></span>
                    <div class="stat-content">
                        <div class="stat-value" id="active-optimization">0</div>
                        <div class="stat-label">Optimizaciones Activas</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <span class="stat-icon dashicons dashicons-format-image"></span>
                    <div class="stat-content">
                        <div class="stat-value" id="active-images">0</div>
                        <div class="stat-label">Funciones de Imágenes Activas</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <span class="stat-icon dashicons dashicons-search"></span>
                    <div class="stat-content">
                        <div class="stat-value" id="active-seo">0</div>
                        <div class="stat-label">Funciones SEO Activas</div>
                    </div>
                </div>
            </div>
            
            <div class="wpto-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#security" class="nav-tab nav-tab-active">
                        <span class="dashicons dashicons-shield"></span>
                        Seguridad
                    </a>
                    <a href="#optimization" class="nav-tab">
                        <span class="dashicons dashicons-performance"></span>
                        Optimización
                    </a>
                    <a href="#images" class="nav-tab">
                        <span class="dashicons dashicons-format-image"></span>
                        Imágenes
                    </a>
                    <a href="#seo" class="nav-tab">
                        <span class="dashicons dashicons-search"></span>
                        SEO
                    </a>
                </nav>
                
                <!-- TAB: SEGURIDAD -->
                <div id="security" class="tab-content active">
                    <h2>Módulo de Seguridad</h2>
                    <p class="description">Protege tu sitio WordPress con estas funciones de seguridad avanzadas.</p>
                    
                    <form class="wpto-options-form" data-module="security">
                        <div class="wpto-functions-grid">
                            
                            <!-- Cambio de URL de Login -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Cambio de URL de Login</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="security[custom_login_url]" value="1" <?php checked($this->is_option_active('security', 'custom_login_url')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Cambia la URL de wp-admin y wp-login.php a una personalizada para ocultar el punto de entrada de administración.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('security', 'custom_login_url') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <strong>Nueva URL de login:</strong>
                                        <input type="text" name="security[login_slug]" value="<?php echo esc_attr($this->get_option_value('security', 'login_slug')); ?>" placeholder="mi-panel-admin" class="regular-text">
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Protección Fuerza Bruta -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Protección Contra Fuerza Bruta</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="security[brute_force_protection]" value="1" <?php checked($this->is_option_active('security', 'brute_force_protection')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Limita los intentos de login fallidos y bloquea IPs sospechosas automáticamente.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('security', 'brute_force_protection') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <strong>Máximo de intentos:</strong>
                                        <select name="security[max_login_attempts]">
                                            <option value="3" <?php selected($this->get_option_value('security', 'max_login_attempts', '5'), '3'); ?>>3 intentos</option>
                                            <option value="5" <?php selected($this->get_option_value('security', 'max_login_attempts', '5'), '5'); ?>>5 intentos</option>
                                            <option value="10" <?php selected($this->get_option_value('security', 'max_login_attempts', '5'), '10'); ?>>10 intentos</option>
                                        </select>
                                    </label>
                                    <label>
                                        <strong>Tiempo de bloqueo:</strong>
                                        <select name="security[lockout_duration]">
                                            <option value="900" <?php selected($this->get_option_value('security', 'lockout_duration', '1800'), '900'); ?>>15 minutos</option>
                                            <option value="1800" <?php selected($this->get_option_value('security', 'lockout_duration', '1800'), '1800'); ?>>30 minutos</option>
                                            <option value="3600" <?php selected($this->get_option_value('security', 'lockout_duration', '1800'), '3600'); ?>>1 hora</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- 2FA -->
                            <div class="function-card priority-medium">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Autenticación de Dos Factores (2FA)</h3>
                                        <span class="priority-badge medium">Media Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="security[two_factor_auth]" value="1" <?php checked($this->is_option_active('security', 'two_factor_auth')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Añade una capa extra de seguridad con apps TOTP (Google Authenticator, Authy, etc.). Configúralo en tu perfil de usuario.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('security', 'two_factor_auth') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="security[2fa_for_admins]" value="1" <?php checked($this->is_option_active('security', '2fa_for_admins')); ?>>
                                        Obligatorio para administradores
                                    </label>
                                    <label style="display:block; margin-top:8px;">
                                        <strong>Grace period (días):</strong>
                                        <input type="number" name="security[2fa_grace_days]" value="<?php echo esc_attr($this->get_option_value('security', '2fa_grace_days', '7')); ?>" min="0" max="30" style="width:80px;">
                                        <span class="description">0 = sin gracia</span>
                                    </label>
                                    <p class="description" style="margin-top: 8px;">Si activas esta opción, los administradores deberán configurar 2FA en su perfil para iniciar sesión.</p>
                                </div>
                            </div>
                            
                            <!-- Hardening Automático -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Hardening Automático</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="security[auto_hardening]" value="1" <?php checked($this->is_option_active('security', 'auto_hardening')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>17 medidas de seguridad automáticas: deshabilitar editor, ocultar versión WP, proteger archivos sensibles, etc.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('security', 'auto_hardening') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="security[disable_xmlrpc]" value="1" <?php checked($this->is_option_active('security', 'disable_xmlrpc')); ?>>
                                        Deshabilitar XML-RPC
                                    </label>
                                    <label>
                                        <input type="checkbox" name="security[disable_file_edit]" value="1" <?php checked($this->is_option_active('security', 'disable_file_edit'), true); ?>>
                                        Deshabilitar editor de archivos
                                    </label>
                                    <label>
                                        <input type="checkbox" name="security[security_headers]" value="1" <?php checked($this->is_option_active('security', 'security_headers'), true); ?>>
                                        Añadir cabeceras de seguridad HTTP
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Monitoreo de Archivos -->
                            <div class="function-card priority-medium">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Monitoreo de Cambios en Archivos</h3>
                                        <span class="priority-badge medium">Media Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="security[file_monitoring]" value="1" <?php checked($this->is_option_active('security', 'file_monitoring')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Detecta modificaciones no autorizadas en archivos core de WordPress.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('security', 'file_monitoring') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="security[email_on_changes]" value="1" <?php checked($this->is_option_active('security', 'email_on_changes')); ?>>
                                        Enviar email al detectar cambios
                                    </label>
                                    <?php
                                    $file_monitor_state = get_option('wpto_file_monitor_state', array());
                                    $last_scan = !empty($file_monitor_state['last_scan']) ? $file_monitor_state['last_scan'] : '';
                                    $last_status = !empty($file_monitor_state['last_status']) ? $file_monitor_state['last_status'] : 'n/a';
                                    $last_summary = !empty($file_monitor_state['last_summary']) ? $file_monitor_state['last_summary'] : 'Sin datos';
                                    $file_count = !empty($file_monitor_state['count']) ? intval($file_monitor_state['count']) : 0;
                                    $last_changes = !empty($file_monitor_state['last_changes']) ? $file_monitor_state['last_changes'] : array();
                                    ?>
                                    <div class="wpto-file-monitor-status" data-last-changes="<?php echo esc_attr(wp_json_encode($last_changes)); ?>">
                                        <strong>Último escaneo:</strong>
                                        <?php echo $last_scan ? esc_html(date_i18n('Y-m-d H:i:s', strtotime($last_scan))) : 'Nunca'; ?><br>
                                        <strong>Archivos monitorizados:</strong> <?php echo esc_html($file_count); ?><br>
                                        <strong>Estado:</strong> <?php echo esc_html($last_status); ?><br>
                                        <strong>Resumen:</strong> <?php echo esc_html($last_summary); ?>
                                    </div>
                                    <button type="button" class="button button-secondary" id="wpto-file-monitor-run" style="margin-top:8px;">
                                        Ejecutar escaneo ahora
                                    </button>
                                    <span id="wpto-file-monitor-run-status" style="margin-left:10px; color:#646970;"></span>
                                    <button type="button" class="button" id="wpto-file-monitor-show" style="margin-top:8px; margin-left:6px;">
                                        Ver cambios detectados
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Gestión de Sesiones -->
                            <div class="function-card priority-medium">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Gestión de Sesiones</h3>
                                        <span class="priority-badge medium">Media Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="security[session_management]" value="1" <?php checked($this->is_option_active('security', 'session_management')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Cierre de sesión automático por inactividad y renovación de claves de seguridad.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('security', 'session_management') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <strong>Tiempo de inactividad (minutos):</strong>
                                        <input type="number" name="security[session_timeout]" value="<?php echo esc_attr($this->get_option_value('security', 'session_timeout', '30')); ?>" min="5" max="120">
                                    </label>
                                </div>
                            </div>
                            
                            <!-- WAF Básico -->
                            <div class="function-card priority-low">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Firewall de Aplicación Web (WAF)</h3>
                                        <span class="priority-badge low">Baja Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="security[basic_waf]" value="1" <?php checked($this->is_option_active('security', 'basic_waf')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Protección básica contra SQL injection, XSS y ataques conocidos.</p>
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="wpto-form-footer">
                            <button type="submit" class="button button-primary button-large">
                                <span class="dashicons dashicons-saved"></span>
                                Guardar Configuración de Seguridad
                            </button>
                            <span class="save-status"></span>
                        </div>
                    </form>
                </div>
                
                <!-- TAB: OPTIMIZACIÓN -->
                <div id="optimization" class="tab-content">
                    <h2>Módulo de Optimización</h2>
                    <p class="description">Mejora el rendimiento de tu sitio con estas optimizaciones.</p>
                    
                    <form class="wpto-options-form" data-module="optimization">
                        <div class="wpto-functions-grid">
                            
                            <!-- Optimización de BD -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Optimización de Base de Datos</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="optimization[database_optimization]" value="1" <?php checked($this->is_option_active('optimization', 'database_optimization')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Limpieza automática de revisiones, borradores, spam y optimización de tablas.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('optimization', 'database_optimization') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="optimization[clean_revisions]" value="1" <?php checked($this->is_option_active('optimization', 'clean_revisions'), true); ?>>
                                        Limpiar revisiones de posts
                                    </label>
                                    <label>
                                        <input type="checkbox" name="optimization[clean_autodrafts]" value="1" <?php checked($this->is_option_active('optimization', 'clean_autodrafts'), true); ?>>
                                        Limpiar borradores automáticos
                                    </label>
                                    <label>
                                        <input type="checkbox" name="optimization[clean_spam]" value="1" <?php checked($this->is_option_active('optimization', 'clean_spam'), true); ?>>
                                        Limpiar comentarios spam
                                    </label>
                                    <label>
                                        <strong>Frecuencia:</strong>
                                        <select name="optimization[db_cleanup_frequency]">
                                            <option value="daily" <?php selected($this->get_option_value('optimization', 'db_cleanup_frequency', 'weekly'), 'daily'); ?>>Diaria</option>
                                            <option value="weekly" <?php selected($this->get_option_value('optimization', 'db_cleanup_frequency', 'weekly'), 'weekly'); ?>>Semanal</option>
                                            <option value="monthly" <?php selected($this->get_option_value('optimization', 'db_cleanup_frequency', 'weekly'), 'monthly'); ?>>Mensual</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Desactivación de Recursos -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Desactivación de Recursos Innecesarios</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="optimization[disable_unnecessary]" value="1" <?php checked($this->is_option_active('optimization', 'disable_unnecessary')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Desactiva emojis, embeds, jQuery Migrate, Dashicons en frontend, etc.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('optimization', 'disable_unnecessary') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="optimization[disable_emojis]" value="1" <?php checked($this->is_option_active('optimization', 'disable_emojis')); ?>>
                                        Emojis de WordPress
                                    </label>
                                    <label>
                                        <input type="checkbox" name="optimization[disable_embeds]" value="1" <?php checked($this->is_option_active('optimization', 'disable_embeds')); ?>>
                                        Embeds (oEmbed)
                                    </label>
                                    <label>
                                        <input type="checkbox" name="optimization[disable_jquery_migrate]" value="1" <?php checked($this->is_option_active('optimization', 'disable_jquery_migrate')); ?>>
                                        jQuery Migrate
                                    </label>
                                    <label>
                                        <input type="checkbox" name="optimization[disable_dashicons]" value="1" <?php checked($this->is_option_active('optimization', 'disable_dashicons')); ?>>
                                        Dashicons en frontend
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Minificación -->
                            <div class="function-card priority-medium">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Minificación CSS/JS</h3>
                                        <span class="priority-badge medium">Media Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="optimization[minification]" value="1" <?php checked($this->is_option_active('optimization', 'minification')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Minifica y concatena archivos CSS y JavaScript para reducir tamaño y peticiones.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('optimization', 'minification') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="optimization[minify_css]" value="1" <?php checked($this->is_option_active('optimization', 'minify_css'), true); ?>>
                                        Minificar CSS
                                    </label>
                                    <label>
                                        <input type="checkbox" name="optimization[minify_js]" value="1" <?php checked($this->is_option_active('optimization', 'minify_js'), true); ?>>
                                        Minificar JavaScript
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Lazy Loading -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Carga Diferida (Lazy Loading)</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="optimization[lazy_loading]" value="1" <?php checked($this->is_option_active('optimization', 'lazy_loading')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Carga imágenes e iframes solo cuando son visibles en pantalla.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('optimization', 'lazy_loading') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="optimization[lazy_images]" value="1" <?php checked($this->is_option_active('optimization', 'lazy_images'), true); ?>>
                                        Imágenes
                                    </label>
                                    <label>
                                        <input type="checkbox" name="optimization[lazy_iframes]" value="1" <?php checked($this->is_option_active('optimization', 'lazy_iframes'), true); ?>>
                                        Iframes (YouTube, Vimeo)
                                    </label>
                                </div>
                            </div>
                            
                            <!-- DNS Prefetch -->
                            <div class="function-card priority-medium">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>DNS Prefetch y Preconnect</h3>
                                        <span class="priority-badge medium">Media Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="optimization[dns_prefetch]" value="1" <?php checked($this->is_option_active('optimization', 'dns_prefetch')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Optimiza conexiones a dominios externos (Google Fonts, Analytics, CDN).</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('optimization', 'dns_prefetch') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <strong>Dominios (uno por línea):</strong>
                                        <textarea name="optimization[prefetch_domains]" rows="3" class="large-text"><?php echo esc_textarea($this->get_option_value('optimization', 'prefetch_domains', "fonts.googleapis.com\nwww.google-analytics.com")); ?></textarea>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Optimización Gutenberg -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Optimización de Gutenberg</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="optimization[gutenberg_optimization]" value="1" <?php checked($this->is_option_active('optimization', 'gutenberg_optimization')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Carga solo los bloques y estilos que realmente uses.</p>
                                </div>
                            </div>
                            
                            <!-- Object Caching -->
                            <div class="function-card priority-medium">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Object Caching (Redis/Memcached)</h3>
                                        <span class="priority-badge medium">Media Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="optimization[object_caching]" value="1" <?php checked($this->is_option_active('optimization', 'object_caching')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Caché avanzado de objetos con detección automática de Redis o Memcached.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('optimization', 'object_caching') ? 'block' : 'none'; ?>;">
                                    <p class="cache-status">
                                        <strong>Estado:</strong> 
                                        <span id="cache-detection">Detectando...</span>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Limpieza de Código -->
                            <div class="function-card priority-low">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Limpieza Automática de Código</h3>
                                        <span class="priority-badge low">Baja Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="optimization[code_cleanup]" value="1" <?php checked($this->is_option_active('optimization', 'code_cleanup')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Elimina comentarios HTML y reduce whitespace excesivo.</p>
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="wpto-form-footer">
                            <button type="submit" class="button button-primary button-large">
                                <span class="dashicons dashicons-saved"></span>
                                Guardar Configuración de Optimización
                            </button>
                            <span class="save-status"></span>
                        </div>
                    </form>
                </div>
                
                <!-- TAB: IMÁGENES -->
                <div id="images" class="tab-content">
                    <h2>Módulo de Gestión de Imágenes</h2>
                    <p class="description">Optimiza y gestiona tus imágenes de forma eficiente.</p>
                    
                    <form class="wpto-options-form" data-module="images">
                        <div class="wpto-functions-grid">
                            
                            <!-- Conversión WebP/AVIF -->
                            <div class="function-card priority-critical">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Conversión Automática a WebP/AVIF</h3>
                                        <span class="priority-badge critical">Prioridad Crítica</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="images[webp_conversion]" value="1" <?php checked($this->is_option_active('images', 'webp_conversion')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Convierte automáticamente imágenes a WebP/AVIF al subirlas, reduciendo hasta 80% el tamaño.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('images', 'webp_conversion') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="images[generate_webp]" value="1" <?php checked($this->is_option_active('images', 'generate_webp'), true); ?>>
                                        Generar WebP
                                    </label>
                                    <label>
                                        <input type="checkbox" name="images[generate_avif]" value="1" <?php checked($this->is_option_active('images', 'generate_avif')); ?>>
                                        Generar AVIF
                                    </label>
                                    <label>
                                        <strong>Calidad (%):</strong>
                                        <?php $quality = $this->get_option_value('images', 'conversion_quality', '85'); ?>
                                        <input type="range" name="images[conversion_quality]" min="60" max="100" value="<?php echo esc_attr($quality); ?>" step="5">
                                        <span class="range-value"><?php echo esc_html($quality); ?>%</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Control de Tamaños -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Control de Tamaños de Imagen</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="images[size_control]" value="1" <?php checked($this->is_option_active('images', 'size_control')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Genera solo los tamaños de imagen que realmente necesites.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('images', 'size_control') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="images[disable_theme_sizes]" value="1" <?php checked($this->is_option_active('images', 'disable_theme_sizes')); ?>>
                                        Deshabilitar tamaños de tema/plugins
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Sobrescritura de Imágenes -->
                            <div class="function-card priority-medium">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Sobrescritura de Imágenes con Mismo Nombre</h3>
                                        <span class="priority-badge medium">Media Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="images[overwrite_duplicates]" value="1" <?php checked($this->is_option_active('images', 'overwrite_duplicates')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Sobrescribe automáticamente imágenes con el mismo nombre en lugar de crear duplicados.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('images', 'overwrite_duplicates') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="images[confirm_overwrite]" value="1" <?php checked($this->is_option_active('images', 'confirm_overwrite')); ?>>
                                        Pedir confirmación antes de sobrescribir
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Eliminación Completa -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Eliminación de Todas las Versiones</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="images[complete_deletion]" value="1" <?php checked($this->is_option_active('images', 'complete_deletion')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Al borrar una imagen, elimina también todas sus versiones (WebP, AVIF, tamaños).</p>
                                </div>
                            </div>
                            
                            <!-- Optimización y Compresión -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Optimización y Compresión</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="images[image_optimization]" value="1" <?php checked($this->is_option_active('images', 'image_optimization')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Compresión automática, JPEG progresivo y redimensión si excede dimensiones máximas.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('images', 'image_optimization') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <strong>Calidad JPEG (%):</strong>
                                        <?php $jpeg_quality = $this->get_option_value('images', 'jpeg_quality', '82'); ?>
                                        <input type="range" name="images[jpeg_quality]" min="60" max="100" value="<?php echo esc_attr($jpeg_quality); ?>" step="1">
                                        <span class="range-value"><?php echo esc_html($jpeg_quality); ?>%</span>
                                    </label>
                                    <label>
                                        <strong>Ancho máximo (px):</strong>
                                        <input type="number" name="images[max_width]" value="<?php echo esc_attr($this->get_option_value('images', 'max_width', '2048')); ?>" min="800" max="4096">
                                    </label>
                                </div>
                            </div>
                            
                            <!-- ALT Automático -->
                            <div class="function-card priority-medium">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>ALT Automático desde Nombre de Archivo</h3>
                                        <span class="priority-badge medium">Media Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="images[auto_alt]" value="1" <?php checked($this->is_option_active('images', 'auto_alt')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Genera texto ALT automáticamente desde el nombre de archivo (limpia guiones, números, capitaliza).</p>
                                </div>
                            </div>
                            
                            <!-- Sugerencias ALT Contextuales -->
                            <div class="function-card priority-low">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Sugerencias de ALT Contextuales</h3>
                                        <span class="priority-badge low">Baja Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="images[contextual_alt]" value="1" <?php checked($this->is_option_active('images', 'contextual_alt')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Sugiere texto ALT basado en el título del post donde se inserta la imagen.</p>
                                </div>
                            </div>
                            
                            <!-- Conversión Batch -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Conversión Batch de Imágenes</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="images[batch_conversion]" value="1" <?php checked($this->is_option_active('images', 'batch_conversion')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Herramienta para convertir todas las imágenes existentes a WebP/AVIF de una vez.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('images', 'batch_conversion') ? 'block' : 'none'; ?>;">
                                    <a href="<?php echo admin_url('admin.php?page=wpto-batch-converter'); ?>" class="button">
                                        Ir a Conversión Batch
                                    </a>
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="wpto-form-footer">
                            <button type="submit" class="button button-primary button-large">
                                <span class="dashicons dashicons-saved"></span>
                                Guardar Configuración de Imágenes
                            </button>
                            <span class="save-status"></span>
                        </div>
                    </form>
                </div>
                
                <!-- TAB: SEO -->
                <div id="seo" class="tab-content">
                    <h2>Módulo de SEO</h2>
                    <p class="description">Optimiza tu sitio para motores de búsqueda.</p>
                    
                    <form class="wpto-options-form" data-module="seo">
                        <div class="wpto-functions-grid">
                            
                            <!-- Panel de Encabezados -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Panel de Estructura de Encabezados</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="seo[heading_panel]" value="1" <?php checked($this->is_option_active('seo', 'heading_panel')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Análisis en tiempo real de estructura H1-H6 con detección de errores y sugerencias.</p>
                                </div>
                            </div>
                            
                            <!-- Meta Descripción y Título -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Meta Descripción, Título SEO y Keywords</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="seo[meta_fields]" value="1" <?php checked($this->is_option_active('seo', 'meta_fields')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Campos personalizados con contador de caracteres y vista previa SERP.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('seo', 'meta_fields') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="seo[compatible_yoast]" value="1" <?php checked($this->is_option_active('seo', 'compatible_yoast'), true); ?>>
                                        Detectar y no duplicar con Yoast/Rank Math
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Schema Markup -->
                            <div class="function-card priority-medium">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Schema Markup Automático</h3>
                                        <span class="priority-badge medium">Media Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="seo[schema_markup]" value="1" <?php checked($this->is_option_active('seo', 'schema_markup')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Genera JSON-LD automático para Article, Organization, Breadcrumbs, etc.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('seo', 'schema_markup') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="seo[schema_article]" value="1" <?php checked($this->is_option_active('seo', 'schema_article'), true); ?>>
                                        Article Schema
                                    </label>
                                    <label>
                                        <input type="checkbox" name="seo[schema_breadcrumbs]" value="1" <?php checked($this->is_option_active('seo', 'schema_breadcrumbs'), true); ?>>
                                        Breadcrumbs Schema
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Sitemap XML -->
                            <div class="function-card priority-medium">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Sitemap XML Automático</h3>
                                        <span class="priority-badge medium">Media Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="seo[xml_sitemap]" value="1" <?php checked($this->is_option_active('seo', 'xml_sitemap')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Genera sitemap.xml automáticamente y hace ping a Google/Bing al publicar.</p>
                                </div>
                                <div class="function-config" style="display: <?php echo $this->is_option_active('seo', 'xml_sitemap') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" name="seo[auto_ping]" value="1" <?php checked($this->is_option_active('seo', 'auto_ping'), true); ?>>
                                        Ping automático a buscadores
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Análisis SEO en Tiempo Real -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Análisis SEO en Tiempo Real</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="seo[realtime_analysis]" value="1" <?php checked($this->is_option_active('seo', 'realtime_analysis')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Análisis mientras escribes en Gutenberg con puntuación en vivo y recomendaciones.</p>
                                </div>
                            </div>
                            
                            <!-- Vista Previa SERP -->
                            <div class="function-card priority-high">
                                <div class="function-header">
                                    <div class="function-title">
                                        <h3>Vista Previa SERP de Google</h3>
                                        <span class="priority-badge high">Alta Prioridad</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="seo[serp_preview]" value="1" <?php checked($this->is_option_active('seo', 'serp_preview')); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="function-description">
                                    <p>Vista previa exacta de cómo se verá tu contenido en Google (Desktop/Mobile).</p>
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="wpto-form-footer">
                            <button type="submit" class="button button-primary button-large">
                                <span class="dashicons dashicons-saved"></span>
                                Guardar Configuración de SEO
                            </button>
                            <span class="save-status"></span>
                        </div>
                    </form>
                </div>
                
            </div>
            
            <!-- Sección de Exportar/Importar/Reset -->
            <div style="background: white; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Gestión de Configuración</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" id="wpto-export-config" class="button button-secondary">
                        <span class="dashicons dashicons-download"></span> Exportar Configuración
                    </button>
                    <button type="button" id="wpto-import-config" class="button button-secondary">
                        <span class="dashicons dashicons-upload"></span> Importar Configuración
                    </button>
                    <input type="file" id="wpto-import-file" accept=".json" style="display: none;">
                    <button type="button" id="wpto-reset-config" class="button button-secondary" style="color: #d63638;">
                        <span class="dashicons dashicons-update"></span> Resetear Configuración
                    </button>
                </div>
                <div id="wpto-config-status" style="margin-top: 10px;"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizar página de estadísticas
     */
    public function render_stats_page() {
        $security_count = $this->count_active_functions('security');
        $optimization_count = $this->count_active_functions('optimization');
        $images_count = $this->count_active_functions('images');
        $seo_count = $this->count_active_functions('seo');
        
        global $wpdb;
        $recent_logs = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}wpto_activity_log ORDER BY timestamp DESC LIMIT 20",
            ARRAY_A
        );
        
        ?>
        <div class="wrap wpto-wrap">
            <h1>Estadísticas del Sistema</h1>
            
            <div class="wpto-header-stats" style="margin-top: 20px;">
                <div class="stat-card">
                    <span class="stat-icon dashicons dashicons-shield"></span>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo esc_html($security_count); ?></div>
                        <div class="stat-label">Funciones de Seguridad Activas</div>
        </div>
                </div>
                
                <div class="stat-card">
                    <span class="stat-icon dashicons dashicons-performance"></span>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo esc_html($optimization_count); ?></div>
                        <div class="stat-label">Optimizaciones Activas</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <span class="stat-icon dashicons dashicons-format-image"></span>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo esc_html($images_count); ?></div>
                        <div class="stat-label">Funciones de Imágenes Activas</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <span class="stat-icon dashicons dashicons-search"></span>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo esc_html($seo_count); ?></div>
                        <div class="stat-label">Funciones SEO Activas</div>
                    </div>
                </div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; margin-top: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Registro de Actividades Recientes</h2>
                <?php if (!empty($recent_logs)): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Acción</th>
                                <th>Detalles</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td><?php echo esc_html($log['timestamp']); ?></td>
                                    <td><?php echo esc_html($log['action']); ?></td>
                                    <td><?php echo esc_html($log['details']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $log['status'] === 'success' ? '#00a32a' : '#d63638'; ?>;">
                                            <?php echo esc_html(ucfirst($log['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No hay actividades registradas aún.</p>
                <?php endif; ?>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Estado del Sistema</h2>
                <p><strong>Estado de Caché:</strong> <span id="cache-status-display">Detectando...</span></p>
            </div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $.ajax({
                url: wptoAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpto_detect_cache',
                    nonce: wptoAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#cache-status-display').html(response.data.message);
                    }
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Renderizar página de conversión batch
     */
    public function render_batch_converter_page() {
        ?>
        <div class="wrap wpto-wrap">
            <h1>Conversión Batch de Imágenes</h1>
            <p class="description">Convierte todas las imágenes existentes a WebP/AVIF de una vez.</p>
            
            <div style="background: white; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Configuración</h2>
                <form id="wpto-batch-form">
                    <table class="form-table">
                        <tr>
                            <th><label>Formato de salida</label></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="format[]" value="webp" checked> WebP
                                </label>
                                <label style="margin-left: 20px;">
                                    <input type="checkbox" name="format[]" value="avif"> AVIF
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><label>Calidad</label></th>
                            <td>
                                <input type="range" name="quality" min="60" max="100" value="85" step="5">
                                <span class="range-value">85%</span>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="button" id="wpto-start-batch" class="button button-primary button-large">
                            Iniciar Conversión
                        </button>
                    </p>
                </form>
            </div>
            
            <div id="wpto-batch-progress" style="display: none; background: white; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Progreso</h2>
                <div style="background: #f0f0f1; border-radius: 4px; height: 30px; position: relative; overflow: hidden;">
                    <div id="wpto-progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: bold;">
                        <span id="wpto-progress-text">0%</span>
                    </div>
                </div>
                <p id="wpto-progress-status" style="margin-top: 10px;">Preparando...</p>
            </div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('input[type="range"]').on('input', function() {
                $(this).siblings('.range-value').text($(this).val() + '%');
            });
            
            $('#wpto-start-batch').on('click', function() {
                var formats = [];
                $('input[name="format[]"]:checked').each(function() {
                    formats.push($(this).val());
                });
                
                if (formats.length === 0) {
                    alert('Selecciona al menos un formato de salida.');
                    return;
                }
                
                $('#wpto-batch-progress').show();
                processBatchConversion(formats, $('input[name="quality"]').val(), 0);
            });
            
            function processBatchConversion(formats, quality, offset) {
                $.ajax({
                    url: wptoAdmin.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wpto_batch_convert',
                        nonce: wptoAdmin.nonce,
                        formats: formats,
                        quality: quality,
                        offset: offset
                    },
                    success: function(response) {
                        if (response.success) {
                            var data = response.data;
                            var progress = Math.round((data.processed / data.total) * 100);
                            
                            $('#wpto-progress-bar').css('width', progress + '%');
                            $('#wpto-progress-text').text(progress + '%');
                            $('#wpto-progress-status').text('Procesadas: ' + data.processed + ' / ' + data.total);
                            
                            if (data.processed < data.total) {
                                processBatchConversion(formats, quality, data.processed);
                            } else {
                                $('#wpto-progress-status').html('<strong style="color: #00a32a;">✓ Conversión completada</strong>');
                            }
                        } else {
                            $('#wpto-progress-status').html('<strong style="color: #d63638;">✗ Error: ' + response.data + '</strong>');
                        }
                    }
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * Renderizar página de logs
     */
    public function render_logs_page() {
        global $wpdb;
        
        // Obtener parámetros de filtro
        $log_type = isset($_GET['log_type']) ? sanitize_text_field($_GET['log_type']) : 'all';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $per_page = 50;
        $offset = ($page - 1) * $per_page;
        
        // Construir query para logs de actividad
        $where = array('1=1');
        if ($log_type !== 'all' && $log_type !== 'debug') {
            $where[] = $wpdb->prepare("log_type = %s", $log_type);
        }
        if ($status_filter !== 'all') {
            $where[] = $wpdb->prepare("status = %s", $status_filter);
        }
        if (!empty($search)) {
            $where[] = $wpdb->prepare("(action LIKE %s OR details LIKE %s)", '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%');
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Contar total
        $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wpto_activity_log WHERE {$where_clause}");
        $total_pages = ceil($total_logs / $per_page);
        
        // Obtener logs
        $activity_logs = array();
        if ($log_type !== 'debug') {
            $activity_logs = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}wpto_activity_log WHERE {$where_clause} ORDER BY timestamp DESC LIMIT %d OFFSET %d",
                    $per_page,
                    $offset
                ),
                ARRAY_A
            );
        }
        
        // Obtener debug.log
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';
        $debug_log_content = '';
        $debug_log_size = 0;
        $debug_log_exists = file_exists($debug_log_path);
        
        if ($debug_log_exists && $log_type === 'debug') {
            $debug_log_size = filesize($debug_log_path);
            // Leer últimas 1000 líneas para no sobrecargar
            if ($debug_log_size > 0) {
                $lines = file($debug_log_path);
                $debug_log_content = implode('', array_slice($lines, -1000));
            }
        }
        
        ?>
        <div class="wrap wpto-wrap">
            <h1>Logs del Sistema</h1>
            
            <!-- Filtros -->
            <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <form method="get" action="" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: end;">
                    <input type="hidden" name="page" value="wpto-logs">
                    
                    <div>
                        <label><strong>Tipo de Log:</strong></label><br>
                        <select name="log_type" style="width: 150px;">
                            <option value="all" <?php selected($log_type, 'all'); ?>>Todos</option>
                            <option value="system" <?php selected($log_type, 'system'); ?>>Sistema</option>
                            <option value="security" <?php selected($log_type, 'security'); ?>>Seguridad</option>
                            <option value="optimization" <?php selected($log_type, 'optimization'); ?>>Optimización</option>
                            <option value="images" <?php selected($log_type, 'images'); ?>>Imágenes</option>
                            <option value="seo" <?php selected($log_type, 'seo'); ?>>SEO</option>
                            <option value="debug" <?php selected($log_type, 'debug'); ?>>Debug.log</option>
                        </select>
                    </div>
                    
                    <?php if ($log_type !== 'debug'): ?>
                    <div>
                        <label><strong>Estado:</strong></label><br>
                        <select name="status" style="width: 120px;">
                            <option value="all" <?php selected($status_filter, 'all'); ?>>Todos</option>
                            <option value="success" <?php selected($status_filter, 'success'); ?>>Éxito</option>
                            <option value="warning" <?php selected($status_filter, 'warning'); ?>>Advertencia</option>
                            <option value="error" <?php selected($status_filter, 'error'); ?>>Error</option>
                        </select>
                    </div>
                    
                    <div>
                        <label><strong>Buscar:</strong></label><br>
                        <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Buscar en logs..." style="width: 200px;">
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <button type="submit" class="button button-primary">Filtrar</button>
                        <a href="<?php echo admin_url('admin.php?page=wpto-logs'); ?>" class="button">Limpiar</a>
                    </div>
                </form>
            </div>
            
            <?php if ($log_type === 'debug'): ?>
                <!-- Vista de Debug.log -->
                <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h2>Debug.log de WordPress</h2>
                        <div>
                            <?php if ($debug_log_exists): ?>
                                <span style="color: #666;">Tamaño: <?php echo size_format($debug_log_size); ?></span>
                                <button type="button" id="wpto-clear-debug-log" class="button button-secondary" style="margin-left: 10px;">
                                    Limpiar Debug.log
                                </button>
                                <button type="button" id="wpto-download-debug-log" class="button button-secondary" style="margin-left: 10px;">
                                    Descargar Debug.log
                                </button>
                            <?php else: ?>
                                <span style="color: #666;">Debug.log no existe. Activa WP_DEBUG_LOG en wp-config.php para generar logs.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($debug_log_exists && !empty($debug_log_content)): ?>
                        <div style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; max-height: 600px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.5;">
                            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"><?php echo esc_html($debug_log_content); ?></pre>
                        </div>
                    <?php elseif ($debug_log_exists && empty($debug_log_content)): ?>
                        <p>El archivo debug.log está vacío.</p>
                    <?php else: ?>
                        <div style="padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                            <p><strong>Debug.log no encontrado</strong></p>
                            <p>Para activar el debug.log, añade estas líneas a tu archivo <code>wp-config.php</code>:</p>
                            <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;">define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);</pre>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Vista de Logs de Actividad -->
                <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h2>Logs de Actividad (<?php echo number_format($total_logs); ?> registros)</h2>
                        <div>
                            <button type="button" id="wpto-export-logs" class="button button-secondary">
                                Exportar Logs
                            </button>
                            <button type="button" id="wpto-clear-logs" class="button button-secondary" style="margin-left: 10px;">
                                Limpiar Logs Antiguos
                            </button>
                        </div>
                    </div>
                    
                    <?php if (!empty($activity_logs)): ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Fecha/Hora</th>
                                    <th style="width: 100px;">Tipo</th>
                                    <th style="width: 120px;">Estado</th>
                                    <th>Acción</th>
                                    <th>Detalles</th>
                                    <th style="width: 100px;">Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activity_logs as $log): ?>
                                <tr>
                                    <td><?php echo esc_html(date_i18n('Y-m-d H:i:s', strtotime($log['timestamp']))); ?></td>
                                    <td>
                                        <span class="dashicons dashicons-<?php echo $log['log_type'] === 'security' ? 'shield' : ($log['log_type'] === 'system' ? 'admin-tools' : 'admin-generic'); ?>"></span>
                                        <?php echo esc_html(ucfirst($log['log_type'])); ?>
                                    </td>
                                    <td>
                                        <span style="padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase; 
                                            background: <?php echo $log['status'] === 'success' ? '#d4edda' : ($log['status'] === 'warning' ? '#fff3cd' : '#f8d7da'); ?>;
                                            color: <?php echo $log['status'] === 'success' ? '#155724' : ($log['status'] === 'warning' ? '#856404' : '#721c24'); ?>;">
                                            <?php echo esc_html($log['status']); ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo esc_html($log['action']); ?></strong></td>
                                    <td><?php echo esc_html($log['details']); ?></td>
                                    <td>
                                        <?php 
                                        if ($log['user_id'] > 0) {
                                            $user = get_user_by('id', $log['user_id']);
                                            echo $user ? esc_html($user->display_name) : 'N/A';
                                        } else {
                                            echo 'Sistema';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <!-- Paginación -->
                        <?php if ($total_pages > 1): ?>
                        <div style="margin-top: 20px; text-align: center;">
                            <?php
                            $base_url = admin_url('admin.php?page=wpto-logs');
                            $base_url .= '&log_type=' . urlencode($log_type);
                            $base_url .= '&status=' . urlencode($status_filter);
                            if (!empty($search)) {
                                $base_url .= '&search=' . urlencode($search);
                            }
                            
                            echo paginate_links(array(
                                'base' => $base_url . '&paged=%#%',
                                'format' => '',
                                'current' => $page,
                                'total' => $total_pages,
                                'prev_text' => '&laquo; Anterior',
                                'next_text' => 'Siguiente &raquo;'
                            ));
                            ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No se encontraron logs con los filtros seleccionados.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Exportar logs
            $('#wpto-export-logs').on('click', function() {
                const params = new URLSearchParams(window.location.search);
                params.set('action', 'wpto_export_logs');
                params.set('nonce', '<?php echo wp_create_nonce('wpto_export_logs'); ?>');
                
                window.location.href = wptoAdmin.ajaxurl + '?' + params.toString();
            });
            
            // Limpiar logs antiguos
            $('#wpto-clear-logs').on('click', function() {
                if (!confirm('¿Estás seguro de limpiar los logs antiguos (más de 30 días)? Esta acción no se puede deshacer.')) {
                    return;
                }
                
                $.ajax({
                    url: wptoAdmin.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wpto_clear_old_logs',
                        nonce: wptoAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Logs antiguos eliminados correctamente.');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.data || 'Error desconocido'));
                        }
                    }
                });
            });
            
            // Limpiar debug.log
            $('#wpto-clear-debug-log').on('click', function() {
                if (!confirm('¿Estás seguro de limpiar el debug.log? Esta acción no se puede deshacer.')) {
                    return;
                }
                
                $.ajax({
                    url: wptoAdmin.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wpto_clear_debug_log',
                        nonce: wptoAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Debug.log limpiado correctamente.');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.data || 'Error desconocido'));
                        }
                    }
                });
            });
            
            // Descargar debug.log
            $('#wpto-download-debug-log').on('click', function() {
                window.location.href = wptoAdmin.ajaxurl + '?action=wpto_download_debug_log&nonce=<?php echo wp_create_nonce('wpto_download_debug_log'); ?>';
            });
        });
        </script>
        <?php
    }

    /**
     * Renderizar página de edición masiva SEO
     */
    public function render_bulk_seo_page() {
        ?>
        <div class="wrap wpto-wrap" id="wpto-bulk-seo">
            <h1>Edición Masiva SEO</h1>

            <div class="wpto-bulk-filters">
                <select id="wpto-filter-post-type">
                    <?php
                    $post_types = get_post_types(array('public' => true), 'objects');
                    foreach ($post_types as $post_type) {
                        echo '<option value="' . esc_attr($post_type->name) . '">' . esc_html($post_type->label) . '</option>';
                    }
                    ?>
                </select>
                <select id="wpto-filter-status">
                    <option value="any">Todos los estados</option>
                    <option value="publish">Publicado</option>
                    <option value="draft">Borrador</option>
                </select>
                <select id="wpto-filter-seo">
                    <option value="all">Todos</option>
                    <option value="missing_title">Sin título SEO</option>
                    <option value="missing_desc">Sin meta descripción</option>
                    <option value="incomplete">Incompletos</option>
                    <option value="complete">Completos</option>
                </select>
                <select id="wpto-filter-focus">
                    <option value="any">Focus keyword: cualquiera</option>
                    <option value="missing">Focus keyword: vacío</option>
                    <option value="present">Focus keyword: presente</option>
                </select>
                <select id="wpto-filter-keywords">
                    <option value="any">Keywords: cualquiera</option>
                    <option value="missing">Keywords: vacío</option>
                    <option value="present">Keywords: presente</option>
                </select>
                <select id="wpto-filter-title-length">
                    <option value="any">Título SEO: cualquiera</option>
                    <option value="empty">Título SEO: vacío</option>
                    <option value="ok">Título SEO: óptimo</option>
                    <option value="short">Título SEO: corto</option>
                    <option value="long">Título SEO: largo</option>
                    <option value="out_of_range">Título SEO: fuera de rango</option>
                </select>
                <select id="wpto-filter-desc-length">
                    <option value="any">Meta desc: cualquiera</option>
                    <option value="empty">Meta desc: vacío</option>
                    <option value="ok">Meta desc: óptimo</option>
                    <option value="short">Meta desc: corto</option>
                    <option value="long">Meta desc: largo</option>
                    <option value="out_of_range">Meta desc: fuera de rango</option>
                </select>
                <select id="wpto-filter-per-page">
                    <option value="25">25 / página</option>
                    <option value="50">50 / página</option>
                    <option value="100">100 / página</option>
                </select>
                <input type="text" id="wpto-filter-search" placeholder="Buscar por título">
                <button class="button button-primary" id="wpto-bulk-load">Cargar Posts</button>
            </div>

            <div class="wpto-bulk-stats">
                <div class="wpto-bulk-stat"><strong>Total (página)</strong><div class="wpto-stat-total">0</div></div>
                <div class="wpto-bulk-stat"><strong>Sin Título</strong><div class="wpto-stat-missing-title">0</div></div>
                <div class="wpto-bulk-stat"><strong>Sin Descripción</strong><div class="wpto-stat-missing-desc">0</div></div>
                <div class="wpto-bulk-stat"><strong>Completos</strong><div class="wpto-stat-complete">0</div></div>
            </div>

            <div class="wpto-bulk-stats wpto-bulk-stats-global">
                <div class="wpto-bulk-stat"><strong>Total (global)</strong><div class="wpto-stat-total-global">0</div></div>
                <div class="wpto-bulk-stat"><strong>Sin Título (global)</strong><div class="wpto-stat-missing-title-global">0</div></div>
                <div class="wpto-bulk-stat"><strong>Sin Desc (global)</strong><div class="wpto-stat-missing-desc-global">0</div></div>
                <div class="wpto-bulk-stat"><strong>Completos (global)</strong><div class="wpto-stat-complete-global">0</div></div>
                <div class="wpto-bulk-stat wpto-bulk-stat-meta">
                    <strong>Última generación <span class="wpto-badge wpto-badge-info" id="wpto-stat-last-ms-badge" title="">i</span></strong>
                    <div class="wpto-stat-last-ms">—</div>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin:10px 0;">
                <button class="button button-secondary" id="wpto-bulk-generate-all">Generar Automáticamente Todos</button>
                <button class="button button-primary" id="wpto-bulk-save">Guardar Todos los Cambios</button>
                <div class="wpto-bulk-status" style="margin-left:10px; align-self:center;"></div>
            </div>

            <div class="wpto-bulk-pagination">
                <button class="button" id="wpto-bulk-prev">Anterior</button>
                <span id="wpto-bulk-page-info">Página 1 / 1</span>
                <button class="button" id="wpto-bulk-next">Siguiente</button>
            </div>

            <table class="wpto-bulk-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th style="width:220px;">Post</th>
                        <th>Título SEO</th>
                        <th>Meta Descripción</th>
                        <th>Keywords</th>
                        <th>Focus</th>
                        <th style="width:60px;">Auto</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * AJAX: Detectar sistema de caché
     */
    public function ajax_detect_cache() {
        check_ajax_referer('wpto_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        $cache_system = $this->detect_cache_system();
        
        wp_send_json_success(array(
            'message' => $cache_system['message'],
            'system' => $cache_system['system']
        ));
    }
    
    /**
     * Detectar sistema de caché
     */
    private function detect_cache_system() {
        if (class_exists('Redis')) {
            return array(
                'system' => 'redis',
                'message' => '<span style="color: #00a32a;">✓ Redis detectado y disponible</span>'
            );
        } elseif (class_exists('Memcached')) {
            return array(
                'system' => 'memcached',
                'message' => '<span style="color: #00a32a;">✓ Memcached detectado y disponible</span>'
            );
        } else {
            return array(
                'system' => 'transients',
                'message' => '<span style="color: #dba617;">⚠ Solo Transients de WordPress disponibles (Redis/Memcached no detectados)</span>'
            );
        }
    }
    
    /**
     * AJAX: Conversión batch de imágenes
     */
    public function ajax_batch_convert() {
        check_ajax_referer('wpto_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        $formats = isset($_POST['formats']) ? $_POST['formats'] : array();
        $quality = isset($_POST['quality']) ? intval($_POST['quality']) : 85;
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $batch_size = 10; // Procesar 10 imágenes por vez
        
        // Obtener imágenes
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => $batch_size,
            'offset' => $offset,
            'post_status' => 'inherit'
        );
        
        $attachments = get_posts($args);
        
        // Contar total de imágenes
        $count_args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        $total_attachments = get_posts($count_args);
        $total = count($total_attachments);
        
        $processed = $offset;
        
        foreach ($attachments as $attachment) {
            $file_path = get_attached_file($attachment->ID);
            if ($file_path && file_exists($file_path)) {
                if (in_array('webp', $formats)) {
                    $this->images->convert_to_webp($file_path, $quality);
                }
                if (in_array('avif', $formats)) {
                    $this->images->convert_to_avif($file_path, $quality);
                }
                $processed++;
            }
        }
        
        $this->log_activity('batch_conversion', sprintf('Procesadas %d imágenes', $processed - $offset), 'success');
        
        wp_send_json_success(array(
            'processed' => $processed,
            'total' => $total
        ));
    }
    
    /**
     * Registrar actividad en log
     */
    public function log_activity($action, $details = '', $status = 'success', $log_type = 'system') {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'wpto_activity_log',
            array(
                'action' => sanitize_text_field($action),
                'details' => sanitize_textarea_field($details),
                'status' => sanitize_text_field($status),
                'user_id' => get_current_user_id(),
                'log_type' => sanitize_text_field($log_type),
                'timestamp' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );
        
        // Disparar hook para notificaciones
        do_action('wpto_activity_logged', $action, $details, $status);
    }

    /**
     * Invalidar caché de stats globales al guardar contenido
     */
    public function invalidate_bulk_stats_cache($post_id, $post = null, $update = null) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        update_option('wpto_bulk_stats_version', time());
    }

    /**
     * Invalidar caché al borrar contenido
     */
    public function invalidate_bulk_stats_cache_simple($post_id) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        update_option('wpto_bulk_stats_version', time());
    }

    /**
     * Invalidar caché al cambiar estado de publicación
     */
    public function invalidate_bulk_stats_cache_transition($new_status, $old_status, $post) {
        if ($new_status === $old_status) {
            return;
        }
        if (!$post || empty($post->ID)) {
            return;
        }
        if (wp_is_post_revision($post->ID) || wp_is_post_autosave($post->ID)) {
            return;
        }
        update_option('wpto_bulk_stats_version', time());
    }
    
    /**
     * AJAX: Exportar configuración
     */
    public function ajax_export_config() {
        check_ajax_referer('wpto_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        $config = array(
            'security' => get_option('wpto_security_options', array()),
            'optimization' => get_option('wpto_optimization_options', array()),
            'images' => get_option('wpto_images_options', array()),
            'seo' => get_option('wpto_seo_options', array()),
            'exported_at' => current_time('mysql'),
            'version' => WPTO_VERSION
        );
        
        $this->log_activity('export_config', 'Configuración exportada', 'success');
        
        wp_send_json_success(array(
            'config' => $config,
            'json' => wp_json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ));
    }
    
    /**
     * AJAX: Importar configuración
     */
    public function ajax_import_config() {
        check_ajax_referer('wpto_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }

        if (empty($_POST['config_json'])) {
            wp_send_json_error('No se proporcionó configuración');
        }

        $config = json_decode(stripslashes($_POST['config_json']), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('JSON inválido: ' . json_last_error_msg());
        }

        // Validar estructura
        if (!is_array($config)) {
            wp_send_json_error('Configuración inválida');
        }

        // Lista de módulos válidos
        $valid_modules = array('security', 'optimization', 'images', 'seo');

        // Importar cada módulo con sanitización completa
        foreach ($valid_modules as $module) {
            if (isset($config[$module]) && is_array($config[$module])) {
                // Sanitizar todas las opciones antes de guardar
                $sanitized = $this->sanitize_import_options($config[$module]);
                update_option('wpto_' . $module . '_options', $sanitized);
            }
        }

        // Recargar opciones
        $this->load_options();

        $this->log_activity('import_config', 'Configuración importada correctamente', 'success');

        wp_send_json_success(array(
            'message' => 'Configuración importada correctamente'
        ));
    }

    /**
     * Sanitizar opciones importadas recursivamente
     */
    private function sanitize_import_options($options) {
        if (!is_array($options)) {
            return array();
        }

        $sanitized = array();

        foreach ($options as $key => $value) {
            // Validar que la clave sea alfanumérica con guiones bajos
            $clean_key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
            if (empty($clean_key) || $clean_key !== $key) {
                continue; // Saltar claves inválidas
            }

            if (is_array($value)) {
                // Recursivamente sanitizar arrays anidados
                $sanitized[$clean_key] = $this->sanitize_import_options($value);
            } elseif (is_bool($value)) {
                $sanitized[$clean_key] = $value;
            } elseif (is_numeric($value)) {
                $sanitized[$clean_key] = intval($value);
            } elseif ($value === '1' || $value === 'true' || $value === '0' || $value === 'false') {
                // Valores booleanos como string
                $sanitized[$clean_key] = ($value === '1' || $value === 'true') ? '1' : '0';
            } else {
                // Sanitizar strings - permitir texto pero escapar HTML
                $sanitized[$clean_key] = sanitize_textarea_field($value);
            }
        }

        return $sanitized;
    }
    
    /**
     * AJAX: Guardar opciones
     */
    public function ajax_save_options() {
        check_ajax_referer('wpto_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
            return;
        }

        if (empty($_POST['module'])) {
            wp_send_json_error('Módulo no especificado');
            return;
        }

        $module = sanitize_text_field($_POST['module']);

        // Validar que sea un módulo válido
        $valid_modules = array('security', 'optimization', 'images', 'seo');
        if (!in_array($module, $valid_modules)) {
            wp_send_json_error('Módulo inválido');
            return;
        }

        $options = isset($_POST['options']) ? $_POST['options'] : array();

        // Validar que options sea un array
        if (!is_array($options)) {
            wp_send_json_error('Formato de opciones inválido');
            return;
        }

        // Obtener opciones actuales
        $current_options = get_option('wpto_' . $module . '_options', array());
        if (!is_array($current_options)) {
            $current_options = array();
        }

        // Sanitizar nuevas opciones
        $sanitized_options = $this->sanitize_options($module, $options);

        // Procesar opciones: eliminar las que tienen valor '0' (checkboxes desmarcados)
        $final_options = array();
        foreach ($sanitized_options as $key => $value) {
            // Sanitizar la clave
            $key = sanitize_key($key);
            if (empty($key)) {
                continue;
            }

            // Solo incluir si el valor no es '0', false, o vacío (checkboxes desmarcados)
            if ($value !== '0' && $value !== false && $value !== '' && $value !== 0) {
                $final_options[$key] = $value;
            }
        }

        // Para campos que no son checkboxes principales, mantener los valores actuales si no se enviaron
        // Pero eliminar checkboxes que estaban activos y ahora no están
        foreach ($current_options as $key => $value) {
            // Si era un checkbox activo y no está en las nuevas opciones, eliminarlo
            if (($value === '1' || $value === true || $value === 1) && !isset($final_options[$key])) {
                // No incluir (se elimina)
                continue;
            }
            // Si no es un checkbox principal y no está en las nuevas opciones, mantenerlo
            if (!isset($final_options[$key]) && ($value !== '1' && $value !== true && $value !== 1)) {
                $final_options[$key] = $value;
            }
        }

        // Manejar cambios especiales de seguridad (2FA obligatorio)
        if ($module === 'security') {
            $prev_enforced = !empty($current_options['2fa_for_admins']);
            $next_enforced = !empty($final_options['2fa_for_admins']);
            if (!$prev_enforced && $next_enforced) {
                update_option('wpto_2fa_enforce_since', current_time('timestamp'));
            } elseif ($prev_enforced && !$next_enforced) {
                delete_option('wpto_2fa_enforce_since');
            } elseif ($next_enforced && !get_option('wpto_2fa_enforce_since')) {
                update_option('wpto_2fa_enforce_since', current_time('timestamp'));
            }
        }

        // Guardar en la base de datos
        $result = update_option('wpto_' . $module . '_options', $final_options);

        // update_option devuelve false si el valor no cambió, lo cual no es un error
        // Solo verificar si hay un error real de BD
        if ($result === false && $final_options !== $current_options) {
            wp_send_json_error('Error al guardar en la base de datos');
            return;
        }
        
        // Registrar en log
        $this->log_activity('save_options', sprintf('Configuración guardada para módulo: %s', $module), 'success');
        
        // Devolver conteos actualizados
        $counts = array(
            'security' => $this->count_active_functions('security'),
            'optimization' => $this->count_active_functions('optimization'),
            'images' => $this->count_active_functions('images'),
            'seo' => $this->count_active_functions('seo'),
        );
        
        wp_send_json_success(array(
            'message' => 'Configuración guardada correctamente',
            'module' => $module,
            'counts' => $counts
        ));
    }
    
    /**
     * AJAX: Obtener estado de funciones activas
     */
    public function ajax_get_status() {
        check_ajax_referer('wpto_nonce', 'nonce');
        
        $counts = array(
            'security' => $this->count_active_functions('security'),
            'optimization' => $this->count_active_functions('optimization'),
            'images' => $this->count_active_functions('images'),
            'seo' => $this->count_active_functions('seo'),
        );
        
        wp_send_json_success($counts);
    }
    
    /**
     * Añadir widget al dashboard
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'wpto_dashboard_widget',
            'WP Total Optimizer - Estado del Sistema',
            array($this, 'render_dashboard_widget')
        );
    }
    
    /**
     * Renderizar widget del dashboard
     */
    public function render_dashboard_widget() {
        $counts = array(
            'security' => $this->count_active_functions('security'),
            'optimization' => $this->count_active_functions('optimization'),
            'images' => $this->count_active_functions('images'),
            'seo' => $this->count_active_functions('seo'),
        );
        
        global $wpdb;
        $recent_logs = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}wpto_activity_log ORDER BY timestamp DESC LIMIT 5",
            ARRAY_A
        );
        
        $monitoring_status = $this->monitoring ? $this->monitoring->get_overall_status() : null;
        
        ?>
        <div style="padding: 10px 0;">
            <h3 style="margin-top: 0;">Funciones Activas</h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 15px;">
                <div style="padding: 10px; background: #f0f0f1; border-radius: 4px;">
                    <strong>Seguridad:</strong> <?php echo esc_html($counts['security']); ?>
                </div>
                <div style="padding: 10px; background: #f0f0f1; border-radius: 4px;">
                    <strong>Optimización:</strong> <?php echo esc_html($counts['optimization']); ?>
                </div>
                <div style="padding: 10px; background: #f0f0f1; border-radius: 4px;">
                    <strong>Imágenes:</strong> <?php echo esc_html($counts['images']); ?>
                </div>
                <div style="padding: 10px; background: #f0f0f1; border-radius: 4px;">
                    <strong>SEO:</strong> <?php echo esc_html($counts['seo']); ?>
                </div>
            </div>
            
            <?php if ($monitoring_status): ?>
            <h3>Estado del Sistema</h3>
            <div style="padding: 10px; background: <?php echo $monitoring_status['overall'] === 'success' ? '#d4edda' : ($monitoring_status['overall'] === 'warning' ? '#fff3cd' : '#f8d7da'); ?>; border-radius: 4px; margin-bottom: 15px;">
                <strong>Estado General:</strong> 
                <span style="text-transform: uppercase; color: <?php echo $monitoring_status['overall'] === 'success' ? '#155724' : ($monitoring_status['overall'] === 'warning' ? '#856404' : '#721c24'); ?>;">
                    <?php echo esc_html($monitoring_status['overall']); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <h3>Últimas Actividades</h3>
            <?php if (!empty($recent_logs)): ?>
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($recent_logs as $log): ?>
                <li style="margin-bottom: 5px;">
                    <strong><?php echo esc_html($log['action']); ?></strong><br>
                    <small style="color: #666;"><?php echo esc_html($log['details']); ?> - <?php echo esc_html(human_time_diff(strtotime($log['timestamp']), current_time('timestamp'))); ?> ago</small>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p>No hay actividades recientes.</p>
            <?php endif; ?>
            
            <p style="margin-top: 15px;">
                <a href="<?php echo admin_url('admin.php?page=wpto-control-panel'); ?>" class="button button-primary">
                    Ir al Panel de Control
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * AJAX: Resetear configuración
     */
    public function ajax_reset_config() {
        check_ajax_referer('wpto_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Eliminar todas las opciones
        delete_option('wpto_security_options');
        delete_option('wpto_optimization_options');
        delete_option('wpto_images_options');
        delete_option('wpto_seo_options');
        
        // Registrar en log
        $this->log_activity('reset_config', 'Configuración reseteada a valores por defecto', 'success');
        
        wp_send_json_success(array(
            'message' => 'Configuración reseteada correctamente'
        ));
    }
    
    /**
     * AJAX: Exportar logs
     */
    public function ajax_export_logs() {
        check_ajax_referer('wpto_export_logs', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        global $wpdb;
        
        // Obtener todos los logs
        $logs = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}wpto_activity_log ORDER BY timestamp DESC",
            ARRAY_A
        );
        
        // Generar CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=wpto-logs-' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, array('Fecha', 'Tipo', 'Estado', 'Acción', 'Detalles', 'Usuario ID', 'Usuario'));
        
        // Datos
        foreach ($logs as $log) {
            $user_name = 'Sistema';
            if ($log['user_id'] > 0) {
                $user = get_user_by('id', $log['user_id']);
                $user_name = $user ? $user->display_name : 'N/A';
            }
            
            fputcsv($output, array(
                $log['timestamp'],
                $log['log_type'],
                $log['status'],
                $log['action'],
                $log['details'],
                $log['user_id'],
                $user_name
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * AJAX: Limpiar logs antiguos
     */
    public function ajax_clear_old_logs() {
        check_ajax_referer('wpto_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        global $wpdb;
        
        // Eliminar logs más antiguos de 30 días
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->prefix}wpto_activity_log 
             WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        $this->log_activity('clear_old_logs', sprintf('Eliminados %d logs antiguos', $deleted), 'success');
        
        wp_send_json_success(array(
            'message' => sprintf('Se eliminaron %d logs antiguos', $deleted),
            'deleted' => $deleted
        ));
    }
    
    /**
     * AJAX: Limpiar debug.log
     */
    public function ajax_clear_debug_log() {
        check_ajax_referer('wpto_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';
        
        if (file_exists($debug_log_path)) {
            if (is_writable($debug_log_path)) {
                file_put_contents($debug_log_path, '');
                $this->log_activity('clear_debug_log', 'Debug.log limpiado', 'success');
                wp_send_json_success(array('message' => 'Debug.log limpiado correctamente'));
            } else {
                wp_send_json_error('No se puede escribir en debug.log. Verifica permisos.');
            }
        } else {
            wp_send_json_error('Debug.log no existe');
        }
    }
    
    /**
     * AJAX: Descargar debug.log
     */
    public function ajax_download_debug_log() {
        check_ajax_referer('wpto_download_debug_log', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';
        
        if (file_exists($debug_log_path)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename=debug-' . date('Y-m-d-His') . '.log');
            header('Content-Length: ' . filesize($debug_log_path));
            
            readfile($debug_log_path);
            exit;
        } else {
            wp_die('Debug.log no existe');
        }
    }

    /**
     * Evaluar filtros SEO para un post
     */
    private function seo_bulk_evaluate_post($post_id, $seo_filter, $focus_filter, $keywords_filter, $title_length_filter, $desc_length_filter) {
        $seo_title = (string) $this->seo->get_synced_seo_value($post_id, '_wpto_seo_title', 'rank_math_title', '_yoast_wpseo_title');
        $seo_desc = (string) $this->seo->get_synced_seo_value($post_id, '_wpto_seo_description', 'rank_math_description', '_yoast_wpseo_metadesc');
        $seo_keywords = get_post_meta($post_id, '_wpto_seo_keywords', true);
        $seo_keywords = is_string($seo_keywords) ? $seo_keywords : '';
        $focus_keyword = (string) $this->seo->get_synced_seo_value($post_id, '_wpto_focus_keyword', 'rank_math_focus_keyword', '_yoast_wpseo_focuskw');

        $title_length = mb_strlen($seo_title);
        $desc_length = mb_strlen($seo_desc);

        $missing_title = ($seo_title === '');
        $missing_desc = ($seo_desc === '');
        $complete = !$missing_title && !$missing_desc;

        $include = true;
        if ($seo_filter === 'missing_title' && !$missing_title) $include = false;
        if ($seo_filter === 'missing_desc' && !$missing_desc) $include = false;
        if ($seo_filter === 'incomplete' && $complete) $include = false;
        if ($seo_filter === 'complete' && !$complete) $include = false;

        if ($focus_filter === 'missing' && !empty($focus_keyword)) $include = false;
        if ($focus_filter === 'present' && empty($focus_keyword)) $include = false;

        if ($keywords_filter === 'missing' && !empty($seo_keywords)) $include = false;
        if ($keywords_filter === 'present' && empty($seo_keywords)) $include = false;

        if ($title_length_filter === 'empty' && $title_length > 0) $include = false;
        if ($title_length_filter === 'ok' && !($title_length >= 50 && $title_length <= 60)) $include = false;
        if ($title_length_filter === 'short' && !($title_length > 0 && $title_length < 30)) $include = false;
        if ($title_length_filter === 'long' && !($title_length > 70)) $include = false;
        if ($title_length_filter === 'out_of_range' && !($title_length > 0 && ($title_length < 30 || $title_length > 70))) $include = false;

        if ($desc_length_filter === 'empty' && $desc_length > 0) $include = false;
        if ($desc_length_filter === 'ok' && !($desc_length >= 150 && $desc_length <= 160)) $include = false;
        if ($desc_length_filter === 'short' && !($desc_length > 0 && $desc_length < 120)) $include = false;
        if ($desc_length_filter === 'long' && !($desc_length > 160)) $include = false;
        if ($desc_length_filter === 'out_of_range' && !($desc_length > 0 && ($desc_length < 120 || $desc_length > 160))) $include = false;

        return array(
            'include' => $include,
            'seo_title' => $seo_title,
            'seo_desc' => $seo_desc,
            'seo_keywords' => $seo_keywords,
            'focus_keyword' => $focus_keyword,
            'missing_title' => $missing_title,
            'missing_desc' => $missing_desc,
            'complete' => $complete
        );
    }

    /**
     * Comprobar si hay más resultados con filtros
     */
    private function seo_bulk_has_more($args, $cursor, $direction, $seo_filter, $focus_filter, $keywords_filter, $title_length_filter, $desc_length_filter) {
        if (empty($cursor)) {
            return false;
        }

        $batch_size = 200;
        $lower_bound = null;
        $upper_bound = null;
        if ($direction === 'prev') {
            $lower_bound = $cursor;
        } else {
            $upper_bound = $cursor;
        }

        while (true) {
            $query_args = $args;
            $query_args['posts_per_page'] = $batch_size;

            $filter_callback = null;
            if ($lower_bound || $upper_bound) {
                $filter_callback = function($where) use ($lower_bound, $upper_bound) {
                    global $wpdb;
                    if ($lower_bound) {
                        $where .= $wpdb->prepare(" AND {$wpdb->posts}.ID > %d", $lower_bound);
                    }
                    if ($upper_bound) {
                        $where .= $wpdb->prepare(" AND {$wpdb->posts}.ID < %d", $upper_bound);
                    }
                    return $where;
                };
                add_filter('posts_where', $filter_callback);
            }

            $query = new WP_Query($query_args);

            if ($filter_callback) {
                remove_filter('posts_where', $filter_callback);
            }

            if (empty($query->posts)) {
                return false;
            }

            foreach ($query->posts as $post_id) {
                $evaluation = $this->seo_bulk_evaluate_post(
                    $post_id,
                    $seo_filter,
                    $focus_filter,
                    $keywords_filter,
                    $title_length_filter,
                    $desc_length_filter
                );

                if (!empty($evaluation['include'])) {
                    return true;
                }
            }

            $last_post_id = end($query->posts);
            $upper_bound = $last_post_id ?: $upper_bound;

            if (count($query->posts) < $batch_size) {
                return false;
            }
        }
    }

    /**
     * AJAX: Cargar posts para edición masiva SEO
     */
    public function ajax_seo_bulk_load() {
        check_ajax_referer('wpto_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }

        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
        $post_status = isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'any';
        $seo_filter = isset($_POST['seo_filter']) ? sanitize_text_field($_POST['seo_filter']) : 'all';
        $focus_filter = isset($_POST['focus_filter']) ? sanitize_text_field($_POST['focus_filter']) : 'any';
        $keywords_filter = isset($_POST['keywords_filter']) ? sanitize_text_field($_POST['keywords_filter']) : 'any';
        $title_length_filter = isset($_POST['title_length_filter']) ? sanitize_text_field($_POST['title_length_filter']) : 'any';
        $desc_length_filter = isset($_POST['desc_length_filter']) ? sanitize_text_field($_POST['desc_length_filter']) : 'any';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $cursor = isset($_POST['cursor']) ? intval($_POST['cursor']) : 0;
        $direction = isset($_POST['direction']) ? sanitize_text_field($_POST['direction']) : 'next';
        $per_page = isset($_POST['per_page']) ? max(1, min(100, intval($_POST['per_page']))) : 25;
        if (!in_array($direction, array('next', 'prev'), true)) {
            $direction = 'next';
        }

        $args = array(
            'post_type' => $post_type,
            'post_status' => $post_status,
            'posts_per_page' => 0,
            'fields' => 'ids',
            's' => $search,
            'orderby' => 'ID',
            'order' => 'DESC'
        );

        $items = array();

        $stats = array(
            'total' => 0,
            'missing_title' => 0,
            'missing_desc' => 0,
            'complete' => 0
        );

        $batch_size = max(50, $per_page * 2);
        $has_next = false;
        $has_prev = false;
        $next_cursor = null;
        $prev_cursor = null;
        $lower_bound = null;
        $upper_bound = null;
        if ($cursor > 0) {
            if ($direction === 'prev') {
                $lower_bound = $cursor;
            } else {
                $upper_bound = $cursor;
            }
        }

        while (count($items) < $per_page) {
            $query_args = $args;
            $query_args['posts_per_page'] = $batch_size;

            $filter_callback = null;
            if ($lower_bound || $upper_bound) {
                $filter_callback = function($where) use ($lower_bound, $upper_bound) {
                    global $wpdb;
                    if ($lower_bound) {
                        $where .= $wpdb->prepare(" AND {$wpdb->posts}.ID > %d", $lower_bound);
                    }
                    if ($upper_bound) {
                        $where .= $wpdb->prepare(" AND {$wpdb->posts}.ID < %d", $upper_bound);
                    }
                    return $where;
                };
                add_filter('posts_where', $filter_callback);
            }

            $query = new WP_Query($query_args);

            if ($filter_callback) {
                remove_filter('posts_where', $filter_callback);
            }

            if (empty($query->posts)) {
                break;
            }

            foreach ($query->posts as $post_id) {
                $post = get_post($post_id);
                if (!$post) {
                    continue;
                }

                $evaluation = $this->seo_bulk_evaluate_post(
                    $post_id,
                    $seo_filter,
                    $focus_filter,
                    $keywords_filter,
                    $title_length_filter,
                    $desc_length_filter
                );

                if (empty($evaluation['include'])) {
                    continue;
                }

                $stats['total']++;
                if (!empty($evaluation['missing_title'])) $stats['missing_title']++;
                if (!empty($evaluation['missing_desc'])) $stats['missing_desc']++;
                if (!empty($evaluation['complete'])) $stats['complete']++;

                $items[] = array(
                    'id' => $post_id,
                    'title' => esc_html(get_the_title($post_id)),
                    'status' => $post->post_status,
                    'post_type' => $post->post_type,
                    'seo_title' => esc_html($evaluation['seo_title']),
                    'seo_description' => esc_textarea($evaluation['seo_desc']),
                    'seo_keywords' => esc_html($evaluation['seo_keywords']),
                    'focus_keyword' => esc_html($evaluation['focus_keyword']),
                    'excerpt' => esc_html(wp_trim_words(wp_strip_all_tags($post->post_excerpt ?: $post->post_content), 25))
                );

                if (count($items) >= $per_page) {
                    break 2;
                }
            }

            $last_post_id = end($query->posts);
            $upper_bound = $last_post_id ?: $upper_bound;

            if (count($query->posts) < $batch_size) {
                break;
            }
        }

        $first_id = !empty($items) ? $items[0]['id'] : null;
        $last_id = !empty($items) ? $items[count($items) - 1]['id'] : null;
        if ($last_id) {
            $has_next = $this->seo_bulk_has_more(
                $args,
                $last_id,
                'next',
                $seo_filter,
                $focus_filter,
                $keywords_filter,
                $title_length_filter,
                $desc_length_filter
            );
        }
        if ($first_id) {
            $has_prev = $this->seo_bulk_has_more(
                $args,
                $first_id,
                'prev',
                $seo_filter,
                $focus_filter,
                $keywords_filter,
                $title_length_filter,
                $desc_length_filter
            );
        }
        if ($cursor <= 0 && $direction === 'next') {
            $has_prev = false;
        }
        $next_cursor = $has_next ? $last_id : null;
        $prev_cursor = $has_prev ? $first_id : null;

        wp_send_json_success(array(
            'items' => $items,
            'stats' => $stats,
            'has_next' => $has_next,
            'has_prev' => $has_prev,
            'next_cursor' => $next_cursor,
            'prev_cursor' => $prev_cursor,
            'per_page' => $per_page
        ));
    }

    /**
     * AJAX: Estadísticas globales para edición masiva SEO
     */
    public function ajax_seo_bulk_stats() {
        check_ajax_referer('wpto_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }

        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
        $post_status = isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'any';
        $seo_filter = isset($_POST['seo_filter']) ? sanitize_text_field($_POST['seo_filter']) : 'all';
        $focus_filter = isset($_POST['focus_filter']) ? sanitize_text_field($_POST['focus_filter']) : 'any';
        $keywords_filter = isset($_POST['keywords_filter']) ? sanitize_text_field($_POST['keywords_filter']) : 'any';
        $title_length_filter = isset($_POST['title_length_filter']) ? sanitize_text_field($_POST['title_length_filter']) : 'any';
        $desc_length_filter = isset($_POST['desc_length_filter']) ? sanitize_text_field($_POST['desc_length_filter']) : 'any';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        $version = intval(get_option('wpto_bulk_stats_version', 0));
        $cache_key = 'wpto_bulk_stats_' . md5(wp_json_encode(array(
            'blog' => get_current_blog_id(),
            'version' => $version,
            'post_type' => $post_type,
            'post_status' => $post_status,
            'seo_filter' => $seo_filter,
            'focus_filter' => $focus_filter,
            'keywords_filter' => $keywords_filter,
            'title_length_filter' => $title_length_filter,
            'desc_length_filter' => $desc_length_filter,
            'search' => $search
        )));

        $cached = get_transient($cache_key);
        $meta = array(
            'last_ms' => intval(get_option('wpto_bulk_stats_last_ms', 0)),
            'last_at' => get_option('wpto_bulk_stats_last_at', '')
        );
        if ($cached !== false) {
            wp_send_json_success(array(
                'stats' => $cached,
                'meta' => $meta,
                'cached' => true
            ));
        }

        $start_time = microtime(true);

        $args = array(
            'post_type' => $post_type,
            'post_status' => $post_status,
            'posts_per_page' => 0,
            'fields' => 'ids',
            's' => $search,
            'orderby' => 'ID',
            'order' => 'DESC'
        );

        $stats = array(
            'total' => 0,
            'missing_title' => 0,
            'missing_desc' => 0,
            'complete' => 0
        );

        $batch_size = 500;
        $last_id = null;

        while (true) {
            $query_args = $args;
            $query_args['posts_per_page'] = $batch_size;

            $filter_callback = null;
            if ($last_id) {
                $filter_callback = function($where) use ($last_id) {
                    global $wpdb;
                    return $where . $wpdb->prepare(" AND {$wpdb->posts}.ID < %d", $last_id);
                };
                add_filter('posts_where', $filter_callback);
            }

            $query = new WP_Query($query_args);

            if ($filter_callback) {
                remove_filter('posts_where', $filter_callback);
            }

            if (empty($query->posts)) {
                break;
            }

            foreach ($query->posts as $post_id) {
                $evaluation = $this->seo_bulk_evaluate_post(
                    $post_id,
                    $seo_filter,
                    $focus_filter,
                    $keywords_filter,
                    $title_length_filter,
                    $desc_length_filter
                );

                if (empty($evaluation['include'])) {
                    continue;
                }

                $stats['total']++;
                if (!empty($evaluation['missing_title'])) $stats['missing_title']++;
                if (!empty($evaluation['missing_desc'])) $stats['missing_desc']++;
                if (!empty($evaluation['complete'])) $stats['complete']++;
            }

            $last_post_id = end($query->posts);
            $last_id = $last_post_id ?: $last_id;

            if (count($query->posts) < $batch_size) {
                break;
            }
        }

        $duration_ms = (int) round((microtime(true) - $start_time) * 1000);
        update_option('wpto_bulk_stats_last_ms', $duration_ms);
        update_option('wpto_bulk_stats_last_at', current_time('mysql'));
        $meta = array(
            'last_ms' => $duration_ms,
            'last_at' => get_option('wpto_bulk_stats_last_at', '')
        );
        $this->log_activity(
            'bulk_seo_stats',
            sprintf('Stats globales generadas en %d ms (total %d)', $duration_ms, $stats['total']),
            'success',
            'seo'
        );

        set_transient($cache_key, $stats, 300);
        wp_send_json_success(array(
            'stats' => $stats,
            'meta' => $meta,
            'cached' => false
        ));
    }

    /**
     * AJAX: Guardar cambios de edición masiva SEO
     */
    public function ajax_seo_bulk_save() {
        check_ajax_referer('wpto_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }

        $items = isset($_POST['items']) ? $_POST['items'] : array();
        if (!is_array($items)) {
            wp_send_json_error('Formato inválido');
        }

        $updated = 0;

        foreach ($items as $item) {
            $post_id = isset($item['id']) ? intval($item['id']) : 0;
            if ($post_id <= 0) {
                continue;
            }

            $title = isset($item['title']) ? sanitize_text_field($item['title']) : '';
            $desc = isset($item['description']) ? sanitize_textarea_field($item['description']) : '';
            $keywords = isset($item['keywords']) ? sanitize_text_field($item['keywords']) : '';
            $focus = isset($item['focus']) ? sanitize_text_field($item['focus']) : '';

            // Guardar con sincronización
            $this->seo->set_synced_seo_value($post_id, '_wpto_seo_title', $title, 'rank_math_title', '_yoast_wpseo_title');
            $this->seo->set_synced_seo_value($post_id, '_wpto_seo_description', $desc, 'rank_math_description', '_yoast_wpseo_metadesc');
            $this->seo->set_synced_seo_value($post_id, '_wpto_focus_keyword', $focus, 'rank_math_focus_keyword', '_yoast_wpseo_focuskw');
            update_post_meta($post_id, '_wpto_seo_keywords', $keywords);

            // Actualizar warnings
            $title_length = mb_strlen($title);
            if ($title_length > 0 && ($title_length < 30 || $title_length > 70)) {
                update_post_meta($post_id, '_wpto_seo_title_warning', true);
            } else {
                delete_post_meta($post_id, '_wpto_seo_title_warning');
            }
            $desc_length = mb_strlen($desc);
            if ($desc_length > 0 && ($desc_length < 120 || $desc_length > 160)) {
                update_post_meta($post_id, '_wpto_seo_description_warning', true);
            } else {
                delete_post_meta($post_id, '_wpto_seo_description_warning');
            }

            $updated++;
        }

        $this->log_activity('bulk_seo_save', sprintf('Edición masiva SEO: %d posts actualizados', $updated), 'success', 'seo');
        update_option('wpto_bulk_stats_version', time());

        wp_send_json_success(array(
            'updated' => $updated
        ));
    }

    /**
     * AJAX: Ejecutar escaneo de monitoreo de archivos
     */
    public function ajax_run_file_scan() {
        check_ajax_referer('wpto_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }

        if (!$this->security) {
            wp_send_json_error('Módulo de seguridad no disponible');
        }

        $state = $this->security->run_file_monitoring();
        if (!is_array($state)) {
            wp_send_json_error('No se pudo ejecutar el escaneo');
        }

        // No exponer hashes completos en respuesta
        if (isset($state['hashes'])) {
            unset($state['hashes']);
        }
        if (!empty($state['last_scan'])) {
            $state['last_scan'] = date_i18n('Y-m-d H:i:s', strtotime($state['last_scan']));
        }

        wp_send_json_success($state);
    }

    /**
     * AJAX: Obtener últimos cambios detectados en archivos
     */
    public function ajax_get_file_changes() {
        check_ajax_referer('wpto_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }

        $state = get_option('wpto_file_monitor_state', array());
        $last_scan = !empty($state['last_scan']) ? date_i18n('Y-m-d H:i:s', strtotime($state['last_scan'])) : '';
        $last_status = !empty($state['last_status']) ? $state['last_status'] : 'n/a';
        $last_summary = !empty($state['last_summary']) ? $state['last_summary'] : 'Sin datos';
        $count = !empty($state['count']) ? intval($state['count']) : 0;
        $last_changes = !empty($state['last_changes']) ? $state['last_changes'] : array(
            'modified' => array(),
            'added' => array(),
            'deleted' => array()
        );

        wp_send_json_success(array(
            'last_scan' => $last_scan,
            'last_status' => $last_status,
            'last_summary' => $last_summary,
            'count' => $count,
            'last_changes' => $last_changes
        ));
    }
    
    /**
     * Contar funciones activas por módulo
     */
    private function count_active_functions($module) {
        $options = get_option('wpto_' . $module . '_options', array());
        $count = 0;
        
        // Lista de funciones principales por módulo (solo checkboxes principales, no subcampos)
        $main_functions = array(
            'security' => array(
                'custom_login_url', 'brute_force_protection', 'two_factor_auth', 
                'auto_hardening', 'file_monitoring', 'session_management', 'basic_waf'
            ),
            'optimization' => array(
                'database_optimization', 'disable_unnecessary', 'minification', 
                'lazy_loading', 'dns_prefetch', 'gutenberg_optimization', 
                'object_caching', 'code_cleanup'
            ),
            'images' => array(
                'webp_conversion', 'size_control', 'overwrite_duplicates', 
                'complete_deletion', 'image_optimization', 'auto_alt', 
                'contextual_alt', 'batch_conversion'
            ),
            'seo' => array(
                'heading_panel', 'meta_fields', 'schema_markup', 
                'xml_sitemap', 'realtime_analysis', 'serp_preview'
            )
        );
        
        // Contar solo funciones principales activas
        if (isset($main_functions[$module])) {
            foreach ($main_functions[$module] as $function_key) {
                if (isset($options[$function_key])) {
                    $value = $options[$function_key];
                    if ($value === '1' || $value === true || $value === 1) {
                        $count++;
                    }
                }
            }
        } else {
            // Fallback: contar todas las opciones activas (método anterior)
        foreach ($options as $key => $value) {
                // Ignorar subcampos (que tienen guiones bajos o son arrays)
                if (strpos($key, '_') === false && !is_array($value)) {
                    if ($value === '1' || $value === true || $value === 1) {
                $count++;
                    }
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Sanitizar opciones
     */
    private function sanitize_options($module, $options) {
        $sanitized = array();
        
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize_options($module, $value);
            } else {
                // Sanitizar según tipo
                if (is_numeric($value)) {
                    $sanitized[$key] = intval($value);
                } elseif ($value === '1' || $value === 'true') {
                    $sanitized[$key] = '1';
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Mostrar avisos de administración
     */
    public function admin_notices() {
        $screen = get_current_screen();
        
        if (strpos($screen->id, 'wpto-') === false) {
            return;
        }
        
        // Verificar si hay configuraciones críticas desactivadas
        $security_options = get_option('wpto_security_options', array());
        
        if (empty($security_options['brute_force_protection'])) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>WP Total Optimizer:</strong> 
                    La protección contra fuerza bruta está desactivada. 
                    <a href="#security">Actívala ahora</a> para mayor seguridad.
                </p>
            </div>
            <?php
        }
    }
}

// Inicializar el plugin
function wpto_init() {
    return WP_Total_Optimizer::get_instance();
}

add_action('plugins_loaded', 'wpto_init');

// Hook de activación
register_activation_hook(__FILE__, function() {
    // Crear opciones por defecto
    add_option('wpto_security_options', array());
    add_option('wpto_optimization_options', array());
    add_option('wpto_images_options', array());
    add_option('wpto_seo_options', array());
    
    // Crear tablas necesarias si no existen
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpto_activity_log (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        action varchar(255) NOT NULL,
        details text,
        status varchar(20) DEFAULT 'success',
        user_id bigint(20) DEFAULT 0,
        log_type varchar(50) DEFAULT 'system',
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY log_type (log_type),
        KEY timestamp (timestamp)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});

// Hook de desactivación
register_deactivation_hook(__FILE__, function() {
    // Limpiar tareas programadas
    wp_clear_scheduled_hook('wpto_daily_health_check');
    wp_clear_scheduled_hook('wpto_weekly_pagespeed_check');
    wp_clear_scheduled_hook('wpto_db_cleanup');
    wp_clear_scheduled_hook('wpto_file_monitor_scan');
});
