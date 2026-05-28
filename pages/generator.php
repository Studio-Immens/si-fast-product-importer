<?php
defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    return;
}

$sifp_api_key = get_option('sifp_gemini_api_key');
?>

<div class="sifp-main-container sifp-generator-section">

    <div class="sifp-header-logo">
        <img src="<?php echo SIFProd_URL . 'assets/flash-products-logo-128.png'; ?>" class="sifp-admin-logo" alt="SI Flash Products Logo">
        <h1 class="sifp-plugin-title">Flash Products</h1>
    </div>
    <div class="sifp-generator-header">
        <div class="sifp-generator-header__title-group">
            <h2><?php esc_html_e('Product Generator', 'si-flash-products'); ?></h2>
            <p><?php esc_html_e('Create products with AI magic and modern interface.', 'si-flash-products'); ?></p>
        </div>
    </div>

    <div class="sifp-generator-layout">
        <!-- Main Form Column -->
        <div class="sifp-generator-layout__main">
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

                <!-- SEO Optimization Card -->
                <div class="sifp-card">
                    <div class="sifp-card__header">
                        <span class="dashicons dashicons-google"></span>
                        <h3><?php esc_html_e('SEO Optimization', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="sifp-card__body">
                        <div class="sifp-form-field full">
                            <label><?php esc_html_e('SEO Title', 'si-flash-products'); ?></label>
                            <input type="text" name="seo_title" id="out_seo_title" placeholder="<?php esc_attr_e('Meta title for search engines...', 'si-flash-products'); ?>">
                        </div>
                        <div class="sifp-form-field full">
                            <label><?php esc_html_e('SEO Description', 'si-flash-products'); ?></label>
                            <textarea name="seo_description" id="out_seo_description" rows="2" placeholder="<?php esc_attr_e('Meta description for search engines...', 'si-flash-products'); ?>"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Inventory -->
                <div class="sifp-card">
                    <div class="sifp-card__header">
                        <span class="dashicons dashicons-cart"></span>
                        <h3><?php esc_html_e('Pricing and Inventory', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="sifp-card__body grid-2">
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('Regular Price (€)', 'si-flash-products'); ?></label>
                            <input type="number" step="0.01" name="regular_price" id="out_regular_price" placeholder="0.00">
                        </div>
                        <div class="sifp-form-field">
                            <label><?php esc_html_e('SKU', 'si-flash-products'); ?></label>
                            <input type="text" name="sku" id="out_sku" placeholder="PROD-001">
                        </div>
                    </div>
                </div>

                <!-- Attributes Card -->
                <div class="sifp-card">
                    <div class="sifp-card__header">
                        <span class="dashicons dashicons-tag"></span>
                        <h3><?php esc_html_e('Product Attributes', 'si-flash-products'); ?></h3>
                        <button type="button" id="sifp_add_attribute_btn" class="sifp-button sifp-button--preset sifp-u-ml-auto sifp-u-font-10">
                            <span class="dashicons dashicons-plus"></span> <?php esc_html_e('Add Attribute', 'si-flash-products'); ?>
                        </button>
                    </div>
                    <div class="sifp-card__body" id="sifp_attributes_container">
                        <!-- Attributes will be added here -->
                    </div>
                </div>

                <!-- Variations Card -->
                <div class="sifp-card sifp-u-hidden" id="sifp_variations_card">
                    <div class="sifp-card__header">
                        <span class="dashicons dashicons-networking"></span>
                        <h3><?php esc_html_e('Product Variations', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="sifp-card__body">
                        <div id="sifp_variations_container" class="sifp-variations-list">
                            <!-- Variations will be added here -->
                        </div>
                    </div>
                </div>

                <!-- Hidden template for attribute row -->
                <div class="sifp-attribute-row-template sifp-u-hidden">
                    <div class="sifp-attribute-row grid-3 sifp-u-grid-generator">
                        <div class="sifp-form-field sifp-u-mb-0">
                            <label><?php esc_html_e('Name', 'si-flash-products'); ?></label>
                            <input type="text" class="sifp-attribute-row__name" placeholder="e.g. Color">
                        </div>
                        <div class="sifp-form-field sifp-u-mb-0">
                            <label><?php esc_html_e('Values (pipe separated)', 'si-flash-products'); ?></label>
                            <input type="text" class="sifp-attribute-row__values" placeholder="Red | Blue | Green">
                        </div>
                        <button type="button" class="sifp-attribute-row__remove sifp-u-button-error-small">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>

                <!-- Media -->
                <div class="sifp-card">
                    <div class="sifp-card__header">
                        <span class="dashicons dashicons-admin-media"></span>
                        <h3><?php esc_html_e('Media', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="sifp-card__body">
                        <div class="sifp-form-field full">
                            <label><?php esc_html_e('Main Image URL', 'si-flash-products'); ?></label>
                            <div class="sifp-image-input-group">
                                <input type="text" name="sifp_img" id="out_sifp_img">
                                <button type="button" id="sifp_select_image_btn" class="sifp-button sifp-button--preset"><?php esc_html_e('Browse', 'si-flash-products'); ?></button>
                                <div id="sifp_img_preview_container" class="sifp-image-preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- AI Assistant Sidebar -->
        <div class="sifp-generator-layout__sidebar">
            <div class="sifp-card sifp-card--ai sticky">
                <div class="sifp-card__header sifp-card__header--ai">
                    <div class="sifp-ai-logo"><span class="dashicons dashicons-admin-appearance"></span></div>
                    <div class="sifp-ai-title">
                        <h4><?php esc_html_e('AI Assistant', 'si-flash-products'); ?></h4>
                        <span class="sifp-ai-badge">Gemini</span>
                    </div>
                </div>
                
                <div class="sifp-card__body sifp-card__body--ai">
                    <div class="sifp-form-field">
                        <label><?php esc_html_e('Product Name', 'si-flash-products'); ?></label>
                        <input type="text" id="sifp_ai_product_name" placeholder="<?php esc_attr_e('e.g. Wireless Headphones', 'si-flash-products'); ?>">
                    </div>

                    <div class="sifp-form-field">
                        <label><?php esc_html_e('Extra Context', 'si-flash-products'); ?></label>
                        <textarea id="sifp_ai_product_context" rows="4" placeholder="<?php esc_attr_e('E.g.: luxury steel watch, waterproof...', 'si-flash-products'); ?>"></textarea>
                    </div>
                    
                    <button type="button" class="sifp-button sifp-ai-generate-btn" <?php disabled( empty($api_key) ); ?>>
                        <span class="dashicons dashicons-sparkles"></span>
                        <?php esc_html_e('GENERATE WITH AI', 'si-flash-products'); ?>
                    </button>
                </div>

                <div class="sifp-card__footer sifp-card__footer--ai">
                    <button type="button" class="sifp-button sifp-ai-import-btn full-width">
                        <span class="dashicons dashicons-cloud-upload"></span>
                        <?php esc_html_e('CREATE PRODUCT', 'si-flash-products'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
