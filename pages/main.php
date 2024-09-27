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

        <div class="FPCard" fp_title="Blank Product" fp_description="Blank Product description" fp_exerp="Blank Product exerpt" fp_categories="cat1,cat2,cat3" fp_tag="tag1,tag2,tag3" fp_ingredient="ing1,ing2,ing3" fp_macro_cat="Macro_cat" fp_allerg="allerg" fp_sticker="sticker" fp_temp="cold,hot,ambient" fp_img="" fp_gallery="" onclick="FP_Open_Detail(this)">

            <div class="FPCardHead">
                <img src="">
                <div class="FORapidImport">
                    <span class="dashicons dashicons-plus"></span>
                </div>
            </div>

            <div class="FPCardFoot">
                <strong class="FPCardTitle">
                    <?php echo esc_html__('Product Title','flash-products');?>
                </strong>
            </div>

        </div>

    </div>

    <div class="FPSideBar" style="display:none;">

    </div>

    <div class="FPDetailSection" style="display:none;">
        <div class="FPDetailHead">
            <strong><?php echo esc_html__('Product Title','flash-products');?></strong>
            <div onclick="FP_Close_Detail(this)" class="FPClose" style="margin-left:auto;"><?php echo esc_html__('CLOSE','flash-products');?></div>
        </div>
        <div class="FPDetailBody">
            <div class="FPDetailBodyImages">
                
            </div>
            <div class="FPDetailBodyCol">

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Exerpt:','flash-products');?></strong>
                    <p fp-block="exerpt">
                        <?php echo esc_html__('Product exerption text','flash-products');?>
                    </p>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Description:','flash-products');?></strong>
                    <p fp-block="description">
                        <?php echo esc_html__('Product description text','flash-products');?>
                    </p>
                </div>

            </div>
            <div class="FPDetailBodyCol">
                
                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Categories:','flash-products');?></strong>
                    <div fp-block="categories">
                        <div class="PFCloud">category 1</div>
                    </div>
                </div>

            </div>
        </div>
        <div class="FPDetailFoot">

        </div>
    </div>
    <div class="FPBackGroundSection" style="display:none;"></div>

</div>







