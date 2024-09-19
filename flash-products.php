<?php

/**
 * @package   Flash_Products
 * @author    Mauro Arnone <mauro.arnone.ma@gmail.com>
 * @copyright InnovazioneWeb
 * @license   GPL v.3
 * @link      innovazioneweb.com
 *
 * Plugin Name:     IW Flash Products
 * Plugin URI:      innovazioneweb.com/flash-products
 * Description:     Flash Products is a powerful WordPress plugin that gives you instant access to a vast database of ready-to-import products for your WooCommerce store. Designed for e-commerce businesses looking to expand their product range quickly and efficiently, Flash Products allows you to import high-quality, pre-configured items with just a few clicks. No more manual data entry or product configuration—this plugin simplifies the process, saving you time and effort.
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
			<p><?php esc_html_e( 'ERROR! Flash Products per funzionare correttamente ha bisogno che il Plugin Woocommerce sia installato e attivo', FProd_TEXTDOMAIN ); ?></p>
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
		<p><?php esc_html_e( 'ERROR! Flash Products per funzionare correttamente ha bisogno che il Plugin IW Flash Order sia installato e attivo', FProd_TEXTDOMAIN ); ?></p>
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
add_action( 'admin_enqueue_scripts', 'FP_load_style' );

function FP_load_animation() {
	wp_enqueue_script( 'Flash-Products-functions-js', plugin_dir_url( __FILE__ ) . '/functions.js', array( 'jquery' ), NULL, false );
}
add_action( 'wp_enqueue_scripts', 'FP_load_animation' );
add_action( 'admin_enqueue_scripts', 'FP_load_animation' );


// add_action( 'admin_enqueue_scripts', 'WOP_load_wp_media_files' );
// function WOP_load_wp_media_files( $page ) {
//     wp_enqueue_media();
// }




 /**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
register_activation_hook( __FILE__, 'activate_flash_products' );
function activate_flash_products() {
	FP_create_meta_table();
}

 /**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
register_deactivation_hook( __FILE__, 'deactivate_flash_products' );
function deactivate_flash_products() {

}



function FP_menu_page(){
    $menu_slug = 'flash_products';
    $position = (FP_get_meta( 'FP_menu_order') != '')?FP_get_meta( 'FP_menu_order') : '15';

    $link = get_home_url().'/wp-content/plugins/Flash-Products/includes/img/flash-products-logo-20.png';

   add_menu_page( 'FlashProducts', esc_html__( 'FlashProducts', 'flash-products' ), 'manage_options', $menu_slug, 'FP_main_menu_page', $link, $position );
   add_submenu_page( $menu_slug, 'flash_products_settings', esc_html__( 'Settings', 'flash-products' ), 'manage_options', $menu_slug.'_settings', 'FP_sub_menu_page_settings' );
}
add_action( 'admin_menu', 'FP_menu_page' );

function FP_head_menu_page(){
    ?>
    <div id="FPadminContent">
    <h1 style="margin:30px 0px;display:flex;"> 
        <img src="https://innovazioneweb.com/wp-content/uploads/2023/10/cropped-logo-512-transparent-bg.png" width="50" height="50" alt="light logo">
        Flash Products 
		<img src="https://innovazioneweb.com/wp-content/uploads/2024/09/flash-products-logo-512.png" width="50" height="50" alt="light logo">
        <!-- <button class="FPzero FPbutton" onclick="FPtutorialPage();" style="margin: 0px 20px 0px auto!important;padding: 0px 10px!important;"> tutorial </button>  -->
    </h1>
    <?php

}
function FP_nav_menu_page(){
    $color1 = 'var(--fp-main-color)';
    $color2 = 'var(--fp-bg3-color)';
    $FlashOrder_color = ( $_REQUEST['page'] == 'flash_products' )? $color1 : $color2;//phpcs:ignore
    $Settings_color = ( $_REQUEST['page'] == 'flash_products_settings' )? $color1 : $color2;//phpcs:ignore
    FP_head_menu_page();
	// FP_debug($_REQUEST['page']);
    ?>
    <nav class="FPMainNav">
        <a href="admin.php?page=flash_products" class="FPMainNavEl" style="background-color: <?php echo esc_attr($FlashOrder_color); ?>;">
        <?php esc_html_e( 'FlashProducts', 'flash_order' ); ?></a>
        <a href="admin.php?page=flash_products_settings" class="FPMainNavEl" style="background-color: <?php echo esc_attr($Settings_color); ?>;">
        <?php esc_html_e( 'Settings', 'flash_order' ); ?></a>
    </nav>
    <?php
}
function FP_foot_menu_page( $debug = false ){
    ?>
    </div>
    <?php
    if ( $debug ) {
        FP_debug( $_POST );//phpcs:ignore
    }
}

function FP_main_menu_page(){
	FP_nav_menu_page();
	include( plugin_dir_path( __FILE__ ) . 'pages/main.php');
	FP_foot_menu_page();
	}
function FP_sub_menu_page_settings(){
	FP_nav_menu_page();
	include( plugin_dir_path( __FILE__ ) . 'pages/settings.php');
	FP_foot_menu_page();
	}




