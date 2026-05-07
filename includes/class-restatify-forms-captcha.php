<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Verifies CAPTCHA tokens for reCAPTCHA v3 and Cloudflare Turnstile.
 */
final class Restatify_Forms_Captcha {

    /**
     * Verifies a token against the configured provider.
     *
     * @param array<string,mixed> $security Form security config.
     */
    public function verify( array $security, string $token ): bool {
        $provider = $security['captcha_provider'] ?? 'none';

        if ( $provider === 'none' ) {
            return true;
        }

        if ( $token === '' ) {
            return false;
        }

        if ( $provider === 'recaptcha' ) {
            return $this->verify_recaptcha( (string) ( $security['recaptcha_secret_key'] ?? '' ), $token );
        }

        if ( $provider === 'turnstile' ) {
            return $this->verify_turnstile( (string) ( $security['turnstile_secret_key'] ?? '' ), $token );
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function verify_recaptcha( string $secret, string $token ): bool {
        if ( $secret === '' ) {
            return false;
        }

        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'body'    => [
                    'secret'   => $secret,
                    'response' => $token,
                    'remoteip' => $this->get_client_ip(),
                ],
                'timeout' => 10,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return is_array( $body )
            && ! empty( $body['success'] )
            && (float) ( $body['score'] ?? 0 ) >= 0.5;
    }

    private function verify_turnstile( string $secret, string $token ): bool {
        if ( $secret === '' ) {
            return false;
        }

        $response = wp_remote_post(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            [
                'body'    => [
                    'secret'   => $secret,
                    'response' => $token,
                    'remoteip' => $this->get_client_ip(),
                ],
                'timeout' => 10,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return is_array( $body ) && ! empty( $body['success'] );
    }

    private function get_client_ip(): string {
        // Only use the direct connection IP — do NOT trust proxy headers here.
        return sanitize_text_field( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
    }
}
