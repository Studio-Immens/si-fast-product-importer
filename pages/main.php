<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

// Use Transients for API results
$categories = get_transient( 'fp_api_categories' );
if ( false === $categories ) {
    $response = wp_remote_get( 'https://flashproducts.studioimmens.com/wp-json/flash_products/v1/taxonomy?tax=product_cat' );
    $categories = json_decode( wp_remote_retrieve_body( $response ) );
    
    // Add Local Categories to the list
    $local_db_path = SIFProd_PLUGIN_PATH . 'includes/local_products.json';
    if ( file_exists( $local_db_path ) ) {
        $local_data = json_decode( file_get_contents( $local_db_path ), true );
        if ( is_array($local_data) ) {
            $local_cats = array_unique(array_column($local_data, 'fp_categories'));
            if ( ! isset($categories->result) ) $categories = (object) array('result' => array());
            
            foreach ($local_cats as $cat_name) {
                // Check if already exists in remote
                $exists = false;
                foreach ($categories->result as $remote_cat) {
                    if ( strtolower($remote_cat->name) === strtolower($cat_name) ) {
                        $exists = true;
                        break;
                    }
                }
                if ( ! $exists ) {
                    $categories->result[] = (object) array(
                        'name' => $cat_name,
                        'slug' => sanitize_title($cat_name)
                    );
                }
            }
        }
    }

    set_transient( 'fp_api_categories', $categories, DAY_IN_SECONDS );
}

$languages = get_transient( 'fp_api_languages' );
if ( false === $languages ) {
    $response = wp_remote_get( 'https://flashproducts.studioimmens.com/wp-json/flash_products/v1/taxonomy?tax=Languages' );
    $languages = json_decode( wp_remote_retrieve_body( $response ) );
    set_transient( 'fp_api_languages', $languages, DAY_IN_SECONDS );
}

?>

