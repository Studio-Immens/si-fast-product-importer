<?php
namespace SIFlashProducts\Integrations;

use SIFlashProducts\Core\AIInterface;
use SIFlashProducts\Core\ModelRegistry;

defined( 'ABSPATH' ) || exit;

class GeminiProvider implements AIInterface {

    private $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function get_id(): string {
        return 'gemini';
    }

    public function get_name(): string {
        return 'Google Gemini';
    }

    public function get_api_key_option_name(): string {
        return 'sifp_gemini_api_key';
    }

    public function get_model_option_name(): string {
        return 'sifp_ai_model';
    }

    public function is_available(): bool {
        $key = get_option( $this->get_api_key_option_name() );
        return ! empty( $key );
    }

    public function get_available_models(): array {
        return ModelRegistry::instance()->get_gemini_models();
    }

    public function call_ai( string $prompt, bool $json_mode = false, int $max_tokens = 8192 ): array {
        $api_key = get_option( $this->get_api_key_option_name() );
        if ( ! $api_key ) {
            return array(
                'success' => false,
                'error'   => __( 'Gemini API Key is missing in settings.', 'si-flash-products' ),
            );
        }

        $model = $this->get_effective_model();
        $url   = $this->api_url . $model . ':generateContent?key=' . $api_key;

        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array( 'text' => $prompt ),
                    ),
                ),
            ),
            'generationConfig' => array(
                'temperature'      => floatval( get_option( 'sifp_ai_creativity', '0.7' ) ),
                'maxOutputTokens'  => $max_tokens,
            ),
        );

        if ( $json_mode ) {
            $body['generationConfig']['response_mime_type'] = 'application/json';
        }

        $response = wp_remote_post( $url, array(
            'body'    => wp_json_encode( $body ),
            'headers' => array( 'Content-Type' => 'application/json' ),
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
                'error'   => $res_body['error']['message'] ?? __( 'Gemini API error', 'si-flash-products' ),
            );
        }

        $text = $res_body['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if ( $json_mode ) {
            $text = preg_replace( '/^```json\s*/i', '', $text );
            $text = preg_replace( '/```\s*$/i', '', $text );
            $text = trim( $text );
        }

        return array(
            'success' => true,
            'text'    => $text,
            'time'    => $res_body['usageMetadata'] ?? array(),
        );
    }

    private function get_effective_model(): string {
        $model = get_option( $this->get_model_option_name(), 'gemini-2.0-flash' );
        // Validate: preview models with dates may not exist — fall back to stable
        $known_working = array(
            'gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.5-pro-exp-03-25',
            'gemini-1.5-flash', 'gemini-1.5-pro',
        );
        if ( ! in_array( $model, $known_working, true ) && strpos( $model, 'custom' ) === false ) {
            $custom = get_option( $this->get_model_option_name() . '_custom', '' );
            if ( ! empty( $custom ) ) {
                return $custom;
            }
        }
        if ( 'custom' === $model ) {
            $custom = get_option( $this->get_model_option_name() . '_custom', '' );
            if ( ! empty( $custom ) ) {
                return $custom;
            }
        }
        return $model;
    }
}