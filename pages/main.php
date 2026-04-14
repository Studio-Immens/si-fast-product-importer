<?php

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    return;
}

// Use Transients for API results
$categories = get_transient( 'sifp_api_categories' );
if ( false === $categories ) {
    $response = wp_remote_get( 'https://flashproducts.studioimmens.com/wp-json/flash_products/v1/taxonomy?tax=product_cat' );
    
    if ( is_wp_error( $response ) ) {
        $categories = (object) array( 'result' => array() );
    } else {
        $categories = json_decode( wp_remote_retrieve_body( $response ) );
    }
    
    // Add Local Categories to the list
    $upload_dir = wp_upload_dir();
    $local_db_path = $upload_dir['basedir'] . '/si-flash-products/local_products.json';
    if ( file_exists( $local_db_path ) ) {
        $local_data = json_decode( file_get_contents( $local_db_path ), true );
        if ( is_array($local_data) ) {
            $local_cats = array_unique(array_column($local_data, 'sifp_categories'));
            if ( ! isset($categories->result) || ! is_array($categories->result) ) $categories->result = array();
            
            foreach ($local_cats as $cat_name) {
                // Check if already exists in remote
                $exists = false;
                foreach ($categories->result as $remote_cat) {
                    if ( isset($remote_cat->name) && strtolower($remote_cat->name) === strtolower($cat_name) ) {
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

    set_transient( 'sifp_api_categories', $categories, DAY_IN_SECONDS );
}

$languages = get_transient( 'sifp_api_languages' );
if ( false === $languages ) {
    $response = wp_remote_get( 'https://flashproducts.studioimmens.com/wp-json/flash_products/v1/taxonomy?tax=Languages' );
    
    if ( is_wp_error( $response ) ) {
        $languages = (object) array( 'result' => array() );
    } else {
        $languages = json_decode( wp_remote_retrieve_body( $response ) );
    }
    
    set_transient( 'sifp_api_languages', $languages, DAY_IN_SECONDS );
}

?>

<div id="sifp-admin-content" class="sifp-main-container">

    <div class="sifp-navbar">

        <div class="sifp-nav-element">
            <?php echo esc_html__('Languages:','si-flash-products');?>
            <select class="sifp-languages" name="sifp_languages">
                <option value=""> <?php esc_html_e( '- select -', 'si-flash-products' ); ?> </option>
                <?php 
                if ( isset( $languages->result ) && is_array( $languages->result ) ) {
                    foreach ($languages->result as $key => $value) {
                        echo '<option value="'.esc_attr($value->slug).'">'.esc_html($value->name).'</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="sifp-nav-element">
            <?php echo esc_html__('Categories:','si-flash-products');?>
            <select class="sifp-categories" name="sifp_categories">
                <option value=""> <?php esc_html_e( '- select -', 'si-flash-products' ); ?> </option>
                <?php 
                if ( isset( $categories->result ) && is_array( $categories->result ) ) {
                    foreach ($categories->result as $key => $value) {
                        echo '<option value="'.esc_attr($value->slug).'">'.esc_html($value->name).'</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="sifp-nav-element">
            <?php echo esc_html__('Keyword:','si-flash-products');?>
            <input class="sifp-keyword" name="sifp_keyword" type="search" placeholder="<?php esc_attr_e('type a keyword', 'si-flash-products'); ?>">
        </div>
        

        <div class="sifp-nav-element">
            <?php echo esc_html__('Source:','si-flash-products');?>
            <select class="sifp-source" name="sifp_source">
                <option value="all"> <?php esc_html_e('All Sources', 'si-flash-products'); ?> </option>
                <option value="local"> <?php esc_html_e('Local JSON DB', 'si-flash-products'); ?> </option>
                <option value="remote"> <?php esc_html_e('Remote Databases', 'si-flash-products'); ?> </option>
            </select>
        </div>

        <div class="sifp-nav-element">
            <?php echo esc_html__('Order:','si-flash-products');?>
            <select class="sifp-orderby" name="sifp_orderby">
                <option value="name"> <?php esc_html_e('By Name', 'si-flash-products'); ?> </option>
                <option value="date"> <?php esc_html_e('By Date', 'si-flash-products'); ?> </option>
            </select>
        </div>
        


        <div class="sifp-nav-element">
            <?php echo esc_html__('Limit:','si-flash-products');?>
            <input class="sifp-limit" name="sifp_limit" type="number" title="<?php echo esc_html__('Results per page. max value is 1000','si-flash-products');?>" min="1" max="1000" step="1" value="100" style="width:65px">
        </div>

        <div class="sifp-nav-element">
            <?php echo esc_html__('Page:','si-flash-products');?>
            <input class="sifp-offset" name="sifp_offset" type="number" placeholder="<?php esc_attr_e('type a number', 'si-flash-products'); ?>" step="1" value="0" total_pages="0" style="width:65px">
        </div>

        <div class="sifp-nav-element">
            <?php echo esc_html__('Founds:','si-flash-products');?>
            <strong class="sifp-found"></strong>
        </div>

        <div class="sifp-nav-element bulk-actions" style="display:none; margin-left: 20px;">
            <input type="checkbox" id="select_all_products">
            <label for="select_all_products"><?php esc_html_e('Select All', 'si-flash-products'); ?></label>
            <button class="sifp-bulk-import-btn button-primary" style="margin-left: 10px;"><?php esc_html_e('Import Selected', 'si-flash-products'); ?> (<span class="selected-count">0</span>)</button>
        </div>


        <button class="sifp-nav-element sifp-search-btn" style="margin-left:auto;"><?php echo esc_html__('SEARCH','si-flash-products');?></button>
    </div>

    <div class="sifp-container">

        <div class="sifp-card sifp-default-card" data-product="">

            <div class="sifp-card-head">
                <div class="sifp-card-selection">
                    <input type="checkbox" class="sifp-select-product">
                </div>
                <img class="sifp-card-img" src="<?php echo esc_url( wc_placeholder_img_src('300') ); ?>">
                <div class="sifp-rapid-import" title="<?php esc_attr_e('Rapid Import', 'si-flash-products'); ?>">
                    <span class="dashicons dashicons-plus"></span>
                </div>
            </div>

            <div class="sifp-card-foot">
                <strong class="sifp-card-title">
                    <?php echo esc_html__('Product Title','si-flash-products');?>
                </strong>
            </div>

        </div>

    </div>

    <div class="sifp-sidebar" style="display:none;">

    </div>

    <div class="sifp-detail-section" style="display:none;">
        <div class="sifp-detail-section__head">
            <input type="text" class="sifp-detail-title editable-title" sifp-edit="post_title" value="">
            <div class="sifp-button--close" style="margin-left:auto;"><?php echo esc_html__('CLOSE','si-flash-products');?></div>
        </div>
        <div class="sifp-detail-section__body">
            <div class="sifp-detail-body-images">
                
            </div>
            <div class="sifp-detail-section__col">

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('Excerpt:','si-flash-products');?></strong>
                    <textarea sifp-edit="post_excerpt" rows="3"></textarea>
                </div>

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('Description:','si-flash-products');?></strong>
                    <textarea sifp-edit="post_content" rows="10"></textarea>
                </div>

            </div>
            <div class="sifp-detail-section__col">

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('Categories:','si-flash-products');?></strong>
                    <input type="text" sifp-edit="sifp_categories" placeholder="<?php esc_attr_e('cat1, cat2...', 'si-flash-products'); ?>">
                </div>

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('Tags:','si-flash-products');?></strong>
                    <input type="text" sifp-edit="sifp_tag" placeholder="<?php esc_attr_e('tag1, tag2...', 'si-flash-products'); ?>">
                </div>

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('Ingredients:','si-flash-products');?></strong>
                    <input type="text" sifp-edit="sifp_ingredient" placeholder="<?php esc_attr_e('ing1, ing2...', 'si-flash-products'); ?>">
                </div>

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('Allergens:','si-flash-products');?></strong>
                    <input type="text" sifp-edit="sifp_allerg" placeholder="<?php esc_attr_e('all1, all2...', 'si-flash-products'); ?>">
                </div>

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('Stickers:','si-flash-products');?></strong>
                    <input type="text" sifp-edit="sifp_sticker" placeholder="<?php esc_attr_e('sticker1, sticker2...', 'si-flash-products'); ?>">
                </div>

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('Temperature:','si-flash-products');?></strong>
                    <input type="text" sifp-edit="sifp_temp" placeholder="<?php esc_attr_e('Cold, Hot...', 'si-flash-products'); ?>">
                </div>

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('SKU:','si-flash-products');?></strong>
                    <input type="text" sifp-edit="sku" placeholder="<?php esc_attr_e('PROD-001', 'si-flash-products'); ?>">
                </div>

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('Regular Price:','si-flash-products');?></strong>
                    <input type="number" step="0.01" sifp-edit="regular_price">
                </div>

                <div class="sifp-detail-block">
                    <strong><?php echo esc_html__('Sale Price:','si-flash-products');?></strong>
                    <input type="number" step="0.01" sifp-edit="sale_price">
                </div>

            </div>
        </div>
        <div class="sifp-detail-section__foot">
        <button class="button-primary sifp-import-edited-btn">
            <span class="dashicons dashicons-download"></span>
            <?php esc_html_e('Import with Edited Values', 'si-flash-products'); ?>
        </button>
    </div>
    </div>
    <div class="sifp-background-section" style="display:none;"></div>

</div>







