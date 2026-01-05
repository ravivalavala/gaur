<?php
/**
 * GAUR Hero Sections - Main Bridge
 */
if ( ! defined('ABSPATH') ) exit;

// Get the correct base path for our files
function gaur_get_hero_base_path() {
    // Files are in: gaur-theme/inc/hero-sections/
    return trailingslashit(dirname(__FILE__));
}

// 1. Create the Main "Hero Sections" Menu and Register Post Type
add_action('init', function() {
    register_post_type('hero_section', [
        'labels' => [
            'name'          => 'Hero Sections',
            'singular_name' => 'Hero Section',
            'menu_name'     => 'Hero Sections',
            'add_new'       => 'Add New Hero',
            'add_new_item'  => 'Add New Hero Section',
            'edit_item'     => 'Edit Hero Section',
            'view_item'     => 'View Hero Section',
        ],
        'public'       => true,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-images-alt2',
        'supports'     => ['title', 'editor', 'thumbnail'],
        'rewrite'      => ['slug' => 'hero'],
        'has_archive'  => false,
    ]);
});


// 2. Load Default Data (CSS, JSON Content, and Images) on Activation
function gaur_load_hero_sample_data() {
    // Get the correct base path
    $base_path = gaur_get_hero_base_path();
    
    // A) Load CSS from file to Database
    $css_path = $base_path . 'hero-sections.css';
    if ( file_exists($css_path) && ! get_option('gaur_hero_custom_css') ) {
        $css_content = file_get_contents($css_path);
        if ( $css_content !== false ) {
            update_option('gaur_hero_custom_css', $css_content);
        }
    }
    
    // B) Load Sample Content and Images from JSON
    $json_path = $base_path . 'hero-data.json';
    if ( ! file_exists($json_path) ) {
        error_log('GAUR Hero: JSON file not found at ' . $json_path);
        return false;
    }
    
    $json_data = file_get_contents($json_path);
    $samples   = json_decode($json_data, true);
    
    if ( ! is_array($samples) ) {
        error_log('GAUR Hero: Invalid JSON data');
        return false;
    }
    
    // Load WordPress admin functions for image handling
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    
    $imported_count = 0;
    
    foreach ( $samples as $sample ) {
        // Check if this hero already exists by slug
        $existing = get_posts([
            'post_type' => 'hero_section',
            'name' => $sample['slug'],
            'posts_per_page' => 1,
            'post_status' => 'any'
        ]);
        
        if ( ! empty($existing) ) {
            error_log('GAUR Hero: Skipping "' . $sample['slug'] . '" - already exists');
            continue;
        }
        
        $hero_id = wp_insert_post([
            'post_title'   => sanitize_text_field($sample['title']),
            'post_content' => wp_kses_post($sample['content']),
            'post_status'  => 'publish',
            'post_type'    => 'hero_section',
            'post_name'    => sanitize_title($sample['slug'])
        ]);
        
        if ( is_wp_error($hero_id) ) {
            error_log('GAUR Hero: Failed to create post - ' . $hero_id->get_error_message());
            continue;
        }
        
        update_post_meta($hero_id, 'button_text', sanitize_text_field($sample['button_text']));
        update_post_meta($hero_id, 'button_link', esc_url_raw(home_url($sample['button_link'])));
        
        // ✅ ADD THIS: Save CSS from JSON
        if ( isset($sample['css']) && !empty(trim($sample['css'])) ) {
            $css = wp_unslash($sample['css']);
            $css = wp_strip_all_tags($css); // Remove any HTML tags for safety
            update_post_meta($hero_id, '_hero_custom_css', $css);
            error_log('GAUR Hero: CSS imported for "' . $sample['title'] . '"');
        }
        
        // Handle image import
        $img_filename = $sample['image'];
        $img_path = $base_path . 'images/' . $img_filename;
        
        if ( file_exists($img_path) ) {
            // Method 1: Using media_sideload_image (simpler)
            $attachment_id = media_sideload_image($img_path, $hero_id, $sample['title'], 'id');
            
            if ( ! is_wp_error($attachment_id) ) {
                set_post_thumbnail($hero_id, $attachment_id);
                error_log('GAUR Hero: Image set for "' . $sample['title'] . '"');
            } else {
                error_log('GAUR Hero: Failed to upload image - ' . $attachment_id->get_error_message());
            }
        } else {
            error_log('GAUR Hero: Image file not found at ' . $img_path);
        }
        
        $imported_count++;
        error_log('GAUR Hero: Created hero - "' . $sample['title'] . '" (ID: ' . $hero_id . ')');
    }
    
    return $imported_count;
}

