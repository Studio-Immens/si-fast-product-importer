<?php
namespace SIFlashProducts\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Main Plugin Class
 */
final class Plugin {

    /**
     * The single instance of the class
     *
     * @var Plugin
     */
    protected static $_instance = null;

    /**
     * Main Plugin Instance
     *
     * @return Plugin - Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define Constants
     */
    private function define_constants() {
        $this->define( 'SIFProd_VERSION', '1.1.0' );
        $this->define( 'SIFProd_PATH', plugin_dir_path( SIFProd_FILE ) );
        $this->define( 'SIFProd_URL', plugin_dir_url( SIFProd_FILE ) );
        $this->define( 'SIFProd_BASENAME', plugin_basename( SIFProd_FILE ) );
    }

    /**
     * Define constant if not already set
     *
     * @param string $name
     * @param string|bool $value
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Include required files
     */
    public function includes() {
        require_once SIFProd_PATH . 'includes/Helpers/Functions.php';
        
        // Load legacy functions.php if it exists (per user request)
        if ( file_exists( SIFProd_PATH . 'functions.php' ) ) {
            require_once SIFProd_PATH . 'functions.php';
        }
    }

    /**
     * Initialize Hooks
     */
    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), -1 );
        register_activation_hook( SIFProd_FILE, array( '\SIFlashProducts\Core\Database', 'install' ) );
    }

    /**
     * On Plugins Loaded
     */
    public function on_plugins_loaded() {
        load_plugin_textdomain( 'si-flash-products', false, dirname( SIFProd_BASENAME ) . '/languages' );
        
        if ( $this->is_woocommerce_active() ) {
            $this->init_components();
        } else {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
        }
    }

    /**
     * Initialize Components
     */
    private function init_components() {
        \SIFlashProducts\Admin\AdminManager::instance();
        \SIFlashProducts\Admin\AJAXHandler::instance();
        \SIFlashProducts\Admin\ProductAISidebar::instance();
        \SIFlashProducts\Core\Database::instance();
        \SIFlashProducts\Core\AIProviderManager::instance();
    }

    /**
     * Check if WooCommerce is active
     */
    public function is_woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }

    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'ERROR! Flash Products needs the WooCommerce plugin installed and active to work properly.', 'si-flash-products' ); ?></p>
        </div>
        <?php
    }
}
