<?php
namespace SIFlashProducts\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * AJAX Handler Class
 */
class AJAXHandler {

    /**
     * Instance
     * @var AJAXHandler
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
        // Search
        add_action( 'wp_ajax_sifp_search_products', array( $this, 'search_products' ) );
        
        // Import
        add_action( 'wp_ajax_sifp_import_product', array( $this, 'import_product' ) );
        
        // AI Generation
        add_action( 'wp_ajax_sifp_ai_generate_product', array( $this, 'ai_generate_product' ) );
        
        // Settings/DB
        add_action( 'wp_ajax_sifp_sync_db', array( $this, 'sync_db' ) );
        add_action( 'wp_ajax_sifp_clear_logs', array( $this, 'clear_logs' ) );
    }

    /**
     * Clear Error Logs
     */
    public function clear_logs() {
        check_ajax_referer( 'sifp_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'si-flash-products' ) );
        }

        delete_option( 'sifp_error_logs' );
        wp_send_json_success( __( 'Logs cleared successfully!', 'si-flash-products' ) );
    }

    /**
     * Search Products
     */
    public function search_products() {
        check_ajax_referer( 'sifp_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'si-flash-products' ) );
        }

        $args = array(
            's'          => sanitize_text_field( $_GET['s'] ?? '' ),
            'categories' => sanitize_text_field( $_GET['categories'] ?? '' ),
            'limit'      => intval( $_GET['limit'] ?? 100 ),
            'offset'     => intval( $_GET['offset'] ?? 0 ),
            'orderby'    => sanitize_text_field( $_GET['orderby'] ?? 'title' ),
        );

        $db = \SIFlashProducts\Core\Database::instance();
        $results = $db->search( $args );

        wp_send_json_success( $results );
    }

    /**
     * Import Product
     */
    public function import_product() {
        check_ajax_referer( 'sifp_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'si-flash-products' ) );
        }

        $product_data = $_POST['product'] ?? array();
        
        // Sanitize product data
        $product_data = $this->sanitize_product_data( $product_data );

        $importer = new \SIFlashProducts\Core\Importer();
        $result = $importer->create_woo_product( $product_data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( array(
            'id'  => $result,
            'url' => get_edit_post_link( $result, 'url' )
        ) );
    }

    /**
     * AI Generate Product
     */
    public function ai_generate_product() {
        check_ajax_referer( 'sifp_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'si-flash-products' ) );
        }

        $input_data = $_POST['data'] ?? array();
        
        $generator = new \SIFlashProducts\Core\AIGenerator();
        $result = $generator->generate( $input_data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( $result );
    }

    /**
     * Sync DB
     */
    public function sync_db() {
        check_ajax_referer( 'sifp_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'si-flash-products' ) );
        }

        $upload_dir = wp_upload_dir();
        $json_file = $upload_dir['basedir'] . '/si-flash-products/local_products.json';

        $db = \SIFlashProducts\Core\Database::instance();
        $success = $db->sync_json_to_db( $json_file );

        if ( $success ) {
            wp_send_json_success( __( 'Database synced successfully!', 'si-flash-products' ) );
        } else {
            wp_send_json_error( __( 'Failed to sync database. Ensure the JSON file exists.', 'si-flash-products' ) );
        }
    }

    /**
     * Sanitize product data
     */
    private function sanitize_product_data( $data ) {
        if ( ! is_array( $data ) ) {
            return sanitize_text_field( $data );
        }

        $sanitized = array();
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $sanitized[$key] = $this->sanitize_product_data( $value );
            } else {
                switch ( $key ) {
                    case 'post_content':
                        $sanitized[$key] = wp_kses_post( $value );
                        break;
                    case 'regular_price':
                    case 'sale_price':
                        // Strip everything except numbers and dots
                        $sanitized[$key] = preg_replace( '/[^0-9.]/', '', str_replace( ',', '.', $value ) );
                        break;
                    case 'sku':
                        $sanitized[$key] = sanitize_text_field( strtoupper( $value ) );
                        break;
                    default:
                        $sanitized[$key] = sanitize_text_field( $value );
                        break;
                }
            }
        }
        return $sanitized;
    }
}
