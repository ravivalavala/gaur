<?php
if ( ! defined('ABSPATH') ) exit;

define('GAUR_SHOP_SECTIONS_PATH', get_template_directory() . '/inc/shop-sections/');
define('GAUR_SHOP_SECTIONS_URL', get_template_directory_uri() . '/inc/shop-sections/');

// FIX 1: Match the filename (products-base.php)
require_once GAUR_SHOP_SECTIONS_PATH . 'products-base.php'; 
require_once GAUR_SHOP_SECTIONS_PATH . 'admin-settings.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'gaur-shop-sections',
        GAUR_SHOP_SECTIONS_URL . 'assets/shop-sections.css',
        [],
        '1.0'
    );
});

/**
 * Shortcode: [gaur_product_section section="gaur_clothing_section"]
 * All settings (category, title, limit) are managed in WP Admin under "GAUR Sections"
 */
add_shortcode('gaur_product_section', function ($atts) {
    $atts = shortcode_atts([
        'section' => '',
        'style'   => '',
    ], $atts, 'gaur_product_section');

    if (empty($atts['section'])) return '';

    gaur_render_dynamic_section($atts['section'], $atts['style']);
});

function gaur_render_homepage_sections() {
    foreach ( gaur_get_shop_section_definitions() as $section_id => $section ) {
        $settings = get_option( $section_id, [] );
        $hero_slug = ! empty( $settings['hero'] ) ? $settings['hero'] : $section['hero'];
        $style = sanitize_html_class( str_replace( 'gaur_', '', str_replace( '_section', '', $section_id ) ) );

        echo do_shortcode( '[gaur_hero slug="' . esc_attr( $hero_slug ) . '"]' );
        echo do_shortcode( '[gaur_product_section section="' . esc_attr( $section_id ) . '" style="' . esc_attr( $style ) . '"]' );
    }
}