<div class="FPMainContainer">

    <div class="FPNavBar">

        <div class="FPNavElement">
            <?php echo esc_html__('Languages:','si-flash-products');?>
            <select class="FP_languages" name="FP_languages">
                <option value=""> - select - </option>
                <?php 
                if ( isset( $languages->result ) && is_array( $languages->result ) ) {
                    foreach ($languages->result as $key => $value) {
                        echo '<option value="'.esc_attr($value->slug).'">'.esc_html($value->name).'</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="FPNavElement">
            <?php echo esc_html__('Categories:','si-flash-products');?>
            <select class="FP_categories" name="FP_categories">
                <option value=""> - select - </option>
                <?php 
                if ( isset( $categories->result ) && is_array( $categories->result ) ) {
                    foreach ($categories->result as $key => $value) {
                        echo '<option value="'.esc_attr($value->slug).'">'.esc_html($value->name).'</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="FPNavElement">
            <?php echo esc_html__('Keyword:','si-flash-products');?>
            <input class="FP_keyword" name="FP_keyword" type="search" placeholder="<?php esc_attr_e('type a keyword', 'si-flash-products'); ?>">
        </div>
        

        <div class="FPNavElement">
            <?php echo esc_html__('Source:','si-flash-products');?>
            <select class="FP_source" name="FP_source">
                <option value="all"> <?php esc_html_e('All Sources', 'si-flash-products'); ?> </option>
                <option value="local"> <?php esc_html_e('Local JSON DB', 'si-flash-products'); ?> </option>
                <option value="remote"> <?php esc_html_e('Remote Databases', 'si-flash-products'); ?> </option>
            </select>
        </div>

        <div class="FPNavElement">
            <?php echo esc_html__('Order:','si-flash-products');?>
            <select class="FP_orderby" name="FP_orderby">
                <option value="name"> <?php esc_html_e('By Name', 'si-flash-products'); ?> </option>
                <option value="date"> <?php esc_html_e('By Date', 'si-flash-products'); ?> </option>
            </select>
        </div>
        


        <div class="FPNavElement">
            <?php echo esc_html__('Limit:','si-flash-products');?>
            <input class="FP_limit" name="FP_limit" type="number" title="<?php echo esc_html__('Results per page. max value is 1000','si-flash-products');?>" min="1" max="1000" step="1" value="100" style="width:65px">
        </div>

        <div class="FPNavElement">
            <?php echo esc_html__('Page:','si-flash-products');?>
            <input class="FP_offset" name="FP_offset" type="number" placeholder="<?php esc_attr_e('type a number', 'si-flash-products'); ?>" step="1" value="0" total_pages="0" style="width:65px">
        </div>

        <div class="FPNavElement">
            <?php echo esc_html__('Founds:','si-flash-products');?>
            <strong class="FPfound"></strong>
        </div>

        <div class="FPNavElement bulk-actions" style="display:none; margin-left: 20px;">
            <input type="checkbox" id="select_all_products">
            <label for="select_all_products"><?php esc_html_e('Select All', 'si-flash-products'); ?></label>
            <button class="FP_bulk_import_btn button-primary" style="margin-left: 10px;"><?php esc_html_e('Import Selected', 'si-flash-products'); ?> (<span class="selected-count">0</span>)</button>
        </div>


        <button class="FPNavElement FP_search_btn" style="margin-left:auto;"><?php echo esc_html__('SEARCH','si-flash-products');?></button>
    </div>

    <div class="FPContainer">

        <div class="FPCard FPdefaultCard" fp_title="" fp_short_title="" fp_slang_title="" fp_description="" fp_exerp="" fp_categories="" fp_tag="" fp_ingredient="" fp_macro_cat="" fp_allerg="" fp_sticker="" fp_temp="" fp_img="<?php echo wc_placeholder_img_src('300'); ?>" fp_gallery="">

            <div class="FPCardHead">
                <div class="FPCardSelection">
                    <input type="checkbox" class="fp-product-select">
                </div>
                <img class="FPCardImg" src="<?php echo wc_placeholder_img_src('300'); ?>">
                <div class="FORapidImport">
                    <span class="dashicons dashicons-plus"></span>
                </div>
            </div>

            <div class="FPCardFoot">
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
            <input type="text" class="FPDetailTitle editable-title" fp-edit="post_title" value="">
            <div class="FPClose" style="margin-left:auto;"><?php echo esc_html__('CLOSE','si-flash-products');?></div>
        </div>
        <div class="FPDetailBody">
            <div class="FPDetailBodyImages">
                
            </div>
            <div class="FPDetailBodyCol">

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Excerpt:','si-flash-products');?></strong>
                    <textarea fp-edit="post_excerpt" rows="3"></textarea>
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Description:','si-flash-products');?></strong>
                    <textarea fp-edit="post_content" rows="10"></textarea>
                </div>

            </div>
            <div class="FPDetailBodyCol">

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Categories:','si-flash-products');?></strong>
                    <input type="text" fp-edit="fp_categories" placeholder="cat1, cat2...">
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Tags:','si-flash-products');?></strong>
                    <input type="text" fp-edit="fp_tag" placeholder="tag1, tag2...">
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Ingredients:','si-flash-products');?></strong>
                    <input type="text" fp-edit="fp_ingredient" placeholder="ing1, ing2...">
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Allergens:','si-flash-products');?></strong>
                    <input type="text" fp-edit="fp_allerg" placeholder="all1, all2...">
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Stickers:','si-flash-products');?></strong>
                    <input type="text" fp-edit="fp_sticker" placeholder="sticker1, sticker2...">
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Temperature:','si-flash-products');?></strong>
                    <input type="text" fp-edit="fp_temp" placeholder="Cold, Hot...">
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('SKU:','si-flash-products');?></strong>
                    <input type="text" fp-edit="sku" placeholder="PROD-001">
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Regular Price:','si-flash-products');?></strong>
                    <input type="number" step="0.01" fp-edit="regular_price">
                </div>

                <div class="FPDetailBlock">
                    <strong><?php echo esc_html__('Sale Price:','si-flash-products');?></strong>
                    <input type="number" step="0.01" fp-edit="sale_price">
                </div>

            </div>
        </div>
        <div class="FPDetailFoot">
            <button class="button-primary FP_import_edited_btn">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e('Import with Edited Values', 'si-flash-products'); ?>
            </button>
        </div>
    </div>
    <div class="FPBackGroundSection" style="display:none;"></div>

</div>







