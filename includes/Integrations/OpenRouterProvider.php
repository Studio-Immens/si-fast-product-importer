<?php
namespace SIFlashProducts\Integrations;

use SIFlashProducts\Core\AIInterface;
use SIFlashProducts\Core\ModelRegistry;
use SIFlashProducts\Helpers\Encryption;

defined( 'ABSPATH' ) || exit;

class OpenRouterProvider implements AIInterface {

    public function get_id(): string {
        return 'openrouter';
    }

    public function get_name(): string {
        return 'OpenRouter (Multi-Provider)';
    }

    public function get_api_key_option_name(): string {
        return 'sifp_openrouter_key';
    }

    public function get_model_option_name(): string {
        return 'sifp_openrouter_model';
    }

    public function is_available(): bool {
        $key = get_option( $this->get_api_key_option_name() );
        return ! empty( $key );
    }

    public function get_available_models(): array {
        return ModelRegistry::instance()->get_openrouter_models();
    }

    public function call_ai( string $prompt, bool $json_mode = false, int $max_tokens = 8192 ): array {
        $encrypted = get_option( $this->get_api_key_option_name() );
        if ( empty( $encrypted ) ) {
            return array(
                'success' => false,
                'error'   => __( 'OpenRouter API Key is missing in settings.', 'si-flash-products' ),
            );
        }

        $api_key = Encryption::decrypt( $encrypted );
        $model   = $this->get_effective_model();

        $body = array(
            'model'       => $model,
            'messages'    => array(
                array(
                    'role'    => 'user',
                    'content' => $prompt,
                ),
            ),
            'max_tokens'  => $max_tokens,
            'temperature' => floatval( get_option( 'sifp_ai_creativity', '0.7' ) ),
        );

        if ( $json_mode ) {
            $body['response_format'] = array( 'type' => 'json_object' );
        }

        $response = wp_remote_post( 'https://openrouter.ai/api/v1/chat/completions', array(
            'body'    => wp_json_encode( $body ),
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
                'HTTP-Referer'  => home_url(),
            ),
            'timeout' => 45,
        ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'error'   => $response->get_error_message(),
            );
        }

        $res_body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $res_body['error'] ) ) {
            return array(
                'success' => false,
                'error'   => $res_body['error']['message'] ?? __( 'OpenRouter API error', 'si-flash-products' ),
            );
        }

        $text = $res_body['choices'][0]['message']['content'] ?? '';

        return array(
            'success' => true,
            'text'    => $text,
            'time'    => $res_body['usage'] ?? array(),
        );
    }

    private function get_effective_model(): string {
        $model = get_option( $this->get_model_option_name(), 'openai/gpt-4o-mini' );

        if ( 'custom' === $model ) {
            $custom = get_option( $this->get_model_option_name() . '_custom', '' );
            if ( ! empty( $custom ) ) {
                return $custom;
            }
            return 'openai/gpt-4o-mini';
        }

        return $model;
    }
}