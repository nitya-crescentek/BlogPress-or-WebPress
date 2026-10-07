<?php
/**
 * Helper functions for the Customizer.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'webpress_is_footer_bar_active' ) ) {
	/**
	 * Check to see if we're using our footer bar widget
	 *
	 * @since 1.0.0
	 */
	function webpress_is_footer_bar_active() {
		return ( is_active_sidebar( 'footer-bar' ) ) ? true : false;
	}
}

if ( ! function_exists( 'webpress_is_top_bar_active' ) ) {
	/**
	 * Check to see if the top bar is active
	 *
	 * @since 1.0.0
	 */
	function webpress_is_top_bar_active() {
		$top_bar = is_active_sidebar( 'top-bar' ) ? true : false;
		return $top_bar;
	}
}

if ( ! function_exists( 'webpress_customize_partial_blogname' ) ) {
	/**
	 * Render the site title for the selective refresh partial.
	 *
	 * @since 1.0.0
	 */
	function webpress_customize_partial_blogname() {
		bloginfo( 'name' );
	}
}

if ( ! function_exists( 'webpress_customize_partial_blogdescription' ) ) {
	/**
	 * Render the site tagline for the selective refresh partial.
	 *
	 * @since 1.0.0
	 */
	function webpress_customize_partial_blogdescription() {
		bloginfo( 'description' );
	}
}

if ( ! function_exists( 'webpress_enqueue_color_palettes' ) ) {
	add_action( 'customize_controls_enqueue_scripts', 'webpress_enqueue_color_palettes' );
	/**
	 * Add our custom color palettes to the color pickers in the Customizer.
	 *
	 * @since 1.0.0
	 */
	function webpress_enqueue_color_palettes() {
		// Old versions of WP don't get nice things.
		if ( ! function_exists( 'wp_add_inline_script' ) ) {
			return;
		}

		// Grab our palette array and turn it into JS.
		$palettes = wp_json_encode( webpress_get_default_color_palettes() );

		// Add our custom palettes.
		// json_encode takes care of escaping.
		wp_add_inline_script( 'wp-color-picker', 'jQuery.wp.wpColorPicker.prototype.options.palettes = ' . $palettes . ';' );
	}
}

if ( ! function_exists( 'webpress_sanitize_integer' ) ) {
	/**
	 * Sanitize integers.
	 *
	 * @since 1.0.8
	 * @param string $input The value to check.
	 */
	function webpress_sanitize_integer( $input ) {
		return absint( $input );
	}
}



/**
 * Sanitize a positive number, but allow an empty value.
 *
 * @since 1.0.0
 * @param string $input The value to check.
 */
function webpress_sanitize_empty_absint( $input ) {
	// phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- Intentionally loose.
	if ( '' == $input ) {
		return '';
	}

	return absint( $input );
}

if ( ! function_exists( 'webpress_sanitize_checkbox' ) ) {
	/**
	 * Sanitize checkbox values.
	 *
	 * @since 1.0.8
	 * @param string $checked The value to check.
	 */
	function webpress_sanitize_checkbox( $checked ) {
		// phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- Intentionally loose.
		return ( ( isset( $checked ) && true == $checked ) ? true : false );
	}
}

if ( ! function_exists( 'webpress_sanitize_blog_excerpt' ) ) {
	/**
	 * Sanitize blog excerpt.
	 *
	 * @since 1.0.8
	 * @param string $input The value to check.
	 */
	function webpress_sanitize_blog_excerpt( $input ) {
		$valid = array(
			'full',
			'excerpt',
		);

		if ( in_array( $input, $valid ) ) {
			return $input;
		} else {
			return 'full';
		}
	}
}

if ( ! function_exists( 'webpress_sanitize_hex_color' ) ) {
	/**
	 * Sanitize colors.
	 * Allow blank value.
	 *
	 * @since 1.0.0
	 * @param string $color The color to check.
	 */
	function webpress_sanitize_hex_color( $color ) {
		if ( '' === $color ) {
			return '';
		}

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}

		// Sanitize CSS variables.
		if ( strpos( $color, 'var(' ) !== false ) {
			return sanitize_text_field( $color );
		}

		// Sanitize rgb() values.
		if ( strpos( $color, 'rgb(' ) !== false ) {
			$color = str_replace( ' ', '', $color );

			sscanf( $color, 'rgb(%d,%d,%d)', $red, $green, $blue );
			return 'rgb(' . $red . ',' . $green . ',' . $blue . ')';
		}

		// Sanitize rgba() values.
		if ( strpos( $color, 'rgba' ) !== false ) {
			$color = str_replace( ' ', '', $color );
			sscanf( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );

			return 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $alpha . ')';
		}

		return '';
	}
}

