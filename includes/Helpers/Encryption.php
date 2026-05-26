<?php
namespace SIFlashProducts\Helpers;

defined( 'ABSPATH' ) || exit;

class Encryption {

    public static function encrypt( $data ) {
        if ( empty( $data ) ) {
            return '';
        }

        $key = self::get_key();
        $iv  = openssl_random_pseudo_bytes( 16 );
        $encrypted = openssl_encrypt( $data, 'aes-256-cbc', $key, 0, $iv );

        if ( false === $encrypted ) {
            return '';
        }

        return '$openssl$' . base64_encode( $iv . $encrypted );
    }

    public static function decrypt( $data ) {
        if ( empty( $data ) || ! is_string( $data ) ) {
            return '';
        }

        // Legacy fallback (AUTH_KEY)
        if ( strpos( $data, '$openssl$' ) !== 0 ) {
            $decrypted = @openssl_decrypt(
                base64_decode( $data ),
                'aes-256-cbc',
                defined( 'AUTH_KEY' ) ? AUTH_KEY : wp_salt( 'auth' ),
                0,
                substr( md5( defined( 'AUTH_KEY' ) ? AUTH_KEY : wp_salt( 'auth' ), true ), 0, 16 )
            );
            return false !== $decrypted ? $decrypted : $data;
        }

        $key = self::get_key();
        $raw = base64_decode( substr( $data, 9 ) );
        if ( strlen( $raw ) < 16 ) {
            return '';
        }

        $iv         = substr( $raw, 0, 16 );
        $ciphertext = substr( $raw, 16 );
        $decrypted  = openssl_decrypt( $ciphertext, 'aes-256-cbc', $key, 0, $iv );

        return false !== $decrypted ? $decrypted : '';
    }

    private static function get_key() {
        if ( defined( 'SIFP_ENCRYPTION_KEY' ) && SIFP_ENCRYPTION_KEY ) {
            return hash( 'sha256', SIFP_ENCRYPTION_KEY, true );
        }
        return hash( 'sha256', wp_salt( 'auth' ), true );
    }
}