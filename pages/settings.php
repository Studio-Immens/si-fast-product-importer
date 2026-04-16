<?php

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    return;
}

// Show messages
if ( isset( $_GET['message'] ) ) {
    $message = sanitize_key( $_GET['message'] );
    switch ( $message ) {
        case 'settings_saved':
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully!', 'si-flash-products' ) . '</p></div>';
            break;
        case 'db_regenerated':
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Local database regenerated and synced!', 'si-flash-products' ) . '</p></div>';
            break;
        case 'db_synced':
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Database file synced to table successfully!', 'si-flash-products' ) . '</p></div>';
            break;
        case 'db_file_not_found':
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Database file not found. Please regenerate.', 'si-flash-products' ) . '</p></div>';
            break;
    }
}

// Handle settings update - MOVED to admin_init hook in Helpers/Functions.php
?>



<div id="sifp-settings-section" class="sifp-main-container">

    <div class="sifp-navbar">
        <h1>
            <span class="dashicons dashicons-admin-settings"></span>
            <?php esc_html_e('Plugin Settings', 'si-flash-products'); ?>
        </h1>
        <div class="sifp-nav-element">
            <button form="general" name="update" value="update" class="sifp-button pointer">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e('SAVE ALL SETTINGS', 'si-flash-products'); ?>
            </button>
        </div>
    </div>

    <!-- Local Database Section -->
    <div class="sifp-form-separator">
        <b> <?php esc_html_e('Local Database' , 'si-flash-products'); ?> </b>
    </div>
    <div class="sifp-setting-board sifp-setting-board--db">
        <div class="sifp-db-status">
            <p><?php esc_html_e('The local database contains 2000 demo products that you can search and import. You can regenerate it if needed.', 'si-flash-products'); ?></p>
            <?php
            $upload_dir = wp_upload_dir();
            $json_path = $upload_dir['basedir'] . '/si-flash-products/local_products.json';
            if ( file_exists( $json_path ) ) {
                $size = size_format( filesize( $json_path ) );
                echo '<p><span class="dashicons dashicons-yes-alt sifp-status-icon--success"></span> <strong>' . esc_html__('Status:', 'si-flash-products') . '</strong> ' . sprintf( esc_html__('Database file exists (%s)', 'si-flash-products'), $size ) . '</p>';
                
                // Check if synced to DB
                global $wpdb;
                $table_name = $wpdb->prefix . 'sifp_local_products';
                $count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
                if ( $count > 0 ) {
                    echo '<p><span class="dashicons dashicons-database-view sifp-status-icon--primary"></span> <strong>' . esc_html__('DB Table:', 'si-flash-products') . '</strong> ' . sprintf( esc_html__('%d products synced.', 'si-flash-products'), $count ) . '</p>';
                } else {
                    echo '<p><span class="dashicons dashicons-warning sifp-status-icon--error"></span> <strong>' . esc_html__('DB Table:', 'si-flash-products') . '</strong> <span class="sifp-text--error">' . esc_html__('Empty! Please sync or regenerate.', 'si-flash-products') . '</span></p>';
                }
            } else {
                echo '<p><span class="dashicons dashicons-no-alt sifp-status-icon--error"></span> <strong>' . esc_html__('Status:', 'si-flash-products') . '</strong> ' . esc_html__('Database file not found.', 'si-flash-products') . '</p>';
            }
            ?>
        </div>
        <div class="sifp-db-actions">
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=flash_products_settings&sifp_regenerate_db=1' ), 'sifp_regenerate_db' ) ); ?>" class="sifp-button sifp-button--secondary">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e('Regenerate Local Product DB', 'si-flash-products'); ?>
            </a>
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=flash_products_settings&sifp_sync_db=1' ), 'sifp_sync_db' ) ); ?>" class="sifp-button sifp-button--secondary">
                <span class="dashicons dashicons-database-import"></span>
                <?php esc_html_e('Sync File to DB Table', 'si-flash-products'); ?>
            </a>
        </div>
    </div>

