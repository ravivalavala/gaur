<?php
/**
 * GAUR Hero Sections - Admin Logic
 */
if ( ! defined('ABSPATH') ) exit;

// 1. Add "Hero Styles" submenu page
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=hero_section',
        'Hero Styles',
        'Hero Styles',
        'manage_options',
        'hero-styles',
        'gaur_hero_styles_tab_page'
    );
});

function gaur_hero_styles_tab_page() {
    // Save CSS if form submitted
    if ( isset($_POST['gaur_hero_css']) ) {
        check_admin_referer('gaur_hero_save_css');
        update_option('gaur_hero_custom_css', wp_unslash($_POST['gaur_hero_css']));
        echo '<div class="updated"><p>Global styles saved successfully.</p></div>';
    }

    $css = get_option('gaur_hero_custom_css', '');
    ?>
    <div class="wrap">
        <h1>Hero Sections Settings</h1>
        
        <h2 class="nav-tab-wrapper">
            <a href="edit.php?post_type=hero_section" class="nav-tab">All Heroes</a>
            <a href="?post_type=hero_section&page=hero-styles" class="nav-tab nav-tab-active">Global Styles</a>
        </h2>

        <div style="margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #ddd;">
            <h3>Global Hero CSS</h3>
            <p>This CSS applies to ALL hero sections.</p>
            <form method="post">
                <?php wp_nonce_field('gaur_hero_save_css'); ?>
                <textarea name="gaur_hero_css" style="width:100%; height:300px; font-family:monospace;"><?php echo esc_textarea($css); ?></textarea>
                <p><input type="submit" class="button button-primary" value="Save Global CSS"></p>
            </form>
        </div>
    </div>
    <?php
}

// 2. Add meta box for hero settings (RIGHT SIDE)
add_action('add_meta_boxes', function() {
    add_meta_box(
        'hero_shortcode_settings',   // ID
        'Hero Shortcode Settings',   // Title
        'gaur_hero_shortcode_meta_box', // Callback
        'hero_section',              // Post type
        'side',                      // Context
        'high'                       // Priority
    );
});

function gaur_hero_shortcode_meta_box($post) {
    // Get current values
    $button_text = get_post_meta($post->ID, 'button_text', true);
    $button_link = get_post_meta($post->ID, 'button_link', true);
    
    // Security nonce
    wp_nonce_field('hero_meta_save', 'hero_meta_nonce');
    ?>
    
    <p><strong>Button Text:</strong></p>
    <input type="text" name="button_text" value="<?php echo esc_attr($button_text); ?>" class="widefat">
    
    <p style="margin-top: 15px;"><strong>Button Link:</strong></p>
    <input type="text" name="button_link" value="<?php echo esc_attr($button_link); ?>" class="widefat" placeholder="/shop or https://...">
    
    <hr style="margin: 20px 0;">
    
    <p><strong>Hero Slug:</strong></p>
    <input type="text" name="post_name" value="<?php echo esc_attr($post->post_name); ?>" class="widefat">
    <p class="description">Change this to update the shortcode</p>
    
    <hr style="margin: 20px 0;">
    
    <p><strong>Your Shortcode:</strong></p>
    <code style="display: block; background: #f5f5f5; padding: 10px; margin: 10px 0;">
        [gaur_hero slug="<?php echo esc_attr($post->post_name); ?>"]
    </code>
    <p class="description">Copy this into your page</p>
    
    <?php
}

// 3. Add meta box for Custom CSS (BELOW EDITOR)
add_action('add_meta_boxes', function() {
    add_meta_box(
        'hero_custom_css',           // ID
        'Custom CSS for This Hero',  // Title
        'gaur_hero_css_meta_box',    // Callback
        'hero_section',              // Post type
        'normal',                    // Context
        'high'                       // Priority
    );
});

