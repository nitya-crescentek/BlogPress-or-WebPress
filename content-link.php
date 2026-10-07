<?php
/**
 * The template for displaying Link post formats.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> <?php webpress_do_microdata( 'article' ); ?>>
	<div class="inside-article">
		<?php
		webpress_featured_page_header_inside_single();

		webpress_post_image( 'above-title' );

		$webpress_has_entry_body = webpress_has_entry_body_wrapper();

		if ( $webpress_has_entry_body ) {
			echo '<div class="entry-body">';
		}

		/** This action is documented in content.php */
		do_action( 'webpress_before_content', 'link' );

		if ( webpress_show_entry_header() ) :
			?>
			<header <?php webpress_do_attr( 'entry-header' ); ?>>
				<?php
				/** This action is documented in content.php */
				do_action( 'webpress_before_entry_title', 'link' );

				if ( webpress_show_title() ) {
					$params = webpress_get_the_title_parameters();

					the_title( $params['before'], $params['after'] );
				}

				/** This action is documented in content.php */
				do_action( 'webpress_after_entry_title', 'link' );

				webpress_post_meta();
				?>
			</header>
			<?php
		endif;

		/** This action is documented in content.php */
		do_action( 'webpress_after_entry_header', 'link' );

		webpress_post_image();

		$itemprop = '';

		if ( 'microdata' === webpress_get_schema_type() ) {
			$itemprop = ' itemprop="text"';
		}

		if ( webpress_show_excerpt() ) :
			?>

			<div class="entry-summary"<?php echo $itemprop; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal attribute string built above; escaping would break the markup. ?>>
				<?php
				/** This action is documented in content.php */
				do_action( 'webpress_before_content_output', 'link' );

				the_excerpt();
				?>
			</div>

		<?php else : ?>

			<div class="entry-content"<?php echo $itemprop; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal attribute string built above; escaping would break the markup. ?>>
				<?php
				/** This action is documented in content.php */
				do_action( 'webpress_before_content_output', 'link' );

				the_content();

				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . __( 'Pages:', 'webpress' ),
						'after'  => '</div>',
					)
				);
				?>
			</div>

			<?php
		endif;

		/** This action is documented in content.php */
		do_action( 'webpress_after_entry_content', 'link' );

		webpress_footer_meta();

		/** This action is documented in content.php */
		do_action( 'webpress_after_content', 'link' );

		if ( $webpress_has_entry_body ) {
			echo '</div>';
		}
		?>
	</div>
</article>