<form id="general" method="post" class="sifp-form">
    <?php wp_nonce_field( 'si-flash-prod-sett', 'sett_nonce' ); ?>

    <div class="sifp-form-separator">
        <b> <?php esc_html_e('Global Settings' , 'si-flash-products'); ?> </b>
    </div>

    <div class="sifp-setting-board">
    <?php
    sifp_general_setting( array( 'name' => 'sifp_gemini_api_key',
        'default'   => '',
        'type'      => 'password',
        'class'     => '',
        'text'      => __('Gemini API Key' , 'si-flash-products'),
        'info'      => __('Enter your Google Gemini API Key', 'si-flash-products')
    ) );

    sifp_general_setting( array( 'name' => 'sifp_ai_model',
        'default'   => 'gemini-2.0-flash',
        'type'      => 'select',
        'options'   => array('gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-pro'),
        'class'     => '',
        'text'      => __('AI Model' , 'si-flash-products'),
        'info'      => __('Select the Gemini model to use', 'si-flash-products')
    ) );

    sifp_general_setting( array( 'name' => 'sifp_ai_tone',
        'default'   => 'Professional and persuasive',
        'type'      => 'text',
        'class'     => '',
        'text'      => __('AI Tone' , 'si-flash-products'),
        'info'      => __('Global instruction for the AI voice tone (e.g. Creative, Technical, etc.)', 'si-flash-products')
    ) );
    ?>
    </div>

    <div class="sifp-form-separator">
        <b> <?php esc_html_e('Generator Settings' , 'si-flash-products'); ?> </b>
    </div>

    <div class="sifp-setting-board">
    <?php
    sifp_general_setting( array( 'name' => 'sifp_sku_prefix',
        'default'   => 'PROD-',
        'type'      => 'text',
        'class'     => '',
        'text'      => __('SKU Prefix' , 'si-flash-products'),
        'info'      => __('Prefix used for automatic SKU generation', 'si-flash-products')
    ) );

    sifp_general_setting( array( 'name' => 'sifp_default_stock',
        'default'   => '10',
        'type'      => 'number',
        'class'     => '',
        'text'      => __('Default Stock' , 'si-flash-products'),
        'info'      => __('Default stock quantity if not specified', 'si-flash-products')
    ) );

    sifp_general_setting( array( 'name' => 'sifp_ai_creativity',
        'default'   => '0.7',
        'type'      => 'select',
        'options'   => array('0.2', '0.5', '0.7', '1.0'),
        'class'     => '',
        'text'      => __('AI Creativity (Temperature)' , 'si-flash-products'),
        'info'      => __('0.2 = Very precise, 1.0 = Very creative', 'si-flash-products')
    ) );

    sifp_general_setting( array( 'name' => 'sifp_default_product_status',
        'default'   => 'publish',
        'type'      => 'select',
        'options'   => array('publish', 'draft', 'pending'),
        'class'     => '',
        'text'      => __('Default Product Status' , 'si-flash-products'),
        'info'      => __('The status with which imported/generated products will be created', 'si-flash-products')
    ) );
    ?>
    </div>

    <div class="sifp-form-separator">
        <b> <?php esc_html_e('Remote Databases' , 'si-flash-products'); ?> </b>
    </div>

    <div class="sifp-setting-board">
    <?php
    sifp_general_setting( array( 'name' => 'sifp_remote_db_links',
        'default'   => '',
        'type'      => 'textarea',
        'class'     => '',
        'text'      => __('Remote Database Links' , 'si-flash-products'),
        'info'      => __('Enter one or more remote database URLs, one per line.', 'si-flash-products'),
        'other'     => 'placeholder="https://example.com/wp-json/flash_products/v1/products"'
    ) );
    ?>
    </div>

    <div class="sifp-form-separator">
        <b> <?php esc_html_e('Import Settings' , 'si-flash-products'); ?> </b>
    </div>

    <div class="sifp-setting-board">
    <?php
    sifp_general_setting( array( 'name' => 'sifp_menu_order',
        'default'   => '15',
        'type'      => 'number',
        'class'     => '',
        'text'      => __('Admin Menu Position' , 'si-flash-products'),
        'info'      => __('Enter the position of the menu panel in the backend', 'si-flash-products')
    ) );

    sifp_general_setting( array( 'name' => 'sifp_default_import_status',
        'default'   => 'publish',
        'type'      => 'select',
        'options'   => array('publish', 'draft', 'private'),
        'class'     => '',
        'text'      => __('Default Product Status' , 'si-flash-products'),
        'info'      => __('Set the default status for imported products', 'si-flash-products')
    ) );
    ?>
    </div>

    <div class="sifp-form-separator">
        <b> <?php esc_html_e('Advanced Settings' , 'si-flash-products'); ?> </b>
    </div>

    <div class="sifp-setting-board sifp-setting-board--db">
        <div class="sifp-form-category">
            <b> <?php esc_html_e('Error Logs' , 'si-flash-products'); ?> </b>
        </div>
        <div class="sifp-log-section">
            <div class="sifp-log-header">
                <span><?php esc_html_e('Last 50 events logged by the plugin', 'si-flash-products'); ?></span>
                <button type="button" class="sifp-button clear-logs-btn sifp-button--error">
                    <span class="dashicons dashicons-trash"></span> <?php esc_html_e('Clear Logs', 'si-flash-products'); ?>
                </button>
            </div>
            <div class="sifp-log-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="sifp-log-th-time"><?php esc_html_e('Timestamp', 'si-flash-products'); ?></th>
                            <th class="sifp-log-th-context"><?php esc_html_e('Context', 'si-flash-products'); ?></th>
                            <th class="sifp-log-th-message"><?php esc_html_e('Message', 'si-flash-products'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $logs = get_option('sifp_error_logs', array());
                        if (empty($logs)) {
                            echo '<tr><td colspan="3" class="sifp-no-logs">' . esc_html__('No logs found.', 'si-flash-products') . '</td></tr>';
                        } else {
                            foreach ($logs as $log) {
                                echo '<tr>';
                                echo '<td>' . esc_html($log['timestamp']) . '</td>';
                                echo '<td><code class="sifp-log-code">' . esc_html($log['context']) . '</code></td>';
                                echo '<td>' . esc_html($log['message']) . '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="sifp-form-footer">
        <button name="update" value="update" class="sifp-button pointer sifp-button--large">
            <span class="dashicons dashicons-saved"></span>
            <?php esc_html_e('SAVE ALL SETTINGS', 'si-flash-products'); ?>
        </button>
    </div>

</form>
</div>

<?php do_action('sifp_settings_sections_end'); ?>