function gaur_hero_css_meta_box($post) {
    // Get custom CSS for this hero
    $custom_css = get_post_meta($post->ID, '_hero_custom_css', true);
    
    // Get hero slug for CSS targeting
    $hero_slug = $post->post_name;
    $unique_class = 'hero-' . sanitize_html_class($hero_slug);
    
    wp_nonce_field('hero_css_save', 'hero_css_nonce');
    ?>
    
    <div style="margin-bottom: 20px; padding: 15px; background: #f0f6fc; border-radius: 4px;">
        <h3 style="margin-top: 0;">How to Use:</h3>
        <p>Add CSS that will apply <strong>only to this specific hero</strong>.</p>
        <p>This hero has a unique class: <code><?php echo esc_html($unique_class); ?></code></p>
        <p><strong>Examples:</strong></p>
        <pre style="background: #1e1e1e; color: #d4d4d4; padding: 10px; border-radius: 4px; overflow: auto;">
/* Change overlay color */
.<?php echo esc_html($unique_class); ?> .hero-content {
    background-color: rgba(0, 0, 0, 0.7) !important;
}

/* Change text color */
.<?php echo esc_html($unique_class); ?> .hero-content h1 {
    color: #ffffff !important;
}

/* Remove blur effect */
.<?php echo esc_html($unique_class); ?> .hero-content {
    backdrop-filter: none !important;
}

/* Custom button style */
.<?php echo esc_html($unique_class); ?> .hero-content .btn {
    background: #ff3366 !important;
    border-radius: 25px !important;
}

/* Add border */
.<?php echo esc_html($unique_class); ?> .hero-content {
    border: 3px solid #ffffff !important;
}</pre>
    </div>
    
    <p><strong>Enter your custom CSS:</strong></p>
    <textarea name="hero_custom_css" rows="10" style="width:100%; font-family:monospace; font-size: 14px;" 
              placeholder="Add CSS here. Example: .hero-content { background-color: rgba(0,0,0,0.5); }"><?php echo esc_textarea($custom_css); ?></textarea>
    
    <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div style="padding: 15px; background: #fff8e5; border-radius: 4px;">
            <h4 style="margin-top: 0;">Quick Presets:</h4>
            <button type="button" class="button button-small" onclick="insertCssPreset('dark')">Dark Overlay</button>
            <button type="button" class="button button-small" onclick="insertCssPreset('light')">Light Overlay</button>
            <button type="button" class="button button-small" onclick="insertCssPreset('noblur')">No Blur</button>
            <button type="button" class="button button-small" onclick="insertCssPreset('border')">Add Border</button>
            <button type="button" class="button button-small" onclick="insertCssPreset('gradient')">Gradient Button</button>
        </div>
        
        <div style="padding: 15px; background: #f0f6fc; border-radius: 4px;">
            <h4 style="margin-top: 0;">Available Classes:</h4>
            <ul style="margin: 0; font-size: 12px;">
                <li><code>.hero</code> - Hero container</li>
                <li><code>.hero-content</code> - Content box</li>
                <li><code>.hero-content h1</code> - Title</li>
                <li><code>.hero-content p</code> - Description</li>
                <li><code>.hero-content .btn</code> - Button</li>
            </ul>
        </div>
    </div>
    
    <script>
    function insertCssPreset(preset) {
        const textarea = document.querySelector('textarea[name="hero_custom_css"]');
        const uniqueClass = '<?php echo esc_js($unique_class); ?>';
        let css = '';
        
        switch(preset) {
            case 'dark':
                css = `.${uniqueClass} .hero-content {
    background-color: rgba(0, 0, 0, 0.7) !important;
    backdrop-filter: blur(10px) !important;
}`;
                break;
            case 'light':
                css = `.${uniqueClass} .hero-content {
    background-color: rgba(255, 255, 255, 0.8) !important;
    color: #333 !important;
}`;
                break;
            case 'noblur':
                css = `.${uniqueClass} .hero-content {
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
}`;
                break;
            case 'border':
                css = `.${uniqueClass} .hero-content {
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
    background-color: rgba(0, 0, 0, 0.4) !important;
}`;
                break;
            case 'gradient':
                css = `.${uniqueClass} .hero-content .btn {
    background: linear-gradient(90deg, #ff3366, #ff9933) !important;
    border: none !important;
}`;
                break;
        }
        
        if(textarea.value.trim() !== '' && !textarea.value.endsWith('\n')) {
            textarea.value += '\n\n';
        }
        textarea.value += css;
    }
    </script>
    
    <?php
}

// 4. Save meta box data
add_action('save_post', function($post_id) {
    // Check nonce for shortcode settings
    if (!isset($_POST['hero_meta_nonce']) || !wp_verify_nonce($_POST['hero_meta_nonce'], 'hero_meta_save')) {
        return;
    }
    
    // Check nonce for CSS settings
    if (!isset($_POST['hero_css_nonce']) || !wp_verify_nonce($_POST['hero_css_nonce'], 'hero_css_save')) {
        return;
    }
    
    // Don't save during autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Save button text
    if (isset($_POST['button_text'])) {
        update_post_meta($post_id, 'button_text', sanitize_text_field($_POST['button_text']));
    }
    
    // Save button link
    if (isset($_POST['button_link'])) {
        update_post_meta($post_id, 'button_link', esc_url_raw($_POST['button_link']));
    }
    
    // Save slug if changed
    if (isset($_POST['post_name']) && !empty($_POST['post_name'])) {
        $new_slug = sanitize_title($_POST['post_name']);
        $post = get_post($post_id);
        
        // Only update if slug changed
        if ($post->post_name !== $new_slug) {
            wp_update_post([
                'ID' => $post_id,
                'post_name' => $new_slug
            ]);
        }
    }
    
    // Save custom CSS
    if (isset($_POST['hero_custom_css'])) {
        $css = wp_unslash($_POST['hero_custom_css']);
        $css = wp_strip_all_tags($css); // Remove any HTML tags for safety
        update_post_meta($post_id, '_hero_custom_css', $css);
    }
});

// 5. Add CSS column to hero list
add_filter('manage_hero_section_posts_columns', function($columns) {
    $columns['hero_slug'] = 'Slug';
    $columns['hero_shortcode'] = 'Shortcode';
    $columns['hero_css'] = 'Custom CSS';
    return $columns;
});

add_action('manage_hero_section_posts_custom_column', function($column, $post_id) {
    if ($column === 'hero_slug') {
        $post = get_post($post_id);
        echo '<code>' . esc_html($post->post_name) . '</code>';
    }
    
    if ($column === 'hero_shortcode') {
        $post = get_post($post_id);
        echo '<input type="text" readonly value=\'[gaur_hero slug="' . esc_attr($post->post_name) . '"]\' style="width: 100%; background: #f5f5f5; border: 1px solid #ddd; padding: 5px;" onclick="this.select()">';
    }
    
    if ($column === 'hero_css') {
        $custom_css = get_post_meta($post_id, '_hero_custom_css', true);
        if (!empty($custom_css)) {
            echo '<span class="dashicons dashicons-yes" style="color: #46b450;"></span> Has custom CSS';
        } else {
            echo '<span class="dashicons dashicons-no" style="color: #dc3232;"></span> No custom CSS';
        }
    }
}, 10, 2);