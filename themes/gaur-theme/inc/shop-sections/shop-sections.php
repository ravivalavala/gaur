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

// FIX 2: Use the correct function name: gaur_render_dynamic_section
function gaur_section_latest_clothing() {
    // Passes 'clothing' as the style class
    gaur_render_dynamic_section('gaur_clothing_section', 'clothing');
}

function gaur_section_latest_shoes() {
    // Passes 'shoes' as the style class
    gaur_render_dynamic_section('gaur_shoes_section', 'shoes');
}