<?php
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

$categories = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
) );

$tags = get_terms( array(
    'taxonomy'   => 'product_tag',
    'hide_empty' => false,
) );

$api_key = FP_get_meta('FP_gemini_api_key');
?>

<div class="FPMainContainer GeneratorSection">
    <div class="GeneratorHeader">
        <div class="TitleGroup">
            <h2><?php esc_html_e('Product Generator', 'si-flash-products'); ?></h2>
            <p><?php esc_html_e('Create your products in a modern, creative and fast way.', 'si-flash-products'); ?></p>
        </div>
        
        <div class="GeneratorPresets">
            <button type="button" class="PresetBtn" data-preset="simple"><?php esc_html_e('Simple Draft', 'si-flash-products'); ?></button>
            <button type="button" class="PresetBtn" data-preset="physical"><?php esc_html_e('Physical Item', 'si-flash-products'); ?></button>
            <button type="button" class="PresetBtn" data-preset="premium"><?php esc_html_e('Premium Product', 'si-flash-products'); ?></button>
            <button type="button" class="PresetBtn" data-preset="virtual"><?php esc_html_e('Virtual Service', 'si-flash-products'); ?></button>
            <button type="button" class="PresetBtn" data-preset="downloadable"><?php esc_html_e('Digital Download', 'si-flash-products'); ?></button>
        </div>
    </div>

    <div class="GeneratorLayout">
        <!-- Main Form Column -->
        <div class="GeneratorMain">
            <div id="fp_generator_notices"></div>
            <form id="ai_product_form" class="ModernForm">
                <!-- Basic Info Card -->
                <div class="GeneratorCard">
                    <div class="CardHead">
                        <span class="dashicons dashicons-admin-page"></span>
                        <h3><?php esc_html_e('Basic Information', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="CardBody">
                        <div class="FormField full">
                            <label><?php esc_html_e('Product Title', 'si-flash-products'); ?></label>
                            <input type="text" name="post_title" id="out_post_title" placeholder="<?php esc_attr_e('Product name...', 'si-flash-products'); ?>">
                        </div>
                        
                        <div class="FormField full">
                            <label><?php esc_html_e('Short Description', 'si-flash-products'); ?></label>
                            <textarea name="post_excerpt" id="out_post_excerpt" rows="2" placeholder="<?php esc_attr_e('An eye-catching summary...', 'si-flash-products'); ?>"></textarea>
                        </div>

                        <div class="FormField full">
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
                <div class="GeneratorCard">
                    <div class="CardHead">
                        <span class="dashicons dashicons-cart"></span>
                        <h3><?php esc_html_e('Pricing and Inventory', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="CardBody grid-2">
                        <div class="FormField">
                            <label><?php esc_html_e('Product Type', 'si-flash-products'); ?></label>
                            <div class="CheckboxGroup">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_virtual" id="out_is_virtual" value="yes"> <?php esc_html_e('Virtual', 'si-flash-products'); ?>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_downloadable" id="out_is_downloadable" value="yes"> <?php esc_html_e('Downloadable', 'si-flash-products'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Regular Price (€)', 'si-flash-products'); ?></label>
                            <input type="number" step="0.01" name="regular_price" id="out_regular_price" placeholder="0.00">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Sale Price (€)', 'si-flash-products'); ?></label>
                            <input type="number" step="0.01" name="sale_price" id="out_sale_price" placeholder="0.00">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('SKU', 'si-flash-products'); ?></label>
                            <input type="text" name="sku" id="out_sku" placeholder="PROD-001">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Stock Status', 'si-flash-products'); ?></label>
                            <select name="stock_status" id="out_stock_status">
                                <option value="instock"><?php esc_html_e('In Stock', 'si-flash-products'); ?></option>
                                <option value="outofstock"><?php esc_html_e('Out of Stock', 'si-flash-products'); ?></option>
                                <option value="onbackorder"><?php esc_html_e('On Backorder', 'si-flash-products'); ?></option>
                            </select>
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Quantity', 'si-flash-products'); ?></label>
                            <input type="number" name="stock_qty" id="out_stock_qty" value="10">
                        </div>
                    </div>
                </div>

                <!-- Logistics Card -->
                <div class="GeneratorCard">
                    <div class="CardHead">
                        <span class="dashicons dashicons-performance"></span>
                        <h3><?php esc_html_e('Dimensions and Weight', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="CardBody grid-4">
                        <div class="FormField">
                            <label><?php esc_html_e('Weight (kg)', 'si-flash-products'); ?></label>
                            <input type="text" name="weight" id="out_weight" placeholder="0.00">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Length (cm)', 'si-flash-products'); ?></label>
                            <input type="text" name="length" id="out_length" placeholder="0">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Width (cm)', 'si-flash-products'); ?></label>
                            <input type="text" name="width" id="out_width" placeholder="0">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Height (cm)', 'si-flash-products'); ?></label>
                            <input type="text" name="height" id="out_height" placeholder="0">
                        </div>
                    </div>
                </div>

                <!-- Attributes Card -->
                <div class="GeneratorCard">
                    <div class="CardHead">
                        <span class="dashicons dashicons-tag"></span>
                        <h3><?php esc_html_e('Product Attributes', 'si-flash-products'); ?></h3>
                        <button type="button" id="add_attribute_btn" class="PresetBtn" style="margin-left: auto; font-size: 10px;">
                            <span class="dashicons dashicons-plus"></span> <?php esc_html_e('Add Attribute', 'si-flash-products'); ?>
                        </button>
                    </div>
                    <div class="CardBody" id="attributes_container">
                        <!-- Attributes will be added here -->
                        <div class="attribute-row-template" style="display:none;">
                            <div class="AttributeRow grid-3" style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 15px; margin-bottom: 15px; align-items: flex-end;">
                                <div class="FormField" style="margin-bottom: 0;">
                                    <label><?php esc_html_e('Name (e.g. Color)', 'si-flash-products'); ?></label>
                                    <input type="text" class="attr-name" placeholder="<?php esc_attr_e('Color', 'si-flash-products'); ?>">
                                </div>
                                <div class="FormField" style="margin-bottom: 0;">
                                    <label><?php esc_html_e('Values (pipe separated)', 'si-flash-products'); ?></label>
                                    <input type="text" class="attr-values" placeholder="<?php esc_attr_e('Red | Blue | Green', 'si-flash-products'); ?>">
                                </div>
                                <button type="button" class="remove-attribute-btn" style="background: var(--fp-error); color: #fff; border: none; border-radius: 4px; padding: 10px; cursor: pointer;">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Taxonomy & Media Card -->
                <div class="GeneratorCard">
                    <div class="CardHead">
                        <span class="dashicons dashicons-category"></span>
                        <h3><?php esc_html_e('Categories and Media', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="CardBody">
                        <div class="FormField full">
                            <label><?php esc_html_e('Categories', 'si-flash-products'); ?></label>
                            <div class="TaxonomySelector" data-tax="product_cat">
                                <input type="text" name="fp_categories" id="out_fp_categories" autocomplete="off" placeholder="<?php esc_attr_e('Start typing to search categories...', 'si-flash-products'); ?>">
                                <div class="AutocompleteResults"></div>
                            </div>
                        </div>
                        <div class="FormField full">
                            <label><?php esc_html_e('Tags', 'si-flash-products'); ?></label>
                            <div class="TaxonomySelector" data-tax="product_tag">
                                <input type="text" name="fp_tag" id="out_fp_tag" autocomplete="off" placeholder="<?php esc_attr_e('Start typing to search tags...', 'si-flash-products'); ?>">
                                <div class="AutocompleteResults"></div>
                            </div>
                        </div>
                        <div class="FormField full">
                            <label><?php esc_html_e('Main Image URL', 'si-flash-products'); ?></label>
                            <div class="ImageInputGroup">
                                <input type="text" name="fp_img" id="out_fp_img" placeholder="https://example.com/image.jpg">
                                <button type="button" id="select_image_btn" class="PresetBtn">
                                    <span class="dashicons dashicons-admin-media"></span>
                                    <?php esc_html_e('Browse', 'si-flash-products'); ?>
                                </button>
                                <div id="img_preview_container" class="ImagePreview"></div>
                            </div>
                        </div>
                        <div class="FormField full">
                            <label><?php esc_html_e('Product Gallery', 'si-flash-products'); ?></label>
                            <div class="GalleryInputGroup">
                                <button type="button" id="select_gallery_btn" class="PresetBtn">
                                    <span class="dashicons dashicons-images-alt2"></span>
                                    <?php esc_html_e('Add Gallery Images', 'si-flash-products'); ?>
                                </button>
                                <input type="hidden" name="fp_gallery" id="out_fp_gallery">
                                <div id="gallery_preview_container" class="GalleryPreviewGrid"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- AI Assistant Column (Sidebar) -->
        <div class="GeneratorSidebar">
            <div class="AICard sticky">
                <div class="AICardHead">
                    <div class="AI_Logo">
                        <span class="dashicons dashicons-admin-appearance"></span>
                    </div>
                    <div class="AI_Title">
                        <h4><?php esc_html_e('AI Assistant', 'si-flash-products'); ?></h4>
                        <span class="AI_Badge"><?php esc_html_e('Gemini Flash', 'si-flash-products'); ?></span>
                    </div>
                </div>
                
                <div class="AICardBody">
                    <div class="FormField">
                        <label><?php esc_html_e('Product Name', 'si-flash-products'); ?></label>
                        <input type="text" id="ai_product_name" placeholder="<?php esc_attr_e('e.g. Wireless Headphones', 'si-flash-products'); ?>">
                    </div>

                    <div class="FormField">
                        <label><?php esc_html_e('What do you need?', 'si-flash-products'); ?></label>
                        <textarea id="ai_product_context" rows="4" placeholder="<?php esc_attr_e('E.g.: Generate a description for a luxury steel watch...', 'si-flash-products'); ?>"></textarea>
                    </div>
                    
                    <button type="button" class="FPbutton AI_Generate_Btn" <?php disabled( empty($api_key) ); ?>>
                        <span class="dashicons dashicons-sparkles"></span>
                        <?php esc_html_e('MAKE MAGIC', 'si-flash-products'); ?>
                    </button>

                    <?php if ( empty($api_key) ) : ?>
                        <p class="AI_Error"><?php printf( __('Enter the <a href="%s">API Key</a>', 'si-flash-products'), admin_url('admin.php?page=flash_products_settings') ); ?></p>
                    <?php endif; ?>
                </div>

                <div class="AICardFoot">
                    <button type="button" class="FPbutton AI_Import_Btn full-width">
                        <span class="dashicons dashicons-cloud-upload"></span>
                        <?php esc_html_e('CREATE PRODUCT', 'si-flash-products'); ?>
                    </button>
                    <button type="button" class="FPbutton AI_Clear_Btn ghost">
                        <?php esc_html_e('Clear all', 'si-flash-products'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// End of file

