<?php
/**
 * Bootstrap 5 Nav Walker
 *
 * @package GAUR
 */

if ( ! class_exists( 'WP_Bootstrap_Navwalker' ) ) {

	class WP_Bootstrap_Navwalker extends Walker_Nav_Menu {

		/**
		 * Start Level
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			$indent  = str_repeat( "\t", $depth );
			$output .= "\n$indent<ul class=\"dropdown-menu\">\n";
		}

		/**
		 * Start Element
		 */
		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {

			$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

			$classes   = empty( $item->classes ) ? [] : (array) $item->classes;
			$classes[] = 'nav-item';

			$is_dropdown = in_array( 'menu-item-has-children', $classes, true );

			if ( $is_dropdown && $depth === 0 ) {
				$classes[] = 'dropdown';
			}

			$class_names = implode( ' ', array_map( 'esc_attr', $classes ) );
			$output     .= $indent . '<li class="' . $class_names . '">';

			$atts           = '';
			$atts_title     = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts_target    = ! empty( $item->target ) ? $item->target : '';
			$atts_rel       = ! empty( $item->xfn ) ? $item->xfn : '';
			$atts_href      = ! empty( $item->url ) ? $item->url : '';
			$atts_classes   = 'nav-link';

			if ( $is_dropdown && $depth === 0 ) {
				$atts_classes .= ' dropdown-toggle';
				$atts        .= ' data-bs-toggle="dropdown"';
				$atts        .= ' aria-expanded="false"';
				$atts        .= ' role="button"';
			}

			if ( $depth > 0 ) {
				$atts_classes = 'dropdown-item';
			}

			$atts .= ' class="' . esc_attr( $atts_classes ) . '"';

			if ( $atts_title ) {
				$atts .= ' title="' . esc_attr( $atts_title ) . '"';
			}
			if ( $atts_target ) {
				$atts .= ' target="' . esc_attr( $atts_target ) . '"';
			}
			if ( $atts_rel ) {
				$atts .= ' rel="' . esc_attr( $atts_rel ) . '"';
			}
			if ( $atts_href ) {
				$atts .= ' href="' . esc_url( $atts_href ) . '"';
			}

			$item_output  = $args->before ?? '';
			$item_output .= '<a' . $atts . '>';
			$item_output .= $args->link_before ?? '';
			$item_output .= apply_filters( 'the_title', $item->title, $item->ID );
			$item_output .= $args->link_after ?? '';
			$item_output .= '</a>';
			$item_output .= $args->after ?? '';

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}

	}
}
