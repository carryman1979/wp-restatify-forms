<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Manages plugin options: loading, saving, sanitizing and default values.
 */
final class Restatify_Forms_Options {

    /**
     * Returns all stored forms, normalized.
     *
     * @return array<int,array<string,mixed>>
     */
    public function get_all_forms(): array {
        $saved = get_option( Restatify_Forms_Constants::OPTION_KEY, [] );
        if ( ! is_array( $saved ) ) {
            return [];
        }

        return array_values(
            array_filter(
                array_map( [ $this, 'normalize_form' ], $saved )
            )
        );
    }

    /**
     * Returns a single form by ID, or null.
     *
     * @return array<string,mixed>|null
     */
    public function get_form( string $id ): ?array {
        foreach ( $this->get_all_forms() as $form ) {
            if ( ( $form['id'] ?? '' ) === $id ) {
                return $form;
            }
        }

        return null;
    }

    /**
     * Creates or updates a form config.
     */
    public function save_form( array $form ): bool {
        $form = $this->sanitize_form( $form );
        if ( empty( $form['id'] ) ) {
            return false;
        }

        $all   = $this->get_all_forms();
        $index = null;

        foreach ( $all as $i => $f ) {
            if ( ( $f['id'] ?? '' ) === $form['id'] ) {
                $index = $i;
                break;
            }
        }

        if ( $index !== null ) {
            $all[ $index ] = $form;
        } else {
            $all[] = $form;
        }

        return (bool) update_option( Restatify_Forms_Constants::OPTION_KEY, array_values( $all ) );
    }

    /**
     * Deletes a form by ID.
     */
    public function delete_form( string $id ): bool {
        $all = $this->get_all_forms();
        $all = array_values( array_filter( $all, fn( $f ) => ( $f['id'] ?? '' ) !== $id ) );

        return (bool) update_option( Restatify_Forms_Constants::OPTION_KEY, $all );
    }

