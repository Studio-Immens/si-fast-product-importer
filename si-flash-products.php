<?php

/**
 Plugin Name:     SI Flash Products
 Description:     Flash Products is a powerful WordPress plugin that gives you instant access to a vast database of ready-to-import products for your WooCommerce store. Designed for e-commerce businesses looking to expand their product range quickly and efficiently, Flash Products allows you to import high-quality, pre-configured items with just a few clicks. No more manual data entry or product configuration—this plugin simplifies the process, saving you time and effort.
 Version:         1.0.0
 Author:          Mauro Arnone
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
	add_action( 'admin_notices', 'sifp_admin_notice_woocommerce_plugin_error' );
	return;
}
function sifp_admin_notice_woocommerce_plugin_error() {
	?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'ERROR! Flash Products needs the Woocommerce Plugin installed and active to work properly', 'si-flash-products' ); ?></p>
		</div>
	<?php
}

function sifp_init(){
	load_plugin_textdomain( 'si-flash-products', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	sifp_load_dependencies();
}
add_action( 'plugins_loaded', 'sifp_init' );

function sifp_load_dependencies() {
	require_once dirname( __FILE__ ) . '/functions.php';
}

function sifp_load_assets( $hook ) {
	// Carica gli asset solo nelle pagine del plugin
	if ( strpos( $hook, 'flash_products' ) === false ) {
		return;
	}

	wp_enqueue_style( 'Flash-Products-style-css', SIFProd_PLUGIN_URL . '/style.css', array(), SIFProd_VERSION, 'all' );
	
	// Carica il media uploader di WordPress
	wp_enqueue_media();
	
	wp_enqueue_script( 'Flash-Products-functions-js', SIFProd_PLUGIN_URL . '/functions.js', array( 'jquery' ), SIFProd_VERSION, true );

	// Localizzazione per AJAX e Settings
	wp_localize_script( 'Flash-Products-functions-js', 'sifp_ajax', array(
		'ajax_url'      => admin_url( 'admin-ajax.php' ),
		'nonce'         => wp_create_nonce( 'sifp_nonce' ),
		'sku_prefix'    => sifp_get_setting('sifp_sku_prefix', 'PROD-'),
		'default_stock' => sifp_get_setting('sifp_default_stock', '10'),
		'strings'       => array(
			'confirm_bulk'        => __( 'Are you sure you want to import %d products?', 'si-flash-products' ),
			'bulk_success'        => __( 'products imported successfully!', 'si-flash-products' ),
			'confirm_bulk_import' => __( 'Are you sure you want to import %d products?', 'si-flash-products' ),
			'bulk_import_done'    => __( 'Import completed: %d success, %d failure.', 'si-flash-products' ),
			'confirm_clear_logs'  => __( 'Are you sure you want to clear all logs?', 'si-flash-products' ),
			'error_clear_logs'    => __( 'Error while clearing logs', 'si-flash-products' ),
			'error_missing_name'  => __( 'Please enter at least the product name', 'si-flash-products' ),
			'ai_gen_success'      => __( 'Content generated successfully!', 'si-flash-products' ),
			'attr_limit_reached'  => __( 'At least one attribute must be present (even if empty).', 'si-flash-products' ),
			'importing'           => __( 'Importing...', 'si-flash-products' ),
			'generating'          => __( 'Generating...', 'si-flash-products' ),
			'error_ai_call'       => __( 'Error during AI call', 'si-flash-products' ),
			'error_import'        => __( 'Error during import', 'si-flash-products' ),
			'preset_loaded'       => __( 'Preset loaded!', 'si-flash-products' ),
			'select_image'        => __( 'Select Product Image', 'si-flash-products' ),
			'use_image'           => __( 'Use this image', 'si-flash-products' ),
			'select_gallery'      => __( 'Select Gallery Images', 'si-flash-products' ),
			'add_to_gallery'      => __( 'Add to gallery', 'si-flash-products' ),
			'error'               => __( 'Error', 'si-flash-products' ),
			'fetch_error'         => __( 'Failed to fetch products', 'si-flash-products' ),
		)
	) );
}
add_action( 'admin_enqueue_scripts', 'sifp_load_assets' );

register_activation_hook( __FILE__, 'activate_flash_products' );
function activate_flash_products() {
	// No longer needs custom table creation
}

register_deactivation_hook( __FILE__, 'deactivate_flash_products' );
function deactivate_flash_products() {

}

function sifp_menu_page(){
    $menu_slug = 'flash_products';
    $position = sifp_get_setting('sifp_menu_order', '15');

    $link = SIFProd_PLUGIN_URL.'/includes/img/flash-products-logo-20.png';

    add_menu_page( 'FlashProducts', esc_html__( 'FlashProducts','si-flash-products'), 'manage_options', $menu_slug, 'sifp_main_menu_page', $link, $position);
    add_submenu_page( $menu_slug, 'flash_products_generator', esc_html__( 'Generator', 'si-flash-products' ), 'manage_options', $menu_slug.'_generator', 'sifp_sub_menu_page_generator' );
    add_submenu_page( $menu_slug, 'flash_products_settings', esc_html__( 'Settings', 'si-flash-products' ), 'manage_options', $menu_slug.'_settings', 'sifp_sub_menu_page_settings' );
}
add_action( 'admin_menu', 'sifp_menu_page' );

function sifp_head_menu_page(){
    ?>
    <div id="sifp-admin-content">
    <h1 style="margin:30px 0px;display:flex;"> 
        <img src="<?php echo SIFProd_PLUGIN_URL.'/includes/img/flash-products-logo-512.png' ?>" width="50" height="50" alt="light logo">
        Flash Products 
    </h1>
    <?php

}

function sifp_nav_menu_page(){
    $color1 = 'var(--sifp-primary)';
    $color2 = 'var(--sifp-bg-nav)';
    $page = isset($_REQUEST['page']) ? sanitize_key($_REQUEST['page']) : '';
    $sifp_main_color = ( $page == 'flash_products' )? $color1 : $color2;
    $sifp_generator_color = ( $page == 'flash_products_generator' )? $color1 : $color2;
    $sifp_settings_color = ( $page == 'flash_products_settings' )? $color1 : $color2;
    sifp_head_menu_page();
    ?>
    <nav class="sifp-main-nav">
        <a href="admin.php?page=flash_products" class="sifp-main-nav-el" style="background-color: <?php echo esc_attr($sifp_main_color); ?>;">
        <?php esc_html_e( 'FlashProducts', 'si-flash-products' ); ?></a>
        <a href="admin.php?page=flash_products_generator" class="sifp-main-nav-el" style="background-color: <?php echo esc_attr($sifp_generator_color); ?>;">
        <?php esc_html_e( 'Generator', 'si-flash-products' ); ?></a>
        <a href="admin.php?page=flash_products_settings" class="sifp-main-nav-el" style="background-color: <?php echo esc_attr($sifp_settings_color); ?>;">
        <?php esc_html_e( 'Settings', 'si-flash-products' ); ?></a>
    </nav>
    <?php
}

function sifp_foot_menu_page(){
    ?>
    </div>
    <?php
}

function sifp_main_menu_page(){
	sifp_nav_menu_page();
	include( SIFProd_PLUGIN_PATH . 'pages/main.php');
	sifp_foot_menu_page();
}

function sifp_sub_menu_page_generator(){
	sifp_nav_menu_page();
	include( SIFProd_PLUGIN_PATH . 'pages/generator.php');
	sifp_foot_menu_page();
}

function sifp_sub_menu_page_settings(){
	sifp_nav_menu_page();
	include( SIFProd_PLUGIN_PATH . 'pages/settings.php');
	sifp_foot_menu_page();
}




