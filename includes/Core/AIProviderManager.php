<?php
namespace SIFlashProducts\Core;

defined( 'ABSPATH' ) || exit;

class AIProviderManager {

    protected static $_instance = null;

    private $providers = array();

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->register_default_providers();
    }

    private function register_default_providers() {
        $providers = array(
            'gemini'     => 'SIFlashProducts\\Integrations\\GeminiProvider',
            'openai'     => 'SIFlashProducts\\Integrations\\OpenAIProvider',
            'claude'     => 'SIFlashProducts\\Integrations\\ClaudeProvider',
            'openrouter' => 'SIFlashProducts\\Integrations\\OpenRouterProvider',
        );

        foreach ( $providers as $id => $class ) {
            if ( class_exists( $class ) ) {
                $this->providers[ $id ] = new $class();
            }
        }
    }

    public function register_provider( string $id, AIInterface $instance ) {
        $this->providers[ $id ] = $instance;
    }

    public function get_provider( string $id ): ?AIInterface {
        return $this->providers[ $id ] ?? null;
    }

    public function get_providers(): array {
        return $this->providers;
    }

    public function get_active_provider(): ?AIInterface {
        $active_id = get_option( 'sifp_active_ai_provider', 'gemini' );

        if ( isset( $this->providers[ $active_id ] ) ) {
            return $this->providers[ $active_id ];
        }

        // Fallback to first available
        foreach ( $this->providers as $provider ) {
            return $provider;
        }

        return null;
    }

    public function get_active_provider_id(): string {
        return get_option( 'sifp_active_ai_provider', 'gemini' );
    }
}