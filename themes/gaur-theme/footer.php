<?php
/**
 * The template for displaying the footer
 *
 * @package GAUR
 */
?>

    </div><!-- #content -->

    <!-- Footer -->
    <footer id="site-footer" class="bg-dark text-light pt-5 mt-5">
        <div class="container">
            <div class="row">

                <!-- Brand -->
                <div class="col-md-4 mb-4">
                    <h4 class="fw-bold">
                        <i class="bi bi-lightning-charge-fill text-danger"></i> GAUR
                    </h4>
                    <p class="text-muted">
                        Premium streetwear & sneakers built for performance and style.
                    </p>
                </div>

                <!-- Footer Menu 1 -->
                <div class="col-md-2 mb-4">
                    <h6 class="text-uppercase fw-semibold mb-3">Shop</h6>
                    <?php
                    if ( is_active_sidebar( 'footer-1' ) ) {
                        dynamic_sidebar( 'footer-1' );
                    }
                    ?>
                </div>

                <!-- Footer Menu 2 -->
                <div class="col-md-2 mb-4">
                    <h6 class="text-uppercase fw-semibold mb-3">Company</h6>
                    <?php
                    if ( is_active_sidebar( 'footer-2' ) ) {
                        dynamic_sidebar( 'footer-2' );
                    }
                    ?>
                </div>

                <!-- Footer Menu 3 -->
                <div class="col-md-2 mb-4">
                    <h6 class="text-uppercase fw-semibold mb-3">Support</h6>
                    <?php
                    if ( is_active_sidebar( 'footer-3' ) ) {
                        dynamic_sidebar( 'footer-3' );
                    }
                    ?>
                </div>

                <!-- Social -->
                <div class="col-md-2 mb-4">
                    <h6 class="text-uppercase fw-semibold mb-3">Follow</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-twitter-x fs-5"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-facebook fs-5"></i></a>
                    </div>
                </div>

            </div>

            <hr class="border-secondary">

            <div class="text-center pb-4">
                <small class="text-muted">
                    © <?php echo date( 'Y' ); ?> GAUR. All rights reserved.
                </small>
            </div>
        </div>
    </footer>

<?php wp_footer(); ?>
</body>
</html>
