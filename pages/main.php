<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}
$categories = array();



?>

<div class="FPMainContainer">

    <div class="FPNavBar">
        <div class="FPNavElement">
            Categories:
            <select class="" name="FP_categories">
                <option value="blank"> - </option>
                <?php 
                foreach ($categories as $key => $value) {
                    echo '<option value="'.$value.'">'.$value.'</option>';
                }
                ?>
            </select>
        </div>

        <div class="FPNavElement">
            Keyword:
            <input name="FP_keyword" type="search" placeholder="type a keyword" onkeyup="">
        </div>
    </div>

    <div class="FPContainer">

        <div class="FPCard" fp_title="" fp_description="" fp_description="" fp_exerp="" fp_categories="" fp_tag="" fp_ingredient="" fp_macro_cat="" fp_allerg="" fp_sticker="" fp_temp="" onclick="FP_Open_Detail(this)">

            <div class="FPCardHead">
                <img src="">
                <div class="FORapidImport">
                    <span class="dashicons dashicons-plus"></span>
                </div>
            </div>

            <div class="FPCardFoot">
                <strong class="FPCardTitle">
                    Titolo del prodotto
                </strong>
            </div>

        </div>

    </div>

    <div class="FPSideBar" style="display:none;">

    </div>

    <div class="FPDetailSection" style="display:;">
        <div class="FPDetailHead">

        </div>
        <div class="FPDetailBody">

        </div>
        <div class="FPDetailFoot">

        </div>
    </div>
    <div class="FPBackGroundSection" style="display:;"></div>

</div>







