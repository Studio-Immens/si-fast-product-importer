<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

?>



<div id="settSection">
<form id="general" method="post" class="FPForm" style="">
    <button name="update" value="update" class="FPbutton pointer" style="position:sticky;margin: 10px 10px 10px auto;top:45px;"> UPDATE </button>

    <div class="FPFormSeparator" onclick="jQuery(`[board='global']`).slideToggle()">
        <b> <?php esc_html_e('Global Settings' , 'flash-products'); ?> </b>
        <span class="dashicons dashicons-arrow-down"></span>
    </div>

<!-- global --><div class="FPSetting_Board" board="global">

<?php

FP_general_setting( array( 'name' => 'FP_menu_order',
    'default'   => '15',
    'type'      => 'number',
    'class'     => '',
    'text'      => __('menù admin position' , 'flash-products'),
    'info'      => __('enter the position of the menu panel in the backend', 'flash-products')
) );

?>
<!-- page_flash_order --></div>

    <div class="FPFormSeparator" onclick="jQuery(`[board='page_manage_order']`).slideToggle()">
        <b> <?php esc_html_e('Advanced Settings' , 'flash-products'); ?> </b>
        <span class="dashicons dashicons-arrow-down"></span>
    </div>

<!-- page_manage_order --><div class="FPSetting_Board" board="page_manage_order" style="display:none;">
<?php

?>
    <div class="FPFormCategory">
        <b> <?php esc_html_e('general settings' , 'flash-products'); ?> </b>
    </div>
<?php




?>
    </div>

    <button name="update" value="update" class="FPbutton pointer" style="margin: 10px auto;">UPDATE</button>

</form>

<?php do_action('fp_settings_sections_end'); ?>

</div>

<?php
//FP_debug($_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']);
FP_save_settings( "setting", 'setting' );




