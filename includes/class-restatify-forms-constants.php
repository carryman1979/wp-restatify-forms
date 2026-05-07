<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shared constants for the Restatify Forms plugin.
 */
final class Restatify_Forms_Constants {
    public const OPTION_KEY        = 'restatify_forms_config';
    public const NONCE_SUBMIT      = 'restatify_forms_submit';
    public const NONCE_ADMIN       = 'restatify_forms_admin_nonce';
    public const AJAX_SUBMIT       = 'restatify_forms_submit';
    public const AJAX_SAVE         = 'restatify_forms_save';
    public const AJAX_DELETE       = 'restatify_forms_delete';
    public const TEXT_DOMAIN       = 'wp-restatify-forms';
    public const ADMIN_PAGE_SLUG   = 'wp-restatify-forms';

    public const FIELD_TYPES = [
        'text',
        'email',
        'tel',
        'textarea',
        'select',
        'checkbox',
        'radio',
        'date',
        'hidden',
    ];

    public const EMAIL_VALIDATION_MODES = [
        'none',
        'simple',
        'regex',
        'dns',
    ];

    public const TEL_VALIDATION_MODES = [
        'none',
        'simple',
        'e164',
    ];

    public const CAPTCHA_PROVIDERS = [
        'none',
        'recaptcha',
        'turnstile',
    ];

    public const SUBMISSION_MODES = [
        'mail',
        'endpoint',
    ];

    public const ENDPOINT_FORMATS = [
        'json',
        'form',
    ];

    public const ENDPOINT_AUTH_TYPES = [
        'none',
        'bearer',
        'basic',
    ];

    public const RECIPIENT_TYPES = [
        'to',
        'cc',
        'bcc',
    ];

    private function __construct() {}
}
