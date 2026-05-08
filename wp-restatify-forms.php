<?php
/**
 * Plugin Name: Restatify Forms
 * Description: Multi-form popup builder with configurable fields, email templates and custom endpoint forwarding.
 * Version: 1.0.2
 * Author: Restatify
 * License: GPL-2.0-or-later
 * Requires at least: 6.9
 * Requires PHP: 8.0
 * Text Domain: wp-restatify-forms
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'RESTATIFY_FORMS_PLUGIN_FILE', __FILE__ );
define( 'RESTATIFY_FORMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RESTATIFY_FORMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RESTATIFY_FORMS_VERSION', '1.0.2' );

require_once RESTATIFY_FORMS_PLUGIN_DIR . 'includes/class-restatify-forms-constants.php';
require_once RESTATIFY_FORMS_PLUGIN_DIR . 'includes/class-restatify-forms-options.php';
require_once RESTATIFY_FORMS_PLUGIN_DIR . 'includes/class-restatify-forms-captcha.php';
require_once RESTATIFY_FORMS_PLUGIN_DIR . 'includes/class-restatify-forms-mailer.php';
require_once RESTATIFY_FORMS_PLUGIN_DIR . 'includes/class-restatify-forms-submission.php';
require_once RESTATIFY_FORMS_PLUGIN_DIR . 'includes/class-restatify-forms-ui.php';
require_once RESTATIFY_FORMS_PLUGIN_DIR . 'includes/class-restatify-forms-admin-page.php';
require_once RESTATIFY_FORMS_PLUGIN_DIR . 'includes/class-restatify-forms-plugin.php';

new Restatify_Forms_Plugin( RESTATIFY_FORMS_PLUGIN_FILE );
