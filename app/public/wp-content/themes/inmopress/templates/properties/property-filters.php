<?php
/**
 * Property Filters Template
 *
 * @package Inmopress\CRM
 * @var array $current_filters Current filter values
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use Inmopress\CRM\Property_Filters;

$current_filters = isset($current_filters) ? $current_filters : Property_Filters::get_filter_values();
$provinces = Property_Filters::get_provinces();
$property_types = Property_Filters::get_property_types();
$price_range = Property_Filters::get_price_range($current_filters['proposito'] ?? '');
?>

<div class="inmopress-property-filters">
    <form id="inmopress-filters-form" class="filters-form">
        <div class="filters-header">
            <h3>Filtros</h3>
            <button type="button" class="filters-toggle" aria-label="Toggle filters">
                <span class="toggle-icon">▼</span>
            </button>
        </div>

        <div class="filters-content">
            <!-- Propósito -->
            <div class="filter-group">
                <label class="filter-label">Propósito</label>
                <div class="filter-options">
                    <label>
                        <input type="radio" name="proposito" value="" <?php checked(empty($current_filters['proposito'])); ?>>
                        Todos
                    </label>
                    <label>
                        <input type="radio" name="proposito" value="venta" <?php checked(isset($current_filters['proposito']) && $current_filters['proposito'] === 'venta'); ?>>
                        Venta
                    </label>
                    <label>
                        <input type="radio" name="proposito" value="alquiler" <?php checked(isset($current_filters['proposito']) && $current_filters['proposito'] === 'alquiler'); ?>>
                        Alquiler
                    </label>
                </div>
            </div>

            <!-- Provincia -->
            <div class="filter-group">
                <label class="filter-label" for="filter-provincia">Provincia</label>
                <select name="provincia" id="filter-provincia" class="filter-select">
                    <option value="">Todas</option>
                    <?php foreach ($provinces as $province) : ?>
                        <option value="<?php echo esc_attr($province->term_id); ?>" 
                                <?php selected(isset($current_filters['provincia']) && $current_filters['provincia'] == $province->term_id); ?>>
                            <?php echo esc_html($province->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Población -->
            <div class="filter-group">
                <label class="filter-label" for="filter-poblacion">Población</label>
                <select name="poblacion" id="filter-poblacion" class="filter-select">
                    <option value="">Todas</option>
                    <?php
                    $municipalities = array();
                    if (!empty($current_filters['provincia'])) {
                        $municipalities = Property_Filters::get_municipalities($current_filters['provincia']);
                    }
                    foreach ($municipalities as $municipality) :
                    ?>
                        <option value="<?php echo esc_attr($municipality->term_id); ?>" 
                                <?php selected(isset($current_filters['poblacion']) && $current_filters['poblacion'] == $municipality->term_id); ?>>
                            <?php echo esc_html($municipality->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tipo de Vivienda -->
            <div class="filter-group">
                <label class="filter-label">Tipo de Vivienda</label>
                <div class="filter-options filter-checkboxes">
                    <?php foreach ($property_types as $type) : ?>
                        <label>
                            <input type="checkbox" 
                                   name="tipo_vivienda[]" 
                                   value="<?php echo esc_attr($type->term_id); ?>"
                                   <?php checked(in_array($type->term_id, isset($current_filters['tipo_vivienda']) ? (array) $current_filters['tipo_vivienda'] : array())); ?>>
                            <?php echo esc_html($type->name); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Precio -->
            <div class="filter-group">
                <label class="filter-label">Precio</label>
                <div class="filter-range">
                    <input type="number" 
                           name="precio_min" 
                           placeholder="Mínimo" 
                           min="0" 
                           value="<?php echo esc_attr($current_filters['precio_min'] ?? ''); ?>"
                           class="filter-input">
                    <span class="range-separator">-</span>
                    <input type="number" 
                           name="precio_max" 
                           placeholder="Máximo" 
                           min="0" 
                           value="<?php echo esc_attr($current_filters['precio_max'] ?? ''); ?>"
                           class="filter-input">
                </div>
                <?php if ($price_range['min'] > 0 && $price_range['max'] > 0) : ?>
                    <p class="filter-hint">Rango: <?php echo number_format($price_range['min'], 0, ',', '.'); ?> - <?php echo number_format($price_range['max'], 0, ',', '.'); ?> €</p>
                <?php endif; ?>
            </div>

            <!-- Dormitorios -->
            <div class="filter-group">
                <label class="filter-label" for="filter-dormitorios">Dormitorios (mínimo)</label>
                <select name="dormitorios_min" id="filter-dormitorios" class="filter-select">
                    <option value="">Cualquiera</option>
                    <option value="1" <?php selected(isset($current_filters['dormitorios_min']) && $current_filters['dormitorios_min'] == 1); ?>>1+</option>
                    <option value="2" <?php selected(isset($current_filters['dormitorios_min']) && $current_filters['dormitorios_min'] == 2); ?>>2+</option>
                    <option value="3" <?php selected(isset($current_filters['dormitorios_min']) && $current_filters['dormitorios_min'] == 3); ?>>3+</option>
                    <option value="4" <?php selected(isset($current_filters['dormitorios_min']) && $current_filters['dormitorios_min'] == 4); ?>>4+</option>
                    <option value="5" <?php selected(isset($current_filters['dormitorios_min']) && $current_filters['dormitorios_min'] == 5); ?>>5+</option>
                </select>
            </div>

            <!-- Baños -->
            <div class="filter-group">
                <label class="filter-label" for="filter-banos">Baños (mínimo)</label>
                <select name="banos_min" id="filter-banos" class="filter-select">
                    <option value="">Cualquiera</option>
                    <option value="1" <?php selected(isset($current_filters['banos_min']) && $current_filters['banos_min'] == 1); ?>>1+</option>
                    <option value="2" <?php selected(isset($current_filters['banos_min']) && $current_filters['banos_min'] == 2); ?>>2+</option>
                    <option value="3" <?php selected(isset($current_filters['banos_min']) && $current_filters['banos_min'] == 3); ?>>3+</option>
                    <option value="4" <?php selected(isset($current_filters['banos_min']) && $current_filters['banos_min'] == 4); ?>>4+</option>
                </select>
            </div>

            <!-- Superficie -->
            <div class="filter-group">
                <label class="filter-label">Superficie (m²)</label>
                <div class="filter-range">
                    <input type="number" 
                           name="superficie_min" 
                           placeholder="Mínimo" 
                           min="0" 
                           value="<?php echo esc_attr($current_filters['superficie_min'] ?? ''); ?>"
                           class="filter-input">
                    <span class="range-separator">-</span>
                    <input type="number" 
                           name="superficie_max" 
                           placeholder="Máximo" 
                           min="0" 
                           value="<?php echo esc_attr($current_filters['superficie_max'] ?? ''); ?>"
                           class="filter-input">
                </div>
            </div>

            <!-- Características Especiales -->
            <div class="filter-group">
                <label class="filter-label">Características Especiales</label>
                <div class="filter-options filter-checkboxes filter-columns">
                    <?php
                    // Definir todas las características especiales (true_false fields)
                    $characteristics = array(
                        'aire_acondicionado' => 'Aire acondicionado',
                        'barbacoa' => 'Barbacoa',
                        'lavabajillas' => 'Lavavajillas',
                        'ascensor' => 'Ascensor',
                        'gimnasio' => 'Gimnasio',
                        'encimera_granito' => 'Encimera de granito',
                        'lavanderia' => 'Lavandería',
                        'solar' => 'Solar',
                        'spa' => 'Spa',
                        'minusvalidos' => 'Adaptado minusválidos',
                        'luminoso' => 'Luminoso',
                        'horno' => 'Horno',
                        'puerta_blindada' => 'Puerta blindada',
                        'patio' => 'Patio',
                        'conserje' => 'Conserje',
                        'buhardilla' => 'Buhardilla',
                        'chimenea' => 'Chimenea',
                        'agua_potable' => 'Agua potable',
                        'alarma' => 'Alarma',
                        'armarios_empotrados' => 'Armarios empotrados',
                        'porche' => 'Porche',
                        'despensa' => 'Despensa',
                        'portero_automatico' => 'Portero automático',
                        'jacuzzi' => 'Jacuzzi',
                        'sotano' => 'Sótano',
                        'vistas_mar' => 'Vistas al mar',
                        'vistas_montana' => 'Vistas a la montaña',
                        'suelo_radiante' => 'Suelo radiante',
                        'aislamiento_termico' => 'Aislamiento térmico',
                        'sistema_riego_automatico' => 'Sistema riego automático',
                        'internet' => 'Internet',
                        'sat' => 'SAT',
                        'vitroceramica' => 'Vitrocerámica',
                        'frigorifico' => 'Frigorífico',
                        'microondas' => 'Microondas',
                        'zona_infantil' => 'Zona infantil',
                        'tenis' => 'Tenis',
                        'padel' => 'Padel',
                        'muebles_jardin' => 'Muebles jardín',
                    );
                    
                    foreach ($characteristics as $key => $label) :
                    ?>
                        <label class="filter-checkbox-item">
                            <input type="checkbox" 
                                   name="<?php echo esc_attr($key); ?>" 
                                   value="1"
                                   <?php checked(isset($current_filters[$key]) && $current_filters[$key] !== '' && $current_filters[$key] !== '0'); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Botones -->
            <div class="filter-actions">
                <button type="submit" class="filter-submit">Aplicar Filtros</button>
                <button type="reset" class="filter-reset">Limpiar</button>
            </div>
        </div>
    </form>
</div>

