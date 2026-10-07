<?php
/**
 * Main theme functions.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * A wrapper function to get our options.
 *
 * @since 1.0.0
 *
 * @param string $option The option name to look up.
 * @return string The option value.
 */
function webpress_get_option( $option ) {
	$defaults = webpress_get_defaults();

	if ( ! isset( $defaults[ $option ] ) ) {
		return;
	}

	$options = wp_parse_args(
		get_option( 'webpress_settings', array() ),
		$defaults
	);

	return $options[ $option ];
}

if ( ! function_exists( 'webpress_get_layout' ) ) {
	/**
	 * Get the layout for the current page.
	 *
	 * @since 1.0.0
	 *
	 * @return string The sidebar layout location.
	 */
	function webpress_get_layout() {
		$layout = webpress_get_option( 'layout_setting' );

		if ( is_single() ) {
			$layout = webpress_get_option( 'single_layout_setting' );
		}

		if ( is_singular() ) {
			$layout_meta = get_post_meta( get_the_ID(), '_webpress-sidebar-layout-meta', true );

			if ( $layout_meta ) {
				$layout = $layout_meta;
			}
		}

		if ( is_home() || is_archive() || is_search() || is_tax() ) {
			$layout = webpress_get_option( 'blog_layout_setting' );
		}

		/**
		 * Filters the sidebar layout for the current view.
		 *
		 * Applied after any per-post override, so this is the final say.
		 *
		 * @since 1.0.0
		 *
		 * @param string $layout The sidebar layout slug.
		 * @return string The sidebar layout to use.
		 */
		return apply_filters( 'webpress_sidebar_layout', $layout );
	}
}

/**
 * Get the allowed container layouts.
 *
 * @since 1.0.0
 *
 * @return array Container layout slugs mapped to their labels.
 */
function webpress_get_container_layouts() {
	return array(
		'normal' => __( 'Normal', 'webpress' ),
		'narrow' => __( 'Narrow', 'webpress' ),
		'full-width' => __( 'Full Width', 'webpress' ),
	);
}

/**
 * Get the container layout for the current page.
 *
 * Pages, single posts and blog listings each have their own setting, and a
 * post or page can override it from the Layout meta box.
 *
 * @since 1.0.0
 *
 * @return string normal, narrow or full-width.
 */
function webpress_get_container_layout() {
	$layout = webpress_get_option( 'page_container_layout' );

	if ( is_single() ) {
		$layout = webpress_get_option( 'single_container_layout' );
	}

	if ( is_singular() ) {
		$layout_meta = get_post_meta( get_the_ID(), '_webpress-container-layout', true );

		if ( $layout_meta ) {
			$layout = $layout_meta;
		}

		// The full width page builder container stretches the container too.
		if ( 'true' === get_post_meta( get_the_ID(), '_webpress-full-width-content', true ) ) {
			$layout = 'full-width';
		}
	}

	if ( is_home() || is_archive() || is_search() ) {
		$layout = webpress_get_option( 'blog_container_layout' );
	}

	/**
	 * Filters the container layout for the current view.
	 *
	 * @since 1.0.0
	 *
	 * @param string $layout The container layout: normal, narrow or full-width.
	 * @return string The container layout to use.
	 */
	$layout = apply_filters( 'webpress_container_layout', $layout );

	if ( ! array_key_exists( $layout, webpress_get_container_layouts() ) ) {
		$layout = 'normal';
	}

	return $layout;
}

/**
 * Get the percentage of the container taken up by the content area.
 *
 * @since 1.0.0
 *
 * @param string $sidebar_layout The sidebar layout slug.
 * @return int The content area width as a percentage.
 */
function webpress_get_content_area_width( $sidebar_layout ) {
	$sidebar_width = 30;

	switch ( $sidebar_layout ) {
		case 'right-sidebar':
		case 'left-sidebar':
			return 100 - $sidebar_width;

		case 'both-sidebars':
		case 'both-right':
		case 'both-left':
			return 100 - ( $sidebar_width * 2 );
	}

	return 100;
}

/**
 * Get the maximum width of the page container for the narrow layout.
 *
 * The narrow width applies to the content column, so when sidebars are
 * showing the container grows to fit them beside it. It never grows past
 * the regular container width.
 *
 * @since 1.0.0
 *
 * @param string $sidebar_layout The sidebar layout slug.
 * @return int The container width in pixels.
 */
