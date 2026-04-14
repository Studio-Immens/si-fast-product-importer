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
    }

    /**
     * Sync JSON to DB
     */
    public function sync_json_to_db( $json_file ) {
        if ( ! file_exists( $json_file ) ) {
            return false;
        }

        $products = json_decode( file_get_contents( $json_file ), true );
        if ( ! is_array( $products ) ) {
            return false;
        }

        global $wpdb;
        $wpdb->query( "TRUNCATE TABLE $this->table_name" );

        foreach ( $products as $product ) {
            $wpdb->insert(
                $this->table_name,
                array(
                    'title'         => sanitize_text_field( $product['post_title'] ),
                    'content'       => wp_kses_post( $product['post_content'] ),
                    'excerpt'       => wp_kses_post( $product['post_excerpt'] ),
                    'categories'    => sanitize_text_field( $product['sifp_categories'] ?? '' ),
                    'tags'          => sanitize_text_field( $product['sifp_tag'] ?? '' ),
                    'sku'           => sanitize_text_field( $product['sku'] ?? '' ),
                    'regular_price' => sanitize_text_field( $product['regular_price'] ?? '' ),
                    'sale_price'    => sanitize_text_field( $product['sale_price'] ?? '' ),
                    'img_url'       => esc_url_raw( $product['sifp_img'] ?? '' ),
                    'gallery_urls'  => sanitize_text_field( $product['sifp_gallery'] ?? '' ),
                    'seo_title'     => sanitize_text_field( $product['seo_title'] ?? '' ),
                    'seo_description' => sanitize_textarea_field( $product['seo_description'] ?? '' ),
                    'attributes'    => isset($product['attributes']) ? wp_json_encode($product['attributes']) : '',
                    'extra_data'    => wp_json_encode(array(
                        'ingredient' => wp_kses_post( $product['sifp_ingredient'] ?? '' ),
                        'allerg'     => wp_kses_post( $product['sifp_allerg'] ?? '' ),
                        'sticker'    => sanitize_text_field( $product['sifp_sticker'] ?? '' ),
                        'temp'       => sanitize_text_field( $product['sifp_temp'] ?? '' )
                    )),
                    'created_at'    => current_time('mysql')
                )
            );
        }

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
            $query .= " AND categories LIKE %s";
            $params[] = '%' . $wpdb->esc_like( $args['categories'] ) . '%';
        }

        $total_results = $wpdb->get_var( $wpdb->prepare( str_replace( '*', 'COUNT(*)', $query ), $params ) );

        $orderby = in_array( $args['orderby'], array( 'title', 'id', 'created_at', 'sku' ) ) ? $args['orderby'] : 'title';
        $order   = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

        $query .= " ORDER BY {$orderby} {$order}";
        $query .= " LIMIT %d OFFSET %d";
        $params[] = $args['limit'];
        $params[] = $args['offset'];

        $results = $wpdb->get_results( $wpdb->prepare( $query, $params ), ARRAY_A );

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
