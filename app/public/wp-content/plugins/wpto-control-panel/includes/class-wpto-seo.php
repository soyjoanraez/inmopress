<?php
/**
 * WPTO SEO Module
 * Implementa todas las funciones SEO
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPTO_SEO {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('wpto_seo_options', array());
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Meta campos
        if (!empty($this->options['meta_fields'])) {
            // Mostrar meta boxes si no hay conflicto o si se fuerza el uso
            $force_use = !empty($this->options['compatible_yoast']) && $this->options['compatible_yoast'] === 'force';
            $no_conflict = !$this->is_yoast_active() && !$this->is_rankmath_active();
            
            if ($force_use || $no_conflict || empty($this->options['compatible_yoast'])) {
                add_action('add_meta_boxes', array($this, 'add_seo_meta_boxes'));
                add_action('save_post', array($this, 'save_seo_meta'), 10, 2);
                add_action('wp_head', array($this, 'output_seo_meta'));
                add_filter('document_title_parts', array($this, 'filter_document_title'), 10, 1);
            }
        }
        
        // Schema Markup
        if (!empty($this->options['schema_markup'])) {
            add_action('wp_head', array($this, 'output_schema_markup'));
        }
        
        // Sitemap XML
        if (!empty($this->options['xml_sitemap'])) {
            add_action('init', array($this, 'register_sitemap_route'));
            add_action('template_redirect', array($this, 'handle_sitemap_request'));
            if (!empty($this->options['auto_ping'])) {
                add_action('publish_post', array($this, 'ping_search_engines'));
            }
        }
        
        // Robots.txt personalizado
        if (!empty($this->options['custom_robots_txt'])) {
            add_filter('robots_txt', array($this, 'custom_robots_txt'), 10, 2);
        }

        // Panel de encabezados
        if (!empty($this->options['heading_panel'])) {
            add_action('add_meta_boxes', array($this, 'add_heading_panel_meta_box'));
        }
    }
    
    /**
     * Añadir meta boxes SEO
     */
    public function add_seo_meta_boxes() {
        $post_types = get_post_types(array('public' => true));
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wpto_seo_meta',
                '🔍 WP Total Optimizer - SEO',
                array($this, 'render_seo_meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }
    
    /**
     * Renderizar meta box SEO
     */
    public function render_seo_meta_box($post) {
        wp_nonce_field('wpto_seo_meta', 'wpto_seo_meta_nonce');
        
        // Leer valores, sincronizados con Rank Math/Yoast si están activos
        $meta_title = $this->sync_seo_value($post->ID, '_wpto_seo_title', 'rank_math_title', '_yoast_wpseo_title');
        $meta_description = $this->sync_seo_value($post->ID, '_wpto_seo_description', 'rank_math_description', '_yoast_wpseo_metadesc');
        $meta_keywords = get_post_meta($post->ID, '_wpto_seo_keywords', true);
        $focus_keyword = $this->sync_seo_value($post->ID, '_wpto_focus_keyword', 'rank_math_focus_keyword', '_yoast_wpseo_focuskw');
        
        // Mostrar aviso si Rank Math está activo
        $rankmath_active = $this->is_rankmath_active();
        $yoast_active = $this->is_yoast_active();
        
        ?>
        <?php if ($rankmath_active || $yoast_active): ?>
        <div class="notice notice-info inline" style="margin: 10px 0;">
            <p>
                <strong>ℹ️ Plugin SEO detectado:</strong> 
                <?php if ($rankmath_active): ?>Rank Math<?php endif; ?>
                <?php if ($rankmath_active && $yoast_active): ?> y <?php endif; ?>
                <?php if ($yoast_active): ?>Yoast SEO<?php endif; ?>
                está activo. Los campos se sincronizarán automáticamente.
            </p>
        </div>
        <?php endif; ?>
        
        <?php
        // Mostrar advertencias si hay valores fuera de rango
        $title_warning = get_post_meta($post->ID, '_wpto_seo_title_warning', true);
        $desc_warning = get_post_meta($post->ID, '_wpto_seo_description_warning', true);
        if ($title_warning || $desc_warning):
        ?>
        <div class="notice notice-warning inline" style="margin: 10px 0;">
            <p><strong>⚠️ Advertencias SEO:</strong></p>
            <ul style="margin: 5px 0 0 20px;">
                <?php if ($title_warning): ?>
                <li>El título SEO está fuera del rango recomendado (30-70 caracteres). Se recomienda 50-60 caracteres para mejor visibilidad en Google.</li>
                <?php endif; ?>
                <?php if ($desc_warning): ?>
                <li>La meta descripción está fuera del rango recomendado (120-160 caracteres). Se recomienda 150-160 caracteres para mejor visibilidad.</li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <th><label for="wpto_focus_keyword">Palabra Clave Focus</label></th>
                <td>
                    <input type="text" id="wpto_focus_keyword" name="wpto_focus_keyword" value="<?php echo esc_attr($focus_keyword); ?>" class="regular-text" placeholder="Ej: optimización wordpress" />
                    <p class="description">La palabra clave principal para este contenido</p>
                </td>
            </tr>
            <tr>
                <th><label for="wpto_seo_title">Título SEO</label></th>
                <td>
                    <input type="text" id="wpto_seo_title" name="wpto_seo_title" value="<?php echo esc_attr($meta_title); ?>" class="large-text" maxlength="70" />
                    <p class="description">Longitud recomendada: 50-60 caracteres</p>
                    <p class="description" id="wpto_title_length" style="font-weight: bold;">0 caracteres</p>
                    <div id="wpto_title_status" style="margin-top: 5px;"></div>
                </td>
            </tr>
            <tr>
                <th><label for="wpto_seo_description">Meta Descripción</label></th>
                <td>
                    <textarea id="wpto_seo_description" name="wpto_seo_description" rows="3" class="large-text" maxlength="160"><?php echo esc_textarea($meta_description); ?></textarea>
                    <p class="description">Longitud recomendada: 150-160 caracteres</p>
                    <p class="description" id="wpto_desc_length" style="font-weight: bold;">0 caracteres</p>
                    <div id="wpto_desc_status" style="margin-top: 5px;"></div>
                </td>
            </tr>
            <tr>
                <th><label for="wpto_seo_keywords">Keywords</label></th>
                <td>
                    <input type="text" id="wpto_seo_keywords" name="wpto_seo_keywords" value="<?php echo esc_attr($meta_keywords); ?>" class="large-text" />
                    <p class="description">Separadas por comas (máximo 5-7 recomendado)</p>
                </td>
            </tr>
        </table>
        
        <!-- Vista Previa SERP -->
        <div id="wpto-serp-preview" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
            <h3 style="margin-top: 0;">Vista Previa SERP</h3>
            <div style="margin-bottom: 10px;">
                <button type="button" id="wpto-serp-desktop" class="button button-primary" style="margin-right: 5px;">Desktop</button>
                <button type="button" id="wpto-serp-mobile" class="button">Mobile</button>
            </div>
            <div id="wpto-serp-content" style="padding: 15px; background: white; border: 1px solid #ddd; border-radius: 4px;">
                <div style="color: #1a0dab; font-size: 14px; line-height: 1.3; margin-bottom: 3px;" id="serp-url"><?php echo esc_html(home_url()); ?></div>
                <div style="color: #1a0dab; font-size: 20px; line-height: 1.3; margin-bottom: 3px; cursor: pointer; text-decoration: underline;" id="serp-title"><?php echo esc_html($meta_title ?: get_the_title($post->ID)); ?></div>
                <div style="color: #545454; font-size: 14px; line-height: 1.58;" id="serp-description"><?php echo esc_html($meta_description ?: wp_trim_words(get_the_excerpt($post->ID), 25)); ?></div>
            </div>
        </div>
        
        <!-- Análisis SEO -->
        <div id="wpto-seo-analysis" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
            <h3 style="margin-top: 0;">Análisis SEO</h3>
            <div id="wpto-analysis-checks">
                <div class="wpto-check-item" id="check-title-length">
                    <span class="wpto-check-icon">⏳</span>
                    <span class="wpto-check-text">Analizando longitud del título...</span>
                </div>
                <div class="wpto-check-item" id="check-desc-length">
                    <span class="wpto-check-icon">⏳</span>
                    <span class="wpto-check-text">Analizando longitud de la descripción...</span>
                </div>
                <div class="wpto-check-item" id="check-focus-keyword">
                    <span class="wpto-check-icon">⏳</span>
                    <span class="wpto-check-text">Verificando palabra clave focus...</span>
                </div>
                <div class="wpto-check-item" id="check-keyword-in-title">
                    <span class="wpto-check-icon">⏳</span>
                    <span class="wpto-check-text">Verificando keyword en título...</span>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Inicializar análisis SEO
            if (typeof wptoInitSEOEditor === 'function') {
                wptoInitSEOEditor();
            } else {
                // Fallback si el script no está cargado
                console.warn('wptoInitSEOEditor no está disponible');
            }
        });
        </script>
        <?php
    }

    /**
     * Añadir meta box de estructura de encabezados
     */
    public function add_heading_panel_meta_box() {
        $post_types = get_post_types(array('public' => true));
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wpto_heading_panel',
                '🧭 Estructura de Encabezados (H1-H6)',
                array($this, 'render_heading_panel_meta_box'),
                $post_type,
                'side',
                'high'
            );
        }
    }

    /**
     * Renderizar panel de encabezados
     */
    public function render_heading_panel_meta_box($post) {
        ?>
        <div id="wpto-heading-panel">
            <p class="description">Análisis automático de H1-H6 del contenido.</p>
            <div class="wpto-heading-summary"></div>
            <ul class="wpto-heading-list"></ul>
        </div>
        <?php
    }
    
    
    /**
     * Guardar meta SEO
     */
    public function save_seo_meta($post_id, $post) {
        if (!isset($_POST['wpto_seo_meta_nonce']) || !wp_verify_nonce($_POST['wpto_seo_meta_nonce'], 'wpto_seo_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Guardar y sincronizar título SEO con validación
        if (isset($_POST['wpto_seo_title'])) {
            $title = sanitize_text_field($_POST['wpto_seo_title']);
            // Validar longitud (advertencia si está fuera de rango, pero permitir guardar)
            $title_length = mb_strlen($title);
            if ($title_length > 0 && ($title_length < 30 || $title_length > 70)) {
                // Guardar advertencia en meta para mostrar después
                update_post_meta($post_id, '_wpto_seo_title_warning', true);
            } else {
                delete_post_meta($post_id, '_wpto_seo_title_warning');
            }
            $this->save_synced_seo_value($post_id, '_wpto_seo_title', $title, 'rank_math_title', '_yoast_wpseo_title');
        }
        
        // Guardar y sincronizar descripción SEO con validación
        if (isset($_POST['wpto_seo_description'])) {
            $description = sanitize_textarea_field($_POST['wpto_seo_description']);
            // Validar longitud
            $desc_length = mb_strlen($description);
            if ($desc_length > 0 && ($desc_length < 120 || $desc_length > 160)) {
                update_post_meta($post_id, '_wpto_seo_description_warning', true);
            } else {
                delete_post_meta($post_id, '_wpto_seo_description_warning');
            }
            $this->save_synced_seo_value($post_id, '_wpto_seo_description', $description, 'rank_math_description', '_yoast_wpseo_metadesc');
        }
        
        // Guardar keywords (solo WPTO por ahora)
        if (isset($_POST['wpto_seo_keywords'])) {
            update_post_meta($post_id, '_wpto_seo_keywords', sanitize_text_field($_POST['wpto_seo_keywords']));
        }
        
        // Guardar y sincronizar focus keyword
        if (isset($_POST['wpto_focus_keyword'])) {
            $focus_keyword = sanitize_text_field($_POST['wpto_focus_keyword']);
            $this->save_synced_seo_value($post_id, '_wpto_focus_keyword', $focus_keyword, 'rank_math_focus_keyword', '_yoast_wpseo_focuskw');
        }
    }
    
    /**
     * Filtrar título del documento
     */
    public function filter_document_title($title_parts) {
        if (is_singular()) {
            global $post;
            $meta_title = $this->sync_seo_value($post->ID, '_wpto_seo_title', 'rank_math_title', '_yoast_wpseo_title');
            if (!empty($meta_title)) {
                $title_parts['title'] = $meta_title;
            }
        }
        return $title_parts;
    }
    
    /**
     * Output meta SEO
     */
    public function output_seo_meta() {
        if (is_singular()) {
            global $post;
            
            $meta_title = $this->sync_seo_value($post->ID, '_wpto_seo_title', 'rank_math_title', '_yoast_wpseo_title');
            $meta_description = $this->sync_seo_value($post->ID, '_wpto_seo_description', 'rank_math_description', '_yoast_wpseo_metadesc');
            $meta_keywords = get_post_meta($post->ID, '_wpto_seo_keywords', true);
            $og_image = has_post_thumbnail($post->ID) ? get_the_post_thumbnail_url($post->ID, 'large') : '';
            
            // Meta Description
            if ($meta_description) {
                echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
            }
            
            // Keywords
            if ($meta_keywords) {
                echo '<meta name="keywords" content="' . esc_attr($meta_keywords) . '">' . "\n";
            }
            
            // OpenGraph Tags
            echo '<meta property="og:type" content="article">' . "\n";
            if ($meta_title) {
                echo '<meta property="og:title" content="' . esc_attr($meta_title) . '">' . "\n";
            }
            if ($meta_description) {
                echo '<meta property="og:description" content="' . esc_attr($meta_description) . '">' . "\n";
            }
            echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">' . "\n";
            if ($og_image) {
                echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
            }
            echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
            
            // Twitter Cards
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
            if ($meta_title) {
                echo '<meta name="twitter:title" content="' . esc_attr($meta_title) . '">' . "\n";
            }
            if ($meta_description) {
                echo '<meta name="twitter:description" content="' . esc_attr($meta_description) . '">' . "\n";
            }
            if ($og_image) {
                echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
            }
        }
    }
    
    /**
     * Output Schema Markup
     */
    public function output_schema_markup() {
        if (is_singular()) {
            global $post;
            
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => get_the_title(),
                'datePublished' => get_the_date('c'),
                'dateModified' => get_the_modified_date('c'),
                'author' => array(
                    '@type' => 'Person',
                    'name' => get_the_author()
                ),
                'publisher' => array(
                    '@type' => 'Organization',
                    'name' => get_bloginfo('name'),
                    'logo' => array(
                        '@type' => 'ImageObject',
                        'url' => get_site_icon_url()
                    )
                )
            );
            
            if (has_post_thumbnail()) {
                $schema['image'] = get_the_post_thumbnail_url($post->ID, 'full');
            }
            
            if (!empty($this->options['schema_breadcrumbs'])) {
                $schema['breadcrumb'] = $this->generate_breadcrumb_schema();
            }
            
            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        }
    }
    
    /**
     * Generar schema de breadcrumbs
     */
    private function generate_breadcrumb_schema() {
        $breadcrumbs = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array()
        );
        
        $position = 1;
        $breadcrumbs['itemListElement'][] = array(
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Inicio',
            'item' => home_url()
        );
        
        if (is_singular()) {
            $categories = get_the_category();
            if (!empty($categories)) {
                $category = $categories[0];
                $breadcrumbs['itemListElement'][] = array(
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $category->name,
                    'item' => get_category_link($category->term_id)
                );
            }
            
            $breadcrumbs['itemListElement'][] = array(
                '@type' => 'ListItem',
                'position' => $position,
                'name' => get_the_title(),
                'item' => get_permalink()
            );
        }
        
        return $breadcrumbs;
    }
    
    // Constantes para sitemap paginado
    private $sitemap_posts_per_page = 1000; // URLs por página de sitemap

    /**
     * Registrar ruta de sitemap
     */
    public function register_sitemap_route() {
        // Verificar que no haya conflictos con otros plugins de sitemap
        if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
            // Si Yoast o Rank Math están activos, no registrar nuestro sitemap
            // para evitar conflictos (a menos que se fuerce)
            if (empty($this->options['force_sitemap'])) {
                return;
            }
        }

        // Sitemap index
        add_rewrite_rule('^sitemap\.xml$', 'index.php?wpto_sitemap=index', 'top');
        // Sitemaps paginados
        add_rewrite_rule('^sitemap-([0-9]+)\.xml$', 'index.php?wpto_sitemap=page&wpto_sitemap_page=$matches[1]', 'top');
        add_rewrite_tag('%wpto_sitemap%', '([^&]+)');
        add_rewrite_tag('%wpto_sitemap_page%', '([0-9]+)');

        // Flush rewrite rules solo si es necesario (verificar si la regla existe)
        // Esto evita el impacto en rendimiento de flush en cada petición
        $rules = get_option('rewrite_rules');
        if (!isset($rules['^sitemap\.xml$']) || !isset($rules['^sitemap-([0-9]+)\.xml$'])) {
            flush_rewrite_rules(false);
        }
    }

    /**
     * Manejar petición de sitemap
     */
    public function handle_sitemap_request() {
        // Verificar que no haya conflictos con otros sitemaps
        if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
            if (empty($this->options['force_sitemap'])) {
                return; // Dejar que otros plugins manejen el sitemap
            }
        }

        $sitemap_type = get_query_var('wpto_sitemap');
        if (!$sitemap_type) {
            return;
        }

        if ($sitemap_type === 'index') {
            $this->generate_sitemap_index();
            exit;
        } elseif ($sitemap_type === 'page') {
            $page = intval(get_query_var('wpto_sitemap_page', 1));
            $this->generate_sitemap_page($page);
            exit;
        }
    }

    /**
     * Generar sitemap index XML
     */
    private function generate_sitemap_index() {
        if (!headers_sent()) {
            header('Content-Type: application/xml; charset=utf-8');
        }

        // Contar total de posts publicados
        $total_posts = wp_count_posts();
        $published_count = 0;
        foreach (get_post_types(array('public' => true)) as $post_type) {
            $counts = wp_count_posts($post_type);
            if (isset($counts->publish)) {
                $published_count += $counts->publish;
            }
        }

        $total_pages = max(1, ceil($published_count / $this->sitemap_posts_per_page));

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        for ($i = 1; $i <= $total_pages; $i++) {
            $sitemap_url = home_url('/sitemap-' . $i . '.xml');
            echo '  <sitemap>' . "\n";
            echo '    <loc>' . esc_url($sitemap_url) . '</loc>' . "\n";
            echo '    <lastmod>' . esc_html(date('c')) . '</lastmod>' . "\n";
            echo '  </sitemap>' . "\n";
        }

        echo '</sitemapindex>';
    }

    /**
     * Generar página de sitemap XML
     */
    private function generate_sitemap_page($page = 1) {
        if (!headers_sent()) {
            header('Content-Type: application/xml; charset=utf-8');
        }

        $page = max(1, intval($page));
        $offset = ($page - 1) * $this->sitemap_posts_per_page;

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Obtener posts publicados con paginación
        $args = array(
            'posts_per_page' => $this->sitemap_posts_per_page,
            'offset' => $offset,
            'post_status' => 'publish',
            'post_type' => get_post_types(array('public' => true)),
            'orderby' => 'modified',
            'order' => 'DESC',
            'no_found_rows' => true, // Optimización: no contar total
            'update_post_meta_cache' => false, // Optimización: no cargar meta
            'update_post_term_cache' => false, // Optimización: no cargar términos
        );

        $posts = get_posts($args);

        if (!empty($posts) && is_array($posts)) {
            foreach ($posts as $post) {
                if (empty($post->ID) || !is_numeric($post->ID)) {
                    continue;
                }

                $permalink = get_permalink($post->ID);
                if (empty($permalink)) {
                    continue;
                }

                $lastmod = get_the_modified_date('c', $post->ID);
                if (empty($lastmod)) {
                    $lastmod = get_the_date('c', $post->ID);
                }

                // Determinar prioridad según tipo de contenido
                $priority = '0.5';
                if ($post->post_type === 'page') {
                    $priority = '0.8';
                } elseif ($post->post_type === 'post') {
                    $priority = '0.6';
                }

                echo '  <url>' . "\n";
                echo '    <loc>' . esc_url($permalink) . '</loc>' . "\n";
                echo '    <lastmod>' . esc_html($lastmod) . '</lastmod>' . "\n";
                echo '    <changefreq>weekly</changefreq>' . "\n";
                echo '    <priority>' . $priority . '</priority>' . "\n";
                echo '  </url>' . "\n";
            }
        }

        echo '</urlset>';
    }
    
    /**
     * Ping a buscadores
     */
    public function ping_search_engines($post_id) {
        // Validar post ID
        if (empty($post_id) || !is_numeric($post_id)) {
            return;
        }
        
        // Solo ping si el post está publicado
        $post_status = get_post_status($post_id);
        if ($post_status !== 'publish') {
            return;
        }
        
        $sitemap_url = home_url('sitemap.xml');
        
        // Ping Google (con timeout corto para no bloquear)
        wp_remote_get('https://www.google.com/ping?sitemap=' . urlencode($sitemap_url), array(
            'timeout' => 5,
            'blocking' => false // No bloquear la ejecución
        ));
        
        // Ping Bing (con timeout corto para no bloquear)
        wp_remote_get('https://www.bing.com/ping?sitemap=' . urlencode($sitemap_url), array(
            'timeout' => 5,
            'blocking' => false // No bloquear la ejecución
        ));
    }
    
    /**
     * Verificar si Yoast está activo
     */
    private function is_yoast_active() {
        return defined('WPSEO_VERSION') || class_exists('WPSEO_Options');
    }
    
    /**
     * Verificar si Rank Math está activo
     */
    private function is_rankmath_active() {
        return defined('RANK_MATH_VERSION') || class_exists('RankMath');
    }
    
    /**
     * Robots.txt personalizado
     */
    public function custom_robots_txt($output, $public) {
        // Si el sitio no es público, no generar robots.txt
        if (!$public) {
            return $output;
        }
        
        $custom_robots = !empty($this->options['robots_txt_content']) ? $this->options['robots_txt_content'] : '';
        
        if (!empty($custom_robots)) {
            // Validar que el contenido personalizado no esté vacío después de trim
            $custom_robots = trim($custom_robots);
            if (!empty($custom_robots)) {
                return $custom_robots;
            }
        }
        
        // Generar robots.txt automáticamente si está vacío
        $sitemap_url = home_url('sitemap.xml');
        
        $output = "User-agent: *\n";
        $output .= "Disallow: /wp-admin/\n";
        $output .= "Disallow: /wp-includes/\n";
        $output .= "Disallow: /wp-content/plugins/\n";
        $output .= "Disallow: /wp-content/themes/\n";
        $output .= "Disallow: /wp-json/\n";
        $output .= "Allow: /wp-content/uploads/\n";
        
        // Añadir sitemap solo si está habilitado
        if (!empty($this->options['xml_sitemap'])) {
            $output .= "\nSitemap: " . esc_url($sitemap_url) . "\n";
        }
        
        return $output;
    }
    
    /**
     * Obtener valor SEO sincronizado
     */
    private function sync_seo_value($post_id, $wpto_key, $rankmath_key = '', $yoast_key = '') {
        // Validar post ID
        if (empty($post_id) || !is_numeric($post_id)) {
            return '';
        }
        
        // Validar keys
        if (empty($wpto_key) || !is_string($wpto_key)) {
            return '';
        }
        
        $value = get_post_meta($post_id, $wpto_key, true);
        
        // Si está vacío, intentar Rank Math
        if (empty($value) && $this->is_rankmath_active() && !empty($rankmath_key) && is_string($rankmath_key)) {
            $rm_value = get_post_meta($post_id, $rankmath_key, true);
            if (!empty($rm_value)) {
                $value = $rm_value;
            }
        }
        
        // Si sigue vacío, intentar Yoast
        if (empty($value) && $this->is_yoast_active() && !empty($yoast_key) && is_string($yoast_key)) {
            $yoast_value = get_post_meta($post_id, $yoast_key, true);
            if (!empty($yoast_value)) {
                $value = $yoast_value;
            }
        }
        
        return is_string($value) ? $value : '';
    }

    /**
     * Obtener valor SEO sincronizado (público)
     */
    public function get_synced_seo_value($post_id, $wpto_key, $rankmath_key = '', $yoast_key = '') {
        return $this->sync_seo_value($post_id, $wpto_key, $rankmath_key, $yoast_key);
    }
    
    /**
     * Guardar valor SEO sincronizado
     */
    private function save_synced_seo_value($post_id, $wpto_key, $value, $rankmath_key = '', $yoast_key = '') {
        // Validar post ID
        if (empty($post_id) || !is_numeric($post_id)) {
            return false;
        }
        
        // Validar keys
        if (empty($wpto_key) || !is_string($wpto_key)) {
            return false;
        }
        
        // Sanitizar valor
        $value = is_string($value) ? sanitize_text_field($value) : '';
        
        // Guardar en WPTO
        $result = update_post_meta($post_id, $wpto_key, $value);
        
        // Sincronizar con Rank Math si está activo
        if ($this->is_rankmath_active() && !empty($rankmath_key) && is_string($rankmath_key)) {
            update_post_meta($post_id, $rankmath_key, $value);
        }
        
        // Sincronizar con Yoast si está activo
        if ($this->is_yoast_active() && !empty($yoast_key) && is_string($yoast_key)) {
            update_post_meta($post_id, $yoast_key, $value);
        }
        
        return $result !== false;
    }

    /**
     * Guardar valor SEO sincronizado (público)
     */
    public function set_synced_seo_value($post_id, $wpto_key, $value, $rankmath_key = '', $yoast_key = '') {
        return $this->save_synced_seo_value($post_id, $wpto_key, $value, $rankmath_key, $yoast_key);
    }
}