function webpress_get_narrow_container_width( $sidebar_layout ) {
	$container_width = absint( webpress_get_option( 'container_width' ) );
	$narrow_width = absint( webpress_get_option( 'narrow_container_width' ) );
	$content_area_width = webpress_get_content_area_width( $sidebar_layout );

	$width = (int) round( $narrow_width * 100 / $content_area_width );

	return min( $width, $container_width );
}

/**
 * Get the layout of the posts in blog and archive listings.
 *
 * @since 1.0.0
 *
 * @return string classic, list or grid.
 */
function webpress_get_blog_post_layout() {
	$layout = 'classic';

	$is_listing = is_home() || is_archive() || is_search();

	// WooCommerce and bbPress archives print their own markup.
	$is_plugin_archive = ( function_exists( 'is_woocommerce' ) && is_woocommerce() )
		|| ( function_exists( 'is_bbpress' ) && is_bbpress() );

	if ( $is_listing && ! $is_plugin_archive && webpress_has_default_loop() ) {
		$layout = webpress_get_option( 'blog_post_layout' );
	}

	/**
	 * Filters the layout of the posts in blog and archive listings.
	 *
	 * @since 1.0.0
	 *
	 * @param string $layout The post layout: classic, list or grid.
	 * @return string The post layout to use.
	 */
	$layout = apply_filters( 'webpress_blog_post_layout', $layout );

	if ( ! in_array( $layout, array( 'classic', 'list', 'grid' ), true ) ) {
		$layout = 'classic';
	}

	return $layout;
}

/**
 * Whether listing posts wrap their entry header, content and footer in a
 * .entry-body element, so the featured image can sit beside or above it.
 *
 * @since 1.0.0
 *
 * @return bool Whether to output the wrapper.
 */
function webpress_has_entry_body_wrapper() {
	return ! is_singular() && 'classic' !== webpress_get_blog_post_layout();
}

if ( ! function_exists( 'webpress_get_footer_widgets' ) ) {
	/**
	 * Get the footer widgets for the current page
	 *
	 * @since 1.0.0
	 *
	 * @return int The number of footer widgets.
	 */
	function webpress_get_footer_widgets() {
		$widgets = webpress_get_option( 'footer_widget_setting' );

		if ( is_singular() ) {
			$widgets_meta = get_post_meta( get_the_ID(), '_webpress-footer-widget-meta', true );

			if ( $widgets_meta || '0' === $widgets_meta ) {
				$widgets = $widgets_meta;
			}
		}

		/**
		 * Filters the number of footer widget columns to display.
		 *
		 * Applied after any per-post override, so this is the final say.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $widgets The number of footer widget areas to show.
		 * @return int|string The number of footer widget areas to show.
		 */
		return apply_filters( 'webpress_footer_widgets', $widgets );
	}
}

if ( ! function_exists( 'webpress_show_excerpt' ) ) {
	/**
	 * Figure out if we should show the blog excerpts or full posts
	 *
	 * @since 1.0.0
	 */
	function webpress_show_excerpt() {
		global $post;

		// Check to see if the more tag is being used.
		$more_tag = strpos( $post->post_content, '<!--more-->' );

		$format = ( false !== get_post_format() ) ? get_post_format() : 'standard';

		$show_excerpt = ( 'excerpt' === webpress_get_option( 'post_content' ) ) ? true : false;

		$show_excerpt = ( 'standard' !== $format ) ? false : $show_excerpt;

		$show_excerpt = ( $more_tag ) ? false : $show_excerpt;

		$show_excerpt = ( is_search() ) ? true : $show_excerpt;

		/**
		 * Filters whether the loop shows excerpts instead of full content.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $show_excerpt Whether to show the excerpt.
		 * @return bool Whether to show the excerpt.
		 */
		return apply_filters( 'webpress_show_excerpt', $show_excerpt );
	}
}

if ( ! function_exists( 'webpress_show_title' ) ) {
	/**
	 * Check to see if we should show our page/post title or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether to show the content title.
	 */
	function webpress_show_title() {
		$show_title = true;

		if ( is_singular() && get_post_meta( get_the_ID(), '_webpress-disable-headline', true ) ) {
			$show_title = false;
		}

		/**
		 * Filters whether the content title is displayed.
		 *
		 * Applied after the per-post "disable headline" meta, so a callback can
		 * override that choice in either direction.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $show_title Whether to show the title.
		 * @return bool Whether to show the title.
		 */
		return apply_filters( 'webpress_show_title', $show_title );
	}
}

/**
 * Check whether we should display the entry header or not.
 *
 * @since 1.0.0
 */
