<?php
/**
 * GAUR Theme Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*--------------------------------------------------------------
# Theme Setup
--------------------------------------------------------------*/
add_action( 'after_setup_theme', function () {

    load_theme_textdomain( 'gaur', get_template_directory() . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );

    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-width'  => true,
        'flex-height' => true,
    ] );

    add_theme_support( 'html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ] );

    /* WooCommerce */
    add_theme_support( 'woocommerce' );

    /* Native Woo Gallery */
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    add_theme_support( 'wc-product-gallery-zoom' );

    register_nav_menus( [
        'primary' => __( 'Primary Menu', 'gaur' ),
    ] );
});

/*--------------------------------------------------------------
# Content Width
--------------------------------------------------------------*/
add_action( 'after_setup_theme', function () {
    global $content_width;
    if ( ! isset( $content_width ) ) {
        $content_width = 1200;
    }
}, 0 );

/*--------------------------------------------------------------
# Enqueue Styles & Scripts
--------------------------------------------------------------*/
add_action( 'wp_enqueue_scripts', function () {

    wp_enqueue_style(
        'bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        [],
        '5.3.3'
    );

    wp_enqueue_script(
        'bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        [],
        '5.3.3',
        true
    );

    wp_enqueue_style(
        'bootstrap-icons',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
        [],
        '1.11.3'
    );

    wp_enqueue_style(
        'gaur-style',
        get_stylesheet_uri(),
        [ 'bootstrap' ],
        wp_get_theme()->get( 'Version' )
    );

    wp_enqueue_script(
        'gaur-theme',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        wp_get_theme()->get( 'Version' ),
        true
    );
});

/*--------------------------------------------------------------
# Bootstrap Navwalker
--------------------------------------------------------------*/
require_once get_template_directory() . '/inc/class-wp-bootstrap-navwalker.php';

add_filter( 'template_include', function ( $template ) {
    if ( is_singular( 'product' ) ) {
        return get_theme_file_path( 'single-product.php' );
    }

    return $template;
}, 9999 );

add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( 'product' ) || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    global $product;
    if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) {
        return $content;
    }

    ob_start();
    woocommerce_variable_add_to_cart();
    return $content . ob_get_clean();
} );

/*--------------------------------------------------------------
# Widgets
--------------------------------------------------------------*/
add_action( 'widgets_init', function () {

    register_sidebar( [
        'name' => __( 'Sidebar', 'gaur' ),
        'id'   => 'sidebar-1',
    ] );

    register_sidebar( [
        'name' => __( 'Footer', 'gaur' ),
        'id'   => 'footer-1',
    ] );

    register_sidebar( [
        'name' => __( 'Homepage Widgets', 'gaur' ),
        'id'   => 'homepage-widgets',
    ] );
});

/*--------------------------------------------------------------
# WooCommerce Layout Fixes
--------------------------------------------------------------*/

/* Products per page */
add_action( 'pre_get_posts', function ( $query ) {
    if (
        ! is_admin() &&
        $query->is_main_query() &&
        function_exists( 'is_shop' ) &&
        ( is_shop() || is_product_category() || is_product_tag() )
    ) {
        $query->set( 'posts_per_page', 12 );
    }
});
add_action( 'wp', function () {
    if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) {
        remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
        add_action( 'woocommerce_before_shop_loop_item_title', 'gaur_loop_product_image', 10 );
    }
}, 99 );

function gaur_loop_product_image() {
    global $product;

    if ( $product && $product->get_image_id() ) {
        echo wp_get_attachment_image(
            $product->get_image_id(),
            'full',
            false,
            [ 'class' => 'img-fluid woocommerce-loop-product__image-full' ]
        );
        return;
    }

    echo wc_placeholder_img( 'woocommerce_thumbnail' );
}

/* Force grid columns */
add_filter( 'loop_shop_columns', fn() => 3 );
add_filter( 'woocommerce_loop_shop_columns', fn() => 3 );

/*--------------------------------------------------------------
# AJAX Add to Cart Support
--------------------------------------------------------------*/
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
    ob_start();
    ?>
    <span class="cart-count">
        <?php echo WC()->cart->get_cart_contents_count(); ?>
    </span>
    <?php
    $fragments['span.cart-count'] = ob_get_clean();
    return $fragments;
});

function gaur_render_breadcrumbs() {
    echo '<div class="gaur-breadcrumb" aria-label="Breadcrumb">';
    woocommerce_breadcrumb();
    echo '</div>';
}

add_action( 'woocommerce_before_shop_loop', function () {
    if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) {
        gaur_render_breadcrumbs();
    }
}, 1 );

/*--------------------------------------------------------------
# Auto Create Primary Menu (Once)
--------------------------------------------------------------*/
add_action( 'after_switch_theme', function () {

    if ( wp_get_nav_menu_object( 'Primary Menu' ) ) {
        return;
    }

    $menu_id = wp_create_nav_menu( 'Primary Menu' );

    $contact_page = get_page_by_path( 'contact' );
    if ( ! $contact_page ) {
        $contact_page_id = wp_insert_post( [
            'post_title'  => __( 'Contact', 'gaur' ),
            'post_name'   => 'contact',
            'post_status' => 'publish',
            'post_type'   => 'page',
        ] );
    }

    $items = [
        [ 'Home', home_url('/') ],
        [ 'Shop', home_url('/shop') ],
        [ 'Clothing', home_url('/product-category/clothing') ],
        [ 'Footwear', get_term_link('footwear', 'product_cat') ],
        [ 'Contact', home_url('/contact') ],
    ];

    $menu_items = [];
    foreach ( $items as $item ) {
        $menu_items[$item[0]] = wp_update_nav_menu_item( $menu_id, 0, [
            'menu-item-title'  => $item[0],
            'menu-item-url'    => is_wp_error($item[1]) ? home_url('/') : $item[1],
            'menu-item-status' => 'publish',
        ] );
    }

    foreach ( [
        [ 'Boots', get_term_link('boots', 'product_cat') ],
        [ 'Shoes', get_term_link('shoes', 'product_cat') ],
    ] as $item ) {
        wp_update_nav_menu_item( $menu_id, 0, [
            'menu-item-title'      => $item[0],
            'menu-item-url'        => is_wp_error($item[1]) ? home_url('/shop') : $item[1],
            'menu-item-parent-id'  => $menu_items['Footwear'],
            'menu-item-status'     => 'publish',
        ] );
    }

    set_theme_mod( 'nav_menu_locations', [
        'primary' => $menu_id,
    ] );
});

/*--------------------------------------------------------------
# Theme-Specific Sections
--------------------------------------------------------------*/
if ( file_exists( get_template_directory() . '/inc/hero-sections/hero-sections.php' ) ) {
    require_once get_template_directory() . '/inc/hero-sections/hero-sections.php';
}

if ( file_exists( get_template_directory() . '/inc/shop-sections/shop-sections.php' ) ) {
    require_once get_template_directory() . '/inc/shop-sections/shop-sections.php';
}

/*--------------------------------------------------------------
# WooCommerce: Remove Description Tab
# (Description is shown on right column)
--------------------------------------------------------------*/
if ( class_exists( 'WooCommerce' ) ) {

    if ( ! function_exists( 'gaur_remove_description_tab' ) ) {
        add_filter( 'woocommerce_product_tabs', 'gaur_remove_description_tab', 98 );

        function gaur_remove_description_tab( $tabs ) {
            unset( $tabs['description'] );
            return $tabs;
        }
    }
}
