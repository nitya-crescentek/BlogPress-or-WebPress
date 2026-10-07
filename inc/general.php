<?php
/**
 * General functions.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'webpress_scripts' ) ) {
	add_action( 'wp_enqueue_scripts', 'webpress_scripts' );
	/**
	 * Enqueue scripts and styles
	 */
	function webpress_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$dir_uri = get_template_directory_uri();

		// phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual -- Intentionally loose.
		if ( is_singular() && ( comments_open() || '0' != get_comments_number() ) ) {
			wp_enqueue_style( 'webpress-comments', $dir_uri . "/assets/css/components/comments{$suffix}.css", array(), WEBPRESS_VERSION, 'all' );
		}

		if (
			is_active_sidebar( 'top-bar' ) ||
			is_active_sidebar( 'footer-bar' ) ||
			is_active_sidebar( 'footer-1' ) ||
			is_active_sidebar( 'footer-2' ) ||
			is_active_sidebar( 'footer-3' ) ||
			is_active_sidebar( 'footer-4' ) ||
			is_active_sidebar( 'footer-5' )
		) {
			wp_enqueue_style( 'webpress-widget-areas', $dir_uri . "/assets/css/components/widget-areas{$suffix}.css", array(), WEBPRESS_VERSION, 'all' );
		}

		wp_enqueue_style( 'webpress-style', $dir_uri . "/assets/css/main{$suffix}.css", array(), WEBPRESS_VERSION, 'all' );

		if ( 'classic' !== webpress_get_blog_post_layout() ) {
			wp_enqueue_style( 'webpress-archive-layouts', $dir_uri . "/assets/css/components/archive-layouts{$suffix}.css", array( 'webpress-style' ), WEBPRESS_VERSION, 'all' );
		}

		if ( is_rtl() ) {
			wp_enqueue_style( 'webpress-rtl', $dir_uri . "/assets/css/main-rtl{$suffix}.css", array(), WEBPRESS_VERSION, 'all' );
		}

		/**
		 * Filters whether the child theme's style.css is enqueued automatically.
		 *
		 * Only consulted when a child theme is active. Return false to manage the
		 * child stylesheet yourself.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $load Whether to enqueue the child theme stylesheet. Default true.
		 * @return bool Whether to enqueue it.
		 */
		if ( is_child_theme() && apply_filters( 'webpress_load_child_theme_stylesheet', true ) ) {
			$child_stylesheet = get_stylesheet_directory() . '/style.css';

			// A child theme may keep its CSS elsewhere, so don't assume style.css is on disk.
			$child_version = file_exists( $child_stylesheet ) ? filemtime( $child_stylesheet ) : WEBPRESS_VERSION;

			wp_enqueue_style( 'webpress-child', get_stylesheet_uri(), array( 'webpress-style' ), $child_version, 'all' );
		}

		if ( webpress_has_active_menu() ) {
			wp_enqueue_script( 'webpress-menu', $dir_uri . "/assets/js/menu{$suffix}.js", array(), WEBPRESS_VERSION, true );

			$menu_script_args = array(
				'toggleOpenedSubMenus' => true,
				'openSubMenuLabel'     => esc_attr__( 'Open Sub-Menu', 'webpress' ),
				'closeSubMenuLabel'    => esc_attr__( 'Close Sub-Menu', 'webpress' ),
			);

			webpress_add_inline_script(
				'webpress-menu',
				$menu_script_args,
				'webpressMenu'
			);
		}

		if ( 'click' === webpress_get_option( 'nav_dropdown_type' ) || 'click-arrow' === webpress_get_option( 'nav_dropdown_type' ) ) {
			wp_enqueue_script( 'webpress-dropdown-click', $dir_uri . "/assets/js/dropdown-click{$suffix}.js", array(), WEBPRESS_VERSION, true );

			$dropdown_click_args = array(
				'openSubMenuLabel'  => esc_attr__( 'Open Sub-Menu', 'webpress' ),
				'closeSubMenuLabel' => esc_attr__( 'Close Sub-Menu', 'webpress' ),
			);

			webpress_add_inline_script(
				'webpress-dropdown-click',
				$dropdown_click_args,
				'webpressDropdownClick'
			);
		}

		if ( webpress_get_option( 'nav_search_modal' ) ) {
			wp_enqueue_script( 'webpress-modal', $dir_uri . '/assets/dist/modal.js', array(), WEBPRESS_VERSION, true );
		}

		if ( 'enable' === webpress_get_option( 'nav_search' ) ) {
			wp_enqueue_script( 'webpress-navigation-search', $dir_uri . "/assets/js/navigation-search{$suffix}.js", array(), WEBPRESS_VERSION, true );

			$nav_search_args = array(
				'open'  => esc_attr__( 'Open Search Bar', 'webpress' ),
				'close' => esc_attr__( 'Close Search Bar', 'webpress' ),
			);

			webpress_add_inline_script(
				'webpress-navigation-search',
				$nav_search_args,
				'webpressNavSearch'
			);
		}

		if ( 'enable' === webpress_get_option( 'back_to_top' ) ) {
			wp_enqueue_script( 'webpress-back-to-top', $dir_uri . "/assets/js/back-to-top{$suffix}.js", array(), WEBPRESS_VERSION, true );

			$back_to_top_args = array(
				'smooth' => (bool) webpress_get_option( 'back_to_top_smooth_scroll' ),
			);

			webpress_add_inline_script(
				'webpress-back-to-top',
				$back_to_top_args,
				'webpressBackToTop'
			);
		}

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}

