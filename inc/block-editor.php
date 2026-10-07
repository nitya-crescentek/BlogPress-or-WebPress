<?php
/**
 * Integrate WebPress with the WordPress block editor.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check what sidebar layout we're using.
 * We need this function as the post meta in webpress_get_layout() only runs
 * on is_singular()
 *
 * @since 1.0.0
 *
 * @param bool $meta Check for post meta.
 * @return string The saved sidebar layout.
 */
function webpress_get_block_editor_sidebar_layout( $meta = true ) {
	$layout = webpress_get_option( 'layout_setting' );

	if ( webpress_is_block_editor_single_post() ) {
		$layout = webpress_get_option( 'single_layout_setting' );
	}

	/**
	 * Filters the sidebar layout used by the block editor.
	 *
	 * Applied to the saved default before any per-post meta override, so a
	 * callback here changes the default while leaving explicit per-post choices
	 * intact.
	 *
	 * @since 1.0.0
	 *
	 * @param string $layout The sidebar layout slug.
	 * @return string The sidebar layout to use.
	 */
	$layout = apply_filters( 'webpress_sidebar_layout', $layout );

	if ( $meta ) {
		$layout_meta = get_post_meta( get_the_ID(), '_webpress-sidebar-layout-meta', true );

		if ( $layout_meta ) {
			$layout = $layout_meta;
		}
	}

	return $layout;
}


/**
 * Whether the post being edited uses the single post layout settings.
 *
 * On the front end every singular post type except pages counts as a single
 * post, so the editor follows the same rule.
 *
 * @since 1.0.0
 *
 * @return bool Whether the single post settings apply.
 */
function webpress_is_block_editor_single_post() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	$screen = get_current_screen();

	return is_object( $screen ) && $screen->post_type && 'page' !== $screen->post_type;
}

/**
 * Get the container layout for the post being edited.
 *
 * @since 1.0.0
 *
 * @param bool $meta Check for post meta.
 * @return string normal, narrow or full-width.
 */
function webpress_get_block_editor_container_layout( $meta = true ) {
	$layout = webpress_is_block_editor_single_post()
		? webpress_get_option( 'single_container_layout' )
		: webpress_get_option( 'page_container_layout' );

	if ( $meta ) {
		$layout_meta = get_post_meta( get_the_ID(), '_webpress-container-layout', true );

		if ( $layout_meta ) {
			$layout = $layout_meta;
		}

		if ( 'true' === get_post_meta( get_the_ID(), '_webpress-full-width-content', true ) ) {
			$layout = 'full-width';
		}
	}

	if ( ! array_key_exists( $layout, webpress_get_container_layouts() ) ) {
		$layout = 'normal';
	}

	return $layout;
}

/**
 * Get the content width for this post.
 *
 * @since 1.0.0
 */
function webpress_get_block_editor_content_width() {
	$layout = webpress_get_block_editor_sidebar_layout();
	$content_width = webpress_get_option( 'container_width' ) * ( webpress_get_content_area_width( $layout ) / 100 );

	if ( 'narrow' === webpress_get_block_editor_container_layout() ) {
		$content_width = min( absint( webpress_get_option( 'narrow_container_width' ) ), $content_width );
	}

	return $content_width;
}

add_filter( 'block_editor_settings_all', 'webpress_add_inline_block_editor_styles' );
/**
 * Add dynamic inline styles to the block editor content.
 *
 * @param array $editor_settings The existing editor settings.
 */
function webpress_add_inline_block_editor_styles( $editor_settings ) {
	$show_editor_styles = true;

	if ( $show_editor_styles ) {
		$google_fonts_uri = WebPress_Typography::get_google_fonts_uri();

		if ( $google_fonts_uri ) {
			// Need to use @import for now until this is ready: https://github.com/WordPress/gutenberg/pull/35950.
			$google_fonts_import = sprintf(
				'@import "%s";',
				$google_fonts_uri
			);

			$editor_settings['styles'][] = array( 'css' => $google_fonts_import );
		}

		$editor_settings['styles'][] = array( 'css' => wp_strip_all_tags( webpress_do_inline_block_editor_css() ) );

		$editor_settings['styles'][] = array( 'css' => wp_strip_all_tags( WebPress_Typography::get_css( 'core' ) ) );
	}

	return $editor_settings;
}

add_action( 'enqueue_block_editor_assets', 'webpress_enqueue_backend_block_editor_assets' );
/**
 * Add CSS to the admin side of the block editor.
 *
 * @since 1.0.0
 */
