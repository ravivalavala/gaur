<?php
/**
 * Plugin Name: Clothing & Shoes Shop Setup - JSON Data File
 * Description: Creates clothing & shoes shop data with multiple images per product from JSON file
 * Version: 1.3.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check WooCommerce dependency
 */
add_action('admin_init', function () {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>WooCommerce is required.</strong></p></div>';
        });
    }
});

/**
 * ✅ CORRECT rewrite flush (ONLY on activation)
 */
register_activation_hook(__FILE__, function () {
    if (class_exists('WooCommerce')) {
        WC()->init(); // ensure WC taxonomies & rewrites exist
        flush_rewrite_rules();
    }
});

class ClothingShoesSetup {

    private static $instance = null;
    private $created_products = 0;
    private $created_images = 0;
    private $json_file_path;

    public static function get_instance() {
        return self::$instance ??= new self();
    }

    private function __construct() {
        // Set the JSON file path
        $this->json_file_path = plugin_dir_path(__FILE__) . 'products.json';
        
        add_action('admin_menu', [$this, 'menu']);
        add_action('wp_ajax_css_create_all', [$this, 'create_all_ajax']);
    }

    public function menu() {
        add_submenu_page(
            'woocommerce',
            'Clothing Shop Setup',
            'Clothing Shop Setup',
            'manage_options',
            'clothing-shoes-setup',
            [$this, 'page']
        );
    }

    public function page() {
        $json_exists = file_exists($this->json_file_path);
        
        ?>
        <div class="wrap">
            <h1>Clothing & Shoes Shop Setup</h1>
            
            <?php if (!$json_exists): ?>
                <div class="notice notice-error">
                    <p><strong>Error:</strong> products.json file not found in plugin directory.</p>
                </div>
            <?php endif; ?>
            
            <p>Click the button below to create products from the JSON file and upload images from product folders.</p>
            <button id="css-run" class="button button-primary" <?php echo !$json_exists ? 'disabled' : ''; ?>>
                <?php echo $json_exists ? 'Run Setup' : 'JSON File Missing'; ?>
            </button>
            
            <div id="css-progress" style="margin: 20px 0; display: none;">
                <h3>Setup Progress</h3>
                <div class="progress-bar" style="background: #f1f1f1; border-radius: 5px; overflow: hidden;">
                    <div id="css-progress-bar" style="height: 20px; background: #0073aa; width: 0%; transition: width 0.3s;"></div>
                </div>
                <p id="css-status">Initializing...</p>
                <p id="css-details" style="font-size: 12px; color: #666;"></p>
            </div>
            
            <div id="css-summary" style="display: none; margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                <h3>Setup Summary</h3>
                <table style="width: 100%;">
                    <tr>
                        <td><strong>Products Created:</strong></td>
                        <td id="summary-products">0</td>
                    </tr>
                    <tr>
                        <td><strong>Images Uploaded:</strong></td>
                        <td id="summary-images">0</td>
                    </tr>
                    <tr>
                        <td><strong>Completion Time:</strong></td>
                        <td id="summary-time">-</td>
                    </tr>
                </table>
            </div>
            
            <div id="css-errors" style="display: none; margin-top: 20px; padding: 15px; background: #f8d7da; border-radius: 5px; color: #721c24;">
                <h3>Setup Errors</h3>
                <pre id="css-error-details" style="background: transparent; border: none; color: #721c24;"></pre>
            </div>
            
            <div id="css-debug" style="display: none; margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
                <h3>Debug Information</h3>
                <pre id="css-debug-details"></pre>
            </div>
            
            <pre id="css-result" style="display: none; background: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 400px; overflow: auto; margin-top: 20px;"></pre>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#css-run').on('click', function () {
                    var $button = $(this);
                    $button.prop('disabled', true).text('Processing...');
                    $('#css-progress').show();
                    $('#css-summary').hide();
                    $('#css-errors').hide();
                    $('#css-debug').hide();
                    $('#css-result').hide();
                    $('#css-details').text('Starting setup...');
                    
                    jQuery.post(ajaxurl, {
                        action: 'css_create_all',
                        nonce: '<?php echo wp_create_nonce('css_nonce'); ?>'
                    }, function (response) {
                        if (response.success) {
                            $('#css-result').text(JSON.stringify(response.data, null, 2)).show();
                            $('#css-progress-bar').css('width', '100%');
                            $('#css-status').text('Complete!');
                            $('#css-details').text('Setup completed successfully.');
                            
                            // Show summary
                            $('#css-summary').show();
                            $('#summary-products').text(response.data.products_created);
                            $('#summary-images').text(response.data.images_uploaded);
                            $('#summary-time').text(response.data.timestamp);
                            
                            // Show debug if available
                            if (response.data.debug) {
                                $('#css-debug').show();
                                $('#css-debug-details').text(response.data.debug);
                            }
                        } else {
                            $('#css-errors').show();
                            $('#css-error-details').text('Error: ' + response.data);
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        $('#css-errors').show();
                        $('#css-error-details').text('AJAX request failed: ' + textStatus + ' - ' + errorThrown);
                    }).always(function() {
                        $button.prop('disabled', false).text('Run Setup Again');
                    });
                });
                
