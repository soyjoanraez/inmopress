<?php
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Load asset files
function INBRMFA_load_plugin_asset_files() {
    $options = get_option('INBRMFA_options', array('plugin_enabled' => 1, 'counter_enabled' => 0, 'initial_expand_first' => 0));
   
    if (!isset($options['plugin_enabled']) || !$options['plugin_enabled']) {
        return;
    }
    
    wp_enqueue_style( 'inb-rm-faq-accordion', INBRMFA_PLUGIN_URL . 'assets/css/inb-rm-faq-accordion.min.css', array(), INBRMFA_CURRENT_VERSION );
    wp_enqueue_script( 'inb-rm-faq-accordion-scriptjs', INBRMFA_PLUGIN_URL . 'assets/js/inb-rm-faq-accordion.min.js', array('jquery'), INBRMFA_CURRENT_VERSION, 'true' );
    
    // Pass settings to JavaScript
    $js_settings = array(
        'initial_expand_first' => isset($options['initial_expand_first']) ? $options['initial_expand_first'] : 0
    );
    wp_localize_script( 'inb-rm-faq-accordion-scriptjs', 'inbrmfa_settings', $js_settings );
    
    if (isset($options['counter_enabled']) && $options['counter_enabled']) {
        wp_enqueue_style( 'inb-rm-faq-accordion-counter', INBRMFA_PLUGIN_URL . 'assets/css/inb-rm-faq-accordion-counter.min.css', array(), INBRMFA_CURRENT_VERSION );
    }
    
    $defaults = INBRMFA_get_default_options();
    $INBRMFA_inline_css = "
        /*==============================================================
        Rank Math FAQ Accordion by INB Digital - https://inbdigital.com/
        ================================================================*/
        
        :root {
            --inbrmfa-border-color: " . esc_attr($options['border_color'] ?? $defaults['border_color']) . ";
            --inbrmfa-border-width: " . esc_attr($options['border_width'] ?? $defaults['border_width']) . "px;
            --inbrmfa-border-style: " . esc_attr($options['border_style'] ?? $defaults['border_style']) . ";
            --inbrmfa-border-radius: " . esc_attr($options['border_radius'] ?? $defaults['border_radius']) . "px;
            --inbrmfa-question-font-size: " . esc_attr($options['question_font_size'] ?? $defaults['question_font_size']) . "px;
            --inbrmfa-question-color: " . esc_attr($options['question_color'] ?? $defaults['question_color']) . ";
            --inbrmfa-question-bg-color: " . esc_attr($options['question_background_color'] ?? $defaults['question_background_color']) . ";
            --inbrmfa-question-text-align: " . esc_attr($options['question_text_align'] ?? $defaults['question_text_align']) . ";
            --inbrmfa-answer-font-size: " . esc_attr($options['answer_font_size'] ?? $defaults['answer_font_size']) . "px;
            --inbrmfa-answer-color: " . esc_attr($options['answer_color'] ?? $defaults['answer_color']) . ";
            --inbrmfa-answer-bg-color: " . esc_attr($options['answer_background_color'] ?? $defaults['answer_background_color']) . ";
            --inbrmfa-answer-text-align: " . esc_attr($options['answer_text_align'] ?? $defaults['answer_text_align']) . ";
        }
       
        /*====================================================================
        Rank Math FAQ Accordion by INB Digital - https://inbdigital.com/ - END
        ======================================================================*/
    ";
    wp_add_inline_style( 'inb-rm-faq-accordion', $INBRMFA_inline_css );
}
add_action( 'wp_enqueue_scripts', 'INBRMFA_load_plugin_asset_files' );