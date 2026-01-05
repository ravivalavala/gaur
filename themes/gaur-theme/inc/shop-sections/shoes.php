<?php
if ( ! defined('ABSPATH') ) exit;

function gaur_section_latest_shoes() {

    $opts = get_option('gaur_shoes_section');

    if ( empty($opts['enabled']) ) return;

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => $opts['limit'] ?? 8,
        'tax_query'      => [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $opts['category'] ?? 'shoes'
            ]
        ]
    ];

    $q = new WP_Query($args);

    if ( ! $q->have_posts() ) return;

    echo '<section class="gaur-section shoes">';
    echo '<h2>' . esc_html($opts['title'] ?? 'Latest Shoes') . '</h2>';
    echo '<div class="gaur-grid">';

    while ( $q->have_posts() ) {
        $q->the_post();
        wc_get_template_part('content', 'product');
    }

    echo '</div></section>';

    wp_reset_postdata();
}
