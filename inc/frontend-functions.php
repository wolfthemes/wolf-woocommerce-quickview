<?php
/**
 * WooCommerce Quickview frontend functions
 *
 * General functions available on frontend
 *
 * @author WolfThemes
 * @category Core
 * @package WolfWooCommerceQuickview/Frontend
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Product quickview div
 *
 * @param string $string
 * @return string
 */
function wwcq_output_product_quickview_block() {
	ob_start();
	wc_get_template( 'quickview-wrapper.php', array(), '', WWCQ_DIR . '/templates/' );
	echo ob_get_clean();
}
add_action( 'zample_body_start', 'wwcq_output_product_quickview_block' );
add_action( 'wolf_body_start', 'wwcq_output_product_quickview_block' );

/**
 * Quickview button
 *
 * @since 1.0.0
 */
function wolf_quickview_button() {
	$text = esc_html__( 'Quickview', 'wolf-woocommerce-quickview' );
	?>
	<a
	class="product-quickview-button wwcq-product-quickview-button"
	href="<?php the_permalink(); ?>"
	title="<?php echo esc_attr( $text ); ?>"
	rel="nofollow"
	data-product-title="<?php echo esc_attr( get_the_title() ); ?>"
	data-product-id="<?php the_ID(); ?>"><span class="fa fa-eye"></span></a>
	<?php
}

/**
 * Create a formatted sample of any text
 *
 * Remove HTML and shortcode, sanitize and shorten a string
 *
 * @param string $text
 * @param int $num_words
 * @param string $more
 * @return string
 */
function wwcq_sample( $text, $num_words = 18, $more = '...' ) {
	$text = wp_strip_all_tags( wp_trim_words( strip_shortcodes( $text ), $num_words, $more ) );
	$text = preg_replace( '/(http:|https:)?\/\/[a-zA-Z0-9\/.?&=-]+/', '', $text );
	return $text;
}

/**
 * Get the URL of an attachment from its id
 *
 * @param int $id
 * @param string $size
 * @return string $url
 */
function wwcq_get_url_from_attachment_id( $id, $size = 'large', $fallback = true ) {
	if ( is_numeric( $id ) ) {
		$src = wp_get_attachment_image_src( absint( $id ), $size );

		if ( isset( $src[0] ) ) {

			return esc_url( $src[0] );
		} else {
			return wvc_placeholder_img_url( $size );
		}
	}
}

/**
 * Enqeue styles and scripts
 *
 * @since 1.0.0
 */