function webpress_show_entry_header() {
	return webpress_show_title();
}

if ( ! function_exists( 'webpress_padding_css' ) ) {
	/**
	 * Shorten our padding/margin values into shorthand form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $top Top spacing.
	 * @param int $right Right spacing.
	 * @param int $bottom Bottom spacing.
	 * @param int $left Left spacing.
	 * @return string Element spacing values.
	 */
	function webpress_padding_css( $top, $right, $bottom, $left ) {
		$padding_top = ( isset( $top ) && '' !== $top ) ? absint( $top ) . 'px ' : '0px ';
		$padding_right = ( isset( $right ) && '' !== $right ) ? absint( $right ) . 'px ' : '0px ';
		$padding_bottom = ( isset( $bottom ) && '' !== $bottom ) ? absint( $bottom ) . 'px ' : '0px ';
		$padding_left = ( isset( $left ) && '' !== $left ) ? absint( $left ) . 'px' : '0px';

		if ( ( absint( $padding_top ) === absint( $padding_right ) ) && ( absint( $padding_right ) === absint( $padding_bottom ) ) && ( absint( $padding_bottom ) === absint( $padding_left ) ) ) {
			return $padding_left;
		}

		return $padding_top . $padding_right . $padding_bottom . $padding_left;
	}
}

if ( ! function_exists( 'webpress_get_link_url' ) ) {
	/**
	 * Return the post URL.
	 *
	 * Falls back to the post permalink if no URL is found in the post.
	 *
	 * @since 1.0.0
	 *
	 * @see get_url_in_content()
	 * @return string The Link format URL.
	 */
	function webpress_get_link_url() {
		$has_url = get_url_in_content( get_the_content() );

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter name.
		return $has_url ? $has_url : apply_filters( 'the_permalink', get_permalink() );
	}
}

if ( ! function_exists( 'webpress_get_navigation_location' ) ) {
	/**
	 * Get the location of the navigation and filter it.
	 *
	 * @since 1.0.0
	 *
	 * @return string The primary menu location.
	 */
	function webpress_get_navigation_location() {
		return webpress_get_option( 'nav_position_setting' );
	}
}

/**
 * Check if the logo and site branding are active.
 *
 * @since 1.0.0
 */
function webpress_has_logo_site_branding() {
	$has_site_title = ! webpress_get_option( 'hide_title' ) && get_bloginfo( 'title' );
	$has_site_tagline = ! webpress_get_option( 'hide_tagline' ) && get_bloginfo( 'description' );

	if ( get_theme_mod( 'custom_logo' ) && ( $has_site_title || $has_site_tagline ) ) {
		return true;
	}

	return false;
}

