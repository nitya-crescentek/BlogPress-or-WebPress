<?php
/**
 * Sets all of our theme defaults.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'webpress_get_defaults' ) ) {
	/**
	 * Set default options
	 *
	 * @since 1.0.0
	 */
	function webpress_get_defaults( $filter = true ) {
		$defaults = array(
			'hide_title' => '',
			'hide_tagline' => true,
			'logo' => '',
			'retina_logo' => '',
			'logo_width' => '',
			'top_bar_width' => 'full',
			'top_bar_inner_width' => 'contained',
			'top_bar_alignment' => 'right',
			'container_width' => '1200',
			'narrow_container_width' => '750',
			'container_alignment' => 'text',
			'header_layout_setting' => 'fluid-header',
			'header_inner_width' => 'contained',
			'nav_alignment_setting' => is_rtl() ? 'right' : 'left',
			'header_alignment_setting' => is_rtl() ? 'right' : 'left',
			'nav_layout_setting' => 'fluid-nav',
			'nav_inner_width' => 'contained',
			'nav_position_setting' => 'nav-float-right',
			'nav_drop_point' => '',
			'nav_dropdown_type' => 'hover',
			'nav_dropdown_direction' => is_rtl() ? 'left' : 'right',
			'nav_search' => 'disable',
			'nav_search_modal' => false,
			'content_layout_setting' => 'separate-containers',
			'layout_setting' => 'right-sidebar',
			'blog_layout_setting' => 'right-sidebar',
			'single_layout_setting' => 'right-sidebar',
			'page_container_layout' => 'normal',
			'single_container_layout' => 'normal',
			'blog_container_layout' => 'normal',
			'blog_post_layout' => 'classic',
			'blog_grid_columns' => '2',
			'post_content' => 'excerpt',
			'footer_layout_setting' => 'fluid-footer',
			'footer_inner_width' => 'contained',
			'footer_widget_setting' => '3',
			'footer_bar_alignment' => 'right',
			'back_to_top' => 'enable',
			'back_to_top_position' => 'right',
			'back_to_top_size' => '40',
			'back_to_top_border_radius' => '8',
			'back_to_top_offset' => '30',
			'back_to_top_scroll_start' => '300',
			'back_to_top_scroll_speed' => '400',
			'back_to_top_smooth_scroll' => true,
			'background_color' => 'var(--base-2)',
			'text_color' => 'var(--contrast)',
			'link_color' => 'var(--accent)',
			'link_color_hover' => 'var(--contrast)',
			'link_color_visited' => '',
			'dynamic_css_cache' => true,
			'underline_links' => 'always',
			'font_manager' => array(),
			'typography' => array(),
			'google_font_display' => 'auto',
			'global_colors' => array(
				array(
					'name' => __( 'Contrast', 'webpress' ),
					'slug' => 'contrast',
					'color' => '#222222',
				),
				array(
					/* translators: Contrast number */
					'name' => sprintf( __( 'Contrast %s', 'webpress' ), '2' ),
					'slug' => 'contrast-2',
					'color' => '#575760',
				),
				array(
					/* translators: Contrast number */
					'name' => sprintf( __( 'Contrast %s', 'webpress' ), '3' ),
					'slug' => 'contrast-3',
					'color' => '#b2b2be',
				),
				array(
					'name' => __( 'Base', 'webpress' ),
					'slug' => 'base',
					'color' => '#f0f0f0',
				),
				array(
					/* translators: Base number */
					'name' => sprintf( __( 'Base %s', 'webpress' ), '2' ),
					'slug' => 'base-2',
					'color' => '#f7f8f9',
				),
				array(
					/* translators: Base number */
					'name' => sprintf( __( 'Base %s', 'webpress' ), '3' ),
					'slug' => 'base-3',
					'color' => '#ffffff',
				),
				array(
					'name' => __( 'Accent', 'webpress' ),
					'slug' => 'accent',
					'color' => '#1e73be',
				),
			),
		);

		if ( $filter ) {
			/**
			 * Filters the theme's default option values.
			 *
			 * Lets a child theme or plugin change a default without having to
			 * redefine the whole array, so defaults added in later versions of
			 * the theme are still picked up.
			 *
			 * @since 1.0.0
			 *
			 * @param array $defaults The default option values.
			 * @return array The default option values to use.
			 */
			return apply_filters( 'webpress_option_defaults', $defaults );
		}

		return $defaults;
	}
}

