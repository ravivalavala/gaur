<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * 1. Define the sections in a central array.
 * This makes it easy to add "Hats" or "Accessories" later.
 */
function gaur_get_shop_section_definitions() {
    return [
        'gaur_clothing_section' => 'Clothing Section',
        'gaur_shoes_section'    => 'Shoes Section',
    ];
}

// Add admin menu page
add_action('admin_menu', function () {
    add_theme_page(
        'GAUR Shop Sections',
        'GAUR Sections',
        'manage_options',
        'gaur-shop-sections',
        'gaur_shop_sections_settings'
    );
});

// 2. Register settings using a loop
add_action('admin_init', function () {
    $sections = gaur_get_shop_section_definitions();
    foreach ( $sections as $id => $label ) {
        register_setting('gaur_shop_sections', $id);
    }
});

// 3. Admin page callback with dynamic HTML generation
function gaur_shop_sections_settings() {
    $sections = gaur_get_shop_section_definitions();
    ?>
    <div class="wrap">
        <h1>GAUR Shop Sections</h1>
        <hr>

        <form method="post" action="options.php">
            <?php 
            settings_fields('gaur_shop_sections'); 
            
            foreach ( $sections as $id => $label ) : 
                $val = get_option($id, []); 
                ?>
                <div class="gaur-settings-box" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px;">
                    <h2><?php echo esc_html($label); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Section</th>
                            <td>
                                <input type="checkbox" name="<?php echo $id; ?>[enabled]" value="1" <?php checked($val['enabled'] ?? 0, 1); ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Display Title</th>
                            <td>
                                <input type="text" name="<?php echo $id; ?>[title]" class="regular-text" value="<?php echo esc_attr($val['title'] ?? ''); ?>" placeholder="e.g. New Arrivals">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Category Slug</th>
                            <td>
                                <input type="text" name="<?php echo $id; ?>[category]" class="regular-text" value="<?php echo esc_attr($val['category'] ?? ''); ?>" placeholder="e.g. shoes">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Product Limit</th>
                            <td>
                                <input type="number" name="<?php echo $id; ?>[limit]" value="<?php echo esc_attr($val['limit'] ?? 8); ?>">
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endforeach; ?>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}