if ( ! function_exists( 'webpress_get_svg_icon' ) ) {
	/**
	 * Create SVG icons.
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon The icon to get.
	 * @param bool   $replace Whether we're replacing an icon on action (click).
	 */
	function webpress_get_svg_icon( $icon, $replace = false ) {
		$output = '';

		if ( 'menu-bars' === $icon ) {
			$output = '<svg viewBox="0 0 512 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path d="M0 96c0-13.255 10.745-24 24-24h464c13.255 0 24 10.745 24 24s-10.745 24-24 24H24c-13.255 0-24-10.745-24-24zm0 160c0-13.255 10.745-24 24-24h464c13.255 0 24 10.745 24 24s-10.745 24-24 24H24c-13.255 0-24-10.745-24-24zm0 160c0-13.255 10.745-24 24-24h464c13.255 0 24 10.745 24 24s-10.745 24-24 24H24c-13.255 0-24-10.745-24-24z" /></svg>';
		}

		if ( 'close' === $icon ) {
			$output = '<svg viewBox="0 0 512 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path d="M71.029 71.029c9.373-9.372 24.569-9.372 33.942 0L256 222.059l151.029-151.03c9.373-9.372 24.569-9.372 33.942 0 9.372 9.373 9.372 24.569 0 33.942L289.941 256l151.03 151.029c9.372 9.373 9.372 24.569 0 33.942-9.373 9.372-24.569 9.372-33.942 0L256 289.941l-151.029 151.03c-9.373 9.372-24.569 9.372-33.942 0-9.372-9.373-9.372-24.569 0-33.942L222.059 256 71.029 104.971c-9.372-9.373-9.372-24.569 0-33.942z" /></svg>';
		}

		if ( 'search' === $icon ) {
			$output = '<svg viewBox="0 0 512 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path fill-rule="evenodd" clip-rule="evenodd" d="M208 48c-88.366 0-160 71.634-160 160s71.634 160 160 160 160-71.634 160-160S296.366 48 208 48zM0 208C0 93.125 93.125 0 208 0s208 93.125 208 208c0 48.741-16.765 93.566-44.843 129.024l133.826 134.018c9.366 9.379 9.355 24.575-.025 33.941-9.379 9.366-24.575 9.355-33.941-.025L337.238 370.987C301.747 399.167 256.839 416 208 416 93.125 416 0 322.875 0 208z" /></svg>';
		}

		if ( 'categories' === $icon ) {
			$output = '<svg viewBox="0 0 512 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path d="M0 112c0-26.51 21.49-48 48-48h110.014a48 48 0 0143.592 27.907l12.349 26.791A16 16 0 00228.486 128H464c26.51 0 48 21.49 48 48v224c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48V112z" /></svg>';
		}

		if ( 'tags' === $icon ) {
			$output = '<svg viewBox="0 0 512 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path d="M20 39.5c-8.836 0-16 7.163-16 16v176c0 4.243 1.686 8.313 4.687 11.314l224 224c6.248 6.248 16.378 6.248 22.626 0l176-176c6.244-6.244 6.25-16.364.013-22.615l-223.5-224A15.999 15.999 0 00196.5 39.5H20zm56 96c0-13.255 10.745-24 24-24s24 10.745 24 24-10.745 24-24 24-24-10.745-24-24z"/><path d="M259.515 43.015c4.686-4.687 12.284-4.687 16.97 0l228 228c4.686 4.686 4.686 12.284 0 16.97l-180 180c-4.686 4.687-12.284 4.687-16.97 0-4.686-4.686-4.686-12.284 0-16.97L479.029 279.5 259.515 59.985c-4.686-4.686-4.686-12.284 0-16.97z" /></svg>';
		}

		if ( 'comments' === $icon ) {
			$output = '<svg viewBox="0 0 512 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path d="M132.838 329.973a435.298 435.298 0 0016.769-9.004c13.363-7.574 26.587-16.142 37.419-25.507 7.544.597 15.27.925 23.098.925 54.905 0 105.634-15.311 143.285-41.28 23.728-16.365 43.115-37.692 54.155-62.645 54.739 22.205 91.498 63.272 91.498 110.286 0 42.186-29.558 79.498-75.09 102.828 23.46 49.216 75.09 101.709 75.09 101.709s-115.837-38.35-154.424-78.46c-9.956 1.12-20.297 1.758-30.793 1.758-88.727 0-162.927-43.071-181.007-100.61z"/><path d="M383.371 132.502c0 70.603-82.961 127.787-185.216 127.787-10.496 0-20.837-.639-30.793-1.757-38.587 40.093-154.424 78.429-154.424 78.429s51.63-52.472 75.09-101.67c-45.532-23.321-75.09-60.619-75.09-102.79C12.938 61.9 95.9 4.716 198.155 4.716 300.41 4.715 383.37 61.9 383.37 132.502z" /></svg>';
		}

		if ( 'arrow' === $icon ) {
			$output = '<svg viewBox="0 0 330 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path d="M305.913 197.085c0 2.266-1.133 4.815-2.833 6.514L171.087 335.593c-1.7 1.7-4.249 2.832-6.515 2.832s-4.815-1.133-6.515-2.832L26.064 203.599c-1.7-1.7-2.832-4.248-2.832-6.514s1.132-4.816 2.832-6.515l14.162-14.163c1.7-1.699 3.966-2.832 6.515-2.832 2.266 0 4.815 1.133 6.515 2.832l111.316 111.317 111.316-111.317c1.7-1.699 4.249-2.832 6.515-2.832s4.815 1.133 6.515 2.832l14.162 14.163c1.7 1.7 2.833 4.249 2.833 6.515z" /></svg>';
		}

		if ( 'arrow-right' === $icon ) {
			$output = '<svg viewBox="0 0 192 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M178.425 256.001c0 2.266-1.133 4.815-2.832 6.515L43.599 394.509c-1.7 1.7-4.248 2.833-6.514 2.833s-4.816-1.133-6.515-2.833l-14.163-14.162c-1.699-1.7-2.832-3.966-2.832-6.515 0-2.266 1.133-4.815 2.832-6.515l111.317-111.316L16.407 144.685c-1.699-1.7-2.832-4.249-2.832-6.515s1.133-4.815 2.832-6.515l14.163-14.162c1.7-1.7 4.249-2.833 6.515-2.833s4.815 1.133 6.514 2.833l131.994 131.993c1.7 1.7 2.832 4.249 2.832 6.515z" fill-rule="nonzero" /></svg>';
		}

		if ( 'arrow-left' === $icon ) {
			$output = '<svg viewBox="0 0 192 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M178.425 138.212c0 2.265-1.133 4.813-2.832 6.512L64.276 256.001l111.317 111.277c1.7 1.7 2.832 4.247 2.832 6.513 0 2.265-1.133 4.813-2.832 6.512L161.43 394.46c-1.7 1.7-4.249 2.832-6.514 2.832-2.266 0-4.816-1.133-6.515-2.832L16.407 262.514c-1.699-1.7-2.832-4.248-2.832-6.513 0-2.265 1.133-4.813 2.832-6.512l131.994-131.947c1.7-1.699 4.249-2.831 6.515-2.831 2.265 0 4.815 1.132 6.514 2.831l14.163 14.157c1.7 1.7 2.832 3.965 2.832 6.513z" fill-rule="nonzero" /></svg>';
		}

		if ( 'arrow-up' === $icon ) {
			$output = '<svg viewBox="0 0 330 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M305.863 314.916c0 2.266-1.133 4.815-2.832 6.514l-14.157 14.163c-1.699 1.7-3.964 2.832-6.513 2.832-2.265 0-4.813-1.133-6.512-2.832L164.572 224.276 53.295 335.593c-1.699 1.7-4.247 2.832-6.512 2.832-2.265 0-4.814-1.133-6.513-2.832L26.113 321.43c-1.699-1.7-2.831-4.248-2.831-6.514s1.132-4.816 2.831-6.515L158.06 176.408c1.699-1.7 4.247-2.833 6.512-2.833 2.265 0 4.814 1.133 6.513 2.833L303.03 308.4c1.7 1.7 2.832 4.249 2.832 6.515z" fill-rule="nonzero" /></svg>';
		}

		/**
		 * Filters the raw SVG markup for a single icon.
		 *
		 * Fires before the optional close icon is appended and before the icon is
		 * wrapped in its `.bp-icon` span.
		 *
		 * @since 1.0.0
		 *
		 * @param string $output  The SVG markup, or an empty string for an unknown icon.
		 * @param string $icon    The icon name being requested.
		 * @param bool   $replace Whether a close icon will be appended for JS toggling.
		 * @return string The SVG markup to use.
		 */
		$output = apply_filters( 'webpress_svg_icon', $output, $icon, $replace );

		if ( $replace ) {
			$output .= '<svg viewBox="0 0 512 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path d="M71.029 71.029c9.373-9.372 24.569-9.372 33.942 0L256 222.059l151.029-151.03c9.373-9.372 24.569-9.372 33.942 0 9.372 9.373 9.372 24.569 0 33.942L289.941 256l151.03 151.029c9.372 9.373 9.372 24.569 0 33.942-9.373 9.372-24.569 9.372-33.942 0L256 289.941l-151.029 151.03c-9.373 9.372-24.569 9.372-33.942 0-9.372-9.373-9.372-24.569 0-33.942L222.059 256 71.029 104.971c-9.372-9.373-9.372-24.569 0-33.942z" /></svg>';
		}

		$classes = array(
			'bp-icon',
			'icon-' . $icon,
		);

		$output = sprintf(
			'<span class="%1$s">%2$s</span>',
			implode( ' ', $classes ),
			$output
		);

		return $output;
	}
}

