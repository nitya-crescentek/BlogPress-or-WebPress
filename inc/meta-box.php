<?php
/**
 * Builds our main Layout meta box.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'admin_enqueue_scripts', 'webpress_enqueue_meta_box_scripts' );
/**
 * Adds any scripts for this meta box.
 *
 * @since 1.0.0
 *
 * @param string $hook The current admin page.
 */
function webpress_enqueue_meta_box_scripts( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
		$post_types = get_post_types( array( 'public' => true ) );
		$screen = get_current_screen();
		$post_type = $screen->id;

		if ( in_array( $post_type, (array) $post_types ) ) {
			wp_enqueue_style( 'webpress-layout-metabox', get_template_directory_uri() . '/assets/css/admin/meta-box.css', array(), WEBPRESS_VERSION );
		}
	}
}

add_action( 'add_meta_boxes', 'webpress_register_layout_meta_box' );
/**
 * Register the layout metabox.
 *
 * @since 1.0.0
 */
function webpress_register_layout_meta_box() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	if ( ! defined( 'WEBPRESS_LAYOUT_META_BOX' ) ) {
		define( 'WEBPRESS_LAYOUT_META_BOX', true );
	}

	global $post;

	$blog_id = get_option( 'page_for_posts' );

	// No need for the Layout metabox on the blog page.
	if ( isset( $post->ID ) && $blog_id && (int) $blog_id === (int) $post->ID ) {
		return;
	}

	$post_types = get_post_types( array( 'public' => true ) );

	foreach ( $post_types as $type ) {
		if ( 'attachment' !== $type ) {
			add_meta_box(
				'webpress_layout_options_meta_box',
				esc_html__( 'Layout', 'webpress' ),
				'webpress_do_layout_meta_box',
				$type,
				'side'
			);
		}
	}
}

/**
 * Build our meta box.
 *
 * @since 1.0.0
 *
 * @param object $post All post information.
 */
