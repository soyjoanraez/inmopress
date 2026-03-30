<?php
/**
 * ACF Integration
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ACF Integration class
 */
class ACF_Integration
{

    /**
     * Instance of this class
     *
     * @var ACF_Integration
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return ACF_Integration
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
        // Save JSON.
        add_filter('acf/settings/save_json', array($this, 'acf_json_save_point'));

        // Load JSON.
        add_filter('acf/settings/load_json', array($this, 'acf_json_load_point'));
    }

    /**
     * ACF JSON Save Point
     *
     * @param string $path Path.
     * @return string
     */
    public function acf_json_save_point($path)
    {
        // Update path.
        $path = INMOPRESS_THEME_DIR . '/acf-json';

        // Create directory if not exists.
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    /**
     * ACF JSON Load Point
     *
     * @param array $paths Paths.
     * @return array
     */
    public function acf_json_load_point($paths)
    {
        // Remove original path (optional).
        unset($paths[0]);

        // Append path.
        $paths[] = INMOPRESS_THEME_DIR . '/acf-json';

        return $paths;
    }
}
