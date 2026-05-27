<?php
namespace SIFlashProducts\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Database Management Class
 */
class Database {

    /**
     * Instance
     * @var Database
     */
    protected static $_instance = null;

    /**
     * Table name
     * @var string
     */
    public $table_name;

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
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'sifp_local_products';
    }

    /**
     * Create table on activation
     */
    public static function install() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sifp_local_products';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id varchar(100) DEFAULT '' NOT NULL,
            title text NOT NULL,
            content longtext NOT NULL,
            excerpt text NOT NULL,
            categories text DEFAULT '' NOT NULL,
            tags text DEFAULT '' NOT NULL,
            sku varchar(100) DEFAULT '' NOT NULL,
            regular_price varchar(20) DEFAULT '' NOT NULL,
            sale_price varchar(20) DEFAULT '' NOT NULL,
            img_url text NOT NULL,
            gallery_urls text NOT NULL,
            seo_title text DEFAULT '' NOT NULL,
            seo_description text DEFAULT '' NOT NULL,
            attributes longtext NOT NULL,
            extra_data longtext NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            KEY sku (sku)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        update_option( 'sifp_db_version', '1.1.0' );

        // Copy default JSON file to uploads directory
        $source_file = __DIR__ . '/../local_products.json';
        $upload_dir  = wp_upload_dir();
        $dest_dir    = $upload_dir['basedir'] . '/si-flash-products';

        if ( file_exists( $source_file ) && ! file_exists( $dest_dir . '/local_products.json' ) ) {
            if ( ! file_exists( $dest_dir ) ) {
                wp_mkdir_p( $dest_dir );
            }
            copy( $source_file, $dest_dir . '/local_products.json' );
        }
    }

    /**
     * Sync JSON to DB
     */
    public function sync_json_to_db( $json_file ) {
        if ( ! file_exists( $json_file ) ) {
            return false;
        }

        $json_content = file_get_contents( $json_file );
        // Basic validation before decoding
        if ( empty($json_content) || $json_content[0] !== '[' ) {
             return false;
        }
        
        $products = json_decode( $json_content, true );
        if ( ! is_array( $products ) ) {
            return false;
        }

        global $wpdb;
        
        // Start transaction
        $wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        
        // Delete all old rows
        $truncate_result = $wpdb->query( "TRUNCATE TABLE {$this->table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        
        if ( $truncate_result === false ) {
             $wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
             return false;
        }

        $batch_size = 50;
        $batches = array_chunk($products, $batch_size);

        foreach ( $batches as $batch ) {
            $query = "INSERT INTO {$this->table_name} (title, content, excerpt, categories, tags, sku, regular_price, sale_price, img_url, gallery_urls, seo_title, seo_description, attributes, extra_data, created_at) VALUES ";
            $values = array();
            $placeholders = array();

            foreach ( $batch as $product ) {
                $placeholders[] = "(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)";
                array_push(
                    $values,
                    sanitize_text_field( $product['post_title'] ?? '' ),
                    wp_kses_post( $product['post_content'] ?? '' ),
                    wp_kses_post( $product['post_excerpt'] ?? '' ),
                    sanitize_text_field( $product['sifp_categories'] ?? '' ),
                    sanitize_text_field( $product['sifp_tag'] ?? '' ),
                    sanitize_text_field( $product['sku'] ?? '' ),
                    sanitize_text_field( $product['regular_price'] ?? '' ),
                    sanitize_text_field( $product['sale_price'] ?? '' ),
                    esc_url_raw( $product['sifp_img'] ?? '' ),
                    sanitize_text_field( $product['sifp_gallery'] ?? '' ),
                    sanitize_text_field( $product['seo_title'] ?? '' ),
                    sanitize_textarea_field( $product['seo_description'] ?? '' ),
                    isset($product['attributes']) ? wp_json_encode($product['attributes']) : '',
                    wp_json_encode(array(
                        'ingredient' => wp_kses_post( $product['sifp_ingredient'] ?? '' ),
                        'allerg'     => wp_kses_post( $product['sifp_allerg'] ?? '' ),
                        'sticker'    => sanitize_text_field( $product['sifp_sticker'] ?? '' ),
                        'temp'       => sanitize_text_field( $product['sifp_temp'] ?? '' )
                    )),
                    current_time('mysql')
                );
            }

            $query .= implode(', ', $placeholders);
            
            $insert_result = $wpdb->query( $wpdb->prepare( $query, $values ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter,WordPress.DB.PreparedSQL.NotPrepared
            if ( $insert_result === false ) {
                 $wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                 return false;
            }
        }

        // Commit transaction
        $wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

        return true;
    }

    /**
     * Search products in DB
     */
    public function search( $args = array() ) {
        global $wpdb;

        $defaults = array(
            's'          => '',
            'categories' => '',
            'limit'      => 100,
            'offset'     => 0,
            'orderby'    => 'title',
            'order'      => 'ASC'
        );

        $args = wp_parse_args( $args, $defaults );

        $query = "SELECT * FROM $this->table_name WHERE 1=1";
        $params = array();

        if ( ! empty( $args['s'] ) ) {
            $query .= " AND (title LIKE %s OR content LIKE %s OR sku LIKE %s)";
            $search = '%' . $wpdb->esc_like( $args['s'] ) . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if ( ! empty( $args['categories'] ) ) {
            // Support comma-separated category names (OR logic)
            $cats = explode( ',', $args['categories'] );
            $cat_conditions = array();
            foreach ( $cats as $cat ) {
                $cat = trim( $cat );
                if ( ! empty( $cat ) ) {
                    $cat_conditions[] = 'categories LIKE %s';
                    $params[] = '%' . $wpdb->esc_like( $cat ) . '%';
                }
            }
            if ( ! empty( $cat_conditions ) ) {
                $query .= ' AND (' . implode( ' OR ', $cat_conditions ) . ')';
            }
        }

        $total_results = $wpdb->get_var( $wpdb->prepare( str_replace( '*', 'COUNT(*)', $query ), $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL,PluginCheck.Security.DirectDB.UnescapedDBParameter,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

        $orderby = in_array( $args['orderby'], array( 'title', 'id', 'created_at', 'sku' ) ) ? $args['orderby'] : 'title';
        $order   = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

        $query .= " ORDER BY {$orderby} {$order}";
        $query .= " LIMIT %d OFFSET %d";
        $params[] = $args['limit'];
        $params[] = $args['offset'];

        $results = $wpdb->get_results( $wpdb->prepare( $query, $params ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter,WordPress.DB.PreparedSQL.NotPrepared

        // Format results to match the expected format
        foreach ( $results as &$item ) {
            $extra = json_decode( $item['extra_data'], true );
            $item['post_title'] = $item['title'];
            $item['post_content'] = $item['content'];
            $item['post_excerpt'] = $item['excerpt'];
            $item['sifp_categories'] = $item['categories'];
            $item['sifp_tag'] = $item['tags'];
            $item['sifp_img'] = $item['img_url'];
            $item['sifp_gallery'] = $item['gallery_urls'];
            $item['seo_title'] = $item['seo_title'];
            $item['seo_description'] = $item['seo_description'];
            $item['attributes'] = json_decode( $item['attributes'], true );
            $item['sifp_ingredient'] = $extra['ingredient'] ?? '';
            $item['sifp_allerg'] = $extra['allerg'] ?? '';
            $item['sifp_sticker'] = $extra['sticker'] ?? '';
            $item['sifp_temp'] = $extra['temp'] ?? '';
            $item['source'] = 'local';
        }

        return array(
            'result'        => $results,
            'total_results' => $total_results
        );
    }
}
