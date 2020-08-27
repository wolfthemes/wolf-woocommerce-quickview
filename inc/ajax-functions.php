<?php
/**
 * WooCommerce Quickview AJAX Functions
 *
 *
 * @author WolfThemes
 * @category Ajax
 * @package WolfWooCommerceQuickview/Functions
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get product quickview content
 */
function wwcq_ajax_product_quickview_content() {
	
	extract( $_POST );

	if ( ! isset( $_POST['productId'] ) ) {
		die();
	}

	$product_id = absint( $_REQUEST['productId'] );

	// set the main wp query for the product (cool)
	wp( 'p=' . $product_id . '&post_type=product' );

	ob_start();
	
	add_filter( 'woocommerce_gallery_thumbnail_size', 'wwcqv_filter_image_size' );
	add_filter( 'woocommerce_short_description', 'wwcqv_filter_product_description' );

	wc_get_template( 'quickview-content.php', array(), '', WWCQ_DIR . '/templates/' );

	remove_filter( 'woocommerce_gallery_thumbnail_size', 'wwcqv_filter_image_size' );
	remove_filter( 'woocommerce_short_description', 'wwcqv_filter_product_description' );

	echo ob_get_clean();

	exit();
}
add_action( 'wp_ajax_wwcq_ajax_product_quickview_content', 'wwcq_ajax_product_quickview_content' );
add_action( 'wp_ajax_nopriv_wwcq_ajax_product_quickview_content', 'wwcq_ajax_product_quickview_content' );