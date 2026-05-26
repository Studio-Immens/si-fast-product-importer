<?php
namespace SIFlashProducts\Core;

defined( 'ABSPATH' ) || exit;

class ModelRegistry {

    protected static $_instance = null;

    const CACHE_VERSION = 2;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function cache_key( string $provider_id ): string {
        return 'sifp_models_v' . self::CACHE_VERSION . '_' . $provider_id;
    }

    public function get_models( string $provider_id ): array {
        $method = 'get_' . $provider_id . '_models';
        if ( method_exists( $this, $method ) ) {
            return $this->$method();
        }
        return array();
    }

    public function get_model( string $provider_id, string $model_id ) {
        $models = $this->get_models( $provider_id );
        return $models[ $model_id ] ?? null;
    }

    public function get_gemini_models(): array {
        return array(
            'gemini-2.0-flash' => array(
                'name'         => 'Gemini 2.0 Flash',
                'pricing_tier' => 'free',
                'context'      => 1000000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Stable, fast, free, great quality — recommended',
                'capabilities' => array(
                    'coding'    => 75,
                    'reasoning' => 78,
                    'writing'   => 78,
                    'speed'     => 88,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'gemini-2.0-flash-lite' => array(
                'name'         => 'Gemini 2.0 Flash Lite',
                'pricing_tier' => 'free',
                'context'      => 1000000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Lightweight, fastest Gemini model',
                'capabilities' => array(
                    'coding'    => 65,
                    'reasoning' => 66,
                    'writing'   => 66,
                    'speed'     => 95,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'gemini-2.5-pro-exp-03-25' => array(
                'name'         => 'Gemini 2.5 Pro (experimental)',
                'pricing_tier' => 'premium',
                'context'      => 1000000,
                'max_output'   => 65536,
                'pricing'      => array( 'prompt' => 0.00000125, 'completion' => 0.00001 ),
                'description'  => 'Google best model — experimental, subject to rate limits',
                'capabilities' => array(
                    'coding'    => 88,
                    'reasoning' => 90,
                    'writing'   => 88,
                    'speed'     => 60,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'gemini-1.5-flash' => array(
                'name'         => 'Gemini 1.5 Flash',
                'pricing_tier' => 'free',
                'context'      => 1000000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Reliable, fast, legacy model',
                'capabilities' => array(
                    'coding'    => 68,
                    'reasoning' => 70,
                    'writing'   => 72,
                    'speed'     => 85,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'gemini-1.5-pro' => array(
                'name'         => 'Gemini 1.5 Pro',
                'pricing_tier' => 'premium',
                'context'      => 2000000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0.0000035, 'completion' => 0.0000105 ),
                'description'  => 'Legacy Pro model with 2M context',
                'capabilities' => array(
                    'coding'    => 78,
                    'reasoning' => 82,
                    'writing'   => 80,
                    'speed'     => 55,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'gemini-flash-latest' => array(
                'name'         => 'Gemini Flash Latest (alias)',
                'pricing_tier' => 'free',
                'context'      => 1000000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Legacy alias — resolves to latest flash',
                'capabilities' => array(
                    'coding'    => 72,
                    'reasoning' => 72,
                    'writing'   => 73,
                    'speed'     => 85,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
        );
    }

    public function get_openai_models(): array {
        return array(
            'gpt-4o' => array(
                'name'         => 'GPT-4o',
                'pricing_tier' => 'premium',
                'context'      => 128000,
                'max_output'   => 16384,
                'pricing'      => array( 'prompt' => 0.0000025, 'completion' => 0.00001 ),
                'description'  => 'OpenAI flagship model',
                'capabilities' => array(
                    'coding'    => 85,
                    'reasoning' => 88,
                    'writing'   => 90,
                    'speed'     => 65,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'gpt-4o-mini' => array(
                'name'         => 'GPT-4o Mini',
                'pricing_tier' => 'free',
                'context'      => 128000,
                'max_output'   => 16384,
                'pricing'      => array( 'prompt' => 0.00000015, 'completion' => 0.0000006 ),
                'description'  => 'Fast, affordable, great quality',
                'capabilities' => array(
                    'coding'    => 78,
                    'reasoning' => 80,
                    'writing'   => 82,
                    'speed'     => 82,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'gpt-4.5-preview' => array(
                'name'         => 'GPT-4.5 Preview',
                'pricing_tier' => 'premium',
                'context'      => 128000,
                'max_output'   => 16384,
                'pricing'      => array( 'prompt' => 0.000075, 'completion' => 0.00015 ),
                'description'  => 'OpenAI most capable model',
                'capabilities' => array(
                    'coding'    => 90,
                    'reasoning' => 92,
                    'writing'   => 95,
                    'speed'     => 45,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'o3-mini' => array(
                'name'         => 'o3 Mini',
                'pricing_tier' => 'premium',
                'context'      => 200000,
                'max_output'   => 100000,
                'pricing'      => array( 'prompt' => 0.0000011, 'completion' => 0.0000044 ),
                'description'  => 'OpenAI reasoning model',
                'capabilities' => array(
                    'coding'    => 88,
                    'reasoning' => 92,
                    'writing'   => 75,
                    'speed'     => 55,
                    'vision'    => false,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'gpt-4.1' => array(
                'name'         => 'GPT-4.1',
                'pricing_tier' => 'premium',
                'context'      => 1000000,
                'max_output'   => 32768,
                'pricing'      => array( 'prompt' => 0.000002, 'completion' => 0.000008 ),
                'description'  => 'GPT-4 class with 1M context',
                'capabilities' => array(
                    'coding'    => 86,
                    'reasoning' => 88,
                    'writing'   => 86,
                    'speed'     => 60,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'gpt-4.1-mini' => array(
                'name'         => 'GPT-4.1 Mini',
                'pricing_tier' => 'free',
                'context'      => 1000000,
                'max_output'   => 32768,
                'pricing'      => array( 'prompt' => 0.0000004, 'completion' => 0.0000016 ),
                'description'  => 'Lightweight 1M context model',
                'capabilities' => array(
                    'coding'    => 78,
                    'reasoning' => 80,
                    'writing'   => 80,
                    'speed'     => 78,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
        );
    }

    public function get_claude_models(): array {
        return array(
            'claude-sonnet-4-20250514' => array(
                'name'         => 'Claude Sonnet 4',
                'pricing_tier' => 'premium',
                'context'      => 200000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0.000003, 'completion' => 0.000015 ),
                'description'  => 'Anthropic latest Sonnet',
                'capabilities' => array(
                    'coding'    => 88,
                    'reasoning' => 88,
                    'writing'   => 92,
                    'speed'     => 65,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'claude-opus-4-20250514' => array(
                'name'         => 'Claude Opus 4',
                'pricing_tier' => 'premium',
                'context'      => 200000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0.000015, 'completion' => 0.000075 ),
                'description'  => 'Anthropic most powerful model',
                'capabilities' => array(
                    'coding'    => 92,
                    'reasoning' => 93,
                    'writing'   => 95,
                    'speed'     => 40,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'claude-haiku-4-20250514' => array(
                'name'         => 'Claude Haiku 4',
                'pricing_tier' => 'free',
                'context'      => 200000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0.00000025, 'completion' => 0.00000125 ),
                'description'  => 'Anthropic fastest, cheapest',
                'capabilities' => array(
                    'coding'    => 72,
                    'reasoning' => 74,
                    'writing'   => 74,
                    'speed'     => 90,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'claude-3-5-sonnet-latest' => array(
                'name'         => 'Claude 3.5 Sonnet',
                'pricing_tier' => 'premium',
                'context'      => 200000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0.000003, 'completion' => 0.000015 ),
                'description'  => 'Anthropic legacy Sonnet, still great',
                'capabilities' => array(
                    'coding'    => 84,
                    'reasoning' => 85,
                    'writing'   => 88,
                    'speed'     => 62,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
            'claude-3-haiku-20240307' => array(
                'name'         => 'Claude 3 Haiku',
                'pricing_tier' => 'free',
                'context'      => 200000,
                'max_output'   => 4096,
                'pricing'      => array( 'prompt' => 0.00000025, 'completion' => 0.00000125 ),
                'description'  => 'Anthropic legacy fast model',
                'capabilities' => array(
                    'coding'    => 65,
                    'reasoning' => 66,
                    'writing'   => 68,
                    'speed'     => 88,
                    'vision'    => true,
                    'json_mode' => true,
                    'streaming' => true,
                ),
            ),
        );
    }

    public function get_openrouter_models(): array {
        $cache_key = $this->cache_key( 'openrouter' );
        $cached = get_transient( $cache_key );
        if ( false !== $cached && is_array( $cached ) ) {
            return $cached;
        }

        // Start with defaults (ensures :free aliases are always available)
        $models = $this->get_default_openrouter_models();

        // Fetch from OpenRouter API and merge onto defaults
        $response = wp_remote_get( 'https://openrouter.ai/api/v1/models', array( 'timeout' => 10 ) );
        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( isset( $body['data'] ) && is_array( $body['data'] ) ) {
                foreach ( $body['data'] as $m ) {
                    $id = sanitize_text_field( $m['id'] );
                    $pricing  = $m['pricing'] ?? array( 'prompt' => 0, 'completion' => 0 );
                    $prompt   = floatval( $pricing['prompt'] ?? 0 );
                    $completion = floatval( $pricing['completion'] ?? 0 );
                    $is_free  = ( 0 === $prompt && 0 === $completion );

                    $capabilities = $this->infer_capabilities( $id );

                    $models[ $id ] = array(
                        'name'         => sanitize_text_field( $m['name'] ?? $id ),
                        'pricing_tier' => $is_free ? 'free' : 'premium',
                        'context'      => intval( $m['context_length'] ?? 0 ),
                        'max_output'   => intval( $m['top_provider']['max_completion_tokens'] ?? 4096 ),
                        'pricing'      => array( 'prompt' => $prompt, 'completion' => $completion ),
                        'description'  => sanitize_text_field( $m['description'] ?? '' ),
                        'capabilities' => $capabilities,
                    );
                }
            }
        }

        set_transient( $cache_key, $models, HOUR_IN_SECONDS );
        return $models;
    }

    private function get_default_openrouter_models(): array {
        return array(
            // --- Free models ---
            'openrouter/free' => array(
                'name'         => 'OpenRouter Auto FREE',
                'pricing_tier' => 'free',
                'context'      => 128000,
                'max_output'   => 4096,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Auto-routes to best free model available',
                'capabilities' => array(
                    'coding' => 70, 'reasoning' => 70, 'writing' => 70, 'speed' => 80,
                    'vision' => true, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'google/gemini-2.0-flash-exp:free' => array(
                'name'         => 'Gemini 2.0 Flash Exp FREE',
                'pricing_tier' => 'free',
                'context'      => 1000000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Free Gemini with 1M context via OpenRouter',
                'capabilities' => array(
                    'coding' => 72, 'reasoning' => 68, 'writing' => 72, 'speed' => 88,
                    'vision' => true, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'deepseek/deepseek-chat-v3-0324:free' => array(
                'name'         => 'DeepSeek V3 Chat FREE',
                'pricing_tier' => 'free',
                'context'      => 128000,
                'max_output'   => 4096,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Free DeepSeek V3 chat model',
                'capabilities' => array(
                    'coding' => 82, 'reasoning' => 75, 'writing' => 75, 'speed' => 80,
                    'vision' => false, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'meta-llama/llama-3.3-70b-instruct:free' => array(
                'name'         => 'Llama 3.3 70B FREE',
                'pricing_tier' => 'free',
                'context'      => 128000,
                'max_output'   => 4096,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Free Llama 3.3 70B via OpenRouter',
                'capabilities' => array(
                    'coding' => 78, 'reasoning' => 78, 'writing' => 78, 'speed' => 65,
                    'vision' => false, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'mistralai/mistral-small-3.1-24b-instruct:free' => array(
                'name'         => 'Mistral Small 3.1 FREE',
                'pricing_tier' => 'free',
                'context'      => 128000,
                'max_output'   => 4096,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Free Mistral Small 3.1',
                'capabilities' => array(
                    'coding' => 68, 'reasoning' => 65, 'writing' => 70, 'speed' => 88,
                    'vision' => false, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'qwen/qwen3-235b-a22b:free' => array(
                'name'         => 'Qwen 3 235B FREE',
                'pricing_tier' => 'free',
                'context'      => 128000,
                'max_output'   => 4096,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Free Qwen coding model',
                'capabilities' => array(
                    'coding' => 82, 'reasoning' => 78, 'writing' => 72, 'speed' => 70,
                    'vision' => true, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'nvidia/nemotron-3-super-4b-instruct:free' => array(
                'name'         => 'NVIDIA Nemotron 3 Super FREE',
                'pricing_tier' => 'free',
                'context'      => 128000,
                'max_output'   => 4096,
                'pricing'      => array( 'prompt' => 0, 'completion' => 0 ),
                'description'  => 'Fast and free small model',
                'capabilities' => array(
                    'coding' => 55, 'reasoning' => 50, 'writing' => 52, 'speed' => 90,
                    'vision' => false, 'json_mode' => true, 'streaming' => true,
                ),
            ),

            // --- Premium models ---
            'openai/gpt-4o-mini' => array(
                'name'         => 'GPT-4o Mini (OpenAI)',
                'pricing_tier' => 'free',
                'context'      => 128000,
                'max_output'   => 16384,
                'pricing'      => array( 'prompt' => 0.00000015, 'completion' => 0.0000006 ),
                'description'  => 'Fast, affordable, great quality via OpenRouter',
                'capabilities' => array(
                    'coding' => 78, 'reasoning' => 80, 'writing' => 82, 'speed' => 82,
                    'vision' => true, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'openai/gpt-4o' => array(
                'name'         => 'GPT-4o (OpenAI)',
                'pricing_tier' => 'premium',
                'context'      => 128000,
                'max_output'   => 16384,
                'pricing'      => array( 'prompt' => 0.0000025, 'completion' => 0.00001 ),
                'description'  => 'OpenAI flagship model via OpenRouter',
                'capabilities' => array(
                    'coding' => 85, 'reasoning' => 88, 'writing' => 90, 'speed' => 75,
                    'vision' => true, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'anthropic/claude-3-5-sonnet' => array(
                'name'         => 'Claude 3.5 Sonnet (Anthropic)',
                'pricing_tier' => 'premium',
                'context'      => 200000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0.000003, 'completion' => 0.000015 ),
                'description'  => 'Claude via OpenRouter',
                'capabilities' => array(
                    'coding' => 84, 'reasoning' => 85, 'writing' => 88, 'speed' => 62,
                    'vision' => true, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'google/gemini-2.0-flash-001' => array(
                'name'         => 'Gemini 2.0 Flash (Google)',
                'pricing_tier' => 'premium',
                'context'      => 1000000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0.0000001, 'completion' => 0.0000004 ),
                'description'  => 'Gemini with 1M context via OpenRouter',
                'capabilities' => array(
                    'coding' => 78, 'reasoning' => 78, 'writing' => 78, 'speed' => 88,
                    'vision' => true, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'deepseek/deepseek-chat' => array(
                'name'         => 'DeepSeek V3 (DeepSeek)',
                'pricing_tier' => 'free',
                'context'      => 128000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0.00000027, 'completion' => 0.0000011 ),
                'description'  => 'Top open-source reasoning model',
                'capabilities' => array(
                    'coding' => 90, 'reasoning' => 80, 'writing' => 80, 'speed' => 70,
                    'vision' => false, 'json_mode' => true, 'streaming' => true,
                ),
            ),
            'deepseek/deepseek-r1' => array(
                'name'         => 'DeepSeek R1 (DeepSeek)',
                'pricing_tier' => 'premium',
                'context'      => 128000,
                'max_output'   => 8192,
                'pricing'      => array( 'prompt' => 0.00000055, 'completion' => 0.00000219 ),
                'description'  => 'Specialized reasoning model',
                'capabilities' => array(
                    'coding' => 92, 'reasoning' => 95, 'writing' => 78, 'speed' => 45,
                    'vision' => false, 'json_mode' => true, 'streaming' => true,
                ),
            ),
        );
    }

    private function infer_capabilities( string $model_id ): array {
        $coding    = 70;
        $reasoning = 72;
        $writing   = 74;
        $speed     = 65;
        $vision    = strpos( $model_id, 'vision' ) !== false || strpos( $model_id, 'gemini' ) !== false || strpos( $model_id, 'gpt-4o' ) !== false || strpos( $model_id, 'claude-3' ) !== false || strpos( $model_id, 'claude-4' ) !== false || strpos( $model_id, 'sonnet' ) !== false;

        if ( preg_match( '/claude.*(?:opus|sonnet)/i', $model_id ) ) {
            $coding = 88; $reasoning = 90; $writing = 92; $speed = 50;
        } elseif ( preg_match( '/claude.*haiku/i', $model_id ) ) {
            $coding = 68; $reasoning = 70; $writing = 72; $speed = 88;
        } elseif ( preg_match( '/gpt-4(?:\.|$)/i', $model_id ) ) {
            $coding = 85; $reasoning = 88; $writing = 88; $speed = 60;
        } elseif ( preg_match( '/gpt-4o|chatgpt-4o/i', $model_id ) ) {
            $coding = 85; $reasoning = 88; $writing = 90; $speed = 65;
        } elseif ( preg_match( '/o3|o4|o1/i', $model_id ) ) {
            $coding = 88; $reasoning = 92; $writing = 75; $speed = 45;
        } elseif ( preg_match( '/gemini/i', $model_id ) ) {
            $coding = 78; $reasoning = 80; $writing = 80; $speed = 78;
            $vision = true;
        } elseif ( preg_match( '/deepseek/i', $model_id ) ) {
            $coding = 80; $reasoning = 82; $writing = 76; $speed = 75;
        } elseif ( preg_match( '/llama|meta/i', $model_id ) ) {
            $coding = 70; $reasoning = 72; $writing = 74; $speed = 72;
        } elseif ( preg_match( '/mistral|mixtral/i', $model_id ) ) {
            $coding = 74; $reasoning = 76; $writing = 76; $speed = 76;
        } elseif ( preg_match( '/qwen/i', $model_id ) ) {
            $coding = 72; $reasoning = 74; $writing = 72; $speed = 78;
        }

        return array(
            'coding'    => $coding,
            'reasoning' => $reasoning,
            'writing'   => $writing,
            'speed'     => $speed,
            'vision'    => $vision,
            'json_mode' => true,
            'streaming' => true,
        );
    }

    public function refresh_models( string $provider_id ): bool {
        $cache_key = $this->cache_key( $provider_id );
        delete_transient( $cache_key );
        $this->get_openrouter_models();
        return true;
    }
}