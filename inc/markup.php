<?php
/**
 * Adds HTML markup.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'webpress_body_classes' ) ) {
	add_filter( 'body_class', 'webpress_body_classes' );
	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_body_classes( $classes ) {
		$sidebar_layout       = webpress_get_layout();
		$navigation_location  = webpress_get_navigation_location();
		$navigation_alignment = webpress_get_option( 'nav_alignment_setting' );
		$navigation_dropdown  = webpress_get_option( 'nav_dropdown_type' );
		$header_alignment     = webpress_get_option( 'header_alignment_setting' );
		$content_layout       = webpress_get_option( 'content_layout_setting' );

		// These values all have defaults, but we like to be extra careful.
		$classes[] = ( $sidebar_layout ) ? $sidebar_layout : 'right-sidebar';
		$classes[] = ( $navigation_location ) ? $navigation_location : 'nav-below-header';
		$classes[] = ( $content_layout ) ? $content_layout : 'separate-containers';
		$classes[] = webpress_get_container_layout() . '-container';

		$blog_post_layout = webpress_get_blog_post_layout();

		if ( 'classic' !== $blog_post_layout ) {
			$classes[] = 'archive-layout-' . $blog_post_layout;
		}

		if ( 'grid' === $blog_post_layout ) {
			$classes[] = 'archive-grid-columns-' . absint( webpress_get_option( 'blog_grid_columns' ) );
		}

		if ( 'enable' === webpress_get_option( 'nav_search' ) ) {
			$classes[] = 'nav-search-enabled';
		}

		// Only necessary for nav before or after header.
		if ( 'nav-above-header' === $navigation_location ) {
			if ( 'center' === $navigation_alignment ) {
				$classes[] = 'nav-aligned-center';
			} elseif ( 'right' === $navigation_alignment ) {
				$classes[] = 'nav-aligned-right';
			} elseif ( 'left' === $navigation_alignment ) {
				$classes[] = 'nav-aligned-left';
			}
		}

		if ( 'center' === $header_alignment ) {
			$classes[] = 'header-aligned-center';
		} elseif ( 'right' === $header_alignment ) {
			$classes[] = 'header-aligned-right';
		} elseif ( 'left' === $header_alignment ) {
			$classes[] = 'header-aligned-left';
		}

		if ( 'click' === $navigation_dropdown ) {
			$classes[] = 'dropdown-click';
			$classes[] = 'dropdown-click-menu-item';
		} elseif ( 'click-arrow' === $navigation_dropdown ) {
			$classes[] = 'dropdown-click-arrow';
			$classes[] = 'dropdown-click';
		} else {
			$classes[] = 'dropdown-hover';
		}

		if ( is_singular() ) {
			// Page builder container metabox option.
			// Used to be a single checkbox, hence the name/true value. Now it's a radio choice between full width and contained.
			$content_container = get_post_meta( get_the_ID(), '_webpress-full-width-content', true );

			if ( $content_container ) {
				if ( 'true' === $content_container ) {
					$classes[] = 'full-width-content';
				}

				if ( 'contained' === $content_container ) {
					$classes[] = 'contained-content';
				}
			}

			if ( has_post_thumbnail() ) {
				$classes[] = 'featured-image-active';
			}
		}

		return $classes;
	}
}

if ( ! function_exists( 'webpress_top_bar_classes' ) ) {
	/**
	 * Adds custom classes to the header.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_top_bar_classes( $classes ) {
		$classes[] = 'top-bar';

		if ( 'contained' === webpress_get_option( 'top_bar_width' ) ) {
			$classes[] = 'grid-container';
		}

		$classes[] = 'top-bar-align-' . esc_attr( webpress_get_option( 'top_bar_alignment' ) );

		return $classes;
	}
}

if ( ! function_exists( 'webpress_right_sidebar_classes' ) ) {
	/**
	 * Adds custom classes to the right sidebar.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_right_sidebar_classes( $classes ) {
		$classes[] = 'widget-area';
		$classes[] = 'sidebar';
		$classes[] = 'is-right-sidebar';

		return $classes;
	}
}

if ( ! function_exists( 'webpress_left_sidebar_classes' ) ) {
	/**
	 * Adds custom classes to the left sidebar.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_left_sidebar_classes( $classes ) {
		$classes[] = 'widget-area';
		$classes[] = 'sidebar';
		$classes[] = 'is-left-sidebar';

		return $classes;
	}
}

if ( ! function_exists( 'webpress_content_classes' ) ) {
	/**
	 * Adds custom classes to the content container.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_content_classes( $classes ) {
		$classes[] = 'content-area';

		return $classes;
	}
}

if ( ! function_exists( 'webpress_header_classes' ) ) {
	/**
	 * Adds custom classes to the header.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_header_classes( $classes ) {
		$classes[] = 'site-header';

		if ( 'contained-header' === webpress_get_option( 'header_layout_setting' ) ) {
			$classes[] = 'grid-container';
		}

		if ( webpress_has_inline_mobile_toggle() ) {
			$classes[] = 'has-inline-mobile-toggle';
		}

		return $classes;
	}
}

if ( ! function_exists( 'webpress_inside_header_classes' ) ) {
	/**
	 * Adds custom classes to inside the header.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_inside_header_classes( $classes ) {
		$classes[] = 'inside-header';

		if ( 'full-width' !== webpress_get_option( 'header_inner_width' ) ) {
			$classes[] = 'grid-container';
		}

		return $classes;
	}
}

if ( ! function_exists( 'webpress_navigation_classes' ) ) {
	/**
	 * Adds custom classes to the navigation.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_navigation_classes( $classes ) {
		$classes[] = 'main-navigation';

		if ( 'contained-nav' === webpress_get_option( 'nav_layout_setting' ) ) {
			$navigation_location = webpress_get_navigation_location();

			if ( 'nav-float-right' !== $navigation_location && 'nav-float-left' !== $navigation_location ) {
				$classes[] = 'grid-container';
			}
		}

		$nav_alignment = webpress_get_option( 'nav_alignment_setting' );

		if ( 'center' === $nav_alignment ) {
			$classes[] = 'nav-align-center';
		} elseif ( 'right' === $nav_alignment ) {
			$classes[] = 'nav-align-right';
		} elseif ( is_rtl() && 'left' === $nav_alignment ) {
			$classes[] = 'nav-align-left';
		}

		if ( webpress_has_menu_bar_items() ) {
			$classes[] = 'has-menu-bar-items';
		}

		$submenu_direction = 'right';

		if ( 'left' === webpress_get_option( 'nav_dropdown_direction' ) ) {
			$submenu_direction = 'left';
		}

		if ( 'nav-left-sidebar' === webpress_get_navigation_location() ) {
			$submenu_direction = 'right';

			if ( 'both-right' === webpress_get_layout() ) {
				$submenu_direction = 'left';
			}
		}

		if ( 'nav-right-sidebar' === webpress_get_navigation_location() ) {
			$submenu_direction = 'left';

			if ( 'both-left' === webpress_get_layout() ) {
				$submenu_direction = 'right';
			}
		}

		$classes[] = 'sub-menu-' . $submenu_direction;

		return $classes;
	}
}

if ( ! function_exists( 'webpress_inside_navigation_classes' ) ) {
	/**
	 * Adds custom classes to the inner navigation.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_inside_navigation_classes( $classes ) {
		$classes[] = 'inside-navigation';

		if ( 'full-width' !== webpress_get_option( 'nav_inner_width' ) ) {
			$classes[] = 'grid-container';
		}

		return $classes;
	}
}

if ( ! function_exists( 'webpress_menu_classes' ) ) {
	/**
	 * Adds custom classes to the menu.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_menu_classes( $classes ) {
		$classes[] = 'menu';
		$classes[] = 'sf-menu';

		return $classes;
	}
}

if ( ! function_exists( 'webpress_footer_classes' ) ) {
	/**
	 * Adds custom classes to the footer.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_footer_classes( $classes ) {
		$classes[] = 'site-footer';

		if ( 'contained-footer' === webpress_get_option( 'footer_layout_setting' ) ) {
			$classes[] = 'grid-container';
		}

		if ( is_active_sidebar( 'footer-bar' ) ) {
			$classes[] = 'footer-bar-active';
			$classes[] = 'footer-bar-align-' . esc_attr( webpress_get_option( 'footer_bar_alignment' ) );
		}

		return $classes;
	}
}

if ( ! function_exists( 'webpress_inside_footer_classes' ) ) {
	/**
	 * Adds custom classes to the footer.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_inside_footer_classes( $classes ) {
		$classes[] = 'footer-widgets-container';

		if ( 'full-width' !== webpress_get_option( 'footer_inner_width' ) ) {
			$classes[] = 'grid-container';
		}

		return $classes;
	}
}

if ( ! function_exists( 'webpress_main_classes' ) ) {
	/**
	 * Adds custom classes to the <main> element
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_main_classes( $classes ) {
		$classes[] = 'site-main';

		return $classes;
	}
}

/**
 * Adds custom classes to the #page element
 *
 * @param array $classes The existing classes.
 * @since 1.0.0
 */
function webpress_do_page_container_classes( $classes ) {
	$classes[] = 'site';
	$classes[] = 'grid-container';
	$classes[] = 'container';

	if ( webpress_is_using_hatom() ) {
		$classes[] = 'hfeed';
	}

	return $classes;
}

/**
 * Adds custom classes to the comment author element
 *
 * @param array $classes The existing classes.
 * @since 1.0.0
 */
function webpress_do_comment_author_classes( $classes ) {
	$classes[] = 'comment-author';

	if ( webpress_is_using_hatom() ) {
		$classes[] = 'vcard';
	}

	return $classes;
}

if ( ! function_exists( 'webpress_post_classes' ) ) {
	add_filter( 'post_class', 'webpress_post_classes' );
	/**
	 * Adds custom classes to the <article> element.
	 * Remove .hentry class from pages to comply with structural data guidelines.
	 *
	 * @param array $classes The existing classes.
	 * @since 1.0.0
	 */
	function webpress_post_classes( $classes ) {
		if ( 'page' === get_post_type() || ! webpress_is_using_hatom() ) {
			$classes = array_diff( $classes, array( 'hentry' ) );
		}

		return $classes;
	}
}
