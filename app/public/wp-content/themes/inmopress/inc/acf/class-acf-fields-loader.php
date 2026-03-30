<?php
/**
 * ACF Fields Loader
 *
 * Loads ACF field groups from JSON files
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ACF Fields Loader class
 */
class ACF_Fields_Loader
{

    /**
     * Instance of this class
     *
     * @var ACF_Fields_Loader
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return ACF_Fields_Loader
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('acf/init', array($this, 'load_field_groups'));
    }

    /**
     * Load all ACF field groups from JSON files
     */
    public function load_field_groups()
    {
        // Check if ACF is active
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        $groups_dir = INMOPRESS_THEME_DIR . '/inc/acf/groups';

        // Check if directory exists
        if (!file_exists($groups_dir) || !is_dir($groups_dir)) {
            return;
        }

        // Get all JSON files
        $json_files = glob($groups_dir . '/*.json');

        if (empty($json_files)) {
            return;
        }

        // Load each JSON file
        foreach ($json_files as $file) {
            $this->load_field_group_file($file);
        }
    }

    /**
     * Load a single field group JSON file
     *
     * @param string $file Path to JSON file
     */
    private function load_field_group_file($file)
    {
        // Read file contents
        $json = file_get_contents($file);

        if (false === $json) {
            return;
        }

        // Decode JSON
        $field_group = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($field_group)) {
            return;
        }

        // Register field group
        acf_add_local_field_group($field_group);
    }
}






