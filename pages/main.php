<?php

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    return;
}

// Use Transients for API results
$sifp_categories = get_transient( 'sifp_api_categories' );
if ( false === $sifp_categories || ! is_object( $sifp_categories ) ) {
    $sifp_response = wp_remote_get( 'https://flashproducts.studioimmens.com/wp-json/flash_products/v1/taxonomy?tax=product_cat', array( 'timeout' => 5 ) );
    
    if ( is_wp_error( $sifp_response ) ) {
        $sifp_categories = (object) array( 'result' => array() );
    } else {
        $sifp_categories = json_decode( wp_remote_retrieve_body( $sifp_response ) );
        if ( ! is_object( $sifp_categories ) ) {
            $sifp_categories = (object) array( 'result' => array() );
        }
    }
}

// Final safety check
if ( ! is_object( $sifp_categories ) ) {
    $sifp_categories = (object) array( 'result' => array() );
}
    
    // Add Local Categories to the list
    $sifp_upload_dir = wp_upload_dir();
    $sifp_local_db_path = $sifp_upload_dir['basedir'] . '/si-flash-products/local_products.json';
    if ( file_exists( $sifp_local_db_path ) ) {
        $sifp_local_data = json_decode( file_get_contents( $sifp_local_db_path ), true );
        if ( is_array($sifp_local_data) ) {
            $sifp_local_cats = array_unique(array_column($sifp_local_data, 'sifp_categories'));
            if ( ! isset($sifp_categories->result) || ! is_array($sifp_categories->result) ) $sifp_categories->result = array();
            
            foreach ($sifp_local_cats as $sifp_cat_name) {
                // Check if already exists in remote
                $sifp_exists = false;
                foreach ($sifp_categories->result as $sifp_remote_cat) {
                    if ( isset($sifp_remote_cat->name) && strtolower($sifp_remote_cat->name) === strtolower($sifp_cat_name) ) {
                        $sifp_exists = true;
                        break;
                    }
                }
                if ( ! $sifp_exists ) {
                    $sifp_categories->result[] = (object) array(
                        'name' => $sifp_cat_name,
                        'slug' => sanitize_title($sifp_cat_name)
                    );
                }
            }
        }
    }

    set_transient( 'sifp_api_categories', $sifp_categories, DAY_IN_SECONDS );

// Delete stale transient to force refresh
delete_transient( 'sifp_api_languages' );

$sifp_languages = get_transient( 'sifp_api_languages' );
if ( false === $sifp_languages ) {
    $sifp_response = wp_remote_get( 'https://flashproducts.studioimmens.com/wp-json/flash_products/v1/taxonomy?tax=Languages', array( 'timeout' => 5 ) );
    
    if ( is_wp_error( $sifp_response ) ) {
        // Fallback languages when remote API is unreachable
        $sifp_languages = (object) array(
            'result' => array(
                (object) array( 'slug' => 'it', 'name' => 'Italiano' ),
                (object) array( 'slug' => 'en', 'name' => 'English' ),
            ),
        );
    } else {
        $sifp_languages = json_decode( wp_remote_retrieve_body( $sifp_response ) );
        if ( ! is_object( $sifp_languages ) || ! isset( $sifp_languages->result ) || ! is_array( $sifp_languages->result ) ) {
            $sifp_languages = (object) array(
                'result' => array(
                    (object) array( 'slug' => 'it', 'name' => 'Italiano' ),
                    (object) array( 'slug' => 'en', 'name' => 'English' ),
                ),
            );
        }
    }
    
    set_transient( 'sifp_api_languages', $sifp_languages, DAY_IN_SECONDS );
}

/**
 * Render Pagination Bar
 */
function sifp_render_pagination_bar() {
    ?>
    <div class="sifp-pagination-bar">
        <div class="sifp-nav-element sifp-nav-element--inline">
            <label><?php echo esc_html__('Order:','si-flash-products');?></label>
            <select class="sifp-orderby" name="sifp_orderby">
                <option value="name"> <?php esc_html_e('By Name', 'si-flash-products'); ?> </option>
                <option value="date"> <?php esc_html_e('By Date', 'si-flash-products'); ?> </option>
            </select>
        </div>

        <div class="sifp-nav-element sifp-nav-element--inline">
            <label><?php echo esc_html__('Limit:','si-flash-products');?></label>
            <input class="sifp-limit sifp-u-w-65" name="sifp_limit" type="number" title="<?php echo esc_html__('Results per page. max value is 1000','si-flash-products');?>" min="1" max="1000" step="1" value="100">
        </div>

        <div class="sifp-nav-element sifp-nav-element--inline">
            <label><?php echo esc_html__('Page:','si-flash-products');?></label>
            <input class="sifp-offset sifp-u-w-65" name="sifp_offset" type="number" placeholder="<?php esc_attr_e('type a number', 'si-flash-products'); ?>" step="1" value="0" total_pages="0">
        </div>

        <div class="sifp-nav-element sifp-nav-element--inline sifp-u-ml-auto">
            <label><?php echo esc_html__('Founds:','si-flash-products');?></label>
            <strong class="sifp-found"></strong>
        </div>

        <div class="sifp-nav-element sifp-nav-element--inline bulk-actions sifp-u-ml-auto sifp-u-hidden">
            <input type="checkbox" class="sifp-select-all-products">
            <label><?php esc_html_e('Select All', 'si-flash-products'); ?></label>
            <button class="sifp-bulk-import-btn button-primary sifp-u-ml-10"><?php esc_html_e('Import Selected', 'si-flash-products'); ?> (<span class="selected-count">0</span>)</button>
        </div>
    </div>
    <?php
}

