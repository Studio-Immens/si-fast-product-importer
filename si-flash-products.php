<?php

/**
 Plugin Name:     SI Flash Products
 Description:     Flash Products is a powerful WordPress plugin that gives you instant access to a vast database of ready-to-import products for your WooCommerce store. Designed for e-commerce businesses looking to expand their product range quickly and efficiently, Flash Products allows you to import high-quality, pre-configured items with just a few clicks. No more manual data entry or product configuration—this plugin simplifies the process, saving you time and effort.
 Version:         1.0.0
 Author:          Studio Immens
 Text Domain:     si-flash-products
 License:         GPL v.3
 License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 Domain Path:     /languages
 Requires PHP:    7.4
 */

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

define( 'SIFProd_VERSION', '1.0.0' );
define( 'SIFProd_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SIFProd_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


include_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	add_action( 'admin_notices', 'FP_admin_notice_woocommerce_plugin_error' );
	return;
}
function FP_admin_notice_woocommerce_plugin_error() {
	?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'ERROR! Flash Products needs the Woocommerce Plugin installed and active to work properly', 'si-flash-products' ); ?></p>
		</div>
	<?php
}

function FProd_init(){
	FP_load_dependencies();
}
FProd_init();

function FP_load_dependencies() {
	require_once dirname( __FILE__ ) . '/functions.php';
}

function FP_load_assets( $hook ) {
	// Carica gli asset solo nelle pagine del plugin
	if ( strpos( $hook, 'flash_products' ) === false ) {
		return;
	}

	wp_enqueue_style( 'Flash-Products-style-css', SIFProd_PLUGIN_URL . '/style.css', array(), SIFProd_VERSION, 'all' );
	
	wp_enqueue_script( 'Flash-Products-functions-js', SIFProd_PLUGIN_URL . '/functions.js', array( 'jquery' ), SIFProd_VERSION, true );

	// Localizzazione per AJAX
	wp_localize_script( 'Flash-Products-functions-js', 'fp_ajax', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'fp_import_nonce' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'FP_load_assets' );

// Rimosse le vecchie funzioni di caricamento asset non ottimizzate
// add_action( 'wp_enqueue_scripts', 'FP_load_style' );
// add_action( 'admin_enqueue_scripts', 'FP_load_style' );
// add_action( 'wp_enqueue_scripts', 'FP_load_animation' );
// add_action( 'admin_enqueue_scripts', 'FP_load_animation' );




register_activation_hook( __FILE__, 'activate_flash_products' );
function activate_flash_products() {
	FP_create_meta_table();
}

register_deactivation_hook( __FILE__, 'deactivate_flash_products' );
function deactivate_flash_products() {

}

function FP_menu_page(){
    $menu_slug = 'flash_products';
    $position = (FP_get_meta( 'FP_menu_order') != '')?FP_get_meta( 'FP_menu_order') : '15';

    $link = SIFProd_PLUGIN_URL.'/includes/img/flash-products-logo-20.png';

    add_menu_page( 'FlashProducts', esc_html__( 'FlashProducts','si-flash-products'), 'manage_options', $menu_slug, 'FP_main_menu_page', $link, $position);
    add_submenu_page( $menu_slug, 'flash_products_generator', esc_html__( 'Generator', 'si-flash-products' ), 'manage_options', $menu_slug.'_generator', 'FP_sub_menu_page_generator' );
    add_submenu_page( $menu_slug, 'flash_products_settings', esc_html__( 'Settings', 'si-flash-products' ), 'manage_options', $menu_slug.'_settings', 'FP_sub_menu_page_settings' );
}
add_action( 'admin_menu', 'FP_menu_page' );

function FP_head_menu_page(){
    ?>
    <div id="FPadminContent">
    <h1 style="margin:30px 0px;display:flex;"> 
        <img src="<?php echo SIFProd_PLUGIN_URL.'/includes/img/flash-products-logo-512.png' ?>" width="50" height="50" alt="light logo">
        Flash Products 
        <!-- <button class="FPzero FPbutton" onclick="FPtutorialPage();" style="margin: 0px 20px 0px auto!important;padding: 0px 10px!important;"> tutorial </button>  -->
    </h1>
    <?php

}

function FP_nav_menu_page(){
    $color1 = 'var(--fp-main-color)';
    $color2 = 'var(--fp-bg3-color)';
    $FlashOrder_color = ( $_REQUEST['page'] == 'flash_products' )? $color1 : $color2;//phpcs:ignore
    $Generator_color = ( $_REQUEST['page'] == 'flash_products_generator' )? $color1 : $color2;//phpcs:ignore
    $Settings_color = ( $_REQUEST['page'] == 'flash_products_settings' )? $color1 : $color2;//phpcs:ignore
    FP_head_menu_page();
	// FP_debug($_REQUEST['page']);
    ?>
    <nav class="FPMainNav">
        <a href="admin.php?page=flash_products" class="FPMainNavEl" style="background-color: <?php echo esc_attr($FlashOrder_color); ?>;">
        <?php esc_html_e( 'FlashProducts', 'si-flash-products' ); ?></a>
        <a href="admin.php?page=flash_products_generator" class="FPMainNavEl" style="background-color: <?php echo esc_attr($Generator_color); ?>;">
        <?php esc_html_e( 'Generator', 'si-flash-products' ); ?></a>
        <a href="admin.php?page=flash_products_settings" class="FPMainNavEl" style="background-color: <?php echo esc_attr($Settings_color); ?>;">
        <?php esc_html_e( 'Settings', 'si-flash-products' ); ?></a>
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
	include( SIFProd_PLUGIN_PATH . 'pages/main.php');
	FP_foot_menu_page();
}

function FP_sub_menu_page_generator(){
	FP_nav_menu_page();
	include( SIFProd_PLUGIN_PATH . 'pages/generator.php');
	FP_foot_menu_page();
}

function FP_sub_menu_page_settings(){
	FP_nav_menu_page();
	include( SIFProd_PLUGIN_PATH . 'pages/settings.php');
	FP_foot_menu_page();
}




