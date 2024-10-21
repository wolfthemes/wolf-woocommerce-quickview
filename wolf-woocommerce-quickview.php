<?php
/**
 * Plugin Name: WooCommerce Quickview
 * Plugin URI: https://wolfthemes.com/plugin/wolf-woocommerce-quickview
 * Description: Adds WooCommerce product quickview to your themes.
 * Version: 1.1.6
 * Author: WolfThemes
 * Author URI: http://wolfthemes.com
 * Requires at least: 6.0
 * Tested up to: 6.6
 *
 * Text Domain: wolf-woocommerce-quickview
 * Domain Path: /languages/
 *
 * WC requires at least: 3.0
 * WC tested up to: 4.0
 *
 * @package WolfWooCommerceQuickview
 * @category Core
 * @author WolfThemes
 *
 * Being a free product, this plugin is distributed as-is without official support.
 * Verified customers however, who have purchased a premium theme
 * at https://themeforest.net/user/Wolf-Themes/portfolio?ref=Wolf-Themes
 * will have access to support for this plugin in the forums
 * https://wolfthemes.ticksy.com/
 *
 * Copyright (C) 2017 Constantin Saguin
 * This WordPress Plugin is a free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * It is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * See https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Wolf_WooCommerce_Quickview' ) ) {
	/**
	 * Main Wolf_WooCommerce_Quickview Class
	 *
	 * Contains the main functions for Wolf_WooCommerce_Quickview
	 *
	 * @class Wolf_WooCommerce_Quickview
	 * @version 1.1.6
	 * @since 1.0.0
	 */
	class Wolf_WooCommerce_Quickview {

		/**
		 * @var string
		 */
		public $version = '1.1.6';

		/**
		 * @var WooCommerce Quickview The single instance of the class
		 */
		protected static $_instance = null;

		/**
		 * @var string
		 */
		private $update_url = 'https://plugins.wolfthemes.com/update';

		/**
		 * @var the support forum URL
		 */
		private $support_url = 'https://help.wolfthemes.com/';

		/**
		 * @var string
		 */
		public $template_url;

		/**
		 * Main WooCommerce Quickview Instance
		 *
		 * Ensures only one instance of WooCommerce Quickview is loaded or can be loaded.
		 *
		 * @static
		 * @see WVCCB()
		 * @return WooCommerce Quickview - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * WooCommerce Quickview Constructor.
		 */
		public function __construct() {

			/* Don't do anything if WC is not activated */
			if ( ! $this->is_woocommerce_active() ) {
				return;
			}

			add_action( 'before_woocommerce_init', function() {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
				}
			} );

			$this->define_constants();
			$this->includes();
			$this->init_hooks();

			// Plugin update notifications
			add_action( 'admin_init', array( $this, 'plugin_update' ) );
		}

		/**
		 * Check if WooCommerce is active
		 *
		 * @see https://docs.woocommerce.com/document/create-a-plugin/
		 * @return bool
		 */
		public function is_woocommerce_active() {
			return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
		}

		/**
		 * Hook into actions and filters
		 */
		private function init_hooks() {
			add_action( 'init', array( $this, 'init' ), 0 );
		}

		/**
		 * Define WR Constants
		 */
		private function define_constants() {

			$constants = array(
				'WWCQ_DEV' => false,
				'WWCQ_DIR' => $this->plugin_path(),
				'WWCQ_URI' => $this->plugin_url(),
				'WWCQ_CSS' => $this->plugin_url() . '/assets/css',
				'WWCQ_JS' => $this->plugin_url() . '/assets/js',
				'WWCQ_SLUG' => plugin_basename( dirname( __FILE__ ) ),
				'WWCQ_PATH' => plugin_basename( __FILE__ ),
				'WWCQ_VERSION' => $this->version,
				'WWCQ_UPDATE_URL' => $this->update_url,
				'WWCQ_SUPPORT_URL' => $this->support_url,
			);

			foreach ( $constants as $name => $value ) {
				$this->define( $name, $value );
			}
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {

			if ( ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) ) {
				include_once( 'inc/frontend-functions.php' );
			}

			if ( defined( 'DOING_AJAX' ) ) {
				include_once( 'inc/ajax-functions.php' );
			}
		}

		/**
		 * Init WooCommerce Quickview when WordPress Initialises.
		 */
		public function init() {

			// Set up localisation
			$this->load_plugin_textdomain();
		}

		/**
		 * Loads the plugin text domain for translation
		 */
		public function load_plugin_textdomain() {

			$domain = 'wolf-woocommerce-quickview';
			$locale = apply_filters( 'wolf-woocommerce-quickview', get_locale(), $domain );
			load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Plugin update
		 */
		public function plugin_update() {
			$plugin_slug = WWCQ_SLUG;
			$plugin_path = WWCQ_PATH;
			$remote_path = WWCQ_UPDATE_URL . '/' . $plugin_slug;
			$plugin_data = get_plugin_data( WWCQ_DIR . '/' . WWCQ_SLUG . '.php' );
			$current_version = $plugin_data['Version'];
			include_once( WWCQ_DIR . '/inc/admin/lib/class-update.php');
			new Wolf_WooCommerce_Quickview_Update( $current_version, $remote_path, $plugin_path );
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
	} // end class
} // end class check

/**
 * Returns the main instance of Wolf_WooCommerce_Quickview to prevent the need to use globals.
 *
 * @return Wolf_WooCommerce_Quickview
 */
function WOLFWCQUICKVIEW() {
	return Wolf_WooCommerce_Quickview::instance();
}

WOLFWCQUICKVIEW(); // Go