if ( ! function_exists( 'webpress_do_svg_icon' ) ) {
	/**
	 * Out our icon HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon The icon to print.
	 * @param bool   $replace Whether to include the close icon to be shown using JS.
	 */
	function webpress_do_svg_icon( $icon, $replace = false ) {
		echo webpress_get_svg_icon( $icon, $replace ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in function.
	}
}

/**
 * Get our media queries.
 *
 * @since 1.0.0
 *
 * @param string $name Name of the media query.
 * @return string The full media query.
 */
function webpress_get_media_query( $name ) {
	$desktop = '(min-width:1025px)';
	$tablet_only = '(min-width: 769px) and (max-width: 1024px)';
	$mobile = '(max-width:768px)';
	$mobile_menu = $mobile;

	$queries = array(
		'desktop' => $desktop,
		'tablet_only' => $tablet_only,
		'tablet' => '(max-width: 1024px)',
		'mobile' => $mobile,
		'mobile-menu' => $mobile_menu,
	);

	return $queries[ $name ];
}

/**
 * Display HTML classes for an element.
 *
 * @since 1.0.0
 *
 * @param string       $context The element we're targeting.
 * @param string|array $class One or more classes to add to the class list.
 */
function webpress_do_element_classes( $context, $class = '' ) {
	$after = webpress_set_microdata_markup( '', $context );

	if ( $after ) {
		$after = ' ' . $after;
	}

	echo 'class="' . join( ' ', webpress_get_element_classes( $context, $class ) ) . '"' . $after; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in function.
}

/**
 * Retrieve HTML classes for an element.
 *
 * @since 1.0.0
 *
 * @param string       $context The element we're targeting.
 * @param string|array $class One or more classes to add to the class list.
 * @return array Array of classes.
 */
function webpress_get_element_classes( $context, $class = '' ) {
	$classes = array();

	if ( ! empty( $class ) ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}

		$classes = array_merge( $classes, $class );
	}

	$classes = array_map( 'esc_attr', $classes );

	switch ( $context ) {
		case 'top_bar':
			return webpress_top_bar_classes( $classes );

		case 'right_sidebar':
			return webpress_right_sidebar_classes( $classes );

		case 'left_sidebar':
			return webpress_left_sidebar_classes( $classes );

		case 'content':
			return webpress_content_classes( $classes );

		case 'header':
			return webpress_header_classes( $classes );

		case 'inside_header':
			return webpress_inside_header_classes( $classes );

		case 'navigation':
			return webpress_navigation_classes( $classes );

		case 'inside_navigation':
			return webpress_inside_navigation_classes( $classes );

		case 'menu':
			return webpress_menu_classes( $classes );

		case 'footer':
			return webpress_footer_classes( $classes );

		case 'inside_footer':
			return webpress_inside_footer_classes( $classes );

		case 'main':
			return webpress_main_classes( $classes );

		case 'page':
			return webpress_do_page_container_classes( $classes );

		case 'comment-author':
			return webpress_do_comment_author_classes( $classes );
	}

	return $classes;
}

