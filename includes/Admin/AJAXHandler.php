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
            's'          => sanitize_text_field( wp_unslash( wp_strip_all_tags( $_GET['s'] ?? '' ) ) ),
            'categories' => sanitize_text_field( wp_unslash( wp_strip_all_tags( $_GET['categories'] ?? '' ) ) ),
            'limit'      => intval( $_GET['limit'] ?? 100 ),
            'offset'     => intval( $_GET['offset'] ?? 0 ),
            'orderby'    => sanitize_text_field( $_GET['orderby'] ?? 'title' ),
            'source'     => sanitize_key( $_GET['source'] ?? 'all' ),
        );

        $all_results = array( 'result' => array(), 'total_results' => 0 );

        // 1. Get Remote Results
        if ( $args['source'] === 'all' || $args['source'] === 'remote' ) {
            $remote_links = sifp_get_setting('sifp_remote_db_links');
            $urls = array('https://flashproducts.studioimmens.com/wp-json/flash_products/v1/products');
            
            if ( ! empty( $remote_links ) ) {
                $extra_links = explode( "\n", str_replace( "\r", "", $remote_links ) );
                foreach ( $extra_links as $link ) {
                    $link = trim($link);
                    if ( ! empty( $link ) && filter_var($link, FILTER_VALIDATE_URL) ) {
                        $urls[] = $link;
                    }
                }
            }

            // Create a unique cache key based on search parameters
            $cache_key = 'sifp_remote_search_' . md5( serialize( $args ) . implode('_', $urls) );
            $cached_results = get_transient( $cache_key );
            
            if ( false !== $cached_results ) {
                $all_results['result'] = array_merge($all_results['result'], $cached_results['result']);
                $all_results['total_results'] += intval($cached_results['total_results'] ?? 0);
            } else {
                $remote_fetched = array( 'result' => array(), 'total_results' => 0 );
                
                // Fetch from remote if not cached
                foreach ( $urls as $base_url ) {
                    $url = add_query_arg( array(
                        'categories' => $args['categories'],
                        'orderby'    => $args['orderby'],
                        'limit'      => $args['limit'],
                        'offset'     => $args['offset'],
                        's'          => $args['s'],
                    ), $base_url );
    
                    $response = wp_remote_get( $url, array('timeout' => 15) );
    
                    if ( ! is_wp_error( $response ) ) {
                        $data = json_decode( wp_remote_retrieve_body( $response ), true );
                        if ( isset($data['result']) && is_array($data['result']) ) {
                            $remote_fetched['result'] = array_merge($remote_fetched['result'], $data['result']);
                            $remote_fetched['total_results'] += intval($data['total_results'] ?? 0);
                        }
                    } else if ( function_exists( 'sifp_log' ) ) {
                         sifp_log( 'Remote fetch error for ' . $url . ': ' . $response->get_error_message(), 'ajax_search', 'error' );
                    }
                }
                
                // Cache the fetched remotes for 1 hour
                set_transient( $cache_key, $remote_fetched, HOUR_IN_SECONDS );
                
                $all_results['result'] = array_merge($all_results['result'], $remote_fetched['result']);
                $all_results['total_results'] += intval($remote_fetched['total_results'] ?? 0);
            }
        }

        // 2. Get Local Results (from DB)
        if ( $args['source'] === 'all' || $args['source'] === 'local' ) {
            $db = \SIFlashProducts\Core\Database::instance();
            $local_results = $db->search( $args );
            
            $all_results['result'] = array_merge( $all_results['result'], $local_results['result'] );
            $all_results['total_results'] += intval( $local_results['total_results'] );
        }

        wp_send_json_success( $all_results );
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