// Hook for theme activation
add_action('after_setup_theme', function() {
    if ( ! get_option('gaur_hero_sample_loaded') ) {
        $count = gaur_load_hero_sample_data();
        if ( $count !== false ) {
            update_option('gaur_hero_sample_loaded', true);
            update_option('gaur_hero_sample_count', $count);
            error_log('GAUR Hero: Auto-loaded ' . $count . ' sample hero sections');
        }
    }
});

// Add admin notice to show import status
add_action('admin_notices', function() {
    if ( isset($_GET['gaur_hero_imported']) && $_GET['gaur_hero_imported'] === '1' ) {
        $count = get_option('gaur_hero_sample_count', 0);
        ?>
        <div class="notice notice-success is-dismissible">
            <p>✅ GAUR Hero Sections: Successfully imported <?php echo intval($count); ?> sample hero sections.</p>
        </div>
        <?php
    }
    
    if ( isset($_GET['gaur_hero_imported']) && $_GET['gaur_hero_imported'] === '0' ) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p>❌ GAUR Hero Sections: Failed to import sample data. Check error log.</p>
        </div>
        <?php
    }
});

// Handle manual import
add_action('admin_init', function() {
    if ( isset($_GET['gaur_import_samples']) && isset($_GET['post_type']) && $_GET['post_type'] === 'hero_section' ) {
        // Verify nonce
        if ( ! wp_verify_nonce($_GET['_wpnonce'], 'gaur_import_samples') ) {
            wp_die('Security check failed');
        }
        
        // Clear previous sample data flag
        delete_option('gaur_hero_sample_loaded');
        delete_option('gaur_hero_sample_count');
        
        // Import samples
        $count = gaur_load_hero_sample_data();
        
        if ( $count !== false && $count > 0 ) {
            update_option('gaur_hero_sample_loaded', true);
            update_option('gaur_hero_sample_count', $count);
            
            // Redirect with success message
            wp_redirect(admin_url('edit.php?post_type=hero_section&gaur_hero_imported=1'));
            exit;
        } else {
            // Redirect with error message
            wp_redirect(admin_url('edit.php?post_type=hero_section&gaur_hero_imported=0'));
            exit;
        }
    }
});

// Add manual import button in admin
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=hero_section',
        'Import Samples',
        'Import Samples',
        'manage_options',
        'hero-import-samples',
        'gaur_render_import_page'
    );
});

