<?php
/* Single post template */
get_header();
?>
<div class="container py-5">
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            the_title('<h1 class="mb-4">', '</h1>');
            the_content();
        endwhile;
    endif;
    ?>
</div>
<?php get_footer(); ?>