function webpress_enqueue_backend_block_editor_assets() {
	// Our global colors belong on every block editor screen.
	wp_register_style( 'webpress-block-editor', false, array(), true, true );
	wp_add_inline_style( 'webpress-block-editor', webpress_do_inline_block_editor_css( 'block-editor' ) );
	wp_enqueue_style( 'webpress-block-editor' );

	/*
	 * The content width script is post editor only. The widgets and site editor
	 * screens have no core/editor or core/edit-post store, so loading it there
	 * throws and the editor renders an error notice in its place.
	 */
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'post' !== $screen->base ) {
		return;
	}

	wp_enqueue_script(
		'webpress-block-editor',
		trailingslashit( get_template_directory_uri() ) . 'assets/dist/block-editor.js',
		array( 'wp-data', 'wp-dom-ready', 'wp-element', 'wp-plugins', 'wp-polyfill' ),
		WEBPRESS_VERSION,
		true
	);

	$color_settings = wp_parse_args(
		get_option( 'webpress_settings', array() ),
		webpress_get_color_defaults()
	);

	$spacing_settings = wp_parse_args(
		get_option( 'webpress_spacing_settings', array() ),
		webpress_spacing_get_defaults()
	);

	$text_color = webpress_get_option( 'text_color' );

	if ( $color_settings['content_text_color'] ) {
		$text_color = $color_settings['content_text_color'];
	}

	$sidebar_layout = get_post_meta( get_the_ID(), '_webpress-sidebar-layout-meta', true );
	$container_layout = get_post_meta( get_the_ID(), '_webpress-container-layout', true );
	$content_area_type = get_post_meta( get_the_ID(), '_webpress-full-width-content', true );
	$sidebar_width = (string) ( 100 - webpress_get_content_area_width( 'right-sidebar' ) );

	wp_localize_script(
		'webpress-block-editor',
		'webpressBlockEditor',
		array(
			'sidebarLayout' => $sidebar_layout ? $sidebar_layout : '',
			'defaultSidebarLayout' => webpress_get_block_editor_sidebar_layout( false ),
			'containerLayout' => $container_layout ? $container_layout : '',
			'defaultContainerLayout' => webpress_get_block_editor_container_layout( false ),
			'containerWidth' => webpress_get_option( 'container_width' ),
			'narrowContainerWidth' => webpress_get_option( 'narrow_container_width' ),
			'contentPaddingRight' => absint( $spacing_settings['content_right'] ) . 'px',
			'contentPaddingLeft' => absint( $spacing_settings['content_left'] ) . 'px',
			'rightSidebarWidth' => $sidebar_width,
			'leftSidebarWidth' => $sidebar_width,
			'text_color' => $text_color,
			'show_editor_styles' => true,
			'contentAreaType' => $content_area_type ? $content_area_type : '',
			'customContentWidth' => '',
		)
	);

	// Replaces the content width plugin in the compiled bundle above.
	wp_enqueue_script(
		'webpress-block-editor-layout',
		trailingslashit( get_template_directory_uri() ) . 'assets/js/block-editor-layout.js',
		array( 'webpress-block-editor', 'wp-data', 'wp-dom-ready', 'wp-plugins' ),
		WEBPRESS_VERSION,
		true
	);
}

/**
 * Write our CSS for the block editor.
 *
 * @since 1.0.0
 * @param string $for Define whether this CSS for the block content or the block editor.
 */
