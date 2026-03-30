<?php
/**
 * Plugin Name: Inmopress Emails
 * Description: Sistema completo de gestión de emails SMTP/IMAP integrado en el CRM
 * Version: 1.0.0
 * Author: Inmopress
 */

if (!defined('ABSPATH')) {
    exit;
}

define('INMOPRESS_EMAILS_PATH', plugin_dir_path(__FILE__));
define('INMOPRESS_EMAILS_URL', plugin_dir_url(__FILE__));
define('INMOPRESS_EMAILS_VERSION', '1.0.0');

class Inmopress_Emails
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
        
        // Crear tablas al activar
        register_activation_hook(__FILE__, array($this, 'create_tables'));
    }

    /**
     * Crear tablas de base de datos
     */
    public function create_tables()
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabla de cola de emails
        $table_queue = $wpdb->prefix . 'inmopress_email_queue';
        $sql_queue = "CREATE TABLE IF NOT EXISTS {$table_queue} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            to_email varchar(255) NOT NULL,
            to_name varchar(255),
            from_email varchar(255) NOT NULL,
            subject varchar(500) NOT NULL,
            body_html longtext NOT NULL,
            body_text text,
            attachments longtext,
            priority tinyint(1) DEFAULT 5,
            status varchar(20) DEFAULT 'pending',
            attempts tinyint(2) DEFAULT 0,
            last_error text,
            scheduled_at datetime,
            sent_at datetime,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY priority (priority)
        ) $charset_collate;";
        dbDelta($sql_queue);
        
        // Tabla de plantillas
        $table_templates = $wpdb->prefix . 'inmopress_email_templates';
        $sql_templates = "CREATE TABLE IF NOT EXISTS {$table_templates} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) UNIQUE NOT NULL,
            subject varchar(500) NOT NULL,
            body_html longtext NOT NULL,
            variables text,
            category varchar(100) DEFAULT 'general',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY slug (slug)
        ) $charset_collate;";
        dbDelta($sql_templates);
        
        // Tabla de threads
        $table_threads = $wpdb->prefix . 'inmopress_email_threads';
        $sql_threads = "CREATE TABLE IF NOT EXISTS {$table_threads} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            thread_id varchar(255) UNIQUE NOT NULL,
            subject varchar(500) NOT NULL,
            participants text NOT NULL,
            message_count int(11) DEFAULT 1,
            last_message_id bigint(20),
            last_message_at datetime,
            related_type varchar(50),
            related_id bigint(20),
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY thread_id (thread_id)
        ) $charset_collate;";
        dbDelta($sql_threads);
        
        // Crear plantillas por defecto
        $template_engine = Inmopress_Template_Engine::get_instance();
        $template_engine->create_default_templates();
    }

    private function load_dependencies()
    {
        require_once INMOPRESS_EMAILS_PATH . 'includes/class-email-manager.php';
        require_once INMOPRESS_EMAILS_PATH . 'includes/class-smtp-sender.php';
        require_once INMOPRESS_EMAILS_PATH . 'includes/class-imap-receiver.php';
        require_once INMOPRESS_EMAILS_PATH . 'includes/class-email-parser.php';
        require_once INMOPRESS_EMAILS_PATH . 'includes/class-thread-manager.php';
        require_once INMOPRESS_EMAILS_PATH . 'includes/class-template-engine.php';
        require_once INMOPRESS_EMAILS_PATH . 'includes/class-auto-associator.php';
        require_once INMOPRESS_EMAILS_PATH . 'includes/class-email-queue.php';
    }

    private function init_hooks()
    {
        // Registrar CPT impress_message
        add_action('init', array($this, 'register_message_cpt'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Cron para procesar cola y recibir emails
        add_action('inmopress_process_email_queue', array($this, 'process_email_queue'));
        add_action('inmopress_check_inbox', array($this, 'check_inbox'));
        $this->schedule_crons();

        // AJAX handlers
        add_action('wp_ajax_inmopress_send_email', array($this, 'ajax_send_email'));
        add_action('wp_ajax_inmopress_save_template', array($this, 'ajax_save_template'));

        // Hook para automatizaciones
        add_action('inmopress_automation_send_email', array($this, 'handle_automation_email'), 10, 1);
    }

    /**
     * Registrar CPT impress_message
     */
    public function register_message_cpt()
    {
        $labels = array(
            'name' => 'Mensajes',
            'singular_name' => 'Mensaje',
            'menu_name' => 'Emails',
            'add_new' => 'Nuevo Mensaje',
            'add_new_item' => 'Añadir Nuevo Mensaje',
            'edit_item' => 'Editar Mensaje',
            'new_item' => 'Nuevo Mensaje',
            'view_item' => 'Ver Mensaje',
            'search_items' => 'Buscar Mensajes',
            'not_found' => 'No se encontraron mensajes',
            'not_found_in_trash' => 'No se encontraron mensajes en la papelera',
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'supports' => array('title', 'editor'),
            'has_archive' => false,
        );

        register_post_type('impress_message', $args);
    }

    /**
     * Añadir menú admin
     */
    public function add_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=impress_property',
            'Emails',
            'Emails',
            'edit_posts',
            'inmopress-emails',
            array($this, 'render_emails_page')
        );

        add_submenu_page(
            'edit.php?post_type=impress_property',
            'Plantillas Email',
            'Plantillas Email',
            'manage_options',
            'inmopress-email-templates',
            array($this, 'render_templates_page')
        );

        add_submenu_page(
            'edit.php?post_type=impress_property',
            'Configuración Email',
            'Config Email',
            'manage_options',
            'inmopress-email-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Renderizar página principal de emails
     */
    public function render_emails_page()
    {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'inbox';
        
        if ($tab === 'compose') {
            include INMOPRESS_EMAILS_PATH . 'admin/views/compose.php';
        } else {
            include INMOPRESS_EMAILS_PATH . 'admin/views/inbox.php';
        }
    }

    /**
     * Renderizar página de plantillas
     */
    public function render_templates_page()
    {
        include INMOPRESS_EMAILS_PATH . 'admin/views/templates.php';
    }

    /**
     * Renderizar página de configuración
     */
    public function render_settings_page()
    {
        // Guardar configuración
        if (isset($_POST['save_email_settings']) && check_admin_referer('inmopress_email_settings')) {
            update_option('inmopress_smtp_host', sanitize_text_field($_POST['smtp_host']));
            update_option('inmopress_smtp_port', intval($_POST['smtp_port']));
            update_option('inmopress_smtp_username', sanitize_text_field($_POST['smtp_username']));
            update_option('inmopress_smtp_password', sanitize_text_field($_POST['smtp_password']));
            update_option('inmopress_smtp_encryption', sanitize_text_field($_POST['smtp_encryption']));
            update_option('inmopress_smtp_from_email', sanitize_email($_POST['smtp_from_email']));
            update_option('inmopress_smtp_from_name', sanitize_text_field($_POST['smtp_from_name']));

            // IMAP
            update_option('inmopress_imap_host', sanitize_text_field($_POST['imap_host']));
            update_option('inmopress_imap_port', intval($_POST['imap_port']));
            update_option('inmopress_imap_username', sanitize_text_field($_POST['imap_username']));
            update_option('inmopress_imap_password', sanitize_text_field($_POST['imap_password']));
            update_option('inmopress_imap_enabled', isset($_POST['imap_enabled']) ? 1 : 0);

            echo '<div class="notice notice-success"><p>Configuración guardada.</p></div>';
        }

        include INMOPRESS_EMAILS_PATH . 'admin/views/settings.php';
    }

    /**
     * Programar crons
     */
    private function schedule_crons()
    {
        if (!wp_next_scheduled('inmopress_process_email_queue')) {
            wp_schedule_event(time(), 'inmopress_minute', 'inmopress_process_email_queue');
        }

        if (!wp_next_scheduled('inmopress_check_inbox')) {
            wp_schedule_event(time(), 'inmopress_5min', 'inmopress_check_inbox');
        }

        // Registrar intervalos personalizados
        add_filter('cron_schedules', array($this, 'add_cron_intervals'));
    }

    /**
     * Añadir intervalos de cron personalizados
     */
    public function add_cron_intervals($schedules)
    {
        $schedules['inmopress_minute'] = array(
            'interval' => 60,
            'display' => 'Cada minuto',
        );
        $schedules['inmopress_5min'] = array(
            'interval' => 300,
            'display' => 'Cada 5 minutos',
        );
        return $schedules;
    }

    /**
     * Procesar cola de emails
     */
    public function process_email_queue()
    {
        $queue = Inmopress_Email_Queue::get_instance();
        $queue->process_queue();
    }

    /**
     * Revisar buzón IMAP
     */
    public function check_inbox()
    {
        if (!get_option('inmopress_imap_enabled', 0)) {
            return;
        }

        $receiver = Inmopress_IMAP_Receiver::get_instance();
        $receiver->check_inbox();
    }

    /**
     * AJAX: Enviar email
     */
    public function ajax_send_email()
    {
        check_ajax_referer('inmopress_email_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $manager = Inmopress_Email_Manager::get_instance();
        $result = $manager->send_email(array(
            'to_email' => sanitize_email($_POST['to_email']),
            'to_name' => sanitize_text_field($_POST['to_name']),
            'subject' => sanitize_text_field($_POST['subject']),
            'body_html' => wp_kses_post($_POST['body_html']),
            'body_text' => sanitize_textarea_field($_POST['body_text']),
        ));

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => 'Email enviado correctamente'));
    }

    /**
     * AJAX: Guardar plantilla
     */
    public function ajax_save_template()
    {
        check_ajax_referer('inmopress_email_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $engine = Inmopress_Template_Engine::get_instance();
        $result = $engine->save_template(array(
            'name' => sanitize_text_field($_POST['name']),
            'slug' => sanitize_title($_POST['slug']),
            'subject' => sanitize_text_field($_POST['subject']),
            'body_html' => wp_kses_post($_POST['body_html']),
            'category' => sanitize_text_field($_POST['category']),
        ));

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => 'Plantilla guardada'));
    }

    /**
     * Manejar email de automatización
     */
    public function handle_automation_email($email_data)
    {
        $manager = Inmopress_Email_Manager::get_instance();
        $manager->send_email($email_data);
    }
}

function inmopress_emails()
{
    return Inmopress_Emails::get_instance();
}
add_action('plugins_loaded', 'inmopress_emails');
