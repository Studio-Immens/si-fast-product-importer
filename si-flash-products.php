<?php
/**
 * Plugin Name:     SI Flash Products
 * Description:     Flash Products is a powerful WordPress plugin that gives you instant access to a vast database of ready-to-import products for your WooCommerce store. Designed for e-commerce businesses looking to expand their product range quickly and efficiently, Flash Products allows you to import high-quality, pre-configured items with just a few clicks.
 * Version:         1.1.0
 * Author:          Mauro Arnone
 * Text Domain:     si-flash-products
 * License:         GPLv2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:     /languages
 * Requires PHP:    7.4
 * Requires at least: 5.8
 * Requires Plugins: woocommerce
 * WC requires at least: 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register with WP Consent API to declare support and follow guidelines.
 */
add_filter( 'wp_consent_api_registered_' . plugin_basename( __FILE__ ), '__return_true' );

// Define SIFProd_FILE
if ( ! defined( 'SIFProd_FILE' ) ) {
	define( 'SIFProd_FILE', __FILE__ );
}

/**
 * Autoloader for SIFlashProducts namespace
 */
spl_autoload_register( function ( $class ) {
	$prefix = 'SIFlashProducts\\';
	$base_dir = __DIR__ . '/includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

/**
 * Initialize the plugin
 */
function sifp_init_plugin() {
	// Let the Core\Plugin class handle includes and initialization
	return \SIFlashProducts\Core\Plugin::instance();
}

sifp_init_plugin();