/**
 * Get the kind of schema we're using.
 *
 * @since 1.0.0
 */
function webpress_get_schema_type() {
	return 'microdata';
}

/**
 * Get any necessary microdata.
 *
 * @since 1.0.0
 *
 * @param string $context The element to target.
 * @return string Our final attribute to add to the element.
 */
function webpress_get_microdata( $context ) {
	$data = false;

	if ( 'microdata' !== webpress_get_schema_type() ) {
		return false;
	}

	if ( 'body' === $context ) {
		$type = 'WebPage';

		if ( is_home() || is_archive() || is_attachment() || is_tax() || is_single() ) {
			$type = 'Blog';
		}

		if ( is_search() ) {
			$type = 'SearchResultsPage';
		}

		/**
		 * Filters the schema.org type used for the body element.
		 *
		 * Note this is the schema *type* (Blog, WebPage, SearchResultsPage), not
		 * the schema format returned by webpress_get_schema_type().
		 *
		 * @since 1.0.0
		 *
		 * @param string $type The schema.org type name, without the URL prefix.
		 * @return string The schema.org type to output.
		 */
		$type = apply_filters( 'webpress_schema_type', $type );

		$data = sprintf(
			'itemtype="https://schema.org/%s" itemscope',
			esc_html( $type )
		);
	}

	if ( 'header' === $context ) {
		$data = 'itemtype="https://schema.org/WPHeader" itemscope';
	}

	if ( 'navigation' === $context ) {
		$data = 'itemtype="https://schema.org/SiteNavigationElement" itemscope';
	}

	if ( 'article' === $context ) {
		$type = 'CreativeWork';

		$data = sprintf(
			'itemtype="https://schema.org/%s" itemscope',
			esc_html( $type )
		);
	}

	if ( 'post-author' === $context ) {
		$data = 'itemprop="author" itemtype="https://schema.org/Person" itemscope';
	}

	if ( 'comment-body' === $context ) {
		$data = 'itemtype="https://schema.org/Comment" itemscope';
	}

	if ( 'comment-author' === $context ) {
		$data = 'itemprop="author" itemtype="https://schema.org/Person" itemscope';
	}

	if ( 'sidebar' === $context ) {
		$data = 'itemtype="https://schema.org/WPSideBar" itemscope';
	}

	if ( 'footer' === $context ) {
		$data = 'itemtype="https://schema.org/WPFooter" itemscope';
	}

	return $data;
}

