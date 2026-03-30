<?php
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Get default options
function INBRMFA_get_default_options() {
    return array(
        'plugin_enabled' => 1,
        'counter_enabled' => 0,
        'initial_expand_first' => 0,
        'question_font_size' => 18,
        'question_color' => '#003952',
        'question_background_color' => '#e2f6ff',
        'question_text_align' => 'left',
        'answer_font_size' => 16,
        'answer_color' => '#003952',
        'answer_background_color' => '#f3fbff',
        'answer_text_align' => 'left',
        'border_width' => 1,
        'border_style' => 'solid',
        'border_color' => '#c1ecff',
        'border_radius' => 4,
    );
}

// Get option with default fallback
function INBRMFA_get_option( $key, $default = null ) {
    $options = get_option('INBRMFA_options');
    $defaults = INBRMFA_get_default_options();
    
    if ($default === null && isset($defaults[$key])) {
        $default = $defaults[$key];
    }
    
    return isset($options[$key]) ? $options[$key] : $default;
}

// Plugin Menu
function INBRMFA_add_settings_page() {
    add_options_page( 'Rank Math FAQ Accordion Settings', 'Rank Math FAQ Accordion', 'manage_options', 'inb-rmfa-settings', 'INBRMFA_render_settings_page' );
}
add_action( 'admin_menu', 'INBRMFA_add_settings_page' );