                // Show progress animation
                var $progressBar = $('#css-progress-bar');
                var interval;
                $(document).ajaxSend(function() {
                    var width = 0;
                    clearInterval(interval);
                    interval = setInterval(function() {
                        if (width >= 90) {
                            clearInterval(interval);
                        } else {
                            width += 10;
                            $progressBar.css('width', width + '%');
                        }
                    }, 500);
                });
                
                $(document).ajaxComplete(function() {
                    clearInterval(interval);
                });
            });
        </script>
        <style>
            .progress-bar {
                position: relative;
                height: 20px;
                background: #f1f1f1;
                border-radius: 5px;
                overflow: hidden;
            }
            #css-progress-bar {
                height: 100%;
                background: linear-gradient(90deg, #0073aa, #00a0d2);
                width: 0%;
                transition: width 0.5s ease-in-out;
            }
            .widefat {
                border-collapse: collapse;
                width: 100%;
            }
            .widefat td {
                padding: 8px 10px;
                border-bottom: 1px solid #ddd;
            }
        </style>
        <?php
    }

    public function create_all_ajax() {
        check_ajax_referer('css_nonce', 'nonce');

        // Check if JSON file exists
        if (!file_exists($this->json_file_path)) {
            wp_send_json_error('JSON file not found: products.json');
            return;
        }

        $this->create_categories();
        $this->create_products();

        wp_send_json_success([
            'message' => 'Setup completed from JSON file.',
            'products_created' => $this->created_products,
            'images_uploaded' => $this->created_images,
            'timestamp' => current_time('mysql'),
            'json_file' => basename($this->json_file_path),
            'debug' => $this->get_debug_info()
        ]);
    }

    /**
     * Create categories
     */
    private function create_categories() {
        $cats = [
            ['Men', 'men'],
            ['Women', 'women'],

            ['Clothing', 'men-clothing', 'men'],
            ['Footwear', 'men-footwear', 'men'],

            ['Clothing', 'women-clothing', 'women'],
            ['Footwear', 'women-footwear', 'women'],

            ['T-Shirts', 'men-t-shirts', 'men-clothing'],
            ['Jeans', 'men-jeans', 'men-clothing'],
            ['Shirts', 'men-shirts', 'men-clothing'],

            ['T-Shirts', 'women-t-shirts', 'women-clothing'],
            ['Jeans', 'women-jeans', 'women-clothing'],
            ['Shirts', 'women-shirts', 'women-clothing'],
            
            ['Dresses', 'women-dresses', 'women-clothing'],
            ['Shoes', 'men-shoes', 'men-footwear'],
            ['Sneakers', 'men-sneakers', 'men-footwear'],
            ['Accessories', 'accessories'],
        ];

        foreach ($cats as $cat) {
            [$name, $slug, $parent_slug] = array_pad($cat, 3, null);

            if (term_exists($slug, 'product_cat')) {
                continue;
            }

            $parent_id = 0;
            if ($parent_slug) {
                $parent = get_term_by('slug', $parent_slug, 'product_cat');
                $parent_id = $parent ? $parent->term_id : 0;
            }

            wp_insert_term($name, 'product_cat', [
                'slug'   => $slug,
                'parent' => $parent_id
            ]);
        }
    }

    /**
     * Create products from JSON file with multiple images
     */
    private function create_products() {
        $json_data = file_get_contents($this->json_file_path);
        $data = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('JSON parse error: ' . json_last_error_msg());
            return;
        }

        if (!isset($data['products']) || !is_array($data['products'])) {
            wp_send_json_error('Invalid JSON structure: Missing products array');
            return;
        }

        foreach ($data['products'] as $product_data) {
            $product_id = $this->create_single_product($product_data);
            if ($product_id) {
                $this->created_products++;
                
                // Upload multiple images for this product
                if (isset($product_data['image_folder'])) {
                    $images_uploaded = $this->upload_product_images($product_id, $product_data);
                    $this->created_images += $images_uploaded;
                }
            }
        }
    }

    /**
     * Create a single product
     */
    private function create_single_product($data) {
        // Check if product already exists
        if (wc_get_product_id_by_sku($data['sku'])) {
            error_log('Product SKU already exists: ' . $data['sku']);
            return false;
        }

        // Create product
        $product = new WC_Product_Simple();
        $product->set_name($data['name']);
        $product->set_sku($data['sku']);
        $product->set_regular_price($data['price']);
        $product->set_status('publish');
        $product->set_stock_status('instock');
        
        // Set stock quantity
        if (isset($data['stock_quantity'])) {
            $product->set_manage_stock(true);
            $product->set_stock_quantity($data['stock_quantity']);
        }

        // Set description
        if (isset($data['description'])) {
            $product->set_description($data['description']);
            $product->set_short_description($data['description']);
        }

        // Set categories
        if (isset($data['categories']) && is_array($data['categories'])) {
            $category_ids = [];
            foreach ($data['categories'] as $category_slug) {
                $category = get_term_by('slug', $category_slug, 'product_cat');
                if ($category) {
                    $category_ids[] = $category->term_id;
                }
            }
            if (!empty($category_ids)) {
                $product->set_category_ids($category_ids);
            }
        }

        // Save product
        $product_id = $product->save();

        return $product_id;
    }

    /**
     * Upload multiple images for a product from its folder
     */
    private function upload_product_images($product_id, $product_data) {
        $images_uploaded = 0;
        
        if (!isset($product_data['image_folder'])) {
            error_log('No image folder specified for product: ' . $product_data['name']);
            return $images_uploaded;
        }

        $folder_name = $product_data['image_folder'];
        $images_dir = plugin_dir_path(__FILE__) . 'images/' . $folder_name . '/';
        
        // Check if directory exists
        if (!file_exists($images_dir) || !is_dir($images_dir)) {
            error_log('Image folder not found: ' . $images_dir);
            return $images_uploaded;
        }

        // Get all image files from the folder
        $all_images = $this->get_image_files_from_folder($images_dir);
        
        if (empty($all_images)) {
            error_log('No images found in folder: ' . $images_dir);
            return $images_uploaded;
        }

        // Sort images alphabetically by default
        sort($all_images);

        // If gallery_order is provided and is an array, use that order
        if (isset($product_data['gallery_order']) && is_array($product_data['gallery_order']) && !empty($product_data['gallery_order'])) {
            $ordered_images = [];
            foreach ($product_data['gallery_order'] as $ordered_file) {
                if (in_array($ordered_file, $all_images)) {
                    $ordered_images[] = $ordered_file;
                }
            }
            // Add any remaining images not in the order list
            $remaining_images = array_diff($all_images, $ordered_images);
            $all_images = array_merge($ordered_images, $remaining_images);
        }

        $attachment_ids = [];
        
        foreach ($all_images as $image_file) {
            $image_path = $images_dir . $image_file;
            
            if (!file_exists($image_path)) {
                error_log('Image file not found: ' . $image_path);
                continue;
            }

            $attachment_id = $this->upload_single_image($image_path, $product_id, $product_data['name']);
            
            if ($attachment_id && !is_wp_error($attachment_id)) {
                $attachment_ids[] = $attachment_id;
                $images_uploaded++;
            } else {
                error_log('Failed to upload image: ' . $image_path);
            }
        }

        // Set featured image (first image)
        if (!empty($attachment_ids)) {
            set_post_thumbnail($product_id, $attachment_ids[0]);
            
            // Set gallery images (all remaining images)
            if (count($attachment_ids) > 1) {
                $gallery_ids = array_slice($attachment_ids, 1);
                update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
            }
        }

        return $images_uploaded;
    }

    /**
     * Get all image files from a folder
     */
    private function get_image_files_from_folder($folder_path) {
        $images = [];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
        
        if (!is_dir($folder_path)) {
            return $images;
        }

        $files = scandir($folder_path);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $file_path = $folder_path . $file;
            $file_info = pathinfo($file_path);
            
            if (isset($file_info['extension'])) {
                $extension = strtolower($file_info['extension']);
                
                if (in_array($extension, $allowed_extensions) && is_file($file_path)) {
                    $images[] = $file;
                }
            }
        }

        return $images;
    }

    /**
     * Upload a single image to WordPress media library
     */
    private function upload_single_image($image_path, $product_id, $product_name) {
        // Check if image already exists
        $existing_id = $this->get_image_id_by_filename(basename($image_path));
        if ($existing_id) {
            return $existing_id;
        }

        // Prepare file data
        $filename = basename($image_path);
        $filetype = wp_check_filetype($filename, null);
        
        // Prepare image title
        $image_title = $product_name . ' - ' . pathinfo($filename, PATHINFO_FILENAME);
        $image_title = sanitize_text_field($image_title);

        // Read file contents
        $file_contents = file_get_contents($image_path);
        if ($file_contents === false) {
            error_log('Failed to read image file: ' . $image_path);
            return false;
        }

        // Upload file using wp_upload_bits
        $upload_result = wp_upload_bits($filename, null, $file_contents);
        
        if ($upload_result['error']) {
            error_log('Image upload error: ' . $upload_result['error']);
            return false;
        }

        // Prepare attachment data
        $attachment = [
            'post_mime_type' => $filetype['type'],
            'post_title'     => $image_title,
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        // Insert attachment
        $attach_id = wp_insert_attachment($attachment, $upload_result['file'], $product_id);
        
        if (is_wp_error($attach_id)) {
            error_log('Attachment creation error: ' . $attach_id->get_error_message());
            return false;
        }

        // Generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $upload_result['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }

    /**
     * Check if image already exists in media library by filename
     */
    private function get_image_id_by_filename($filename) {
        global $wpdb;
        
        $attachment = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key = '_wp_attached_file' 
            AND meta_value LIKE %s",
            '%' . $wpdb->esc_like($filename)
        ));

        return $attachment ? (int)$attachment : false;
    }

    /**
     * Get debug information
     */
    private function get_debug_info() {
        $debug = [];
        
        // Check JSON file
        if (file_exists($this->json_file_path)) {
            $debug[] = '✅ JSON file exists: ' . $this->json_file_path;
            
            $json_data = file_get_contents($this->json_file_path);
            $data = json_decode($json_data, true);
            
            if (isset($data['products'])) {
                $debug[] = '✅ Found ' . count($data['products']) . ' products in JSON';
                
                // Check each product's image folder
                foreach ($data['products'] as $product) {
                    if (isset($product['image_folder'])) {
                        $folder_path = plugin_dir_path(__FILE__) . 'images/' . $product['image_folder'] . '/';
                        if (file_exists($folder_path)) {
                            $images = $this->get_image_files_from_folder($folder_path);
                            $debug[] = '✅ Folder ' . $product['image_folder'] . ': ' . count($images) . ' images found';
                        } else {
                            $debug[] = '❌ Folder ' . $product['image_folder'] . ': NOT FOUND';
                        }
                    }
                }
            }
        } else {
            $debug[] = '❌ JSON file not found';
        }
        
        // Check images directory
        $images_dir = plugin_dir_path(__FILE__) . 'images/';
        if (file_exists($images_dir)) {
            $debug[] = '✅ Images directory exists';
        } else {
            $debug[] = '❌ Images directory not found';
        }
        
        return implode("\n", $debug);
    }
}

add_action('plugins_loaded', function () {
    if (class_exists('WooCommerce')) {
        ClothingShoesSetup::get_instance();
    }
});