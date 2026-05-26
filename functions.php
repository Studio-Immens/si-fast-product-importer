<?php
/**
 * Legacy function wrappers.
 *
 * All core logic has moved to includes/ classes.
 * This file provides backward-compatible wrappers.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Log message for debugging (Wrapper for sifp_log)
 */
if ( ! function_exists( 'sifp_log_event' ) ) {
    function sifp_log_event( $message, $context = 'general' ) {
        if ( function_exists( 'sifp_log' ) ) {
            sifp_log( $message, $context );
        }
    }
}

/**
 * Create WooCommerce product from remote data (Wrapper for Importer class)
 */
if ( ! function_exists( 'sifp_create_woo_product' ) ) {
    function sifp_create_woo_product( $data ) {
        $importer = new \SIFlashProducts\Core\Importer();
        return $importer->create_woo_product( $data );
    }
}
