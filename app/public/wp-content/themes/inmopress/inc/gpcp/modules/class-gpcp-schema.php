<?php
/**
 * GPCP Schema Module
 *
 * Schema.org JSON-LD markup
 *
 * @package GPCP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GPCP Schema class
 */
class GPCP_Schema
{
    /**
     * Instance of this class
     *
     * @var GPCP_Schema
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return GPCP_Schema
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
    private function __construct()
    {
        add_action('admin_init', array($this, 'register_settings'));
        
        if (get_option('gpcp_schema_enabled', true)) {
            add_action('wp_head', array($this, 'output_schema'), 1);
        }
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('gpcp_schema', 'gpcp_schema_enabled');
        register_setting('gpcp_schema', 'gpcp_schema_organization_name');
        register_setting('gpcp_schema', 'gpcp_schema_organization_logo');
        register_setting('gpcp_schema', 'gpcp_schema_organization_url');
        register_setting('gpcp_schema', 'gpcp_schema_organization_phone');
        register_setting('gpcp_schema', 'gpcp_schema_organization_email');
        register_setting('gpcp_schema', 'gpcp_schema_organization_address');
    }

    /**
     * Output schema
     */
    public function output_schema()
    {
        $schemas = array();

        // Organization schema
        $schemas[] = $this->get_organization_schema();

        // Website schema
        $schemas[] = $this->get_website_schema();

        // Current page schema
        if (is_singular()) {
            $schemas[] = $this->get_article_schema();
            $schemas[] = $this->get_breadcrumb_schema();
        } elseif (is_home() || is_front_page()) {
            $schemas[] = $this->get_website_schema();
        }

        // Property schema for InmoPress
        if (is_singular('impress_property')) {
            $schemas[] = $this->get_property_schema();
        }

        // Output all schemas
        foreach ($schemas as $schema) {
            if (!empty($schema)) {
                echo '<script type="application/ld+json">' . "\n";
                echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                echo "\n" . '</script>' . "\n";
            }
        }
    }

    /**
     * Get organization schema
     */
    private function get_organization_schema()
    {
        $name = get_option('gpcp_schema_organization_name', get_bloginfo('name'));
        $url = get_option('gpcp_schema_organization_url', home_url());
        $logo = get_option('gpcp_schema_organization_logo', '');
        $phone = get_option('gpcp_schema_organization_phone', '');
        $email = get_option('gpcp_schema_organization_email', get_option('admin_email'));
        $address = get_option('gpcp_schema_organization_address', '');

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $name,
            'url' => $url,
        );

        if ($logo) {
            $schema['logo'] = $logo;
        }

        if ($email) {
            $schema['email'] = $email;
        }

        if ($phone) {
            $schema['telephone'] = $phone;
        }

        if ($address) {
            $schema['address'] = array(
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
            );
        }

