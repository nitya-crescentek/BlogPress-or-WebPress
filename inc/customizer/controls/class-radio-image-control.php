<?php
/**
 * A radio control that shows each choice as a layout icon.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return;
}

/**
 * Layout picker for the Customizer.
 *
 * Renders real radio inputs, so the core Customizer binds the setting with
 * no extra JavaScript, and the choices stay keyboard and screen reader
 * friendly. Each choice key doubles as the name of its icon.
 *
 * @since 1.0.0
 */
class WebPress_Customize_Radio_Image_Control extends WP_Customize_Control {
	/**
	 * The control type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'webpress-radio-image';

	/**
	 * Enqueue the control stylesheet.
	 */
	public function enqueue() {
		wp_enqueue_style(
			'webpress-radio-image-control',
			trailingslashit( get_template_directory_uri() ) . 'inc/customizer/controls/css/radio-image.css',
			array(),
			WEBPRESS_VERSION
		);
	}

	/**
	 * Render the control.
	 */
	protected function render_content() {
		if ( empty( $this->choices ) ) {
			return;
		}

		$name = '_customize-radio-' . $this->id;
		$label_id = '_customize-label-' . $this->id;
		?>
		<?php if ( ! empty( $this->label ) ) : ?>
			<span id="<?php echo esc_attr( $label_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>

		<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
		<?php endif; ?>

		<div class="webpress-radio-image" role="radiogroup" <?php echo $this->label ? 'aria-labelledby="' . esc_attr( $label_id ) . '"' : ''; ?>>
			<?php foreach ( $this->choices as $value => $label ) : ?>
				<label class="webpress-radio-image__choice">
					<input class="screen-reader-text" type="radio" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); ?> <?php checked( $this->value(), $value ); ?> />
					<span class="webpress-radio-image__icon" aria-hidden="true">
						<?php echo self::get_icon( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup defined in this class. ?>
					</span>
					<span class="webpress-radio-image__label"><?php echo esc_html( $label ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Get the SVG icon for a layout choice.
	 *
	 * Content areas are drawn faint and sidebars and images solid, so the
	 * shapes read the same way across every icon.
	 *
	 * @param string $name The choice key.
	 * @return string The SVG markup, or an empty string.
	 */
	public static function get_icon( $name ) {
		$content = '<rect x="%s" y="%s" width="%s" height="%s" rx="1.5" fill="currentColor" opacity=".25"/>';
		$solid = '<rect x="%s" y="%s" width="%s" height="%s" rx="1.5" fill="currentColor" opacity=".65"/>';
		$screen = '<rect x="2" y="3" width="56" height="34" rx="2.5" fill="none" stroke="currentColor" stroke-opacity=".45"/>';

		$shapes = array(
			// Sidebar layouts.
			'right-sidebar' => sprintf( $content, 4, 4, 36, 32 ) . sprintf( $solid, 44, 4, 12, 32 ),
			'left-sidebar' => sprintf( $solid, 4, 4, 12, 32 ) . sprintf( $content, 20, 4, 36, 32 ),
			'no-sidebar' => sprintf( $content, 4, 4, 52, 32 ),
			'both-sidebars' => sprintf( $solid, 4, 4, 10, 32 ) . sprintf( $content, 18, 4, 24, 32 ) . sprintf( $solid, 46, 4, 10, 32 ),
			'both-left' => sprintf( $solid, 4, 4, 10, 32 ) . sprintf( $solid, 18, 4, 10, 32 ) . sprintf( $content, 32, 4, 24, 32 ),
			'both-right' => sprintf( $content, 4, 4, 24, 32 ) . sprintf( $solid, 32, 4, 10, 32 ) . sprintf( $solid, 46, 4, 10, 32 ),

			// Container layouts, drawn inside a screen outline.
			'normal' => $screen . sprintf( $content, 12, 7, 36, 26 ),
			'narrow' => $screen . sprintf( $content, 20, 7, 20, 26 ),
			'full-width' => $screen . sprintf( $content, 5, 7, 50, 26 ),

			// Blog post layouts.
			'classic' => sprintf( $solid, 8, 4, 44, 17 ) . sprintf( $content, 8, 24, 36, 3 ) . sprintf( $content, 8, 29, 44, 3 ) . sprintf( $content, 8, 34, 28, 3 ),
			'list' => sprintf( $solid, 6, 4, 15, 9 ) . sprintf( $content, 24, 5, 30, 3 ) . sprintf( $content, 24, 10, 22, 2 )
				. sprintf( $solid, 6, 15.5, 15, 9 ) . sprintf( $content, 24, 16.5, 30, 3 ) . sprintf( $content, 24, 21.5, 22, 2 )
				. sprintf( $solid, 6, 27, 15, 9 ) . sprintf( $content, 24, 28, 30, 3 ) . sprintf( $content, 24, 33, 22, 2 ),
			'grid' => sprintf( $solid, 6, 4, 22, 11 ) . sprintf( $content, 6, 17, 16, 2.5 )
				. sprintf( $solid, 32, 4, 22, 11 ) . sprintf( $content, 32, 17, 16, 2.5 )
				. sprintf( $solid, 6, 22, 22, 11 ) . sprintf( $content, 6, 35, 16, 2.5 )
				. sprintf( $solid, 32, 22, 22, 11 ) . sprintf( $content, 32, 35, 16, 2.5 ),
		);

		if ( ! isset( $shapes[ $name ] ) ) {
			return '';
		}

		return '<svg viewBox="0 0 60 40" xmlns="http://www.w3.org/2000/svg" focusable="false">' . $shapes[ $name ] . '</svg>';
	}
}
