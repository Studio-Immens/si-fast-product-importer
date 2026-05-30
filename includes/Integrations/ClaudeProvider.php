<?php
namespace SIFlashProducts\Integrations;

use SIFlashProducts\Core\AIInterface;
use SIFlashProducts\Core\ModelRegistry;
use SIFlashProducts\Helpers\Encryption;

defined( 'ABSPATH' ) || exit;

class ClaudeProvider implements AIInterface {

    public function get_id(): string {
        return 'claude';
    }

    public function get_name(): string {
        return 'Claude (Anthropic)';
    }

    public function get_api_key_option_name(): string {
        return 'sifp_claude_key';
    }

    public function get_model_option_name(): string {
        return 'sifp_claude_model';
    }

    public function is_available(): bool {
        $key = get_option( $this->get_api_key_option_name() );
        return ! empty( $key );
    }

    public function get_available_models(): array {
        return ModelRegistry::instance()->get_claude_models();
    }

    public function call_ai( string $prompt, bool $json_mode = false, int $max_tokens = 8192 ): array {
        $encrypted = get_option( $this->get_api_key_option_name() );
        if ( empty( $encrypted ) ) {
            return array(
                'success' => false,
                'error'   => __( 'Claude API Key is missing in settings.', 'si-fast-product-importer' ),
            );
        }

        $api_key = Encryption::decrypt( $encrypted );
        $model   = $this->get_effective_model();

        $body = array(
            'model'      => $model,
            'max_tokens' => $max_tokens,
            'messages'   => array(
                array(
                    'role'    => 'user',
                    'content' => $prompt,
                ),
            ),
        );

        if ( $json_mode ) {
            $body['metadata'] = array( 'json_mode' => true );
        }

        $response = wp_remote_post( 'https://api.anthropic.com/v1/messages', array(
            'body'    => wp_json_encode( $body ),
            'headers' => array(
                'Content-Type'      => 'application/json',
                'x-api-key'         => $api_key,
                'anthropic-version' => '2023-06-01',
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
                'error'   => $res_body['error']['message'] ?? __( 'Claude API error', 'si-fast-product-importer' ),
            );
        }

        $text = '';
        if ( isset( $res_body['content'] ) && is_array( $res_body['content'] ) ) {
            foreach ( $res_body['content'] as $block ) {
                if ( 'text' === ( $block['type'] ?? '' ) ) {
                    $text .= $block['text'] ?? '';
                }
            }
        }

        return array(
            'success' => true,
            'text'    => $text,
            'time'    => $res_body['usage'] ?? array(),
        );
    }

    private function get_effective_model(): string {
        $model = get_option( $this->get_model_option_name(), 'claude-sonnet-4-20250514' );

        if ( 'custom' === $model ) {
            $custom = get_option( $this->get_model_option_name() . '_custom', '' );
            if ( ! empty( $custom ) ) {
                return $custom;
            }
            return 'claude-sonnet-4-20250514';
        }

        return $model;
    }
}