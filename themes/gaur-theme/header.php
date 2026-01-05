<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Bootstrap Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid px-4 px-lg-5">
        <div class="container-xxl d-flex align-items-center">
            <!-- Logo/Brand -->
            <a class="navbar-brand fw-bold fs-3" href="<?php echo home_url(); ?>">
                <i class="bi bi-lightning-charge-fill text-danger"></i> GAUR
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="mainNavbar">
                <?php
                // Primary Navigation Menu
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'navbar-nav me-auto',
                    'fallback_cb' => 'gwp_default_menu',
                    'walker' => new WP_Bootstrap_Navwalker(),
                    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>'
                ) );
                
                // WooCommerce-specific menu if available
                if ( has_nav_menu( 'woocommerce' ) ) {
                    wp_nav_menu( array(
                        'theme_location' => 'woocommerce',
                        'container' => false,
                        'menu_class' => 'navbar-nav',
                        'fallback_cb' => false,
                        'walker' => new WP_Bootstrap_Navwalker(),
                        'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>'
                    ) );
                }
                ?>
                
                <!-- Cart and User Icons -->
                <div class="navbar-nav ms-auto">
                    <!-- Search Form -->
                    <form role="search" method="get" class="d-flex align-items-center me-3" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <div class="input-group">
                            <input type="search" class="form-control form-control-sm" placeholder="Search products..." 
                                value="<?php echo get_search_query(); ?>" name="s" aria-label="Search">
                            <button class="btn btn-outline-secondary btn-sm" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                            <input type="hidden" name="post_type" value="product" />
                        </div>
                    </form>
                    
                    <!-- User Account -->
                    <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                        <a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>" 
                        class="nav-link position-relative me-3">
                            <i class="bi bi-person fs-5"></i>
                        </a>
                        
                        <!-- Cart with Counter -->
                        <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" 
                        class="nav-link position-relative me-3">
                            <i class="bi bi-cart3 fs-5"></i>
                            <?php if ( WC()->cart->get_cart_contents_count() > 0 ) : ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                                    <?php echo WC()->cart->get_cart_contents_count(); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Wishlist (if using YITH Wishlist) -->
                        <?php if ( class_exists( 'YITH_WCWL' ) ) : ?>
                            <a href="<?php echo esc_url( YITH_WCWL()->get_wishlist_url() ); ?>" 
                            class="nav-link position-relative">
                                <i class="bi bi-heart fs-5"></i>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
   </div>
</nav>

<!-- Main Content Wrapper -->
<div id="content" class="site-content">