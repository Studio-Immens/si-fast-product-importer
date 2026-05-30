<?php
namespace SIFlashProducts\Core;

defined( 'ABSPATH' ) || exit;

class AIGenerator {

    public function generate( $data ) {
        $provider = AIProviderManager::instance()->get_active_provider();

        if ( ! $provider ) {
            return new \WP_Error( 'no_provider', __( 'No active AI provider found. Please configure one in Settings.', 'si-fast-product-importer' ) );
        }

        if ( ! $provider->is_available() ) {
            return new \WP_Error( 'provider_not_configured', sprintf(
                /* translators: %s: AI provider name */
                __( '%s is not configured. Please add your API key in Settings.', 'si-fast-product-importer' ),
                $provider->get_name()
            ) );
        }

        $prompt = $this->build_prompt( $data );

        $result = $provider->call_ai( $prompt, true, 8192 );

        if ( ! $result['success'] ) {
            if ( function_exists( 'sifp_log' ) ) {
                sifp_log( 'AI generation failed: ' . ( $result['error'] ?? 'Unknown error' ), 'ai_generator', 'error' );
            }
            return new \WP_Error( 'ai_error', $result['error'] ?? __( 'AI call failed.', 'si-fast-product-importer' ) );
        }

        $content = $result['text'];

        // Clean markdown backticks if present
        $content = preg_replace( '/^```json\s*/i', '', $content );
        $content = preg_replace( '/```\s*$/i', '', $content );
        $content = trim( $content );

        $decoded = json_decode( $content, true );

        if ( ! is_array( $decoded ) ) {
            if ( function_exists( 'sifp_log' ) ) {
                sifp_log( 'Failed to decode JSON from AI. Raw: ' . substr( wp_json_encode( $content ), 0, 500 ), 'ai_generator', 'error' );
            }
            return new \WP_Error( 'ai_invalid_json', __( 'AI returned an invalid JSON format.', 'si-fast-product-importer' ) );
        }

        return $decoded;
    }

    private function build_prompt( $data ) {
        $name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : 'Prodotto';
        $desc = isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '';
        $lang = get_option( 'sifp_default_lang', 'it' );

        $prompt  = "Genera i dettagli completi per un prodotto WooCommerce in lingua: {$lang}.\n";
        $prompt .= "Nome del prodotto: {$name}\n";
        $prompt .= "Descrizione fornita: {$desc}\n\n";
        $prompt .= "Il risultato deve essere un JSON valido con i seguenti campi:\n";
        $prompt .= "- post_title: Titolo accattivante\n";
        $prompt .= "- post_content: Descrizione lunga formattata in HTML (usa <ul>, <p>, <strong>)\n";
        $prompt .= "- post_excerpt: Breve descrizione di 1-2 frasi\n";
        $prompt .= "- sifp_categories: Lista di categorie separate da virgola (es: Elettronica, Casa)\n";
        $prompt .= "- sifp_tag: Lista di tag separati da virgola\n";
        $prompt .= "- sku: Genera uno SKU univoco\n";
        $prompt .= "- regular_price: Prezzo stimato (numero)\n";
        $prompt .= "- seo_title: Meta title ottimizzato SEO (max 60 car.)\n";
        $prompt .= "- seo_description: Meta description ottimizzata SEO (max 160 car.)\n";
        $prompt .= "- attributes: Array di oggetti con {name, value} (es: {name: 'Colore', value: 'Rosso|Blu'})\n";
        $prompt .= "- variations: (Opzionale) Se ha attributi, genera un array di varianti con {attributes, regular_price, sku}\n\n";
        $prompt .= "Ritorna SOLO il JSON.";

        return $prompt;
    }
}