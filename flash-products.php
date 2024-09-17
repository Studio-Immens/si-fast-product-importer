<?php

/**
 * @package   Flash_Products
 * @author    Mauro Arnone <mauro.arnone.ma@gmail.com>
 * @copyright InnovazioneWeb
 * @license   GPL v.3
 * @link      innovazioneweb.com
 *
 * Plugin Name:     Flash_Products
 * Plugin URI:      innovazioneweb.com/flash-products
 * Description:     @TODO
 * Version:         1.0.0
 * Author:          InnovazioneWeb
 * Author URI:      innovazioneweb.com
 * Text Domain:     flash-products
 * License:         GPL v.3
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:     /languages
 * Requires PHP:    7.4
 */

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

define( 'PN_VERSION', '1.0.0' );
define( 'PN_TEXTDOMAIN', 'flash-products' );
define( 'PN_NAME', 'Flash_Products' );
define( 'PN_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'PN_PLUGIN_ABSOLUTE', __FILE__ );
define( 'PN_MIN_PHP_VERSION', '7.4' );
define( 'PN_WP_VERSION', '5.3' );

add_action(
	'init',
	static function () {
		load_plugin_textdomain( PN_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

if ( version_compare( PHP_VERSION, PN_MIN_PHP_VERSION, '<=' ) ) {
	add_action(
		'admin_init',
		static function() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	add_action(
		'admin_notices',
		static function() {
			echo wp_kses_post(
			sprintf(
				'<div class="notice notice-error"><p>%s</p></div>',
				__( '"Flash_Products" requires PHP 5.6 or newer.', PN_TEXTDOMAIN )
			)
			);
		}
	);
	// Return early to prevent loading the plugin.
	return;
}

$plugin_name_libraries = require PN_PLUGIN_ROOT . 'vendor/autoload.php'; //phpcs:ignore

require_once PN_PLUGIN_ROOT . 'functions/functions.php';

require_once PN_PLUGIN_ROOT . 'functions/debug.php';


$requirements = new \Micropackage\Requirements\Requirements(
	'Plugin Name',
	array(
		'php'            => PN_MIN_PHP_VERSION,
		'php_extensions' => array( 'mbstring' ),
		'wp'             => PN_WP_VERSION,
		// 'plugins'            => array(
		// array( 'file' => 'hello-dolly/hello.php', 'name' => 'Hello Dolly', 'version' => '1.5' )
		// ),
	)
);

if ( ! $requirements->satisfied() ) {
	$requirements->print_notice();
	return;
}

/**
 * Create a helper function for easy SDK access.
 *
 * @global type $pn_fs
 * @return object
 */
function pn_fs() {
	global $pn_fs;

	if ( !isset( $pn_fs ) ) {
		require_once PN_PLUGIN_ROOT . 'vendor/freemius/wordpress-sdk/start.php';
		$pn_fs = fs_dynamic_init(
			array(
				'id'             => '',
				'slug'           => 'plugin-name',
				'public_key'     => '',
				'is_live'        => false,
				'is_premium'     => true,
				'has_addons'     => false,
				'has_paid_plans' => true,
				'menu'           => array(
					'slug' => 'plugin-name',
				),
			)
		);

		if ( $pn_fs->is_premium() ) {
			$pn_fs->add_filter(
				'support_forum_url',
				static function ( $wp_org_support_forum_url ) { //phpcs:ignore
					return 'https://your-url.test';
				}
			);
		}
	}
	return $pn_fs;
}
// pn_fs();

Puc_v4_Factory::buildUpdateChecker( 'https://github.com/user-name/repo-name/', __FILE__, 'unique-plugin-or-theme-slug' );

if ( ! wp_installing() ) {
	register_activation_hook( PN_TEXTDOMAIN . '/' . PN_TEXTDOMAIN . '.php', array( new \Plugin_Name\Backend\ActDeact, 'activate' ) );
	register_deactivation_hook( PN_TEXTDOMAIN . '/' . PN_TEXTDOMAIN . '.php', array( new \Plugin_Name\Backend\ActDeact, 'deactivate' ) );
	add_action(
		'plugins_loaded',
		static function () use ( $plugin_name_libraries ) {
			new \Plugin_Name\Engine\Initialize( $plugin_name_libraries );
		}
	);
}




