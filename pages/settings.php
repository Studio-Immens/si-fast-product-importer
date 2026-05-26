<?php
defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    return;
}

use SIFlashProducts\Core\AIProviderManager;
use SIFlashProducts\Helpers\Encryption;

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'ai-providers';
$saved_tab  = isset( $_POST['sifp_active_tab'] ) ? sanitize_key( $_POST['sifp_active_tab'] ) : $active_tab;
$active_tab = $saved_tab;

// Show flash messages
if ( isset( $_GET['message'] ) ) {
    $msg = sanitize_key( $_GET['message'] );
    $class = 'notice-success';
    $text  = '';
    switch ( $msg ) {
        case 'settings_saved':    $text = __( 'Settings saved successfully!', 'si-flash-products' ); break;
        case 'db_regenerated':    $text = __( 'Local database regenerated and synced!', 'si-flash-products' ); break;
        case 'db_synced':         $text = __( 'Database file synced to table successfully!', 'si-flash-products' ); break;
        case 'db_file_not_found': $text = __( 'Database file not found. Please regenerate.', 'si-flash-products' ); $class = 'notice-error'; break;
    }
    if ( $text ) {
        echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $text ) . '</p></div>';
    }
}
?>
<div id="sifp-admin-content" class="sifp-main-container">

    <div class="sifp-settings-header">
        <h1><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Plugin Settings', 'si-flash-products' ); ?></h1>
        <button form="sifp-form" name="update" value="update" class="sifp-button">
            <span class="dashicons dashicons-saved"></span>
            <?php esc_html_e( 'SAVE ALL SETTINGS', 'si-flash-products' ); ?>
        </button>
    </div>

    <!-- Tabs -->
    <div class="sifp-settings-tabs">
        <a href="?page=flash_products_settings&tab=ai-providers"
           class="sifp-settings-tab <?php echo 'ai-providers' === $active_tab ? 'sifp-settings-tab--active' : ''; ?>"
           data-tab="ai-providers">
            <span class="dashicons dashicons-admin-appearance"></span>
            <?php esc_html_e( 'AI Providers', 'si-flash-products' ); ?>
        </a>
        <a href="?page=flash_products_settings&tab=settings"
           class="sifp-settings-tab <?php echo 'settings' === $active_tab ? 'sifp-settings-tab--active' : ''; ?>"
           data-tab="settings">
            <span class="dashicons dashicons-admin-generic"></span>
            <?php esc_html_e( 'Settings', 'si-flash-products' ); ?>
        </a>
        <a href="?page=flash_products_settings&tab=database"
           class="sifp-settings-tab <?php echo 'database' === $active_tab ? 'sifp-settings-tab--active' : ''; ?>"
           data-tab="database">
            <span class="dashicons dashicons-database"></span>
            <?php esc_html_e( 'Database', 'si-flash-products' ); ?>
        </a>
    </div>

    <form id="sifp-form" method="post" class="sifp-form">
        <?php wp_nonce_field( 'si-flash-prod-sett', 'sett_nonce' ); ?>
        <input type="hidden" name="sifp_active_tab" value="<?php echo esc_attr( $active_tab ); ?>">

        <!-- ════════════════════════════════════════════ -->
        <!-- TAB: AI PROVIDERS                           -->
        <!-- ════════════════════════════════════════════ -->
        <div class="sifp-tab-panel" data-tab="ai-providers" <?php echo 'ai-providers' !== $active_tab ? 'style="display:none;"' : ''; ?>>

            <div class="sifp-provider-selector">
                <label for="sifp_active_ai_provider"><?php esc_html_e( 'Active AI Provider', 'si-flash-products' ); ?></label>
                <?php
                $manager   = AIProviderManager::instance();
                $providers = $manager->get_providers();
                $active_id = $manager->get_active_provider_id();
                ?>
                <select name="setting[sifp_active_ai_provider]" id="sifp_active_ai_provider">
                    <?php foreach ( $providers as $pid => $pinst ) :
                        $count = count( $pinst->get_available_models() );
                    ?>
                        <option value="<?php echo esc_attr( $pid ); ?>" <?php selected( $active_id, $pid ); ?>>
                            <?php echo esc_html( $pinst->get_name() ); ?> (<?php echo esc_html( $count ); ?> <?php esc_html_e( 'models', 'si-flash-products' ); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php foreach ( $providers as $pid => $pinst ) :
                $is_active   = ( $active_id === $pid );
                $models      = $pinst->get_available_models();
                $saved_model = get_option( $pinst->get_model_option_name(), '' );
                $saved_key   = get_option( $pinst->get_api_key_option_name(), '' );
                $custom_model = get_option( $pinst->get_model_option_name() . '_custom', '' );

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
                <div class="sifp-provider-card" id="sifp-provider-<?php echo esc_attr( $pid ); ?>" <?php echo $is_active ? '' : 'style="display:none;"'; ?>>

                    <div class="sifp-provider-card__head">
                        <h3><?php echo esc_html( $pinst->get_name() ); ?></h3>
                        <button type="button" class="sifp-provider-card__refresh"
                                data-provider="<?php echo esc_attr( $pid ); ?>"
                                title="<?php esc_attr_e( 'Refresh model list', 'si-flash-products' ); ?>">
                            <span class="dashicons dashicons-update"></span>
                        </button>
                    </div>

                    <div class="sifp-provider-card__body">

                        <!-- Model -->
                        <div class="sifp-field">
                            <label class="sifp-field__label"><?php esc_html_e( 'Model', 'si-flash-products' ); ?></label>
                            <div class="sifp-field__control">
                                <select name="setting[<?php echo esc_attr( $pinst->get_model_option_name() ); ?>]"
                                        class="sifp-ai-model-select"
                                        data-provider="<?php echo esc_attr( $pid ); ?>">
                                    <?php if ( ! empty( $premium_models ) ) : ?>
                                        <optgroup label="<?php esc_attr_e( 'Premium Models', 'si-flash-products' ); ?>">
                                            <?php foreach ( $premium_models as $mid => $mdata ) :
                                                $ctx = $mdata['context'] > 0 ? ' [' . number_format( $mdata['context'] ) . ' ctx]' : '';
                                            ?>
                                                <option value="<?php echo esc_attr( $mid ); ?>" <?php selected( $saved_model, $mid ); ?>
                                                    data-caps='<?php echo esc_attr( wp_json_encode( $mdata['capabilities'] ) ); ?>'
                                                    data-context="<?php echo esc_attr( $mdata['context'] ); ?>"
                                                    data-pricing='<?php echo esc_attr( wp_json_encode( $mdata['pricing'] ?? null ) ); ?>'
                                                    data-description="<?php echo esc_attr( $mdata['description'] ?? '' ); ?>">
                                                    💎 <?php echo esc_html( $mdata['name'] . $ctx ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $free_models ) ) : ?>
                                        <optgroup label="<?php esc_attr_e( 'Free Models', 'si-flash-products' ); ?>">
                                            <?php foreach ( $free_models as $mid => $mdata ) :
                                                $ctx = $mdata['context'] > 0 ? ' [' . number_format( $mdata['context'] ) . ' ctx]' : '';
                                            ?>
                                                <option value="<?php echo esc_attr( $mid ); ?>" <?php selected( $saved_model, $mid ); ?>
                                                    data-caps='<?php echo esc_attr( wp_json_encode( $mdata['capabilities'] ) ); ?>'
                                                    data-context="<?php echo esc_attr( $mdata['context'] ); ?>"
                                                    data-pricing='<?php echo esc_attr( wp_json_encode( $mdata['pricing'] ?? null ) ); ?>'
                                                    data-description="<?php echo esc_attr( $mdata['description'] ?? '' ); ?>">
                                                    ⭐ <?php echo esc_html( $mdata['name'] . $ctx ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                    <option value="custom" <?php selected( $saved_model, 'custom' ); ?>>
                                        <?php esc_html_e( 'Custom Model (enter below)', 'si-flash-products' ); ?>
                                    </option>
                                </select>
                                <p class="sifp-field__hint">
                                    <?php esc_html_e( 'Custom model ID:', 'si-flash-products' ); ?>
                                    <input type="text" name="setting[<?php echo esc_attr( $pinst->get_model_option_name() ); ?>_custom]"
                                        value="<?php echo esc_attr( $custom_model ); ?>"
                                        placeholder="<?php esc_attr_e( 'e.g. my-custom-model', 'si-flash-products' ); ?>">
                                </p>
                            </div>
                        </div>

                        <!-- Capabilities -->
                        <div class="sifp-model-caps" id="sifp-caps-<?php echo esc_attr( $pid ); ?>" style="display:none;"></div>

                        <!-- API Key -->
                        <div class="sifp-field">
                            <label class="sifp-field__label"><?php esc_html_e( 'API Key', 'si-flash-products' ); ?></label>
                            <div class="sifp-field__control">
                                <input type="password"
                                       name="setting[<?php echo esc_attr( $pinst->get_api_key_option_name() ); ?>]"
                                       value="<?php echo esc_attr( $saved_key ); ?>"
                                       class="sifp-ai-api-key"
                                       data-provider="<?php echo esc_attr( $pid ); ?>"
                                       autocomplete="off"
                                       placeholder="<?php esc_attr_e( 'Enter your API key', 'si-flash-products' ); ?>">
                            </div>
                        </div>

                        <!-- Test Connection -->
                        <div class="sifp-field sifp-field--test">
                            <label class="sifp-field__label"><?php esc_html_e( 'Connection', 'si-flash-products' ); ?></label>
                            <div class="sifp-field__control">
                                <button type="button" class="sifp-button sifp-btn-test-ai"
                                        data-provider="<?php echo esc_attr( $pid ); ?>">
                                    <span class="dashicons dashicons-plugins"></span>
                                    <?php esc_html_e( 'Test Connection', 'si-flash-products' ); ?>
                                </button>
                                <span class="sifp-test-result"></span>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ════════════════════════════════════════════ -->
        <!-- TAB: SETTINGS                               -->
        <!-- ════════════════════════════════════════════ -->
        <div class="sifp-tab-panel" data-tab="settings" <?php echo 'settings' !== $active_tab ? 'style="display:none;"' : ''; ?>>

            <!-- Product Defaults -->
            <div class="sifp-settings-section">
                <div class="sifp-settings-section__title"><?php esc_html_e( 'Product Defaults', 'si-flash-products' ); ?></div>
                <div class="sifp-settings-section__body">

                    <div class="sifp-settings-row">
                        <div class="sifp-settings-row__label"><?php esc_html_e( 'SKU Prefix', 'si-flash-products' ); ?></div>
                        <div class="sifp-settings-row__field">
                            <input type="text" name="setting[sifp_sku_prefix]" value="<?php echo esc_attr( get_option( 'sifp_sku_prefix', 'PROD-' ) ); ?>">
                            <p class="sifp-settings-row__hint"><?php esc_html_e( 'Prefix used for automatic SKU generation', 'si-flash-products' ); ?></p>
                        </div>
                    </div>

                    <div class="sifp-settings-row">
                        <div class="sifp-settings-row__label"><?php esc_html_e( 'Default Stock', 'si-flash-products' ); ?></div>
                        <div class="sifp-settings-row__field">
                            <input type="number" name="setting[sifp_default_stock]" value="<?php echo esc_attr( get_option( 'sifp_default_stock', '10' ) ); ?>" min="0">
                            <p class="sifp-settings-row__hint"><?php esc_html_e( 'Stock quantity assigned to imported products', 'si-flash-products' ); ?></p>
                        </div>
                    </div>

                    <div class="sifp-settings-row">
                        <div class="sifp-settings-row__label"><?php esc_html_e( 'Product Status', 'si-flash-products' ); ?></div>
                        <div class="sifp-settings-row__field">
                            <select name="setting[sifp_default_product_status]">
                                <option value="publish" <?php selected( get_option( 'sifp_default_product_status', 'publish' ), 'publish' ); ?>><?php esc_html_e( 'Published', 'si-flash-products' ); ?></option>
                                <option value="draft" <?php selected( get_option( 'sifp_default_product_status', 'publish' ), 'draft' ); ?>><?php esc_html_e( 'Draft', 'si-flash-products' ); ?></option>
                                <option value="pending" <?php selected( get_option( 'sifp_default_product_status', 'publish' ), 'pending' ); ?>><?php esc_html_e( 'Pending Review', 'si-flash-products' ); ?></option>
                            </select>
                            <p class="sifp-settings-row__hint"><?php esc_html_e( 'Default status for imported/generated products', 'si-flash-products' ); ?></p>
                        </div>
                    </div>

                    <div class="sifp-settings-row">
                        <div class="sifp-settings-row__label"><?php esc_html_e( 'Menu Position', 'si-flash-products' ); ?></div>
                        <div class="sifp-settings-row__field">
                            <input type="number" name="setting[sifp_menu_order]" value="<?php echo esc_attr( get_option( 'sifp_menu_order', '15' ) ); ?>" min="1" max="100">
                            <p class="sifp-settings-row__hint"><?php esc_html_e( 'Position of the plugin in the WordPress admin menu', 'si-flash-products' ); ?></p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- AI Generation -->
            <div class="sifp-settings-section">
                <div class="sifp-settings-section__title"><?php esc_html_e( 'AI Generation', 'si-flash-products' ); ?></div>
                <div class="sifp-settings-section__body">

                    <div class="sifp-settings-row">
                        <div class="sifp-settings-row__label"><?php esc_html_e( 'Creativity', 'si-flash-products' ); ?></div>
                        <div class="sifp-settings-row__field">
                            <select name="setting[sifp_ai_creativity]">
                                <?php foreach ( array( '0.2' => '0.2 — Very precise', '0.5' => '0.5 — Balanced', '0.7' => '0.7 — Creative', '1.0' => '1.0 — Maximum' ) as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( get_option( 'sifp_ai_creativity', '0.7' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="sifp-settings-row__hint"><?php esc_html_e( 'Lower = more precise, higher = more creative', 'si-flash-products' ); ?></p>
                        </div>
                    </div>

                    <div class="sifp-settings-row">
                        <div class="sifp-settings-row__label"><?php esc_html_e( 'Tone Instruction', 'si-flash-products' ); ?></div>
                        <div class="sifp-settings-row__field">
                            <input type="text" name="setting[sifp_ai_tone]" value="<?php echo esc_attr( get_option( 'sifp_ai_tone', '' ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. Professional, Persuasive, Technical', 'si-flash-products' ); ?>">
                            <p class="sifp-settings-row__hint"><?php esc_html_e( 'Global instruction for the AI voice tone', 'si-flash-products' ); ?></p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Remote Databases -->
            <div class="sifp-settings-section">
                <div class="sifp-settings-section__title"><?php esc_html_e( 'Remote Databases', 'si-flash-products' ); ?></div>
                <div class="sifp-settings-section__body">

                    <div class="sifp-settings-row">
                        <div class="sifp-settings-row__label"><?php esc_html_e( 'Database URLs', 'si-flash-products' ); ?></div>
                        <div class="sifp-settings-row__field">
                            <textarea name="setting[sifp_remote_db_links]" rows="4" placeholder="https://example.com/wp-json/flash_products/v1/products"><?php echo esc_textarea( get_option( 'sifp_remote_db_links', '' ) ); ?></textarea>
                            <p class="sifp-settings-row__hint"><?php esc_html_e( 'One URL per line. Each endpoint must return a JSON with a "result" array of products.', 'si-flash-products' ); ?></p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Error Logs -->
            <div class="sifp-settings-section">
                <div class="sifp-settings-section__title">
                    <span><?php esc_html_e( 'Error Logs', 'si-flash-products' ); ?></span>
                    <button type="button" class="sifp-button sifp-button--small sifp-button--error clear-logs-btn">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e( 'Clear Logs', 'si-flash-products' ); ?>
                    </button>
                </div>
                <div class="sifp-settings-section__body sifp-settings-section__body--nopad">
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
                                        echo '<td><code>' . esc_html( $log['context'] ?? '' ) . '</code></td>';
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

        <!-- ════════════════════════════════════════════ -->
        <!-- TAB: DATABASE                               -->
        <!-- ════════════════════════════════════════════ -->
        <div class="sifp-tab-panel" data-tab="database" <?php echo 'database' !== $active_tab ? 'style="display:none;"' : ''; ?>>

            <div class="sifp-settings-section">
                <div class="sifp-settings-section__title"><?php esc_html_e( 'Local Database', 'si-flash-products' ); ?></div>
                <div class="sifp-settings-section__body">

                    <p class="sifp-db-intro"><?php esc_html_e( 'The local database contains demo products that you can search and import from the main page.', 'si-flash-products' ); ?></p>

                    <?php
                    $upload_dir = wp_upload_dir();
                    $json_path  = $upload_dir['basedir'] . '/si-flash-products/local_products.json';
                    $has_file   = file_exists( $json_path );
                    $has_db     = false;
                    $count      = 0;

                    if ( $has_file ) {
                        global $wpdb;
                        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}sifp_local_products" );
                        $has_db = ( $count > 0 );
                    }
                    ?>

                    <div class="sifp-db-cards">
                        <div class="sifp-db-card <?php echo $has_file ? 'sifp-db-card--ok' : 'sifp-db-card--empty'; ?>">
                            <div class="sifp-db-card__icon">
                                <span class="dashicons dashicons-media-document"></span>
                            </div>
                            <div class="sifp-db-card__info">
                                <strong><?php esc_html_e( 'JSON File', 'si-flash-products' ); ?></strong>
                                <?php if ( $has_file ) : ?>
                                    <span class="sifp-db-card__status"><?php echo esc_html( size_format( filesize( $json_path ) ) ); ?></span>
                                <?php else : ?>
                                    <span class="sifp-db-card__status sifp-db-card__status--missing"><?php esc_html_e( 'Not found', 'si-flash-products' ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="sifp-db-card <?php echo $has_db ? 'sifp-db-card--ok' : 'sifp-db-card--empty'; ?>">
                            <div class="sifp-db-card__icon">
                                <span class="dashicons dashicons-database"></span>
                            </div>
                            <div class="sifp-db-card__info">
                                <strong><?php esc_html_e( 'DB Table', 'si-flash-products' ); ?></strong>
                                <?php if ( $has_db ) : ?>
                                    <span class="sifp-db-card__status"><?php echo sprintf( esc_html__( '%d products', 'si-flash-products' ), $count ); ?></span>
                                <?php else : ?>
                                    <span class="sifp-db-card__status sifp-db-card__status--missing"><?php esc_html_e( 'Empty', 'si-flash-products' ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
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
        </div>

        <div class="sifp-settings-footer">
            <button name="update" value="update" class="sifp-button sifp-button--large">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'SAVE ALL SETTINGS', 'si-flash-products' ); ?>
            </button>
        </div>
    </form>

</div>