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
    'info'      => __('Inserisci la tua API Key di Google Gemini', 'si-flash-products')
) );

FP_general_setting( array( 'name' => 'FP_ai_model',
    'default'   => 'gemini-2.0-flash',
    'type'      => 'select',
    'options'   => array('gemini-2.0-flash', 'gemini-2.0-flash-latest', 'gemini-1.5-flash', 'gemini-1.5-flash-latest', 'gemini-1.5-pro'),
    'class'     => '',
    'text'      => __('Modello AI' , 'si-flash-products'),
    'info'      => __('Seleziona il modello Gemini da utilizzare', 'si-flash-products')
) );

FP_general_setting( array( 'name' => 'FP_menu_order',
    'default'   => '15',
    'type'      => 'number',
    'class'     => '',
    'text'      => __('menù admin position' , 'si-flash-products'),
    'info'      => __('enter the position of the menu panel in the backend', 'si-flash-products')
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
<!-- page_flash_order --></div>

    <div class="FPFormSeparator">
        <b> <?php esc_html_e('Advanced Settings' , 'si-flash-products'); ?> </b>
        <span class="dashicons dashicons-arrow-down toggle-board" data-board="page_manage_order"></span>
    </div>

<!-- page_manage_order --><div class="FPSetting_Board" board="page_manage_order" style="display:none;">
<?php

?>
    <div class="FPFormCategory">
        <b> <?php esc_html_e('general settings' , 'si-flash-products'); ?> </b>
    </div>
<?php




?>
    </div>

    <button name="update" value="update" class="FPbutton pointer" style="margin: 10px auto;">UPDATE</button>

    <input type="hidden" name="sett_nonce" value="<?php echo wp_create_nonce('si-flash-prod-sett'); ?>">

</form>

<?php do_action('fp_settings_sections_end'); ?>

</div>

<?php
//FP_debug($_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']);
FP_save_settings( "setting", 'setting' );




