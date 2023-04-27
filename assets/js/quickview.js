/*!
 * Plugin methods
 *
 * WooCommerce Quickview 1.1.0
 */
/* jshint -W062 */

/* global WolfWCQuickViewParams, wc_add_to_cart_variation_params */
var WolfWCQuickView = function( $ ) {

	'use strict';

	return {
		initFlag : false,
		isWVC : 'undefined' !== typeof WVC,
		isMobile : ( navigator.userAgent.match( /(iPad)|(iPhone)|(iPod)|(Android)|(PlayBook)|(BB10)|(BlackBerry)|(Opera Mini)|(IEMobile)|(webOS)|(MeeGo)/i ) ) ? true : false,

		/**
		 * Init all functions
		 */
		init : function () {

			if ( this.initFlag ) {
				return;
			}

			this.isMobile = WolfWCQuickViewParams.isMobile;

			this.quickView();
			this.addToCart();
			this.closeButton();

			this.initFlag = true;
		},

		/**
		 * Product quickview
		 */
		quickView : function () {

			var _this = this;

			$( document ).on( 'click', '.wwcq-product-quickview-button', function( event ) {
				event.preventDefault();

				var productId = $( this ).data( 'product-id' ),
					$overlay = $( '#wwcq-product-quickview-overlay' ),
					$ajaxContainer = $( '#wwcq-product-quickview-ajax-content' ),
					data;

				data = {
					action : 'wwcq_ajax_product_quickview_content',
					productId : productId
				};

				$overlay.fadeIn();

				// AJAX request
				$.post( WolfWCQuickViewParams.ajaxUrl, data, function( response ) {

					//console.log( response );

					if ( response ) {

						$ajaxContainer.html( response ).find( '.product-images' ).flexslider( {
							animation: 'slide',
							controlNav: false,
							directionNav: true,
							slideshow : false,
							start: function() {

								setTimeout( function() {

									// if ( typeof wc_add_to_cart_variation_params !== 'undefined' ) {
									// 	$ajaxContainer.find( '.variations_form' ).each( function() {
									// 		$( this ).wc_variation_form();
									// 	} );
									// }


									/* Variation swatch plugin */
                                    if ( $.fn.tawcvs_variation_swatches_form ) {
                                        var $variations_form = $ajaxContainer.find( '.variations_form:not(.swatches-support)' );
                                        if ($variations_form.length > 0) {
                                            $variations_form.each(function () {
                                                $(this).wc_variation_form();
                                            });
                                            $variations_form.tawcvs_variation_swatches_form();
                                        }
                                    }

									$overlay.addClass( 'wwcq-product-quickview-loaded' );
									$( window ).trigger( 'wwcq_product_quickview_loaded' );
								}, 500 );
							}
						} );
					}
				} );
			} );
		},

		/**
		 * Add to cart event
		 */
		addToCart : function () {
			$( document ).on( 'added_to_cart', function( event, fragments, cart_hash, $button ) {
				if ( $button.hasClass( 'wwcq-product-add-to-cart' ) ) {
					$button.attr( 'href', WolfWCQuickViewParams.WooCommerceCartUrl );
					$button.find( 'span' ).attr( 'title', WolfWCQuickViewParams.l10n.viewCart );
					$button.removeClass( 'ajax_add_to_cart' );
				}
			} );
		},

		/**
		 * Close button
		 */
		closeButton : function () {

			var _this = this;

			$( document ).on( 'click', '.wwcq-quickview-close', function( event ) {
				event.preventDefault();

				_this.close();
			} );

			$( document ).mouseup( function( event ) {

				if ( 1 !== event.which ) {
					return;
				}

				var $container = $( '#wwcq-product-quickview-ajax-content' );

				if ( ! $container.is( event.target ) && $container.has( event.target ).length === 0 ) {
					_this.close();
				}
			} );
		},

		/**
		 * Close action
		 */
		close : function() {
			$( '#wwcq-product-quickview-overlay' ).fadeOut( 500, function() {
				$( '#wwcq-product-quickview-ajax-content' ).empty();
				$( '#wwcq-product-quickview-overlay' ).removeClass( 'wwcq-product-quickview-loaded' );
			} );
		}
	};

}( jQuery );

( function( $ ) {

	'use strict';

	$( document ).ready( function() {
		WolfWCQuickView.init();
	} );

} )( jQuery );