if ( ! function_exists( 'webpress_get_color_defaults' ) ) {
	/**
	 * Set default options
	 */
	function webpress_get_color_defaults( $filter = true ) {
		$defaults = array(
			'top_bar_background_color' => '#636363',
			'top_bar_text_color' => '#ffffff',
			'top_bar_link_color' => '#ffffff',
			'top_bar_link_color_hover' => '#303030',
			'header_background_color' => 'var(--base-3)',
			'header_text_color' => '',
			'header_link_color' => '',
			'header_link_hover_color' => '',
			'site_title_color' => 'var(--contrast)',
			'site_tagline_color' => 'var(--contrast-2)',
			'navigation_background_color' => 'var(--base-3)',
			'navigation_text_color' => 'var(--contrast)',
			'navigation_background_hover_color' => '',
			'navigation_text_hover_color' => 'var(--accent)',
			'navigation_background_current_color' => '',
			'navigation_text_current_color' => 'var(--accent)',
			'subnavigation_background_color' => 'var(--base)',
			'subnavigation_text_color' => '',
			'subnavigation_background_hover_color' => '',
			'subnavigation_text_hover_color' => '',
			'subnavigation_background_current_color' => '',
			'subnavigation_text_current_color' => '',
			'navigation_search_background_color' => '',
			'navigation_search_text_color' => '',
			'content_background_color' => 'var(--base-3)',
			'content_text_color' => '',
			'content_link_color' => '',
			'content_link_hover_color' => '',
			'content_title_color' => '',
			'blog_post_title_color' => 'var(--contrast)',
			'blog_post_title_hover_color' => 'var(--contrast-2)',
			'entry_meta_text_color' => 'var(--contrast-2)',
			'entry_meta_link_color' => '',
			'entry_meta_link_color_hover' => '',
			'h1_color' => '',
			'h2_color' => '',
			'h3_color' => '',
			'h4_color' => '',
			'h5_color' => '',
			'h6_color' => '',
			'sidebar_widget_background_color' => 'var(--base-3)',
			'sidebar_widget_text_color' => '',
			'sidebar_widget_link_color' => '',
			'sidebar_widget_link_hover_color' => '',
			'sidebar_widget_title_color' => '',
			'footer_widget_background_color' => 'var(--base-3)',
			'footer_widget_text_color' => '',
			'footer_widget_link_color' => '',
			'footer_widget_link_hover_color' => '',
			'footer_widget_title_color' => '',
			'footer_background_color' => 'var(--base-3)',
			'footer_text_color' => '',
			'footer_link_color' => '',
			'footer_link_hover_color' => '',
			'form_background_color' => 'var(--base-2)',
			'form_text_color' => 'var(--contrast)',
			'form_background_color_focus' => 'var(--base-2)',
			'form_text_color_focus' => 'var(--contrast)',
			'form_border_color' => 'var(--base)',
			'form_border_color_focus' => 'var(--contrast-3)',
			'form_button_background_color' => '#55555e',
			'form_button_background_color_hover' => '#3f4047',
			'form_button_text_color' => '#ffffff',
			'form_button_text_color_hover' => '#ffffff',
			'back_to_top_background_color' => 'rgba( 0,0,0,0.4 )',
			'back_to_top_background_color_hover' => 'rgba( 0,0,0,0.6 )',
			'back_to_top_text_color' => '#ffffff',
			'back_to_top_text_color_hover' => '#ffffff',
			'search_modal_bg_color' => 'var(--base-3)',
			'search_modal_text_color' => 'var(--contrast)',
			'search_modal_overlay_bg_color' => 'rgba(0,0,0,0.2)',
		);

		if ( $filter ) {
			/**
			 * Filters the theme's default colour values.
			 *
			 * @since 1.0.0
			 *
			 * @param array $defaults The default colour values.
			 * @return array The default colour values to use.
			 */
			return apply_filters( 'webpress_color_option_defaults', $defaults );
		}

		return $defaults;
	}
}

