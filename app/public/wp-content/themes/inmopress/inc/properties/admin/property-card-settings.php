<?php
/**
 * Property Card Settings Page Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('inmopress_property_cards');
        // Variables are set by render_settings_page() method
        ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label>Campos ACF Visibles en Cards</label>
                    <p class="description">Selecciona qué campos ACF se mostrarán en las cards de propiedades</p>
                </th>
                <td>
                    <fieldset>
                        <?php
                        $selected_acf = isset($settings['acf_fields']) ? $settings['acf_fields'] : array();
                        foreach ($available_acf_fields as $field_key => $field_label) :
                            $checked = in_array($field_key, $selected_acf) ? 'checked' : '';
                        ?>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr($option_name); ?>[acf_fields][]" 
                                       value="<?php echo esc_attr($field_key); ?>" 
                                       <?php echo $checked; ?>>
                                <?php echo esc_html($field_label); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </fieldset>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label>Taxonomías Visibles en Cards</label>
                    <p class="description">Selecciona qué taxonomías se mostrarán en las cards</p>
                </th>
                <td>
                    <fieldset>
                        <?php
                        $selected_tax = isset($settings['taxonomies']) ? $settings['taxonomies'] : array();
                        foreach ($available_taxonomies as $tax_key => $tax_label) :
                            $checked = in_array($tax_key, $selected_tax) ? 'checked' : '';
                        ?>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr($option_name); ?>[taxonomies][]" 
                                       value="<?php echo esc_attr($tax_key); ?>" 
                                       <?php echo $checked; ?>>
                                <?php echo esc_html($tax_label); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </fieldset>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label>Layout por Defecto</label>
                </th>
                <td>
                    <select name="<?php echo esc_attr($option_name); ?>[default_layout]">
                        <option value="grid" <?php selected($settings['default_layout'], 'grid'); ?>>Grid</option>
                        <option value="list" <?php selected($settings['default_layout'], 'list'); ?>>Lista</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label>Columnas por Defecto (Grid)</label>
                </th>
                <td>
                    <select name="<?php echo esc_attr($option_name); ?>[default_columns]">
                        <option value="1" <?php selected($settings['default_columns'], 1); ?>>1 Columna</option>
                        <option value="2" <?php selected($settings['default_columns'], 2); ?>>2 Columnas</option>
                        <option value="3" <?php selected($settings['default_columns'], 3); ?>>3 Columnas</option>
                        <option value="4" <?php selected($settings['default_columns'], 4); ?>>4 Columnas</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label>Orden de Campos</label>
                    <p class="description">Define el orden en que aparecerán los campos (separados por comas)</p>
                </th>
                <td>
                    <input type="text" 
                           name="<?php echo esc_attr($option_name); ?>[field_order]" 
                           value="<?php 
                           $field_order = isset($settings['field_order']) ? $settings['field_order'] : array();
                           echo esc_attr(is_array($field_order) ? implode(',', $field_order) : $field_order); 
                           ?>" 
                           class="regular-text"
                           placeholder="foto,titulo,referencia,localizacion,precio,dormitorios,banos,superficie">
                    <p class="description">Campos disponibles: foto, titulo, referencia, localizacion, precio, dormitorios, banos, superficie, tipo_vivienda, proposito, estado, certificacion, garajes, ano, agrupacion</p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>

<style>
.form-table fieldset label {
    display: block;
    margin-bottom: 8px;
}
</style>