if ( ! function_exists( 'webpress_widgets_init' ) ) {
	add_action( 'widgets_init', 'webpress_widgets_init' );
	/**
	 * Register widgetized area and update sidebar with default widgets
	 */
	function webpress_widgets_init() {
		$widgets = array(
			'sidebar-1' => __( 'Right Sidebar', 'webpress' ),
			'sidebar-2' => __( 'Left Sidebar', 'webpress' ),
			'header' => __( 'Header', 'webpress' ),
			'footer-1' => __( 'Footer Widget 1', 'webpress' ),
			'footer-2' => __( 'Footer Widget 2', 'webpress' ),
			'footer-3' => __( 'Footer Widget 3', 'webpress' ),
			'footer-4' => __( 'Footer Widget 4', 'webpress' ),
			'footer-5' => __( 'Footer Widget 5', 'webpress' ),
			'footer-bar' => __( 'Footer Bar', 'webpress' ),
			'top-bar' => __( 'Top Bar', 'webpress' ),
		);

		foreach ( $widgets as $id => $name ) {
			register_sidebar(
				array(
					'name'          => $name,
					'id'            => $id,
					'before_widget' => '<aside id="%1$s" class="widget inner-padding %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h2 class="widget-title">',
					'after_title'   => '</h2>',
				)
			);
		}
	}
}

if ( ! function_exists( 'webpress_smart_content_width' ) ) {
	add_action( 'wp', 'webpress_smart_content_width' );
	/**
	 * Set the $content_width depending on layout of current page
	 * Hook into "wp" so we have the correct layout setting from webpress_get_layout()
	 * Hooking into "after_setup_theme" doesn't get the correct layout setting
	 */
	function webpress_smart_content_width() {
		global $content_width;

		$layout = webpress_get_layout();
		$content_width = webpress_get_option( 'container_width' ) * ( webpress_get_content_area_width( $layout ) / 100 );

		if ( 'narrow' === webpress_get_container_layout() ) {
			$content_width = min( absint( webpress_get_option( 'narrow_container_width' ) ), $content_width );
		}
	}
}

if ( ! function_exists( 'webpress_page_menu_args' ) ) {
	add_filter( 'wp_page_menu_args', 'webpress_page_menu_args' );
	/**
	 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The existing menu args.
	 * @return array Menu args.
	 */
	function webpress_page_menu_args( $args ) {
		$args['show_home'] = true;

		return $args;
	}
}

if ( ! function_exists( 'webpress_resource_hints' ) ) {
	add_filter( 'wp_resource_hints', 'webpress_resource_hints', 10, 2 );
	/**
	 * Add resource hints to our Google fonts call.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $urls           URLs to print for resource hints.
	 * @param string $relation_type  The relation type the URLs are printed.
	 * @return array $urls           URLs to print for resource hints.
	 */
	function webpress_resource_hints( $urls, $relation_type ) {
		$handle = 'webpress-google-fonts';
		$hint_type = 'preconnect';
		$has_crossorigin_support = version_compare( $GLOBALS['wp_version'], '4.7-alpha', '>=' );

		if ( wp_style_is( $handle, 'queue' ) ) {
			if ( $relation_type === $hint_type ) {
				if ( $has_crossorigin_support && 'preconnect' === $hint_type ) {
					$urls[] = array(
						'href' => 'https://fonts.gstatic.com',
						'crossorigin',
					);

					$urls[] = array(
						'href' => 'https://fonts.googleapis.com',
						'crossorigin',
					);
				} else {
					$urls[] = 'https://fonts.gstatic.com';
					$urls[] = 'https://fonts.googleapis.com';
				}
			}

			if ( 'dns-prefetch' !== $hint_type ) {
				$googleapis_index = array_search( 'fonts.googleapis.com', $urls );

				if ( false !== $googleapis_index ) {
					unset( $urls[ $googleapis_index ] );
				}
			}
		}

		return $urls;
	}
}

