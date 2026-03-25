<?php
/**
 * Plugin Name: Inmopress Blocks
 * Description: 25 Bloques Gutenberg para Inmopress
 * Version: 1.0.0
 * Author: Inmopress
 */

if (!defined('ABSPATH')) exit;

define('INMOPRESS_BLOCKS_PATH', plugin_dir_path(__FILE__));
define('INMOPRESS_BLOCKS_URL', plugin_dir_url(__FILE__));

class Inmopress_Blocks {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('acf/init', array($this, 'register_blocks'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_filter('block_categories_all', array($this, 'register_category'));
    }
    
    public function register_blocks() {
        if (!function_exists('acf_register_block_type')) {
            return;
        }
        
        $blocks = array(
            // FASE 1: CRÍTICOS
            array('name' => 'hero-inmobiliaria', 'title' => 'Hero Inmobiliaria', 'icon' => 'cover-image'),
            array('name' => 'buscador-inmuebles', 'title' => 'Buscador', 'icon' => 'search'),
            array('name' => 'grid-inmuebles', 'title' => 'Grid Inmuebles', 'icon' => 'grid-view'),
            array('name' => 'ficha-tecnica', 'title' => 'Ficha Técnica', 'icon' => 'list-view'),
            array('name' => 'galeria-inmueble', 'title' => 'Galería', 'icon' => 'format-gallery'),
            array('name' => 'formulario-contacto', 'title' => 'Form Contacto', 'icon' => 'email'),
            
            // FASE 2: DETALLE + LISTADOS (Pendientes de implementar render, pero registrados)
            array('name' => 'caracteristicas', 'title' => 'Características', 'icon' => 'yes'),
            array('name' => 'ubicacion-mapa', 'title' => 'Ubicación', 'icon' => 'location'),
            array('name' => 'inmuebles-similares', 'title' => 'Similares', 'icon' => 'randomize'),
            array('name' => 'tarjeta-destacada', 'title' => 'Tarjeta Destacada', 'icon' => 'id'),
            array('name' => 'carrusel-inmuebles', 'title' => 'Carrusel', 'icon' => 'slides'),
            
            // FASE 3: MARKETING (Pendientes)
            array('name' => 'stats-numeros', 'title' => 'Estadísticas', 'icon' => 'chart-bar'),
            array('name' => 'testimonios', 'title' => 'Testimonios', 'icon' => 'format-quote'),
            array('name' => 'cta', 'title' => 'Call To Action', 'icon' => 'megaphone'),
            array('name' => 'faq', 'title' => 'FAQ', 'icon' => 'editor-help'),
            
            // FASE 4: AVANZADOS (Pendientes)
            array('name' => 'filtros-avanzados', 'title' => 'Filtros Avanzados', 'icon' => 'filter'),
            array('name' => 'mapa-interactivo', 'title' => 'Mapa Interactivo', 'icon' => 'location'),
            array('name' => 'zonas-destacadas', 'title' => 'Zonas Destacadas', 'icon' => 'location-alt'),
            array('name' => 'servicios', 'title' => 'Servicios', 'icon' => 'admin-tools'),
        );
        
        foreach ($blocks as $block) {
            // Solo registrar si existe el directorio del bloque (para desarrollo progresivo)
            if (file_exists(INMOPRESS_BLOCKS_PATH . 'blocks/' . $block['name'] . '/block.json')) {
                register_block_type(INMOPRESS_BLOCKS_PATH . 'blocks/' . $block['name']);
            } elseif (file_exists(INMOPRESS_BLOCKS_PATH . 'blocks/' . $block['name'] . '/render.php')) {
                 // Fallback para registro legacy si no hay block.json (aunque usaremos block.json preferentemente)
                acf_register_block_type(array(
                    'name' => $block['name'],
                    'title' => $block['title'],
                    'render_callback' => array($this, 'render_block'),
                    'category' => 'inmopress',
                    'icon' => $block['icon'],
                    'supports' => array(
                        'align' => array('wide', 'full'),
                        'mode' => true,
                    ),
                ));
            }
        }
    }
    
    public function render_block($block) {
        $slug = str_replace('acf/', '', $block['name']);
        $template = INMOPRESS_BLOCKS_PATH . 'blocks/' . $slug . '/render.php';
        
        if (file_exists($template)) {
            include $template;
        }
    }
    
    public function register_category($categories) {
        return array_merge(
            array(
                array(
                    'slug' => 'inmopress',
                    'title' => 'Inmopress',
                    'icon' => 'admin-home',
                ),
            ),
            $categories
        );
    }
    
    public function enqueue_assets() {
        wp_enqueue_style('inmopress-blocks', INMOPRESS_BLOCKS_URL . 'assets/css/blocks.css', array(), '1.0.0');
        wp_enqueue_script('inmopress-blocks', INMOPRESS_BLOCKS_URL . 'assets/js/blocks.js', array('jquery'), '1.0.0', true);
        
        // Localize script for AJAX
        wp_localize_script('inmopress-blocks', 'inmopress_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('inmopress_nonce')
        ));
    }
}

add_action('plugins_loaded', array('Inmopress_Blocks', 'get_instance'));