if ( ! function_exists( 'webpress_do_microdata' ) ) {
	/**
	 * Output our microdata for an element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context The element to target.
	 */
	function webpress_do_microdata( $context ) {
		echo webpress_get_microdata( $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in function.
	}
}

/**
 * Whether to print hAtom output or not.
 *
 * @since 1.0.0
 */
function webpress_is_using_hatom() {
	return true;
}

/**
 * Check if we have any menu bar items.
 *
 * @since 1.0.0
 */
function webpress_has_menu_bar_items() {
	if ( 'enable' === webpress_get_option( 'nav_search' ) ) {
		return true;
	}

	return (bool) webpress_get_option( 'nav_search_modal' );
}

if ( ! function_exists( 'webpress_do_template_part' ) ) {
	/**
	 * Check if we should include the default template part.
	 *
	 * @since 1.0.0
	 * @param string $template The template to get.
	 */
	function webpress_do_template_part( $template ) {
		if ( 'archive' === $template || 'index' === $template ) {
			get_template_part( 'content', get_post_format() );
		} elseif ( 'none' === $template ) {
			get_template_part( 'no-results' );
		} else {
			get_template_part( 'content', $template );
		}

		webpress_do_comments_template( $template );
	}
}

/**
 * Check if we should use inline mobile navigation.
 *
 * @since 1.0.0
 */
function webpress_has_inline_mobile_toggle() {
	$has_inline_mobile_toggle = 'nav-float-right' === webpress_get_navigation_location() || 'nav-float-left' === webpress_get_navigation_location();

	return $has_inline_mobile_toggle;
}

/**
 * Build our the_title() parameters.
 *
 * @since 1.0.0
 */
function webpress_get_the_title_parameters() {
	$params = array(
		'before' => sprintf(
			'<h1 class="entry-title"%s>',
			'microdata' === webpress_get_schema_type() ? ' itemprop="headline"' : ''
		),
		'after' => '</h1>',
	);

	if ( ! is_singular() ) {
		$params = array(
			'before' => sprintf(
				'<h2 class="entry-title"%2$s><a href="%1$s" rel="bookmark">',
				esc_url( get_permalink() ),
				'microdata' === webpress_get_schema_type() ? ' itemprop="headline"' : ''
			),
			'after' => '</a></h2>',
		);
	}

	if ( 'link' === get_post_format() ) {
		$params = array(
			'before' => sprintf(
				'<h2 class="entry-title"%2$s><a href="%1$s" rel="bookmark">',
				esc_url( webpress_get_link_url() ),
				'microdata' === webpress_get_schema_type() ? ' itemprop="headline"' : ''
			),
			'after' => '</a></h2>',
		);
	}

	return $params;
}

/**
 * Check whether we should display the default loop or not.
 *
 * @since 1.0.0
 *
 * @return bool Whether the default loop should run.
 */
function webpress_has_default_loop() {
	/**
	 * Filters whether the default post loop should run.
	 *
	 * Page builders and single-page plugins return false here to suppress the
	 * theme's own loop while rendering their own content.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $has_default_loop Whether to run the default loop. Default true.
	 * @return bool Whether to run the default loop.
	 */
	return apply_filters( 'webpress_default_loop', true );
}

/**
 * Detemine whether to output site branding container.
 *
 * @since 1.0.0
 */
function webpress_needs_site_branding_container() {
	$container = false;

	if ( webpress_has_logo_site_branding() ) {
		$container = true;
	}

	return $container;
}

/**
 * Merge array of attributes with defaults, and apply contextual filter on array.
 *
 * The contextual filter is of the form `webpress_attr_{context}`.
 *
 * @since 1.0.0
 *
 * @param string $context    The context, to build filter name.
 * @param array  $attributes Optional. Extra attributes to merge with defaults.
 * @param array  $settings   Optional. Custom data to pass to filter.
 * @return array Merged and filtered attributes.
 */
function webpress_parse_attr( $context, $attributes = array(), $settings = array() ) {
	// Initialize an empty class attribute so it's easier to append to in filters.
	if ( ! isset( $attributes['class'] ) ) {
		$attributes['class'] = '';
	}

	// We used to have a class-only system. If it's in use, add the classes.
	$classes = webpress_get_element_classes( $context );

	if ( $classes ) {
		$attributes['class'] .= join( ' ', $classes );
	}

	// Contextual filter.
	return WebPress_HTML_Attributes::get_instance()->parse_attributes( $attributes, $context, $settings );
}

/**
 * Build list of attributes into a string and apply contextual filter on string.
 *
 * The contextual filter is of the form `webpress_attr_{context}_output`.
 *
 * @since 1.0.0
 *
 * @param string $context    The context, to build filter name.
 * @param array  $attributes Optional. Extra attributes to merge with defaults.
 * @param array  $settings   Optional. Custom data to pass to filter.
 * @return string String of HTML attributes and values.
 */
function webpress_get_attr( $context, $attributes = array(), $settings = array() ) {
	$attributes = webpress_parse_attr( $context, $attributes, $settings );

	$output = '';

	// Cycle through attributes, build tag attribute string.
	foreach ( $attributes as $key => $value ) {
		if ( ! $value ) {
			continue;
		}

		// Remove any whitespace at the start or end of our classes.
		if ( 'class' === $key ) {
			$value = trim( $value );
		}

		if ( true === $value ) {
			$output .= esc_html( $key ) . ' ';
		} else {
			$output .= sprintf( '%s="%s" ', esc_html( $key ), esc_attr( $value ) );
		}
	}

	// Before this function existed we had the below to add attributes after the class attribute.
	$after = webpress_set_microdata_markup( '', $context );

	if ( $after ) {
		$after = ' ' . $after;
	}

	$output .= $after;

	/**
	 * Filters the assembled HTML attribute string for a given context.
	 *
	 * The dynamic portion of the hook name, `$context`, refers to the element
	 * being built — for example `webpress_attr_header_output`.
	 *
	 * @since 1.0.0
	 *
	 * @param string $output     The built attribute string, before trimming.
	 * @param string $context    The element context.
	 * @param array  $settings   Custom data passed to the attribute builder.
	 * @return string The attribute string to output.
	 */
	$output = apply_filters( "webpress_attr_{$context}_output", $output, $context, $settings );

	return trim( $output );
}

/**
 * Output our string of HTML attributes.
 *
 * @since 1.0.0
 *
 * @param string $context    The context, to build filter name.
 * @param array  $attributes Optional. Extra attributes to merge with defaults.
 * @param array  $settings   Optional. Custom data to pass to filter.
 */
function webpress_do_attr( $context, $attributes = array(), $settings = array() ) {
	echo webpress_get_attr( $context, $attributes, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- webpress_get_attr() escapes every name and value.
}

/**
 * Build our editor color palette based on our global colors.
 *
 * @since 1.0.0
 */
function webpress_get_editor_color_palette() {
	$global_colors = webpress_get_option( 'global_colors' );
	$editor_palette = array();
	$static_colors = false;

	if ( ! empty( $global_colors ) ) {
		foreach ( (array) $global_colors as $key => $data ) {
			$editor_palette[] = array(
				'name' => $data['name'],
				'slug' => $data['slug'],
				'color' => $static_colors ? $data['color'] : 'var(--' . $data['slug'] . ')',
			);
		}
	}

	return $editor_palette;
}

/**
 * Get our global colors.
 *
 * @since 1.0.0
 */
function webpress_get_global_colors() {
	$global_colors = webpress_get_option( 'global_colors' );
	$colors = array();

	if ( ! empty( $global_colors ) ) {
		foreach ( (array) $global_colors as $key => $data ) {
			$colors[] = array(
				'slug' => $data['slug'],
				'color' => $data['color'],
			);
		}
	}

	return $colors;
}

/**
 * Get our system default font.
 *
 * @since 1.0.0
 */
function webpress_get_system_default_font() {
	return '-apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';
}

/**
 * Check to see if we have a GP menu active.
 * This is primarily used to know whether we need to enqueue menu.js or not.
 *
 * @since 1.0.0
 */
function webpress_has_active_menu() {
	$has_active_menu = true;

	if ( ! webpress_get_navigation_location() ) {
		$has_active_menu = false;
	}

	return $has_active_menu;
}

/**
 * Add inline script.
 *
 * @param string $handle The script handle to attach the inline script to.
 * @param array  $data   The data to be passed to the script.
 * @param string $var    The JavaScript variable name to assign the data to.
 * @param string $position The position to add the inline script.
 */
function webpress_add_inline_script( $handle, $data, $var, $position = 'before' ) {
	if ( ! empty( $data ) ) {
		$json_data = wp_json_encode( $data );
		$inline_script = "var $var = $json_data;";
		wp_add_inline_script( $handle, $inline_script, $position );
	}
}
