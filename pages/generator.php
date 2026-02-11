<?php
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

$categories = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
) );

$api_key = FP_get_meta('FP_gemini_api_key');
?>

<div class="FPMainContainer GeneratorSection">
    <div class="GeneratorHeader">
        <div class="TitleGroup">
            <h2><?php esc_html_e('Product Generator', 'si-flash-products'); ?></h2>
            <p><?php esc_html_e('Crea i tuoi prodotti in modo moderno, creativo e veloce.', 'si-flash-products'); ?></p>
        </div>
        
        <div class="GeneratorPresets">
            <button type="button" class="PresetBtn" data-preset="simple"><?php esc_html_e('Simple Draft', 'si-flash-products'); ?></button>
            <button type="button" class="PresetBtn" data-preset="physical"><?php esc_html_e('Physical Item', 'si-flash-products'); ?></button>
            <button type="button" class="PresetBtn" data-preset="premium"><?php esc_html_e('Premium Product', 'si-flash-products'); ?></button>
        </div>
    </div>

    <div class="GeneratorLayout">
        <!-- Main Form Column -->
        <div class="GeneratorMain">
            <form id="ai_product_form" class="ModernForm">
                <!-- Basic Info Card -->
                <div class="GeneratorCard">
                    <div class="CardHead">
                        <span class="dashicons dashicons-admin-page"></span>
                        <h3><?php esc_html_e('Informazioni Base', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="CardBody">
                        <div class="FormField full">
                            <label><?php esc_html_e('Titolo Prodotto', 'si-flash-products'); ?></label>
                            <input type="text" name="post_title" id="out_post_title" placeholder="<?php esc_attr_e('Nome del prodotto...', 'si-flash-products'); ?>">
                        </div>
                        
                        <div class="FormField full">
                            <label><?php esc_html_e('Descrizione Breve', 'si-flash-products'); ?></label>
                            <textarea name="post_excerpt" id="out_post_excerpt" rows="2" placeholder="<?php esc_attr_e('Un riassunto accattivante...', 'si-flash-products'); ?>"></textarea>
                        </div>

                        <div class="FormField full">
                            <label><?php esc_html_e('Descrizione Completa', 'si-flash-products'); ?></label>
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
                        <h3><?php esc_html_e('Prezzi e Inventario', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="CardBody grid-2">
                        <div class="FormField">
                            <label><?php esc_html_e('Prezzo Listino (€)', 'si-flash-products'); ?></label>
                            <input type="number" step="0.01" name="regular_price" id="out_regular_price" placeholder="0.00">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Prezzo Scontato (€)', 'si-flash-products'); ?></label>
                            <input type="number" step="0.01" name="sale_price" id="out_sale_price" placeholder="0.00">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('SKU', 'si-flash-products'); ?></label>
                            <input type="text" name="sku" id="out_sku" placeholder="PROD-001">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Stato Magazzino', 'si-flash-products'); ?></label>
                            <select name="stock_status" id="out_stock_status">
                                <option value="instock"><?php esc_html_e('Disponibile', 'si-flash-products'); ?></option>
                                <option value="outofstock"><?php esc_html_e('Esaurito', 'si-flash-products'); ?></option>
                                <option value="onbackorder"><?php esc_html_e('In arrivo', 'si-flash-products'); ?></option>
                            </select>
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Quantità', 'si-flash-products'); ?></label>
                            <input type="number" name="stock_qty" id="out_stock_qty" value="10">
                        </div>
                    </div>
                </div>

                <!-- Logistics Card -->
                <div class="GeneratorCard">
                    <div class="CardHead">
                        <span class="dashicons dashicons-performance"></span>
                        <h3><?php esc_html_e('Dimensioni e Peso', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="CardBody grid-4">
                        <div class="FormField">
                            <label><?php esc_html_e('Peso (kg)', 'si-flash-products'); ?></label>
                            <input type="text" name="weight" id="out_weight" placeholder="0.00">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Lung. (cm)', 'si-flash-products'); ?></label>
                            <input type="text" name="length" id="out_length" placeholder="0">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Larg. (cm)', 'si-flash-products'); ?></label>
                            <input type="text" name="width" id="out_width" placeholder="0">
                        </div>
                        <div class="FormField">
                            <label><?php esc_html_e('Alt. (cm)', 'si-flash-products'); ?></label>
                            <input type="text" name="height" id="out_height" placeholder="0">
                        </div>
                    </div>
                </div>

                <!-- Taxonomy & Media Card -->
                <div class="GeneratorCard">
                    <div class="CardHead">
                        <span class="dashicons dashicons-category"></span>
                        <h3><?php esc_html_e('Categorie e Media', 'si-flash-products'); ?></h3>
                    </div>
                    <div class="CardBody">
                        <div class="FormField full">
                            <label><?php esc_html_e('Categorie (separate da virgola)', 'si-flash-products'); ?></label>
                            <input type="text" name="fp_categories" id="out_fp_categories">
                        </div>
                        <div class="FormField full">
                            <label><?php esc_html_e('Tag (separati da virgola)', 'si-flash-products'); ?></label>
                            <input type="text" name="fp_tag" id="out_fp_tag">
                        </div>
                        <div class="FormField full">
                            <label><?php esc_html_e('URL Immagine Principale', 'si-flash-products'); ?></label>
                            <div class="ImageInputGroup">
                                <input type="text" name="fp_img" id="out_fp_img" placeholder="https://example.com/image.jpg">
                                <div id="img_preview_container" class="ImagePreview"></div>
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
                        <label><?php esc_html_e('Di cosa hai bisogno?', 'si-flash-products'); ?></label>
                        <textarea id="ai_product_context" rows="4" placeholder="<?php esc_attr_e('Es: Genera una descrizione per un orologio di lusso in acciaio...', 'si-flash-products'); ?>"></textarea>
                    </div>
                    
                    <button type="button" class="FPbutton AI_Generate_Btn" <?php disabled( empty($api_key) ); ?>>
                        <span class="dashicons dashicons-sparkles"></span>
                        <?php esc_html_e('FAI MAGIA', 'si-flash-products'); ?>
                    </button>

                    <?php if ( empty($api_key) ) : ?>
                        <p class="AI_Error"><?php printf( __('Inserisci la <a href="%s">API Key</a>', 'si-flash-products'), admin_url('admin.php?page=flash_products_settings') ); ?></p>
                    <?php endif; ?>
                </div>

                <div class="AICardFoot">
                    <button type="button" class="FPbutton AI_Import_Btn full-width">
                        <span class="dashicons dashicons-cloud-upload"></span>
                        <?php esc_html_e('CREA PRODOTTO', 'si-flash-products'); ?>
                    </button>
                    <button type="button" class="FPbutton AI_Clear_Btn ghost">
                        <?php esc_html_e('Svuota tutto', 'si-flash-products'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// End of file