?>

<div id="sifp-admin-content" class="sifp-main-container">

    <div class="sifp-header-logo">
        <img src="<?php echo SIFProd_URL . 'assets/flash-products-logo-128.png'; ?>" class="sifp-admin-logo" alt="SI Flash Products Logo">
        <h1 class="sifp-plugin-title">Flash Products</h1>
    </div>

    <div class="sifp-navbar">

        <div class="sifp-nav-element">
            <label><?php echo esc_html__('Languages:','si-flash-products');?></label>
            <select class="sifp-languages" name="sifp_languages">
                <option value=""> <?php esc_html_e( '- select -', 'si-flash-products' ); ?> </option>
                <?php
                $sifp_locale       = get_locale();
                $sifp_detected_lang = strpos( $sifp_locale, 'it' ) === 0 ? 'it' : 'en';
                $sifp_selected_lang = ! empty( $_GET['languages'] ) ? sanitize_text_field( wp_unslash( $_GET['languages'] ) ) : $sifp_detected_lang; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

                if ( isset( $sifp_languages->result ) && is_array( $sifp_languages->result ) ) {
                    foreach ($sifp_languages->result as $sifp_key => $sifp_value) {
                        $sifp_is_selected = ( $sifp_value->slug === $sifp_selected_lang );
                        echo '<option value="'.esc_attr($sifp_value->slug).'"' . ( $sifp_is_selected ? ' selected="selected"' : '' ) . '>'.esc_html($sifp_value->name).'</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="sifp-nav-element">
            <label><?php echo esc_html__('Categories:','si-flash-products');?></label>
            <select class="sifp-categories" name="sifp_categories">
                <option value=""> <?php esc_html_e( '- select -', 'si-flash-products' ); ?> </option>
                <?php
                $sifp_lang_cat_map = array(
                    'it' => array( 'Elettronica', 'Casa', 'Abbigliamento', 'Bellezza', 'Sport' ),
                    'en' => array( 'Electronics', 'Home', 'Clothing', 'Beauty', 'Sports' ),
                );
                $sifp_valid_cats = ! empty( $sifp_selected_lang ) && isset( $sifp_lang_cat_map[ $sifp_selected_lang ] ) ? $sifp_lang_cat_map[ $sifp_selected_lang ] : null;

                if ( isset( $sifp_categories->result ) && is_array( $sifp_categories->result ) ) {
                    foreach ($sifp_categories->result as $sifp_key => $sifp_value) {
                        if ( $sifp_valid_cats !== null && ! in_array( $sifp_value->name, $sifp_valid_cats ) ) {
                            continue;
                        }
                        echo '<option value="'.esc_attr($sifp_value->slug).'">'.esc_html($sifp_value->name).'</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="sifp-nav-element">
            <label><?php echo esc_html__('Keyword:','si-flash-products');?></label>
            <input class="sifp-keyword" name="sifp_keyword" type="search" placeholder="<?php esc_attr_e('type a keyword', 'si-flash-products'); ?>">
        </div>
        
        <div class="sifp-nav-element">
            <label><?php echo esc_html__('Source:','si-flash-products');?></label>
            <select class="sifp-source" name="sifp_source">
                <option value="all"> <?php esc_html_e('All Sources', 'si-flash-products'); ?> </option>
                <option value="local"> <?php esc_html_e('Local JSON DB', 'si-flash-products'); ?> </option>
                <option value="remote"> <?php esc_html_e('Remote Databases', 'si-flash-products'); ?> </option>
            </select>
        </div>

        <button class="sifp-search-btn sifp-u-ml-auto"><?php echo esc_html__('SEARCH','si-flash-products');?></button>
    </div>

    <?php sifp_render_pagination_bar(); ?>

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

    <?php sifp_render_pagination_bar(); ?>

    <div class="sifp-sidebar sifp-u-hidden">

    </div>

    <div class="sifp-detail-section sifp-u-hidden">
        <div class="sifp-detail-section__head">
            <input type="text" class="sifp-detail-title editable-title" sifp-edit="post_title" value="">
            <div class="sifp-button--close sifp-u-ml-auto"><?php echo esc_html__('CLOSE','si-flash-products');?></div>
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
    <div class="sifp-background-section sifp-u-hidden"></div>

</div>







