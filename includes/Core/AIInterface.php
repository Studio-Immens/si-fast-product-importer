<?php
namespace SIFlashProducts\Core;

defined( 'ABSPATH' ) || exit;

interface AIInterface {

    public function get_id(): string;

    public function get_name(): string;

    public function get_available_models(): array;

    public function call_ai( string $prompt, bool $json_mode = false, int $max_tokens = 8192 ): array;

    public function is_available(): bool;

    public function get_api_key_option_name(): string;

    public function get_model_option_name(): string;
}