function webpress_do_layout_meta_box( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'webpress_layout_nonce' );
	$stored_meta = (array) get_post_meta( $post->ID );
	$stored_meta['_webpress-sidebar-layout-meta'][0] = ( isset( $stored_meta['_webpress-sidebar-layout-meta'][0] ) ) ? $stored_meta['_webpress-sidebar-layout-meta'][0] : '';
	$stored_meta['_webpress-footer-widget-meta'][0] = ( isset( $stored_meta['_webpress-footer-widget-meta'][0] ) ) ? $stored_meta['_webpress-footer-widget-meta'][0] : '';
	$stored_meta['_webpress-full-width-content'][0] = ( isset( $stored_meta['_webpress-full-width-content'][0] ) ) ? $stored_meta['_webpress-full-width-content'][0] : '';
	$stored_meta['_webpress-container-layout'][0] = ( isset( $stored_meta['_webpress-container-layout'][0] ) ) ? $stored_meta['_webpress-container-layout'][0] : '';
	$stored_meta['_webpress-disable-headline'][0] = ( isset( $stored_meta['_webpress-disable-headline'][0] ) ) ? $stored_meta['_webpress-disable-headline'][0] : '';

	$tabs = array(
		'sidebars' => array(
			'title' => esc_html__( 'Sidebars', 'webpress' ),
			'target' => '#webpress-layout-sidebars',
			'class' => 'current',
		),
		'container' => array(
			'title' => esc_html__( 'Container', 'webpress' ),
			'target' => '#webpress-layout-page-builder-container',
			'class' => '',
		),
		'footer_widgets' => array(
			'title' => esc_html__( 'Footer Widgets', 'webpress' ),
			'target' => '#webpress-layout-footer-widgets',
			'class' => '',
		),
		'disable_elements' => array(
			'title' => esc_html__( 'Disable Elements', 'webpress' ),
			'target' => '#webpress-layout-disable-elements',
			'class' => '',
		),
	);
	?>
	<script>
		jQuery(document).ready(function($) {
			$( '.webpress-meta-box-menu li a' ).on( 'click', function( event ) {
				event.preventDefault();
				$( this ).parent().addClass( 'current' );
				$( this ).parent().siblings().removeClass( 'current' );
				var tab = $( this ).attr( 'data-target' );

				// Page header module still using href.
				if ( ! tab ) {
					tab = $( this ).attr( 'href' );
				}

				$( '.webpress-meta-box-content' ).children( 'div' ).not( tab ).css( 'display', 'none' );
				$( tab ).fadeIn( 100 );
			});
		});
	</script>
	<div id="webpress-meta-box-container">
		<ul class="webpress-meta-box-menu">
			<?php
			foreach ( (array) $tabs as $tab => $data ) {
				echo '<li class="' . esc_attr( $data['class'] ) . '"><a data-target="' . esc_attr( $data['target'] ) . '" href="#">' . esc_html( $data['title'] ) . '</a></li>';
			}

			?>
		</ul>
		<div class="webpress-meta-box-content">
			<div id="webpress-layout-sidebars">
				<div class="webpress_layouts">
					<label for="webpress-sidebar-layout" class="webpress-layout-metabox-section-title"><?php esc_html_e( 'Sidebar Layout', 'webpress' ); ?></label>

					<select name="_webpress-sidebar-layout-meta" id="webpress-sidebar-layout">
						<option value="" <?php selected( $stored_meta['_webpress-sidebar-layout-meta'][0], '' ); ?>><?php esc_html_e( 'Default', 'webpress' ); ?></option>
						<option value="right-sidebar" <?php selected( $stored_meta['_webpress-sidebar-layout-meta'][0], 'right-sidebar' ); ?>><?php esc_html_e( 'Right Sidebar', 'webpress' ); ?></option>
						<option value="left-sidebar" <?php selected( $stored_meta['_webpress-sidebar-layout-meta'][0], 'left-sidebar' ); ?>><?php esc_html_e( 'Left Sidebar', 'webpress' ); ?></option>
						<option value="no-sidebar" <?php selected( $stored_meta['_webpress-sidebar-layout-meta'][0], 'no-sidebar' ); ?>><?php esc_html_e( 'No Sidebars', 'webpress' ); ?></option>
						<option value="both-sidebars" <?php selected( $stored_meta['_webpress-sidebar-layout-meta'][0], 'both-sidebars' ); ?>><?php esc_html_e( 'Both Sidebars', 'webpress' ); ?></option>
						<option value="both-left" <?php selected( $stored_meta['_webpress-sidebar-layout-meta'][0], 'both-left' ); ?>><?php esc_html_e( 'Both Sidebars on Left', 'webpress' ); ?></option>
						<option value="both-right" <?php selected( $stored_meta['_webpress-sidebar-layout-meta'][0], 'both-right' ); ?>><?php esc_html_e( 'Both Sidebars on Right', 'webpress' ); ?></option>
					</select>
				</div>
			</div>

			<div id="webpress-layout-footer-widgets" style="display: none;">
				<div class="webpress_footer_widget">
					<label for="webpress-footer-widget" class="webpress-layout-metabox-section-title"><?php esc_html_e( 'Footer Widgets', 'webpress' ); ?></label>

					<select name="_webpress-footer-widget-meta" id="webpress-footer-widget">
						<option value="" <?php selected( $stored_meta['_webpress-footer-widget-meta'][0], '' ); ?>><?php esc_html_e( 'Default', 'webpress' ); ?></option>
						<option value="0" <?php selected( $stored_meta['_webpress-footer-widget-meta'][0], '0' ); ?>><?php esc_html_e( '0 Widgets', 'webpress' ); ?></option>
						<option value="1" <?php selected( $stored_meta['_webpress-footer-widget-meta'][0], '1' ); ?>><?php esc_html_e( '1 Widgets', 'webpress' ); ?></option>
						<option value="2" <?php selected( $stored_meta['_webpress-footer-widget-meta'][0], '2' ); ?>><?php esc_html_e( '2 Widgets', 'webpress' ); ?></option>
						<option value="3" <?php selected( $stored_meta['_webpress-footer-widget-meta'][0], '3' ); ?>><?php esc_html_e( '3 Widgets', 'webpress' ); ?></option>
						<option value="4" <?php selected( $stored_meta['_webpress-footer-widget-meta'][0], '4' ); ?>><?php esc_html_e( '4 Widgets', 'webpress' ); ?></option>
						<option value="5" <?php selected( $stored_meta['_webpress-footer-widget-meta'][0], '5' ); ?>><?php esc_html_e( '5 Widgets', 'webpress' ); ?></option>
					</select>
				</div>
			</div>
			<div id="webpress-layout-page-builder-container" style="display: none;">
				<label for="webpress-container-layout" class="webpress-layout-metabox-section-title"><?php esc_html_e( 'Container Layout', 'webpress' ); ?></label>

				<p class="page-builder-content" style="color:#666;font-size:13px;margin-top:0;">
					<?php esc_html_e( 'Default uses the layout set in Appearance > Customize > Layout.', 'webpress' ); ?>
				</p>

				<select name="_webpress-container-layout" id="webpress-container-layout">
					<option value="" <?php selected( $stored_meta['_webpress-container-layout'][0], '' ); ?>><?php esc_html_e( 'Default', 'webpress' ); ?></option>
					<?php foreach ( webpress_get_container_layouts() as $layout_value => $layout_label ) : ?>
						<option value="<?php echo esc_attr( $layout_value ); ?>" <?php selected( $stored_meta['_webpress-container-layout'][0], $layout_value ); ?>><?php echo esc_html( $layout_label ); ?></option>
					<?php endforeach; ?>
				</select>

				<label for="_webpress-full-width-content" class="webpress-layout-metabox-section-title" style="margin-top:1.5em;"><?php esc_html_e( 'Page Builder Mode', 'webpress' ); ?></label>

				<p class="page-builder-content" style="color:#666;font-size:13px;margin-top:0;">
					<?php esc_html_e( 'Removes the content padding so a page builder can control the spacing.', 'webpress' ); ?>
				</p>

				<select name="_webpress-full-width-content" id="_webpress-full-width-content">
					<option value="" <?php selected( $stored_meta['_webpress-full-width-content'][0], '' ); ?>><?php esc_html_e( 'Off', 'webpress' ); ?></option>
					<option value="true" <?php selected( $stored_meta['_webpress-full-width-content'][0], 'true' ); ?>><?php esc_html_e( 'Full width, no padding', 'webpress' ); ?></option>
					<option value="contained" <?php selected( $stored_meta['_webpress-full-width-content'][0], 'contained' ); ?>><?php esc_html_e( 'Contained, no padding', 'webpress' ); ?></option>
				</select>
			</div>
			<div id="webpress-layout-disable-elements" style="display: none;">
				<label class="webpress-layout-metabox-section-title"><?php esc_html_e( 'Disable Elements', 'webpress' ); ?></label>
				<?php if ( ! defined( 'WEBPRESS_DE_VERSION' ) ) : ?>
					<div class="webpress_disable_elements">
						<label for="meta-webpress-disable-headline" style="display:block;margin: 0 0 1em;" title="<?php esc_attr_e( 'Content Title', 'webpress' ); ?>">
							<input type="checkbox" name="_webpress-disable-headline" id="meta-webpress-disable-headline" value="true" <?php checked( $stored_meta['_webpress-disable-headline'][0], 'true' ); ?>>
							<?php esc_html_e( 'Content Title', 'webpress' ); ?>
						</label>

					</div>
				<?php endif; ?>

				<?php
				/**
				 * Fires inside the Disable Elements section of the layout metabox.
				 *
				 * @since 1.0.0
				 *
				 * @param WP_Post $post The post being edited.
				 */
				do_action( 'webpress_layout_meta_box_disable_elements', $post );
				?>
			</div>
			<?php
			/**
			 * Fires after the built-in sections of the layout metabox.
			 *
			 * @since 1.0.0
			 *
			 * @param WP_Post $post The post being edited.
			 */
			do_action( 'webpress_layout_meta_box_settings', $post );
			?>
		</div>
	</div>
	<?php
}