    /**
     * Returns default form structure.
     *
     * @return array<string,mixed>
     */
    public function get_form_defaults(): array {
        return [
            'id'       => '',
            'title'    => '',
            'subtitle' => '',
            'text'     => '',
            'trigger'  => '',
            'fields'   => [],
            'security' => [
                'honeypot'             => true,
                'captcha_provider'     => 'none',
                'recaptcha_site_key'   => '',
                'recaptcha_secret_key' => '',
                'turnstile_site_key'   => '',
                'turnstile_secret_key' => '',
            ],
            'submission' => [
                'mode'                        => 'mail',
                'endpoint_url'                => '',
                'endpoint_format'             => 'json',
                'endpoint_auth_type'          => 'none',
                'endpoint_auth_value'         => '',
                'recipients'                  => [
                    [ 'email' => (string) get_option( 'admin_email', '' ), 'type' => 'to' ],
                ],
                'owner_subject'               => 'Neue Formular-Einsendung: {form_title}',
                'owner_html_body'             => $this->get_default_owner_template(),
                'owner_html_enabled'          => true,
                'confirmation_enabled'        => false,
                'confirmation_subject'        => 'Ihre Anfrage bei {site_name}',
                'confirmation_html_body'      => $this->get_default_confirmation_template(),
                'confirmation_html_enabled'   => true,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * @return array<string,mixed>|null
     */
    private function normalize_form( mixed $form ): ?array {
        if ( ! is_array( $form ) || empty( $form['id'] ) ) {
            return null;
        }

        return wp_parse_args( $form, $this->get_form_defaults() );
    }

    /**
     * @param array<string,mixed> $form
     * @return array<string,mixed>
     */
    private function sanitize_form( array $form ): array {
        $defaults = $this->get_form_defaults();
        $form     = wp_parse_args( $form, $defaults );

        $form['id']       = sanitize_key( (string) $form['id'] );
        $form['title']    = sanitize_text_field( (string) $form['title'] );
        $form['subtitle'] = sanitize_text_field( (string) $form['subtitle'] );
        $form['text']     = wp_kses_post( (string) $form['text'] );
        $form['trigger']  = sanitize_text_field( (string) $form['trigger'] );

        // Fields
        $form['fields'] = is_array( $form['fields'] )
            ? array_values( array_filter( array_map( [ $this, 'sanitize_field' ], $form['fields'] ) ) )
            : [];

        // Security
        if ( ! is_array( $form['security'] ) ) {
            $form['security'] = [];
        }
        $form['security'] = wp_parse_args( $form['security'], $defaults['security'] );

        $form['security']['honeypot'] = (bool) $form['security']['honeypot'];

        $form['security']['captcha_provider'] = in_array(
            $form['security']['captcha_provider'],
            Restatify_Forms_Constants::CAPTCHA_PROVIDERS,
            true
        ) ? $form['security']['captcha_provider'] : 'none';

        $form['security']['recaptcha_site_key']   = sanitize_text_field( (string) $form['security']['recaptcha_site_key'] );
        $form['security']['recaptcha_secret_key'] = sanitize_text_field( (string) $form['security']['recaptcha_secret_key'] );
        $form['security']['turnstile_site_key']   = sanitize_text_field( (string) $form['security']['turnstile_site_key'] );
        $form['security']['turnstile_secret_key'] = sanitize_text_field( (string) $form['security']['turnstile_secret_key'] );

        // Submission
        if ( ! is_array( $form['submission'] ) ) {
            $form['submission'] = [];
        }
        $form['submission'] = wp_parse_args( $form['submission'], $defaults['submission'] );

        $form['submission']['mode'] = in_array(
            $form['submission']['mode'],
            Restatify_Forms_Constants::SUBMISSION_MODES,
            true
        ) ? $form['submission']['mode'] : 'mail';

        $form['submission']['endpoint_url'] = esc_url_raw( (string) $form['submission']['endpoint_url'] );

        $form['submission']['endpoint_format'] = in_array(
            $form['submission']['endpoint_format'],
            Restatify_Forms_Constants::ENDPOINT_FORMATS,
            true
        ) ? $form['submission']['endpoint_format'] : 'json';

        $form['submission']['endpoint_auth_type'] = in_array(
            $form['submission']['endpoint_auth_type'],
            Restatify_Forms_Constants::ENDPOINT_AUTH_TYPES,
            true
        ) ? $form['submission']['endpoint_auth_type'] : 'none';

        $form['submission']['endpoint_auth_value']       = sanitize_text_field( (string) $form['submission']['endpoint_auth_value'] );
        $form['submission']['owner_subject']             = sanitize_text_field( (string) $form['submission']['owner_subject'] );
        $form['submission']['owner_html_body']           = wp_kses_post( (string) $form['submission']['owner_html_body'] );
        $form['submission']['owner_html_enabled']        = (bool) $form['submission']['owner_html_enabled'];
        $form['submission']['confirmation_enabled']      = (bool) $form['submission']['confirmation_enabled'];
        $form['submission']['confirmation_subject']      = sanitize_text_field( (string) $form['submission']['confirmation_subject'] );
        $form['submission']['confirmation_html_body']    = wp_kses_post( (string) $form['submission']['confirmation_html_body'] );
        $form['submission']['confirmation_html_enabled'] = (bool) $form['submission']['confirmation_html_enabled'];

        // Recipients
        if ( is_array( $form['submission']['recipients'] ) ) {
            $recipients = [];
            foreach ( $form['submission']['recipients'] as $r ) {
                if ( ! is_array( $r ) ) {
                    continue;
                }
                $email = sanitize_email( (string) ( $r['email'] ?? '' ) );
                if ( ! is_email( $email ) ) {
                    continue;
                }
                $type         = in_array( $r['type'] ?? '', Restatify_Forms_Constants::RECIPIENT_TYPES, true )
                    ? $r['type']
                    : 'to';
                $recipients[] = [ 'email' => $email, 'type' => $type ];
            }
            if ( empty( $recipients ) ) {
                $recipients[] = [ 'email' => sanitize_email( (string) get_option( 'admin_email', '' ) ), 'type' => 'to' ];
            }
            $form['submission']['recipients'] = $recipients;
        }

        return $form;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function sanitize_field( mixed $field ): ?array {
        if ( ! is_array( $field ) || empty( $field['id'] ) ) {
            return null;
        }

        $type = in_array( $field['type'] ?? '', Restatify_Forms_Constants::FIELD_TYPES, true )
            ? $field['type']
            : 'text';

        return [
            'id'            => sanitize_key( (string) $field['id'] ),
            'type'          => $type,
            'label'         => sanitize_text_field( (string) ( $field['label'] ?? '' ) ),
            'placeholder'   => sanitize_text_field( (string) ( $field['placeholder'] ?? '' ) ),
            'required'      => (bool) ( $field['required'] ?? false ),
            'options'       => is_array( $field['options'] ?? null )
                ? array_values( array_map( 'sanitize_text_field', $field['options'] ) )
                : [],
            'default_value' => sanitize_text_field( (string) ( $field['default_value'] ?? '' ) ),
            'validation'    => is_array( $field['validation'] ?? null )
                ? $this->sanitize_field_validation( $type, $field['validation'] )
                : [],
        ];
    }

    /**
     * @param array<string,mixed> $v
     * @return array<string,string>
     */
    private function sanitize_field_validation( string $type, array $v ): array {
        $result = [];

        if ( $type === 'email' ) {
            $result['email_check'] = in_array( $v['email_check'] ?? '', Restatify_Forms_Constants::EMAIL_VALIDATION_MODES, true )
                ? $v['email_check']
                : 'regex';
        }

        if ( $type === 'tel' ) {
            $result['tel_check'] = in_array( $v['tel_check'] ?? '', Restatify_Forms_Constants::TEL_VALIDATION_MODES, true )
                ? $v['tel_check']
                : 'simple';
        }

        return $result;
    }

    private function get_default_owner_template(): string {
        return '<h2 style="margin:0 0 12px">Neue Formular-Einsendung</h2>'
            . '<p><strong>Formular:</strong> {form_title}<br><strong>Datum:</strong> {date}</p>'
            . '<hr style="border:none;border-top:1px solid #e5e7eb;margin:16px 0">'
            . '{fields_table}'
            . '<p style="margin-top:24px;font-size:12px;color:#9ca3af">Gesendet via {site_name}</p>';
    }

    private function get_default_confirmation_template(): string {
        return '<h2 style="margin:0 0 12px">Vielen Dank!</h2>'
            . '<p>Wir haben Ihre Anfrage erhalten und melden uns so schnell wie möglich bei Ihnen.</p>'
            . '<hr style="border:none;border-top:1px solid #e5e7eb;margin:16px 0">'
            . '{fields_table}'
            . '<p style="margin-top:24px;font-size:12px;color:#9ca3af">{site_name}</p>';
    }
}
