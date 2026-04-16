<?php
namespace SIFlashProducts\Core;

defined( 'ABSPATH' ) || exit;

/**
 * AI Generator Class (Gemini Integration)
 */
class AIGenerator {

    /**
     * API Endpoint
     */
    private $api_url = "https://generativelanguage.googleapis.com/v1beta/models/";

    /**
     * Generate product details
     */
    public function generate( $data ) {
        $api_key = get_option( 'sifp_gemini_api_key' );
        if ( ! $api_key ) {
            return new \WP_Error( 'missing_api_key', __( 'Gemini API Key is missing in settings.', 'si-flash-products' ) );
        }

        $model = get_option( 'sifp_ai_model', 'gemini-flash-latest' );
        $url = "{$this->api_url}{$model}:generateContent?key={$api_key}";

        $prompt = $this->build_prompt( $data );

        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array( 'text' => $prompt )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.7,
                'response_mime_type' => 'application/json'
            )
        );

        $response = wp_remote_post( $url, array(
            'body'    => json_encode( $body ),
            'headers' => array( 'Content-Type' => 'application/json' ),
            'timeout' => 45,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $res_body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $res_body['error'] ) ) {
            return new \WP_Error( 'ai_error', $res_body['error']['message'] ?? __( 'AI Error', 'si-flash-products' ) );
        }

        $content = $res_body['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Clean markdown backticks if present
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/```\s*$/i', '', $content);
        $content = trim($content);
        
        $decoded = json_decode( $content, true );
        
        if ( ! is_array( $decoded ) ) {
            // Give detailed log if decoding fails to help debugging
            if ( function_exists( 'sifp_log' ) ) {
                sifp_log( 'Failed to decode JSON from AI. Raw content: ' . print_r($content, true), 'ai_generator', 'error' );
            }
            return new \WP_Error( 'ai_invalid_json', __( 'AI returned an invalid JSON format.', 'si-flash-products' ) );
        }

        return $decoded;
    }

    /**
     * Build Prompt
     */
    private function build_prompt( $data ) {
        $name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : 'Prodotto';
        $desc = isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '';
        $lang = get_option( 'sifp_default_lang', 'it' );

        $prompt = "Genera i dettagli completi per un prodotto WooCommerce in lingua: {$lang}.\n";
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