/**
 * Sanitize RGBA colors.
 *
 * @since 1.0.0
 * @param string $color The color to check.
 */
function webpress_sanitize_rgba_color( $color ) {
	if ( '' === $color ) {
		return '';
	}

	if ( false === strpos( $color, 'rgba' ) ) {
		return webpress_sanitize_hex_color( $color );
	}

	$color = str_replace( ' ', '', $color );
	sscanf( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );

	return 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $alpha . ')';
}

if ( ! function_exists( 'webpress_sanitize_choices' ) ) {
	/**
	 * Sanitize choices.
	 *
	 * @since 1.0.0
	 * @param string $input The value to check.
	 * @param object $setting The setting object.
	 */
	function webpress_sanitize_choices( $input, $setting ) {
		// Ensure input is a slug.
		$input = sanitize_key( $input );

		// Get list of choices from the control.
		// associated with the setting.
		$choices = $setting->manager->get_control( $setting->id )->choices;

		// If the input is a valid key, return it.
		// otherwise, return the default.
		return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
	}
}


add_action( 'customize_controls_enqueue_scripts', 'webpress_do_control_inline_scripts', 100 );
/**
 * Add misc inline scripts to our controls.
 *
 * We don't want to add these to the controls themselves, as they will be repeated
 * each time the control is initialized.
 *
 * @since 1.0.0
 */
function webpress_do_control_inline_scripts() {
	wp_enqueue_script( 'webpress-customizer-controls', trailingslashit( get_template_directory_uri() ) . 'inc/customizer/controls/js/customizer-controls.js', array( 'customize-controls', 'jquery' ), WEBPRESS_VERSION, true );
	wp_localize_script( 'webpress-customizer-controls', 'webpress_defaults', webpress_get_defaults() );
	wp_localize_script( 'webpress-customizer-controls', 'webpress_color_defaults', webpress_get_color_defaults() );
	wp_localize_script( 'webpress-customizer-controls', 'webpress_typography_defaults', webpress_get_default_fonts() );
	wp_localize_script( 'webpress-customizer-controls', 'webpress_spacing_defaults', webpress_spacing_get_defaults() );
	wp_localize_script( 'webpress-customizer-controls', 'webpressLayoutPreviewUrls', webpress_get_layout_preview_urls() );

	/*
	 * This is a separate handle from webpress-customizer-controls above. They are
	 * two different scripts, and re-using a registered handle is a silent no-op --
	 * the React app would never load and the Colors/Typography panels render empty.
	 */
	wp_enqueue_script(
		'webpress-customizer-app',
		trailingslashit( get_template_directory_uri() ) . 'assets/dist/customizer.js',
		// We're including wp-color-picker for localized strings, nothing more.
		array( 'lodash', 'react', 'react-dom', 'wp-components', 'wp-element', 'wp-hooks', 'wp-i18n', 'wp-polyfill', 'jquery', 'customize-base', 'customize-controls', 'wp-color-picker' ),
		WEBPRESS_VERSION,
		true
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'webpress-customizer-app', 'webpress', get_template_directory() . '/languages' );
	}

	$color_palette = get_theme_support( 'editor-color-palette' );
	$colors = array();

	if ( is_array( $color_palette ) ) {
		foreach ( $color_palette as $key => $value ) {
			foreach ( $value as $color ) {
				$colors[] = array(
					'name' => $color['name'],
					'color' => $color['color'],
				);
			}
		}
	}

	wp_localize_script(
		'webpress-customizer-app',
		'webpressCustomizerControls',
		array(
			'palette' => $colors,
			'showGoogleFonts' => true,
			'colorPickerShouldShift' => function_exists( 'did_filter' ),
			'webpressFontLibrary' => array(),
			'webpressFontLibraryURI' => '',
		)
	);

	wp_enqueue_style(
		'webpress-customizer-app',
		trailingslashit( get_template_directory_uri() ) . 'assets/dist/style-customizer.css',
		array( 'wp-components' ),
		WEBPRESS_VERSION
	);

	$global_colors = webpress_get_global_colors();
	$global_colors_css = ':root {';

	if ( ! empty( $global_colors ) ) {
		foreach ( (array) $global_colors as $key => $data ) {
			$global_colors_css .= '--' . $data['slug'] . ':' . $data['color'] . ';';
		}
	}

	$global_colors_css .= '}';

	wp_add_inline_style( 'webpress-customizer-app', $global_colors_css );
}

