<?php
/**
 * Single Product Template – GAUR Theme
 */
defined( 'ABSPATH' ) || exit;

get_header();

global $post, $product;
$product = wc_get_product( $post->ID );
?>

<div class="container py-5 gaur-product-page">

    <?php gaur_render_breadcrumbs(); ?>

    <div class="row">

        <!-- LEFT: Product Gallery -->
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="pe-lg-4 gaur-product-gallery-panel">
                <?php
                /**
                 * WooCommerce Gallery
                 */
                do_action( 'woocommerce_before_single_product_summary' );
                ?>
            </div>
        </div>

        <!-- RIGHT: Product Info + FULL Description -->
        <div class="col-lg-5">
            <div class="product-info-column ps-lg-3 gaur-product-summary">

                <!-- Title -->
                <h1 class="display-6 fw-bold mb-3"><?php the_title(); ?></h1>

                <!-- Rating -->
                <?php if ( wc_review_ratings_enabled() ) : ?>
                    <div class="mb-3">
                        <?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
                    </div>
                <?php endif; ?>

                <!-- Price -->
                <div class="h3 text-danger mb-4">
                    <?php echo $product->get_price_html(); ?>
                </div>

                <!-- Add to Cart -->
                <div class="mb-5">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>

                <!-- Meta -->
                <div class="pt-4 border-top product-meta">
                    <?php if ( wc_product_sku_enabled() ) : ?>
                        <p class="small text-uppercase mb-1">
                            SKU: <span class="fw-bold">
                                <?php echo $product->get_sku() ? $product->get_sku() : 'N/A'; ?>
                            </span>
                        </p>
                    <?php endif; ?>

                    <p class="small text-uppercase mb-1">
                        Category:
                        <span class="fw-bold">
                            <?php echo wc_get_product_category_list( $product->get_id(), ', ' ); ?>
                        </span>
                    </p>
                </div>

            </div>
        </div>

    </div>

    <!-- TABS BELOW (Additional Info + Reviews only) -->
    <div class="row mt-5">
        <div class="col-12">
            <?php woocommerce_output_product_data_tabs(); ?>
        </div>
    </div>

</div>

<?php get_footer(); ?>
