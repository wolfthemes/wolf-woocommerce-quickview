<?php
/*
 * The quickview template file
  */

while ( have_posts() ) : the_post(); ?>

<div class="woocommerce">
	<div class="<?php echo apply_filters( 'wwcqv_single_product_quickview_class', 'single-product single-product-quickview' ); ?>">
		<article id="product-<?php the_ID(); ?>" <?php post_class( 'entry-single entry-single-product entry-product-quickview clearfix' ); ?>>

			<?php do_action( 'wwcqv_product_image' ); ?>

			<div class="summary entry-summary">
				<div class="summary-content">
					<?php do_action( 'wwcqv_product_summary' ); ?>
				</div>
			</div>
		</article>
	</div>
</div>

<?php endwhile; // end of the loop.