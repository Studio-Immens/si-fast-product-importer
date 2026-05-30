<?php
namespace SIFlashProducts\Admin;

defined( 'ABSPATH' ) || exit;

use SIFlashProducts\Core\AIProviderManager;

class ProductAISidebar {

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
    }

    public function register_meta_box() {
        $provider = AIProviderManager::instance()->get_active_provider();
        if ( ! $provider || ! $provider->is_available() ) {
            return;
        }

        add_meta_box(
            'sifp_ai_product_sidebar',
            __( 'Fast AI Generator', 'si-fast-product-importer' ),
            array( $this, 'render_meta_box' ),
            'product',
            'side',
            'default'
        );
    }

    public function render_meta_box() {
        $provider = AIProviderManager::instance()->get_active_provider();
        ?>
        <div class="sifp-ai-sidebar">
            <p style="margin-top:0;font-size:13px;color:#64748b;">
                <?php esc_html_e( 'Generate product content using', 'si-fast-product-importer' ); ?>
                <strong><?php echo esc_html( $provider ? $provider->get_name() : '' ); ?></strong>
            </p>

            <p>
                <label for="sifp_ai_prod_name" style="display:block;font-weight:600;margin-bottom:4px;font-size:12px;color:#1e293b;">
                    <?php esc_html_e( 'Product Name', 'si-fast-product-importer' ); ?>
                </label>
                <input type="text" id="sifp_ai_prod_name"
                       style="width:100%;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;"
                       placeholder="<?php esc_attr_e( 'e.g. Wireless Headphones', 'si-fast-product-importer' ); ?>">
            </p>

            <p>
                <label for="sifp_ai_prod_context" style="display:block;font-weight:600;margin-bottom:4px;font-size:12px;color:#1e293b;">
                    <?php esc_html_e( 'Extra Context', 'si-fast-product-importer' ); ?>
                </label>
                <textarea id="sifp_ai_prod_context" rows="3"
                          style="width:100%;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;resize:vertical;"
                          placeholder="<?php esc_attr_e( 'E.g.: luxury, waterproof, black', 'si-fast-product-importer' ); ?>"></textarea>
            </p>

            <p>
                <button type="button" id="sifp-ai-prod-generate" class="button button-primary" style="width:100%;height:36px;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <span class="dashicons dashicons-sparkles" style="font-size:16px;width:16px;height:16px;margin:0;"></span>
                    <?php esc_html_e( 'Generate with AI', 'si-fast-product-importer' ); ?>
                </button>
            </p>

            <div id="sifp-ai-prod-result" style="font-size:12px;line-height:1.4;min-height:18px;"></div>
        </div>
        <?php
    }
}