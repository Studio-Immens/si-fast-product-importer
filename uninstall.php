<?php
/**
 * Uninstall SI Flash Products.
 *
 * Cleans up all plugin options, transients, and custom DB tables.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete all plugin options
$options = array(
    'sifp_gemini_api_key',
    'sifp_ai_model',
    'sifp_ai_tone',
    'sifp_ai_creativity',
    'sifp_sku_prefix',
    'sifp_default_stock',
    'sifp_menu_order',
    'sifp_default_product_status',
    'sifp_default_import_status',
    'sifp_remote_db_links',
    'sifp_error_logs',
    'sifp_db_version',
    'sifp_active_ai_provider',
    'sifp_openai_key',
    'sifp_openai_model',
    'sifp_openai_model_custom',
    'sifp_claude_key',
    'sifp_claude_model',
    'sifp_claude_model_custom',
    'sifp_openrouter_key',
    'sifp_openrouter_model',
    'sifp_openrouter_model_custom',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

// Delete all transients with our prefix
global $wpdb;
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like( '_transient_sifp_' ) . '%'
    )
);
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like( '_transient_timeout_sifp_' ) . '%'
    )
);

// Drop custom table
$table_name = $wpdb->prefix . 'sifp_local_products';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// Clean up uploaded JSON file
$upload_dir = wp_upload_dir();
$json_file  = $upload_dir['basedir'] . '/si-flash-products/local_products.json';
if ( file_exists( $json_file ) ) {
    wp_delete_file( $json_file );
}

// Clean up debug logs
$log_dir = WP_CONTENT_DIR . '/uploads/si-flash-products';
if ( file_exists( $log_dir . '/debug.log' ) ) {
    wp_delete_file( $log_dir . '/debug.log' );
}
