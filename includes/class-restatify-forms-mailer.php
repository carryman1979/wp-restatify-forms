<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends form submission notifications (owner + optional submitter confirmation).
 */
final class Restatify_Forms_Mailer {

    public function __construct(
        private ?Restatify_Forms_Options $options = null
    ) {}

    /**
     * Sends owner notification and optional confirmation email.
     *
     * @param array<string,mixed>  $form  Form config.
     * @param array<string,string> $data  Submitted field data [field_id => value].
     */
    public function send( array $form, array $data ): bool {
        if ( $this->options instanceof Restatify_Forms_Options ) {
            $form = $this->options->localize_form( $form );
        }

        $submission = $form['submission'] ?? [];

        $form_title = wp_specialchars_decode( (string) ( $form['title'] ?? '' ), ENT_QUOTES );
        $site_name  = wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );
        $date       = (string) wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

        $fields_html = $this->build_fields_table_html( $form, $data );
        $fields_text = $this->build_fields_text( $form, $data );

        $search  = [ '{form_title}', '{site_name}', '{date}', '{fields_table}', '{fields_text}' ];
        $replace = [ $form_title, $site_name, $date, $fields_html, $fields_text ];

        // Owner notification.
        $recipients = $submission['recipients'] ?? [];
        $to  = array_column( array_filter( $recipients, fn( $r ) => ( $r['type'] ?? 'to' ) === 'to' ), 'email' );
        $cc  = array_column( array_filter( $recipients, fn( $r ) => ( $r['type'] ?? '' ) === 'cc' ), 'email' );
        $bcc = array_column( array_filter( $recipients, fn( $r ) => ( $r['type'] ?? '' ) === 'bcc' ), 'email' );

        if ( ! empty( $to ) ) {
            $subject = str_replace( $search, $replace, (string) ( $submission['owner_subject'] ?? '' ) );
            $body    = str_replace( $search, $replace, (string) ( $submission['owner_html_body'] ?? '' ) );
            $this->dispatch( $to, $subject, $body, ! empty( $submission['owner_html_enabled'] ), $cc, $bcc );
        }

        // Submitter confirmation.
        if ( ! empty( $submission['confirmation_enabled'] ) ) {
            $submitter = $this->find_submitter_email( $form, $data );
            if ( $submitter !== '' ) {
                $subject = str_replace( $search, $replace, (string) ( $submission['confirmation_subject'] ?? '' ) );
                $body    = str_replace( $search, $replace, (string) ( $submission['confirmation_html_body'] ?? '' ) );
                $this->dispatch( [ $submitter ], $subject, $body, ! empty( $submission['confirmation_html_enabled'] ) );
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * @param string[] $to
     * @param string[] $cc
     * @param string[] $bcc
     */
    private function dispatch( array $to, string $subject, string $body, bool $html, array $cc = [], array $bcc = [] ): void {
        if ( empty( $to ) ) {
            return;
        }

        $headers = [];
        if ( $html ) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        }
        foreach ( $cc as $addr ) {
            $headers[] = 'Cc: ' . sanitize_email( $addr );
        }
        foreach ( $bcc as $addr ) {
            $headers[] = 'Bcc: ' . sanitize_email( $addr );
        }

        wp_mail(
            array_map( 'sanitize_email', $to ),
            wp_strip_all_tags( $subject ),
            $html ? $body : wp_strip_all_tags( $body ),
            $headers
        );
    }

    /**
     * @param array<string,mixed>  $form
     * @param array<string,string> $data
     */
    private function find_submitter_email( array $form, array $data ): string {
        foreach ( $form['fields'] ?? [] as $field ) {
            if ( ( $field['type'] ?? '' ) !== 'email' ) {
                continue;
            }
            $val = sanitize_email( (string) ( $data[ $field['id'] ?? '' ] ?? '' ) );
            if ( is_email( $val ) ) {
                return $val;
            }
        }

        return '';
    }

    /**
     * @param array<string,mixed>  $form
     * @param array<string,string> $data
     */
    private function build_fields_table_html( array $form, array $data ): string {
        $rows = '';
        foreach ( $form['fields'] ?? [] as $field ) {
            $fid = $field['id'] ?? '';
            if ( $fid === '' || ( $field['type'] ?? '' ) === 'hidden' ) {
                continue;
            }
            $label = esc_html( $field['label'] ?? $fid );
            $value = esc_html( (string) ( $data[ $fid ] ?? '' ) );
            $rows .= "<tr>"
                . "<td style='padding:6px 12px;font-weight:600;white-space:nowrap;vertical-align:top;border-bottom:1px solid #f3f4f6'>{$label}</td>"
                . "<td style='padding:6px 12px;vertical-align:top;border-bottom:1px solid #f3f4f6'>{$value}</td>"
                . "</tr>";
        }

        return "<table style='border-collapse:collapse;width:100%;font-family:sans-serif;font-size:14px'>"
            . "<tbody>{$rows}</tbody></table>";
    }

    /**
     * @param array<string,mixed>  $form
     * @param array<string,string> $data
     */
    private function build_fields_text( array $form, array $data ): string {
        $lines = [];
        foreach ( $form['fields'] ?? [] as $field ) {
            $fid = $field['id'] ?? '';
            if ( $fid === '' || ( $field['type'] ?? '' ) === 'hidden' ) {
                continue;
            }
            $lines[] = ( $field['label'] ?? $fid ) . ': ' . ( $data[ $fid ] ?? '' );
        }

        return implode( "\n", $lines );
    }
}
