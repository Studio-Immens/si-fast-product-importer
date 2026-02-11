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

<style>
/* Generator UI Style - Immens Style */
.GeneratorSection {
    padding: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

.GeneratorHeader {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 40px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 20px;
}

.TitleGroup h2 {
    font-size: 2.5rem;
    margin: 0;
    color: #fff;
    font-weight: 800;
}

.TitleGroup p {
    color: #888;
    margin: 5px 0 0 0;
    font-size: 1.1rem;
}

.GeneratorPresets {
    display: flex;
    gap: 10px;
}

.PresetBtn {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    transition: var(--fp-transition);
    font-size: 0.9rem;
}

.PresetBtn:hover {
    background: var(--fp-primary);
    border-color: var(--fp-primary);
}

.GeneratorLayout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 40px;
}

.GeneratorCard {
    background: var(--fp-bg-card);
    border-radius: 16px;
    margin-bottom: 30px;
    border: 1px solid rgba(255,255,255,0.05);
    overflow: hidden;
}

.CardHead {
    padding: 20px 25px;
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    display: flex;
    align-items: center;
    gap: 12px;
}

.CardHead h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #fff;
}

.CardHead .dashicons {
    color: var(--fp-primary);
}

.CardBody {
    padding: 25px;
}

.CardBody.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.CardBody.grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }

.FormField {
    margin-bottom: 20px;
}

.FormField label {
    display: block;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #888;
    margin-bottom: 8px;
}

.FormField input, .FormField select, .FormField textarea {
    width: 100%;
    background: rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 12px 15px;
    color: #fff;
    transition: var(--fp-transition);
}

.FormField input:focus {
    border-color: var(--fp-primary);
    box-shadow: 0 0 0 3px rgba(15, 156, 192, 0.2);
    outline: none;
}

/* AI Sidebar */
.AICard {
    background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
    border-radius: 20px;
    padding: 30px;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}

.AICard.sticky {
    position: sticky;
    top: 50px;
}

.AICardHead {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
}

.AI_Logo {
    width: 50px;
    height: 50px;
    background: var(--fp-primary);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.AI_Logo .dashicons {
    font-size: 30px;
    width: 30px;
    height: 30px;
    color: #fff;
}

.AI_Title h4 {
    margin: 0;
    font-size: 1.2rem;
    color: #fff;
}

.AI_Badge {
    font-size: 0.7rem;
    background: rgba(255,255,255,0.1);
    padding: 2px 8px;
    border-radius: 10px;
    color: #aaa;
}

.AI_Generate_Btn {
    width: 100%;
    margin-top: 10px;
    background: var(--fp-primary) !important;
    padding: 15px !important;
    border-radius: 12px !important;
    font-weight: bold !important;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-transform: uppercase;
}

.AI_Import_Btn {
    background: var(--fp-accent) !important;
    padding: 18px !important;
    border-radius: 12px !important;
    font-weight: 800 !important;
    font-size: 1rem !important;
    margin-top: 30px;
}

.AI_Clear_Btn.ghost {
    background: transparent !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    margin-top: 10px;
    width: 100%;
}

.AI_Error {
    color: var(--fp-error-color);
    font-size: 0.8rem;
    margin-top: 10px;
    text-align: center;
}

.ImageInputGroup {
    display: flex;
    gap: 15px;
    align-items: center;
}

.ImagePreview {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.1);
    background-size: cover;
    background-position: center;
    flex-shrink: 0;
}

@media (max-width: 1100px) {
    .GeneratorLayout { grid-template-columns: 1fr; }
    .AICard.sticky { position: static; }
}
</style>
