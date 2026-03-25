<?php
/**
 * WPTO Notifications Module
 * Sistema de notificaciones por email
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPTO_Notifications {

    private $options;

    // Rate limiting: máximo de emails por tipo en un período
    private $rate_limits = array(
        'security' => array('max' => 5, 'period' => 3600),    // 5 por hora
        'errors' => array('max' => 10, 'period' => 3600),     // 10 por hora
        'changes' => array('max' => 20, 'period' => 3600),    // 20 por hora
    );

    public function __construct() {
        $this->options = get_option('wpto_notifications_options', array());
        $this->init_hooks();
    }

    private function init_hooks() {
        // Hook personalizado para cuando se registra una actividad
        add_action('wpto_activity_logged', array($this, 'check_and_send_notification'), 10, 3);

        // Resumen semanal
        if (!empty($this->options['weekly_summary'])) {
            if (!wp_next_scheduled('wpto_weekly_summary')) {
                wp_schedule_event(time(), 'weekly', 'wpto_weekly_summary');
            }
            add_action('wpto_weekly_summary', array($this, 'send_weekly_summary'));
        }
    }

    /**
     * Verificar rate limit antes de enviar email
     */
    private function check_rate_limit($type) {
        if (!isset($this->rate_limits[$type])) {
            return true; // Sin límite definido
        }

        $limit = $this->rate_limits[$type];
        $transient_key = 'wpto_email_count_' . $type;
        $count_data = get_transient($transient_key);

        if ($count_data === false) {
            // Primera notificación del período
            set_transient($transient_key, array('count' => 1, 'first' => time()), $limit['period']);
            return true;
        }

        if ($count_data['count'] >= $limit['max']) {
            // Límite alcanzado, enviar resumen si es la primera vez que se alcanza
            if ($count_data['count'] == $limit['max']) {
                $this->send_rate_limit_notice($type, $limit['max'], $limit['period']);
                // Incrementar para no enviar más avisos
                $count_data['count']++;
                $remaining = $limit['period'] - (time() - $count_data['first']);
                set_transient($transient_key, $count_data, max(1, $remaining));
            }
            return false;
        }

        // Incrementar contador
        $count_data['count']++;
        $remaining = $limit['period'] - (time() - $count_data['first']);
        set_transient($transient_key, $count_data, max(1, $remaining));

        return true;
    }

    /**
     * Enviar aviso de rate limit alcanzado
     */
    private function send_rate_limit_notice($type, $max, $period) {
        $email = !empty($this->options['notification_email']) ? $this->options['notification_email'] : get_option('admin_email');
        $period_text = $period >= 3600 ? round($period / 3600) . ' hora(s)' : round($period / 60) . ' minuto(s)';

        $subject = '[' . get_bloginfo('name') . '] Límite de notificaciones alcanzado - WP Total Optimizer';
        $message = "Se ha alcanzado el límite de notificaciones por email.\n\n";
        $message .= "Tipo: " . ucfirst($type) . "\n";
        $message .= "Límite: " . $max . " emails por " . $period_text . "\n";
        $message .= "Fecha: " . current_time('mysql') . "\n\n";
        $message .= "Las notificaciones adicionales de este tipo se omitirán temporalmente.\n";
        $message .= "Por favor, revisa los logs del plugin para ver todos los eventos.\n";
        $message .= "Panel de logs: " . admin_url('admin.php?page=wpto-logs') . "\n";

        wp_mail($email, $subject, $message);
    }

    /**
     * Verificar y enviar notificación
     */
    public function check_and_send_notification($action, $details, $status) {
        if (empty($this->options['email_notifications'])) {
            return;
        }

        $email = !empty($this->options['notification_email']) ? $this->options['notification_email'] : get_option('admin_email');

        // Verificar qué tipos de notificaciones enviar
        $send_for = !empty($this->options['notification_types']) ? $this->options['notification_types'] : array();

        // Notificaciones de seguridad
        if (in_array('security', $send_for) && $status === 'error' && strpos($action, 'security') !== false) {
            if ($this->check_rate_limit('security')) {
                $this->send_security_alert($email, $action, $details);
            }
        }

        // Notificaciones de errores críticos
        if (in_array('errors', $send_for) && $status === 'error') {
            if ($this->check_rate_limit('errors')) {
                $this->send_error_notification($email, $action, $details);
            }
        }

        // Notificaciones de cambios importantes
        if (in_array('changes', $send_for) && in_array($action, array('save_options', 'import_config', 'reset_config'))) {
            if ($this->check_rate_limit('changes')) {
                $this->send_change_notification($email, $action, $details);
            }
        }
    }
    
    /**
     * Enviar alerta de seguridad
     */
    private function send_security_alert($email, $action, $details) {
        $subject = '[' . get_bloginfo('name') . '] Alerta de Seguridad - WP Total Optimizer';
        $message = "Se ha detectado una actividad de seguridad:\n\n";
        $message .= "Acción: " . $action . "\n";
        $message .= "Detalles: " . $details . "\n";
        $message .= "Fecha: " . current_time('mysql') . "\n";
        $message .= "URL: " . home_url() . "\n\n";
        $message .= "Por favor, revisa tu sitio inmediatamente.\n";
        
        wp_mail($email, $subject, $message);
    }
    
    /**
     * Enviar notificación de error
     */
    private function send_error_notification($email, $action, $details) {
        $subject = '[' . get_bloginfo('name') . '] Error Detectado - WP Total Optimizer';
        $message = "Se ha detectado un error en el sistema:\n\n";
        $message .= "Acción: " . $action . "\n";
        $message .= "Detalles: " . $details . "\n";
        $message .= "Fecha: " . current_time('mysql') . "\n";
        $message .= "URL: " . home_url() . "\n\n";
        $message .= "Por favor, revisa los logs para más información.\n";
        
        wp_mail($email, $subject, $message);
    }
    
    /**
     * Enviar notificación de cambio
     */
    private function send_change_notification($email, $action, $details) {
        $subject = '[' . get_bloginfo('name') . '] Cambio de Configuración - WP Total Optimizer';
        $message = "Se ha realizado un cambio importante en la configuración:\n\n";
        $message .= "Acción: " . $action . "\n";
        $message .= "Detalles: " . $details . "\n";
        $message .= "Fecha: " . current_time('mysql') . "\n";
        $message .= "Usuario: " . wp_get_current_user()->display_name . "\n";
        $message .= "URL: " . home_url() . "\n\n";
        $message .= "Si no realizaste este cambio, por favor revisa tu sitio inmediatamente.\n";
        
        wp_mail($email, $subject, $message);
    }
    
    /**
     * Enviar resumen semanal
     */
    public function send_weekly_summary() {
        if (empty($this->options['weekly_summary'])) {
            return;
        }
        
        $email = !empty($this->options['notification_email']) ? $this->options['notification_email'] : get_option('admin_email');
        
        global $wpdb;
        
        // Obtener estadísticas de la semana
        $total_activities = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wpto_activity_log 
             WHERE timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        $success_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wpto_activity_log 
             WHERE status = 'success' AND timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        $error_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wpto_activity_log 
             WHERE status = 'error' AND timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        // Obtener funciones activas
        $security_count = $this->count_active_functions('security');
        $optimization_count = $this->count_active_functions('optimization');
        $images_count = $this->count_active_functions('images');
        $seo_count = $this->count_active_functions('seo');
        
        $subject = '[' . get_bloginfo('name') . '] Resumen Semanal - WP Total Optimizer';
        $message = "Resumen semanal de WP Total Optimizer\n";
        $message .= "=====================================\n\n";
        $message .= "Período: Últimos 7 días\n";
        $message .= "Fecha: " . current_time('mysql') . "\n\n";
        
        $message .= "ESTADÍSTICAS DE ACTIVIDAD:\n";
        $message .= "- Total de actividades: " . $total_activities . "\n";
        $message .= "- Exitosas: " . $success_count . "\n";
        $message .= "- Errores: " . $error_count . "\n\n";
        
        $message .= "FUNCIONES ACTIVAS:\n";
        $message .= "- Seguridad: " . $security_count . "\n";
        $message .= "- Optimización: " . $optimization_count . "\n";
        $message .= "- Imágenes: " . $images_count . "\n";
        $message .= "- SEO: " . $seo_count . "\n\n";
        
        $message .= "URL del sitio: " . home_url() . "\n";
        $message .= "Panel de control: " . admin_url('admin.php?page=wpto-control-panel') . "\n";
        
        wp_mail($email, $subject, $message);
    }
    
    /**
     * Contar funciones activas (helper)
     */
    private function count_active_functions($module) {
        $options = get_option('wpto_' . $module . '_options', array());
        $count = 0;
        
        foreach ($options as $key => $value) {
            if ($value === '1' || $value === true || $value === 1) {
                $count++;
            }
        }
        
        return $count;
    }
}


