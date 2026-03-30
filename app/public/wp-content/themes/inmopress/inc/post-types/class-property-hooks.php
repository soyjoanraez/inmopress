<?php
/**
 * Property Post Type Hooks
 *
 * Handles title synchronization and permalink generation for impress_property
 *
 * @package Inmopress\CRM
 */

namespace Inmopress\CRM;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Property Hooks class
 */
class Property_Hooks
{

    /**
     * Instance of this class
     *
     * @var Property_Hooks
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Property_Hooks
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
        // Sync referencia field to post title when saving
        add_action('acf/save_post', array($this, 'sync_referencia_to_title'), 20);
        
        // Update post slug when saving
        add_action('acf/save_post', array($this, 'update_post_slug'), 25);
        
        // Modify permalink structure
        add_filter('post_type_link', array($this, 'modify_property_permalink'), 10, 2);
    }

    /**
     * Sync referencia field to post title
     *
     * @param int $post_id Post ID
     */
    public function sync_referencia_to_title($post_id)
    {
        // Only process impress_property post type
        if (get_post_type($post_id) !== 'impress_property') {
            return;
        }

        // Get referencia field value
        $referencia = get_field('referencia', $post_id);

        // If referencia exists and is different from current title, update it
        if (!empty($referencia)) {
            $current_title = get_the_title($post_id);
            
            // Only update if different to avoid infinite loops
            if ($current_title !== $referencia) {
                // Remove this hook temporarily to avoid recursion
                remove_action('acf/save_post', array($this, 'sync_referencia_to_title'), 20);
                
                // Update post title
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => sanitize_text_field($referencia),
                ));
                
                // Re-add the hook
                add_action('acf/save_post', array($this, 'sync_referencia_to_title'), 20);
            }
        }
    }

    /**
     * Modify permalink for impress_property posts
     *
     * @param string  $post_link The post's permalink.
     * @param WP_Post $post      The post object.
     * @return string Modified permalink
     */
    public function modify_property_permalink($post_link, $post)
    {
        // Only process impress_property post type
        if ($post->post_type !== 'impress_property') {
            return $post_link;
        }

        // Get titulo_seo and referencia fields
        $titulo_seo = get_field('titulo_seo', $post->ID);
        $referencia = get_field('referencia', $post->ID);

        // Build slug parts
        $slug_parts = array();

        // Add titulo_seo if available
        if (!empty($titulo_seo)) {
            $slug_parts[] = sanitize_title($titulo_seo);
        }

        // Add referencia if available
        if (!empty($referencia)) {
            $slug_parts[] = sanitize_title($referencia);
        }

        // If we have slug parts, replace the slug in the permalink
        if (!empty($slug_parts)) {
            $new_slug = implode('-', $slug_parts);
            
            // Replace the post slug in the permalink
            // WordPress uses %postname% in permalink structure, so we replace it
            $post_link = str_replace($post->post_name, $new_slug, $post_link);
            
            return $post_link;
        }

        // Fallback to default permalink if no fields available
        return $post_link;
    }

    /**
     * Update post slug (post_name) when saving
     *
     * @param int $post_id Post ID
     */
    public function update_post_slug($post_id)
    {
        // Only process impress_property post type
        if (get_post_type($post_id) !== 'impress_property') {
            return;
        }

        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Get the post object
        $post = get_post($post_id);
        if (!$post) {
            return;
        }

        // Get titulo_seo and referencia fields
        $titulo_seo = get_field('titulo_seo', $post_id);
        $referencia = get_field('referencia', $post_id);

        // Build slug parts
        $slug_parts = array();

        if (!empty($titulo_seo)) {
            $slug_parts[] = sanitize_title($titulo_seo);
        }

        if (!empty($referencia)) {
            $slug_parts[] = sanitize_title($referencia);
        }

        // If we have slug parts, update the post_name
        if (!empty($slug_parts)) {
            $new_slug = implode('-', $slug_parts);
            
            // Only update if different
            if ($post->post_name !== $new_slug) {
                // Remove this hook temporarily to avoid recursion
                remove_action('acf/save_post', array($this, 'update_post_slug'), 25);
                
                // Update post slug
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_name' => $new_slug,
                ));
                
                // Re-add the hook
                add_action('acf/save_post', array($this, 'update_post_slug'), 25);
            }
        }
    }
}

