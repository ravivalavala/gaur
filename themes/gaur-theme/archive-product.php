<?php
/**
 * Archive Product Template
 * Matches single-product.php style
 */

defined( 'ABSPATH' ) || exit;

get_header();

?>

<div class="container py-5">
    <!-- Archive Title -->
    <h1 class="mb-5"><?php woocommerce_page_title(); ?></h1>

    <?php if ( have_posts() ) : ?>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php
            while ( have_posts() ) :
                the_post();
                $product = wc_get_product( get_the_ID() );
                if ( ! $product ) continue;
            ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                            <?php
                            if ( has_post_thumbnail() ) {
                                echo get_the_post_thumbnail( get_the_ID(), 'woocommerce_thumbnail', [
                                    'class' => 'card-img-top',
                                    'style' => 'height: 300px; object-fit: contain;'
                                ] );
                            } else {
                                echo '<img src="' . wc_placeholder_img_src() . '" class="card-img-top" style="height: 300px; object-fit: contain;" alt="Product Image">';
                            }
                            ?>
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark">
                                    <?php the_title(); ?>
                                </a>
                            </h5>
                            <p class="card-text text-danger fw-bold">
                                <?php echo $product->get_price_html(); ?>
                            </p>
                            <div class="mt-auto">
                                <a href="<?php the_permalink(); ?>" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-eye me-1"></i>View Product
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-5">
            <?php
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => __('« Previous', 'gaur'),
                'next_text' => __('Next »', 'gaur'),
                'screen_reader_text' => __('Products navigation', 'gaur'),
            ) );
            ?>
        </div>

    <?php else : ?>
        <p><?php esc_html_e( 'No products found in this category.', 'gaur' ); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();