if ( ! function_exists( 'webpress_get_default_fonts' ) ) {
	/**
	 * Set default options.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $filter Whether to return the filtered values or original values.
	 * @return array Option defaults.
	 */
	function webpress_get_default_fonts( $filter = true ) {
		$defaults = array(
			'font_body' => 'System Stack',
			'font_body_category' => '',
			'font_body_variants' => '',
			'body_font_weight' => 'normal',
			'body_font_transform' => 'none',
			'body_font_size' => '17',
			'body_line_height' => '1.5', // no unit.
			'paragraph_margin' => '1.5', // em.
			'font_top_bar' => 'inherit',
			'font_top_bar_category' => '',
			'font_top_bar_variants' => '',
			'top_bar_font_weight' => 'normal',
			'top_bar_font_transform' => 'none',
			'top_bar_font_size' => '13',
			'font_site_title' => 'inherit',
			'font_site_title_category' => '',
			'font_site_title_variants' => '',
			'site_title_font_weight' => 'bold',
			'site_title_font_transform' => 'none',
			'site_title_font_size' => '25',
			'mobile_site_title_font_size' => '',
			'font_site_tagline' => 'inherit',
			'font_site_tagline_category' => '',
			'font_site_tagline_variants' => '',
			'site_tagline_font_weight' => 'normal',
			'site_tagline_font_transform' => 'none',
			'site_tagline_font_size' => '15',
			'font_navigation' => 'inherit',
			'font_navigation_category' => '',
			'font_navigation_variants' => '',
			'navigation_font_weight' => 'normal',
			'navigation_font_transform' => 'none',
			'navigation_font_size' => '15',
			'font_widget_title' => 'inherit',
			'font_widget_title_category' => '',
			'font_widget_title_variants' => '',
			'widget_title_font_weight' => 'normal',
			'widget_title_font_transform' => 'none',
			'widget_title_font_size' => '20',
			'widget_title_separator' => '30',
			'widget_content_font_size' => '17',
			'font_buttons' => 'inherit',
			'font_buttons_category' => '',
			'font_buttons_variants' => '',
			'buttons_font_weight' => 'normal',
			'buttons_font_transform' => 'none',
			'buttons_font_size' => '',
			'font_heading_1' => 'inherit',
			'font_heading_1_category' => '',
			'font_heading_1_variants' => '',
			'heading_1_weight' => 'normal',
			'heading_1_transform' => 'none',
			'heading_1_font_size' => '42',
			'heading_1_line_height' => '1.2', // em.
			'heading_1_margin_bottom' => '20',
			'mobile_heading_1_font_size' => '31',
			'font_heading_2' => 'inherit',
			'font_heading_2_category' => '',
			'font_heading_2_variants' => '',
			'heading_2_weight' => 'normal',
			'heading_2_transform' => 'none',
			'heading_2_font_size' => '35',
			'heading_2_line_height' => '1.2', // em.
			'heading_2_margin_bottom' => '20',
			'mobile_heading_2_font_size' => '27',
			'font_heading_3' => 'inherit',
			'font_heading_3_category' => '',
			'font_heading_3_variants' => '',
			'heading_3_weight' => 'normal',
			'heading_3_transform' => 'none',
			'heading_3_font_size' => '29',
			'heading_3_line_height' => '1.2', // em.
			'heading_3_margin_bottom' => '20',
			'mobile_heading_3_font_size' => '24',
			'font_heading_4' => 'inherit',
			'font_heading_4_category' => '',
			'font_heading_4_variants' => '',
			'heading_4_weight' => 'normal',
			'heading_4_transform' => 'none',
			'heading_4_font_size' => '24',
			'heading_4_line_height' => '', // em.
			'mobile_heading_4_font_size' => '22',
			'font_heading_5' => 'inherit',
			'font_heading_5_category' => '',
			'font_heading_5_variants' => '',
			'heading_5_weight' => 'normal',
			'heading_5_transform' => 'none',
			'heading_5_font_size' => '20',
			'heading_5_line_height' => '', // em.
			'mobile_heading_5_font_size' => '19',
			'font_heading_6' => 'inherit',
			'font_heading_6_category' => '',
			'font_heading_6_variants' => '',
			'heading_6_weight' => 'normal',
			'heading_6_transform' => 'none',
			'heading_6_font_size' => '',
			'heading_6_line_height' => '', // em.
			'font_footer' => 'inherit',
			'font_footer_category' => '',
			'font_footer_variants' => '',
			'footer_weight' => 'normal',
			'footer_transform' => 'none',
			'footer_font_size' => '15',
		);

		if ( $filter ) {
			/**
			 * Filters the theme's default typography values.
			 *
			 * @since 1.0.0
			 *
			 * @param array $defaults The default typography values.
			 * @return array The default typography values to use.
			 */
			return apply_filters( 'webpress_font_option_defaults', $defaults );
		}

		return $defaults;
	}
}