function wwcq_enqueue_scripts() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
	
	// Styles
	wp_enqueue_style( 'wolf-woocommerce-quickview', WWCQ_CSS . '/quickview' . $suffix . '.css', array(), WWCQ_VERSION, 'all' );

	// Scripts
	wp_enqueue_script( 'wolf-woocommerce-quickview', WWCQ_JS . '/quickview' . $suffix . '.js', array( 'jquery' ), WWCQ_VERSION, true );

	if ( ! wp_script_is( 'wc-add-to-cart-variation' ) ) {
		$wc_assets_url = WC()->plugin_url();
		wp_register_script( 'wc-add-to-cart-variation', $wc_assets_url . '/assets/js/frontend/add-to-cart-variation' . $suffix . '.js', array( 'jquery', 'wp-util' ), WC_VERSION );
	}

	wp_enqueue_script( 'wc-add-to-cart' );
	wp_enqueue_script( 'wc-add-to-cart-variation' );

	if ( class_exists( 'TA_WC_Variation_Swatches' ) ) {
		$tawcvs_dir = plugins_url( 'variation-swatches-for-woocommerce' );
		wp_enqueue_style( 'tawcvs-frontend', $tawcvs_dir . '/assets/css/frontend.css', array(), WWCQ_VERSION );
		wp_enqueue_script( 'tawcvs-frontend', $tawcvs_dir . '/assets/js/frontend.js', 'variation-swatches-for-woocommerce', array( 'jquery' ), WWCQ_VERSION, true );
	}

	// Add JS global variables
	wp_localize_script(
		'wolf-woocommerce-quickview', 'WolfWCQuickViewParams', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'WooCommerceCartUrl' => ( function_exists( 'wc_get_cart_url' ) ) ? wc_get_cart_url() : '',
			'isMobile' => wp_is_mobile(),
			'l10n' => array(
				'viewCart' => esc_html__( 'View cart', 'wolf-woocommerce-quickview' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts',  'wwcq_enqueue_scripts' );

/**
 * Product images
 */
function wwcqv_show_product_images_bak() {
	wc_get_template( 'single-product/product-image.php' );
}

/**
 * Product images
 */
function wwcqv_show_product_images() {
	global $product;


	?>
	<div class="product-images flexslider">
		<?php
			
			do_action( 'wwcqv_product_images_start' );

			/**
			 * If gallery
			 */
			$attachment_ids = $product->get_gallery_image_ids();
			
			if ( is_array( $attachment_ids ) && ! empty( $attachment_ids ) ) {

				echo '<ul class="slides">';

				if ( has_post_thumbnail( $product_id ) ) {
					?>
					<li class="slide">
						<span class="slide-content">
								<?php echo $product->get_image( 'large' ); ?>
						</span>
					</li>
					<?php
				}
				
				foreach ( $attachment_ids as $attachment_id ) {
					if ( wp_attachment_is_image( $attachment_id ) ) {
						?>
						<li class="slide">
							<span class="slide-content">
								<?php
									echo wp_get_attachment_image( $attachment_id, 'large' );
								?>
							</span>
						</li>
						<?php
					}
				}

				echo '</ul>';

			/**
			 * If featured image only
			 */
			} elseif ( has_post_thumbnail( $product_id ) ) {
				?>
				<span class="slide-content">
						<?php echo $product->get_image( 'large' ); ?>
				</span>
				<?php

			/**
			 * Placeholder
			 */
			} else {
				
				$html  = '<span class="slide-content"><span class="woocommerce-product-gallery__image--placeholder">';
				$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src() ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
				$html .= '</span></span>';

				echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $attachment_id );
			}
		?>
	</div>
	<?php
}

/**
 * Load wc action for quick view product template
 */
function wwcqv_view_action_template() {

	// Image
	add_action( 'wwcqv_product_image', 'woocommerce_show_product_sale_flash', 10 );
	add_action( 'wwcqv_product_image', 'wwcqv_show_product_images', 20 );
	//add_action( 'wwcqv_product_image', 'woocommerce_show_product_images', 20 );

	// Summary
	
	add_action( 'wwcqv_product_summary', 'wwcqv_single_title', 5 );
	
	add_action( 'wwcqv_product_summary', 'woocommerce_template_single_rating', 10 );
	add_action( 'wwcqv_product_summary', 'woocommerce_template_single_price', 15 );
	add_action( 'wwcqv_product_summary', 'woocommerce_template_single_excerpt', 20 );
	
	/* Variation swatch plugin */
	if ( class_exists( 'TA_WC_Variation_Swatches_Frontend' ) ) {
		add_action( 'init', array( 'TA_WC_Variation_Swatches_Frontend', 'instance' ) );
	}
	

	add_action( 'wwcqv_product_summary', 'woocommerce_template_single_add_to_cart', 25 );
	//add_action( 'wwcqv_product_summary', 'woocommerce_template_single_meta', 30 );
}
wwcqv_view_action_template();

/**
 * Custom excerpt length
 *
 * @param $excerpt
 * @return $excerpt
 */
function wwcqv_filter_product_description( $excerpt ) {
	return wwcq_sample( $excerpt, apply_filters( 'wwcqv_excerpt_length', 18 ) );
}

/**
 * Filter image sizer
 *
 * @param $size
 * @return $size
 */
function wwcqv_filter_image_size( $size ) {
	return 'large';
}

/**
 * Product title linked to page
 */
function wwcqv_single_title() {
	the_title( '<h2 class="product_title entry-title"><a class="entry-link" href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
}

/**
 * Thumbnail cropping
 */
function wwcqv_inline_style() {
	
	$cropping = get_option( 'woocommerce_thumbnail_cropping', '1:1' );

	if ( 'custom' === $cropping ) {
		
		$w = max( 1, get_option( 'woocommerce_thumbnail_cropping_custom_width', '4' ) );
		$h = max( 1, get_option( 'woocommerce_thumbnail_cropping_custom_height', '3' ) );
	
	} elseif ( 'uncropped' === $cropping ) {
		
		$w = 3;
		$h = 4;

	} else {
		$scale = explode( ':', $cropping );
		$w = absint( $scale[0] );
		$h = absint( $scale[1] );
	}

	$padding_bottom = $h / $w * 100;

	$custom_css = "
		.wwcq-product-quickview-container .product-images .slide-content{
			padding-bottom:$padding_bottom%;
		}
	";
	wp_add_inline_style( 'wolf-woocommerce-quickview', $custom_css );
}
add_action( 'wp_enqueue_scripts',  'wwcqv_inline_style' );