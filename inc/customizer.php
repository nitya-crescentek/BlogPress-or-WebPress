<?php
/**
 * Builds our Customizer controls.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'customize_register', 'webpress_set_customizer_helpers', 1 );
/**
 * Set up helpers early so they're always available.
 * Other modules might need access to them at some point.
 *
 * @since 1.0.0
 */
function webpress_set_customizer_helpers() {
	require_once trailingslashit( get_template_directory() ) . 'inc/customizer/customizer-helpers.php';
}

if ( ! function_exists( 'webpress_customize_register' ) ) {
	add_action( 'customize_register', 'webpress_customize_register', 20 );
	/**
	 * Add our base options to the Customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	function webpress_customize_register( $wp_customize ) {
		if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
			return;
		}

		$defaults = webpress_get_defaults();
		$color_defaults = webpress_get_color_defaults();
		$typography_defaults = webpress_get_default_fonts();

		if ( $wp_customize->get_control( 'blogdescription' ) ) {
			$wp_customize->get_control( 'blogdescription' )->priority = 3;
			$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
		}

		if ( $wp_customize->get_control( 'blogname' ) ) {
			$wp_customize->get_control( 'blogname' )->priority = 1;
			$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
		}

		if ( $wp_customize->get_control( 'custom_logo' ) ) {
			$wp_customize->get_setting( 'custom_logo' )->transport = 'refresh';
		}

		if ( method_exists( $wp_customize, 'register_control_type' ) ) {
			$wp_customize->register_control_type( 'WebPress_Range_Slider_Control' );
		}

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'blogname',
				array(
					'selector' => '.main-title a',
					'render_callback' => 'webpress_customize_partial_blogname',
				)
			);

			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector' => '.site-description',
					'render_callback' => 'webpress_customize_partial_blogdescription',
				)
			);
		}

		$wp_customize->add_setting(
			'webpress_settings[hide_title]',
			array(
				'default' => $defaults['hide_title'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[hide_title]',
			array(
				'type' => 'checkbox',
				'label' => __( 'Hide site title', 'webpress' ),
				'section' => 'title_tagline',
				'priority' => 2,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[hide_tagline]',
			array(
				'default' => $defaults['hide_tagline'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[hide_tagline]',
			array(
				'type' => 'checkbox',
				'label' => __( 'Hide site tagline', 'webpress' ),
				'section' => 'title_tagline',
				'priority' => 4,
			)
		);

		if ( ! function_exists( 'the_custom_logo' ) ) {
			$wp_customize->add_setting(
				'webpress_settings[logo]',
				array(
					'default' => $defaults['logo'],
					'type' => 'option',
					'sanitize_callback' => 'esc_url_raw',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					'webpress_settings[logo]',
					array(
						'label' => __( 'Logo', 'webpress' ),
						'section' => 'title_tagline',
						'settings' => 'webpress_settings[logo]',
					)
				)
			);
		}

		$wp_customize->add_setting(
			'webpress_settings[retina_logo]',
			array(
				'default' => $defaults['retina_logo'],
				'type' => 'option',
				'sanitize_callback' => 'esc_url_raw',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'webpress_settings[retina_logo]',
				array(
					'label' => __( 'Retina Logo', 'webpress' ),
					'section' => 'title_tagline',
					'settings' => 'webpress_settings[retina_logo]',
					'active_callback' => 'webpress_has_custom_logo_callback',
				)
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[logo_width]',
			array(
				'default' => $defaults['logo_width'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_empty_absint',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WebPress_Range_Slider_Control(
				$wp_customize,
				'webpress_settings[logo_width]',
				array(
					'label' => __( 'Logo Width', 'webpress' ),
					'section' => 'title_tagline',
					'settings' => array(
						'desktop' => 'webpress_settings[logo_width]',
					),
					'choices' => array(
						'desktop' => array(
							'min' => 20,
							'max' => 1200,
							'step' => 10,
							'edit' => true,
							'unit' => 'px',
						),
					),
					'active_callback' => 'webpress_has_custom_logo_callback',
				)
			)
		);

		$wp_customize->add_section(
			'webpress_colors_section',
			array(
				'title' => esc_attr__( 'Colors', 'webpress' ),
				'priority' => 30,
			)
		);

		WebPress_Customize_Field::add_title(
			'webpress_color_manager_title',
			array(
				'section' => 'webpress_colors_section',
				'title' => __( 'Global Colors', 'webpress' ),
			)
		);

		WebPress_Customize_Field::add_field(
			'webpress_settings[global_colors]',
			'WebPress_Customize_React_Control',
			array(
				'default' => $defaults['global_colors'],
				'sanitize_callback' => function( $colors ) {
					if ( ! is_array( $colors ) ) {
						return;
					}

					$new_settings = array();

					foreach ( (array) $colors as $key => $data ) {
						if ( empty( $data['slug'] ) || empty( $data['color'] ) ) {
							continue;
						}

						$slug = preg_replace( '/[^a-z0-9-\s]+/i', '', $data['slug'] );
						$slug = strtolower( $slug );
						$new_settings[ $key ]['name'] = sanitize_text_field( $slug );
						$new_settings[ $key ]['slug'] = sanitize_text_field( $slug );
						$new_settings[ $key ]['color'] = webpress_sanitize_rgba_color( $data['color'] );
					}

					// Reset array keys starting at 0.
					$new_settings = array_values( $new_settings );

					return $new_settings;
				},
				'transport' => 'postMessage',
			),
			array(
				'type' => 'webpress-color-manager-control',
				'label' => __( 'Choose Color', 'webpress' ),
				'section' => 'webpress_colors_section',
				'choices' => array(
					'alpha' => true,
					'showPalette' => false,
					'showReset' => false,
					'showVarName' => true,
				),
			)
		);

		$fields_dir = trailingslashit( get_template_directory() ) . 'inc/customizer/fields';
		require_once $fields_dir . '/body.php';
		require_once $fields_dir . '/top-bar.php';
		require_once $fields_dir . '/header.php';
		require_once $fields_dir . '/primary-navigation.php';

		require_once $fields_dir . '/buttons.php';
		require_once $fields_dir . '/content.php';
		require_once $fields_dir . '/forms.php';
		require_once $fields_dir . '/sidebar-widgets.php';
		require_once $fields_dir . '/footer-widgets.php';
		require_once $fields_dir . '/footer-bar.php';
		require_once $fields_dir . '/back-to-top.php';
		require_once $fields_dir . '/search-modal.php';

		$wp_customize->add_section(
			'webpress_typography_section',
			array(
				'title' => esc_attr__( 'Typography', 'webpress' ),
				'priority' => 35,
				'active_callback' => function() {
					return true;
				},
			)
		);

		WebPress_Customize_Field::add_title(
			'webpress_font_manager_title',
			array(
				'section' => 'webpress_typography_section',
				'title' => __( 'Font Manager', 'webpress' ),
			)
		);

		WebPress_Customize_Field::add_field(
			'webpress_settings[font_manager]',
			'WebPress_Customize_React_Control',
			array(
				'default' => $defaults['font_manager'],
				'sanitize_callback' => function( $fonts ) {
					if ( ! is_array( $fonts ) ) {
						return;
					}

					$options = array(
						'fontFamily' => 'sanitize_text_field',
						'googleFont' => 'rest_sanitize_boolean',
						'googleFontApi' => 'absint',
						'googleFontCategory' => 'sanitize_text_field',
						'googleFontVariants' => 'sanitize_text_field',
					);

					$new_settings = array();

					foreach ( (array) $fonts as $key => $data ) {
						if ( empty( $data['fontFamily'] ) ) {
							continue;
						}

						foreach ( $options as $option => $sanitize ) {
							if ( array_key_exists( $option, $data ) ) {
								$new_settings[ $key ][ $option ] = $sanitize( $data[ $option ] );
							}
						}
					}

					// Reset array keys starting at 0.
					$new_settings = array_values( $new_settings );

					return $new_settings;
				},
				'transport' => 'refresh',
			),
			array(
				'type' => 'webpress-font-manager-control',
				'label' => __( 'Choose Font', 'webpress' ),
				'section' => 'webpress_typography_section',
			)
		);

		WebPress_Customize_Field::add_field(
			'webpress_settings[google_font_display]',
			'',
			array(
				'default' => $defaults['google_font_display'],
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'refresh',
			),
			array(
				'type' => 'select',
				'label' => __( 'Google font-display', 'webpress' ),
				'description' => sprintf(
					'<a href="%s" target="_blank" rel="noreferrer noopener">%s</a>',
					'https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display',
					esc_html__( 'Learn about font-display', 'webpress' )
				),
				'section' => 'webpress_typography_section',
				'choices' => array(
					'auto' => esc_html__( 'Auto', 'webpress' ),
					'block' => esc_html__( 'Block', 'webpress' ),
					'swap' => esc_html__( 'Swap', 'webpress' ),
					'fallback' => esc_html__( 'Fallback', 'webpress' ),
					'optional' => esc_html__( 'Optional', 'webpress' ),
				),
				'active_callback' => function() {
					$font_manager = webpress_get_option( 'font_manager' );
					$has_google_font = false;

					foreach ( (array) $font_manager as $key => $data ) {
						if ( ! empty( $data['googleFont'] ) ) {
							$has_google_font = true;
							break;
						}
					}

					return $has_google_font;
				},
			)
		);

		WebPress_Customize_Field::add_title(
			'webpress_typography_manager_title',
			array(
				'section' => 'webpress_typography_section',
				'title' => __( 'Typography Manager', 'webpress' ),
			)
		);

		WebPress_Customize_Field::add_field(
			'webpress_settings[typography]',
			'WebPress_Customize_React_Control',
			array(
				'default' => $defaults['typography'],
				'sanitize_callback' => function( $settings ) {
					if ( ! is_array( $settings ) ) {
						return;
					}

					$options = array(
						'selector' => 'sanitize_text_field',
						'customSelector' => 'sanitize_text_field',
						'fontFamily' => 'sanitize_text_field',
						'fontWeight' => 'sanitize_text_field',
						'textTransform' => 'sanitize_text_field',
						'textDecoration' => 'sanitize_text_field',
						'fontStyle' => 'sanitize_text_field',
						'fontSize' => 'sanitize_text_field',
						'fontSizeTablet' => 'sanitize_text_field',
						'fontSizeMobile' => 'sanitize_text_field',
						'fontSizeUnit' => 'sanitize_text_field',
						'lineHeight' => 'sanitize_text_field',
						'lineHeightTablet' => 'sanitize_text_field',
						'lineHeightMobile' => 'sanitize_text_field',
						'lineHeightUnit' => 'sanitize_text_field',
						'letterSpacing' => 'sanitize_text_field',
						'letterSpacingTablet' => 'sanitize_text_field',
						'letterSpacingMobile' => 'sanitize_text_field',
						'letterSpacingUnit' => 'sanitize_text_field',
						'marginBottom' => 'sanitize_text_field',
						'marginBottomTablet' => 'sanitize_text_field',
						'marginBottomMobile' => 'sanitize_text_field',
						'marginBottomUnit' => 'sanitize_text_field',
						'module' => 'sanitize_text_field',
						'group' => 'sanitize_text_field',
					);

					$new_settings = array();

					foreach ( (array) $settings as $key => $data ) {
						if ( empty( $data['selector'] ) ) {
							continue;
						}

						foreach ( $options as $option => $sanitize ) {
							if ( array_key_exists( $option, $data ) ) {
								$new_settings[ $key ][ $option ] = $sanitize( $data[ $option ] );
							}
						}
					}

					// Reset array keys starting at 0.
					$new_settings = array_values( $new_settings );

					return $new_settings;
				},
				'transport' => 'refresh',
			),
			array(
				'type' => 'webpress-typography-control',
				'label' => __( 'Configure', 'webpress' ),
				'section' => 'webpress_typography_section',
			)
		);

		if ( ! $wp_customize->get_panel( 'webpress_layout_panel' ) ) {
			$wp_customize->add_panel(
				'webpress_layout_panel',
				array(
					'priority' => 25,
					'title' => __( 'Layout', 'webpress' ),
				)
			);
		}

		$wp_customize->add_section(
			'webpress_layout_container',
			array(
				'title' => __( 'Container', 'webpress' ),
				'priority' => 10,
				'panel' => 'webpress_layout_panel',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[container_width]',
			array(
				'default' => $defaults['container_width'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_integer',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WebPress_Range_Slider_Control(
				$wp_customize,
				'webpress_settings[container_width]',
				array(
					'type' => 'webpress-range-slider',
					'label' => __( 'Container Width', 'webpress' ),
					'section' => 'webpress_layout_container',
					'settings' => array(
						'desktop' => 'webpress_settings[container_width]',
					),
					'choices' => array(
						'desktop' => array(
							'min' => 700,
							'max' => 2000,
							'step' => 5,
							'edit' => true,
							'unit' => 'px',
						),
					),
					'priority' => 0,
				)
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[narrow_container_width]',
			array(
				'default' => $defaults['narrow_container_width'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_integer',
			)
		);

		$wp_customize->add_control(
			new WebPress_Range_Slider_Control(
				$wp_customize,
				'webpress_settings[narrow_container_width]',
				array(
					'type' => 'webpress-range-slider',
					'label' => __( 'Narrow Content Width', 'webpress' ),
					'description' => __( 'The width of the content column on anything set to the Narrow container layout.', 'webpress' ),
					'section' => 'webpress_layout_container',
					'settings' => array(
						'desktop' => 'webpress_settings[narrow_container_width]',
					),
					'choices' => array(
						'desktop' => array(
							'min' => 500,
							'max' => 1200,
							'step' => 10,
							'edit' => true,
							'unit' => 'px',
						),
					),
					'priority' => 1,
				)
			)
		);

		$wp_customize->add_section(
			'webpress_top_bar',
			array(
				'title' => __( 'Top Bar', 'webpress' ),
				'priority' => 15,
				'panel' => 'webpress_layout_panel',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[top_bar_width]',
			array(
				'default' => $defaults['top_bar_width'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[top_bar_width]',
			array(
				'type' => 'select',
				'label' => __( 'Top Bar Width', 'webpress' ),
				'section' => 'webpress_top_bar',
				'choices' => array(
					'full' => __( 'Full', 'webpress' ),
					'contained' => __( 'Contained', 'webpress' ),
				),
				'settings' => 'webpress_settings[top_bar_width]',
				'priority' => 5,
				'active_callback' => 'webpress_is_top_bar_active',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[top_bar_inner_width]',
			array(
				'default' => $defaults['top_bar_inner_width'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[top_bar_inner_width]',
			array(
				'type' => 'select',
				'label' => __( 'Top Bar Inner Width', 'webpress' ),
				'section' => 'webpress_top_bar',
				'choices' => array(
					'full' => __( 'Full', 'webpress' ),
					'contained' => __( 'Contained', 'webpress' ),
				),
				'settings' => 'webpress_settings[top_bar_inner_width]',
				'priority' => 10,
				'active_callback' => 'webpress_is_top_bar_active',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[top_bar_alignment]',
			array(
				'default' => $defaults['top_bar_alignment'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[top_bar_alignment]',
			array(
				'type' => 'select',
				'label' => __( 'Top Bar Alignment', 'webpress' ),
				'section' => 'webpress_top_bar',
				'choices' => array(
					'left' => __( 'Left', 'webpress' ),
					'center' => __( 'Center', 'webpress' ),
					'right' => __( 'Right', 'webpress' ),
				),
				'settings' => 'webpress_settings[top_bar_alignment]',
				'priority' => 15,
				'active_callback' => 'webpress_is_top_bar_active',
			)
		);

		$wp_customize->add_section(
			'webpress_layout_header',
			array(
				'title' => __( 'Header', 'webpress' ),
				'priority' => 20,
				'panel' => 'webpress_layout_panel',
			)
		);

		$wp_customize->add_setting(
			'webpress_header_helper',
			array(
				'default' => 'current',
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_preset_layout',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_header_helper',
			array(
				'type' => 'select',
				'label' => __( 'Header Presets', 'webpress' ),
				'section' => 'webpress_layout_header',
				'choices' => array(
					'current' => __( 'Current', 'webpress' ),
					'default' => __( 'Default', 'webpress' ),
					'classic' => __( 'Classic', 'webpress' ),
					'nav-before' => __( 'Navigation Before', 'webpress' ),
					'nav-after' => __( 'Navigation After', 'webpress' ),
					'nav-before-centered' => __( 'Navigation Before - Centered', 'webpress' ),
					'nav-after-centered' => __( 'Navigation After - Centered', 'webpress' ),
					'nav-left' => __( 'Navigation Left', 'webpress' ),
				),
				'settings' => 'webpress_header_helper',
				'priority' => 4,
			)
		);

		if ( ! $wp_customize->get_setting( 'webpress_settings[site_title_font_size]' ) ) {
			$typography_defaults = webpress_get_default_fonts();

			$wp_customize->add_setting(
				'webpress_settings[site_title_font_size]',
				array(
					'default' => $typography_defaults['site_title_font_size'],
					'type' => 'option',
					'sanitize_callback' => 'absint',
					'transport' => 'postMessage',
				)
			);
		}

		if ( ! $wp_customize->get_setting( 'webpress_spacing_settings[header_top]' ) ) {
			$spacing_defaults = webpress_spacing_get_defaults();

			$wp_customize->add_setting(
				'webpress_spacing_settings[header_top]',
				array(
					'default' => $spacing_defaults['header_top'],
					'type' => 'option',
					'sanitize_callback' => 'absint',
					'transport' => 'postMessage',
				)
			);
		}

		if ( ! $wp_customize->get_setting( 'webpress_spacing_settings[header_bottom]' ) ) {
			$spacing_defaults = webpress_spacing_get_defaults();

			$wp_customize->add_setting(
				'webpress_spacing_settings[header_bottom]',
				array(
					'default' => $spacing_defaults['header_bottom'],
					'type' => 'option',
					'sanitize_callback' => 'absint',
					'transport' => 'postMessage',
				)
			);
		}

		$wp_customize->add_setting(
			'webpress_settings[header_layout_setting]',
			array(
				'default' => $defaults['header_layout_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[header_layout_setting]',
			array(
				'type' => 'select',
				'label' => __( 'Header Width', 'webpress' ),
				'section' => 'webpress_layout_header',
				'choices' => array(
					'fluid-header' => __( 'Full', 'webpress' ),
					'contained-header' => __( 'Contained', 'webpress' ),
				),
				'settings' => 'webpress_settings[header_layout_setting]',
				'priority' => 5,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[header_inner_width]',
			array(
				'default' => $defaults['header_inner_width'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[header_inner_width]',
			array(
				'type' => 'select',
				'label' => __( 'Inner Header Width', 'webpress' ),
				'section' => 'webpress_layout_header',
				'choices' => array(
					'contained' => __( 'Contained', 'webpress' ),
					'full-width' => __( 'Full', 'webpress' ),
				),
				'settings' => 'webpress_settings[header_inner_width]',
				'priority' => 6,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[header_alignment_setting]',
			array(
				'default' => $defaults['header_alignment_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[header_alignment_setting]',
			array(
				'type' => 'select',
				'label' => __( 'Header Alignment', 'webpress' ),
				'section' => 'webpress_layout_header',
				'choices' => array(
					'left' => __( 'Left', 'webpress' ),
					'center' => __( 'Center', 'webpress' ),
					'right' => __( 'Right', 'webpress' ),
				),
				'settings' => 'webpress_settings[header_alignment_setting]',
				'priority' => 10,
			)
		);

		$wp_customize->add_section(
			'webpress_layout_navigation',
			array(
				'title' => __( 'Primary Navigation', 'webpress' ),
				'priority' => 30,
				'panel' => 'webpress_layout_panel',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[nav_layout_setting]',
			array(
				'default' => $defaults['nav_layout_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[nav_layout_setting]',
			array(
				'type' => 'select',
				'label' => __( 'Navigation Width', 'webpress' ),
				'section' => 'webpress_layout_navigation',
				'choices' => array(
					'fluid-nav' => __( 'Full', 'webpress' ),
					'contained-nav' => __( 'Contained', 'webpress' ),
				),
				'settings' => 'webpress_settings[nav_layout_setting]',
				'priority' => 15,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[nav_inner_width]',
			array(
				'default' => $defaults['nav_inner_width'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[nav_inner_width]',
			array(
				'type' => 'select',
				'label' => __( 'Inner Navigation Width', 'webpress' ),
				'section' => 'webpress_layout_navigation',
				'choices' => array(
					'contained' => __( 'Contained', 'webpress' ),
					'full-width' => __( 'Full', 'webpress' ),
				),
				'settings' => 'webpress_settings[nav_inner_width]',
				'priority' => 16,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[nav_alignment_setting]',
			array(
				'default' => $defaults['nav_alignment_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[nav_alignment_setting]',
			array(
				'type' => 'select',
				'label' => __( 'Navigation Alignment', 'webpress' ),
				'section' => 'webpress_layout_navigation',
				'choices' => array(
					'left' => __( 'Left', 'webpress' ),
					'center' => __( 'Center', 'webpress' ),
					'right' => __( 'Right', 'webpress' ),
				),
				'settings' => 'webpress_settings[nav_alignment_setting]',
				'priority' => 20,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[nav_position_setting]',
			array(
				'default' => $defaults['nav_position_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'refresh',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[nav_position_setting]',
			array(
				'type' => 'select',
				'label' => __( 'Navigation Location', 'webpress' ),
				'section' => 'webpress_layout_navigation',
				'choices' => array(
					'nav-below-header' => __( 'Below Header', 'webpress' ),
					'nav-above-header' => __( 'Above Header', 'webpress' ),
					'nav-float-right' => __( 'Float Right', 'webpress' ),
					'nav-float-left' => __( 'Float Left', 'webpress' ),
					'nav-left-sidebar' => __( 'Left Sidebar', 'webpress' ),
					'nav-right-sidebar' => __( 'Right Sidebar', 'webpress' ),
					'' => __( 'No Navigation', 'webpress' ),
				),
				'settings' => 'webpress_settings[nav_position_setting]',
				'priority' => 22,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[nav_drop_point]',
			array(
				'default' => $defaults['nav_drop_point'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_empty_absint',
			)
		);

		$wp_customize->add_control(
			new WebPress_Range_Slider_Control(
				$wp_customize,
				'webpress_settings[nav_drop_point]',
				array(
					'label' => __( 'Navigation Drop Point', 'webpress' ),
					'sub_description' => __( 'The width when the navigation ceases to float and drops below your logo.', 'webpress' ),
					'section' => 'webpress_layout_navigation',
					'settings' => array(
						'desktop' => 'webpress_settings[nav_drop_point]',
					),
					'choices' => array(
						'desktop' => array(
							'min' => 500,
							'max' => 2000,
							'step' => 10,
							'edit' => true,
							'unit' => 'px',
						),
					),
					'priority' => 22,
				)
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[nav_dropdown_type]',
			array(
				'default' => $defaults['nav_dropdown_type'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[nav_dropdown_type]',
			array(
				'type' => 'select',
				'label' => __( 'Navigation Dropdown', 'webpress' ),
				'section' => 'webpress_layout_navigation',
				'choices' => array(
					'hover' => __( 'Hover', 'webpress' ),
					'click' => __( 'Click - Menu Item', 'webpress' ),
					'click-arrow' => __( 'Click - Arrow', 'webpress' ),
				),
				'settings' => 'webpress_settings[nav_dropdown_type]',
				'priority' => 22,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[nav_dropdown_direction]',
			array(
				'default' => $defaults['nav_dropdown_direction'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[nav_dropdown_direction]',
			array(
				'type' => 'select',
				'label' => __( 'Dropdown Direction', 'webpress' ),
				'section' => 'webpress_layout_navigation',
				'choices' => array(
					'right' => __( 'Right', 'webpress' ),
					'left' => __( 'Left', 'webpress' ),
				),
				'settings' => 'webpress_settings[nav_dropdown_direction]',
				'priority' => 22,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[nav_search]',
			array(
				'default' => $defaults['nav_search'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[nav_search]',
			array(
				'type' => 'select',
				'label' => __( 'Navigation Search', 'webpress' ),
				'section' => 'webpress_layout_navigation',
				'choices' => array(
					'enable' => __( 'Enable', 'webpress' ),
					'disable' => __( 'Disable', 'webpress' ),
				),
				'settings' => 'webpress_settings[nav_search]',
				'priority' => 23,
				'active_callback' => function() {
					return 'enable' === webpress_get_option( 'nav_search' );
				},
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[nav_search_modal]',
			array(
				'default' => $defaults['nav_search_modal'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[nav_search_modal]',
			array(
				'type' => 'checkbox',
				'label' => esc_html__( 'Enable navigation search modal', 'webpress' ),
				'section' => 'webpress_layout_navigation',
				'priority' => 23,
				'active_callback' => function() {
					return 'disable' === webpress_get_option( 'nav_search' );
				},
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[content_layout_setting]',
			array(
				'default' => $defaults['content_layout_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[content_layout_setting]',
			array(
				'type' => 'select',
				'label' => __( 'Content Layout', 'webpress' ),
				'section' => 'webpress_layout_container',
				'choices' => array(
					'separate-containers' => __( 'Separate Containers', 'webpress' ),
					'one-container' => __( 'One Container', 'webpress' ),
				),
				'settings' => 'webpress_settings[content_layout_setting]',
				'priority' => 25,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[container_alignment]',
			array(
				'default' => $defaults['container_alignment'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[container_alignment]',
			array(
				'type' => 'select',
				'label' => __( 'Container Alignment', 'webpress' ),
				'section' => 'webpress_layout_container',
				'choices' => array(
					'boxes' => __( 'Boxes', 'webpress' ),
					'text' => __( 'Text', 'webpress' ),
				),
				'settings' => 'webpress_settings[container_alignment]',
				'priority' => 30,
			)
		);

		$sidebar_layouts = array(
			'right-sidebar' => __( 'Right Sidebar', 'webpress' ),
			'left-sidebar' => __( 'Left Sidebar', 'webpress' ),
			'no-sidebar' => __( 'No Sidebars', 'webpress' ),
			'both-sidebars' => __( 'Both Sidebars', 'webpress' ),
			'both-left' => __( 'Both Sidebars on Left', 'webpress' ),
			'both-right' => __( 'Both Sidebars on Right', 'webpress' ),
		);

		$container_layouts = webpress_get_container_layouts();

		$container_layout_description = __( 'Narrow keeps the content column at the narrow width set under Layout > Container. Full Width stretches the content across the screen.', 'webpress' );

		$wp_customize->add_section(
			'webpress_layout_pages',
			array(
				'title' => __( 'Pages', 'webpress' ),
				'description' => __( 'The layout for pages. Each page can override it from the Layout box in the editor.', 'webpress' ),
				'priority' => 40,
				'panel' => 'webpress_layout_panel',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[page_container_layout]',
			array(
				'default' => $defaults['page_container_layout'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			new WebPress_Customize_Radio_Image_Control(
				$wp_customize,
				'webpress_settings[page_container_layout]',
				array(
					'label' => __( 'Container Layout', 'webpress' ),
					'description' => $container_layout_description,
					'section' => 'webpress_layout_pages',
					'choices' => $container_layouts,
					'settings' => 'webpress_settings[page_container_layout]',
					'priority' => 10,
				)
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[layout_setting]',
			array(
				'default' => $defaults['layout_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			new WebPress_Customize_Radio_Image_Control(
				$wp_customize,
				'webpress_settings[layout_setting]',
				array(
					'label' => __( 'Sidebar Layout', 'webpress' ),
					'description' => __( 'Also used for the 404 page.', 'webpress' ),
					'section' => 'webpress_layout_pages',
					'choices' => $sidebar_layouts,
					'settings' => 'webpress_settings[layout_setting]',
					'priority' => 20,
				)
			)
		);

		$wp_customize->add_section(
			'webpress_layout_single',
			array(
				'title' => __( 'Single Posts', 'webpress' ),
				'description' => __( 'The layout for single posts and other single post types. Each post can override it from the Layout box in the editor.', 'webpress' ),
				'priority' => 42,
				'panel' => 'webpress_layout_panel',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[single_container_layout]',
			array(
				'default' => $defaults['single_container_layout'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			new WebPress_Customize_Radio_Image_Control(
				$wp_customize,
				'webpress_settings[single_container_layout]',
				array(
					'label' => __( 'Container Layout', 'webpress' ),
					'description' => $container_layout_description,
					'section' => 'webpress_layout_single',
					'choices' => $container_layouts,
					'settings' => 'webpress_settings[single_container_layout]',
					'priority' => 10,
				)
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[single_layout_setting]',
			array(
				'default' => $defaults['single_layout_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			new WebPress_Customize_Radio_Image_Control(
				$wp_customize,
				'webpress_settings[single_layout_setting]',
				array(
					'label' => __( 'Sidebar Layout', 'webpress' ),
					'section' => 'webpress_layout_single',
					'choices' => $sidebar_layouts,
					'settings' => 'webpress_settings[single_layout_setting]',
					'priority' => 20,
				)
			)
		);

		$wp_customize->add_section(
			'webpress_layout_footer',
			array(
				'title' => __( 'Footer', 'webpress' ),
				'priority' => 50,
				'panel' => 'webpress_layout_panel',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[footer_layout_setting]',
			array(
				'default' => $defaults['footer_layout_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[footer_layout_setting]',
			array(
				'type' => 'select',
				'label' => __( 'Footer Width', 'webpress' ),
				'section' => 'webpress_layout_footer',
				'choices' => array(
					'fluid-footer' => __( 'Full', 'webpress' ),
					'contained-footer' => __( 'Contained', 'webpress' ),
				),
				'settings' => 'webpress_settings[footer_layout_setting]',
				'priority' => 40,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[footer_inner_width]',
			array(
				'default' => $defaults['footer_inner_width'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[footer_inner_width]',
			array(
				'type' => 'select',
				'label' => __( 'Inner Footer Width', 'webpress' ),
				'section' => 'webpress_layout_footer',
				'choices' => array(
					'contained' => __( 'Contained', 'webpress' ),
					'full-width' => __( 'Full', 'webpress' ),
				),
				'settings' => 'webpress_settings[footer_inner_width]',
				'priority' => 41,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[footer_widget_setting]',
			array(
				'default' => $defaults['footer_widget_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[footer_widget_setting]',
			array(
				'type' => 'select',
				'label' => __( 'Footer Widgets', 'webpress' ),
				'section' => 'webpress_layout_footer',
				'choices' => array(
					'0' => '0',
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				),
				'settings' => 'webpress_settings[footer_widget_setting]',
				'priority' => 45,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[footer_bar_alignment]',
			array(
				'default' => $defaults['footer_bar_alignment'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[footer_bar_alignment]',
			array(
				'type' => 'select',
				'label' => __( 'Footer Bar Alignment', 'webpress' ),
				'section' => 'webpress_layout_footer',
				'choices' => array(
					'left' => __( 'Left', 'webpress' ),
					'center' => __( 'Center', 'webpress' ),
					'right' => __( 'Right', 'webpress' ),
				),
				'settings' => 'webpress_settings[footer_bar_alignment]',
				'priority' => 47,
				'active_callback' => 'webpress_is_footer_bar_active',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[back_to_top]',
			array(
				'default' => $defaults['back_to_top'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[back_to_top]',
			array(
				'type' => 'select',
				'label' => __( 'Back to Top Button', 'webpress' ),
				'section' => 'webpress_layout_footer',
				'choices' => array(
					'enable' => __( 'Enable', 'webpress' ),
					'' => __( 'Disable', 'webpress' ),
				),
				'settings' => 'webpress_settings[back_to_top]',
				'priority' => 50,
			)
		);

		// The options below only make sense once the button is switched on.
		$back_to_top_is_active = function() {
			return 'enable' === webpress_get_option( 'back_to_top' );
		};

		$back_to_top_options = array(
			'back_to_top_position' => array(
				'label' => __( 'Button Position', 'webpress' ),
				'type' => 'select',
				'sanitize' => 'webpress_sanitize_choices',
				'choices' => array(
					'right' => __( 'Bottom Right', 'webpress' ),
					'left' => __( 'Bottom Left', 'webpress' ),
				),
				'priority' => 51,
			),
			'back_to_top_size' => array(
				'label' => __( 'Button Size', 'webpress' ),
				'description' => __( 'Width and height of the button, in pixels.', 'webpress' ),
				'type' => 'number',
				'sanitize' => 'webpress_sanitize_integer',
				'input_attrs' => array(
					'min' => 20,
					'max' => 100,
					'step' => 1,
				),
				'priority' => 52,
			),
			'back_to_top_border_radius' => array(
				'label' => __( 'Border Radius', 'webpress' ),
				'description' => __( 'Use half the button size for a circle.', 'webpress' ),
				'type' => 'number',
				'sanitize' => 'webpress_sanitize_integer',
				'input_attrs' => array(
					'min' => 0,
					'max' => 50,
					'step' => 1,
				),
				'priority' => 53,
			),
			'back_to_top_offset' => array(
				'label' => __( 'Distance From Edge', 'webpress' ),
				'description' => __( 'Space between the button and the edge of the screen, in pixels.', 'webpress' ),
				'type' => 'number',
				'sanitize' => 'webpress_sanitize_integer',
				'input_attrs' => array(
					'min' => 0,
					'max' => 200,
					'step' => 1,
				),
				'priority' => 54,
			),
			'back_to_top_scroll_start' => array(
				'label' => __( 'Show After Scrolling', 'webpress' ),
				'description' => __( 'How far down the page the visitor must scroll before the button appears, in pixels.', 'webpress' ),
				'type' => 'number',
				'sanitize' => 'webpress_sanitize_integer',
				'input_attrs' => array(
					'min' => 0,
					'step' => 50,
				),
				'priority' => 55,
			),
			'back_to_top_scroll_speed' => array(
				'label' => __( 'Scroll Speed', 'webpress' ),
				'description' => __( 'Duration of the scroll animation, in milliseconds.', 'webpress' ),
				'type' => 'number',
				'sanitize' => 'webpress_sanitize_integer',
				'input_attrs' => array(
					'min' => 0,
					'max' => 3000,
					'step' => 50,
				),
				'priority' => 56,
			),
		);

		foreach ( $back_to_top_options as $option => $args ) {
			$wp_customize->add_setting(
				'webpress_settings[' . $option . ']',
				array(
					'default' => $defaults[ $option ],
					'type' => 'option',
					'sanitize_callback' => $args['sanitize'],
				)
			);

			$control_args = array(
				'type' => $args['type'],
				'label' => $args['label'],
				'section' => 'webpress_layout_footer',
				'settings' => 'webpress_settings[' . $option . ']',
				'priority' => $args['priority'],
				'active_callback' => $back_to_top_is_active,
			);

			if ( isset( $args['description'] ) ) {
				$control_args['description'] = $args['description'];
			}

			if ( isset( $args['choices'] ) ) {
				$control_args['choices'] = $args['choices'];
			}

			if ( isset( $args['input_attrs'] ) ) {
				$control_args['input_attrs'] = $args['input_attrs'];
			}

			$wp_customize->add_control( 'webpress_settings[' . $option . ']', $control_args );
		}

		$wp_customize->add_setting(
			'webpress_settings[back_to_top_smooth_scroll]',
			array(
				'default' => $defaults['back_to_top_smooth_scroll'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[back_to_top_smooth_scroll]',
			array(
				'type' => 'checkbox',
				'label' => __( 'Smooth Scrolling', 'webpress' ),
				'description' => __( 'Animate the scroll instead of jumping straight to the top.', 'webpress' ),
				'section' => 'webpress_layout_footer',
				'settings' => 'webpress_settings[back_to_top_smooth_scroll]',
				'priority' => 57,
				'active_callback' => $back_to_top_is_active,
			)
		);

		$wp_customize->add_section(
			'webpress_blog_section',
			array(
				'title' => __( 'Blog & Archives', 'webpress' ),
				'description' => __( 'The layout for the blog, category, tag, author, date and search results pages.', 'webpress' ),
				'priority' => 44,
				'panel' => 'webpress_layout_panel',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[blog_container_layout]',
			array(
				'default' => $defaults['blog_container_layout'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			new WebPress_Customize_Radio_Image_Control(
				$wp_customize,
				'webpress_settings[blog_container_layout]',
				array(
					'label' => __( 'Container Layout', 'webpress' ),
					'description' => $container_layout_description,
					'section' => 'webpress_blog_section',
					'choices' => $container_layouts,
					'settings' => 'webpress_settings[blog_container_layout]',
					'priority' => 1,
				)
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[blog_layout_setting]',
			array(
				'default' => $defaults['blog_layout_setting'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			new WebPress_Customize_Radio_Image_Control(
				$wp_customize,
				'webpress_settings[blog_layout_setting]',
				array(
					'label' => __( 'Sidebar Layout', 'webpress' ),
					'section' => 'webpress_blog_section',
					'choices' => $sidebar_layouts,
					'settings' => 'webpress_settings[blog_layout_setting]',
					'priority' => 2,
				)
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[blog_post_layout]',
			array(
				'default' => $defaults['blog_post_layout'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			new WebPress_Customize_Radio_Image_Control(
				$wp_customize,
				'webpress_settings[blog_post_layout]',
				array(
					'label' => __( 'Post Layout', 'webpress' ),
					'description' => __( 'Classic stacks full width posts, List puts the featured image beside the text, and Grid arranges posts in columns.', 'webpress' ),
					'section' => 'webpress_blog_section',
					'choices' => array(
						'classic' => __( 'Classic', 'webpress' ),
						'list' => __( 'List', 'webpress' ),
						'grid' => __( 'Grid', 'webpress' ),
					),
					'settings' => 'webpress_settings[blog_post_layout]',
					'priority' => 3,
				)
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[blog_grid_columns]',
			array(
				'default' => $defaults['blog_grid_columns'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[blog_grid_columns]',
			array(
				'type' => 'select',
				'label' => __( 'Grid Columns', 'webpress' ),
				'description' => __( 'Tablets show up to two columns and phones show one.', 'webpress' ),
				'section' => 'webpress_blog_section',
				'choices' => array(
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'settings' => 'webpress_settings[blog_grid_columns]',
				'priority' => 4,
				'active_callback' => 'webpress_is_blog_grid_layout_callback',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[post_content]',
			array(
				'default' => $defaults['post_content'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_blog_excerpt',
			)
		);

		$wp_customize->add_control(
			'blog_content_control',
			array(
				'type' => 'select',
				'label' => __( 'Content Type', 'webpress' ),
				'section' => 'webpress_blog_section',
				'choices' => array(
					'full' => __( 'Full Content', 'webpress' ),
					'excerpt' => __( 'Excerpt', 'webpress' ),
				),
				'settings' => 'webpress_settings[post_content]',
				'priority' => 10,
			)
		);

		$wp_customize->add_section(
			'webpress_general_section',
			array(
				'title' => __( 'General', 'webpress' ),
				'priority' => 99,
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[underline_links]',
			array(
				'default' => $defaults['underline_links'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_choices',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[underline_links]',
			array(
				'type' => 'select',
				'label' => __( 'Underline Links', 'webpress' ),
				'description' => __( 'Add underlines to your links in your main content areas.', 'webpress' ),
				'section' => 'webpress_general_section',
				'choices' => array(
					'always' => __( 'Always', 'webpress' ),
					'hover' => __( 'On hover', 'webpress' ),
					'not-hover' => __( 'Not on hover', 'webpress' ),
					'never' => __( 'Never', 'webpress' ),
				),
				'settings' => 'webpress_settings[underline_links]',
			)
		);

		$wp_customize->add_setting(
			'webpress_settings[dynamic_css_cache]',
			array(
				'default' => $defaults['dynamic_css_cache'],
				'type' => 'option',
				'sanitize_callback' => 'webpress_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'webpress_settings[dynamic_css_cache]',
			array(
				'type' => 'checkbox',
				'label' => __( 'Cache dynamic CSS', 'webpress' ),
				'description' => __( 'Cache CSS generated by your options to boost performance.', 'webpress' ),
				'section' => 'webpress_general_section',
			)
		);
	}
}
