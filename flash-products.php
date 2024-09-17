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

define( 'FProd_VERSION', '1.0.0' );
define( 'FProd_TEXTDOMAIN', 'flash-products' );
define( 'FProd_NAME', 'Flash_Products' );
define( 'FProd_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'FProd_PLUGIN_ABSOLUTE', __FILE__ );
define( 'FProd_MIN_PHP_VERSION', '7.4' );
define( 'FProd_WP_VERSION', '5.3' );


include_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	add_action( 'admin_notices', 'FP_admin_notice_woocommerce_plugin_error' );
	return;
}
function FP_admin_notice_woocommerce_plugin_error() {
	?>
		<div class="notice notice-error">
			<p><?php _e( 'ERRORE! WooPrint per funzionare correttamente ha bisogno che il Plugin Woocommerce sia installato e attivo', 'WooPrint' ); ?></p>
		</div>
	<?php
}

// if ( !is_plugin_active( 'flash_order/flash_order.php' ) ) {
// 	add_action( 'admin_notices', 'FP_admin_notice__plugin_base_error' );
// 	return;
// }
function FP_admin_notice__plugin_base_error() {
?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'ERRORE! WooPrint per funzionare correttamente ha bisogno che il Plugin IW Flash Order sia installato e attivo', 'WooPrint' ); ?></p>
	</div>
<?php
}




add_action( 'init', static function () {
	load_plugin_textdomain( FProd_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );


function FProd_init(){
	FP_load_dependencies();
}
FProd_init();

function FP_load_dependencies() {
	require_once dirname( __FILE__ ) . '/functions.php';
}

function FP_load_style() {
	wp_enqueue_style( 'Flash-Products-style-css', plugin_dir_url( __FILE__ ).'/style.css', false, NULL, 'all' );
}
add_action( 'wp_enqueue_scripts', 'FP_load_style' );
// add_action( 'admin_enqueue_scripts', 'FP_load_style' );

function FP_load_animation() {
	wp_enqueue_script( 'Flash-Products-functions-js', plugin_dir_url( __FILE__ ) . '/functions.js', array( 'jquery' ), NULL, false );
}
add_action( 'wp_enqueue_scripts', 'FP_load_animation' );
// add_action( 'admin_enqueue_scripts', 'FP_load_animation' );


// add_action( 'admin_enqueue_scripts', 'WOP_load_wp_media_files' );
// function WOP_load_wp_media_files( $page ) {
//     wp_enqueue_media();
// }