function gaur_render_import_page() {
    // Get the correct base path
    $base_path = gaur_get_hero_base_path();
    
    // Check if we have files
    $json_path = $base_path . 'hero-data.json';
    $images_path = $base_path . 'images/';
    $css_path = $base_path . 'hero-sections.css';
    
    $has_json = file_exists($json_path);
    $has_images = file_exists($images_path);
    $has_css = file_exists($css_path);
    
    // Count image files if directory exists
    $image_count = 0;
    $image_list = [];
    if ($has_images) {
        $image_files = glob($images_path . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        $image_count = is_array($image_files) ? count($image_files) : 0;
        foreach ($image_files as $file) {
            $image_list[] = basename($file);
        }
    }
    
    // Read JSON content to preview
    $json_preview = '';
    if ($has_json) {
        $json_content = file_get_contents($json_path);
        $json_data = json_decode($json_content, true);
        if (is_array($json_data)) {
            $json_preview = '<pre style="background: #f5f5f5; padding: 10px; overflow: auto; max-height: 200px;">' . 
                          htmlspecialchars(json_encode($json_data, JSON_PRETTY_PRINT)) . 
                          '</pre>';
        }
    }
    ?>
    <div class="wrap">
        <h1>Import Sample Hero Sections</h1>
        
        <div class="card" style="margin-top: 20px;">
            <h2 class="title">Import Sample Content</h2>
            
            <div style="margin-bottom: 20px; padding: 10px; background: #f8f9fa; border-left: 4px solid <?php echo $has_json ? '#46b450' : '#dc3232'; ?>; border-radius: 4px;">
                <h3 style="margin-top: 0;">System Check</h3>
                <table class="widefat" style="background: white;">
                    <tr>
                        <td width="200"><strong>📁 Current Directory:</strong></td>
                        <td><code><?php echo $base_path; ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>📄 JSON File:</strong></td>
                        <td>
                            <?php if ($has_json): ?>
                                ✅ Found at: <code><?php echo $json_path; ?></code>
                            <?php else: ?>
                                ❌ Missing: <code><?php echo $json_path; ?></code>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>🖼️ Images Directory:</strong></td>
                        <td>
                            <?php if ($has_images): ?>
                                ✅ Found (<?php echo $image_count; ?> images)
                                <?php if ($image_count > 0): ?>
                                    <br><small>Images: <?php echo implode(', ', array_slice($image_list, 0, 5)); ?>
                                    <?php if ($image_count > 5): ?>... and <?php echo ($image_count - 5); ?> more<?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                ❌ Missing: <code><?php echo $images_path; ?></code>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>🎨 CSS File:</strong></td>
                        <td>
                            <?php if ($has_css): ?>
                                ✅ Found
                            <?php else: ?>
                                ⚠️ Missing (optional)
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <?php if ($has_json && $has_images): ?>
                    <div style="margin-top: 10px; padding: 10px; background: #e7f4e4; border-radius: 4px;">
                        <p style="color: #2c662d; margin: 0;">✓ All required files are in the correct location!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($json_preview): ?>
                <div style="margin: 20px 0;">
                    <h4>JSON Content Preview:</h4>
                    <?php echo $json_preview; ?>
                </div>
            <?php endif; ?>
            
            <p>This will import the sample hero sections from <code>hero-data.json</code> including images.</p>
            <p><strong>⚠️ Important:</strong> This will NOT duplicate existing hero sections with the same slug.</p>
            
            <div style="margin-top: 30px; padding: 20px; background: #f0f6fc; border-radius: 4px;">
                <?php if ($has_json && $has_images): ?>
                    <a href="<?php echo wp_nonce_url(
                        admin_url('edit.php?post_type=hero_section&gaur_import_samples=1'), 
                        'gaur_import_samples', 
                        '_wpnonce'
                    ); ?>" 
                       class="button button-primary button-large" 
                       onclick="return confirm('Are you sure you want to import sample hero sections?\\n\\nThis will create new hero sections but will NOT overwrite existing ones.')">
                        🚀 Import Sample Hero Sections Now
                    </a>
                    <p class="description" style="margin-top: 10px;">Clicking this button will import all sample data from the JSON file.</p>
                <?php else: ?>
                    <button class="button button-large" disabled style="opacity: 0.6;">
                        🚫 Cannot Import - Missing Files
                    </button>
                    <p style="color: #dc3232; font-weight: bold; margin-top: 10px;">
                        ⚠️ Required files are missing. Please check the System Check above.
                    </p>
                <?php endif; ?>
                
                <a href="<?php echo admin_url('edit.php?post_type=hero_section'); ?>" 
                   class="button button-large" 
                   style="margin-left: 10px;">
                    ↩️ Return to Hero Sections
                </a>
            </div>
            
            <?php if (!$has_json || !$has_images): ?>
                <div style="margin-top: 30px; padding: 20px; background: #fff8e5; border: 1px solid #ffb900; border-radius: 4px;">
                    <h3>📁 Expected Folder Structure:</h3>
                    <pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow: auto;">
gaur-theme/
└── inc/
    └── hero-sections/
        ├── hero-sections.php      <strong style="color: #4ec9b0;">(This file)</strong>
        ├── hero-sections.css      <strong><?php echo $has_css ? '✅' : '⚠️'; ?></strong>
        ├── hero-data.json         <strong><?php echo $has_json ? '✅' : '❌ MISSING'; ?></strong>
        ├── hero-admin.php
        ├── hero-frontend.php
        └── images/                <strong><?php echo $has_images ? '✅' : '❌ MISSING'; ?></strong>
            ├── freedom.jpg
            └── shoes.jpg
                    </pre>
                    <p><strong>Solution:</strong> Make sure all files are in the same directory as <code>hero-sections.php</code></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h3>📊 Current Status</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Sample Data Loaded:</strong></td>
                        <td>
                            <code style="color: <?php echo get_option('gaur_hero_sample_loaded') ? '#46b450' : '#dc3232'; ?>">
                                <?php echo get_option('gaur_hero_sample_loaded') ? 'YES' : 'NO'; ?>
                            </code>
                        </td>
                        <td>
                            <?php if (get_option('gaur_hero_sample_loaded')): ?>
                                <span class="dashicons dashicons-yes" style="color: #46b450;"></span> Already loaded
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Sample Count:</strong></td>
                        <td>
                            <code><?php echo get_option('gaur_hero_sample_count', 0); ?> items</code>
                        </td>
                        <td>
                            <?php if (get_option('gaur_hero_sample_count', 0) > 0): ?>
                                <span class="dashicons dashicons-format-gallery"></span> 
                                <a href="<?php echo admin_url('edit.php?post_type=hero_section'); ?>">
                                    View Heroes
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Custom CSS Loaded:</strong></td>
                        <td>
                            <code style="color: <?php echo get_option('gaur_hero_custom_css') ? '#46b450' : '#dc3232'; ?>">
                                <?php echo get_option('gaur_hero_custom_css') ? 'YES' : 'NO'; ?>
                            </code>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('edit.php?post_type=hero_section&page=hero-styles'); ?>">
                                <span class="dashicons dashicons-edit"></span> Edit CSS
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <form method="post" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                <?php wp_nonce_field('gaur_reset_sample_data', 'gaur_reset_nonce'); ?>
                <input type="hidden" name="gaur_reset_sample_data" value="1">
                <button type="submit" class="button button-secondary" 
                        onclick="return confirm('This will reset the sample data flag.\n\nYou will be able to import samples again.\n\nExisting hero sections will NOT be deleted.\n\nContinue?')">
                    <span class="dashicons dashicons-update"></span> Reset Sample Data Flag
                </button>
                <p class="description">This only resets the "loaded" flag. It does NOT delete existing hero sections.</p>
            </form>
        </div>
    </div>
    <?php
}

// Handle reset sample data
add_action('admin_init', function() {
    if ( isset($_POST['gaur_reset_sample_data']) && isset($_POST['gaur_reset_nonce']) ) {
        if ( wp_verify_nonce($_POST['gaur_reset_nonce'], 'gaur_reset_sample_data') ) {
            delete_option('gaur_hero_sample_loaded');
            delete_option('gaur_hero_sample_count');
            wp_redirect(admin_url('edit.php?post_type=hero_section&page=hero-import-samples&reset=1'));
            exit;
        }
    }
    
    // Show reset success message
    if ( isset($_GET['reset']) && $_GET['reset'] === '1' ) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>✅ Sample data flag has been reset. You can now import samples again.</p>
            </div>
            <?php
        });
    }
});

// Load admin and frontend files
$base_path = gaur_get_hero_base_path();

// Load admin file
if ( is_admin() && file_exists($base_path . 'hero-admin.php') ) {
    require_once $base_path . 'hero-admin.php';
}

// Load frontend file (always load for shortcode functionality)
if ( file_exists($base_path . 'hero-frontend.php') ) {
    require_once $base_path . 'hero-frontend.php';
}