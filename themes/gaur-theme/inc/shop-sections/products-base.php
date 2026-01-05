<?php
if ( ! defined('ABSPATH') ) exit;

function gaur_render_dynamic_section($option_key, $style_class = '') {
    $opts = get_option($option_key);
    if ( empty($opts['enabled']) ) return;

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => !empty($opts['limit']) ? intval($opts['limit']) : 8,
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => !empty($opts['category']) ? $opts['category'] : '',
            ),
        ),
    );

    $q = new WP_Query($args);
    if ( ! $q->have_posts() ) return;

    // Remove buttons for the clean catalog look
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

    echo '<div class="container py-5">';
    echo '<section class="gaur-section ' . esc_attr($style_class) . '">';
    echo '<h2 class="mb-4 fw-bold text-uppercase fs-3">' . esc_html($opts['title'] ?? 'Latest Products') . '</h2>'; 
    echo '<div class="row g-4">'; 

    while ( $q->have_posts() ) {
        $q->the_post();
        $product = wc_get_product( get_the_ID() );
        
        if ( $product ) {
            echo '<div class="col-6 col-md-4 col-lg-3">';
            echo '<figure class="figure w-100 mb-0">';
            echo '<a href="' . get_permalink() . '" class="d-block mb-2 text-decoration-none">';
            
            if ( has_post_thumbnail() ) {
                echo get_the_post_thumbnail(get_the_ID(), 'woocommerce_thumbnail', array('class' => 'figure-img img-fluid rounded shadow-sm mb-0'));
            }
            
            echo '</a>';
            echo '<figcaption class="figure-caption text-center">';
            echo '<h3 class="h6 mb-1 text-dark fw-bold text-uppercase">' . get_the_title() . '</h3>';
            echo '<div class="product-price text-muted">' . $product->get_price_html() . '</div>';
            echo '</figcaption>';
            echo '</figure>';
            echo '</div>';
        }
    }

    echo '</div>'; // Close Row
    echo '</section>';
    echo '</div>'; // Close Container

    // Put the action back for other pages (like the Shop page)
    add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

    wp_reset_postdata();
}