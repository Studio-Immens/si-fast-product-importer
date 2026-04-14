<?php
namespace SIFlashProducts\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Product Importer Class
 */
class Importer {

    /**
     * Create WooCommerce Product
     */
    public function create_woo_product( $data ) {
        if ( ! class_exists( 'WC_Product' ) ) {
            return new \WP_Error( 'wc_missing', __( 'WooCommerce is not active.', 'si-flash-products' ) );
        }

        $type = ! empty( $data['variations'] ) ? 'variable' : 'simple';
        $product = $type === 'variable' ? new \WC_Product_Variable() : new \WC_Product_Simple();

        $product->set_name( sanitize_text_field( $data['post_title'] ?? '' ) );
        $product->set_status( get_option( 'sifp_default_product_status', 'publish' ) );
        $product->set_description( wp_kses_post( $data['post_content'] ?? '' ) );
        $product->set_short_description( wp_kses_post( $data['post_excerpt'] ?? '' ) );
        $product->set_sku( sanitize_text_field( $data['sku'] ?? '' ) );
        $product->set_regular_price( sanitize_text_field( $data['regular_price'] ?? '' ) );
        $product->set_sale_price( sanitize_text_field( $data['sale_price'] ?? '' ) );
        $product->set_stock_status( 'instock' );
        $product->set_manage_stock( true );
        $product->set_stock_quantity( intval( get_option( 'sifp_default_stock', 10 ) ) );

        // Attributes
        if ( ! empty( $data['attributes'] ) ) {
            $attributes = array();
            foreach ( $data['attributes'] as $attr ) {
                $attribute = new \WC_Product_Attribute();
                $attribute->set_name( $attr['name'] );
                $attribute->set_options( explode( '|', $attr['value'] ) );
                $attribute->set_visible( true );
                $attribute->set_variation( $type === 'variable' );
                $attributes[] = $attribute;
            }
            $product->set_attributes( $attributes );
        }

        // Categories
        if ( ! empty( $data['sifp_categories'] ) ) {
            $cat_ids = array();
            $categories = explode( ',', $data['sifp_categories'] );
            foreach ( $categories as $cat_name ) {
                $term = get_term_by( 'name', trim( $cat_name ), 'product_cat' );
                if ( ! $term ) {
                    $term = wp_insert_term( trim( $cat_name ), 'product_cat' );
                }
                if ( ! is_wp_error( $term ) ) {
                    $cat_ids[] = is_array( $term ) ? $term['term_id'] : $term->term_id;
                }
            }
            $product->set_category_ids( $cat_ids );
        }

        // Save initial product
        $product_id = $product->save();

        if ( ! $product_id ) {
            if ( function_exists( 'sifp_log' ) ) {
                sifp_log( 'Failed to save product: ' . ( $data['post_title'] ?? 'Untitled' ), 'importer', 'error' );
            }
            return new \WP_Error( 'save_failed', __( 'Failed to save product.', 'si-flash-products' ) );
        }

        // Handle Images
        $this->handle_product_images( $product_id, $data );

        // Handle Variations if variable
        if ( $type === 'variable' ) {
            $this->create_variations( $product_id, $data['variations'] );
        }

        // SEO and Meta Tags (Yoast/RankMath support)
        $this->handle_seo_meta( $product_id, $data );

        // Extra custom meta
        update_post_meta( $product_id, '_sifp_ingredient', wp_kses_post( $data['sifp_ingredient'] ?? '' ) );
        update_post_meta( $product_id, '_sifp_allerg', wp_kses_post( $data['sifp_allerg'] ?? '' ) );
        update_post_meta( $product_id, '_sifp_sticker', sanitize_text_field( $data['sifp_sticker'] ?? '' ) );
        update_post_meta( $product_id, '_sifp_temp', sanitize_text_field( $data['sifp_temp'] ?? '' ) );

        return $product_id;
    }

    /**
     * Handle Product Images
     */
    private function handle_product_images( $product_id, $data ) {
        if ( ! empty( $data['sifp_img'] ) ) {
            $attach_id = $this->sideload_image( $data['sifp_img'], $product_id );
            if ( $attach_id ) {
                set_post_thumbnail( $product_id, $attach_id );
            }
        }

        if ( ! empty( $data['sifp_gallery'] ) ) {
            $gallery_ids = array();
            $urls = explode( ',', $data['sifp_gallery'] );
            foreach ( $urls as $url ) {
                $attach_id = $this->sideload_image( trim( $url ), $product_id );
                if ( $attach_id ) {
                    $gallery_ids[] = $attach_id;
                }
            }
            update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery_ids ) );
        }
    }

    /**
     * Sideload Image
     */
    private function sideload_image( $url, $post_id ) {
        if ( empty( $url ) ) return false;
        
        // If it's already an ID (from media library)
        if ( is_numeric( $url ) ) return intval( $url );

        // Basic URL validation
        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return false;
        }

        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $attach_id = media_sideload_image( $url, $post_id, null, 'id' );
        return is_wp_error( $attach_id ) ? false : $attach_id;
    }

    /**
     * Create Variations
     */
    private function create_variations( $product_id, $variations ) {
        foreach ( $variations as $var_data ) {
            try {
                $variation = new \WC_Product_Variation();
                $variation->set_parent_id( $product_id );
                
                // Prepare attributes for variation
                $var_attributes = array();
                if ( ! empty( $var_data['attributes'] ) ) {
                    foreach ( $var_data['attributes'] as $name => $value ) {
                        // Slugify the attribute name for WooCommerce
                        $key = sanitize_title( $name );
                        $var_attributes[ $key ] = $value;
                    }
                }
                
                $variation->set_attributes( $var_attributes );
                $variation->set_regular_price( $var_data['regular_price'] );
                
                if ( ! empty( $var_data['sale_price'] ) ) {
                    $variation->set_sale_price( $var_data['sale_price'] );
                }
                
                $variation->set_sku( $var_data['sku'] ?? '' );
                $variation->set_manage_stock( true );
                $variation->set_stock_quantity( intval( get_option( 'sifp_default_stock', 10 ) ) );
                $variation->set_status( 'publish' );
                $variation->save();
            } catch ( \Exception $e ) {
                if ( function_exists( 'sifp_log' ) ) {
                    sifp_log( sprintf( 'Error creating variation for product %d: %s', $product_id, $e->getMessage() ), 'importer', 'error' );
                }
            }
        }
    }

    /**
     * Handle SEO and Meta Tags
     */
    private function handle_seo_meta( $product_id, $data ) {
        // Yoast SEO
        if ( defined( 'WPSEO_VERSION' ) ) {
            update_post_meta( $product_id, '_yoast_wpseo_title', sanitize_text_field( $data['seo_title'] ?? '' ) );
            update_post_meta( $product_id, '_yoast_wpseo_metadesc', sanitize_textarea_field( $data['seo_description'] ?? '' ) );
        }

        // Rank Math
        if ( defined( 'RANK_MATH_VERSION' ) ) {
            update_post_meta( $product_id, 'rank_math_title', sanitize_text_field( $data['seo_title'] ?? '' ) );
            update_post_meta( $product_id, 'rank_math_description', sanitize_textarea_field( $data['seo_description'] ?? '' ) );
        }
    }
}
