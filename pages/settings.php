<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

?>



<div id="settSection">
<form id="general" method="post" class="FPForm" style="">
    <button name="update" value="update" class="FPbutton pointer" style="position:sticky;margin: 10px 10px 10px auto;top:45px;"> UPDATE </button>

    <div class="FPFormSeparator">
        <b> <?php esc_html_e('Global Settings' , 'si-flash-products'); ?> </b>
        <span class="dashicons dashicons-arrow-down toggle-board" data-board="global"></span>
    </div>

<!-- global --><div class="FPSetting_Board" board="global">

<?php

FP_general_setting( array( 'name' => 'FP_gemini_api_key',
    'default'   => '',
    'type'      => 'text',
    'class'     => '',
    'text'      => __('Gemini API Key' , 'si-flash-products'),
    'info'      => __('Enter your Google Gemini API Key', 'si-flash-products')
) );

FP_general_setting( array( 'name' => 'FP_ai_model',
    'default'   => 'gemini-2.0-flash',
    'type'      => 'select',
    'options'   => array('gemini-2.0-flash', 'gemini-2.0-flash-latest', 'gemini-1.5-flash', 'gemini-1.5-flash-latest', 'gemini-1.5-pro'),
    'class'     => '',
    'text'      => __('AI Model' , 'si-flash-products'),
    'info'      => __('Select the Gemini model to use', 'si-flash-products')
) );

FP_general_setting( array( 'name' => 'FP_ai_tone',
    'default'   => 'Professional and persuasive',
    'type'      => 'text',
    'class'     => '',
    'text'      => __('AI Tone' , 'si-flash-products'),
    'info'      => __('Global instruction for the AI voice tone (e.g. Creative, Technical, etc.)', 'si-flash-products')
) );

?>
</div>

    <div class="FPFormSeparator">
        <b> <?php esc_html_e('Generator Settings' , 'si-flash-products'); ?> </b>
        <span class="dashicons dashicons-arrow-down toggle-board" data-board="generator_settings"></span>
    </div>

<div class="FPSetting_Board" board="generator_settings" style="display:none;">
<?php

FP_general_setting( array( 'name' => 'FP_sku_prefix',
    'default'   => 'PROD-',
    'type'      => 'text',
    'class'     => '',
    'text'      => __('SKU Prefix' , 'si-flash-products'),
    'info'      => __('Prefix used for automatic SKU generation', 'si-flash-products')
) );

FP_general_setting( array( 'name' => 'FP_default_stock',
    'default'   => '10',
    'type'      => 'number',
    'class'     => '',
    'text'      => __('Default Stock' , 'si-flash-products'),
    'info'      => __('Default stock quantity if not specified', 'si-flash-products')
) );

FP_general_setting( array( 'name' => 'FP_ai_creativity',
    'default'   => '0.7',
    'type'      => 'select',
    'options'   => array('0.2', '0.5', '0.7', '1.0'),
    'class'     => '',
    'text'      => __('AI Creativity (Temperature)' , 'si-flash-products'),
    'info'      => __('0.2 = Very precise, 1.0 = Very creative', 'si-flash-products')
) );

FP_general_setting( array( 'name' => 'FP_default_product_status',
    'default'   => 'publish',
    'type'      => 'select',
    'options'   => array('publish', 'draft', 'pending'),
    'class'     => '',
    'text'      => __('Default Product Status' , 'si-flash-products'),
    'info'      => __('The status with which imported/generated products will be created', 'si-flash-products')
) );

?>
</div>

    <div class="FPFormSeparator">
        <b> <?php esc_html_e('Import Settings' , 'si-flash-products'); ?> </b>
        <span class="dashicons dashicons-arrow-down toggle-board" data-board="import_settings"></span>
    </div>

<div class="FPSetting_Board" board="import_settings" style="display:none;">
<?php

FP_general_setting( array( 'name' => 'FP_menu_order',
    'default'   => '15',
    'type'      => 'number',
    'class'     => '',
    'text'      => __('Admin Menu Position' , 'si-flash-products'),
    'info'      => __('Enter the position of the menu panel in the backend', 'si-flash-products')
) );

FP_general_setting( array( 'name' => 'FP_default_product_status',
    'default'   => 'publish',
    'type'      => 'select',
    'options'   => array('publish', 'draft', 'private'),
    'class'     => '',
    'text'      => __('Default Product Status' , 'si-flash-products'),
    'info'      => __('Set the default status for imported products', 'si-flash-products')
) );

?>
</div>

    <div class="FPFormSeparator">
        <b> <?php esc_html_e('Advanced Settings' , 'si-flash-products'); ?> </b>
        <span class="dashicons dashicons-arrow-down toggle-board" data-board="page_manage_order"></span>
    </div>

<!-- page_manage_order --><div class="FPSetting_Board" board="page_manage_order" style="display:none;">
    <div class="FPFormCategory">
        <b> <?php esc_html_e('Error Logs' , 'si-flash-products'); ?> </b>
    </div>
    <div class="FPLogSection" style="padding: 20px;">
        <div class="FPLogHeader" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <span><?php esc_html_e('Last 50 events logged by the plugin', 'si-flash-products'); ?></span>
            <button type="button" class="FPbutton clear-logs-btn" style="background-color: var(--fp-error); font-size: 11px; padding: 5px 10px;">
                <span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px;"></span> <?php esc_html_e('Clear Logs', 'si-flash-products'); ?>
            </button>
        </div>
        <div class="FPLogTableContainer" style="max-height: 400px; overflow-y: auto; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
            <table class="wp-list-table widefat fixed striped" style="border: none;">
                <thead>
                    <tr>
                        <th style="width: 150px;"><?php esc_html_e('Timestamp', 'si-flash-products'); ?></th>
                        <th style="width: 120px;"><?php esc_html_e('Context', 'si-flash-products'); ?></th>
                        <th><?php esc_html_e('Message', 'si-flash-products'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $logs = get_option('fp_error_logs', array());
                    if (empty($logs)) {
                        echo '<tr><td colspan="3" style="text-align:center;">' . esc_html__('No logs found.', 'si-flash-products') . '</td></tr>';
                    } else {
                        foreach ($logs as $log) {
                            echo '<tr>';
                            echo '<td>' . esc_html($log['timestamp']) . '</td>';
                            echo '<td><code style="background:#eee; padding: 2px 5px; border-radius:3px;">' . esc_html($log['context']) . '</code></td>';
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

    <button name="update" value="update" class="FPbutton pointer" style="margin: 10px auto;">UPDATE</button>

    <input type="hidden" name="sett_nonce" value="<?php echo wp_create_nonce('si-flash-prod-sett'); ?>">

</form>

<?php do_action('fp_settings_sections_end'); ?>

</div>

<?php
//FP_debug($_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']);
FP_save_settings( "setting", 'setting' );




