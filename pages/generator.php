<?php
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

if ( ! current_user_can( 'manage_options' ) ) {
    return;
}

$categories = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
) );

$tags = get_terms( array(
    'taxonomy'   => 'product_tag',
    'hide_empty' => false,
) );
$api_key = sifp_get_setting('sifp_gemini_api_key');
?>

<div class="sifp-main-container sifp-generator-section">
    <div class="sifp-generator-header">
        <div class="sifp-generator-header__title-group">
            <h2><?php esc_html_e('Product Generator', 'si-flash-products'); ?></h2>
            <p><?php esc_html_e('Create your products in a modern, creative and fast way.', 'si-flash-products'); ?></p>
        </div>
        
        <div class="sifp-generator-header__presets">
            <button type="button" class="sifp-button sifp-button--preset" data-preset="simple"><?php esc_html_e('Simple Draft', 'si-flash-products'); ?></button>
            <button type="button" class="sifp-button sifp-button--preset" data-preset="physical"><?php esc_html_e('Physical Item', 'si-flash-products'); ?></button>
            <button type="button" class="sifp-button sifp-button--preset" data-preset="premium"><?php esc_html_e('Premium Product', 'si-flash-products'); ?></button>
            <button type="button" class="sifp-button sifp-button--preset" data-preset="virtual"><?php esc_html_e('Virtual Service', 'si-flash-products'); ?></button>
            <button type="button" class="sifp-button sifp-button--preset" data-preset="downloadable"><?php esc_html_e('Digital Download', 'si-flash-products'); ?></button>
        </div>
    </div>

    <div class="sifp-generator-layout">
        <!-- Main Form Column -->
        <div class="sifp-generator-layout__main">
            <div id="sifp_generator_notices"></div>
            <form id="sifp_ai_product_form" class="sifp-form--modern">
                <!-- Basic Info Card -->
                <div class="sifp-card">
                    <div class="sifp-card__header">
                        <span class="dashicons dashicons-admin-page"></span>
                        <h3><?php esc_html_e('Basic Information', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="sifp-card__body">
                        <div class="sifp-form-field full">
                            <label><?php esc_html_e('Product Title', 'si-flash-products'); ?></label>
                            <input type="text" name="post_title" id="out_post_title" placeholder="<?php esc_attr_e('Product name...', 'si-flash-products'); ?>" required>
                        </div>
                        
                        <div class="sifp-form-field full">
                            <label><?php esc_html_e('Short Description', 'si-flash-products'); ?></label>
                            <textarea name="post_excerpt" id="out_post_excerpt" rows="2" placeholder="<?php esc_attr_e('An eye-catching summary...', 'si-flash-products'); ?>"></textarea>
                        </div>

                        <div class="sifp-form-field full">
                            <label><?php esc_html_e('Full Description', 'si-flash-products'); ?></label>
                            <?php 
                            wp_editor( '', 'out_post_content', array(
                                'textarea_name' => 'post_content',
                                'textarea_rows' => 8,
                                'media_buttons' => true,
                                'tinymce'       => true,
                                'quicktags'     => true
                            ) ); 
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Inventory & Pricing Card -->
                <div class="sifp-card">
                    <div class="sifp-card__header">
                        <span class="dashicons dashicons-cart"></span>
                        <h3><?php esc_html_e('Pricing and Inventory', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="sifp-card__body grid-2">
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('Product Type', 'si-flash-products'); ?></label>
                            <div class="sifp-form-field__checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_virtual" id="out_is_virtual" value="yes"> <?php esc_html_e('Virtual', 'si-flash-products'); ?>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_downloadable" id="out_is_downloadable" value="yes"> <?php esc_html_e('Downloadable', 'si-flash-products'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('Regular Price (€)', 'si-flash-products'); ?></label>
                            <input type="number" step="0.01" name="regular_price" id="out_regular_price" placeholder="0.00">
                        </div>
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('Sale Price (€)', 'si-flash-products'); ?></label>
                            <input type="number" step="0.01" name="sale_price" id="out_sale_price" placeholder="0.00">
                        </div>
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('SKU', 'si-flash-products'); ?></label>
                            <input type="text" name="sku" id="out_sku" placeholder="PROD-001">
                        </div>
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('Stock Status', 'si-flash-products'); ?></label>
                            <select name="stock_status" id="out_stock_status">
                                <option value="instock"><?php esc_html_e('In Stock', 'si-flash-products'); ?></option>
                                <option value="outofstock"><?php esc_html_e('Out of Stock', 'si-flash-products'); ?></option>
                                <option value="onbackorder"><?php esc_html_e('On Backorder', 'si-flash-products'); ?></option>
                            </select>
                        </div>
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('Quantity', 'si-flash-products'); ?></label>
                            <input type="number" name="stock_qty" id="out_stock_qty" value="10">
                        </div>
                    </div>
                </div>

                <!-- Logistics Card -->
                <div class="sifp-card">
                    <div class="sifp-card__header">
                        <span class="dashicons dashicons-performance"></span>
                        <h3><?php esc_html_e('Dimensions and Weight', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="sifp-card__body grid-4">
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('Weight (kg)', 'si-flash-products'); ?></label>
                            <input type="text" name="weight" id="out_weight" placeholder="0.00">
                        </div>
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('Length (cm)', 'si-flash-products'); ?></label>
                            <input type="text" name="length" id="out_length" placeholder="0">
                        </div>
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('Width (cm)', 'si-flash-products'); ?></label>
                            <input type="text" name="width" id="out_width" placeholder="0">
                        </div>
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('Height (cm)', 'si-flash-products'); ?></label>
                            <input type="text" name="height" id="out_height" placeholder="0">
                        </div>
                    </div>
                </div>

                <!-- Attributes Card -->
                <div class="sifp-card">
                    <div class="sifp-card__header">
                        <span class="dashicons dashicons-tag"></span>
                        <h3><?php esc_html_e('Product Attributes', 'si-flash-products'); ?></h3>
                        <button type="button" id="sifp_add_attribute_btn" class="sifp-button sifp-button--preset" style="margin-left: auto; font-size: 10px;">
                            <span class="dashicons dashicons-plus"></span> <?php esc_html_e('Add Attribute', 'si-flash-products'); ?>
                        </button>
                    </div>
                    <div class="sifp-card__body" id="sifp_attributes_container">
                        <!-- Attributes will be added here -->
                        <div class="sifp-attribute-row-template" style="display:none;">
                            <div class="sifp-attribute-row grid-3" style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 15px; margin-bottom: 15px; align-items: flex-end;">
                                <div class="sifp-form-field" style="margin-bottom: 0;">
                                    <label><?php esc_html_e('Name (e.g. Color)', 'si-flash-products'); ?></label>
                                    <input type="text" class="sifp-attribute-row__name" placeholder="<?php esc_attr_e('Color', 'si-flash-products'); ?>">
                                </div>
                                <div class="sifp-form-field" style="margin-bottom: 0;">
                                    <label><?php esc_html_e('Values (pipe separated)', 'si-flash-products'); ?></label>
                                    <input type="text" class="sifp-attribute-row__values" placeholder="<?php esc_attr_e('Red | Blue | Green', 'si-flash-products'); ?>">
                                </div>
                                <button type="button" class="sifp-attribute-row__remove" style="background: var(--sifp-error); color: #fff; border: none; border-radius: 4px; padding: 10px; cursor: pointer;">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Taxonomy & Media Card -->
                <div class="sifp-card">
                    <div class="sifp-card__header">
                        <span class="dashicons dashicons-category"></span>
                        <h3><?php esc_html_e('Categories and Media', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="sifp-card__body">
                        <div class="sifp-form-field full">
                            <label><?php esc_html_e('Categories', 'si-flash-products'); ?></label>
                            <div class="sifp-taxonomy-selector" data-tax="product_cat">
                                <input type="text" name="sifp_categories" id="out_sifp_categories" autocomplete="off" placeholder="<?php esc_attr_e('Start typing to search categories...', 'si-flash-products'); ?>">
                                <div class="sifp-autocomplete-results"></div>
                            </div>
                        </div>
                        <div class="sifp-form-field full">
                            <label><?php esc_html_e('Tags', 'si-flash-products'); ?></label>
                            <div class="sifp-taxonomy-selector" data-tax="product_tag">
                                <input type="text" name="sifp_tag" id="out_sifp_tag" autocomplete="off" placeholder="<?php esc_attr_e('Start typing to search tags...', 'si-flash-products'); ?>">
                                <div class="sifp-autocomplete-results"></div>
                            </div>
                        </div>
                        <div class="sifp-form-field full">
                            <label><?php esc_html_e('Main Image URL', 'si-flash-products'); ?></label>
                            <div class="sifp-image-input-group">
                                <input type="text" name="sifp_img" id="out_sifp_img" placeholder="https://example.com/image.jpg">
                                <button type="button" id="sifp_select_image_btn" class="sifp-button sifp-button--preset">
                                    <span class="dashicons dashicons-admin-media"></span>
                                    <?php esc_html_e('Browse', 'si-flash-products'); ?>
                                </button>
                                <div id="sifp_img_preview_container" class="sifp-image-preview"></div>
                            </div>
                        </div>
                        <div class="sifp-form-field full">
                            <label><?php esc_html_e('Product Gallery', 'si-flash-products'); ?></label>
                            <div class="sifp-gallery-input-group">
                                <button type="button" id="sifp_select_gallery_btn" class="sifp-button sifp-button--preset">
                                    <span class="dashicons dashicons-images-alt2"></span>
                                    <?php esc_html_e('Add Gallery Images', 'si-flash-products'); ?>
                                </button>
                                <input type="hidden" name="sifp_gallery" id="out_sifp_gallery">
                                <div id="sifp_gallery_preview_container" class="sifp-gallery-preview-grid"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- AI Assistant Column (Sidebar) -->
        <div class="sifp-generator-layout__sidebar">
            <div class="sifp-card sifp-card--ai sticky">
                <div class="sifp-card__header sifp-card__header--ai">
                    <div class="sifp-ai-logo">
                        <span class="dashicons dashicons-admin-appearance"></span>
                    </div>
                    <div class="sifp-ai-title">
                        <h4><?php esc_html_e('AI Assistant', 'si-flash-products'); ?></h4>
                        <span class="sifp-ai-badge"><?php esc_html_e('Gemini Flash', 'si-flash-products'); ?></span>
                    </div>
                </div>
                
                <div class="sifp-card__body sifp-card__body--ai">
                    <div class="sifp-form-field">
                        <label><?php esc_html_e('Product Name', 'si-flash-products'); ?></label>
                        <input type="text" id="sifp_ai_product_name" placeholder="<?php esc_attr_e('e.g. Wireless Headphones', 'si-flash-products'); ?>">
                    </div>

                    <div class="sifp-form-field">
                        <label><?php esc_html_e('What do you need?', 'si-flash-products'); ?></label>
                        <textarea id="sifp_ai_product_context" rows="4" placeholder="<?php esc_attr_e('E.g.: Generate a description for a luxury steel watch...', 'si-flash-products'); ?>"></textarea>
                    </div>
                    
                    <button type="button" class="sifp-button sifp-ai-generate-btn" <?php disabled( empty($api_key) ); ?>>
                        <span class="dashicons dashicons-sparkles"></span>
                        <?php esc_html_e('MAKE MAGIC', 'si-flash-products'); ?>
                    </button>

                    <?php if ( empty($api_key) ) : ?>
                        <p class="sifp-ai-error"><?php printf( wp_kses( __('Enter the <a href="%s">API Key</a>', 'si-flash-products'), array('a' => array('href' => array())) ), esc_url( admin_url('admin.php?page=flash_products_settings') ) ); ?></p>
                    <?php endif; ?>
                </div>

                <div class="sifp-card__footer sifp-card__footer--ai">
                    <button type="button" class="sifp-button sifp-ai-import-btn full-width">
                        <span class="dashicons dashicons-cloud-upload"></span>
                        <?php esc_html_e('CREATE PRODUCT', 'si-flash-products'); ?>
                    </button>
                    <button type="button" class="sifp-button sifp-ai-clear-btn ghost">
                        <?php esc_html_e('Clear all', 'si-flash-products'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// End of file