add_action( 'save_post', 'webpress_save_layout_meta_data' );
/**
 * Saves the sidebar layout meta data.
 *
 * @since 1.0.0
 * @param int $post_id Post ID.
 */
function webpress_save_layout_meta_data( $post_id ) {
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST['webpress_layout_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['webpress_layout_nonce'] ), basename( __FILE__ ) ) ) ? true : false;

	if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	$sidebar_layout_key   = '_webpress-sidebar-layout-meta';
	$sidebar_layout_value = isset( $_POST[ $sidebar_layout_key ] )
		? sanitize_text_field( wp_unslash( $_POST[ $sidebar_layout_key ] ) )
		: '';

	if ( $sidebar_layout_value ) {
		update_post_meta( $post_id, $sidebar_layout_key, $sidebar_layout_value );
	} else {
		delete_post_meta( $post_id, $sidebar_layout_key );
	}

	$footer_widget_key   = '_webpress-footer-widget-meta';
	$footer_widget_value = isset( $_POST[ $footer_widget_key ] )
		? sanitize_text_field( wp_unslash( $_POST[ $footer_widget_key ] ) )
		: '';

	// Check for empty string to allow 0 as a value.
	if ( '' !== $footer_widget_value ) {
		update_post_meta( $post_id, $footer_widget_key, $footer_widget_value );
	} else {
		delete_post_meta( $post_id, $footer_widget_key );
	}

	$page_builder_container_key   = '_webpress-full-width-content';
	$page_builder_container_value = isset( $_POST[ $page_builder_container_key ] )
		? sanitize_text_field( wp_unslash( $_POST[ $page_builder_container_key ] ) )
		: '';

	if ( $page_builder_container_value ) {
		update_post_meta( $post_id, $page_builder_container_key, $page_builder_container_value );
	} else {
		delete_post_meta( $post_id, $page_builder_container_key );
	}

	$container_layout_key   = '_webpress-container-layout';
	$container_layout_value = isset( $_POST[ $container_layout_key ] )
		? sanitize_key( wp_unslash( $_POST[ $container_layout_key ] ) )
		: '';

	if ( array_key_exists( $container_layout_value, webpress_get_container_layouts() ) ) {
		update_post_meta( $post_id, $container_layout_key, $container_layout_value );
	} else {
		delete_post_meta( $post_id, $container_layout_key );
	}

	// We only need this if the Disable Elements module doesn't exist.
	if ( ! defined( 'WEBPRESS_DE_VERSION' ) ) {
		$disable_content_title_key   = '_webpress-disable-headline';
		$disable_content_title_value = isset( $_POST[ $disable_content_title_key ] )
			? sanitize_text_field( wp_unslash( $_POST[ $disable_content_title_key ] ) )
			: '';

		if ( $disable_content_title_value ) {
			update_post_meta( $post_id, $disable_content_title_key, $disable_content_title_value );
		} else {
			delete_post_meta( $post_id, $disable_content_title_key );
		}
	}
}
