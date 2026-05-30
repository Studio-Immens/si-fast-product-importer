<?php
/**
 * Uninstall SI Fast Product Importer.
 *
 * Cleans up all plugin options, transients, and custom DB tables.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete all plugin options
$sifp_options = array(
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

foreach ( $sifp_options as $sifp_option ) {
    delete_option( $sifp_option );
}

// Delete all transients with our prefix
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like( '_transient_sifp_' ) . '%'
    )
);
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like( '_transient_timeout_sifp_' ) . '%'
    )
);

// Drop custom table
$sifp_table_name = $wpdb->prefix . 'sifp_local_products';
$wpdb->query( "DROP TABLE IF EXISTS {$sifp_table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange,PluginCheck.Security.DirectDB.UnescapedDBParameter,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery

// Clean up uploaded JSON file
$sifp_upload_dir = wp_upload_dir();
$sifp_json_file  = $sifp_upload_dir['basedir'] . '/si-fast-product-importer/local_products.json';
if ( file_exists( $sifp_json_file ) ) {
    wp_delete_file( $sifp_json_file );
}

// Clean up debug logs
$sifp_upload_dir = wp_upload_dir();
$sifp_log_dir = $sifp_upload_dir['basedir'] . '/si-fast-product-importer';
if ( file_exists( $sifp_log_dir . '/debug.log' ) ) {
    wp_delete_file( $sifp_log_dir . '/debug.log' );
}
