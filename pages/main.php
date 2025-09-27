<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}
$categories = array('result');
$categories = json_decode( wp_remote_retrieve_body( wp_remote_get( 'https://flashproducts.studioimmens.com/wp-json/flash_products/v1/taxonomy?tax=product_cat') ) );

$languages = array('result');
$languages = json_decode( wp_remote_retrieve_body( wp_remote_get( 'https://flashproducts.studioimmens.com/wp-json/flash_products/v1/taxonomy?tax=Languages') ) );

// fo_debug( $languages->result );

?>

<div class="FPMainContainer">

    <div class="FPNavBar">

        <div class="FPNavElement">
            <?php echo esc_html__('Languages:','si-flash-products');?>
            <select class="FP_languages" name="FP_languages" onchange="FP_search_product();">
                <?php 
                foreach ($languages->result as $key => $value) {
                    echo '<option value="'.esc_attr($value->slug).'">'.esc_attr($value->name).'</option>';
                }
                ?>
            </select>
        </div>

        <div class="FPNavElement">
            <?php echo esc_html__('Categories:','si-flash-products');?>
            <select class="FP_categories" name="FP_categories" onchange="FP_search_product();">
                <option value=""> - select - </option>
                <?php 
                foreach ($categories->result as $key => $value) {
                    echo '<option value="'.esc_attr($value->slug).'">'.esc_attr($value->name).'</option>';
                }
                ?>
            </select>
        </div>

        <div class="FPNavElement">
            <?php echo esc_html__('Keyword:','si-flash-products');?>
            <input class="FP_keyword" name="FP_keyword" type="search" placeholder="type a keyword" onkeyup="FP_search_product();">
        </div>
        

        <div class="FPNavElement">
            <?php echo esc_html__('Order:','si-flash-products');?>
            <select class="FP_orderby" name="FP_orderby" onchange="FP_search_product();">
                <option value="name"> By Name </option>
                <option value="date"> By Date </option>
            </select>
        </div>
        


        <div class="FPNavElement">
            <?php echo esc_html__('Limit:','si-flash-products');?>
            <input class="FP_limit" name="FP_limit" type="number" title="<?php echo esc_html__('Results per page. max value is 500','si-flash-products');?>" min="1" max="500" step="1" value="50" onchange="FP_search_product();" style="width:65px">
        </div>

        <div class="FPNavElement">
            <?php echo esc_html__('Page:','si-flash-products');?>
            <input class="FP_offset" name="FP_offset" type="number" placeholder="type a number" step="1" value="0" total_pages="0" onchange="FP_search_product();" style="width:65px">
        </div>

        <div class="FPNavElement">
            <?php echo esc_html__('Founds:','si-flash-products');?>
            <strong class="FPfound"></strong>
        </div>


        <button class="FPNavElement" style="margin-left:auto;" onclick="FP_search_product();"><?php echo esc_html__('SEARCH','si-flash-products');?></button>
    </div>

    <div class="FPContainer">

        <div class="FPCard FPdefaultCard" fp_title="" fp_short_title="" fp_slang_title="" fp_description="" fp_exerp="" fp_categories="" fp_tag="" fp_ingredient="" fp_macro_cat="" fp_allerg="" fp_sticker="" fp_temp="" fp_img="<?php echo wc_placeholder_img_src('300'); ?>" fp_gallery="">

            <div class="FPCardHead">
                <img src="<?php echo wc_placeholder_img_src('300'); ?>" onclick="FP_Open_Detail(jQuery(this).closest('.FPCard'))">
                <div class="FORapidImport" onclick="FP_Import_product(jQuery(this).closest('.FPCard'));">
                    <span class="dashicons dashicons-plus"></span>
                </div>
            </div>

            <div class="FPCardFoot" onclick="FP_Open_Detail(jQuery(this).closest('.FPCard'))">
                <strong class="FPCardTitle">
                    <?php echo esc_html__('Product Title','si-flash-products');?>
                </strong>
            </div>

        </div>

    </div>

    <div class="FPSideBar" style="display:none;">

    </div>

    <div class="FPDetailSection" style="display:none;">
        <div class="FPDetailHead">
            <strong><?php echo esc_html__('Product Title','si-flash-products');?></strong>
            <div onclick="FP_Close_Detail(this)" class="FPClose" style="margin-left:auto;"><?php echo esc_html__('CLOSE','si-flash-products');?></div>
        </div>
        <div class="FPDetailBody">
            <div class="FPDetailBodyImages">
                
            </div>
            <div class="FPDetailBodyCol">

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Exerpt:','si-flash-products');?></strong>
                    <p fp-block="exerpt"></p>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Description:','si-flash-products');?></strong>
                    <p fp-block="description"></p>
                </div>

            </div>
            <div class="FPDetailBodyCol">

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Categories:','si-flash-products');?></strong>
                    <div fp-block="fp_categories">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Tags:','si-flash-products');?></strong>
                    <div fp-block="fp_tag">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Ingredients:','si-flash-products');?></strong>
                    <div fp-block="fp_ingredient">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Allergene:','si-flash-products');?></strong>
                    <div fp-block="fp_allerg">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Sticker:','si-flash-products');?></strong>
                    <div fp-block="fp_sticker">
                        <div class="PFCloud">blank detail</div>
                    </div>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Temperature:','si-flash-products');?></strong>
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







