<?php
/**
 * Featured image elements.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'webpress_post_image' ) ) {
	/**
	 * Prints the Post Image to post excerpts
	 *
	 * The templates call this both above and below the entry header, and the
	 * image only prints at the location that matches the blog post layout.
	 *
	 * @since 1.0.0
	 *
	 * @param string $location Where the template is calling from: above-title or below-title.
	 */
	function webpress_post_image( $location = 'below-title' ) {
		// If there's no featured image, return.
		if ( ! has_post_thumbnail() ) {
			return;
		}

		$blog_post_layout = webpress_get_blog_post_layout();

		/**
		 * Filters where the featured image sits in blog and archive listings.
		 *
		 * @since 1.0.0
		 *
		 * @param string $image_location   above-title or below-title.
		 * @param string $blog_post_layout The post layout: classic, list or grid.
		 * @return string Where to print the featured image.
		 */
		$image_location = apply_filters(
			'webpress_post_image_location',
			'classic' === $blog_post_layout ? 'below-title' : 'above-title',
			$blog_post_layout
		);

		if ( $location !== $image_location ) {
			return;
		}

		// If we're not on any single post/page or the 404 template, we must be showing excerpts.
		if ( ! is_singular() && ! is_404() ) {
			$attrs = array();

			if ( 'microdata' === webpress_get_schema_type() ) {
				$attrs = array(
					'itemprop' => 'image',
				);
			}

			/**
			 * Filters the image size used for featured images in listings.
			 *
			 * The list and grid layouts show smaller images, so they use a
			 * smaller size by default.
			 *
			 * @since 1.0.0
			 *
			 * @param string $size             A registered image size name.
			 * @param string $blog_post_layout The post layout: classic, list or grid.
			 * @return string The image size to use.
			 */
			$image_size = apply_filters(
				'webpress_post_image_size',
				'classic' === $blog_post_layout ? 'full' : 'medium_large',
				$blog_post_layout
			);

			echo sprintf(
				'<div class="post-image">
					%3$s
					<a href="%1$s">
						%2$s
					</a>
				</div>',
				esc_url( get_permalink() ),
				get_the_post_thumbnail(
					get_the_ID(),
					$image_size,
					$attrs
				),
				''
			);
		}
	}
}

if ( ! function_exists( 'webpress_featured_page_header_area' ) ) {
	/**
	 * Build the page header.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The featured image container class.
	 */
	function webpress_featured_page_header_area( $class ) {
		// Don't run the function unless we're on a page it applies to.
		if ( ! is_singular() ) {
			return;
		}

		// Don't run the function unless we have a post thumbnail.
		if ( ! has_post_thumbnail() ) {
			return;
		}

		$attrs = array();

		if ( 'microdata' === webpress_get_schema_type() ) {
			$attrs = array(
				'itemprop' => 'image',
			);
		}
		?>
		<div class="featured-image <?php echo esc_attr( $class ); ?> grid-container grid-parent">
			<?php
				the_post_thumbnail(
					'full',
					$attrs
				);
			?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'webpress_featured_page_header' ) ) {
	/**
	 * Add page header above content.
	 *
	 * @since 1.0.0
	 */
	function webpress_featured_page_header() {
		if ( function_exists( 'webpress_page_header' ) ) {
			return;
		}

		if ( is_page() ) {
			webpress_featured_page_header_area( 'page-header-image' );
		}
	}
}

if ( ! function_exists( 'webpress_featured_page_header_inside_single' ) ) {
	/**
	 * Add post header inside content.
	 * Only add to single post.
	 *
	 * @since 1.0.0
	 */
	function webpress_featured_page_header_inside_single() {
		if ( function_exists( 'webpress_page_header' ) ) {
			return;
		}

		if ( is_single() ) {
			webpress_featured_page_header_area( 'page-header-image-single' );
		}
	}
}
