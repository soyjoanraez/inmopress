<?php
/**
 * Archive Filters Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use Inmopress\CRM\Property_Filters;

$current_filters = Property_Filters::get_filter_values();
include INMOPRESS_THEME_DIR . '/templates/properties/property-filters.php';
?>


