<?php
/* Template for pages */
get_header();
?>
<main class="container py-5 gaur-page-content">
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            the_title('<h1 class="mb-4">', '</h1>');
            the_content();
        endwhile;
    endif;
    ?>
</main>
<?php get_footer(); ?>
