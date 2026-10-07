<?php
/**
 * The template for displaying posts within the loop.
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

		/**
		 * Fires at the top of the article, before the entry header.
		 *
		 * @since 1.0.0
		 *
		 * @param string $context The template part being rendered.
		 */
		do_action( 'webpress_before_content', 'content' );

		if ( webpress_show_entry_header() ) :
			?>
			<header <?php webpress_do_attr( 'entry-header' ); ?>>
				<?php
				/**
				 * Fires inside the entry header, before the entry title.
				 *
				 * @since 1.0.0
				 *
				 * @param string $context The template part being rendered.
				 */
				do_action( 'webpress_before_entry_title', 'content' );

				if ( webpress_show_title() ) {
					$params = webpress_get_the_title_parameters();

					the_title( $params['before'], $params['after'] );
				}

				/**
				 * Fires inside the entry header, after the entry title.
				 *
				 * @since 1.0.0
				 *
				 * @param string $context The template part being rendered.
				 */
				do_action( 'webpress_after_entry_title', 'content' );

				webpress_post_meta();
				?>
			</header>
			<?php
		endif;

		/**
		 * Fires after the entry header, before the featured image.
		 *
		 * Fires even when the entry header itself is disabled.
		 *
		 * @since 1.0.0
		 *
		 * @param string $context The template part being rendered.
		 */
		do_action( 'webpress_after_entry_header', 'content' );

		webpress_post_image();

		$itemprop = '';

		if ( 'microdata' === webpress_get_schema_type() ) {
			$itemprop = ' itemprop="text"';
		}

		if ( webpress_show_excerpt() ) :
			?>

			<div class="entry-summary"<?php echo $itemprop; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal attribute string built above; escaping would break the markup. ?>>
				<?php
				/**
				 * Fires immediately before the post content or excerpt is output.
				 *
				 * @since 1.0.0
				 *
				 * @param string $context The template part being rendered.
				 */
				do_action( 'webpress_before_content_output', 'content' );

				the_excerpt();
				?>
			</div>

		<?php else : ?>

			<div class="entry-content"<?php echo $itemprop; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal attribute string built above; escaping would break the markup. ?>>
				<?php
				/** This action is documented in content.php */
				do_action( 'webpress_before_content_output', 'content' );

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

		/**
		 * Fires after the entry content or excerpt wrapper closes.
		 *
		 * @since 1.0.0
		 *
		 * @param string $context The template part being rendered.
		 */
		do_action( 'webpress_after_entry_content', 'content' );

		webpress_footer_meta();

		/**
		 * Fires at the bottom of the article, inside the article wrapper.
		 *
		 * @since 1.0.0
		 *
		 * @param string $context The template part being rendered.
		 */
		do_action( 'webpress_after_content', 'content' );

		if ( $webpress_has_entry_body ) {
			echo '</div>';
		}
		?>
	</div>
</article>
