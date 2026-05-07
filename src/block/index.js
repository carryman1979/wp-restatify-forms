import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, RadioControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
    edit: function Edit( { attributes, setAttributes } ) {
        var { formId, label, style } = attributes;

        // Fetch available forms from the REST API or use a localized variable.
        var forms = ( window.rsfmBlockData && window.rsfmBlockData.forms ) || [];

        var formOptions = [
            { label: __( '— Formular auswählen —', 'wp-restatify-forms' ), value: '' },
            ...forms.map( function ( f ) {
                return { label: f.title || f.id, value: f.id };
            } ),
        ];

        var blockProps = useBlockProps( {
            className: 'rsfm-block-preview',
        } );

        var triggerPreview = formId
            ? '#restatify-form-' + formId
            : '';

        return (
            <>
                <InspectorControls>
                    <PanelBody title={ __( 'Formular-Trigger', 'wp-restatify-forms' ) }>
                        <SelectControl
                            label={ __( 'Formular', 'wp-restatify-forms' ) }
                            value={ formId }
                            options={ formOptions }
                            onChange={ ( val ) => setAttributes( { formId: val } ) }
                        />
                        <TextControl
                            label={ __( 'Beschriftung', 'wp-restatify-forms' ) }
                            value={ label }
                            onChange={ ( val ) => setAttributes( { label: val } ) }
                        />
                        <RadioControl
                            label={ __( 'Stil', 'wp-restatify-forms' ) }
                            selected={ style }
                            options={ [
                                { label: __( 'Button', 'wp-restatify-forms' ), value: 'button' },
                                { label: __( 'Link', 'wp-restatify-forms' ), value: 'link' },
                            ] }
                            onChange={ ( val ) => setAttributes( { style: val } ) }
                        />
                        { triggerPreview && (
                            <p style={ { fontSize: '12px', color: '#646970', marginTop: '8px' } }>
                                { __( 'Trigger-Link:', 'wp-restatify-forms' ) } <code>{ triggerPreview }</code>
                            </p>
                        ) }
                    </PanelBody>
                </InspectorControls>

                <div { ...blockProps }>
                    { ! formId && (
                        <div style={ { padding: '12px 16px', background: '#f0f6fc', border: '1px solid #c3c4c7', borderRadius: '4px', fontSize: '13px', color: '#2271b1' } }>
                            { __( 'Bitte ein Formular im Block-Inspektor auswählen.', 'wp-restatify-forms' ) }
                        </div>
                    ) }
                    { formId && style === 'button' && (
                        <span style={ { display: 'inline-block', padding: '0.6rem 1.4rem', borderRadius: '999px', background: 'var(--wp--preset--color--primary, #ff6b00)', color: '#fff', fontWeight: 600, fontSize: '0.95rem', cursor: 'default', pointerEvents: 'none' } }>
                            { label }
                        </span>
                    ) }
                    { formId && style === 'link' && (
                        <span style={ { textDecoration: 'underline', cursor: 'default', pointerEvents: 'none' } }>
                            { label }
                        </span>
                    ) }
                </div>
            </>
        );
    },

    // save: null → server-side render via render.php / render_callback
    save: function () {
        return null;
    },
} );
