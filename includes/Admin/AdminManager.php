<?php
namespace SIFlashProducts\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Management Class
 */
class AdminManager {

    /**
     * Instance
     * @var AdminManager
     */
    protected static $_instance = null;

    /**
     * Instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Register Menu
     */
    public function register_menu() {
        add_menu_page(
            __( 'Flash Products', 'si-flash-products' ),
            __( 'Flash Products', 'si-flash-products' ),
            'manage_options',
            'flash_products',
            array( $this, 'render_main_page' ),
            'dashicons-products',
            30
        );

        add_submenu_page(
            'flash_products',
            __( 'Generator', 'si-flash-products' ),
            __( 'Generator', 'si-flash-products' ),
            'manage_options',
            'flash_products_generator',
            array( $this, 'render_generator_page' )
        );

        add_submenu_page(
            'flash_products',
            __( 'Settings', 'si-flash-products' ),
            __( 'Settings', 'si-flash-products' ),
            'manage_options',
            'flash_products_settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Enqueue Assets
     */
    public function enqueue_assets( $hook ) {
        $is_product_edit = in_array( $hook, array( 'post.php', 'post-new.php' ), true )
            && function_exists( 'get_current_screen' )
            && get_current_screen()
            && 'product' === get_current_screen()->post_type;

        if ( strpos( $hook, 'flash_products' ) === false && ! $is_product_edit ) {
            return;
        }

        wp_enqueue_style( 'sifp-admin-style', SIFProd_URL . 'style.css', array(), SIFProd_VERSION );
        wp_enqueue_media();
        wp_enqueue_script( 'sifp-admin-functions', SIFProd_URL . 'functions.js', array( 'jquery' ), SIFProd_VERSION, true );

        // Enqueue settings JS only on settings page
        if ( strpos( $hook, 'flash_products_settings' ) !== false ) {
            wp_enqueue_script( 'sifp-settings', SIFProd_URL . 'assets/settings.js', array( 'jquery' ), SIFProd_VERSION, true );
        }

        wp_localize_script( 'sifp-admin-functions', 'sifp_ajax', array(
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'sifp_nonce' ),
            'sku_prefix'    => get_option( 'sifp_sku_prefix', 'PROD-' ),
            'default_stock' => get_option( 'sifp_default_stock', '10' ),
            'strings'       => array(
                'confirm_bulk'        => __( 'Are you sure you want to import %d products?', 'si-flash-products' ),
                'bulk_success'        => __( 'products imported successfully!', 'si-flash-products' ),
                'confirm_bulk_import' => __( 'Are you sure you want to import %d products?', 'si-flash-products' ),
                'bulk_import_done'    => __( 'Import completed: %d success, %d failure.', 'si-flash-products' ),
                'confirm_clear_logs'  => __( 'Are you sure you want to clear all logs?', 'si-flash-products' ),
                'logs_cleared'        => __( 'Logs cleared successfully!', 'si-flash-products' ),
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
                'use_image'           => __( 'Use Image', 'si-flash-products' ),
                'select_gallery'      => __( 'Select Gallery Images', 'si-flash-products' ),
                'add_to_gallery'      => __( 'Add to gallery', 'si-flash-products' ),
                'error'               => __( 'Error', 'si-flash-products' ),
                'fetch_error'         => __( 'Failed to fetch products', 'si-flash-products' ),
                'no_results'          => __( 'No products found matching your search.', 'si-flash-products' ),
            )
        ) );
    }

    /**
     * Render Main Page
     */
    public function render_main_page() {
        include_once SIFProd_PATH . 'pages/main.php';
    }

    /**
     * Render Generator Page
     */
    public function render_generator_page() {
        include_once SIFProd_PATH . 'pages/generator.php';
    }

    /**
     * Render Settings Page
     */
    public function render_settings_page() {
        include_once SIFProd_PATH . 'pages/settings.php';
    }
}
