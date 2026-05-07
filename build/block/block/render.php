<?php
/**
 * Server-side render for the Form Trigger block.
 * This file is referenced in block.json "render" only as a fallback.
 * The primary render callback is registered in class-restatify-forms-plugin.php.
 *
 * @package Restatify_Forms
 */

defined( 'ABSPATH' ) || exit;

$form_id = isset( $attributes['formId'] ) ? sanitize_key( $attributes['formId'] ) : '';
$label   = isset( $attributes['label'] )  ? sanitize_text_field( $attributes['label'] ) : __( 'Kontakt aufnehmen', 'wp-restatify-forms' );
$style   = isset( $attributes['style'] )  ? sanitize_text_field( $attributes['style'] ) : 'button';

if ( ! $form_id ) {
    return '';
}

$trigger = '#restatify-form-' . $form_id;
$class   = ( $style === 'button' ) ? 'rsfm-trigger-btn' : 'rsfm-trigger-link';

echo '<a href="' . esc_attr( $trigger ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</a>';
