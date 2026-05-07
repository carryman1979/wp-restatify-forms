<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main composition root: wires all services and registers WordPress hooks.
 */
final class Restatify_Forms_Plugin {

    private Restatify_Forms_Options      $options;
    private Restatify_Forms_Captcha      $captcha;
    private Restatify_Forms_Mailer       $mailer;
    private Restatify_Forms_Submission   $submission;
    private Restatify_Forms_UI           $ui;
    private Restatify_Forms_Admin_Page   $admin_page;
    /** @var array<int,string> */
    private array                        $block_editor_handles = [];

    public function __construct( private string $plugin_file ) {
        $this->options    = new Restatify_Forms_Options();
        $this->captcha    = new Restatify_Forms_Captcha();
        $this->mailer     = new Restatify_Forms_Mailer( $this->options );
        $this->submission = new Restatify_Forms_Submission( $this->options, $this->captcha, $this->mailer );
        $this->ui         = new Restatify_Forms_UI( $this->options );
        $this->admin_page = new Restatify_Forms_Admin_Page( $this->options );

        $this->register_hooks();
    }

    private function register_hooks(): void {
        // Textdomain.
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'init', [ $this->options, 'register_polylang_strings' ], 20 );

        // Admin page.
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this->admin_page, 'enqueue_admin_assets' ] );

        // Admin AJAX.
        add_action( 'wp_ajax_' . Restatify_Forms_Constants::AJAX_SAVE,   [ $this->admin_page, 'ajax_save' ] );
        add_action( 'wp_ajax_' . Restatify_Forms_Constants::AJAX_DELETE, [ $this->admin_page, 'ajax_delete' ] );

        // Frontend.
        add_action( 'wp_enqueue_scripts', [ $this->ui, 'enqueue_assets' ] );
        add_action( 'wp_footer', [ $this->ui, 'render_popups' ], 30 );
        add_filter( 'wp_link_query', [ $this->ui, 'extend_wp_link_query' ], 10, 2 );

        // Frontend AJAX (logged-in + logged-out).
        add_action( 'wp_ajax_' . Restatify_Forms_Constants::AJAX_SUBMIT,        [ $this->submission, 'handle_ajax' ] );
        add_action( 'wp_ajax_nopriv_' . Restatify_Forms_Constants::AJAX_SUBMIT, [ $this->submission, 'handle_ajax' ] );

        // Gutenberg block.
        add_action( 'init', [ $this, 'register_block' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_data' ] );
    }

    public function load_textdomain(): void {
        load_plugin_textdomain(
            Restatify_Forms_Constants::TEXT_DOMAIN,
            false,
            dirname( plugin_basename( $this->plugin_file ) ) . '/languages'
        );
    }

    public function register_admin_menu(): void {
        add_menu_page(
            __( 'Restatify Forms', Restatify_Forms_Constants::TEXT_DOMAIN ),
            __( 'RS Forms', Restatify_Forms_Constants::TEXT_DOMAIN ),
            'manage_options',
            Restatify_Forms_Constants::ADMIN_PAGE_SLUG,
            [ $this, 'dispatch_admin_page' ],
            'dashicons-feedback',
            58
        );
    }

    /**
     * Dispatches either the list or the edit page based on the 'action' URL param.
     */
    public function dispatch_admin_page(): void {
        $action = sanitize_key( (string) ( $_GET['action'] ?? 'list' ) );

        if ( $action === 'edit' ) {
            $this->admin_page->render_edit_page();
        } else {
            $this->admin_page->render_list_page();
        }
    }

    /**
     * Registers the optional "Form Trigger" Gutenberg block from the build folder.
     */
    public function register_block(): void {
        $block_dir = RESTATIFY_FORMS_PLUGIN_DIR . 'build/block';
        if ( ! file_exists( $block_dir . '/block.json' ) ) {
            return;
        }

        $block_type = register_block_type(
            $block_dir,
            [
                'render_callback' => [ $this, 'render_block' ],
            ]
        );

        if ( $block_type instanceof WP_Block_Type ) {
            $this->block_editor_handles = $block_type->editor_script_handles;
        }
    }

    /**
     * Passes available form IDs/titles to the block editor script.
     */
    public function enqueue_block_editor_data(): void {
        if ( empty( $this->block_editor_handles ) ) {
            return;
        }

        $forms = array_values(
            array_map(
                static fn( array $form ): array => [
                    'id'    => (string) ( $form['id'] ?? '' ),
                    'title' => (string) ( $form['title'] ?? '' ),
                ],
                $this->options->get_all_forms()
            )
        );

        $json = wp_json_encode( [ 'forms' => $forms ] );
        if ( ! is_string( $json ) ) {
            return;
        }

        foreach ( $this->block_editor_handles as $handle ) {
            wp_add_inline_script( $handle, 'window.rsfmBlockData = ' . $json . ';', 'before' );
        }
    }

    /**
     * Server-side render callback for the Form Trigger block.
     *
     * @param array<string,mixed> $attrs
     */
    public function render_block( array $attrs ): string {
        $form_id = sanitize_key( (string) ( $attrs['formId'] ?? '' ) );
        $label   = esc_html( (string) ( $attrs['label'] ?? __( 'Kontakt aufnehmen', Restatify_Forms_Constants::TEXT_DOMAIN ) ) );
        $style   = $attrs['style'] ?? 'button';

        if ( $form_id === '' ) {
            return '';
        }

        $form = $this->options->get_form( $form_id );
        if ( $form === null ) {
            return '';
        }

        $trigger = esc_attr( $form['trigger'] );

        if ( $style === 'link' ) {
            return "<a href=\"{$trigger}\" class=\"rsfm-trigger-link\">{$label}</a>";
        }

        return "<a href=\"{$trigger}\" class=\"rsfm-trigger-btn\">{$label}</a>";
    }
}