function webpress_do_inline_block_editor_css( $for = 'block-content' ) {
	$css = new WebPress_CSS();

	$css->set_selector( ':root' );

	$global_colors = webpress_get_global_colors();

	if ( ! empty( $global_colors ) ) {
		foreach ( (array) $global_colors as $key => $data ) {
			if ( ! empty( $data['slug'] ) && ! empty( $data['color'] ) ) {
				$css->add_property( '--' . $data['slug'], $data['color'] );
			}
		}

		foreach ( (array) $global_colors as $key => $data ) {
			if ( ! empty( $data['slug'] ) && ! empty( $data['color'] ) ) {
				$css->set_selector( '.has-' . $data['slug'] . '-color' );
				$css->add_property( 'color', 'var(--' . $data['slug'] . ')' );

				$css->set_selector( '.has-' . $data['slug'] . '-background-color' );
				$css->add_property( 'background-color', 'var(--' . $data['slug'] . ')' );
			}
		}
	}

	// If this CSS is for the editor only (not the block content), we can return here.
	if ( 'block-editor' === $for ) {
		return $css->css_output();
	}

	$color_settings = wp_parse_args(
		get_option( 'webpress_settings', array() ),
		webpress_get_color_defaults()
	);

	$content_width = webpress_get_block_editor_content_width();

	$spacing_settings = wp_parse_args(
		get_option( 'webpress_spacing_settings', array() ),
		webpress_spacing_get_defaults()
	);

	$content_width_calc = sprintf(
		'calc(%1$s - %2$s - %3$s)',
		absint( $content_width ) . 'px',
		absint( $spacing_settings['content_left'] ) . 'px',
		absint( $spacing_settings['content_right'] ) . 'px'
	);

	$content_area_type = get_post_meta( get_the_ID(), '_webpress-full-width-content', true );

	if ( 'full-width' === webpress_get_block_editor_container_layout() ) {
		$content_width_calc = '100%';
	} elseif ( 'contained' === $content_area_type ) {
		// Page builder mode has no content padding to take off.
		$content_width_calc = absint( $content_width ) . 'px';
	}

	$css->set_selector( 'body' );
	$css->add_property( '--content-width', $content_width_calc );

	$css->set_selector( 'body .wp-block' );
	$css->add_property( 'max-width', 'var(--content-width)' );

	$css->set_selector( '.wp-block[data-align="full"]' );
	$css->add_property( 'max-width', 'none' );

	$css->set_selector( '.wp-block[data-align="wide"]' );
	$css->add_property( 'max-width', absint( $content_width ), false, 'px' );

	$underline_links = webpress_get_option( 'underline_links' );

	if ( 'never' !== $underline_links ) {
		if ( 'always' === $underline_links ) {
			$css->set_selector( ':where(.wp-block a)' );
			$css->add_property( 'text-decoration', 'underline' );
		}

		if ( 'hover' === $underline_links ) {
			$css->set_selector( ':where(.wp-block a)' );
			$css->add_property( 'text-decoration', 'none' );

			$css->set_selector( ':where(.wp-block a:hover), :where(.wp-block a:focus)' );
			$css->add_property( 'text-decoration', 'underline' );
		}

		if ( 'not-hover' === $underline_links ) {
			$css->set_selector( ':where(.wp-block a)' );
			$css->add_property( 'text-decoration', 'underline' );

			$css->set_selector( ':where(.wp-block a:hover), :where(.wp-block a:focus)' );
			$css->add_property( 'text-decoration', 'none' );
		}

		$css->set_selector( 'a.button, .wp-block-button__link' );
		$css->add_property( 'text-decoration', 'none' );
	} else {
		$css->set_selector( '.wp-block a' );
		$css->add_property( 'text-decoration', 'none' );
	}

	$css->set_selector( '.wp-block-group__inner-container' );
	$css->add_property( 'max-width', absint( $content_width ), false, 'px' );
	$css->add_property( 'margin-left', 'auto' );
	$css->add_property( 'margin-right', 'auto' );
	$css->add_property( 'padding', webpress_padding_css( $spacing_settings['content_top'], $spacing_settings['content_right'], $spacing_settings['content_bottom'], $spacing_settings['content_left'] ) );

	$css->set_selector( 'a.button, a.button:visited, .wp-block-button__link:not(.has-background)' );
	$css->add_property( 'color', $color_settings['form_button_text_color'] );
	$css->add_property( 'background-color', $color_settings['form_button_background_color'] );
	$css->add_property( 'padding', '10px 20px' );
	$css->add_property( 'border', '0' );
	$css->add_property( 'border-radius', '0' );

	$css->set_selector( 'a.button:hover, a.button:active, a.button:focus, .wp-block-button__link:not(.has-background):active, .wp-block-button__link:not(.has-background):focus, .wp-block-button__link:not(.has-background):hover' );
	$css->add_property( 'color', $color_settings['form_button_text_color_hover'] );
	$css->add_property( 'background-color', $color_settings['form_button_background_color_hover'] );

	$css->set_selector( 'body' );

	if ( $color_settings['content_text_color'] ) {
		$css->add_property( 'color', $color_settings['content_text_color'] );
	} else {
		$css->add_property( 'color', webpress_get_option( 'text_color' ) );
	}

	$css->set_selector( '.content-title-visibility' );

	if ( $color_settings['content_text_color'] ) {
		$css->add_property( 'color', $color_settings['content_text_color'] );
	} else {
		$css->add_property( 'color', webpress_get_option( 'text_color' ) );
	}

	$css->set_selector( 'h1' );

	$css->add_property( 'color', $color_settings['h1_color'] );

	if ( $color_settings['content_title_color'] ) {
		$css->set_selector( '.edit-post-visual-editor__post-title-wrapper h1' );
		$css->add_property( 'color', $color_settings['content_title_color'] );
	}

	$css->set_selector( 'h2' );

	$css->add_property( 'color', $color_settings['h2_color'] );

	$css->set_selector( 'h3' );

	$css->add_property( 'color', $color_settings['h3_color'] );

	$css->set_selector( 'h4' );

	$css->add_property( 'color', $color_settings['h4_color'] );

	$css->set_selector( 'h5' );

	$css->add_property( 'color', $color_settings['h5_color'] );

	$css->set_selector( 'h6' );

	$css->add_property( 'color', $color_settings['h6_color'] );

	$css->set_selector( 'a.button, .block-editor-block-list__layout .wp-block-button .wp-block-button__link' );

	if ( version_compare( $GLOBALS['wp_version'], '5.7-alpha.1', '>' ) ) {
		$css->set_selector( '.block-editor__container .edit-post-visual-editor' );
		$css->add_property( 'background-color', webpress_get_option( 'background_color' ) );

		$css->set_selector( 'body' );

		if ( $color_settings['content_background_color'] ) {
			$css->add_property( 'background-color', $color_settings['content_background_color'] );
		} else {
			$css->add_property( 'background-color', webpress_get_option( 'background_color' ) );
		}
	} else {
		$css->set_selector( 'body' );
		$css->add_property( 'background-color', webpress_get_option( 'background_color' ) );

		if ( $color_settings['content_background_color'] ) {
			$body_background = webpress_get_option( 'background_color' );
			$content_background = $color_settings['content_background_color'];

			$css->add_property( 'background', 'linear-gradient(' . $content_background . ',' . $content_background . '), linear-gradient(' . $body_background . ',' . $body_background . ')' );
		}
	}

	$css->set_selector( 'a' );

	if ( $color_settings['content_link_color'] ) {
		$css->add_property( 'color', $color_settings['content_link_color'] );
	} else {
		$css->add_property( 'color', webpress_get_option( 'link_color' ) );
	}

	$css->set_selector( 'a:hover, a:focus, a:active' );

	if ( $color_settings['content_link_hover_color'] ) {
		$css->add_property( 'color', $color_settings['content_link_hover_color'] );
	} else {
		$css->add_property( 'color', webpress_get_option( 'link_color_hover' ) );
	}

	return $css->css_output();
}