if ( ! function_exists( 'webpress_remove_caption_padding' ) ) {
	add_filter( 'img_caption_shortcode_width', 'webpress_remove_caption_padding' );
	/**
	 * Remove WordPress's default padding on images with captions
	 *
	 * @param int $width Default WP .wp-caption width (image width + 10px).
	 * @return int Updated width to remove 10px padding.
	 */
	function webpress_remove_caption_padding( $width ) {
		return $width - 10;
	}
}

if ( ! function_exists( 'webpress_enhanced_image_navigation' ) ) {
	add_filter( 'attachment_link', 'webpress_enhanced_image_navigation', 10, 2 );
	/**
	 * Filter in a link to a content ID attribute for the next/previous image links on image attachment pages.
	 *
	 * @param string $url The input URL.
	 * @param int    $id The ID of the post.
	 */
	function webpress_enhanced_image_navigation( $url, $id ) {
		if ( ! is_attachment() && ! wp_attachment_is_image( $id ) ) {
			return $url;
		}

		$image = get_post( $id );
		// phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual -- Intentionally loose.
		if ( ! empty( $image->post_parent ) && $image->post_parent != $id ) {
			$url .= '#main';
		}

		return $url;
	}
}

if ( ! function_exists( 'webpress_categorized_blog' ) ) {
	/**
	 * Determine whether blog/site has more than one category.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True of there is more than one category, false otherwise.
	 */
	function webpress_categorized_blog() {
		$all_the_cool_cats = get_transient( 'webpress_categories' );

		if ( false === $all_the_cool_cats ) {
			// Create an array of all the categories that are attached to posts.
			$all_the_cool_cats = get_categories(
				array(
					'fields'     => 'ids',
					'hide_empty' => 1,

					// We only need to know if there is more than one category.
					'number'     => 2,
				)
			);

			// Count the number of categories that are attached to the posts.
			$all_the_cool_cats = count( $all_the_cool_cats );

			set_transient( 'webpress_categories', $all_the_cool_cats );
		}

		if ( $all_the_cool_cats > 1 ) {
			// This blog has more than 1 category so twentyfifteen_categorized_blog should return true.
			return true;
		} else {
			// This blog has only 1 category so twentyfifteen_categorized_blog should return false.
			return false;
		}
	}
}

if ( ! function_exists( 'webpress_category_transient_flusher' ) ) {
	add_action( 'edit_category', 'webpress_category_transient_flusher' );
	add_action( 'save_post', 'webpress_category_transient_flusher' );
	/**
	 * Flush out the transients used in {@see webpress_categorized_blog()}.
	 *
	 * @since 1.0.0
	 */
	function webpress_category_transient_flusher() {
		// Like, beat it. Dig?
		delete_transient( 'webpress_categories' );
	}
}

if ( ! function_exists( 'webpress_get_default_color_palettes' ) ) {
	/**
	 * Set up our colors for the color picker palettes and filter them so you can change them.
	 *
	 * @since 1.0.0
	 */
	function webpress_get_default_color_palettes() {
		$palettes = array(
			'#000000',
			'#FFFFFF',
			'#F1C40F',
			'#E74C3C',
			'#1ABC9C',
			'#1e72bd',
			'#8E44AD',
			'#00CC77',
		);

		return $palettes;
	}
}

/**
 * Adds microdata to elements.
 *
 * @since 1.0.0
 * @param string $output The existing output after the class attribute.
 * @param string $context What element we're targeting.
 */
function webpress_set_microdata_markup( $output, $context ) {
	if ( 'left_sidebar' === $context || 'right_sidebar' === $context ) {
		$context = 'sidebar';
	}

	if ( 'footer' === $context ) {
		return $output;
	}

	if ( 'site-info' === $context ) {
		$context = 'footer';
	}

	$microdata = webpress_get_microdata( $context );

	if ( $microdata ) {
		return $microdata;
	}

	return $output;
}

add_action( 'wp_footer', 'webpress_do_a11y_scripts' );
/**
 * Enqueue scripts in the footer.
 *
 * @since 1.0.0
 */
function webpress_do_a11y_scripts() {
	/**
	 * Filters whether the accessibility helper script is printed in the footer.
	 *
	 * The script toggles a `using-mouse` body class so focus outlines are shown
	 * for keyboard users only.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $load Whether to print the a11y script. Default true.
	 * @return bool Whether to print it.
	 */
	if ( apply_filters( 'webpress_load_a11y_script', true ) && function_exists( 'wp_print_inline_script_tag' ) ) {
		wp_print_inline_script_tag(
			'!function(){"use strict";if("querySelector"in document&&"addEventListener"in window){var e=document.body;e.addEventListener("pointerdown",(function(){e.classList.add("using-mouse")}),{passive:!0}),e.addEventListener("keydown",(function(){e.classList.remove("using-mouse")}),{passive:!0})}}();',
			array(
				'id' => 'webpress-a11y',
			)
		);
	}
}