if ( ! function_exists( 'webpress_customizer_live_preview' ) ) {
	add_action( 'customize_preview_init', 'webpress_customizer_live_preview', 100 );
	/**
	 * Add our live preview scripts
	 *
	 * @since 1.0.0
	 */
	function webpress_customizer_live_preview() {
		$spacing_settings = wp_parse_args(
			get_option( 'webpress_spacing_settings', array() ),
			webpress_spacing_get_defaults()
		);

		wp_enqueue_script( 'webpress-themecustomizer', trailingslashit( get_template_directory_uri() ) . 'inc/customizer/controls/js/customizer-live-preview.js', array( 'customize-preview' ), WEBPRESS_VERSION, true );

		wp_localize_script(
			'webpress-themecustomizer',
			'webpress_live_preview',
			array(
				'mobile' => webpress_get_media_query( 'mobile' ),
				'tablet' => webpress_get_media_query( 'tablet_only' ),
				'desktop' => webpress_get_media_query( 'desktop' ),
				'contentLeft' => absint( $spacing_settings['content_left'] ),
				'contentRight' => absint( $spacing_settings['content_right'] ),
				'isFlex' => true,
				'isRTL' => is_rtl(),
			)
		);

		wp_enqueue_script(
			'webpress-postMessage',
			trailingslashit( get_template_directory_uri() ) . 'inc/customizer/controls/js/postMessage.js',
			array( 'jquery', 'customize-preview', 'wp-hooks' ),
			WEBPRESS_VERSION,
			true
		);

		global $webpress_customize_fields;
		wp_localize_script( 'webpress-postMessage', 'webpressPostMessageFields', $webpress_customize_fields );
	}
}

/**
 * Check to see if we have a logo or not.
 *
 * Used as an active callback. Calling has_custom_logo creates a PHP notice for
 * multisite users.
 *
 * @since 1.0.0
 */
function webpress_has_custom_logo_callback() {
	if ( get_theme_mod( 'custom_logo' ) ) {
		return true;
	}

	return false;
}

/**
 * Get a URL to preview for each of the layout sections in the Customizer.
 *
 * @since 1.0.0
 *
 * @return array URLs keyed by Customizer section ID. Empty when there is nothing to show.
 */
function webpress_get_layout_preview_urls() {
	$latest_post = get_posts(
		array(
			'numberposts' => 1,
			'post_type' => 'post',
			'post_status' => 'publish',
			'fields' => 'ids',
		)
	);

	$page_id = 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_on_front' ) : 0;

	if ( ! $page_id ) {
		$pages = get_posts(
			array(
				'numberposts' => 1,
				'post_type' => 'page',
				'post_status' => 'publish',
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'exclude' => array( (int) get_option( 'page_for_posts' ) ),
				'fields' => 'ids',
			)
		);

		$page_id = $pages ? (int) $pages[0] : 0;
	}

	return array(
		'webpress_layout_pages' => $page_id ? get_permalink( $page_id ) : '',
		'webpress_layout_single' => $latest_post ? get_permalink( $latest_post[0] ) : '',
		'webpress_blog_section' => get_post_type_archive_link( 'post' ),
	);
}

/**
 * Check whether blog listings use the grid layout.
 *
 * Used as an active callback.
 *
 * @since 1.0.0
 */
function webpress_is_blog_grid_layout_callback() {
	return 'grid' === webpress_get_option( 'blog_post_layout' );
}

/**
 * Save our preset layout controls. These should always save to be "current".
 *
 * @since 1.0.0
 */
function webpress_sanitize_preset_layout() {
	return 'current';
}
