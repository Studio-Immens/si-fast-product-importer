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

    <div class="sifp-navbar" style="justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0; font-size: 24px;">
            <span class="dashicons dashicons-admin-settings" style="font-size: 24px; width: 24px; height: 24px; margin-right: 10px;"></span>
            <?php esc_html_e('Plugin Settings', 'si-flash-products'); ?>
        </h1>
        <div class="sifp-nav-element">
            <button form="general" name="update" value="update" class="sifp-button pointer">
                <span class="dashicons dashicons-saved" style="margin-right: 5px;"></span>
                <?php esc_html_e('SAVE ALL SETTINGS', 'si-flash-products'); ?>
            </button>
        </div>
    </div>

    <!-- Local Database Section -->
    <div class="sifp-form-separator">
        <b> <?php esc_html_e('Local Database' , 'si-flash-products'); ?> </b>
    </div>
    <div class="sifp-setting-board" style="padding: 20px;">
        <p><?php esc_html_e('The local database contains 2000 demo products that you can search and import. You can regenerate it if needed.', 'si-flash-products'); ?></p>
        <?php
        $upload_dir = wp_upload_dir();
        $json_path = $upload_dir['basedir'] . '/si-flash-products/local_products.json';
        if ( file_exists( $json_path ) ) {
            $size = size_format( filesize( $json_path ) );
            echo '<p><strong>' . esc_html__('Status:', 'si-flash-products') . '</strong> ' . sprintf( esc_html__('Database file exists (%s)', 'si-flash-products'), $size ) . '</p>';
            
            // Check if synced to DB
            global $wpdb;
            $table_name = $wpdb->prefix . 'sifp_local_products';
            $count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
            if ( $count > 0 ) {
                echo '<p><strong>' . esc_html__('DB Table:', 'si-flash-products') . '</strong> ' . sprintf( esc_html__('%d products synced.', 'si-flash-products'), $count ) . '</p>';
            } else {
                echo '<p><strong>' . esc_html__('DB Table:', 'si-flash-products') . '</strong> <span style="color:var(--sifp-error);">' . esc_html__('Empty! Please sync or regenerate.', 'si-flash-products') . '</span></p>';
            }
        } else {
            echo '<p><strong>' . esc_html__('Status:', 'si-flash-products') . '</strong> ' . esc_html__('Database file not found.', 'si-flash-products') . '</p>';
        }
        ?>
        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=flash_products_settings&sifp_regenerate_db=1' ), 'sifp_regenerate_db' ) ); ?>" class="sifp-button" style="background-color: var(--sifp-bg-input) !important; color: #fff !important; border: 1px solid rgba(255,255,255,0.1) !important;">
                <span class="dashicons dashicons-update" style="margin-right: 5px;"></span>
                <?php esc_html_e('Regenerate Local Product DB', 'si-flash-products'); ?>
            </a>
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=flash_products_settings&sifp_sync_db=1' ), 'sifp_sync_db' ) ); ?>" class="sifp-button" style="background-color: var(--sifp-bg-input) !important; color: #fff !important; border: 1px solid rgba(255,255,255,0.1) !important;">
                <span class="dashicons dashicons-database-import" style="margin-right: 5px;"></span>
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

    <div class="sifp-setting-board">
        <div class="sifp-form-category">
            <b> <?php esc_html_e('Error Logs' , 'si-flash-products'); ?> </b>
        </div>
        <div class="sifp-log-section" style="padding: 20px;">
            <div class="sifp-log-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <span><?php esc_html_e('Last 50 events logged by the plugin', 'si-flash-products'); ?></span>
                <button type="button" class="sifp-button clear-logs-btn" style="background-color: var(--sifp-error) !important; font-size: 11px; padding: 5px 10px;">
                    <span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px;"></span> <?php esc_html_e('Clear Logs', 'si-flash-products'); ?>
                </button>
            </div>
            <div class="sifp-log-table-container" style="max-height: 500px; overflow-y: auto;">
                <table class="wp-list-table widefat fixed striped" style="border: none; background: transparent !important;">
                    <thead>
                        <tr>
                            <th style="width: 150px; color: #fff;"><?php esc_html_e('Timestamp', 'si-flash-products'); ?></th>
                            <th style="width: 120px; color: #fff;"><?php esc_html_e('Context', 'si-flash-products'); ?></th>
                            <th style="color: #fff;"><?php esc_html_e('Message', 'si-flash-products'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $logs = get_option('sifp_error_logs', array());
                        if (empty($logs)) {
                            echo '<tr><td colspan="3" style="text-align:center;">' . esc_html__('No logs found.', 'si-flash-products') . '</td></tr>';
                        } else {
                            foreach ($logs as $log) {
                                echo '<tr>';
                                echo '<td>' . esc_html($log['timestamp']) . '</td>';
                                echo '<td><code style="background:var(--sifp-bg-nav); color:var(--sifp-primary); padding: 2px 5px; border-radius:3px;">' . esc_html($log['context']) . '</code></td>';
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

    <div style="margin-top: 30px; text-align: right;">
        <button name="update" value="update" class="sifp-button pointer" style="padding: 15px 40px !important; font-size: 16px !important;">
            <span class="dashicons dashicons-saved" style="margin-right: 10px;"></span>
            <?php esc_html_e('SAVE ALL SETTINGS', 'si-flash-products'); ?>
        </button>
    </div>

</form>
</div>

<?php do_action('sifp_settings_sections_end'); ?>




