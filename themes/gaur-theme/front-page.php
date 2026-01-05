<?php
/**
 * Homepage Template
 *
 * @package GAUR
 */

get_header(); ?>

<!-- Page Background Wrapper -->
<div class="homepage-bg">

    <?php echo do_shortcode('[gaur_hero slug="freedom-series-wear-your-boldness"]');  // Freedom Series ?>

    <?php echo do_shortcode('[gaur_hero slug="stylish-hoodies-t-shirts"]'); // Clothing Hero ?>


    <!-- 3) Latest Clothing Products -->
    <?php gaur_section_latest_clothing(); ?>

    <?php echo do_shortcode('[gaur_hero slug="trendy-shoes-collection"]'); // Shoes Hero ?>

    <!-- 5) Latest Shoes -->
    <?php gaur_section_latest_shoes(); ?>

</div>

<?php get_footer(); ?>
