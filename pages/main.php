<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

$categories = array();
$categories = json_decode( wp_remote_retrieve_body(wp_remote_get('https://flashproducts.innovazioneweb.com/wp-json/flash_products/v1/taxonomy?tax=product_cat') ) );

// fo_debug( $categories->result );

?>

<div class="FPMainContainer">

    <div class="FPNavBar">
        <div class="FPNavElement">
            <?php echo esc_html__('Categories:','flash-products');?>
            <select class="FP_categories" name="FP_categories" onchange="FP_search_product();">
                <option value=""> - select - </option>
                <?php 
                foreach ($categories->result as $key => $value) {
                    echo '<option value="'.$value->name.'">'.$value->name.'</option>';
                }
                ?>
            </select>
        </div>

        <div class="FPNavElement">
            <?php echo esc_html__('Keyword:','flash-products');?>
            <input name="FP_keyword" type="search" placeholder="type a keyword" onkeyup="">
        </div>
    </div>

    <div class="FPContainer">

        <div class="FPCard FPdefaultCard" fp_title="Product title" fp_short_title="Product short title" fp_slang_title="Product slang title" fp_description="Blank Product description" fp_exerp="Blank Product exerpt" fp_categories="cat1,cat2,cat3" fp_tag="tag1,tag2,tag3" fp_ingredient="ing1,ing2,ing3" fp_macro_cat="Macro_cat" fp_allerg="allerg" fp_sticker="sticker" fp_temp="cold,hot,ambient" fp_img="" fp_gallery="">

            <div class="FPCardHead">
                <img src="" onclick="FP_Open_Detail(jQuery(this).closest('.FPCard'))">
                <div class="FORapidImport" onclick="FP_Import_product(jQuery(this).closest('.FPCard'));">
                    <span class="dashicons dashicons-plus"></span>
                </div>
            </div>

            <div class="FPCardFoot" onclick="FP_Open_Detail(jQuery(this).closest('.FPCard'))">
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
                    <strong><?php echo esc_html__('Macro Categories:','flash-products');?></strong>
                    <div fp-block="fp_macro_cat">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Categories:','flash-products');?></strong>
                    <div fp-block="fp_categories">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Tags:','flash-products');?></strong>
                    <div fp-block="fp_tag">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Ingredients:','flash-products');?></strong>
                    <div fp-block="fp_ingredient">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Allergene:','flash-products');?></strong>
                    <div fp-block="fp_allerg">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Sticker:','flash-products');?></strong>
                    <div fp-block="fp_sticker">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Temperature:','flash-products');?></strong>
                    <div fp-block="fp_temp">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

            </div>
        </div>
        <div class="FPDetailFoot">

        </div>
    </div>
    <div class="FPBackGroundSection" style="display:none;"></div>

</div>







