<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * 1. Define the sections in a central array.
 * This makes it easy to add "Hats" or "Accessories" later.
 */
function gaur_get_shop_section_definitions() {
    return [
        'gaur_tactical_section' => [ 'label' => 'Tactical Boots', 'hero' => 'gaur-tactical-boots', 'category' => 'mens-tactical-boots', 'title' => 'Tactical Boots', 'limit' => 4 ],
        'gaur_freedom_section'  => [ 'label' => 'Freedom Section', 'hero' => 'freedom-series-wear-your-boldness', 'category' => 'men-t-shirts', 'title' => 'Freedom Collection', 'limit' => 4 ],
        'gaur_clothing_section' => [ 'label' => 'Clothing Section', 'hero' => 'stylish-hoodies-t-shirts', 'category' => 'clothing', 'title' => 'Clothing', 'limit' => 4 ],
        'gaur_shoes_section'    => [ 'label' => 'Shoes Section', 'hero' => 'trendy-shoes-collection', 'category' => 'shoes', 'title' => 'Shoes', 'limit' => 4 ],
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

            foreach ( $sections as $id => $section ) :
                $val = wp_parse_args(get_option($id, []), $section);
                ?>
                <div class="gaur-settings-box" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px;">
                    <h2><?php echo esc_html($section['label']); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">Hero Slug</th>
                            <td>
                                <input type="text" name="<?php echo $id; ?>[hero]" class="regular-text" value="<?php echo esc_attr($val['hero']); ?>">
                                <p class="description">Hero displayed immediately before this product section.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Enable Section</th>
                            <td>
                                <input type="checkbox" name="<?php echo $id; ?>[enabled]" value="1" <?php checked($val['enabled'] ?? 0, 1); ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Display Title</th>
                            <td>
                                <input type="text" name="<?php echo $id; ?>[title]" class="regular-text" value="<?php echo esc_attr($val['title']); ?>" placeholder="e.g. New Arrivals">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Category Slug</th>
                            <td>
                                <select name="<?php echo $id; ?>[category]" class="regular-text">
                                    <option value="">-- Select Category --</option>
                                    <?php
                                    $categories = get_terms([
                                        'taxonomy'   => 'product_cat',
                                        'hide_empty' => false,
                                    ]);
                                    if (!is_wp_error($categories)) {
                                        foreach ($categories as $cat) {
                                            $selected = ($val['category'] === $cat->slug) ? 'selected' : '';
                                            echo '<option value="' . esc_attr($cat->slug) . '" ' . $selected . '>' . esc_html($cat->name) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Product Limit</th>
                            <td>
                                <input type="number" min="1" max="24" name="<?php echo $id; ?>[limit]" value="<?php echo esc_attr($val['limit']); ?>">
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