// Render settings page
function INBRMFA_render_settings_page() {
    ?>
    <div class="wrap inb-rmfa-settings__wrap">
        <span class="wp-header-end"></span>

        <div class="inb-rmfa-settings__header">
            <h1>Rank Math FAQ Accordion - Settings</h1>
            <div class="inb-rmfa-settings__version">v<?php echo INBRMFA_CURRENT_VERSION ?></div>
        </div>
        <div class="inb-rmfa-settings__grid">
            <div class="inb-rmfa-settings__main">
                <div class="inb-rmfa-settings__main_inner">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('INBRMFA_settings');
                        do_settings_sections('inb-rmfa-settings');
                        submit_button();
                        ?>
                        <div class="inb-rmfa-settings__reset-button--section">
                            <p class="description">Reset all settings to their default values. This action cannot be undone.</p>
                            <button type="button" id="INBRMFA_reset_settings" class="inb-rmfa-settings__reset-button">Reset to Default</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="inb-rmfa-settings__sidebar">
                <div class="inb-rmfa-settings__sidebar_box">
                    <h3>How to contact us?</h3>
                    <ul class="inb-rmfa-settings__sidebar_inks-list">
                        <li><a href="https://inbdigital.com/documentation/convert-rank-math-faq-to-accordion-plugin-docs/?utm_source=Plugin&utm_medium=Rank%20Math%20FAQ%20Accordion%20-%20Settings%20Page%20Sidebar&utm_campaign=WP" target="_blank">Documentation</a></li>
                        <li><a href="https://inbdigital.com/?utm_source=Plugin&utm_medium=Rank%20Math%20FAQ%20Accordion%20-%20Settings%20Page%20Sidebar&utm_campaign=WP" target="_blank">INB Digital (homepage)</a></li>
                        <li><a href="https://inbdigital.com/plugins/?utm_source=Plugin&utm_medium=Rank%20Math%20FAQ%20Accordion%20-%20Settings%20Page%20Sidebar&utm_campaign=WP" target="_blank">Our plugins</a></li>
                        <li><a href="https://inbdigital.com/plugins-support/?utm_source=Plugin&utm_medium=Rank%20Math%20FAQ%20Accordion%20-%20Settings%20Page%20Sidebar&utm_campaign=WP" target="_blank">Contact support</a></li>
                        <li><a href="https://www.facebook.com/inbdigital.official" target="_blank">Like us on Facebook</a></li>
                        <li><a href="https://twitter.com/inb_digital" target="_blank">Follow us on X (Twitter)</a></li>
                        <li><a href="https://www.instagram.com/inbdigital/" target="_blank">Follow us on Instagram</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Register settings
function INBRMFA_register_settings() {
    register_setting( 'INBRMFA_settings', 'INBRMFA_options', 'INBRMFA_sanitize_options' );

    add_settings_section( 'INBRMFA_main_section', null, null, 'inb-rmfa-settings' );
    
    // General Settings
    add_settings_field( 'INBRMFA_section_general', '<h3>General Settings</h3>', function() { echo ''; }, 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_plugin_enabled', 'Enable Plugin', 'INBRMFA_plugin_enabled_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_counter_enabled', 'Enable Counter', 'INBRMFA_counter_enabled_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_initial_expand_first', 'Initial Expand First FAQ', 'INBRMFA_initial_expand_first_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );

    // Question Styling
    add_settings_field( 'INBRMFA_section_question', '<h3>Question Styling</h3>', function() { echo ''; }, 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_question_font_size', 'Font Size', 'INBRMFA_question_font_size_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_question_color', 'Text Color', 'INBRMFA_question_color_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_question_background_color', 'Background Color', 'INBRMFA_question_background_color_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_question_text_align', 'Text Align', 'INBRMFA_question_text_align_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );

    // Answer Styling
    add_settings_field( 'INBRMFA_section_answer', '<h3>Answer Styling</h3>', function() { echo ''; }, 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_answer_font_size', 'Font Size', 'INBRMFA_answer_font_size_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_answer_color', 'Text Color', 'INBRMFA_answer_color_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_answer_background_color', 'Background Color', 'INBRMFA_answer_background_color_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_answer_text_align', 'Text Align', 'INBRMFA_answer_text_align_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );

    // Border Styling
    add_settings_field( 'INBRMFA_section_border', '<h3>Border Styling</h3>', function() { echo ''; }, 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_border_width', 'Border Width', 'INBRMFA_border_width_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_border_style', 'Border Style', 'INBRMFA_border_style_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_border_color', 'Border Color', 'INBRMFA_border_color_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
    add_settings_field( 'INBRMFA_border_radius', 'Border Radius', 'INBRMFA_border_radius_callback', 'inb-rmfa-settings', 'INBRMFA_main_section' );
}
add_action( 'admin_init', 'INBRMFA_register_settings' );

// Settings callbacks
function INBRMFA_plugin_enabled_callback() {
    $value = INBRMFA_get_option('plugin_enabled');
    echo '<input type="checkbox" name="INBRMFA_options[plugin_enabled]" value="1" ' . checked(1, $value, false) . '/>';
}

function INBRMFA_counter_enabled_callback() {
    $value = INBRMFA_get_option('counter_enabled');
    echo '<input type="checkbox" name="INBRMFA_options[counter_enabled]" value="1" ' . checked(1, $value, false) . '/>';
}

function INBRMFA_initial_expand_first_callback() {
    $value = INBRMFA_get_option('initial_expand_first');
    echo '<input type="checkbox" name="INBRMFA_options[initial_expand_first]" value="1" ' . checked(1, $value, false) . '/>';
    echo '<p class="description">Check this option to automatically expand the first FAQ item when the page loads.</p>';
}

function INBRMFA_question_font_size_callback() {
    $value = INBRMFA_get_option('question_font_size');
    echo '<div class="inb-rmfa-settings__field-wrapper">';
    echo '<div class="inb-rmfa-settings__input-group">';
    echo '<input type="number" class="inb-rmfa-settings__form-control" min="1" name="INBRMFA_options[question_font_size]" value="' . esc_attr($value) . '" required /><span class="inb-rmfa-settings__input-group_text">px</span>';
    echo '</div>';
    echo '</div>';
}

function INBRMFA_question_color_callback() {
    $default_value = INBRMFA_get_option('question_color');
    $value = INBRMFA_get_option('question_color');
    echo '<input type="text" class="inbrmfa-color-picker" name="INBRMFA_options[question_color]" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($default_value) . '" />';
}

function INBRMFA_question_background_color_callback() {
    $default_value = INBRMFA_get_option('question_background_color');
    $value = INBRMFA_get_option('question_background_color');
    echo '<input type="text" class="inbrmfa-color-picker" name="INBRMFA_options[question_background_color]" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($default_value) . '" />';
}

function INBRMFA_question_text_align_callback() {
    $value = INBRMFA_get_option('question_text_align');
    $alignments = array('left', 'center', 'right');

    echo '<select name="INBRMFA_options[question_text_align]" class="inb-rmfa-settings__form-control-select">';
    foreach ($alignments as $alignment) {
        echo '<option value="'. esc_attr($alignment). '"'. selected($alignment, $value, false). '>'. esc_html(ucfirst($alignment)). '</option>';
    }
    echo '</select>';
}

function INBRMFA_answer_font_size_callback() {
    $value = INBRMFA_get_option('answer_font_size');
    echo '<div class="inb-rmfa-settings__field-wrapper">';
    echo '<div class="inb-rmfa-settings__input-group">';
    echo '<input type="number" class="inb-rmfa-settings__form-control" min="1" name="INBRMFA_options[answer_font_size]" value="' . esc_attr($value) . '" required /><span class="inb-rmfa-settings__input-group_text">px</span>';
    echo '</div>';
    echo '</div>';
}

function INBRMFA_answer_color_callback() {
    $default_value = INBRMFA_get_option('answer_color');
    $value = INBRMFA_get_option('answer_color');
    echo '<input type="text" class="inbrmfa-color-picker" name="INBRMFA_options[answer_color]" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($default_value) . '" />';
}

function INBRMFA_answer_background_color_callback() {
    $default_value = INBRMFA_get_option('answer_background_color');
    $value = INBRMFA_get_option('answer_background_color');
    echo '<input type="text" class="inbrmfa-color-picker" name="INBRMFA_options[answer_background_color]" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($default_value) . '" />';
}

function INBRMFA_answer_text_align_callback() {
    $value = INBRMFA_get_option('answer_text_align');
    $alignments = array('left', 'center', 'right');

    echo '<select name="INBRMFA_options[answer_text_align]" class="inb-rmfa-settings__form-control-select">';
    foreach ($alignments as $alignment) {
        echo '<option value="'. esc_attr($alignment). '"'. selected($alignment, $value, false). '>'. esc_html(ucfirst($alignment)). '</option>';
    }
    echo '</select>';
}

function INBRMFA_border_width_callback() {
    $value = INBRMFA_get_option('border_width');
    echo '<div class="inb-rmfa-settings__field-wrapper">';
    echo '<div class="inb-rmfa-settings__input-group">';
    echo '<input type="number" class="inb-rmfa-settings__form-control" min="0" name="INBRMFA_options[border_width]" value="' . esc_attr($value) . '" required /><span class="inb-rmfa-settings__input-group_text">px</span>';
    echo '</div>';
    echo '</div>';
}

function INBRMFA_border_style_callback() {
    $value = INBRMFA_get_option('border_style');
    $styles = array('solid', 'dashed', 'dotted');
    
    echo '<select name="INBRMFA_options[border_style]" class="inb-rmfa-settings__form-control-select">';
    foreach ($styles as $style) {
        echo '<option value="' . esc_attr($style) . '"' . selected($style, $value, false) . '>' . esc_html(ucfirst($style)) . '</option>';
    }
    echo '</select>';
}

function INBRMFA_border_color_callback() {
    $default_value = INBRMFA_get_option('border_color');
    $value = INBRMFA_get_option('border_color');
    echo '<input type="text" class="inbrmfa-color-picker" name="INBRMFA_options[border_color]" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($default_value) . '" />';
}

function INBRMFA_border_radius_callback() {
    $value = INBRMFA_get_option('border_radius');
    echo '<div class="inb-rmfa-settings__field-wrapper">';
    echo '<div class="inb-rmfa-settings__input-group">';
    echo '<input type="number" class="inb-rmfa-settings__form-control" min="1" name="INBRMFA_options[border_radius]" value="' . esc_attr($value) . '" required /><span class="inb-rmfa-settings__input-group_text">px</span>';
    echo '</div>';
    echo '</div>';
}

// Sanitizing function
function INBRMFA_sanitize_options( $input ) {
    $sanitized_input = array();
    $defaults = INBRMFA_get_default_options();

    $sanitized_input['plugin_enabled'] = isset($input['plugin_enabled']) ? absint($input['plugin_enabled']) : 0;
    $sanitized_input['counter_enabled'] = isset($input['counter_enabled']) ? absint($input['counter_enabled']) : 0;
    $sanitized_input['initial_expand_first'] = isset($input['initial_expand_first']) ? absint($input['initial_expand_first']) : 0;
    $sanitized_input['question_font_size'] = isset($input['question_font_size']) ? absint($input['question_font_size']) : $defaults['question_font_size'];
    $sanitized_input['question_color'] = isset($input['question_color']) ? sanitize_hex_color($input['question_color']) : $defaults['question_color'];
    $sanitized_input['question_background_color'] = isset($input['question_background_color']) ? sanitize_hex_color($input['question_background_color']) : $defaults['question_background_color'];
    $sanitized_input['question_text_align'] = isset($input['question_text_align']) ? sanitize_text_field($input['question_text_align']) : $defaults['question_text_align'];
    $sanitized_input['answer_font_size'] = isset($input['answer_font_size']) ? absint($input['answer_font_size']) : $defaults['answer_font_size'];
    $sanitized_input['answer_color'] = isset($input['answer_color']) ? sanitize_hex_color($input['answer_color']) : $defaults['answer_color'];
    $sanitized_input['answer_background_color'] = isset($input['answer_background_color']) ? sanitize_hex_color($input['answer_background_color']) : $defaults['answer_background_color'];
    $sanitized_input['answer_text_align'] = isset($input['answer_text_align']) ? sanitize_text_field($input['answer_text_align']) : $defaults['answer_text_align'];
    $sanitized_input['border_width'] = isset($input['border_width']) ? absint($input['border_width']) : $defaults['border_width'];
    $sanitized_input['border_style'] = isset($input['border_style']) ? sanitize_text_field($input['border_style']) : $defaults['border_style'];
    $sanitized_input['border_color'] = isset($input['border_color']) ? sanitize_hex_color($input['border_color']) : $defaults['border_color'];
    $sanitized_input['border_radius'] = isset($input['border_radius']) ? absint($input['border_radius']) : $defaults['border_radius'];

    return $sanitized_input;
}

// Load asset file for settings page
function INBRMFA_enqueue_admin_scripts( $hook ) {
    if ('settings_page_inb-rmfa-settings' !== $hook) {
        return;
    }
    // Enqueue the color picker script and styles
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('INBRMFA-admin-js', INBRMFA_PLUGIN_URL . 'assets/js/inb-rm-faq-accordion-admin.js', array('jquery', 'wp-color-picker'), INBRMFA_CURRENT_VERSION, true);

    // Enqueue admin styles
    wp_enqueue_style(
        'INBRMFA-admin-style', 
        INBRMFA_PLUGIN_URL . 'assets/css/inb-rm-faq-accordion-admin.css', 
        array(), 
        INBRMFA_CURRENT_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'INBRMFA_enqueue_admin_scripts' );

// Reset Settings
function INBRMFA_reset_settings() {
    $default_options = INBRMFA_get_default_options();
    update_option( 'INBRMFA_options', $default_options );
    wp_send_json_success();
}
add_action( 'wp_ajax_INBRMFA_reset_settings', 'INBRMFA_reset_settings' );

// Remove all admin notices on plugin settings page except plugin-specific ones
function INBRMFA_remove_admin_notices() {
    $screen = get_current_screen();
    if ( isset( $screen->id ) && $screen->id === 'settings_page_inb-rmfa-settings' ) {
        // Remove all admin notices
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );
    }
}
add_action( 'current_screen', 'INBRMFA_remove_admin_notices' );