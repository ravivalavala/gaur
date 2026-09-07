<?php
/* Single post template */

if (have_posts()) :
    while (have_posts()) : the_post();
        if (get_post_type() === 'product') {
            require get_theme_file_path( 'single-product.php' );
            return;
        }

        get_header();
        ?>
        <div class="container py-5">
            <?php the_title('<h1 class="mb-4">', '</h1>'); ?>
            <?php the_content(); ?>
        </div>
        <?php
        get_footer();
    endwhile;
endif;
