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

<div class="FPMainContainer AI_Generator">
    <div class="AI_Header">
        <h2><?php esc_html_e('AI Product Generator', 'si-flash-products'); ?></h2>
        <?php if ( empty($api_key) ) : ?>
            <div class="notice notice-warning inline">
                <p><?php printf( 
                    __('Per utilizzare il generatore AI, devi inserire una API Key valida nelle <a href="%s">impostazioni</a>.', 'si-flash-products'),
                    admin_url('admin.php?page=flash_products_settings')
                ); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="AI_Grid">
        <div class="AI_Column AI_Input_Section">
            <div class="AI_Field">
                <label><?php esc_html_e('Nome Prodotto (Input principale)', 'si-flash-products'); ?></label>
                <input type="text" id="ai_product_name" placeholder="<?php esc_attr_e('es: Scarpe da corsa ultra leggere', 'si-flash-products'); ?>">
            </div>

            <div class="AI_Field">
                <label><?php esc_html_e('Altre info / Parole chiave', 'si-flash-products'); ?></label>
                <textarea id="ai_product_context" rows="4" placeholder="<?php esc_attr_e('Inserisci dettagli come materiale, colore, brand, o target di riferimento...', 'si-flash-products'); ?>"></textarea>
            </div>

            <button class="FPbutton AI_Generate_Btn" <?php disabled( empty($api_key) ); ?>>
                <span class="dashicons dashicons-admin-appearance"></span>
                <?php esc_html_e('GENERA CON AI', 'si-flash-products'); ?>
            </button>
        </div>

        <div class="AI_Column AI_Output_Section">
            <form id="ai_product_form">
                <div class="AI_Field">
                    <label><?php esc_html_e('Titolo Generato', 'si-flash-products'); ?></label>
                    <input type="text" name="post_title" id="out_post_title">
                </div>

                <div class="AI_Field">
                    <label><?php esc_html_e('Descrizione Breve', 'si-flash-products'); ?></label>
                    <textarea name="post_excerpt" id="out_post_excerpt" rows="3"></textarea>
                </div>

                <div class="AI_Field">
                    <label><?php esc_html_e('Descrizione Completa', 'si-flash-products'); ?></label>
                    <?php 
                    wp_editor( '', 'out_post_content', array(
                        'textarea_name' => 'post_content',
                        'textarea_rows' => 10,
                        'media_buttons' => false,
                        'tinymce'       => true,
                        'quicktags'     => true
                    ) ); 
                    ?>
                </div>

                <div class="AI_Field_Row">
                    <div class="AI_Field">
                        <label><?php esc_html_e('Categorie (separate da virgola)', 'si-flash-products'); ?></label>
                        <input type="text" name="fp_categories" id="out_fp_categories">
                    </div>
                    <div class="AI_Field">
                        <label><?php esc_html_e('Tag (separati da virgola)', 'si-flash-products'); ?></label>
                        <input type="text" name="fp_tag" id="out_fp_tag">
                    </div>
                </div>

                <div class="AI_Field">
                    <label><?php esc_html_e('URL Immagine (opzionale)', 'si-flash-products'); ?></label>
                    <input type="text" name="fp_img" id="out_fp_img" placeholder="https://example.com/image.jpg">
                </div>

                <div class="AI_Actions">
                    <button type="button" class="FPbutton AI_Import_Btn" style="background-color: var(--fp-main-color);">
                        <?php esc_html_e('IMPORTA PRODOTTO', 'si-flash-products'); ?>
                    </button>
                    <button type="button" class="FPbutton AI_Clear_Btn" style="background-color: var(--fp-bg3-color);">
                        <?php esc_html_e('PULISCI', 'si-flash-products'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.AI_Generator { padding: 20px; }
.AI_Grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; margin-top: 20px; }
.AI_Column { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.AI_Field { margin-bottom: 20px; }
.AI_Field label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
.AI_Field input[type="text"], 
.AI_Field input[type="number"], 
.AI_Field textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
.AI_Generate_Btn { width: 100%; padding: 15px !important; font-size: 16px !important; background-color: #6200ee !important; color: #fff !important; }
.AI_Generate_Btn .dashicons { margin-right: 10px; }
.AI_Field_Row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.AI_Actions { display: flex; gap: 10px; margin-top: 25px; }
.AI_Actions .FPbutton { flex: 1; padding: 12px !important; }
</style>