        return $schema;
    }

    /**
     * Get website schema
     */
    private function get_website_schema()
    {
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'url' => home_url(),
            'potentialAction' => array(
                '@type' => 'SearchAction',
                'target' => home_url('/?s={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ),
        );
    }

    /**
     * Get article schema
     */
    private function get_article_schema()
    {
        global $post;
        
        if (!$post) {
            return null;
        }

        $author = get_the_author_meta('display_name', $post->post_author);
        $published = get_the_date('c', $post->ID);
        $modified = get_the_modified_date('c', $post->ID);
        $image = get_the_post_thumbnail_url($post->ID, 'large');

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title($post->ID),
            'datePublished' => $published,
            'dateModified' => $modified,
            'author' => array(
                '@type' => 'Person',
                'name' => $author,
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
            ),
        );

        if ($image) {
            $schema['image'] = $image;
        }

        return $schema;
    }

    /**
     * Get breadcrumb schema
     */
    private function get_breadcrumb_schema()
    {
        global $post;
        
        if (!$post) {
            return null;
        }

        $items = array();
        $position = 1;

        // Home
        $items[] = array(
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => home_url(),
        );

        // Categories or pages
        if (is_singular('post')) {
            $categories = get_the_category($post->ID);
            if (!empty($categories)) {
                $category = $categories[0];
                $items[] = array(
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $category->name,
                    'item' => get_category_link($category->term_id),
                );
            }
        }

        // Current page
        $items[] = array(
            '@type' => 'ListItem',
            'position' => $position,
            'name' => get_the_title($post->ID),
            'item' => get_permalink($post->ID),
        );

        return array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        );
    }

    /**
     * Get property schema (for InmoPress)
     */
    private function get_property_schema()
    {
        global $post;
        
        if (!$post || $post->post_type !== 'impress_property') {
            return null;
        }

        // This would integrate with ACF fields for property data
        // For now, basic structure
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateAgent',
            'name' => get_bloginfo('name'),
            'url' => home_url(),
        );

        return $schema;
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (isset($_POST['gpcp_schema_save'])) {
            check_admin_referer('gpcp_schema_save');
            
            update_option('gpcp_schema_enabled', isset($_POST['gpcp_schema_enabled']));
            update_option('gpcp_schema_organization_name', sanitize_text_field($_POST['gpcp_schema_organization_name']));
            update_option('gpcp_schema_organization_logo', esc_url_raw($_POST['gpcp_schema_organization_logo']));
            update_option('gpcp_schema_organization_url', esc_url_raw($_POST['gpcp_schema_organization_url']));
            update_option('gpcp_schema_organization_phone', sanitize_text_field($_POST['gpcp_schema_organization_phone']));
            update_option('gpcp_schema_organization_email', sanitize_email($_POST['gpcp_schema_organization_email']));
            update_option('gpcp_schema_organization_address', sanitize_textarea_field($_POST['gpcp_schema_organization_address']));
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada.', 'gpcp') . '</p></div>';
        }

        $enabled = get_option('gpcp_schema_enabled', true);
        $org_name = get_option('gpcp_schema_organization_name', get_bloginfo('name'));
        $org_logo = get_option('gpcp_schema_organization_logo', '');
        $org_url = get_option('gpcp_schema_organization_url', home_url());
        $org_phone = get_option('gpcp_schema_organization_phone', '');
        $org_email = get_option('gpcp_schema_organization_email', get_option('admin_email'));
        $org_address = get_option('gpcp_schema_organization_address', '');
        ?>
        <div class="wrap">
            <h1><?php _e('Schema Markup', 'gpcp'); ?></h1>
            <p><?php _e('Configura el Schema.org JSON-LD para mejorar el SEO de tu sitio.', 'gpcp'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('gpcp_schema_save'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Activar Schema Markup', 'gpcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gpcp_schema_enabled" value="1" <?php checked($enabled); ?> />
                                <?php _e('Generar Schema.org JSON-LD automáticamente', 'gpcp'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Se generará automáticamente: Organization, WebSite, Article, BreadcrumbList y Property (para inmuebles).', 'gpcp'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_schema_organization_name"><?php _e('Nombre de la Organización', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gpcp_schema_organization_name" name="gpcp_schema_organization_name" value="<?php echo esc_attr($org_name); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_schema_organization_logo"><?php _e('Logo de la Organización', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="gpcp_schema_organization_logo" name="gpcp_schema_organization_logo" value="<?php echo esc_url($org_logo); ?>" class="regular-text" />
                            <button type="button" class="button gpcp-upload-button" data-target="gpcp_schema_organization_logo"><?php _e('Subir Logo', 'gpcp'); ?></button>
                            <?php if ($org_logo): ?>
                            <p><img src="<?php echo esc_url($org_logo); ?>" style="max-width: 200px; height: auto;" /></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_schema_organization_url"><?php _e('URL de la Organización', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="gpcp_schema_organization_url" name="gpcp_schema_organization_url" value="<?php echo esc_url($org_url); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_schema_organization_phone"><?php _e('Teléfono', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gpcp_schema_organization_phone" name="gpcp_schema_organization_phone" value="<?php echo esc_attr($org_phone); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_schema_organization_email"><?php _e('Email', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="gpcp_schema_organization_email" name="gpcp_schema_organization_email" value="<?php echo esc_attr($org_email); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="gpcp_schema_organization_address"><?php _e('Dirección', 'gpcp'); ?></label>
                        </th>
                        <td>
                            <textarea id="gpcp_schema_organization_address" name="gpcp_schema_organization_address" rows="3" class="large-text"><?php echo esc_textarea($org_address); ?></textarea>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'gpcp'), 'primary', 'gpcp_schema_save'); ?>
            </form>
        </div>
        <?php
    }
}