add_filter( 'wp_theme_json_data_theme', 'webpress_sync_theme_json_with_customizer' );
/**
 * Keep theme.json in step with the Customizer.
 *
 * theme.json is a static file, but the global colours and container width are
 * user-editable in the Customizer. Without this the block editor would keep
 * showing the shipped defaults after a user changed them.
 *
 * The Customizer stays the source of truth; theme.json supplies the defaults
 * that this overlays.
 *
 * @since 1.0.0
 *
 * @param WP_Theme_JSON_Data $theme_json The theme.json data object.
 * @return WP_Theme_JSON_Data The updated data object.
 */
function webpress_sync_theme_json_with_customizer( $theme_json ) {
	$new_data = array(
		'version'  => 3,
		'settings' => array(),
	);

	$global_colors = webpress_get_option( 'global_colors' );

	if ( ! empty( $global_colors ) && is_array( $global_colors ) ) {
		$palette = array();

		foreach ( $global_colors as $color ) {
			if ( empty( $color['slug'] ) || empty( $color['color'] ) ) {
				continue;
			}

			$palette[] = array(
				'name'  => ! empty( $color['name'] ) ? $color['name'] : $color['slug'],
				'slug'  => $color['slug'],
				'color' => $color['color'],
			);
		}

		if ( $palette ) {
			$new_data['settings']['color']['palette'] = $palette;
		}
	}

	$container_width = absint( webpress_get_option( 'container_width' ) );

	if ( $container_width ) {
		$new_data['settings']['layout'] = array(
			'contentSize' => $container_width . 'px',
			'wideSize'    => $container_width . 'px',
		);
	}

	if ( empty( $new_data['settings'] ) ) {
		return $theme_json;
	}

	return $theme_json->update_with( $new_data );
}
