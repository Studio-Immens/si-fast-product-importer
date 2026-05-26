<?php
defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    return;
}

use SIFlashProducts\Core\AIProviderManager;
use SIFlashProducts\Core\ModelRegistry;
use SIFlashProducts\Helpers\Encryption;

// Determine active tab
$active_tab  = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'ai-providers';
$saved_tab   = isset( $_POST['sifp_active_tab'] ) ? sanitize_key( $_POST['sifp_active_tab'] ) : $active_tab;
$active_tab  = $saved_tab;

// Show messages
if ( isset( $_GET['message'] ) ) {
    $message = sanitize_key( $_GET['message'] );
    $msg_class = 'notice-success';
    $msg_text  = '';
    switch ( $message ) {
        case 'settings_saved':
            $msg_text = __( 'Settings saved successfully!', 'si-flash-products' );
            break;
        case 'db_regenerated':
            $msg_text = __( 'Local database regenerated and synced!', 'si-flash-products' );
            break;
        case 'db_synced':
            $msg_text = __( 'Database file synced to table successfully!', 'si-flash-products' );
            break;
        case 'db_file_not_found':
            $msg_text = __( 'Database file not found. Please regenerate.', 'si-flash-products' );
            $msg_class = 'notice-error';
            break;
    }
    if ( $msg_text ) {
        echo '<div class="notice ' . esc_attr( $msg_class ) . ' is-dismissible"><p>' . esc_html( $msg_text ) . '</p></div>';
    }
}
?>
<div id="sifp-admin-content" class="sifp-main-container sifp-settings-page">

    <div class="sifp-navbar">
        <h1>
            <span class="dashicons dashicons-admin-settings"></span>
            <?php esc_html_e( 'Plugin Settings', 'si-flash-products' ); ?>
        </h1>
        <button form="sifp-settings-form" name="update" value="update" class="sifp-button pointer">
            <span class="dashicons dashicons-saved"></span>
            <?php esc_html_e( 'SAVE ALL SETTINGS', 'si-flash-products' ); ?>
        </button>
    </div>

    <!-- Tab Navigation -->
    <div class="sifp-main-nav sifp-settings-tabs">
        <a href="?page=flash_products_settings&tab=ai-providers"
           class="sifp-main-nav-el <?php echo 'ai-providers' === $active_tab ? 'sifp-main-nav-el--active' : ''; ?>"
           data-tab="ai-providers">
            <span class="dashicons dashicons-admin-appearance"></span>
            <?php esc_html_e( 'AI Providers', 'si-flash-products' ); ?>
        </a>
        <a href="?page=flash_products_settings&tab=import"
           class="sifp-main-nav-el <?php echo 'import' === $active_tab ? 'sifp-main-nav-el--active' : ''; ?>"
           data-tab="import">
            <span class="dashicons dashicons-import"></span>
            <?php esc_html_e( 'Import Settings', 'si-flash-products' ); ?>
        </a>
        <a href="?page=flash_products_settings&tab=database"
           class="sifp-main-nav-el <?php echo 'database' === $active_tab ? 'sifp-main-nav-el--active' : ''; ?>"
           data-tab="database">
            <span class="dashicons dashicons-database"></span>
            <?php esc_html_e( 'Database', 'si-flash-products' ); ?>
        </a>
        <a href="?page=flash_products_settings&tab=advanced"
           class="sifp-main-nav-el <?php echo 'advanced' === $active_tab ? 'sifp-main-nav-el--active' : ''; ?>"
           data-tab="advanced">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php esc_html_e( 'Advanced', 'si-flash-products' ); ?>
        </a>
    </div>

    <form id="sifp-settings-form" method="post" class="sifp-form">
        <?php wp_nonce_field( 'si-flash-prod-sett', 'sett_nonce' ); ?>
        <input type="hidden" name="sifp_active_tab" value="<?php echo esc_attr( $active_tab ); ?>">

        <!-- ======================== -->
        <!-- TAB: AI PROVIDERS         -->
        <!-- ======================== -->
        <div class="sifp-tab-content" data-tab="ai-providers" <?php echo 'ai-providers' !== $active_tab ? 'style="display:none;"' : ''; ?>>

            <div class="sifp-form-separator">
                <b><?php esc_html_e( 'Active AI Provider', 'si-flash-products' ); ?></b>
            </div>

            <div class="sifp-setting-board sifp-setting-board--provider-select">
                <?php
                $manager       = AIProviderManager::instance();
                $providers     = $manager->get_providers();
                $active_id     = $manager->get_active_provider_id();
                ?>
                <div class="sifp-setting-el sifp-setting-el--full">
                    <strong class="sifp-text-settings"><?php esc_html_e( 'Select Provider', 'si-flash-products' ); ?></strong>
                    <p class="sifp-text-settings"><?php esc_html_e( 'Choose the primary AI provider for product generation.', 'si-flash-products' ); ?></p>
                    <select name="setting[sifp_active_ai_provider]" id="sifp_active_ai_provider">
                        <?php foreach ( $providers as $pid => $pinst ) :
                            $models = $pinst->get_available_models();
                            $count = count( $models );
                        ?>
                            <option value="<?php echo esc_attr( $pid ); ?>" <?php selected( $active_id, $pid ); ?>>
                                <?php echo esc_html( $pinst->get_name() ); ?> (<?php echo esc_html( $count ); ?> <?php esc_html_e( 'models', 'si-flash-products' ); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Provider-specific sections -->
            <?php foreach ( $providers as $pid => $pinst ) :
                $is_active   = ( $active_id === $pid );
                $models      = $pinst->get_available_models();
                $saved_model = get_option( $pinst->get_model_option_name(), '' );
                $saved_key   = get_option( $pinst->get_api_key_option_name(), '' );

                // Decrypt if needed
                if ( in_array( $pid, array( 'openai', 'claude', 'openrouter' ), true ) && ! empty( $saved_key ) ) {
                    $saved_key = Encryption::decrypt( $saved_key );
                }

                $premium_models = array();
                $free_models    = array();
                foreach ( $models as $mid => $mdata ) {
                    if ( 'free' === ( $mdata['pricing_tier'] ?? 'premium' ) ) {
                        $free_models[ $mid ] = $mdata;
                    } else {
                        $premium_models[ $mid ] = $mdata;
                    }
                }
                ?>
                <div class="sifp-provider-section" id="sifp-provider-<?php echo esc_attr( $pid ); ?>" <?php echo $is_active ? '' : 'style="display:none;"'; ?>>
                    <div class="sifp-form-separator">
                        <b><?php echo esc_html( $pinst->get_name() ); ?></b>
                        <button type="button" class="sifp-button sifp-button--secondary sifp-btn-refresh-models"
                                data-provider="<?php echo esc_attr( $pid ); ?>"
                                title="<?php esc_attr_e( 'Refresh models', 'si-flash-products' ); ?>">
                            <span class="dashicons dashicons-update"></span>
                        </button>
                    </div>

                    <div class="sifp-setting-board">
                        <!-- Model Select -->
                        <div class="sifp-setting-el">
                            <strong class="sifp-text-settings"><?php esc_html_e( 'Model', 'si-flash-products' ); ?></strong>
                            <select name="setting[<?php echo esc_attr( $pinst->get_model_option_name() ); ?>]"
                                    class="sifp-ai-model-select"
                                    data-provider="<?php echo esc_attr( $pid ); ?>">
                                <?php if ( ! empty( $premium_models ) ) : ?>
                                    <optgroup label="<?php esc_attr_e( 'Premium Models', 'si-flash-products' ); ?>">
                                        <?php foreach ( $premium_models as $mid => $mdata ) :
                                            $ctx_display = $mdata['context'] > 0 ? ' [' . number_format( $mdata['context'] ) . ' ctx]' : '';
                                        ?>
                                            <option value="<?php echo esc_attr( $mid ); ?>" <?php selected( $saved_model, $mid ); ?>
                                                data-caps='<?php echo esc_attr( wp_json_encode( $mdata['capabilities'] ) ); ?>'
                                                data-context="<?php echo esc_attr( $mdata['context'] ); ?>"
                                                data-pricing='<?php echo esc_attr( wp_json_encode( $mdata['pricing'] ?? null ) ); ?>'
                                                data-description="<?php echo esc_attr( $mdata['description'] ?? '' ); ?>">
                                                💎 <?php echo esc_html( $mdata['name'] . $ctx_display ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                                <?php if ( ! empty( $free_models ) ) : ?>
                                    <optgroup label="<?php esc_attr_e( 'Free Models', 'si-flash-products' ); ?>">
                                        <?php foreach ( $free_models as $mid => $mdata ) :
                                            $ctx_display = $mdata['context'] > 0 ? ' [' . number_format( $mdata['context'] ) . ' ctx]' : '';
                                        ?>
                                            <option value="<?php echo esc_attr( $mid ); ?>" <?php selected( $saved_model, $mid ); ?>
                                                data-caps='<?php echo esc_attr( wp_json_encode( $mdata['capabilities'] ) ); ?>'
                                                data-context="<?php echo esc_attr( $mdata['context'] ); ?>"
                                                data-pricing='<?php echo esc_attr( wp_json_encode( $mdata['pricing'] ?? null ) ); ?>'
                                                data-description="<?php echo esc_attr( $mdata['description'] ?? '' ); ?>">
                                                ⭐ <?php echo esc_html( $mdata['name'] . $ctx_display ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                                <option value="custom" <?php selected( $saved_model, 'custom' ); ?>>
                                    <?php esc_html_e( 'Custom Model (enter below)', 'si-flash-products' ); ?>
                                </option>
                            </select>
                            <p class="sifp-text-settings" style="margin-top:8px;">
                                <?php esc_html_e( 'Or enter custom model ID:', 'si-flash-products' ); ?>
                                <input type="text" name="setting[<?php echo esc_attr( $pinst->get_model_option_name() ); ?>_custom]"
                                    value="<?php echo esc_attr( get_option( $pinst->get_model_option_name() . '_custom', '' ) ); ?>"
                                    placeholder="<?php esc_attr_e( 'e.g., custom-model-id', 'si-flash-products' ); ?>"
                                    style="width:200px;margin-left:8px;">
                            </p>
                            <div class="sifp-model-caps" id="sifp-caps-<?php echo esc_attr( $pid ); ?>" style="display:none;margin-top:12px;"></div>
                        </div>

                        <!-- API Key -->
                        <div class="sifp-setting-el">
                            <strong class="sifp-text-settings">
                                <?php esc_html_e( 'API Key', 'si-flash-products' ); ?>
                            </strong>
                            <p class="sifp-text-settings"><?php echo esc_html( sprintf( __( 'Enter your %s API Key', 'si-flash-products' ), $pinst->get_name() ) ); ?></p>
                            <input type="password"
                                   name="setting[<?php echo esc_attr( $pinst->get_api_key_option_name() ); ?>]"
                                   value="<?php echo esc_attr( $saved_key ); ?>"
                                   class="sifp-ai-api-key"
                                   data-provider="<?php echo esc_attr( $pid ); ?>"
                                   autocomplete="off">
                        </div>

                        <!-- Test Connection -->
                        <div class="sifp-setting-el sifp-setting-el--test">
                            <strong class="sifp-text-settings"><?php esc_html_e( 'Test Connection', 'si-flash-products' ); ?></strong>
                            <p class="sifp-text-settings"><?php esc_html_e( 'Verify your API key and model work together.', 'si-flash-products' ); ?></p>
                            <button type="button" class="sifp-button sifp-btn-test-ai sifp-button--secondary"
                                    data-provider="<?php echo esc_attr( $pid ); ?>">
                                <span class="dashicons dashicons-plugins"></span>
                                <?php esc_html_e( 'Test Connection', 'si-flash-products' ); ?>
                            </button>
                            <span class="sifp-ai-test-result" style="margin-left:10px;font-weight:700;display:inline-block;"></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ======================== -->
        <!-- TAB: IMPORT SETTINGS      -->
        <!-- ======================== -->
        <div class="sifp-tab-content" data-tab="import" <?php echo 'import' !== $active_tab ? 'style="display:none;"' : ''; ?>>

            <div class="sifp-form-separator">
                <b><?php esc_html_e( 'Product Defaults', 'si-flash-products' ); ?></b>
            </div>

            <div class="sifp-setting-board">
                <?php
                sifp_general_setting( array(
                    'name'    => 'sifp_sku_prefix',
                    'default' => 'PROD-',
                    'type'    => 'text',
                    'text'    => __( 'SKU Prefix', 'si-flash-products' ),
                    'info'    => __( 'Prefix used for automatic SKU generation', 'si-flash-products' ),
                ) );

                sifp_general_setting( array(
                    'name'    => 'sifp_default_stock',
                    'default' => '10',
                    'type'    => 'number',
                    'text'    => __( 'Default Stock Quantity', 'si-flash-products' ),
                    'info'    => __( 'Stock quantity assigned to imported products', 'si-flash-products' ),
                ) );

                sifp_general_setting( array(
                    'name'    => 'sifp_default_product_status',
                    'default' => 'publish',
                    'type'    => 'select',
                    'options' => array( 'publish', 'draft', 'pending' ),
                    'text'    => __( 'Default Product Status', 'si-flash-products' ),
                    'info'    => __( 'Status for imported/generated products', 'si-flash-products' ),
                ) );

                sifp_general_setting( array(
                    'name'    => 'sifp_default_import_status',
                    'default' => 'publish',
                    'type'    => 'select',
                    'options' => array( 'publish', 'draft', 'private' ),
                    'text'    => __( 'Fallback Import Status', 'si-flash-products' ),
                    'info'    => __( 'Fallback status if the primary status is not available', 'si-flash-products' ),
                ) );

                sifp_general_setting( array(
                    'name'    => 'sifp_menu_order',
                    'default' => '15',
                    'type'    => 'number',
                    'text'    => __( 'Admin Menu Position', 'si-flash-products' ),
                    'info'    => __( 'Position of the plugin menu in the WordPress admin', 'si-flash-products' ),
                ) );
                ?>
            </div>

            <div class="sifp-form-separator">
                <b><?php esc_html_e( 'AI Generation', 'si-flash-products' ); ?></b>
            </div>

            <div class="sifp-setting-board">
                <?php
                sifp_general_setting( array(
                    'name'    => 'sifp_ai_creativity',
                    'default' => '0.7',
                    'type'    => 'select',
                    'options' => array( '0.2', '0.5', '0.7', '1.0' ),
                    'text'    => __( 'AI Creativity (Temperature)', 'si-flash-products' ),
                    'info'    => __( '0.2 = Very precise, 1.0 = Very creative', 'si-flash-products' ),
                ) );

                sifp_general_setting( array(
                    'name'    => 'sifp_ai_tone',
                    'default' => '',
                    'type'    => 'text',
                    'text'    => __( 'AI Tone Instruction', 'si-flash-products' ),
                    'info'    => __( 'Global instruction for AI voice (e.g. Professional, Creative)', 'si-flash-products' ),
                ) );
                ?>
            </div>
        </div>

        <!-- ======================== -->
        <!-- TAB: DATABASE             -->
        <!-- ======================== -->
        <div class="sifp-tab-content" data-tab="database" <?php echo 'database' !== $active_tab ? 'style="display:none;"' : ''; ?>>

            <div class="sifp-form-separator">
                <b><?php esc_html_e( 'Local Database', 'si-flash-products' ); ?></b>
            </div>

            <div class="sifp-setting-board sifp-setting-board--db">
                <div class="sifp-db-status">
                    <p><?php esc_html_e( 'The local database contains demo products you can search and import.', 'si-flash-products' ); ?></p>
                    <?php
                    $upload_dir = wp_upload_dir();
                    $json_path  = $upload_dir['basedir'] . '/si-flash-products/local_products.json';
                    if ( file_exists( $json_path ) ) {
                        $size = size_format( filesize( $json_path ) );
                        echo '<p><span class="dashicons dashicons-yes-alt sifp-status-icon--success"></span> <strong>' . esc_html__( 'Status:', 'si-flash-products' ) . '</strong> ' . sprintf( esc_html__( 'Database file exists (%s)', 'si-flash-products' ), $size ) . '</p>';

                        global $wpdb;
                        $table_name = $wpdb->prefix . 'sifp_local_products';
                        $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}sifp_local_products" ) );
                        if ( $count > 0 ) {
                            echo '<p><span class="dashicons dashicons-database-view sifp-status-icon--primary"></span> <strong>' . esc_html__( 'DB Table:', 'si-flash-products' ) . '</strong> ' . sprintf( esc_html__( '%d products synced.', 'si-flash-products' ), $count ) . '</p>';
                        } else {
                            echo '<p><span class="dashicons dashicons-warning sifp-status-icon--error"></span> <strong>' . esc_html__( 'DB Table:', 'si-flash-products' ) . '</strong> <span class="sifp-text--error">' . esc_html__( 'Empty! Please sync or regenerate.', 'si-flash-products' ) . '</span></p>';
                        }
                    } else {
                        echo '<p><span class="dashicons dashicons-no-alt sifp-status-icon--error"></span> <strong>' . esc_html__( 'Status:', 'si-flash-products' ) . '</strong> ' . esc_html__( 'Database file not found. Regenerate below.', 'si-flash-products' ) . '</p>';
                    }
                    ?>
                </div>
                <div class="sifp-db-actions">
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=flash_products_settings&tab=database&sifp_regenerate_db=1' ), 'sifp_regenerate_db' ) ); ?>" class="sifp-button sifp-button--secondary">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e( 'Regenerate Product DB', 'si-flash-products' ); ?>
                    </a>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=flash_products_settings&tab=database&sifp_sync_db=1' ), 'sifp_sync_db' ) ); ?>" class="sifp-button sifp-button--secondary">
                        <span class="dashicons dashicons-database-import"></span>
                        <?php esc_html_e( 'Sync File to DB Table', 'si-flash-products' ); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- ======================== -->
        <!-- TAB: ADVANCED             -->
        <!-- ======================== -->
        <div class="sifp-tab-content" data-tab="advanced" <?php echo 'advanced' !== $active_tab ? 'style="display:none;"' : ''; ?>>

            <div class="sifp-form-separator">
                <b><?php esc_html_e( 'Remote Databases', 'si-flash-products' ); ?></b>
            </div>

            <div class="sifp-setting-board">
                <?php
                sifp_general_setting( array(
                    'name'    => 'sifp_remote_db_links',
                    'default' => '',
                    'type'    => 'textarea',
                    'class'   => '',
                    'text'    => __( 'Remote Database URLs', 'si-flash-products' ),
                    'info'    => __( 'One URL per line. Each must return JSON with "result" array.', 'si-flash-products' ),
                    'other'   => 'placeholder="https://example.com/wp-json/flash_products/v1/products"',
                ) );
                ?>
            </div>

            <div class="sifp-form-separator">
                <b><?php esc_html_e( 'Error Logs', 'si-flash-products' ); ?></b>
            </div>

            <div class="sifp-setting-board sifp-setting-board--db">
                <div class="sifp-log-section">
                    <div class="sifp-log-header">
                        <span><?php esc_html_e( 'Last 50 events', 'si-flash-products' ); ?></span>
                        <button type="button" class="sifp-button clear-logs-btn sifp-button--error">
                            <span class="dashicons dashicons-trash"></span>
                            <?php esc_html_e( 'Clear Logs', 'si-flash-products' ); ?>
                        </button>
                    </div>
                    <div class="sifp-log-table-container">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th class="sifp-log-th-time"><?php esc_html_e( 'Timestamp', 'si-flash-products' ); ?></th>
                                    <th class="sifp-log-th-context"><?php esc_html_e( 'Context', 'si-flash-products' ); ?></th>
                                    <th class="sifp-log-th-message"><?php esc_html_e( 'Message', 'si-flash-products' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $logs = get_option( 'sifp_error_logs', array() );
                                if ( empty( $logs ) ) {
                                    echo '<tr><td colspan="3" class="sifp-no-logs">' . esc_html__( 'No logs found.', 'si-flash-products' ) . '</td></tr>';
                                } else {
                                    foreach ( $logs as $log ) {
                                        echo '<tr>';
                                        echo '<td>' . esc_html( $log['timestamp'] ?? '' ) . '</td>';
                                        echo '<td><code class="sifp-log-code">' . esc_html( $log['context'] ?? '' ) . '</code></td>';
                                        echo '<td>' . esc_html( $log['message'] ?? '' ) . '</td>';
                                        echo '</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="sifp-form-footer">
            <button name="update" value="update" class="sifp-button pointer sifp-button--large">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'SAVE ALL SETTINGS', 'si-flash-products' ); ?>
            </button>
        </div>
    </form>

</div>
<?php
do_action( 'sifp_settings_sections_end' );