if ( ! function_exists( 'webpress_spacing_get_defaults' ) ) {
	/**
	 * Set the default options.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $filter Whether to return the filtered values or original values.
	 * @return array Option defaults.
	 */
	function webpress_spacing_get_defaults( $filter = true ) {
		$defaults = array(
			'top_bar_top' => '10',
			'top_bar_right' => '40',
			'top_bar_bottom' => '10',
			'top_bar_left' => '40',
			'mobile_top_bar_top' => '',
			'mobile_top_bar_right' => '30',
			'mobile_top_bar_bottom' => '',
			'mobile_top_bar_left' => '30',
			'header_top' => '20',
			'header_right' => '40',
			'header_bottom' => '20',
			'header_left' => '40',
			'mobile_header_top' => '',
			'mobile_header_right' => '30',
			'mobile_header_bottom' => '',
			'mobile_header_left' => '30',
			'menu_item' => '20',
			'menu_item_height' => '60',
			'sub_menu_item_height' => '10',
			'sub_menu_width' => '200',
			'content_top' => '40',
			'content_right' => '40',
			'content_bottom' => '40',
			'content_left' => '40',
			'mobile_content_top' => '30',
			'mobile_content_right' => '30',
			'mobile_content_bottom' => '30',
			'mobile_content_left' => '30',
			'separator' => '20',
			'mobile_separator' => '',
			'left_sidebar_width' => '30',
			'right_sidebar_width' => '30',
			'widget_top' => '40',
			'widget_right' => '40',
			'widget_bottom' => '40',
			'widget_left' => '40',
			'mobile_widget_top' => '30',
			'mobile_widget_right' => '30',
			'mobile_widget_bottom' => '30',
			'mobile_widget_left' => '30',
			'footer_widget_container_top' => '40',
			'footer_widget_container_right' => '40',
			'footer_widget_container_bottom' => '40',
			'footer_widget_container_left' => '40',
			'mobile_footer_widget_container_top' => '30',
			'mobile_footer_widget_container_right' => '30',
			'mobile_footer_widget_container_bottom' => '30',
			'mobile_footer_widget_container_left' => '30',
			'footer_widget_separator' => '40',
			'footer_top' => '20',
			'footer_right' => '40',
			'footer_bottom' => '20',
			'footer_left' => '40',
			'mobile_footer_top' => '',
			'mobile_footer_right' => '30',
			'mobile_footer_bottom' => '',
			'mobile_footer_left' => '30',
		);

		if ( $filter ) {
			/**
			 * Filters the theme's default spacing values.
			 *
			 * @since 1.0.0
			 *
			 * @param array $defaults The default spacing values.
			 * @return array The default spacing values to use.
			 */
			return apply_filters( 'webpress_spacing_option_defaults', $defaults );
		}

		return $defaults;
	}
}
