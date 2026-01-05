<?php
/**
 * The main template file
 * This is the fallback template for your GAUR theme.
 */

get_header(); 
?>

<div id="primary" class="content-area container py-5">
    <main id="main" class="site-main">

        <?php if ( have_posts() ) : ?>

            <header class="page-header mb-4">
                <h1 class="page-title"><?php single_post_title(); ?></h1>
            </header>

            <?php
            while ( have_posts() ) : the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('mb-4'); ?>>
                    <header class="entry-header">
                        <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    </header>

                    <div class="entry-content">
                        <?php the_excerpt(); ?>
                        <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline-danger mt-2">Read More</a>
                    </div>
                </article>
            <?php
            endwhile;

            // Pagination
            the_posts_pagination( array(
                'prev_text' => '<i class="bi bi-chevron-left"></i>',
                'next_text' => '<i class="bi bi-chevron-right"></i>',
            ) );

        else : 
            echo '<p class="text-center">No posts found.</p>';
        endif;
        ?>

    </main>
</div>

<?php get_footer(); ?>
