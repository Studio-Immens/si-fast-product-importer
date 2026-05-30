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
        add_action( 'admin_head', array( $this, 'admin_icon_styles' ) );
        add_filter( 'admin_footer_text', '__return_empty_string', 11 );
        add_filter( 'update_footer', '__return_empty_string', 11 );
    }

    /**
     * Register Menu
     */
    public function register_menu() {
        add_menu_page(
            __( 'Fast Product Importer', 'si-fast-product-importer' ),
            __( 'Fast Product Importer', 'si-fast-product-importer' ),
            'manage_options',
            'si_fast_products',
            array( $this, 'render_main_page' ),
			SIFProd_URL . 'assets/flash-products-logo-128.png',
            30
        );

        add_submenu_page(
            'si_fast_products',
            __( 'Generator', 'si-fast-product-importer' ),
            __( 'Generator', 'si-fast-product-importer' ),
            'manage_options',
            'si_fast_products_generator',
            array( $this, 'render_generator_page' )
        );

        add_submenu_page(
            'si_fast_products',
            __( 'Settings', 'si-fast-product-importer' ),
            __( 'Settings', 'si-fast-product-importer' ),
            'manage_options',
            'si_fast_products_settings',
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

        if ( strpos( $hook, 'si_fast_products' ) === false && ! $is_product_edit ) {
            return;
        }

        wp_enqueue_style( 'sifp-admin-style', SIFProd_URL . 'style.css', array(), SIFProd_VERSION );
        wp_enqueue_media();
        wp_enqueue_script( 'sifp-admin-functions', SIFProd_URL . 'functions.js', array( 'jquery' ), SIFProd_VERSION, true );

        // Enqueue settings JS only on settings page
        if ( strpos( $hook, 'si_fast_products_settings' ) !== false ) {
            wp_enqueue_script( 'sifp-settings', SIFProd_URL . 'assets/settings.js', array( 'jquery' ), SIFProd_VERSION, true );
        }

        wp_localize_script( 'sifp-admin-functions', 'sifp_ajax', array(
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'sifp_nonce' ),
            'sku_prefix'    => get_option( 'sifp_sku_prefix', 'PROD-' ),
            'default_stock' => get_option( 'sifp_default_stock', '10' ),
            'strings'       => array(
                /* translators: %d: number of products to import */
                'confirm_bulk'        => __( 'Are you sure you want to import %1$d products?', 'si-fast-product-importer' ),
                'bulk_success'        => __( 'products imported successfully!', 'si-fast-product-importer' ),
                /* translators: %d: number of products to import */
                'confirm_bulk_import' => __( 'Are you sure you want to import %1$d products?', 'si-fast-product-importer' ),
                /* translators: %1$d: number of successful imports, %2$d: number of failed imports */
                'bulk_import_done'    => __( 'Import completed: %1$d success, %2$d failure.', 'si-fast-product-importer' ),
                'confirm_clear_logs'  => __( 'Are you sure you want to clear all logs?', 'si-fast-product-importer' ),
                'logs_cleared'        => __( 'Logs cleared successfully!', 'si-fast-product-importer' ),
                'error_clear_logs'    => __( 'Error while clearing logs', 'si-fast-product-importer' ),
                'error_missing_name'  => __( 'Please enter at least the product name', 'si-fast-product-importer' ),
                'ai_gen_success'      => __( 'Content generated successfully!', 'si-fast-product-importer' ),
                'attr_limit_reached'  => __( 'At least one attribute must be present (even if empty).', 'si-fast-product-importer' ),
                'importing'           => __( 'Importing...', 'si-fast-product-importer' ),
                'generating'          => __( 'Generating...', 'si-fast-product-importer' ),
                'error_ai_call'       => __( 'Error during AI call', 'si-fast-product-importer' ),
                'error_import'        => __( 'Error during import', 'si-fast-product-importer' ),
                'preset_loaded'       => __( 'Preset loaded!', 'si-fast-product-importer' ),
                'select_image'        => __( 'Select Product Image', 'si-fast-product-importer' ),
                'use_image'           => __( 'Use Image', 'si-fast-product-importer' ),
                'select_gallery'      => __( 'Select Gallery Images', 'si-fast-product-importer' ),
                'add_to_gallery'      => __( 'Add to gallery', 'si-fast-product-importer' ),
                'error'               => __( 'Error', 'si-fast-product-importer' ),
                'fetch_error'         => __( 'Failed to fetch products', 'si-fast-product-importer' ),
                'no_results'          => __( 'No products found matching your search.', 'si-fast-product-importer' ),
            )
        ) );
    }

    /**
     * Admin Icon Styles
     */
    public function admin_icon_styles() {
        wp_register_style( 'sifp-admin-icon', false );
        wp_enqueue_style( 'sifp-admin-icon' );

        $css = '
            li#toplevel_page_si_fast_products .wp-menu-image {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                overflow: hidden !important;
            }
            li#toplevel_page_si_fast_products .wp-menu-image img {
                max-width: 20px !important;
                max-height: 20px !important;
                width: 20px !important;
                height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                opacity: 0.8;
                transition: all 0.2s ease-in-out;
            }
            li#toplevel_page_si_fast_products:hover .wp-menu-image img,
            li#toplevel_page_si_fast_products.wp-has-current-submenu .wp-menu-image img {
                opacity: 1;
                scale: 1.1;
            }
        ';
        wp_add_inline_style( 'sifp-admin-icon', $css );
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
