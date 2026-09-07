<?php
/**
 * GAUR Hero Sections - Frontend
 */
if ( ! defined('ABSPATH') ) exit;

// Load the CSS into head
add_action('wp_head', function() {
    // Load global CSS
    $global_css = get_option('gaur_hero_custom_css', '');
    if ($global_css) {
        echo "<style id='gaur-hero-global-css'>\n$global_css\n</style>";
    }
    
    // Load individual hero CSS
    $args = [
        'post_type' => 'hero_section',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ];
    
    $heroes = get_posts($args);
    
    foreach ($heroes as $hero) {
        $custom_css = get_post_meta($hero->ID, '_hero_custom_css', true);
        if (!empty($custom_css)) {
            $slug = $hero->post_name;
            $unique_class = 'hero-' . sanitize_html_class($slug);
            
            echo "<style id='gaur-hero-{$slug}'>\n";
            echo "/* CSS for: {$slug} */\n";
            
            // Check if user wrote full CSS with selectors
            $has_selector = (strpos($custom_css, '{') !== false);
            
            if ($has_selector) {
                // User wrote full CSS - output as-is but prefix selectors with the unique class
                $lines = explode("\n", $custom_css);
                $output = '';
                
                foreach ($lines as $line) {
                    $trimmed_line = trim($line);
                    
                    if (empty($trimmed_line)) {
                        continue;
                    }
                    
                    // Check if line is a CSS rule (contains { but not @media or @keyframes)
                    if (strpos($trimmed_line, '{') !== false && 
                        strpos($trimmed_line, '@') === false &&
                        !preg_match('/^[0-9\.]/', $trimmed_line)) {
                        
                        // It's a CSS selector - prefix it with our unique class
                        // Handle multiple selectors separated by commas
                        $selectors = explode(',', $trimmed_line);
                        $prefixed_selectors = [];
                        
                        foreach ($selectors as $selector) {
                            $selector = trim($selector);
                            // Remove any existing .hero-freedom-series prefix to avoid duplication
                            $selector = preg_replace('/^\.' . preg_quote($unique_class, '/') . '\s+/', '', $selector);
                            $prefixed_selectors[] = ".{$unique_class} " . $selector;
                        }
                        
                        $output .= implode(', ', $prefixed_selectors) . "\n";
                    } else {
                        // It's a CSS property, comment, or @ rule - output as-is
                        $output .= $trimmed_line . "\n";
                    }
                }
                
                echo $output;
            } else {
                // User wrote just properties - wrap them
                echo ".{$unique_class} {\n";
                echo $custom_css . "\n";
                echo "}\n";
            }
            
            echo "</style>\n";
        }
    }
});

// Shortcode: [gaur_hero slug="my-slug"]
add_shortcode('gaur_hero', function($atts) {
    $atts = shortcode_atts(['slug' => ''], $atts);
    
    if (empty($atts['slug'])) {
        return '';
    }
    
    $query = new WP_Query([
        'post_type' => 'hero_section', 
        'name' => $atts['slug'],
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ]);
    
    if ( ! $query->have_posts() ) {
        return '';
    }
    
    $query->the_post();

    $btn_text = get_post_meta(get_the_ID(), 'button_text', true);
    $btn_link = get_post_meta(get_the_ID(), 'button_link', true);

    $bg_img   = get_the_post_thumbnail_url(get_the_ID(), 'full');
    
    // Get unique class for this hero
    $slug = $atts['slug'];
    $unique_class = 'hero-' . sanitize_html_class($slug);
    
    // Get custom CSS
    $custom_css = get_post_meta(get_the_ID(), '_hero_custom_css', true);
    $has_custom_css = !empty($custom_css);

    ob_start(); ?>
    
    <!-- Hero Section with inline style for background -->
    <section class="hero <?php echo esc_attr($unique_class); ?>" style="background-image: url('<?php echo esc_url($bg_img); ?>');">
        <div class="hero-content">
            <h1><?php the_title(); ?></h1>
            <div class="hero-text"><?php the_content(); ?></div>
            <?php if ($btn_text) : ?>
                <a href="<?php echo esc_url($btn_link); ?>" class="btn"><?php echo esc_html($btn_text); ?></a>
            <?php endif; ?>
        </div>
        
      
    </section>
    
    <?php
    wp_reset_postdata();
    return ob_get_clean();
});

// Debug helper for admins
add_action('wp_footer', function() {
    if (current_user_can('edit_posts') && isset($_GET['debug_hero_css'])) {
        echo '<div style="position: fixed; bottom: 10px; left: 10px; background: rgba(0,0,0,0.9); color: white; padding: 15px; z-index: 9999; font-size: 12px; max-width: 400px; border: 2px solid #ff3366;">';
        echo '<h3 style="margin-top: 0; color: #ff3366;">Hero CSS Debug</h3>';
        
        $heroes = get_posts([
            'post_type' => 'hero_section',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);
        
        foreach ($heroes as $hero) {
            $css = get_post_meta($hero->ID, '_hero_custom_css', true);
            if (!empty($css)) {
                echo '<div style="margin-bottom: 10px; padding: 10px; background: rgba(255,255,255,0.1);">';
                echo '<strong>' . esc_html($hero->post_title) . '</strong><br>';
                echo 'Raw CSS:<br>';
                echo '<pre style="background: #1e1e1e; color: #d4d4d4; padding: 5px; margin: 5px 0; font-size: 10px; overflow: auto; max-height: 100px;">';
                echo htmlspecialchars($css);
                echo '</pre>';
                echo '</div>';
            }
        }
        
        echo '</div>';
    }
});